<?php

namespace App\Services;

class SeoService
{
    private array $meta = [];
    private array $og = [];
    private array $twitter = [];
    private array $structuredData = [];
    private string $title = '';
    private string $description = '';
    private string $canonicalUrl = '';

    /**
     * Set basic meta information
     */
    public function setMeta(string $title, string $description, string $canonicalUrl = ''): self
    {
        $this->title = $title;
        $this->description = $description;
        $this->canonicalUrl = $canonicalUrl ?: $this->getCurrentUrl();

        $this->meta = [
            'title' => $title,
            'description' => $description,
            'robots' => 'index, follow',
            'viewport' => 'width=device-width, initial-scale=1.0',
            'charset' => 'UTF-8'
        ];

        // Set OpenGraph
        $this->og = [
            'title' => $title,
            'description' => $description,
            'url' => $this->canonicalUrl,
            'type' => 'website',
            'site_name' => 'Digital Birth Certificate System'
        ];

        // Set Twitter Cards
        $this->twitter = [
            'card' => 'summary_large_image',
            'title' => $title,
            'description' => $description
        ];

        return $this;
    }

    /**
     * Set page-specific meta tags
     */
    public function addMeta(string $name, string $content): self
    {
        $this->meta[$name] = $content;
        return $this;
    }

    /**
     * Set OpenGraph data
     */
    public function setOg(string $property, string $content): self
    {
        $this->og[$property] = $content;
        return $this;
    }

    /**
     * Set Twitter Card data
     */
    public function setTwitter(string $name, string $content): self
    {
        $this->twitter[$name] = $content;
        return $this;
    }

    /**
     * Add structured data
     */
    public function addStructuredData(array $data): self
    {
        $this->structuredData[] = $data;
        return $this;
    }

    /**
     * Set breadcrumbs structured data
     */
    public function setBreadcrumbs(array $items): self
    {
        $breadcrumbList = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];

        foreach ($items as $position => $item) {
            $breadcrumbList['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'item' => [
                    '@id' => $item['url'],
                    'name' => $item['title']
                ]
            ];
        }

        $this->addStructuredData($breadcrumbList);
        return $this;
    }

    /**
     * Set organization structured data
     */
    public function setOrganization(): self
    {
        $org = [
            '@context' => 'https://schema.org',
            '@type' => 'GovernmentOrganization',
            'name' => 'Digital Birth Certificate System',
            'url' => $this->getCurrentUrl(),
            'logo' => $this->getCurrentUrl() . '/images/logo.png',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+1-234-567-8900',
                'contactType' => 'customer service',
                'areaServed' => 'US',
                'availableLanguage' => ['English']
            ]
        ];

        $this->addStructuredData($org);
        return $this;
    }

    /**
     * Generate HTML meta tags
     */
    public function generateMetaTags(): string
    {
        $html = '';

        // Basic meta tags
        foreach ($this->meta as $name => $content) {
            $html .= sprintf('<meta name="%s" content="%s">' . PHP_EOL, 
                htmlspecialchars($name), 
                htmlspecialchars($content)
            );
        }

        // Canonical URL
        if ($this->canonicalUrl) {
            $html .= sprintf('<link rel="canonical" href="%s">' . PHP_EOL, 
                htmlspecialchars($this->canonicalUrl)
            );
        }

        // OpenGraph tags
        foreach ($this->og as $property => $content) {
            $html .= sprintf('<meta property="og:%s" content="%s">' . PHP_EOL,
                htmlspecialchars($property),
                htmlspecialchars($content)
            );
        }

        // Twitter Card tags
        foreach ($this->twitter as $name => $content) {
            $html .= sprintf('<meta name="twitter:%s" content="%s">' . PHP_EOL,
                htmlspecialchars($name),
                htmlspecialchars($content)
            );
        }

        return $html;
    }

    /**
     * Generate structured data script tags
     */
    public function generateStructuredData(): string
    {
        if (empty($this->structuredData)) {
            return '';
        }

        $html = '';
        foreach ($this->structuredData as $data) {
            $html .= sprintf(
                '<script type="application/ld+json">%s</script>' . PHP_EOL,
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        return $html;
    }

    /**
     * Get current URL
     */
    private function getCurrentUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Get page title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get meta description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set default meta tags for error pages
     */
    public function setErrorPage(int $code): self
    {
        $title = match ($code) {
            404 => 'Page Not Found',
            403 => 'Access Denied',
            500 => 'Server Error',
            default => 'Error'
        };

        $description = match ($code) {
            404 => 'The requested page could not be found.',
            403 => 'You do not have permission to access this page.',
            500 => 'An internal server error occurred.',
            default => 'An error occurred while processing your request.'
        };

        $this->setMeta(
            "$title - Digital Birth Certificate System",
            $description
        );

        $this->addMeta('robots', 'noindex, nofollow');

        return $this;
    }

    /**
     * Set meta tags for certificate verification page
     */
    public function setCertificateVerificationPage(string $certificateId = ''): self
    {
        $title = 'Verify Birth Certificate';
        $description = 'Verify the authenticity of a birth certificate using our secure verification system.';

        if ($certificateId) {
            $title .= " #$certificateId";
            $description = "Verify the authenticity of birth certificate #$certificateId using our secure verification system.";
        }

        $this->setMeta($title, $description)
            ->addStructuredData([
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $title,
                'description' => $description,
                'provider' => [
                    '@type' => 'GovernmentOrganization',
                    'name' => 'Digital Birth Certificate System'
                ]
            ]);

        return $this;
    }
}