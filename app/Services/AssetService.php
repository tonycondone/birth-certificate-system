<?php

namespace App\Services;

class AssetService
{
    private string $publicPath;
    private array $manifestCache = [];
    private CacheService $cache;
    private array $deferredScripts = [];
    private array $preloadAssets = [];

    public function __construct(string $publicPath = null, CacheService $cache = null)
    {
        $this->publicPath = $publicPath ?? __DIR__ . '/../../public';
        $this->cache = $cache ?? new CacheService();
    }

    /**
     * Get versioned asset path from manifest
     */
    public function asset(string $path): string
    {
        $manifestPath = $this->publicPath . '/mix-manifest.json';
        
        if (empty($this->manifestCache)) {
            if (file_exists($manifestPath)) {
                $this->manifestCache = json_decode(file_get_contents($manifestPath), true) ?? [];
            }
        }

        $key = '/' . ltrim($path, '/');
        return $this->manifestCache[$key] ?? $path;
    }

    /**
     * Generate image tag with lazy loading and optimization
     */
    public function image(
        string $src,
        string $alt,
        array $attributes = [],
        bool $lazy = true
    ): string {
        $width = $attributes['width'] ?? null;
        $height = $attributes['height'] ?? null;
        $class = $attributes['class'] ?? '';
        
        // Generate srcset for responsive images
        $srcset = '';
        if (!empty($attributes['responsive'])) {
            $srcset = $this->generateSrcSet($src);
        }

        $attributesStr = '';
        foreach ($attributes as $key => $value) {
            if (!in_array($key, ['width', 'height', 'class', 'responsive'])) {
                $attributesStr .= sprintf(' %s="%s"', $key, htmlspecialchars($value));
            }
        }

        return sprintf(
            '<img src="%s"%s alt="%s"%s%s%s%s class="%s"%s>',
            $this->asset($src),
            $srcset ? ' srcset="' . $srcset . '"' : '',
            htmlspecialchars($alt),
            $width ? ' width="' . $width . '"' : '',
            $height ? ' height="' . $height . '"' : '',
            $lazy ? ' loading="lazy"' : '',
            $lazy ? ' decoding="async"' : '',
            'img-fluid ' . $class,
            $attributesStr
        );
    }

    /**
     * Generate script tag with defer/async options
     */
    public function script(
        string $src,
        bool $defer = true,
        bool $async = false,
        array $attributes = []
    ): string {
        $path = $this->asset($src);
        
        if ($defer) {
            $this->deferredScripts[] = [
                'src' => $path,
                'async' => $async,
                'attributes' => $attributes
            ];
            return '';
        }

        $attributesStr = '';
        foreach ($attributes as $key => $value) {
            $attributesStr .= sprintf(' %s="%s"', $key, htmlspecialchars($value));
        }

        return sprintf(
            '<script src="%s"%s%s%s></script>',
            $path,
            $defer ? ' defer' : '',
            $async ? ' async' : '',
            $attributesStr
        );
    }

    /**
     * Get all deferred scripts
     */
    public function getDeferredScripts(): string
    {
        $html = '';
        foreach ($this->deferredScripts as $script) {
            $attributesStr = '';
            foreach ($script['attributes'] as $key => $value) {
                $attributesStr .= sprintf(' %s="%s"', $key, htmlspecialchars($value));
            }

            $html .= sprintf(
                '<script src="%s" defer%s%s></script>',
                $script['src'],
                $script['async'] ? ' async' : '',
                $attributesStr
            );
        }
        return $html;
    }

    /**
     * Add preload hint for critical assets
     */
    public function preload(string $path, string $as, array $attributes = []): void
    {
        $this->preloadAssets[] = [
            'path' => $this->asset($path),
            'as' => $as,
            'attributes' => $attributes
        ];
    }

    /**
     * Get all preload tags
     */
    public function getPreloadTags(): string
    {
        $html = '';
        foreach ($this->preloadAssets as $asset) {
            $attributesStr = '';
            foreach ($asset['attributes'] as $key => $value) {
                $attributesStr .= sprintf(' %s="%s"', $key, htmlspecialchars($value));
            }

            $html .= sprintf(
                '<link rel="preload" href="%s" as="%s"%s>',
                $asset['path'],
                $asset['as'],
                $attributesStr
            );
        }
        return $html;
    }

    /**
     * Generate srcset for responsive images
     */
    private function generateSrcSet(string $src): string
    {
        $sizes = [320, 480, 768, 1024, 1920];
        $srcset = [];
        
        foreach ($sizes as $size) {
            $resizedSrc = $this->getResizedImagePath($src, $size);
            if ($resizedSrc) {
                $srcset[] = sprintf('%s %dw', $resizedSrc, $size);
            }
        }

        return implode(', ', $srcset);
    }

    /**
     * Get resized image path
     */
    private function getResizedImagePath(string $src, int $width): ?string
    {
        $pathInfo = pathinfo($src);
        $newPath = sprintf(
            '%s/%s-%d.%s',
            $pathInfo['dirname'],
            $pathInfo['filename'],
            $width,
            $pathInfo['extension']
        );

        if (file_exists($this->publicPath . '/' . ltrim($newPath, '/'))) {
            return $this->asset($newPath);
        }

        return null;
    }

    /**
     * Inline critical CSS
     */
    public function inlineCriticalCss(string $path): string
    {
        $cacheKey = 'critical_css_' . md5($path);
        
        return $this->cache->remember($cacheKey, function() use ($path) {
            $cssPath = $this->publicPath . '/' . ltrim($this->asset($path), '/');
            if (file_exists($cssPath)) {
                return sprintf(
                    '<style>%s</style>',
                    file_get_contents($cssPath)
                );
            }
            return '';
        }, 3600);
    }

    /**
     * Generate integrity hash for asset
     */
    public function integrity(string $path): string
    {
        $fullPath = $this->publicPath . '/' . ltrim($this->asset($path), '/');
        if (file_exists($fullPath)) {
            return 'sha384-' . base64_encode(hash_file('sha384', $fullPath, true));
        }
        return '';
    }
}