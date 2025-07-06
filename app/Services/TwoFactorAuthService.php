<?php
namespace App\Services;

use App\Repositories\UserRepository;
use Firebase\JWT\JWT;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class TwoFactorAuthService {
    private $userRepository;
    private $secretKey;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
        $this->secretKey = $_ENV['2FA_SECRET'] ?? bin2hex(random_bytes(32));
    }

    public function generateTwoFactorSecret(): string {
        return base64_encode(random_bytes(20));
    }

    public function generateQRCode(string $username, string $secret): string {
        $qrCode = QrCode::create("otpauth://totp/{$username}?secret={$secret}&issuer=BirthCertSystem");
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        return $result->getDataUri();
    }

    public function validateTwoFactorToken(int $userId, string $token): bool {
        $user = $this->userRepository->findById($userId);
        if (!$user || !$user['two_factor_secret']) {
            return false;
        }

        $valid = $this->verifyToken($user['two_factor_secret'], $token);
        
        if ($valid) {
            $this->userRepository->updateTwoFactorStatus($userId, true);
        }

        return $valid;
    }

    private function verifyToken(string $secret, string $userToken): bool {
        $currentTime = time();
        $timeSlice = floor($currentTime / 30);

        for ($i = -1; $i <= 1; $i++) {
            $calculatedToken = $this->getToken($secret, $timeSlice + $i);
            if (hash_equals($calculatedToken, $userToken)) {
                return true;
            }
        }

        return false;
    }

    private function getToken(string $secret, int $timeSlice): string {
        $secretKey = base64_decode($secret);
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        $hmac = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hmac, -1)) & 0xf;
        $code = unpack('N', substr($hmac, $offset, 4))[1] & 0x7fffffff;
        return str_pad((string)($code % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public function disableTwoFactor(int $userId): bool {
        return $this->userRepository->updateTwoFactorStatus($userId, false);
    }
} 