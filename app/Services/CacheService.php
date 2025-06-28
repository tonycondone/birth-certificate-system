<?php

namespace App\Services;

class CacheService
{
    private string $cachePath;
    private int $defaultTtl;
    private array $memoryCache = [];

    public function __construct(string $cachePath = null, int $defaultTtl = 3600)
    {
        $this->cachePath = $cachePath ?? __DIR__ . '/../../storage/cache';
        $this->defaultTtl = $defaultTtl;

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Get item from cache
     */
    public function get(string $key, $default = null)
    {
        // Check memory cache first
        if (isset($this->memoryCache[$key])) {
            $item = $this->memoryCache[$key];
            if ($item['expires'] > time()) {
                return $item['value'];
            }
            unset($this->memoryCache[$key]);
        }

        $path = $this->getFilePath($key);
        if (!file_exists($path)) {
            return $default;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return $default;
        }

        $data = json_decode($content, true);
        if (!$data || !isset($data['expires']) || !isset($data['value'])) {
            @unlink($path);
            return $default;
        }

        if ($data['expires'] <= time()) {
            @unlink($path);
            return $default;
        }

        // Store in memory cache
        $this->memoryCache[$key] = $data;
        return $data['value'];
    }

    /**
     * Store item in cache
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        // Store in memory cache
        $this->memoryCache[$key] = $data;

        // Store in file cache
        $path = $this->getFilePath($key);
        return file_put_contents($path, json_encode($data)) !== false;
    }

    /**
     * Remove item from cache
     */
    public function delete(string $key): bool
    {
        unset($this->memoryCache[$key]);
        $path = $this->getFilePath($key);
        if (file_exists($path)) {
            return @unlink($path);
        }
        return true;
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        $this->memoryCache = [];
        $files = glob($this->cachePath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        return true;
    }

    /**
     * Get or compute cache item
     */
    public function remember(string $key, callable $callback, int $ttl = null)
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    /**
     * Cache page content
     */
    public function cachePage(string $url, string $content, int $ttl = null): bool
    {
        $key = 'page_' . md5($url);
        return $this->set($key, $content, $ttl);
    }

    /**
     * Get cached page
     */
    public function getPage(string $url)
    {
        $key = 'page_' . md5($url);
        return $this->get($key);
    }

    /**
     * Cache API response
     */
    public function cacheApiResponse(string $endpoint, array $params, $response, int $ttl = null): bool
    {
        $key = 'api_' . md5($endpoint . serialize($params));
        return $this->set($key, $response, $ttl);
    }

    /**
     * Get cached API response
     */
    public function getApiResponse(string $endpoint, array $params)
    {
        $key = 'api_' . md5($endpoint . serialize($params));
        return $this->get($key);
    }

    /**
     * Cache database query result
     */
    public function cacheQuery(string $query, array $params, $result, int $ttl = null): bool
    {
        $key = 'query_' . md5($query . serialize($params));
        return $this->set($key, $result, $ttl);
    }

    /**
     * Get cached query result
     */
    public function getQueryResult(string $query, array $params)
    {
        $key = 'query_' . md5($query . serialize($params));
        return $this->get($key);
    }

    /**
     * Clean expired cache items
     */
    public function cleanup(): void
    {
        $files = glob($this->cachePath . '/*');
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $content = @file_get_contents($file);
            if ($content === false) {
                @unlink($file);
                continue;
            }

            $data = json_decode($content, true);
            if (!$data || !isset($data['expires']) || $data['expires'] <= time()) {
                @unlink($file);
            }
        }

        // Clean memory cache
        foreach ($this->memoryCache as $key => $item) {
            if ($item['expires'] <= time()) {
                unset($this->memoryCache[$key]);
            }
        }
    }

    /**
     * Get cache file path for key
     */
    private function getFilePath(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.cache';
    }
}