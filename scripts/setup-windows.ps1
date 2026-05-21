param(
    [string] $DbHost = "127.0.0.1",
    [string] $DbPort = "5432",
    [string] $DbDatabase = "restauracja",
    [string] $DbUsername = "postgres",
    [AllowNull()][string] $DbPassword = $null,
    [switch] $Fresh,
    [switch] $SkipSeed,
    [switch] $SkipInstall,
    [switch] $SkipBuild
)

$ErrorActionPreference = "Stop"

function Write-Step {
    param([string] $Message)

    Write-Host ""
    Write-Host "==> $Message" -ForegroundColor Cyan
}

function Invoke-Step {
    param(
        [string] $Message,
        [scriptblock] $Command
    )

    Write-Step $Message
    $global:LASTEXITCODE = 0
    & $Command

    if ($LASTEXITCODE -ne 0) {
        throw "Step failed: $Message"
    }
}

function Test-CommandExists {
    param([string] $Command)

    return $null -ne (Get-Command $Command -ErrorAction SilentlyContinue)
}

function Set-EnvValue {
    param(
        [string] $Path,
        [string] $Key,
        [string] $Value
    )

    $lines = @()

    if (Test-Path $Path) {
        $lines = Get-Content -LiteralPath $Path
    }

    $escapedKey = [regex]::Escape($Key)
    $newLine = "$Key=$Value"
    $found = $false

    $updated = foreach ($line in $lines) {
        if ($line -match "^\s*$escapedKey=") {
            $found = $true
            $newLine
        } else {
            $line
        }
    }

    if (-not $found) {
        $updated += $newLine
    }

    Set-Content -LiteralPath $Path -Value $updated -Encoding UTF8
}

function Get-EnvValue {
    param(
        [string] $Path,
        [string] $Key
    )

    if (-not (Test-Path $Path)) {
        return $null
    }

    $escapedKey = [regex]::Escape($Key)
    $line = Get-Content -LiteralPath $Path | Where-Object { $_ -match "^\s*$escapedKey=" } | Select-Object -First 1

    if (-not $line) {
        return $null
    }

    return ($line -replace "^\s*$escapedKey=", "").Trim()
}

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
Set-Location $projectRoot

if (-not (Test-Path "artisan")) {
    throw "File artisan was not found. Run this script from the project directory or from scripts."
}

Write-Host "Restauracja 2026 setup" -ForegroundColor Green
Write-Host "Project directory: $projectRoot"

foreach ($command in @("php", "composer", "npm")) {
    if (-not (Test-CommandExists $command)) {
        throw "Missing command '$command'. Install the required tool and run this script again."
    }
}

if (-not $SkipInstall) {
    Invoke-Step "Installing PHP dependencies" {
        composer install
    }

    Invoke-Step "Installing frontend dependencies" {
        npm install
    }
}

if (-not (Test-Path ".env")) {
    Invoke-Step "Creating .env from .env.example" {
        Copy-Item ".env.example" ".env"
    }
}

Invoke-Step "Updating .env configuration" {
    Set-EnvValue ".env" "DB_CONNECTION" "pgsql"
    Set-EnvValue ".env" "DB_HOST" $DbHost
    Set-EnvValue ".env" "DB_PORT" $DbPort
    Set-EnvValue ".env" "DB_DATABASE" $DbDatabase
    Set-EnvValue ".env" "DB_USERNAME" $DbUsername

    if ($null -ne $DbPassword) {
        Set-EnvValue ".env" "DB_PASSWORD" $DbPassword
    }

    Set-EnvValue ".env" "SESSION_DRIVER" "database"
    Set-EnvValue ".env" "CACHE_STORE" "database"
    Set-EnvValue ".env" "QUEUE_CONNECTION" "database"
}

$appKey = Get-EnvValue ".env" "APP_KEY"

if ([string]::IsNullOrWhiteSpace($appKey)) {
    Invoke-Step "Generating APP_KEY" {
        php artisan key:generate
    }
}

Invoke-Step "Clearing Laravel configuration" {
    php artisan config:clear
}

if ($Fresh) {
    if ($SkipSeed) {
        Invoke-Step "Resetting database without seeders" {
            php artisan migrate:fresh
        }
    } else {
        Invoke-Step "Resetting database with seeders" {
            php artisan migrate:fresh --seed
        }
    }
} else {
    Invoke-Step "Running migrations" {
        php artisan migrate
    }

    if (-not $SkipSeed) {
        Invoke-Step "Running seeders" {
            php artisan db:seed
        }
    }
}

Invoke-Step "Clearing application cache" {
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
}

if (-not $SkipBuild) {
    Invoke-Step "Building frontend assets" {
        npm run build
    }
}

Write-Host ""
Write-Host "Setup completed." -ForegroundColor Green
Write-Host "Start the application with:"
Write-Host "php artisan serve" -ForegroundColor Yellow
Write-Host ""
Write-Host "Test accounts after running seeders:"
Write-Host "admin@example.com / password"
Write-Host "manager@example.com / password"
Write-Host "kelner@example.com / password"
Write-Host "kuchnia@example.com / password"
Write-Host "bar@example.com / password"
