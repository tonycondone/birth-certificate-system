
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Birth Certificate System</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="/">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
$pageTitle = 'API Documentation - Digital Birth Certificate System';
require_once __DIR__ . '/layouts/base.php';
?>

<!-- API Documentation Section -->
<section class="api-docs-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">
                            <i class="fas fa-code me-2"></i>
                            API Documentation
                        </h2>
                    </div>
                    <div class="card-body">
                        <p class="lead">
                            Welcome to the Digital Birth Certificate System API. This API allows you to integrate with our system programmatically.
                        </p>
                        
                        <!-- Authentication -->
                        <div class="mb-5">
                            <h3 class="border-bottom pb-2">
                                <i class="fas fa-key me-2"></i>Authentication
                            </h3>
                            <p>Most API endpoints require authentication. You can authenticate using:</p>
                            <ul>
                                <li><strong>API Key:</strong> Include in headers or query parameters</li>
                                <li><strong>Session:</strong> For web-based applications</li>
                            </ul>
                            <div class="alert alert-info">
                                <strong>Demo API Keys:</strong> <code>demo_api_key_123</code>, <code>test_key_456</code>
                            </div>
                        </div>

                        <!-- Endpoints -->
                        <div class="mb-5">
                            <h3 class="border-bottom pb-2">
                                <i class="fas fa-link me-2"></i>API Endpoints
                            </h3>
                            
                            <!-- System Statistics -->
                            <div class="endpoint-card mb-4">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">
                                            <span class="badge bg-light text-dark me-2">GET</span>
                                            System Statistics
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Endpoint:</strong> <code>/api/statistics</code></p>
                                        <p><strong>Description:</strong> Get comprehensive system statistics</p>
                                        <p><strong>Authentication:</strong> Required (API Key)</p>
                                        
                                        <h6>Request Example:</h6>
                                        <pre><code>curl -X GET "https://your-domain.com/api/statistics" \
  -H "Authorization: demo_api_key_123"</code></pre>
                                        
                                        <h6>Response Example:</h6>
                                        <pre><code>{
  "success": true,
  "data": {
    "users": {
      "total_users": 150,
      "total_parents": 100,
      "total_hospitals": 30,
      "total_registrars": 15,
      "total_admins": 5
    },
    "applications": {
      "total_applications": 500,
      "pending_applications": 50,
      "approved_applications": 400,
      "rejected_applications": 50
    },
    "certificates": {
      "total_certificates": 400,
      "active_certificates": 380,
      "expired_certificates": 15,
      "revoked_certificates": 5
    }
  },
  "timestamp": "2024-01-15 10:30:00",
  "version": "1.0.0"
}</code></pre>
                                    </div>
                                </div>
                            </div>

                            <!-- Certificate Verification -->
                            <div class="endpoint-card mb-4">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">
                                            <span class="badge bg-light text-dark me-2">GET</span>
                                            Certificate Verification
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Endpoint:</strong> <code>/api/verify-certificate</code></p>
                                        <p><strong>Description:</strong> Verify a birth certificate</p>
                                        <p><strong>Authentication:</strong> Not required (Public endpoint)</p>
                                        
                                        <h6>Parameters:</h6>
                                        <ul>
                                            <li><code>certificate_number</code> (required) - The certificate number to verify</li>
                                        </ul>
                                        
                                        <h6>Request Example:</h6>
                                        <pre><code>curl -X GET "https://your-domain.com/api/verify-certificate?certificate_number=BC2024001"</code></pre>
                                        
                                        <h6>Response Example:</h6>
                                        <pre><code>{
  "success": true,
  "data": {
    "valid": true,
    "message": "Certificate is valid",
    "certificate": {
      "certificate_number": "BC2024001",
      "certificate_status": "active",
      "issue_date": "2024-01-15 10:30:00",
      "child_first_name": "John",
      "child_last_name": "Doe",
      "date_of_birth": "2024-01-01",
      "gender": "male",
      "place_of_birth": "City Hospital",
      "parent_first_name": "Jane",
      "parent_last_name": "Doe"
    }
  },
  "timestamp": "2024-01-15 10:30:00"
}</code></pre>
                                    </div>
                                </div>
                            </div>

                            <!-- User Applications -->
                            <div class="endpoint-card mb-4">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="mb-0">
                                            <span class="badge bg-light text-dark me-2">GET</span>
                                            User Applications
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Endpoint:</strong> <code>/api/user-applications</code></p>
                                        <p><strong>Description:</strong> Get user's birth certificate applications</p>
                                        <p><strong>Authentication:</strong> Required (Session)</p>
                                        
                                        <h6>Parameters:</h6>
                                        <ul>
                                            <li><code>page</code> (optional) - Page number (default: 1)</li>
                                            <li><code>limit</code> (optional) - Items per page (default: 10)</li>
                                            <li><code>status</code> (optional) - Filter by status</li>
                                        </ul>
                                        
                                        <h6>Request Example:</h6>
                                        <pre><code>curl -X GET "https://your-domain.com/api/user-applications?page=1&limit=5&status=approved" \
  -H "Cookie: PHPSESSID=your_session_id"</code></pre>
                                        
                                        <h6>Response Example:</h6>
                                        <pre><code>{
  "success": true,
  "data": {
    "applications": [
      {
        "application_number": "APP2024001",
        "child_first_name": "John",
        "child_last_name": "Doe",
        "date_of_birth": "2024-01-01",
        "gender": "male",
        "status": "approved",
        "submitted_at": "2024-01-15 10:30:00",
        "hospital_verified_at": "2024-01-16 14:20:00",
        "registrar_verified_at": "2024-01-17 09:15:00"
      }
    ],
    "total": 1,
    "page": 1,
    "limit": 5,
    "pages": 1
  },
  "pagination": {
    "page": 1,
    "limit": 5,
    "total": 1
  },
  "timestamp": "2024-01-15 10:30:00"
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error Handling -->
                        <div class="mb-5">
                            <h3 class="border-bottom pb-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>Error Handling
                            </h3>
                            <p>All API endpoints return consistent error responses:</p>
                            
                            <div class="alert alert-danger">
                                <h6>Error Response Format:</h6>
                                <pre><code>{
  "success": false,
  "error": "Error message description",
  "timestamp": "2024-01-15 10:30:00"
}</code></pre>
                            </div>
                            
                            <h6>Common HTTP Status Codes:</h6>
                            <ul>
                                <li><strong>200</strong> - Success</li>
                                <li><strong>400</strong> - Bad Request (invalid parameters)</li>
                                <li><strong>401</strong> - Unauthorized (authentication required)</li>
                                <li><strong>403</strong> - Forbidden (insufficient permissions)</li>
                                <li><strong>404</strong> - Not Found</li>
                                <li><strong>500</strong> - Internal Server Error</li>
                            </ul>
                        </div>

                        <!-- Rate Limiting -->
                        <div class="mb-5">
                            <h3 class="border-bottom pb-2">
                                <i class="fas fa-tachometer-alt me-2"></i>Rate Limiting
                            </h3>
                            <p>To ensure fair usage, API endpoints are rate limited:</p>
                            <ul>
                                <li><strong>Public endpoints:</strong> 100 requests per hour</li>
                                <li><strong>Authenticated endpoints:</strong> 1000 requests per hour</li>
                                <li><strong>Admin endpoints:</strong> 5000 requests per hour</li>
                            </ul>
                        </div>

                        <!-- Code Examples -->
                        <div class="mb-5">
                            <h3 class="border-bottom pb-2">
                                <i class="fas fa-code me-2"></i>Code Examples
                            </h3>
                            
                            <!-- JavaScript Example -->
                            <div class="mb-4">
                                <h5>JavaScript (Fetch API)</h5>
                                <pre><code>// Verify certificate
async function verifyCertificate(certificateNumber) {
    try {
        const response = await fetch(`/api/verify-certificate?certificate_number=${certificateNumber}`);
        const data = await response.json();
        
        if (data.success) {
            console.log('Certificate:', data.data.certificate);
        } else {
            console.error('Error:', data.error);
        }
    } catch (error) {
        console.error('Request failed:', error);
    }
}

// Get system statistics
async function getStatistics() {
    try {
        const response = await fetch('/api/statistics', {
            headers: {
                'Authorization': 'demo_api_key_123'
            }
        });
        const data = await response.json();
        
        if (data.success) {
            console.log('Statistics:', data.data);
        }
    } catch (error) {
        console.error('Request failed:', error);
    }
}</code></pre>
                            </div>

                            <!-- PHP Example -->
                            <div class="mb-4">
                                <h5>PHP (cURL)</h5>
                                <pre><code>// Verify certificate
function verifyCertificate($certificateNumber) {
    $url = "https://your-domain.com/api/verify-certificate?certificate_number=" . urlencode($certificateNumber);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Get system statistics
function getStatistics($apiKey) {
    $url = "https://your-domain.com/api/statistics";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}</code></pre>
                            </div>
                        </div>

                        <!-- Support -->
                        <div class="alert alert-info">
                            <h5><i class="fas fa-life-ring me-2"></i>Need Help?</h5>
                            <p>If you need assistance with the API or have questions about integration:</p>
                            <ul>
                                <li>Check our <a href="/contact">contact page</a> for support</li>
                                <li>Review the <a href="/docs">full documentation</a></li>
                                <li>Join our <a href="/community">developer community</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.api-docs-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.endpoint-card .card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.endpoint-card .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

pre {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    font-size: 14px;
    overflow-x: auto;
}

code {
    background: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.9em;
}

.badge {
    font-size: 0.8em;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add copy functionality to code blocks
    const codeBlocks = document.querySelectorAll('pre code');
    codeBlocks.forEach(block => {
        block.style.cursor = 'pointer';
        block.title = 'Click to copy';
        
        block.addEventListener('click', function() {
            navigator.clipboard.writeText(this.textContent).then(() => {
                // Show temporary success message
                const originalText = this.textContent;
                this.textContent = 'Copied!';
                this.style.color = '#28a745';
                
                setTimeout(() => {
                    this.textContent = originalText;
                    this.style.color = '';
                }, 1000);
            });
        });
    });
});
</script> 