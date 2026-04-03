# SCANTEC Playwright Smoke Tests

This package adds a Playwright-based smoke navigation test for SCANTEC, a PHP document management and legajo control system.

## Files included

- `tests/smoke-navigation.spec.js`
- `package.json`
- `.env.example`

## What the smoke test does

The test logs into SCANTEC using valid credentials and then performs a server-rendered navigation smoke test.

It validates:

- login succeeds and PHP session is established
- links from the main navigation, header, sidebar, and menu groups are collected dynamically
- every internal page visited returns a non-error HTTP response
- pages do not contain obvious PHP runtime errors such as `Warning:` or `Fatal error:`
- pages are not effectively blank
- page titles are not empty
- key internal routes are checked explicitly even if they are not visible in the menu
- external, anchor, `mailto:`, `javascript:`, and logout links are skipped
- redirect loops are detected and reported
- a JSON report is written to `test-results/navigation-report.json`

## Requirements

- Node.js 20+ recommended
- A reachable SCANTEC environment
- A valid SCANTEC user with access to the internal app

## Install

From the project root:

```bash
npm install
```

Then install the Playwright browser:

```bash
npx playwright install chromium
```

Official Playwright installation docs:

- https://playwright.dev/docs/intro
- https://playwright.dev/docs/test-cli

## Configure environment variables

Copy `.env.example` to `.env` and fill in real values:

```bash
cp .env.example .env
```

Windows PowerShell alternative:

```powershell
Copy-Item .env.example .env
```

Required variables:

- `SCANTEC_URL`
- `SCANTEC_USER`
- `SCANTEC_PASS`

Example:

```env
SCANTEC_URL=http://localhost/scantec
SCANTEC_USER=admin
SCANTEC_PASS=admin123
```

## Run the smoke test

Headless:

```bash
npm run test:smoke
```

Headed:

```bash
npm run test:smoke:headed
```

Run all Playwright tests:

```bash
npm test
```

Run the file directly:

```bash
npx playwright test tests/smoke-navigation.spec.js
```

Headed direct run:

```bash
npx playwright test tests/smoke-navigation.spec.js --headed
```

## Output

The test writes:

- terminal PASS / FAIL / SKIP logs per URL
- a summary table at the end with total visited, passed, failed, and skipped
- a JSON report file at:

```text
test-results/navigation-report.json
```

The test also writes a Playwright storage state file after login so the authenticated session can be reused within the smoke flow.

## Notes

- The test expects the login page to contain:
  - `input[name="usuario"]`
  - `input[name="clave"]`
  - `input[name="csrf_token"]`
- The test treats `/reportes/index` as optional and will mark it as `SKIP` if the route is not available.
- Because SCANTEC is server-rendered HTML and not a SPA, the test uses full page navigation and content assertions for each visited route.

## Troubleshooting

If login fails:

- verify `SCANTEC_URL` points to the real login page
- verify the user has access to the internal dashboard
- confirm the login form still uses `usuario`, `clave`, and `csrf_token`

If many pages fail because of redirects:

- check session timeout configuration
- verify the app is not redirecting back to login
- confirm the base URL does not add or strip a different subfolder

If pages fail because of low visible text:

- inspect whether the route renders a placeholder, partial layout, or permission error page
- adjust the threshold only if that route is intentionally minimal
