const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');
const dotenv = require('dotenv');

dotenv.config();

test.describe.configure({ mode: 'serial' });
test.setTimeout(180000);

const PAGE_TIMEOUT_MS = 10_000;
const REQUEST_DELAY_MS = 500;
const ERROR_PATTERNS = [
  'Warning:',
  'Fatal error:',
  'Notice:',
  'Parse error:',
  'Undefined',
];
const OPTIONAL_PATHS = new Set([
  '/configuracion/licencia',
  '/reportes/index',
]);
const EXPLICIT_PATHS = [
  'dashboard/dashboard_legajos',
  'legajos/armar_legajo',
  'legajos/buscar_legajos',
  'legajos/verificar_legajos',
  'legajos/administrar_legajos',
  'expedientes/indice_busqueda',
  'expedientes/reporte',
  'usuarios/listar',
  'seguridad/roles',
  'usuarios/grupo',
  'seguridad/permisos_legajos',
  'configuracion/configuracion_legajos',
  'configuracion/servidor_smtp',
  'configuracion/mantenimiento',
  'configuracion/licencia',
  'reportes/index',
];

test('SCANTEC full navigation smoke test', async ({ browser }) => {
  const baseUrl = requireEnv('SCANTEC_URL');
  const username = requireEnv('SCANTEC_USER');
  const password = requireEnv('SCANTEC_PASS');

  const outputDir = path.resolve(process.cwd(), 'test-results');
  const storageStatePath = path.join(outputDir, 'navigation-storage-state.json');
  const reportPath = path.join(outputDir, 'navigation-report.json');
  ensureDir(outputDir);

  const loginContext = await browser.newContext();
  const loginPage = await loginContext.newPage();
  loginPage.setDefaultTimeout(PAGE_TIMEOUT_MS);

  const loginResult = await performLogin({
    page: loginPage,
    baseUrl,
    username,
    password,
  });

  await loginContext.storageState({ path: storageStatePath });
  await loginContext.close();

  const crawlContext = await browser.newContext({ storageState: storageStatePath });
  const page = await crawlContext.newPage();
  page.setDefaultTimeout(PAGE_TIMEOUT_MS);

  const results = [];
  const visited = new Set();

  results.push({
    url: loginResult.finalUrl,
    source: 'login',
    status: 'PASS',
    httpStatus: loginResult.httpStatus,
    title: loginResult.title,
    textLength: loginResult.textLength,
    redirectLoop: false,
    details: 'Login successful and session established.',
  });

  const discoveredLinks = await collectNavigationLinks(page, baseUrl, results, visited);
  const explicitLinks = EXPLICIT_PATHS.map((pathname) => new URL(pathname, ensureTrailingSlash(baseUrl)).toString());
  const queue = dedupeUrls([...discoveredLinks, ...explicitLinks]);

  for (const url of queue) {
    if (visited.has(normalizeUrl(url))) {
      continue;
    }

    await delay(REQUEST_DELAY_MS);
    const result = await inspectInternalPage(page, url, baseUrl);
    visited.add(normalizeUrl(url));
    results.push(result);
  }

  const summary = summarizeResults(results);
  console.table([
    {
      totalVisited: summary.totalVisited,
      passed: summary.passed,
      failed: summary.failed,
      skipped: summary.skipped,
    },
  ]);

  fs.writeFileSync(
    reportPath,
    JSON.stringify(
      {
        generatedAt: new Date().toISOString(),
        baseUrl,
        summary,
        results,
      },
      null,
      2
    ),
    'utf8'
  );

  await crawlContext.close();

  expect(summary.failed, 'Navigation smoke test has failing URLs.').toBe(0);
});

async function performLogin({ page, baseUrl, username, password }) {
  const loginUrl = ensureTrailingSlash(baseUrl);
  const response = await page.goto(loginUrl, { waitUntil: 'domcontentloaded', timeout: PAGE_TIMEOUT_MS });

  expect(response, 'Login page did not return an HTTP response.').not.toBeNull();
  expect(response.status(), 'Login page returned an unexpected status.').toBeLessThan(400);

  await page.waitForSelector('input[name="usuario"]', { timeout: PAGE_TIMEOUT_MS });
  await page.waitForSelector('input[name="clave"]', { timeout: PAGE_TIMEOUT_MS });
  const csrfTokenLocator = page.locator('input[name="csrf_token"]');
  const hasCsrfToken = (await csrfTokenLocator.count()) > 0;
  if (hasCsrfToken) {
    const csrfToken = await csrfTokenLocator.inputValue();
    expect(csrfToken, 'CSRF token is empty on login page.').toBeTruthy();
  }

  await page.locator('input[name="usuario"]').fill(username);
  await page.locator('input[name="clave"]').fill(password);

  const submitLocator = page.locator('button[type="submit"], input[type="submit"]').first();
  await Promise.all([
    page.waitForLoadState('domcontentloaded', { timeout: PAGE_TIMEOUT_MS }),
    submitLocator.click(),
  ]);

  await resolveConcurrentSessionPrompt(page);
  await page.waitForTimeout(800);

  const finalUrl = page.url();
  const title = (await page.title()).trim();
  const bodyText = await getVisibleText(page);

  expect(finalUrl.toLowerCase(), 'Login redirected back to login page.').not.toContain('login');
  expect(await page.locator('input[name="usuario"]').count(), 'Session not established after login.').toBe(0);
  expect(title, 'Dashboard/home title is empty after login.').not.toBe('');
  expect(bodyText.length, 'Dashboard/home page looks blank after login.').toBeGreaterThanOrEqual(100);

  return {
    finalUrl,
    httpStatus: 200,
    title,
    textLength: bodyText.length,
  };
}

async function resolveConcurrentSessionPrompt(page) {
  const concurrentSessionHeading = page.getByRole('heading', { name: /sesi[oó]n ya iniciada/i });
  const hasConcurrentSessionPrompt = (await concurrentSessionHeading.count()) > 0;

  if (!hasConcurrentSessionPrompt) {
    return;
  }

  const continueButton = page.getByRole('button', { name: /cerrar sesi[oó]n anterior y continuar/i });
  await Promise.all([
    page.waitForLoadState('domcontentloaded', { timeout: PAGE_TIMEOUT_MS }),
    continueButton.click(),
  ]);
}

async function collectNavigationLinks(page, baseUrl, results, visited) {
  await page.goto(baseUrl, { waitUntil: 'domcontentloaded', timeout: PAGE_TIMEOUT_MS });
  await page.waitForTimeout(500);

  const hrefs = await page.$$eval(
    'nav a[href], aside a[href], header a[href], [role="navigation"] a[href], .sidebar a[href], .menu-group a[href]',
    (anchors) => anchors.map((anchor) => anchor.getAttribute('href')).filter(Boolean)
  );

  const internal = [];
  for (const rawHref of hrefs) {
    const classification = classifyHref(rawHref, baseUrl);
    if (classification.kind === 'skip') {
      results.push({
        url: rawHref,
        source: 'navigation',
        status: 'SKIP',
        httpStatus: null,
        title: '',
        textLength: 0,
        redirectLoop: false,
        details: classification.reason,
      });
      continue;
    }

    const normalized = normalizeUrl(classification.url);
    if (!visited.has(normalized)) {
      internal.push(classification.url);
    }
  }

  return dedupeUrls(internal);
}

async function inspectInternalPage(page, url, baseUrl) {
  const pathname = new URL(url).pathname;

  try {
    const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: PAGE_TIMEOUT_MS });
    const responseStatus = response ? response.status() : null;
    const redirectInfo = getRedirectInfo(response);

    if (redirectInfo.hasLoop) {
      return buildResult(url, 'FAIL', responseStatus, '', 0, true, `Redirect loop detected: ${redirectInfo.chain.join(' -> ')}`);
    }

    if (isOptionalPath(pathname) && responseStatus === 404) {
      return buildResult(url, 'SKIP', responseStatus, '', 0, false, 'Optional route does not exist in this installation.');
    }

    if (responseStatus === null || responseStatus >= 400) {
      return buildResult(url, 'FAIL', responseStatus, '', 0, false, `Unexpected HTTP status ${responseStatus ?? 'NO_RESPONSE'}.`);
    }

    const title = (await page.title()).trim();
    const text = await getVisibleText(page);
    const html = await page.content();
    const phpError = ERROR_PATTERNS.find((pattern) => html.includes(pattern) || text.includes(pattern));
    const isInternalNotFound = /p[aá]gina no encontrada/i.test(title) || /p[aá]gina no encontrada/i.test(text);

    if (!title) {
      return buildResult(url, 'FAIL', responseStatus, title, text.length, false, 'Empty page title.');
    }

    if (isOptionalPath(pathname) && isInternalNotFound) {
      return buildResult(url, 'SKIP', responseStatus, title, text.length, false, 'Optional route is not available in this installation.');
    }

    if (phpError) {
      return buildResult(url, 'FAIL', responseStatus, title, text.length, false, `Detected PHP error string: ${phpError}`);
    }

    if (text.length < 200) {
      return buildResult(url, 'FAIL', responseStatus, title, text.length, false, `Visible text too short (${text.length} chars).`);
    }

    return buildResult(url, 'PASS', responseStatus, title, text.length, false, 'OK');
  } catch (error) {
    const message = error && error.message ? error.message : String(error);
    const redirectLoop = /ERR_TOO_MANY_REDIRECTS|redirect/i.test(message);
    const optional = isOptionalPath(pathname);

    if (optional && /404|Not Found/i.test(message)) {
      return buildResult(url, 'SKIP', null, '', 0, false, 'Optional route is not available.');
    }

    return buildResult(url, 'FAIL', null, '', 0, redirectLoop, message);
  }
}

function classifyHref(rawHref, baseUrl) {
  const href = String(rawHref || '').trim();
  if (!href) {
    return { kind: 'skip', reason: 'Empty href.' };
  }

  if (href.startsWith('#')) {
    return { kind: 'skip', reason: 'Anchor link.' };
  }

  if (/^mailto:/i.test(href)) {
    return { kind: 'skip', reason: 'Mailto link.' };
  }

  if (/^javascript:/i.test(href)) {
    return { kind: 'skip', reason: 'Javascript link.' };
  }

  const url = new URL(href, ensureTrailingSlash(baseUrl));
  const base = new URL(ensureTrailingSlash(baseUrl));
  if (url.origin !== base.origin) {
    return { kind: 'skip', reason: 'External link.' };
  }

  if (/salir|logout|cerrar/i.test(url.pathname)) {
    return { kind: 'skip', reason: 'Logout link.' };
  }

  return { kind: 'internal', url: url.toString() };
}

function getRedirectInfo(response) {
  const chain = [];
  let request = response ? response.request() : null;

  while (request) {
    chain.unshift(request.url());
    request = request.redirectedFrom();
  }

  const seen = new Set();
  let hasLoop = false;
  for (const url of chain) {
    if (seen.has(url)) {
      hasLoop = true;
      break;
    }
    seen.add(url);
  }

  return { chain, hasLoop };
}

async function getVisibleText(page) {
  const text = await page.locator('body').innerText().catch(() => '');
  return text.replace(/\s+/g, ' ').trim();
}

function buildResult(url, status, httpStatus, title, textLength, redirectLoop, details) {
  return {
    url,
    source: 'crawl',
    status,
    httpStatus,
    title,
    textLength,
    redirectLoop,
    details,
  };
}

function summarizeResults(results) {
  const visited = results.filter((row) => row.status !== 'SKIP').length;
  const passed = results.filter((row) => row.status === 'PASS').length;
  const failed = results.filter((row) => row.status === 'FAIL').length;
  const skipped = results.filter((row) => row.status === 'SKIP').length;

  return {
    totalVisited: visited,
    passed,
    failed,
    skipped,
  };
}

function ensureDir(dirPath) {
  fs.mkdirSync(dirPath, { recursive: true });
}

function ensureTrailingSlash(url) {
  return url.endsWith('/') ? url : `${url}/`;
}

function normalizeUrl(url) {
  const normalized = new URL(url);
  normalized.hash = '';
  return normalized.toString().replace(/\/$/, '');
}

function dedupeUrls(urls) {
  const seen = new Set();
  const unique = [];

  for (const url of urls) {
    const normalized = normalizeUrl(url);
    if (seen.has(normalized)) {
      continue;
    }
    seen.add(normalized);
    unique.push(url);
  }

  return unique;
}

function requireEnv(name) {
  const value = process.env[name];
  if (!value) {
    throw new Error(`Missing required environment variable: ${name}`);
  }
  return value;
}

function delay(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function isOptionalPath(pathname) {
  for (const optionalPath of OPTIONAL_PATHS) {
    if (pathname === optionalPath || pathname.endsWith(optionalPath)) {
      return true;
    }
  }

  return false;
}
