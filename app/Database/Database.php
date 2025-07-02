<?php

namespace App\Database;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                // Get database configuration from environment variables
                $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
                $dbname = $_ENV['DB_DATABASE'] ?? 'birth_certificate_system';
                $username = $_ENV['DB_USERNAME'] ?? 'root';
                $password = $_ENV['DB_PASSWORD'] ?? '1212';
                
                // Log connection attempt for debugging
                if ($_ENV['APP_DEBUG'] ?? false) {
                    error_log("Database connection attempt: mysql:host={$host};dbname={$dbname};charset=utf8mb4");
                }

                // Try to connect to the database server first without specifying a database
                try {
                    $tempConnection = new PDO(
                        "mysql:host={$host};charset=utf8mb4",
                        $username,
                        $password,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_TIMEOUT => 5, // 5 second timeout
                        ]
                    );
                    
                    // Check if database exists, create if it doesn't
                    $stmt = $tempConnection->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbname}'");
                    if (!$stmt->fetch()) {
                        if ($_ENV['APP_DEBUG'] ?? false) {
                            error_log("Database '{$dbname}' not found, attempting to create it");
                        }
                        $tempConnection->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    }
                    
                    // Close temporary connection
                    $tempConnection = null;
                } catch (PDOException $e) {
                    // Log server connection error
                    error_log("Database server connection failed: " . $e->getMessage());
                    throw new PDOException('Unable to connect to database server. Please check if MySQL is running.');
                }

                // Connect to the specific database
                self::$connection = new PDO(
                    "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_TIMEOUT => 5, // 5 second timeout
                    ]
                );
                
                if ($_ENV['APP_DEBUG'] ?? false) {
                    error_log("Database connection established successfully");
                }
            } catch (PDOException $e) {
                // Log detailed error
                error_log("Database connection error: " . $e->getMessage());
                
                if ($_ENV['APP_DEBUG'] ?? false) {
                    throw new PDOException('Database connection failed: ' . $e->getMessage());
                }
                throw new PDOException('Database connection failed. Please try again later.');
            }
        }

        return self::$connection;
    }

    public static function closeConnection(): void
    {
        self::$connection = null;
        
        if ($_ENV['APP_DEBUG'] ?? false) {
            error_log("Database connection closed");
        }
    }
    
    /**
     * Run a test query to check if the connection is working
     *
     * @return bool True if connection is working, false otherwise
     */
    public static function testConnection(): bool
    {
        try {
            $connection = self::getConnection();
            $connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            error_log("Database test connection failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get database diagnostic information
     *
     * @return array Database diagnostic information
     */
    public static function getDiagnostics(): array
    {
        $diagnostics = [
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'database' => $_ENV['DB_DATABASE'] ?? 'birth_certificate_system',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'connection_status' => 'Unknown',
            'tables' => [],
            'error' => null
        ];
        
        try {
            $connection = self::getConnection();
            $diagnostics['connection_status'] = 'Connected';
            
            // Get tables
            $stmt = $connection->query('SHOW TABLES');
            $diagnostics['tables'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (PDOException $e) {
            $diagnostics['connection_status'] = 'Failed';
            $diagnostics['error'] = $e->getMessage();
        }
        
        return $diagnostics;
    }
}