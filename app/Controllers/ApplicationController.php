<?php

namespace App\Controllers;

use App\Database\Database;

class ApplicationController
{
    public function submit()
    {
        // Check if user is logged in and is a parent
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'parent') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Validate input
            $childName = $_POST['child_name'] ?? '';
            $dateOfBirth = $_POST['date_of_birth'] ?? '';
            $placeOfBirth = $_POST['place_of_birth'] ?? '';
            $gender = $_POST['gender'] ?? '';
            
            if (empty($childName) || empty($dateOfBirth) || empty($placeOfBirth) || empty($gender)) {
                throw new \Exception('All fields are required');
            }
            
            // Insert application
            $stmt = $pdo->prepare("
                INSERT INTO birth_applications (
                    parent_id, child_name, date_of_birth, place_of_birth, 
                    gender, status, created_at
                ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $childName,
                $dateOfBirth,
                $placeOfBirth,
                $gender
            ]);
            
            $applicationId = $pdo->lastInsertId();
            
            // Handle file uploads if any
            if (isset($_FILES['documents'])) {
                $this->handleFileUploads($applicationId, $_FILES['documents']);
            }
            
            $response = [
                'success' => true,
                'message' => 'Application submitted successfully',
                'application_id' => $applicationId
            ];
            
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    private function handleFileUploads($applicationId, $files)
    {
        $uploadDir = __DIR__ . '/../../public/uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        foreach ($files['tmp_name'] as $key => $tmpName) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = $files['name'][$key];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Validate file type
                $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
                if (!in_array($fileExt, $allowedTypes)) {
                    continue;
                }
                
                // Generate unique filename
                $newFileName = uniqid() . '_' . $fileName;
                $filePath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($tmpName, $filePath)) {
                    // Store file reference in database
                    $pdo = Database::getConnection();
                    $stmt = $pdo->prepare("
                        INSERT INTO application_documents (
                            application_id, file_name, file_path, uploaded_at
                        ) VALUES (?, ?, ?, NOW())
                    ");
                    $stmt->execute([$applicationId, $fileName, $newFileName]);
                }
            }
        }
    }
} 