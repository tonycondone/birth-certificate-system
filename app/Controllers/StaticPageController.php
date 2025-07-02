<?php
namespace App\Controllers;

class StaticPageController
{
    public function about()    { include BASE_PATH . '/resources/views/about.php'; }
    
    public function contact()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processContactForm();
        } else {
            $this->showContactForm();
        }
    }
    
    private function showContactForm($errors = [], $old = [])
    {
        $pageTitle = 'Contact Us - Digital Birth Certificate System';
        include BASE_PATH . '/resources/views/contact.php';
    }

    private function processContactForm()
    {
        $errors = [];
        $old = $_POST;

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name)) {
            $errors['name'] = 'Name is required.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        }

        if (empty($subject)) {
            $errors['subject'] = 'Subject is required.';
        }

        if (empty($message)) {
            $errors['message'] = 'Message is required.';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = 'Please fix the errors below.';
            return $this->showContactForm($errors, $old);
        }

        // Simulate sending an email
        error_log("Contact form submitted: From: $name <$email>, Subject: $subject, Message: $message");

        $_SESSION['success'] = 'Thank you for your message! We will get back to you shortly.';
        header('Location: /contact');
        exit;
    }

    public function faq()      { include BASE_PATH . '/resources/views/faq.php'; }
    public function privacy()  { include BASE_PATH . '/resources/views/privacy.php'; }
    public function terms()    { include BASE_PATH . '/resources/views/terms.php'; }
    public function apiDocs()  { include BASE_PATH . '/resources/views/api-docs.php'; }
} 