<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

class TinypngPlugin extends Plugin
{
    protected $tinypng;

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

            // Compress each image
            foreach ($images as $image) {
                $this->compressImage($image);
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
}
