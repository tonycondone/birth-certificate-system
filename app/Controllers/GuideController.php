<?php

namespace App\Controllers;

/**
 * GuideController
 * 
 * Handles user guide and help documentation
 */
class GuideController
{
    /**
     * Display the user guide home page
     */
    public function index()
    {
        $pageTitle = 'User Guide - Digital Birth Certificate System';
        
        // Include the guide view
        include BASE_PATH . '/resources/views/guide/index.php';
    }
    
    /**
     * Display specific guide section
     */
    public function section($section)
    {
        // Define valid guide sections
        $validSections = [
            'applications',
            'certificates',
            'verification',
            'payment',
            'tracking',
            'faq'
        ];
        
        // Check if requested section is valid
        if (!in_array($section, $validSections)) {
            header('Location: /guide');
            exit;
        }
        
        // Set page title based on section
        $titles = [
            'applications' => 'Application Process Guide',
            'certificates' => 'Birth Certificate Guide',
            'verification' => 'Certificate Verification Guide',
            'payment' => 'Payment Process Guide',
            'tracking' => 'Application Tracking Guide',
            'faq' => 'Frequently Asked Questions'
        ];
        
        $pageTitle = $titles[$section] . ' - Digital Birth Certificate System';
        
        // Include the section-specific view
        include BASE_PATH . '/resources/views/guide/' . $section . '.php';
    }
    
    /**
     * Display tutorial guide
     */
    public function tutorial($topic)
    {
        // Define valid tutorial topics
        $validTopics = [
            'new-application',
            'tracking-status',
            'certificate-verification',
            'payment-process'
        ];
        
        // Check if requested topic is valid
        if (!in_array($topic, $validTopics)) {
            header('Location: /guide');
            exit;
        }
        
        // Set page title based on topic
        $titles = [
            'new-application' => 'How to Submit a New Application',
            'tracking-status' => 'How to Track Application Status',
            'certificate-verification' => 'How to Verify a Certificate',
            'payment-process' => 'How to Complete Payment'
        ];
        
        $pageTitle = $titles[$topic] . ' - Tutorial';
        
        // Include the tutorial view
        include BASE_PATH . '/resources/views/guide/tutorials/' . $topic . '.php';
    }
    
    /**
     * Display video help
     */
    public function video($id)
    {
        // Define valid video IDs and their information
        $videos = [
            'application-walkthrough' => [
                'title' => 'Application Walkthrough',
                'url' => 'https://example.com/videos/application-walkthrough.mp4',
                'description' => 'Step-by-step walkthrough of the birth certificate application process'
            ],
            'certificate-validation' => [
                'title' => 'Certificate Validation',
                'url' => 'https://example.com/videos/certificate-validation.mp4',
                'description' => 'How to validate the authenticity of a birth certificate'
            ]
        ];
        
        // Check if requested video exists
        if (!isset($videos[$id])) {
            header('Location: /guide');
            exit;
        }
        
        $video = $videos[$id];
        $pageTitle = $video['title'] . ' - Video Tutorial';
        
        // Include the video view
        include BASE_PATH . '/resources/views/guide/video.php';
    }
    
    /**
     * Display support resources
     */
    public function support()
    {
        $pageTitle = 'Support Resources - Digital Birth Certificate System';
        
        // Define support resources
        $supportResources = [
            [
                'title' => 'Contact Support',
                'icon' => 'fa-envelope',
                'description' => 'Get in touch with our support team for personalized assistance',
                'link' => '/contact'
            ],
            [
                'title' => 'FAQ',
                'icon' => 'fa-question-circle',
                'description' => 'Browse our frequently asked questions',
                'link' => '/guide/section/faq'
            ],
            [
                'title' => 'Video Tutorials',
                'icon' => 'fa-video-camera',
                'description' => 'Watch step-by-step video guides',
                'link' => '/guide/videos'
            ],
            [
                'title' => 'User Guide',
                'icon' => 'fa-book',
                'description' => 'Read our comprehensive user guide',
                'link' => '/guide'
            ]
        ];
        
        // Include the support view
        include BASE_PATH . '/resources/views/guide/support.php';
    }
    
    /**
     * List available video tutorials
     */
    public function videos()
    {
        $pageTitle = 'Video Tutorials - Digital Birth Certificate System';
        
        // Define available videos
        $videos = [
            [
                'id' => 'application-walkthrough',
                'title' => 'Application Walkthrough',
                'description' => 'Step-by-step walkthrough of the birth certificate application process',
                'thumbnail' => '/images/videos/application-thumb.jpg',
                'duration' => '5:23'
            ],
            [
                'id' => 'certificate-validation',
                'title' => 'Certificate Validation',
                'description' => 'How to validate the authenticity of a birth certificate',
                'thumbnail' => '/images/videos/validation-thumb.jpg',
                'duration' => '3:47'
            ],
            [
                'id' => 'payment-process',
                'title' => 'Payment Process',
                'description' => 'Guide to completing the payment for your birth certificate',
                'thumbnail' => '/images/videos/payment-thumb.jpg',
                'duration' => '4:12'
            ],
            [
                'id' => 'tracking-application',
                'title' => 'Tracking Your Application',
                'description' => 'How to track the status of your birth certificate application',
                'thumbnail' => '/images/videos/tracking-thumb.jpg',
                'duration' => '2:58'
            ]
        ];
        
        // Include the videos listing view
        include BASE_PATH . '/resources/views/guide/videos.php';
    }
} 