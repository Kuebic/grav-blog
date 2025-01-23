<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

class TinypngPlugin extends Plugin
{
    protected $tinypng;
    protected $processedImages = [];

    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onPageContentProcessed' => ['onPageContentProcessed', 0]
        ];
    }

    /**
     * Activate plugin if path matches to the configured one.
     */
    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }
        require_once __DIR__ . '/classes/tinypng.php';
        $this->tinypng = new Tinypng($this->config->get('plugins.tinypng.api_key'));
        $this->enable([
            'onImageMediumSaved' => ['onImageMediumSaved', 0],
        ]);

        // Load the list of already processed images
        $this->loadProcessedImages();
    }

    public function onImageMediumSaved(Event $event)
    {
        $path = $event['image'];
        $this->compressImage($path);
    }

    public function onPageContentProcessed(Event $event)
    {
        $page = $event['page'];

        // Check if the page is a blog post
        if ($page->isPage() && $page->template() == 'post') {
            // Get the folder path for the post
            $folderPath = $page->path();

            // Scan the folder for images
            $images = $this->scanFolderForImages($folderPath);

            // Compress each image (only if not already processed)
            foreach ($images as $image) {
                $relativePath = $this->getRelativePath($image);
                if (!$this->isImageProcessed($relativePath)) {
                    $this->compressImage($image);
                    $this->markImageAsProcessed($relativePath);
                }
            }
        }
    }

    protected function scanFolderForImages($folderPath)
    {
        $images = [];
        if (is_dir($folderPath)) {
            $files = scandir($folderPath);
            foreach ($files as $file) {
                if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                    $images[] = $folderPath . '/' . $file;
                }
            }
        }
        return $images;
    }

    protected function compressImage($path)
    {
        if (file_exists($path)) {
            $result = $this->tinypng->tinify($path);
            if (!empty($result)) {
                file_put_contents($path, $result);
            }
        }
    }

    protected function loadProcessedImages()
    {
        $cacheFile = $this->getCacheFilePath();
        if (file_exists($cacheFile)) {
            $this->processedImages = json_decode(file_get_contents($cacheFile), true) ?? [];
        }
    }

    protected function isImageProcessed($relativePath)
    {
        return in_array($relativePath, $this->processedImages);
    }

    protected function markImageAsProcessed($relativePath)
    {
        if (!$this->isImageProcessed($relativePath)) {
            $this->processedImages[] = $relativePath;
            $this->saveProcessedImages();
        }
    }

    protected function saveProcessedImages()
    {
        $cacheFile = $this->getCacheFilePath();
        file_put_contents($cacheFile, json_encode($this->processedImages, JSON_PRETTY_PRINT));
    }

    protected function getCacheFilePath()
    {
        return __DIR__ . '/processed_images.json';
    }

    protected function getRelativePath($absolutePath)
    {
        // Get the Grav root directory
        $gravRoot = $this->grav['locator']->findResource('');

        // Convert the absolute path to a relative path
        return ltrim(str_replace($gravRoot, '', $absolutePath), '/');
    }

    protected function getAbsolutePath($relativePath)
    {
        // Get the Grav root directory
        $gravRoot = $this->grav['locator']->findResource('');

        // Convert the relative path back to an absolute path
        return $gravRoot . '/' . $relativePath;
    }
}
