<?php

namespace TFD;

class Image extends Model
{

    public $postType = 'attachment';
    public $original;
    public $focalPoint;

    private $viewPath;


    public $virtual = [
        'name',
        'alt',
        'caption',
        'description',
        'href',
        'src',
        'originalSrc',
        'width',
        'height',
        'orientation',
        'fpx',
        'fpy',
        'mimeType',
        'originalSrc'
    ];

    public $sizeGroup;

    protected function boot()
    {
        if (!empty($this->ID)) {
            $this->original = $this->getOriginal();
            $this->focalPoint = $this->getFocalPoint();
            $this->viewPath = apply_filters('tfd_image_view_path', __DIR__ . '/image/views/');
        }
        parent::boot();
    }

    private function getOriginal()
    {
        $image = self::$dynamicResizeEnabled ? fly_get_attachment_image_src($this->ID, 'full') : wp_get_attachment_image_src($this->ID, 'full');
        if (isset($image) && is_array($image) && !empty($image)) {
            return (object)[
                'src' => self::$dynamicResizeEnabled ? $image['src'] : $image[0],
                'width' => self::$dynamicResizeEnabled ? $image['width'] : $image[1],
                'height' => self::$dynamicResizeEnabled ? $image['height'] : $image[2],
            ];
        }
        return null;
    }

    private function getFocalPoint()
    {
        $focalPoint = $this->getMeta('theiaSmartThumbnails_position');
        $x = isset($focalPoint) && !empty($focalPoint) ? $focalPoint[0] : .5;
        $y = isset($focalPoint) && !empty($focalPoint) ? $focalPoint[1] : .5;
        return (object)[
            'x' => $x,
            'y' => $y,
            'bg_pos' => $x * 100 . '%' . $y * 100 . '%',
            'bg_pos_x' => $x * 100 . '%',
            'bg_pos_y' => $y * 100 . '%',
        ];
    }

    public function draw($sizeGroup, $drawType)
    {
        switch ($drawType) {
            case 'figure':
                return $this->drawFigure();
            case 'picture':
                return $this->drawPicture();
            default:
                return $this->drawImage();
        }
    }

    public function drawFigure()
    {
        return $this->renderView('figure', [
            'src' => $this->src,
            'alt' => $this->alt,
            'width' => $this->width,
            'height' => $this->height,
            'sources' => [],
            'image' => $this,
        ]);
    }

    public function drawPicture()
    {
        return $this->renderView('picture', [
            'src' => $this->src,
            'alt' => $this->alt,
            'width' => $this->width,
            'height' => $this->height,
            'sources' => [],
            'image' => $this,
        ]);
    }

    public function drawImage()
    {
        return $this->renderView('image', [
            'src' => $this->src,
            'alt' => $this->alt,
            'width' => $this->width,
            'height' => $this->height,
            'image' => $this,
        ]);
    }

    private function renderView($view, $params = [])
    {
        extract($params);
        $view = str_replace('.', '/', $view);
        $path = $this->viewPath . $view . '.php';
        return  file_exists($path) ? include($path) : '';
    }

    // ----------------------------------------------------
    // PROPERTIES
    // ---------------------------------------------------
    public function _getOrginalSrc()
    {
        return wp_get_attachment_url($this->ID);
    }

    public function _getMimeType()
    {
        return get_post_mime_type($this->ID);
    }

    public function _getAlt()
    {
        return $this->getMeta('_wp_attachment_image_alt');
    }

    public function _getName()
    {
        return $this->title;
    }

    public function _getCaption()
    {
        return $this->_post->post_excerpt ?: $this->_post->post_content;
    }

    public function _getDescription()
    {
        return $this->_post->post_content;
    }

    public function _getHref()
    {
        return get_permalink($this->ID);
    }

    public function _getWidth()
    {
        return $this->original ? $this->original->width : 0;
    }

    public function _getHeight()
    {
        return $this->original ? $this->original->height : 0;
    }

    public function _getOrientation()
    {
        if ($this->width < $this->height) {
            return 'portrait';
        } elseif ($this->width == $this->height) {
            return 'square';
        }
        return 'landscape';
    }

    public function _getFpx()
    {
        return $this->focalPoint->x;
    }

    public function _getFpy()
    {
        return $this->focalPoint->y;
    }




    // ----------------------------------------------------
    // STATIC METHODS
    // ----------------------------------------------------
    public static function isDynamicResizeEnabled()
    {
        return function_exists('fly_get_attachment_image_src');
    }

    public static function isCloudinaryEnabled()
    {
        return function_exists('cloudinary_url');
    }

    public static function isSVG($id)
    {
        return is_numeric($id) ? get_post_mime_type((int)$id) === 'image/svg+xml' : false;
    }

    public static function isJPG($id)
    {
        return is_numeric($id) ? get_post_mime_type((int)$id) === 'image/jpeg' : false;
    }

    public static function isPNG($id)
    {
        return is_numeric($id) ? get_post_mime_type((int)$id) === 'image/png' : false;
    }

    public static function isGIF($id)
    {
        return is_numeric($id) ? get_post_mime_type((int)$id) === 'image/gif' : false;
    }

    public static function isImage($id)
    {
        if (!is_numeric($id)) {
            return false;
        }
        switch (get_post_mime_type((int)$id)) {
            case 'image/svg+xml':
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
            case 'image/webp':
                return true;

            default:
                return false;
        }
    }


    /**
     * Find featured image model by it's post ID
     *
     * @param  int $ID
     * @return Object|NULL
     */
    public static function findFeaturedImage($id)
    {
        return has_post_thumbnail($id) ? Image::find((int)get_post_thumbnail_id($id)) : null;
    }
}
