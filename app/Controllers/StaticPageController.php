<?php

namespace App\Controllers;

/**
 * StaticPageController
 * 
 * Handles static pages like about, contact, FAQ, etc.
 */
class StaticPageController
{
    /**
     * About page
     */
    public function about()
    {
        $pageTitle = 'About - Digital Birth Certificate System';
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>$pageTitle</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row'>
                    <div class='col-md-8'>
                        <div class='card'>
                            <div class='card-header'>
                                <h1>About Digital Birth Certificate System</h1>
                            </div>
                            <div class='card-body'>
                                <p class='lead'>A secure, efficient, and reliable digital platform for birth certificate management.</p>
                                
                                <h3>Our Mission</h3>
                                <p>To provide a modern, secure, and user-friendly platform for managing birth certificates, making the process more efficient for citizens, hospitals, and government agencies.</p>
                                
                                <h3>Key Features</h3>
                                <ul>
                                    <li>Secure online application submission</li>
                                    <li>Real-time application tracking</li>
                                    <li>Digital certificate verification</li>
                                    <li>Multi-role access (Citizens, Hospitals, Registrars, Administrators)</li>
                                    <li>Comprehensive reporting and analytics</li>
                                </ul>
                                
                                <h3>Security & Privacy</h3>
                                <p>We take security and privacy seriously. All data is encrypted and stored securely, and we comply with relevant data protection regulations.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='mt-3'>
                    <a href='/' class='btn btn-primary'>Home</a>
                    <a href='/contact' class='btn btn-outline-primary'>Contact Us</a>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Contact page
     */
    public function contact()
    {
        $pageTitle = 'Contact - Digital Birth Certificate System';
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>$pageTitle</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row'>
                    <div class='col-md-8'>
                        <div class='card'>
                            <div class='card-header'>
                                <h1>Contact Us</h1>
                            </div>
                            <div class='card-body'>
                                <div class='row'>
                                    <div class='col-md-6'>
                                        <h3>Get in Touch</h3>
                                        <p><strong>Email:</strong> support@birthcertificate.gov</p>
                                        <p><strong>Phone:</strong> +1 (555) 123-4567</p>
                                        <p><strong>Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
                                        
                                        <h4>Mailing Address</h4>
                                        <address>
                                            Digital Birth Certificate System<br>
                                            123 Government Plaza<br>
                                            City, State 12345
                                        </address>
                                    </div>
                                    <div class='col-md-6'>
                                        <h3>Quick Links</h3>
                                        <ul class='list-unstyled'>
                                            <li><a href='/faq'>Frequently Asked Questions</a></li>
                                            <li><a href='/track'>Track Your Application</a></li>
                                            <li><a href='/verify'>Verify a Certificate</a></li>
                                            <li><a href='/login'>Login to Your Account</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='mt-3'>
                    <a href='/' class='btn btn-primary'>Home</a>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * FAQ page
     */
    public function faq()
    {
        $pageTitle = 'FAQ - Digital Birth Certificate System';
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>$pageTitle</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row'>
                    <div class='col-md-10'>
                        <div class='card'>
                            <div class='card-header'>
                                <h1>Frequently Asked Questions</h1>
                            </div>
                            <div class='card-body'>
                                <div class='accordion' id='faqAccordion'>
                                    <div class='accordion-item'>
                                        <h2 class='accordion-header' id='heading1'>
                                            <button class='accordion-button' type='button' data-bs-toggle='collapse' data-bs-target='#collapse1'>
                                                How do I apply for a birth certificate?
                                            </button>
                                        </h2>
                                        <div id='collapse1' class='accordion-collapse collapse show' data-bs-parent='#faqAccordion'>
                                            <div class='accordion-body'>
                                                You can apply for a birth certificate by creating an account and submitting an online application. You'll need to provide required documents and pay the applicable fees.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class='accordion-item'>
                                        <h2 class='accordion-header' id='heading2'>
                                            <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse2'>
                                                How long does it take to process an application?
                                            </button>
                                        </h2>
                                        <div id='collapse2' class='accordion-collapse collapse' data-bs-parent='#faqAccordion'>
                                            <div class='accordion-body'>
                                                Processing times vary but typically take 5-10 business days for standard applications. Expedited processing is available for an additional fee.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class='accordion-item'>
                                        <h2 class='accordion-header' id='heading3'>
                                            <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse3'>
                                                How can I track my application?
                                            </button>
                                        </h2>
                                        <div id='collapse3' class='accordion-collapse collapse' data-bs-parent='#faqAccordion'>
                                            <div class='accordion-body'>
                                                You can track your application using the tracking number provided when you submitted your application. Visit the tracking page and enter your tracking number.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class='accordion-item'>
                                        <h2 class='accordion-header' id='heading4'>
                                            <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse4'>
                                                How do I verify a birth certificate?
                                            </button>
                                        </h2>
                                        <div id='collapse4' class='accordion-collapse collapse' data-bs-parent='#faqAccordion'>
                                            <div class='accordion-body'>
                                                You can verify a birth certificate by entering the certificate number on our verification page. This will confirm the authenticity of the document.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='mt-3'>
                    <a href='/' class='btn btn-primary'>Home</a>
                    <a href='/contact' class='btn btn-outline-primary'>Contact Us</a>
                </div>
            </div>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>";
    }
    
    /**
     * Privacy policy page
     */
    public function privacy()
    {
        $pageTitle = 'Privacy Policy - Digital Birth Certificate System';
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>$pageTitle</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row'>
                    <div class='col-md-10'>
                        <div class='card'>
                            <div class='card-header'>
                                <h1>Privacy Policy</h1>
                            </div>
                            <div class='card-body'>
                                <p><strong>Last updated:</strong> " . date('F j, Y') . "</p>
                                
                                <h3>Information We Collect</h3>
                                <p>We collect information you provide directly to us, such as when you create an account, submit an application, or contact us for support.</p>
                                
                                <h3>How We Use Your Information</h3>
                                <p>We use the information we collect to provide, maintain, and improve our services, process applications, and communicate with you.</p>
                                
                                <h3>Information Sharing</h3>
                                <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy.</p>
                                
                                <h3>Data Security</h3>
                                <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
                                
                                <h3>Contact Us</h3>
                                <p>If you have any questions about this Privacy Policy, please contact us at privacy@birthcertificate.gov</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='mt-3'>
                    <a href='/' class='btn btn-primary'>Home</a>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Terms of service page
     */
    public function terms()
    {
        $pageTitle = 'Terms of Service - Digital Birth Certificate System';
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>$pageTitle</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row'>
                    <div class='col-md-10'>
                        <div class='card'>
                            <div class='card-header'>
                                <h1>Terms of Service</h1>
                            </div>
                            <div class='card-body'>
                                <p><strong>Last updated:</strong> " . date('F j, Y') . "</p>
                                
                                <h3>Acceptance of Terms</h3>
                                <p>By accessing and using this service, you accept and agree to be bound by the terms and provision of this agreement.</p>
                                
                                <h3>Use License</h3>
                                <p>Permission is granted to temporarily use this service for personal, non-commercial transitory viewing only.</p>
                                
                                <h3>Disclaimer</h3>
                                <p>The materials on this website are provided on an 'as is' basis. We make no warranties, expressed or implied.</p>
                                
                                <h3>Limitations</h3>
                                <p>In no event shall the Digital Birth Certificate System be liable for any damages arising out of the use or inability to use the materials on this website.</p>
                                
                                <h3>Contact Information</h3>
                                <p>If you have any questions about these Terms of Service, please contact us at legal@birthcertificate.gov</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='mt-3'>
                    <a href='/' class='btn btn-primary'>Home</a>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * API documentation page
     */
    public function apiDocs()
    {
        $pageTitle = 'API Documentation - Digital Birth Certificate System';
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>$pageTitle</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row'>
                    <div class='col-md-10'>
                        <div class='card'>
                            <div class='card-header'>
                                <h1>API Documentation</h1>
                            </div>
                            <div class='card-body'>
                                <p class='lead'>API documentation for the Digital Birth Certificate System</p>
                                
                                <h3>Authentication</h3>
                                <p>All API requests require authentication using API keys or OAuth tokens.</p>
                                
                                <h3>Endpoints</h3>
                                <div class='table-responsive'>
                                    <table class='table table-striped'>
                                        <thead>
                                            <tr>
                                                <th>Method</th>
                                                <th>Endpoint</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>GET</td>
                                                <td>/api/certificates/verify/{id}</td>
                                                <td>Verify a certificate</td>
                                            </tr>
                                            <tr>
                                                <td>POST</td>
                                                <td>/api/applications</td>
                                                <td>Submit a new application</td>
                                            </tr>
                                            <tr>
                                                <td>GET</td>
                                                <td>/api/applications/{id}</td>
                                                <td>Get application details</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <h3>Rate Limiting</h3>
                                <p>API requests are limited to 100 requests per hour per API key.</p>
                                
                                <h3>Support</h3>
                                <p>For API support, contact api-support@birthcertificate.gov</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='mt-3'>
                    <a href='/' class='btn btn-primary'>Home</a>
                </div>
            </div>
        </body>
        </html>";
    }
}
