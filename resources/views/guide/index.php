<?php
// resources/views/guide/index.php
$pageTitle = $pageTitle ?? 'User Guide';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .guide-header {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .guide-section {
            margin-bottom: 40px;
        }
        .guide-card {
            height: 100%;
            transition: transform 0.2s;
        }
        .guide-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .guide-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #0d6efd;
        }
        .guide-video-container {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-top: 30px;
        }
        .video-card {
            margin-bottom: 20px;
        }
        .video-thumbnail {
            position: relative;
            overflow: hidden;
            border-radius: 5px;
        }
        .video-thumbnail img {
            width: 100%;
            transition: transform 0.3s;
        }
        .video-thumbnail:hover img {
            transform: scale(1.05);
        }
        .video-play {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background-color: rgba(255,255,255,0.8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .video-play i {
            color: #ff0000;
            font-size: 24px;
        }
        .video-duration {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: rgba(0,0,0,0.7);
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Guide Header -->
        <div class="guide-header text-center">
            <h1 class="display-5 fw-bold">Birth Certificate System User Guide</h1>
            <p class="lead">Everything you need to know about using our digital birth certificate system</p>
            <div class="d-flex justify-content-center gap-2 mt-4">
                <a href="/guide/section/faq" class="btn btn-primary">
                    <i class="fa fa-question-circle"></i> Frequently Asked Questions
                </a>
                <a href="/guide/videos" class="btn btn-outline-primary">
                    <i class="fa fa-video-camera"></i> Video Tutorials
                </a>
                <a href="/contact" class="btn btn-outline-secondary">
                    <i class="fa fa-envelope"></i> Get Support
                </a>
            </div>
        </div>

        <!-- Guide Sections -->
        <div class="guide-section">
            <h2 class="mb-4">Quick Start Guides</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <div class="card guide-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="guide-icon">
                                <i class="fa fa-file-text-o"></i>
                            </div>
                            <h5 class="card-title">Application Process</h5>
                            <p class="card-text">Learn how to apply for a birth certificate, required documents, and steps to complete your application.</p>
                            <a href="/guide/section/applications" class="btn btn-outline-primary mt-2">Read Guide</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card guide-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="guide-icon">
                                <i class="fa fa-search"></i>
                            </div>
                            <h5 class="card-title">Track Applications</h5>
                            <p class="card-text">Find out how to track the status of your application and check processing times.</p>
                            <a href="/guide/section/tracking" class="btn btn-outline-primary mt-2">Read Guide</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card guide-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="guide-icon">
                                <i class="fa fa-credit-card"></i>
                            </div>
                            <h5 class="card-title">Payment Process</h5>
                            <p class="card-text">Understand the payment methods, fees, and how to complete payment for your certificate.</p>
                            <a href="/guide/section/payment" class="btn btn-outline-primary mt-2">Read Guide</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="guide-section">
            <h2 class="mb-4">Detailed Guides</h2>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <div class="col">
                    <div class="card guide-card h-100">
                        <div class="card-body p-4">
                            <h5 class="card-title"><i class="fa fa-certificate text-primary me-2"></i> Birth Certificates</h5>
                            <p class="card-text">Comprehensive guide to understanding birth certificates, their components, and how to read them.</p>
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item border-0 ps-0"><i class="fa fa-check-circle text-success me-2"></i> Certificate validation</li>
                                <li class="list-group-item border-0 ps-0"><i class="fa fa-check-circle text-success me-2"></i> Security features</li>
                                <li class="list-group-item border-0 ps-0"><i class="fa fa-check-circle text-success me-2"></i> Renewal process</li>
                            </ul>
                            <a href="/guide/section/certificates" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card guide-card h-100">
                        <div class="card-body p-4">
                            <h5 class="card-title"><i class="fa fa-shield text-primary me-2"></i> Verification Guide</h5>
                            <p class="card-text">Learn how to verify the authenticity of birth certificates and protect against fraud.</p>
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item border-0 ps-0"><i class="fa fa-check-circle text-success me-2"></i> Online verification</li>
                                <li class="list-group-item border-0 ps-0"><i class="fa fa-check-circle text-success me-2"></i> QR code scanning</li>
                                <li class="list-group-item border-0 ps-0"><i class="fa fa-check-circle text-success me-2"></i> Certificate registry check</li>
                            </ul>
                            <a href="/guide/section/verification" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Tutorial Videos -->
        <div class="guide-video-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Featured Tutorial Videos</h2>
                <a href="/guide/videos" class="btn btn-sm btn-outline-primary">View All Videos</a>
            </div>
            <div class="row">
                <div class="col-md-6 video-card">
                    <a href="/guide/video/application-walkthrough" class="text-decoration-none">
                        <div class="video-thumbnail">
                            <img src="/assets/images/video-app-walkthrough.jpg" alt="Application Walkthrough" onerror="this.src='https://via.placeholder.com/600x338?text=Application+Walkthrough'">
                            <div class="video-play">
                                <i class="fa fa-play"></i>
                            </div>
                            <div class="video-duration">5:23</div>
                        </div>
                        <h5 class="mt-2">Application Walkthrough</h5>
                        <p class="text-muted">Step-by-step guide to completing your birth certificate application</p>
                    </a>
                </div>
                <div class="col-md-6 video-card">
                    <a href="/guide/video/certificate-validation" class="text-decoration-none">
                        <div class="video-thumbnail">
                            <img src="/assets/images/video-certificate-validation.jpg" alt="Certificate Validation" onerror="this.src='https://via.placeholder.com/600x338?text=Certificate+Validation'">
                            <div class="video-play">
                                <i class="fa fa-play"></i>
                            </div>
                            <div class="video-duration">3:47</div>
                        </div>
                        <h5 class="mt-2">Certificate Validation</h5>
                        <p class="text-muted">Learn how to verify the authenticity of a birth certificate</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Help -->
        <div class="card mt-5">
            <div class="card-header">
                <h3 class="mb-0">Need Help?</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <h5><i class="fa fa-phone text-primary me-2"></i> Contact Support</h5>
                        <p>Our support team is available Monday to Friday, 8:00 AM to 5:00 PM.</p>
                        <a href="/contact" class="btn btn-sm btn-outline-primary">Contact Us</a>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <h5><i class="fa fa-question-circle text-primary me-2"></i> FAQ</h5>
                        <p>Find answers to commonly asked questions about our services.</p>
                        <a href="/guide/section/faq" class="btn btn-sm btn-outline-primary">View FAQ</a>
                    </div>
                    <div class="col-md-4">
                        <h5><i class="fa fa-download text-primary me-2"></i> Resources</h5>
                        <p>Download helpful resources and forms for your application.</p>
                        <a href="/resources" class="btn btn-sm btn-outline-primary">Download</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 