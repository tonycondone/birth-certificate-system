<?php
require_once 'vendor/autoload.php';

class SystemAnalyzer {
    private $components = [
        'Controllers' => 'app/Controllers',
        'Services' => 'app/Services',
        'Repositories' => 'app/Repositories',
        'Models' => 'app/Models',
        'Middleware' => 'app/Middleware'
    ];

    public function analyzeSystem() {
        echo "🔍 Comprehensive Birth Certificate System Analysis\n";
        echo "==============================================\n\n";

        foreach ($this->components as $componentType => $path) {
            echo "📁 $componentType Analysis:\n";
            $this->analyzeDirectory($path);
            echo "\n";
        }

        $this->performSecurityScan();
        $this->identifyMissingFeatures();
    }

    private function analyzeDirectory($path) {
        if (!is_dir($path)) {
            echo "  ❌ Directory not found: $path\n";
            return;
        }

        $files = glob("$path/*.php");
        echo "  Total Files: " . count($files) . "\n";
        
        foreach ($files as $file) {
            $className = basename($file, '.php');
            echo "  • $className\n";
        }
    }

    private function performSecurityScan() {
        echo "🛡️ Security Vulnerability Scan:\n";
        $securityChecks = [
            'SQL Injection Risks' => $this->findSqlInjectionRisks(),
            'XSS Vulnerabilities' => $this->findXssVulnerabilities(),
            'Authentication Weaknesses' => $this->findAuthenticationIssues()
        ];

        foreach ($securityChecks as $category => $issues) {
            echo "  $category:\n";
            if (empty($issues)) {
                echo "    ✅ No issues found\n";
            } else {
                foreach ($issues as $issue) {
                    echo "    ❌ $issue\n";
                }
            }
        }
    }

    private function findSqlInjectionRisks() {
        $risks = [];
        $files = glob('app/**/*.php', GLOB_BRACE);
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/\$[a-zA-Z_]+\s*=\s*"SELECT.*\$/', $content)) {
                $risks[] = "Potential SQL injection in " . basename($file);
            }
        }
        
        return $risks;
    }

    private function findXssVulnerabilities() {
        $vulnerabilities = [];
        $files = glob('app/**/*.php', GLOB_BRACE);
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/echo\s*\$_(?:GET|POST|REQUEST)\[/', $content)) {
                $vulnerabilities[] = "Potential XSS vulnerability in " . basename($file);
            }
        }
        
        return $vulnerabilities;
    }

    private function findAuthenticationIssues() {
        $issues = [];
        $files = glob('app/**/*.php', GLOB_BRACE);
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/password_hash\(.*\'md5\'/', $content)) {
                $issues[] = "Weak password hashing in " . basename($file);
            }
        }
        
        return $issues;
    }

    private function identifyMissingFeatures() {
        echo "🚧 Missing Features Analysis:\n";
        $missingFeatures = [
            'Advanced Audit Logging' => false,
            'Two-Factor Authentication' => false,
            'Comprehensive Error Handling' => false,
            'Rate Limiting' => false,
            'Comprehensive Input Validation' => false
        ];

        // Check for specific implementations
        $missingFeatures['Advanced Audit Logging'] = !$this->checkAuditLogging();
        $missingFeatures['Two-Factor Authentication'] = !$this->checkTwoFactorAuth();
        $missingFeatures['Comprehensive Error Handling'] = !$this->checkErrorHandling();
        $missingFeatures['Rate Limiting'] = !$this->checkRateLimiting();
        $missingFeatures['Comprehensive Input Validation'] = !$this->checkInputValidation();

        foreach ($missingFeatures as $feature => $isMissing) {
            echo "  " . ($isMissing ? "❌" : "✅") . " $feature\n";
        }
    }

    private function checkAuditLogging() {
        return file_exists('app/Services/LoggingService.php');
    }

    private function checkTwoFactorAuth() {
        return file_exists('app/Services/TwoFactorAuthService.php');
    }

    private function checkErrorHandling() {
        return file_exists('app/Handlers/ErrorHandler.php');
    }

    private function checkRateLimiting() {
        return file_exists('app/Middleware/RateLimitMiddleware.php');
    }

    private function checkInputValidation() {
        return file_exists('app/Validators/InputValidator.php');
    }
}

// Run the analysis
$analyzer = new SystemAnalyzer();
$analyzer->analyzeSystem(); 