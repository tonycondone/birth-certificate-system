<?php
namespace App\Services;

use PDO;
use Exception;
use App\Database\Database;
use App\Repositories\BirthApplicationRepository;
use App\Repositories\CertificateRepository;
use App\Repositories\UserRepository;

class DependencyContainer
{
    private static ?self $instance = null;
    private $services = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getDatabase(): PDO
    {
        if (!isset($this->services['database'])) {
            try {
                $this->services['database'] = Database::getConnection();
            } catch (Exception $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                throw $e;
            }
        }
        return $this->services['database'];
    }

    public function getBirthApplicationRepository(): BirthApplicationRepository
    {
        if (!isset($this->services['birth_application_repository'])) {
            try {
                // Explicitly check class existence
                if (!class_exists(BirthApplicationRepository::class)) {
                    throw new Exception("BirthApplicationRepository class not found. Check autoloading.");
                }

                $this->services['birth_application_repository'] = new BirthApplicationRepository(
                    $this->getDatabase()
                );
            } catch (Exception $e) {
                error_log("Birth Application Repository Creation Error: " . $e->getMessage());
                throw $e;
            }
        }
        return $this->services['birth_application_repository'];
    }

    public function getCertificateRepository(): CertificateRepository
    {
        if (!isset($this->services['certificate_repository'])) {
            try {
                // Explicitly check class existence
                if (!class_exists(CertificateRepository::class)) {
                    throw new Exception("CertificateRepository class not found. Check autoloading.");
                }

                $this->services['certificate_repository'] = new CertificateRepository(
                    $this->getDatabase()
                );
            } catch (Exception $e) {
                error_log("Certificate Repository Creation Error: " . $e->getMessage());
                throw $e;
            }
        }
        return $this->services['certificate_repository'];
    }

    public function getUserRepository(): UserRepository
    {
        if (!isset($this->services['user_repository'])) {
            try {
                // Explicitly check class existence
                if (!class_exists(UserRepository::class)) {
                    throw new Exception("UserRepository class not found. Check autoloading.");
                }

                $this->services['user_repository'] = new UserRepository(
                    $this->getDatabase()
                );
            } catch (Exception $e) {
                error_log("User Repository Creation Error: " . $e->getMessage());
                throw $e;
            }
        }
        return $this->services['user_repository'];
    }

    public function getNotificationService(): NotificationService
    {
        if (!isset($this->services['notification_service'])) {
            try {
                $this->services['notification_service'] = new NotificationService(
                    $this->getDatabase()
                );
            } catch (Exception $e) {
                error_log("Notification Service Creation Error: " . $e->getMessage());
                throw $e;
            }
        }
        return $this->services['notification_service'];
    }

    public function getCertificateVerificationService(): CertificateVerificationService
    {
        if (!isset($this->services['certificate_verification_service'])) {
            try {
                $this->services['certificate_verification_service'] = new CertificateVerificationService(
                    $this->getDatabase(),
                    $this->getBirthApplicationRepository(),
                    $this->getCertificateRepository(),
                    $this->getUserRepository(),
                    $this->getNotificationService()
                );
            } catch (Exception $e) {
                error_log("Certificate Verification Service Creation Error: " . $e->getMessage());
                throw $e;
            }
        }
        return $this->services['certificate_verification_service'];
    }

    public function getAuthService(): AuthService
    {
        if (!isset($this->services['auth_service'])) {
            try {
                $this->services['auth_service'] = new AuthService(
                    $this->getDatabase()
                );
            } catch (Exception $e) {
                error_log("Auth Service Creation Error: " . $e->getMessage());
                throw $e;
            }
        }
        return $this->services['auth_service'];
    }
} 