<?php

namespace App\Controllers;

use App\Database\Database;
use Exception;

/**
 * TrackingController
 * 
 * Handles application tracking functionality
 */
class TrackingController
{
    /**
     * Show the tracking form
     */
    public function showTrackingForm()
    {
        $pageTitle = 'Track Application - Digital Birth Certificate System';
        
        // Get tracking number from query string if available
        $trackingNumber = $_GET['tracking_number'] ?? '';
        
        // Check if tracking form view exists
        $viewPath = BASE_PATH . '/resources/views/tracking/form.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            // Fallback to applications/track.php if it exists
            $fallbackPath = BASE_PATH . '/resources/views/applications/track.php';
            if (file_exists($fallbackPath)) {
                include $fallbackPath;
            } else {
                // Show a basic form if no view exists
                echo $this->getBasicTrackingForm();
            }
        }
    }
    
    /**
     * Get a basic tracking form HTML
     */
    private function getBasicTrackingForm()
    {
        return '<!DOCTYPE html>
        <html>
        <head>
            <title>Track Application - Digital Birth Certificate System</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Track Your Application</h4>
                            </div>
                            <div class="card-body">
                                ' . ($this->getFlashMessage()) . '
                                <form action="/track/search" method="post">
                                    <div class="mb-3">
                                        <label for="tracking_number" class="form-label">Enter Tracking Number</label>
                                        <input type="text" class="form-control" id="tracking_number" name="tracking_number" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Track Application</button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <a href="/" class="btn btn-link">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Get flash message HTML
     */
    private function getFlashMessage()
    {
        $html = '';
        if (isset($_SESSION['error'])) {
            $html = '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            $html = '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        return $html;
    }

    /**
     * Search for application by tracking number
     */
    public function search()
    {
        $trackingNumber = $_POST['tracking_number'] ?? $_GET['tracking_number'] ?? '';
        
        if (empty($trackingNumber)) {
            $_SESSION['error'] = 'Please enter a tracking number';
            header('Location: /track');
            exit;
        }
        
        // Redirect to tracking detail page
        header("Location: /track/" . urlencode($trackingNumber));
        exit;
    }
    
    /**
     * Show tracking details for a specific tracking number
     */
    public function show($trackingNumber = null)
    {
        if (!$trackingNumber) {
            $_SESSION['error'] = 'Invalid tracking number';
            header('Location: /track');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get application details
            $stmt = $pdo->prepare("
                SELECT a.*, u.first_name, u.last_name, u.email,
                       h.name as hospital_name
                FROM applications a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN hospitals h ON a.hospital_id = h.id
                WHERE a.reference_number = ? OR a.tracking_number = ?
            ");
            $stmt->execute([$trackingNumber, $trackingNumber]);
            $application = $stmt->fetch();
            
            if (!$application) {
                $_SESSION['error'] = 'Application not found with tracking number: ' . $trackingNumber;
                header('Location: /track');
                exit;
            }
            
            // Get status history
            $stmt = $pdo->prepare("
                SELECT * FROM application_status_history 
                WHERE application_id = ? 
                ORDER BY created_at ASC
            ");
            $stmt->execute([$application['id']]);
            $statusHistory = $stmt->fetchAll();
            
            $pageTitle = 'Track Application - Digital Birth Certificate System';
            
            // Include tracking view or show fallback
            $viewPath = BASE_PATH . '/resources/views/tracking/show.php';
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo $this->getTrackingView($application, $statusHistory);
            }
            
        } catch (Exception $e) {
            error_log("Tracking error: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to retrieve tracking information';
            header('Location: /track');
            exit;
        }
    }
    
    /**
     * Alias for show() method - used by the route /track/{trackingNumber}
     */
    public function trackApplication($trackingNumber = null)
    {
        return $this->show($trackingNumber);
    }
    
    /**
     * Get tracking view HTML
     */
    private function getTrackingView($application, $statusHistory)
    {
        $statusColors = [
            'pending' => 'warning',
            'under_review' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'completed' => 'success'
        ];
        
        $statusColor = $statusColors[$application['status']] ?? 'secondary';
        
        $html = "<!DOCTYPE html>
        <html>
        <head>
            <title>Track Application - Digital Birth Certificate System</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row'>
                    <div class='col-md-8'>
                        <div class='card'>
                            <div class='card-header'>
                                <h4>Application Tracking</h4>
                            </div>
                            <div class='card-body'>
                                <div class='row mb-4'>
                                    <div class='col-md-6'>
                                        <h5>Application Details</h5>
                                        <p><strong>Tracking Number:</strong> " . htmlspecialchars($application['reference_number']) . "</p>
                                        <p><strong>Applicant:</strong> " . htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) . "</p>
                                        <p><strong>Email:</strong> " . htmlspecialchars($application['email']) . "</p>
                                        <p><strong>Submitted:</strong> " . date('F j, Y g:i A', strtotime($application['created_at'])) . "</p>
                                    </div>
                                    <div class='col-md-6'>
                                        <h5>Current Status</h5>
                                        <span class='badge bg-$statusColor fs-6'>" . ucfirst(str_replace('_', ' ', $application['status'])) . "</span>
                                        " . ($application['hospital_name'] ? "<p class='mt-2'><strong>Hospital:</strong> " . htmlspecialchars($application['hospital_name']) . "</p>" : '') . "
                                    </div>
                                </div>";
        
        if (!empty($statusHistory)) {
            $html .= "<h5>Status History</h5>
                     <div class='timeline'>";
            
            foreach ($statusHistory as $status) {
                $html .= "<div class='timeline-item mb-3'>
                            <div class='card'>
                                <div class='card-body'>
                                    <h6 class='card-title'>" . ucfirst(str_replace('_', ' ', $status['status'])) . "</h6>
                                    <p class='card-text'>" . htmlspecialchars($status['notes'] ?? 'Status updated') . "</p>
                                    <small class='text-muted'>" . date('F j, Y g:i A', strtotime($status['created_at'])) . "</small>
                                </div>
                            </div>
                          </div>";
            }
            
            $html .= "</div>";
        }
        
        $html .= "      </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='card'>
                        <div class='card-header'>
                            <h5>What's Next?</h5>
                        </div>
                        <div class='card-body'>";
        
        switch ($application['status']) {
            case 'pending':
                $html .= "<p>Your application is being reviewed. You will be notified of any updates.</p>";
                break;
            case 'under_review':
                $html .= "<p>Your application is currently under review by our team.</p>";
                break;
            case 'approved':
                $html .= "<p>Congratulations! Your application has been approved. Your certificate will be issued shortly.</p>";
                break;
            case 'rejected':
                $html .= "<p>Unfortunately, your application has been rejected. Please contact support for more information.</p>";
                break;
            case 'completed':
                $html .= "<p>Your certificate has been issued and is ready for download.</p>";
                break;
            default:
                $html .= "<p>Please check back later for updates on your application.</p>";
        }
        
        $html .= "      </div>
                    </div>
                </div>
            </div>
            <div class='mt-3'>
                <a href='/track' class='btn btn-secondary'>Track Another Application</a>
                <a href='/' class='btn btn-primary'>Home</a>
            </div>
        </div>
        </body>
        </html>";
        
        return $html;
    }
}
