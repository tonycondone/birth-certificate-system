# Create Clean PHP INI Script
Write-Host "Creating clean php.ini file..." -ForegroundColor Green

$phpIniPath = "C:\php\php.ini"
$backupPath = "$phpIniPath.backup.$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Create backup
Copy-Item $phpIniPath $backupPath
Write-Host "Backup created: $backupPath" -ForegroundColor Yellow

# Get the original content
$originalContent = Get-Content $phpIniPath

# Create new content with unique extensions
$newContent = @()

# Add all non-extension lines first
foreach ($line in $originalContent) {
    if ($line -notmatch '^extension=') {
        $newContent += $line
    }
}

# Add unique extensions
$extensions = @(
    "extension=curl",
    "extension=fileinfo", 
    "extension=gd",
    "extension=json",
    "extension=mbstring",
    "extension=mysqli",
    "extension=openssl",
    "extension=pdo",
    "extension=pdo_mysql",
    "extension=tokenizer",
    "extension=xml"
)

# Find where to insert extensions (after the extension section comment)
$insertIndex = 0
for ($i = 0; $i -lt $newContent.Length; $i++) {
    if ($newContent[$i] -match '^; Extensions$' -or $newContent[$i] -match '^;extension=') {
        $insertIndex = $i
        break
    }
}

# Insert extensions
$newContent = $newContent[0..($insertIndex-1)] + $extensions + $newContent[$insertIndex..($newContent.Length-1)]

# Write the new content
Set-Content $phpIniPath $newContent

Write-Host "Clean php.ini created successfully!" -ForegroundColor Green
Write-Host ""

# Test PHP
Write-Host "Testing PHP configuration..." -ForegroundColor Cyan
php -m | Where-Object { $_ -match '^(pdo|json|xml|tokenizer|mbstring|fileinfo)' } | ForEach-Object {
    Write-Host "  ✓ $_" -ForegroundColor Green
}

Write-Host ""
Write-Host "PHP configuration updated successfully!" -ForegroundColor Green
Write-Host "Please restart your web server to apply changes." -ForegroundColor Yellow 