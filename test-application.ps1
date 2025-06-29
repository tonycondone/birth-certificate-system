# Test Application Script
Write-Host "========================================" -ForegroundColor Green
Write-Host "Testing Digital Birth Certificate System" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Test 1: Check if server is running
Write-Host "1. Checking if server is running..." -ForegroundColor Cyan
$serverRunning = netstat -an | findstr ":8000" | findstr "LISTENING"
if ($serverRunning) {
    Write-Host "   ✓ Server is running on port 8000" -ForegroundColor Green
} else {
    Write-Host "   ✗ Server is not running" -ForegroundColor Red
    exit 1
}

# Test 2: Test home page
Write-Host "2. Testing home page..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/home" -UseBasicParsing -TimeoutSec 10
    if ($response.StatusCode -eq 200) {
        Write-Host "   ✓ Home page loads successfully" -ForegroundColor Green
        if ($response.Content -match "Digital Birth Certificate") {
            Write-Host "   ✓ Page content is correct" -ForegroundColor Green
        } else {
            Write-Host "   ⚠ Page content may be incomplete" -ForegroundColor Yellow
        }
    } else {
        Write-Host "   ✗ Home page returned status: $($response.StatusCode)" -ForegroundColor Red
    }
} catch {
    Write-Host "   ✗ Failed to load home page: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Test login page
Write-Host "3. Testing login page..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/login" -UseBasicParsing -TimeoutSec 10
    if ($response.StatusCode -eq 200) {
        Write-Host "   ✓ Login page loads successfully" -ForegroundColor Green
    } else {
        Write-Host "   ✗ Login page returned status: $($response.StatusCode)" -ForegroundColor Red
    }
} catch {
    Write-Host "   ✗ Failed to load login page: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 4: Test 404 error page
Write-Host "4. Testing 404 error page..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/nonexistent-page" -UseBasicParsing -TimeoutSec 10
    if ($response.StatusCode -eq 404) {
        Write-Host "   ✓ 404 error page works correctly" -ForegroundColor Green
    } else {
        Write-Host "   ⚠ 404 page returned status: $($response.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "   ⚠ 404 test failed: $($_.Exception.Message)" -ForegroundColor Yellow
}

# Test 5: Check PHP extensions
Write-Host "5. Checking PHP extensions..." -ForegroundColor Cyan
$requiredExtensions = @("pdo", "json", "xml", "tokenizer", "mbstring", "fileinfo")
$loadedExtensions = php -m 2>$null | Where-Object { $_ -match '^[a-zA-Z]' }

foreach ($ext in $requiredExtensions) {
    if ($loadedExtensions -contains $ext) {
        Write-Host "   ✓ $ext extension loaded" -ForegroundColor Green
    } else {
        Write-Host "   ✗ $ext extension not loaded" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Test Results Summary" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

Write-Host "Application Status: " -NoNewline
if ($serverRunning -and $response.StatusCode -eq 200) {
    Write-Host "✅ WORKING" -ForegroundColor Green
} else {
    Write-Host "❌ ISSUES DETECTED" -ForegroundColor Red
}

Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Open http://localhost:8000 in your browser" -ForegroundColor White
Write-Host "2. Test the registration and login functionality" -ForegroundColor White
Write-Host "3. Try accessing restricted pages to test error handling" -ForegroundColor White
Write-Host "4. Check that all user roles work correctly" -ForegroundColor White

Write-Host ""
Write-Host "If you see any issues, check the server logs above." -ForegroundColor Cyan 