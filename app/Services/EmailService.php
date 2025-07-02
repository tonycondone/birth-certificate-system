<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Exception;
use App\Services\BlockchainService;
use App\Services\LoggingService;

class EmailService
{
    private PHPMailer $mailer;
    private string $fromEmail;
    private string $fromName;
    private string $baseUrl;
    private $blockchainService;
    private $loggingService;

    public function __construct(
        BlockchainService $blockchainService, 
        LoggingService $loggingService
    ) {
        $this->blockchainService = $blockchainService;
        $this->loggingService = $loggingService;
        $this->mailer = new PHPMailer(true);
        $this->fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@birthcertificate.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Birth Certificate System';
        $this->baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        
        $this->configureMailer();
    }

    /**
     * Configure PHPMailer with SMTP settings
     */
    private function configureMailer(): void
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['MAIL_HOST'] ?? 'localhost';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? '';
            $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $_ENV['MAIL_PORT'] ?? 587;

            // Default settings
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
            // Enable debug in development
            if ($_ENV['APP_ENV'] === 'development') {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }
        } catch (PHPMailerException $e) {
            error_log("Email configuration error: " . $e->getMessage());
            throw new Exception("Email service configuration failed");
        }
    }

    /**
     * Send email verification
     */
    public function sendVerificationEmail(string $email, string $name, string $token): bool
    {
        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = 'Verify Your Email - Birth Certificate System';
            
            $verificationUrl = $this->baseUrl . '/auth/verify-email?token=' . urlencode($token);
            
            $htmlBody = $this->getVerificationEmailTemplate($name, $verificationUrl);
            $textBody = $this->getVerificationEmailTextTemplate($name, $verificationUrl);
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send verification email to {$email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(string $email, string $name, string $token): bool
    {
        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = 'Reset Your Password - Birth Certificate System';
            
            $resetUrl = $this->baseUrl . '/auth/reset-password?token=' . urlencode($token);
            
            $htmlBody = $this->getPasswordResetEmailTemplate($name, $resetUrl);
            $textBody = $this->getPasswordResetEmailTextTemplate($name, $resetUrl);
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send password reset email to {$email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send two-factor authentication setup email
     */
    public function sendTwoFactorSetupEmail(string $email, string $name, array $backupCodes): bool
    {
        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = 'Two-Factor Authentication Enabled - Birth Certificate System';
            
            $htmlBody = $this->getTwoFactorSetupEmailTemplate($name, $backupCodes);
            $textBody = $this->getTwoFactorSetupEmailTextTemplate($name, $backupCodes);
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send 2FA setup email to {$email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send application status update email
     */
    public function sendApplicationStatusEmail(string $email, string $name, string $applicationNumber, string $status): bool
    {
        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = "Application Status Update - {$applicationNumber}";
            
            $htmlBody = $this->getApplicationStatusEmailTemplate($name, $applicationNumber, $status);
            $textBody = $this->getApplicationStatusEmailTextTemplate($name, $applicationNumber, $status);
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send application status email to {$email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get verification email HTML template
     */
    private function getVerificationEmailTemplate(string $name, string $verificationUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Verify Your Email</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Welcome to Birth Certificate System!</h2>
                <p>Hello {$name},</p>
                <p>Thank you for registering with our secure birth certificate system. To complete your registration, please verify your email address by clicking the button below:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$verificationUrl}' style='background-color: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Verify Email Address</a>
                </div>
                
                <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #7f8c8d;'>{$verificationUrl}</p>
                
                <p>This link will expire in 24 hours for security reasons.</p>
                
                <p>If you didn't create an account with us, please ignore this email.</p>
                
                <hr style='border: none; border-top: 1px solid #ecf0f1; margin: 30px 0;'>
                <p style='font-size: 12px; color: #7f8c8d;'>
                    This is an automated message from the Birth Certificate System. Please do not reply to this email.
                </p>
            </div>
        </body>
        </html>";
    }

    /**
     * Get verification email text template
     */
    private function getVerificationEmailTextTemplate(string $name, string $verificationUrl): string
    {
        return "
        Welcome to Birth Certificate System!
        
        Hello {$name},
        
        Thank you for registering with our secure birth certificate system. To complete your registration, please verify your email address by visiting this link:
        
        {$verificationUrl}
        
        This link will expire in 24 hours for security reasons.
        
        If you didn't create an account with us, please ignore this email.
        
        ---
        This is an automated message from the Birth Certificate System. Please do not reply to this email.";
    }

    /**
     * Get password reset email HTML template
     */
    private function getPasswordResetEmailTemplate(string $name, string $resetUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Reset Your Password</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #e74c3c;'>Password Reset Request</h2>
                <p>Hello {$name},</p>
                <p>We received a request to reset your password for your Birth Certificate System account. Click the button below to create a new password:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetUrl}' style='background-color: #e74c3c; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                </div>
                
                <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #7f8c8d;'>{$resetUrl}</p>
                
                <p>This link will expire in 1 hour for security reasons.</p>
                
                <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>
                
                <hr style='border: none; border-top: 1px solid #ecf0f1; margin: 30px 0;'>
                <p style='font-size: 12px; color: #7f8c8d;'>
                    This is an automated message from the Birth Certificate System. Please do not reply to this email.
                </p>
            </div>
        </body>
        </html>";
    }

    /**
     * Get password reset email text template
     */
    private function getPasswordResetEmailTextTemplate(string $name, string $resetUrl): string
    {
        return "
        Password Reset Request
        
        Hello {$name},
        
        We received a request to reset your password for your Birth Certificate System account. Visit this link to create a new password:
        
        {$resetUrl}
        
        This link will expire in 1 hour for security reasons.
        
        If you didn't request a password reset, please ignore this email. Your password will remain unchanged.
        
        ---
        This is an automated message from the Birth Certificate System. Please do not reply to this email.";
    }

    /**
     * Get two-factor setup email HTML template
     */
    private function getTwoFactorSetupEmailTemplate(string $name, array $backupCodes): string
    {
        $backupCodesList = implode('<br>', $backupCodes);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Two-Factor Authentication Enabled</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #27ae60;'>Two-Factor Authentication Enabled</h2>
                <p>Hello {$name},</p>
                <p>Two-factor authentication has been successfully enabled for your Birth Certificate System account. This adds an extra layer of security to protect your account.</p>
                
                <h3 style='color: #2c3e50;'>Backup Codes</h3>
                <p>Please save these backup codes in a secure location. You can use them to access your account if you lose your authenticator device:</p>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <code style='font-family: monospace; font-size: 14px;'>{$backupCodesList}</code>
                </div>
                
                <p><strong>Important:</strong> Each backup code can only be used once. After using a code, it will be permanently disabled.</p>
                
                <hr style='border: none; border-top: 1px solid #ecf0f1; margin: 30px 0;'>
                <p style='font-size: 12px; color: #7f8c8d;'>
                    This is an automated message from the Birth Certificate System. Please do not reply to this email.
                </p>
            </div>
        </body>
        </html>";
    }

    /**
     * Get two-factor setup email text template
     */
    private function getTwoFactorSetupEmailTextTemplate(string $name, array $backupCodes): string
    {
        $backupCodesList = implode("\n", $backupCodes);
        
        return "
        Two-Factor Authentication Enabled
        
        Hello {$name},
        
        Two-factor authentication has been successfully enabled for your Birth Certificate System account. This adds an extra layer of security to protect your account.
        
        Backup Codes:
        Please save these backup codes in a secure location. You can use them to access your account if you lose your authenticator device:
        
        {$backupCodesList}
        
        Important: Each backup code can only be used once. After using a code, it will be permanently disabled.
        
        ---
        This is an automated message from the Birth Certificate System. Please do not reply to this email.";
    }

    /**
     * Get application status email HTML template
     */
    private function getApplicationStatusEmailTemplate(string $name, string $applicationNumber, string $status): string
    {
        $statusColor = $this->getStatusColor($status);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Application Status Update</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Application Status Update</h2>
                <p>Hello {$name},</p>
                <p>The status of your birth certificate application has been updated.</p>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                    <p><strong>Application Number:</strong> {$applicationNumber}</p>
                    <p><strong>New Status:</strong> <span style='color: {$statusColor}; font-weight: bold;'>{$status}</span></p>
                </div>
                
                <p>You can log into your account to view the full details of your application.</p>
                
                <hr style='border: none; border-top: 1px solid #ecf0f1; margin: 30px 0;'>
                <p style='font-size: 12px; color: #7f8c8d;'>
                    This is an automated message from the Birth Certificate System. Please do not reply to this email.
                </p>
            </div>
        </body>
        </html>";
    }

    /**
     * Get application status email text template
     */
    private function getApplicationStatusEmailTextTemplate(string $name, string $applicationNumber, string $status): string
    {
        return "
        Application Status Update
        
        Hello {$name},
        
        The status of your birth certificate application has been updated.
        
        Application Number: {$applicationNumber}
        New Status: {$status}
        
        You can log into your account to view the full details of your application.
        
        ---
        This is an automated message from the Birth Certificate System. Please do not reply to this email.";
    }

    /**
     * Get color for status display
     */
    private function getStatusColor(string $status): string
    {
        return match (strtolower($status)) {
            'approved' => '#27ae60',
            'rejected' => '#e74c3c',
            'under_review' => '#f39c12',
            'certificate_issued' => '#27ae60',
            default => '#7f8c8d'
        };
    }

    /**
     * Generate email verification token
     * 
     * @param string $email
     * @return string
     */
    public function generateVerificationToken(string $email): string {
        // Use blockchain service to generate a secure token
        $tokenData = [
            'email' => $email,
            'timestamp' => time(),
            'purpose' => 'email_verification'
        ];
        
        return $this->blockchainService->generateSecureHash(json_encode($tokenData));
    }

    /**
     * Send email verification link
     * 
     * @param string $email
     * @param string $verificationToken
     * @return bool
     */
    public function sendEmailVerification(string $email, string $verificationToken): bool {
        try {
            // Reset PHPMailer for a new email
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
            $this->mailer->clearCustomHeaders();

            // Set email parameters
            $this->mailer->setFrom($_ENV['SYSTEM_EMAIL'] ?? 'noreply@birthcertificate.gov', 'Birth Certificate System');
            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Verify Your Email - Birth Certificate System';

            // Construct verification link with blockchain-secured token
            $verificationLink = sprintf(
                '%s/verify-email?token=%s&email=%s', 
                $_ENV['APP_URL'] ?? 'https://birthcertificate.gov',
                urlencode($verificationToken),
                urlencode($email)
            );

            // Email body with enhanced security information
            $emailBody = $this->generateVerificationEmailBody($verificationLink);
            $this->mailer->Body = $emailBody;

            // Send email
            $sent = $this->mailer->send();

            // Log the email verification attempt
            $this->loggingService->auditTrail('EMAIL_VERIFICATION_SENT', 'users', [
                'email' => $email,
                'status' => $sent ? 'success' : 'failed'
            ]);

            return $sent;
        } catch (PHPMailerException $e) {
            $this->loggingService->error('Email Verification Failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate secure, informative email body
     * 
     * @param string $verificationLink
     * @return string
     */
    private function generateVerificationEmailBody(string $verificationLink): string {
        return sprintf('
            <html>
            <body>
                <h2>Email Verification - Birth Certificate System</h2>
                <p>To verify your email, please click the link below:</p>
                <p><a href="%s">Verify Email</a></p>
                <hr>
                <small>
                    This link is uniquely generated and will expire in 1 hour. 
                    If you did not request this verification, please ignore this email.
                    <br>
                    Verification Link Secured by Blockchain Technology
                </small>
            </body>
            </html>
        ', $verificationLink);
    }

    /**
     * Verify email verification token
     * 
     * @param string $email
     * @param string $token
     * @return bool
     */
    public function verifyEmailToken(string $email, string $token): bool {
        try {
            // Validate token age and integrity
            $tokenData = json_decode(
                $this->blockchainService->decodeSecureHash($token), 
                true
            );

            // Check token validity
            if (!$tokenData || 
                $tokenData['email'] !== $email || 
                $tokenData['purpose'] !== 'email_verification' ||
                (time() - $tokenData['timestamp']) > 3600 // 1-hour expiration
            ) {
                return false;
            }

            // Log successful verification
            $this->loggingService->auditTrail('EMAIL_VERIFIED', 'users', [
                'email' => $email
            ]);

            return true;
        } catch (\Exception $e) {
            $this->loggingService->error('Email Verification Error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 