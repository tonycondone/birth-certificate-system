<?php
namespace App\Validators;

class InputValidator {
    public static function validateBirthCertificateApplication(array $data): array {
        $errors = [];

        // Child's Information
        if (empty($data['child_first_name']) || strlen($data['child_first_name']) < 2) {
            $errors[] = 'Invalid child first name';
        }

        if (empty($data['child_last_name']) || strlen($data['child_last_name']) < 2) {
            $errors[] = 'Invalid child last name';
        }

        if (empty($data['date_of_birth']) || !self::validateDate($data['date_of_birth'])) {
            $errors[] = 'Invalid date of birth';
        }

        // Parents' Information
        if (empty($data['father_first_name']) || strlen($data['father_first_name']) < 2) {
            $errors[] = 'Invalid father first name';
        }

        if (empty($data['mother_first_name']) || strlen($data['mother_first_name']) < 2) {
            $errors[] = 'Invalid mother first name';
        }

        // Place of Birth
        if (empty($data['place_of_birth']) || strlen($data['place_of_birth']) < 2) {
            $errors[] = 'Invalid place of birth';
        }

        // Additional Validations
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }

        return $errors;
    }

    public static function validateUserRegistration(array $data): array {
        $errors = [];

        if (empty($data['username']) || strlen($data['username']) < 4) {
            $errors[] = 'Username must be at least 4 characters';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $data['password'])) {
            $errors[] = 'Password must include uppercase, lowercase, number, and special character';
        }

        return $errors;
    }

    public static function sanitizeInput(string $input): string {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    private static function validateDate(string $date, string $format = 'Y-m-d'): bool {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function validatePhoneNumber(string $phone): bool {
        return preg_match('/^\+?[1-9]\d{1,14}$/', $phone);
    }

    public static function validateSSN(string $ssn): bool {
        return preg_match('/^\d{3}-\d{2}-\d{4}$/', $ssn);
    }
} 