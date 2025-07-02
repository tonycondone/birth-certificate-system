<?php
namespace App\Services;

use Exception;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

class BlockchainService {
    /**
     * Generate a unique identification number (UIN) for a birth certificate
     * 
     * @return string
     */
    public function generateUIN(): string {
        // Use cryptographically secure random number generation
        return bin2hex(random_bytes(16));
    }

    /**
     * Generate RSA key pair for digital signatures
     * 
     * @return array
     */
    public function generateKeyPair(): array {
        $rsa = RSA::createKey(2048);
        return [
            'public_key' => $rsa->getPublicKey(),
            'private_key' => $rsa->getPrivateKey()
        ];
    }

    /**
     * Create digital signature for a birth certificate
     * 
     * @param string $certificateData
     * @param string $privateKey
     * @return string
     */
    public function createDigitalSignature(string $certificateData, string $privateKey): string {
        $rsa = PublicKeyLoader::load($privateKey);
        return base64_encode($rsa->sign($certificateData));
    }

    /**
     * Verify digital signature
     * 
     * @param string $certificateData
     * @param string $signature
     * @param string $publicKey
     * @return bool
     */
    public function verifyDigitalSignature(string $certificateData, string $signature, string $publicKey): bool {
        try {
            $rsa = PublicKeyLoader::load($publicKey);
            return $rsa->verify($certificateData, base64_decode($signature));
        } catch (Exception $e) {
            // Log signature verification failure
            return false;
        }
    }

    /**
     * Generate SHA-256 hash for blockchain immutability
     * 
     * @param string $data
     * @return string
     */
    public function generateBlockchainHash(string $data): string {
        return hash('sha256', $data);
    }

    /**
     * Generate QR Code data with enhanced security
     * 
     * @param array $certificateData
     * @return string
     */
    public function generateSecureQRCodeData(array $certificateData): string {
        $qrData = [
            'uin' => $this->generateUIN(),
            'hash' => $this->generateBlockchainHash(json_encode($certificateData)),
            'timestamp' => time()
        ];
        return json_encode($qrData);
    }

    /**
     * Generate a secure hash for various purposes
     * 
     * @param string $data
     * @return string
     */
    public function generateSecureHash(string $data): string {
        // Use multiple layers of cryptographic security
        $salt = bin2hex(random_bytes(16)); // Cryptographically secure random salt
        $hashedData = hash('sha256', $salt . $data);
        
        // Combine salt and hash for additional security
        return base64_encode(json_encode([
            'salt' => $salt,
            'hash' => $hashedData,
            'timestamp' => time()
        ]));
    }

    /**
     * Decode and verify a secure hash
     * 
     * @param string $encodedHash
     * @return string|false
     */
    public function decodeSecureHash(string $encodedHash) {
        try {
            $hashData = json_decode(base64_decode($encodedHash), true);
            
            // Validate hash structure
            if (!isset($hashData['salt'], $hashData['hash'], $hashData['timestamp'])) {
                return false;
            }

            // Check hash age (e.g., 1-hour expiration)
            if (time() - $hashData['timestamp'] > 3600) {
                return false;
            }

            return $hashData['hash'];
        } catch (\Exception $e) {
            // Log decoding errors
            return false;
        }
    }
} 