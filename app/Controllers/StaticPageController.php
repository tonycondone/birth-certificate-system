<?php
namespace App\Controllers;

class StaticPageController
{
    public function about()    { include __DIR__ . '/../../resources/views/about.php'; }
    public function contact()  { include __DIR__ . '/../../resources/views/contact.php'; }
    public function faq()      { include __DIR__ . '/../../resources/views/faq.php'; }
    public function privacy()  { include __DIR__ . '/../../resources/views/privacy.php'; }
    public function terms()    { include __DIR__ . '/../../resources/views/terms.php'; }
    public function apiDocs()  { include __DIR__ . '/../../resources/views/api-docs.php'; }
} 