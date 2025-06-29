# Fix PHP INI Duplicate Entries Script
Write-Host "========================================" -ForegroundColor Green
Write-Host "PHP INI Duplicate Entries Fix Script" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

$phpIniPath = "C:\php\php.ini"

# Check if php.ini exists
if (-not (Test-Path $phpIniPath)) {
    Write-Host "ERROR: php.ini not found at $phpIniPath" -ForegroundColor Red
    exit 1
}

Write-Host "Found php.ini at: $phpIniPath" -ForegroundColor Yellow
Write-Host ""

# Create backup
$backupPath = "$phpIniPath.backup.$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Write-Host "Creating backup: $backupPath" -ForegroundColor Yellow
Copy-Item $phpIniPath $backupPath

# Read the file
$content = Get-Content $phpIniPath

# Find duplicate extensions
$extensions = @{}
$duplicates = @()

for ($i = 0; $i -lt $content.Length; $i++) {
    $line = $content[$i]
    if ($line -match '^extension=([^;]+)$') {
        $extName = $matches[1].Trim()
        if ($extensions.ContainsKey($extName)) {
            $duplicates += @{
                Name = $extName
                LineNumber = $i + 1
                FirstOccurrence = $extensions[$extName]
            }
        } else {
            $extensions[$extName] = $i + 1
        }
    }
}

if ($duplicates.Count -eq 0) {
    Write-Host "No duplicate extensions found!" -ForegroundColor Green
} else {
    Write-Host "Found duplicate extensions:" -ForegroundColor Red
    foreach ($dup in $duplicates) {
        Write-Host "  - $($dup.Name): First at line $($dup.FirstOccurrence), Duplicate at line $($dup.LineNumber)" -ForegroundColor Red
    }
    Write-Host ""
    
    # Remove duplicates (keep the first occurrence)
    $newContent = @()
    $skipLines = $duplicates | ForEach-Object { $_.LineNumber - 1 }
    
    for ($i = 0; $i -lt $content.Length; $i++) {
        if ($i -notin $skipLines) {
            $newContent += $content[$i]
        } else {
            Write-Host "Removing duplicate: $($content[$i])" -ForegroundColor Yellow
        }
    }
    
    # Write the cleaned content back
    Set-Content $phpIniPath $newContent
    Write-Host ""
    Write-Host "Duplicates removed successfully!" -ForegroundColor Green
}

Write-Host ""
Write-Host "Current extension status:" -ForegroundColor Cyan
php -m | Where-Object { $_ -match '^(pdo|json|xml|tokenizer|mbstring|fileinfo)' } | ForEach-Object {
    Write-Host "  ✓ $_" -ForegroundColor Green
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Fix completed! Please restart your web server." -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green 