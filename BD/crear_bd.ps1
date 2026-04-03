Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Read-RequiredText {
    param(
        [string]$Prompt,
        [string]$DefaultValue = ''
    )

    while ($true) {
        if ($DefaultValue -ne '') {
            $value = Read-Host "$Prompt [$DefaultValue]"
            if ([string]::IsNullOrWhiteSpace($value)) {
                $value = $DefaultValue
            }
        } else {
            $value = Read-Host $Prompt
        }

        if (-not [string]::IsNullOrWhiteSpace($value)) {
            return $value.Trim()
        }

        Write-Host "Debe completar este dato." -ForegroundColor Yellow
    }
}

function Read-Choice {
    param(
        [string]$Prompt,
        [string[]]$ValidValues
    )

    while ($true) {
        $value = Read-Host $Prompt
        if (-not [string]::IsNullOrWhiteSpace($value)) {
            $normalized = $value.Trim().ToUpper()
            if ($ValidValues -contains $normalized) {
                return $normalized
            }
        }

        Write-Host ("Opciones validas: " + ($ValidValues -join ', ')) -ForegroundColor Yellow
    }
}

function Get-PlainTextFromSecureString {
    param([Security.SecureString]$SecureString)

    $bstr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($SecureString)
    try {
        return [Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr)
    } finally {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr)
    }
}

function Get-MysqlExecutable {
    $candidates = @(
        'C:\xampp\mysql\bin\mysql.exe',
        'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe',
        'C:\Program Files\MySQL\MySQL Server 8.1\bin\mysql.exe'
    )

    foreach ($candidate in $candidates) {
        if (Test-Path $candidate) {
            return $candidate
        }
    }

    $command = Get-Command mysql.exe -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    throw "No se encontró mysql.exe. Verifique XAMPP/MySQL o agregue mysql.exe al PATH."
}

function Get-PhpExecutable {
    $candidates = @(
        'C:\xampp\php\php.exe',
        'C:\php\php.exe'
    )

    foreach ($candidate in $candidates) {
        if (Test-Path $candidate) {
            return $candidate
        }
    }

    $command = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    throw "No se encontró php.exe. Verifique XAMPP/PHP o agregue php.exe al PATH."
}

function Get-PhpDefineValue {
    param(
        [string]$PhpFilePath,
        [string]$ConstantName
    )

    if (-not (Test-Path $PhpFilePath)) {
        throw "No se encontró el archivo de configuracion: $PhpFilePath"
    }

    $content = Get-Content -Path $PhpFilePath -Raw -Encoding UTF8
    $pattern = "define\s*\(\s*['""]{0}['""]\s*,\s*['""](?<value>[^'""]*)['""]\s*\)" -f [regex]::Escape($ConstantName)
    $match = [regex]::Match($content, $pattern, [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    if (-not $match.Success) {
        return $null
    }

    return $match.Groups['value'].Value
}

function Quote-MysqlIdentifier {
    param([string]$Value)

    return ('`{0}`' -f $Value.Replace('`', '``'))
}

function Quote-MysqlString {
    param([string]$Value)

    return ("'{0}'" -f $Value.Replace('\', '\\').Replace("'", "\'"))
}

function Assert-SafeIdentifier {
    param(
        [string]$Value,
        [string]$Label
    )

    if ($Value -notmatch '^[A-Za-z0-9_]+$') {
        throw "$Label invalido. Solo se permiten letras, numeros y guion bajo."
    }
}

function Invoke-MysqlCommand {
    param(
        [string]$MysqlExe,
        [string]$HostName,
        [string]$PortNumber,
        [string]$UserName,
        [string]$Password,
        [string]$Sql
    )

    $previousMysqlPwd = $env:MYSQL_PWD
    try {
        $env:MYSQL_PWD = $Password
        $args = @(
            "--host=$HostName",
            "--port=$PortNumber",
            "--user=$UserName",
            '--default-character-set=utf8mb4',
            '--batch',
            '--skip-column-names',
            '-e',
            $Sql
        )

        $output = & $MysqlExe @args 2>&1
        if ($LASTEXITCODE -ne 0) {
            $message = ($output | Out-String).Trim()
            if ([string]::IsNullOrWhiteSpace($message)) {
                $message = 'Fallo la ejecucion de mysql.exe.'
            }
            throw $message
        }

        return $output
    } finally {
        if ($null -eq $previousMysqlPwd) {
            Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
        } else {
            $env:MYSQL_PWD = $previousMysqlPwd
        }
    }
}

function Import-SqlFile {
    param(
        [string]$MysqlExe,
        [string]$HostName,
        [string]$PortNumber,
        [string]$UserName,
        [string]$Password,
        [string]$SqlFilePath
    )

    $previousMysqlPwd = $env:MYSQL_PWD
    try {
        $env:MYSQL_PWD = $Password
        $command = '"' + $MysqlExe + '" ' +
            '--host="' + $HostName + '" ' +
            '--port="' + $PortNumber + '" ' +
            '--user="' + $UserName + '" ' +
            '--default-character-set=utf8mb4 < "' + $SqlFilePath + '" 2>&1'

        $output = cmd.exe /c $command
        if ($LASTEXITCODE -ne 0) {
            $message = ($output | Out-String).Trim()
            if ([string]::IsNullOrWhiteSpace($message)) {
                $message = "Fallo la importacion del archivo SQL."
            }
            throw $message
        }

        return $output
    } finally {
        if ($null -eq $previousMysqlPwd) {
            Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
        } else {
            $env:MYSQL_PWD = $previousMysqlPwd
        }
    }
}

function Remove-TriggerBlocks {
    param([string]$SqlContent)

    $normalized = $SqlContent -replace "`r`n", "`n"
    $pattern = '(?is)SET\s+@OLDTMP_SQL_MODE=@@SQL_MODE,.*?;\s*DELIMITER\s*//\s*CREATE\s+TRIGGER\b.*?END//\s*DELIMITER\s*;\s*SET\s+SQL_MODE=@OLDTMP_SQL_MODE;\s*'
    $normalized = [regex]::Replace($normalized, $pattern, '')
    return ($normalized -replace "`n", "`r`n")
}

function Get-PhpBcryptHash {
    param(
        [string]$PhpExe,
        [string]$PlainPassword
    )

    $encodedPassword = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($PlainPassword))
    $script = '$password = base64_decode($argv[1]); echo password_hash($password, PASSWORD_BCRYPT, [''cost'' => 12]);'
    $hash = & $PhpExe -r $script $encodedPassword 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "No se pudo generar el hash del usuario administrador del sistema."
    }

    return ($hash | Out-String).Trim()
}

function ConvertTo-PhpSingleQuotedString {
    param([string]$Value)

    return "'" + $Value.Replace('\', '\\').Replace("'", "\'") + "'"
}

function Get-DbConnectionEntriesFromConfig {
    param([string]$ConfigPath)

    if (-not (Test-Path $ConfigPath)) {
        return @{}
    }

    $content = Get-Content -Path $ConfigPath -Raw -Encoding UTF8
    $blockPattern = '(?s)// <db-connections>\s*(?<body>.*?)\s*// </db-connections>'
    $blockMatch = [regex]::Match($content, $blockPattern)
    if (-not $blockMatch.Success) {
        return @{}
    }

    $body = $blockMatch.Groups['body'].Value
    $entryPattern = "(?s)'(?<name>[^']+)'\s*=>\s*\[\s*'host'\s*=>\s*'(?<host>[^']*)',\s*'port'\s*=>\s*'(?<port>[^']*)',\s*'user'\s*=>\s*'(?<user>[^']*)',\s*'password'\s*=>\s*'(?<password>[^']*)'\s*,\s*\],?"
    $matches = [regex]::Matches($body, $entryPattern)

    $entries = [ordered]@{}
    foreach ($match in $matches) {
        $entries[$match.Groups['name'].Value] = [ordered]@{
            host = $match.Groups['host'].Value
            port = $match.Groups['port'].Value
            user = $match.Groups['user'].Value
            password = $match.Groups['password'].Value
        }
    }

    return $entries
}

function Save-DbConnectionEntry {
    param(
        [string]$ConfigPath,
        [string]$DatabaseName,
        [string]$HostName,
        [string]$PortNumber,
        [string]$UserName,
        [string]$Password
    )

    $entries = Get-DbConnectionEntriesFromConfig -ConfigPath $ConfigPath
    $entries[$DatabaseName] = [ordered]@{
        host = $HostName
        port = $PortNumber
        user = $UserName
        password = $Password
    }

    $lines = New-Object System.Collections.Generic.List[string]
    foreach ($entry in $entries.GetEnumerator()) {
        $lines.Add(("            {0} => [" -f (ConvertTo-PhpSingleQuotedString -Value $entry.Key)))
        $lines.Add(("                'host' => {0}," -f (ConvertTo-PhpSingleQuotedString -Value ([string]$entry.Value.host))))
        $lines.Add(("                'port' => {0}," -f (ConvertTo-PhpSingleQuotedString -Value ([string]$entry.Value.port))))
        $lines.Add(("                'user' => {0}," -f (ConvertTo-PhpSingleQuotedString -Value ([string]$entry.Value.user))))
        $lines.Add(("                'password' => {0}," -f (ConvertTo-PhpSingleQuotedString -Value ([string]$entry.Value.password))))
        $lines.Add('            ],')
    }

    $replacement = "// <db-connections>`r`n" + ($lines -join "`r`n") + "`r`n            // </db-connections>"
    $content = Get-Content -Path $ConfigPath -Raw -Encoding UTF8
    $pattern = '(?s)// <db-connections>\s*.*?\s*// </db-connections>'
    $match = [regex]::Match($content, $pattern)
    if (-not $match.Success) {
        throw "No se encontró el bloque <db-connections> en Config/DB_Config.php."
    }

    $updated = $content.Substring(0, $match.Index) + $replacement + $content.Substring($match.Index + $match.Length)
    [System.IO.File]::WriteAllText($ConfigPath, $updated, [System.Text.UTF8Encoding]::new($false))

    $savedEntries = Get-DbConnectionEntriesFromConfig -ConfigPath $ConfigPath
    if (-not $savedEntries.Contains($DatabaseName)) {
        throw "No se pudo guardar la conexion de la base '$DatabaseName' en Config/DB_Config.php."
    }
}

try {
    $scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
    $configPath = Join-Path (Split-Path -Parent $scriptDir) 'Config\DB_Config.php'
    $sqlFiles = @(Get-ChildItem -Path $scriptDir -Filter *.sql | Sort-Object Name)

    if (-not $sqlFiles) {
        throw "No se encontraron archivos .sql en la carpeta BD."
    }

    Write-Host ""
    Write-Host "=== Creacion de Base de Datos ===" -ForegroundColor Cyan
    Write-Host ""

    $selectedSql = $null
    if ($sqlFiles.Count -eq 1) {
        $selectedSql = $sqlFiles[0]
        Write-Host ("Se utilizara el archivo SQL: " + $selectedSql.Name) -ForegroundColor Green
    } else {
        Write-Host "Hay varios archivos SQL disponibles:" -ForegroundColor Yellow
        for ($i = 0; $i -lt $sqlFiles.Count; $i++) {
            Write-Host ("[{0}] {1}" -f ($i + 1), $sqlFiles[$i].Name)
        }

        while (-not $selectedSql) {
            $selection = Read-Host "Indique el numero del archivo SQL a utilizar"
            $index = 0
            if ([int]::TryParse($selection, [ref]$index) -and $index -ge 1 -and $index -le $sqlFiles.Count) {
                $selectedSql = $sqlFiles[$index - 1]
            } else {
                Write-Host "Seleccion invalida." -ForegroundColor Yellow
            }
        }
    }

    $hostName = Get-PhpDefineValue -PhpFilePath $configPath -ConstantName 'DB_HOST_DEFAULT'
    $portNumber = Get-PhpDefineValue -PhpFilePath $configPath -ConstantName 'DB_PORT_DEFAULT'
    $databaseNameDefault = Get-PhpDefineValue -PhpFilePath $configPath -ConstantName 'BD_DEFAULT'
    $rootUser = Get-PhpDefineValue -PhpFilePath $configPath -ConstantName 'DB_USER_ROOT'
    $rootPassword = Get-PhpDefineValue -PhpFilePath $configPath -ConstantName 'ROOT_PASS'
    $databaseAdminUser = Get-PhpDefineValue -PhpFilePath $configPath -ConstantName 'DB_APP_USER_DEFAULT'
    $databaseAdminPassword = Get-PhpDefineValue -PhpFilePath $configPath -ConstantName 'DB_APP_PASS_DEFAULT'
    $databaseAdminHost = 'localhost'

    if ([string]::IsNullOrWhiteSpace($hostName)) {
        $hostName = 'localhost'
    }
    if ([string]::IsNullOrWhiteSpace($portNumber)) {
        $portNumber = '3306'
    }
    if ([string]::IsNullOrWhiteSpace($databaseNameDefault)) {
        $databaseNameDefault = 'scantec_basic'
    }
    if ([string]::IsNullOrWhiteSpace($rootUser)) {
        $rootUser = $databaseAdminUser
    }
    if ($null -eq $rootPassword -or $rootPassword -eq '') {
        $rootPassword = $databaseAdminPassword
    }
    if ([string]::IsNullOrWhiteSpace($databaseAdminUser)) {
        throw "No se pudo leer DB_APP_USER_DEFAULT desde Config/DB_Config.php."
    }
    if ($null -eq $databaseAdminPassword) {
        throw "No se pudo leer DB_APP_PASS_DEFAULT desde Config/DB_Config.php."
    }
    if ([string]::IsNullOrWhiteSpace($rootUser)) {
        throw "No se pudo leer DB_USER_ROOT ni resolver un usuario de creacion desde Config/DB_Config.php."
    }
    if ($null -eq $rootPassword) {
        throw "No se pudo leer ROOT_PASS ni resolver una contrasena de creacion desde Config/DB_Config.php."
    }

    Write-Host "Credenciales tomadas desde Config/DB_Config.php:" -ForegroundColor Cyan
    Write-Host ("Host: " + $hostName)
    Write-Host ("Puerto: " + $portNumber)
    Write-Host ("Usuario de creacion: " + $rootUser)
    Write-Host ("Usuario de la aplicacion por defecto: " + $databaseAdminUser)
    Write-Host ""

    $databaseName = Read-RequiredText -Prompt 'Nombre de la base de datos a crear' -DefaultValue $databaseNameDefault
    $databaseAdminUser = Read-RequiredText -Prompt 'Usuario administrador MySQL para la nueva base' -DefaultValue $databaseAdminUser
    $databaseAdminHost = 'localhost'
    $databaseAdminPasswordSecure = Read-Host 'Contrasena para ese usuario administrador MySQL' -AsSecureString
    $databaseAdminPasswordInput = Get-PlainTextFromSecureString -SecureString $databaseAdminPasswordSecure
    if (-not [string]::IsNullOrWhiteSpace($databaseAdminPasswordInput)) {
        $databaseAdminPassword = $databaseAdminPasswordInput
    }

    Write-Host ""
    Write-Host "Administrador inicial del sistema:" -ForegroundColor Cyan
    $systemAdminNombre = Read-RequiredText -Prompt 'Nombre del administrador del sistema' -DefaultValue 'Administrador'
    $systemAdminUsuario = Read-RequiredText -Prompt 'Usuario del administrador del sistema' -DefaultValue 'admin'
    $systemAdminPasswordSecure = Read-Host 'Contrasena del administrador del sistema' -AsSecureString
    $systemAdminPassword = Get-PlainTextFromSecureString -SecureString $systemAdminPasswordSecure
    if ([string]::IsNullOrWhiteSpace($systemAdminPassword)) {
        throw "Debe ingresar una contrasena para el administrador del sistema."
    }
    $systemAdminEmail = Read-RequiredText -Prompt 'Correo del administrador del sistema' -DefaultValue 'admin@localhost'

    Assert-SafeIdentifier -Value $databaseName -Label 'Nombre de la base de datos'
    Assert-SafeIdentifier -Value $databaseAdminUser -Label 'Usuario administrador de la base'
    Assert-SafeIdentifier -Value $systemAdminUsuario -Label 'Usuario del administrador del sistema'

    $mysqlExe = Get-MysqlExecutable
    $phpExe = Get-PhpExecutable
    Write-Host ("mysql.exe encontrado en: " + $mysqlExe) -ForegroundColor DarkGray
    Write-Host ("php.exe encontrado en: " + $phpExe) -ForegroundColor DarkGray

    $databaseNameSql = Quote-MysqlString -Value $databaseName
    $dbExistsOutput = Invoke-MysqlCommand `
        -MysqlExe $mysqlExe `
        -HostName $hostName `
        -PortNumber $portNumber `
        -UserName $rootUser `
        -Password $rootPassword `
        -Sql "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = $databaseNameSql;"
    $dbExists = ($dbExistsOutput | Out-String).Trim()

    if (-not [string]::IsNullOrWhiteSpace($dbExists)) {
        Write-Host ""
        Write-Host ("La base de datos '" + $databaseName + "' ya existe.") -ForegroundColor Yellow
        $existingChoice = Read-Choice -Prompt 'Escriba R para reemplazarla o C para cancelar' -ValidValues @('R', 'C')
        if ($existingChoice -eq 'C') {
            Write-Host "Operacion cancelada por el usuario." -ForegroundColor Yellow
            exit 0
        }

        $dropDatabaseSql = "DROP DATABASE IF EXISTS {0};" -f (Quote-MysqlIdentifier -Value $databaseName)
        Invoke-MysqlCommand `
            -MysqlExe $mysqlExe `
            -HostName $hostName `
            -PortNumber $portNumber `
            -UserName $rootUser `
            -Password $rootPassword `
            -Sql $dropDatabaseSql | Out-Null
        Write-Host ("Base de datos '" + $databaseName + "' eliminada correctamente.") -ForegroundColor Yellow
    }

    $createDatabaseSql = @"
CREATE DATABASE IF NOT EXISTS $(Quote-MysqlIdentifier -Value $databaseName)
CHARACTER SET utf8mb4
COLLATE utf8mb4_0900_ai_ci;
"@
    Invoke-MysqlCommand `
        -MysqlExe $mysqlExe `
        -HostName $hostName `
        -PortNumber $portNumber `
        -UserName $rootUser `
        -Password $rootPassword `
        -Sql $createDatabaseSql | Out-Null

    $userManagementApplied = $false
    if ($databaseAdminUser -eq $rootUser -and $databaseAdminHost -eq 'localhost') {
        Write-Host "Se reutilizaran las credenciales de Config/DB_Config.php para acceder a la nueva base." -ForegroundColor DarkGray
        $grantExistingUserSql = @"
GRANT ALL PRIVILEGES ON $(Quote-MysqlIdentifier -Value $databaseName).* TO $(Quote-MysqlIdentifier -Value $databaseAdminUser)@$(Quote-MysqlString -Value $databaseAdminHost);
FLUSH PRIVILEGES;
"@
        try {
            Invoke-MysqlCommand `
                -MysqlExe $mysqlExe `
                -HostName $hostName `
                -PortNumber $portNumber `
                -UserName $rootUser `
                -Password $rootPassword `
                -Sql $grantExistingUserSql | Out-Null
            $userManagementApplied = $true
        } catch {
            Write-Host "No se pudieron reasignar privilegios al usuario existente. Se continuara con la importacion igualmente." -ForegroundColor Yellow
        }
    } else {
        $createUserSql = @"
CREATE USER IF NOT EXISTS $(Quote-MysqlIdentifier -Value $databaseAdminUser)@$(Quote-MysqlString -Value $databaseAdminHost)
IDENTIFIED BY $(Quote-MysqlString -Value $databaseAdminPassword);
ALTER USER $(Quote-MysqlIdentifier -Value $databaseAdminUser)@$(Quote-MysqlString -Value $databaseAdminHost)
IDENTIFIED BY $(Quote-MysqlString -Value $databaseAdminPassword);
GRANT ALL PRIVILEGES ON $(Quote-MysqlIdentifier -Value $databaseName).* TO $(Quote-MysqlIdentifier -Value $databaseAdminUser)@$(Quote-MysqlString -Value $databaseAdminHost);
FLUSH PRIVILEGES;
"@
        try {
            Invoke-MysqlCommand `
                -MysqlExe $mysqlExe `
                -HostName $hostName `
                -PortNumber $portNumber `
                -UserName $rootUser `
                -Password $rootPassword `
                -Sql $createUserSql | Out-Null
            $userManagementApplied = $true
        } catch {
            $userError = $_.Exception.Message
            if ($userError -match 'CREATE USER privilege' -or $userError -match 'Access denied') {
                Write-Host "No hay privilegios para crear/actualizar usuarios MySQL. Se continuara usando las credenciales actuales." -ForegroundColor Yellow
            } else {
                throw
            }
        }
    }

    $sqlContent = Get-Content -Path $selectedSql.FullName -Raw -Encoding UTF8
    $sqlContent = [regex]::Replace($sqlContent, '(?im)^\s*CREATE\s+DATABASE\s+IF\s+NOT\s+EXISTS\s+`?[^`\s;]+`?.*?;\s*$', '')
    $sqlContent = [regex]::Replace($sqlContent, '(?im)^\s*USE\s+`?[^`\s;]+`?\s*;\s*$', '')

    $prefixSql = @"
USE $(Quote-MysqlIdentifier -Value $databaseName);

"@

    $tempSqlPath = Join-Path $env:TEMP ("crear_bd_" + [guid]::NewGuid().ToString('N') + ".sql")
    [System.IO.File]::WriteAllText($tempSqlPath, $prefixSql + $sqlContent, [System.Text.UTF8Encoding]::new($false))

    try {
        try {
            Import-SqlFile `
                -MysqlExe $mysqlExe `
                -HostName $hostName `
                -PortNumber $portNumber `
                -UserName $rootUser `
                -Password $rootPassword `
                -SqlFilePath $tempSqlPath
        } catch {
            $importError = $_.Exception.Message
            if ($importError -match 'ERROR 1419' -or $importError -match 'SUPER privilege' -or $importError -match 'log_bin_trust_function_creators') {
                Write-Host "MySQL rechazo la creacion de triggers por falta de privilegios. Se reintentara sin triggers." -ForegroundColor Yellow
                $sqlWithoutTriggers = Remove-TriggerBlocks -SqlContent ($prefixSql + $sqlContent)
                [System.IO.File]::WriteAllText($tempSqlPath, $sqlWithoutTriggers, [System.Text.UTF8Encoding]::new($false))
                Import-SqlFile `
                    -MysqlExe $mysqlExe `
                    -HostName $hostName `
                    -PortNumber $portNumber `
                    -UserName $rootUser `
                    -Password $rootPassword `
                    -SqlFilePath $tempSqlPath
                Write-Host "La base se importo sin triggers. Si luego queres auditoria automatica, habra que crearlos con un usuario de mayor privilegio." -ForegroundColor Yellow
            } else {
                throw
            }
        }
    } finally {
        if (Test-Path $tempSqlPath) {
            Remove-Item $tempSqlPath -Force -ErrorAction SilentlyContinue
        }
    }

    $systemAdminHash = Get-PhpBcryptHash -PhpExe $phpExe -PlainPassword $systemAdminPassword
    $databaseIdentifier = Quote-MysqlIdentifier -Value $databaseName
    $adminRoleDescription = Quote-MysqlString -Value 'Administrador Scantec'
    $defaultGroupDescription = Quote-MysqlString -Value 'GENERAL'
    $activeStatus = Quote-MysqlString -Value 'ACTIVO'
    $systemAdminNombreSql = Quote-MysqlString -Value $systemAdminNombre
    $systemAdminUsuarioSql = Quote-MysqlString -Value $systemAdminUsuario
    $systemAdminHashSql = Quote-MysqlString -Value $systemAdminHash
    $systemAdminEmailSql = Quote-MysqlString -Value $systemAdminEmail
    $sourceLocalSql = Quote-MysqlString -Value 'scantec'

    $seedSystemAdminSql = @"
INSERT INTO $databaseIdentifier.roles (id_rol, descripcion)
SELECT 1, $adminRoleDescription
WHERE NOT EXISTS (SELECT 1 FROM $databaseIdentifier.roles WHERE id_rol = 1);

INSERT INTO $databaseIdentifier.usu_grupo (id_grupo, descripcion, estado)
SELECT 1, $defaultGroupDescription, $activeStatus
WHERE NOT EXISTS (SELECT 1 FROM $databaseIdentifier.usu_grupo WHERE id_grupo = 1);

INSERT INTO $databaseIdentifier.usuarios (nombre, departamento, usuario, clave, id_rol, estado_usuario, id_grupo, clave_actualizacion, email, fuente_registro)
SELECT $systemAdminNombreSql, 'SISTEMAS', $systemAdminUsuarioSql, $systemAdminHashSql, 1, $activeStatus, 1, NOW(), $systemAdminEmailSql, $sourceLocalSql
WHERE NOT EXISTS (
    SELECT 1
    FROM $databaseIdentifier.usuarios
    WHERE usuario = $systemAdminUsuarioSql
);
"@
    Invoke-MysqlCommand `
        -MysqlExe $mysqlExe `
        -HostName $hostName `
        -PortNumber $portNumber `
        -UserName $rootUser `
        -Password $rootPassword `
        -Sql $seedSystemAdminSql | Out-Null

    $testConnectionSql = "USE {0}; SELECT DATABASE();" -f $databaseIdentifier
    Invoke-MysqlCommand `
        -MysqlExe $mysqlExe `
        -HostName $hostName `
        -PortNumber $portNumber `
        -UserName $databaseAdminUser `
        -Password $databaseAdminPassword `
        -Sql $testConnectionSql | Out-Null

    Save-DbConnectionEntry `
        -ConfigPath $configPath `
        -DatabaseName $databaseName `
        -HostName $hostName `
        -PortNumber $portNumber `
        -UserName $databaseAdminUser `
        -Password $databaseAdminPassword

    Write-Host ""
    Write-Host ("Base de datos '" + $databaseName + "' creada correctamente usando '" + $selectedSql.Name + "'.") -ForegroundColor Green
    if ($userManagementApplied) {
        Write-Host ("Usuario administrador asignado: " + $databaseAdminUser + "@" + $databaseAdminHost) -ForegroundColor Green
    } else {
        Write-Host ("Base creada usando las credenciales actuales: " + $rootUser + "@localhost") -ForegroundColor Yellow
    }
    Write-Host ("Administrador Scantec creado: " + $systemAdminUsuario) -ForegroundColor Green
    Write-Host ("Conexion verificada y registrada en Config/DB_Config.php para la base '" + $databaseName + "'.") -ForegroundColor Green
    Write-Host "Credenciales tomadas desde Config/DB_Config.php." -ForegroundColor Green
    Write-Host ""
} catch {
    Write-Host ""
    Write-Host ("Error: " + $_.Exception.Message) -ForegroundColor Red
    Write-Host ""
    exit 1
}

