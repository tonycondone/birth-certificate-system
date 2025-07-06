<?php
require_once 'vendor/autoload.php';

class ComprehensiveSystemAnalyzer {
    private $components = [
        'Controllers' => 'app/Controllers',
        'Services' => 'app/Services',
        'Repositories' => 'app/Repositories',
        'Models' => 'app/Models',
        'Middleware' => 'app/Middleware'
    ];

    private $missingFeatures = [];
    private $potentialBugs = [];

    public function analyze() {
        echo "🔍 Comprehensive Birth Certificate System Analysis\n";
        echo "==============================================\n\n";

        $this->analyzeArchitecture();
        $this->identifyMissingFeatures();
        $this->detectPotentialBugs();
        $this->generateReport();
    }

    private function analyzeArchitecture() {
        echo "📂 System Architecture:\n";
        foreach ($this->components as $type => $path) {
            echo "  $type:\n";
            $this->listComponentFiles($path);
        }
    }

    private function listComponentFiles($path) {
        if (!is_dir($path)) {
            echo "    ❌ Directory not found: $path\n";
            return;
        }

        $files = glob("$path/*.php");
        foreach ($files as $file) {
            $className = basename($file, '.php');
            echo "    • $className\n";
        }
    }

    private function identifyMissingFeatures() {
        echo "\n🚧 Missing Features Analysis:\n";
        
        $this->checkAuthenticationFeatures();
        $this->checkApplicationWorkflow();
        $this->checkReportingCapabilities();
        $this->checkComplianceFeatures();

        if (!empty($this->missingFeatures)) {
            foreach ($this->missingFeatures as $category => $features) {
                echo "  $category:\n";
                foreach ($features as $feature) {
                    echo "    ❌ $feature\n";
                }
            }
        }
    }

    private function checkAuthenticationFeatures() {
        $authFiles = glob('app/Services/Auth*');
        $missingAuth = [];

        if (!$this->fileExists('app/Services/TwoFactorAuthService.php')) {
            $missingAuth[] = 'Two-Factor Authentication';
        }
        if (!$this->fileExists('app/Services/PasswordResetService.php')) {
            $missingAuth[] = 'Password Reset Mechanism';
        }
        if (!$this->fileExists('app/Middleware/RoleBasedAccessMiddleware.php')) {
            $missingAuth[] = 'Role-Based Access Control';
        }

        if (!empty($missingAuth)) {
            $this->missingFeatures['Authentication'] = $missingAuth;
        }
    }

    private function checkApplicationWorkflow() {
        $missingWorkflow = [];

        if (!$this->fileExists('app/Services/ApplicationTrackingService.php')) {
            $missingWorkflow[] = 'Comprehensive Application Tracking';
        }
        if (!$this->fileExists('app/Services/PaymentService.php')) {
            $missingWorkflow[] = 'Integrated Payment Processing';
        }
        if (!$this->fileExists('app/Services/NotificationService.php')) {
            $missingWorkflow[] = 'Status Notification System';
        }

        if (!empty($missingWorkflow)) {
            $this->missingFeatures['Application Workflow'] = $missingWorkflow;
        }
    }

    private function checkReportingCapabilities() {
        $missingReporting = [];

        if (!$this->fileExists('app/Services/ReportGenerationService.php')) {
            $missingReporting[] = 'Advanced Reporting Tools';
        }
        if (!$this->fileExists('app/Services/StatisticsService.php')) {
            $missingReporting[] = 'System-wide Statistics';
        }

        if (!empty($missingReporting)) {
            $this->missingFeatures['Reporting'] = $missingReporting;
        }
    }

    private function checkComplianceFeatures() {
        $missingCompliance = [];

        if (!$this->fileExists('app/Services/ComplianceService.php')) {
            $missingCompliance[] = 'GDPR/HIPAA Compliance Tracking';
        }
        if (!$this->fileExists('app/Services/AuditTrailService.php')) {
            $missingCompliance[] = 'Comprehensive Audit Logging';
        }

        if (!empty($missingCompliance)) {
            $this->missingFeatures['Compliance'] = $missingCompliance;
        }
    }

    private function detectPotentialBugs() {
        echo "\n🐛 Potential Bugs Detection:\n";
        
        $this->checkSecurityVulnerabilities();
        $this->checkPerformanceIssues();
        $this->checkDataIntegrity();

        if (!empty($this->potentialBugs)) {
            foreach ($this->potentialBugs as $category => $bugs) {
                echo "  $category:\n";
                foreach ($bugs as $bug) {
                    echo "    ⚠️ $bug\n";
                }
            }
        }
    }

    private function checkSecurityVulnerabilities() {
        $securityIssues = [];

        $files = glob('app/**/*.php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            
            if (preg_match('/\$_(?:GET|POST|REQUEST)\[/', $content)) {
                $securityIssues[] = "Potential XSS in " . basename($file);
            }
            
            if (preg_match('/mysql_/', $content)) {
                $securityIssues[] = "Deprecated MySQL extension in " . basename($file);
            }
        }

        if (!empty($securityIssues)) {
            $this->potentialBugs['Security'] = $securityIssues;
        }
    }

    private function checkPerformanceIssues() {
        $performanceIssues = [];

        // Check for potential N+1 query problems
        $repositories = glob('app/Repositories/*.php');
        foreach ($repositories as $repo) {
            $content = file_get_contents($repo);
            if (preg_match('/->find\(\)\s*->\s*load/', $content)) {
                $performanceIssues[] = "Potential N+1 query in " . basename($repo);
            }
        }

        if (!empty($performanceIssues)) {
            $this->potentialBugs['Performance'] = $performanceIssues;
        }
    }

    private function checkDataIntegrity() {
        $dataIntegrityIssues = [];

        // Check for missing validation
        $controllers = glob('app/Controllers/*.php');
        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);
            if (!preg_match('/validate\(/', $content)) {
                $dataIntegrityIssues[] = "Missing input validation in " . basename($controller);
            }
        }

        if (!empty($dataIntegrityIssues)) {
            $this->potentialBugs['Data Integrity'] = $dataIntegrityIssues;
        }
    }

    private function fileExists($path) {
        return file_exists($path);
    }

    private function generateReport() {
        echo "\n📋 System Health Report\n";
        echo "---------------------\n";
        
        echo "Missing Features: " . 
            (empty($this->missingFeatures) ? "✅ None" : count($this->missingFeatures)) . "\n";
        
        echo "Potential Bugs: " . 
            (empty($this->potentialBugs) ? "✅ None" : count($this->potentialBugs)) . "\n";
    }
}

// Run the analysis
$analyzer = new ComprehensiveSystemAnalyzer();
$analyzer->analyze(); 