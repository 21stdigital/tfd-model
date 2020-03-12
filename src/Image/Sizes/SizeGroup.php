<?php

namespace TFD\Image\Sizes;

use TFD;

class SizeGroup
{
    // Color
    // https://cloudinary.com/documentation/image_transformations#color_effects
    public $effect = null; // grayscale

    // Gravity
    // https://cloudinary.com/documentation/image_transformations#control_gravity
    public $gravity = 'auto'; // g_xy_center g_face:auto
    // Resizing and cropping images
    // https://cloudinary.com/documentation/image_transformations#resizing_and_cropping_images
    public $cropMode = 'fill'; //'fit limit

    // https://cloudinary.com/documentation/image_transformations#automatic_format_selection
    public $fetchFormat = 'auto';

    // https://cloudinary.com/cookbook/applying_rounded_corners_to_an_image
    public $radius = null;

    // Automatic pixel density detection
    // https://cloudinary.com/documentation/responsive_images#automatic_pixel_density_detection
    public $drpAuto = true;

    public $quality = 'auto'; //'80';
    public $formatTypes = ['jpg'];
    public $unit = 'px';

    public $dpr = [1,2];
    public $artDirection = true;

    public $imgSrcset = [
        1024,
        640,
        320,
    ];

    public $imgSizes = [
        '(min-width: 26em) 33.3vw',
        '100vw',
    ];

    public $detailedSources = [
        [
            'media' => '(min-width: 36em)',
            'srcset' => [
                [720, 540],
                [640, 480],
                [320, 240],
            ],
            'sizes' => [
                "50vw"
            ],
        ],
        [
            'media' => '',
            'srcset' => [720, 720],
            'sizes' => '',
        ],
    ];

    public $sources =  [
        [0, 375, 256, 'max'],
        [576, 360, 360, 'max'],
        [768, 420, 420, 'max'],
        [992, 560, 560, 'max'],
        [1200, 620, 620, 'min'],
        ['default', 620, 620],
    ];

    public function replaceExtension($filename, $newExtension)
    {
        $info = pathinfo($filename);
        return $info['dirname'] . '/' . $info['filename'] . '.' . $newExtension;
    }


    public static function dprNameSuffix($dpr)
    {
        return '@' . $dpr . 'x';
    }

    public static function dprMediaSuffix($dpr)
    {
        return $dpr . 'x';
    }


    public function isCrop()
    {
        return $this->cropMode && $this->cropMode != 'fit' ? true : false;
    }

    public function getTransformations($w, $h)
    {
        $transformation = [
            'width' => $w,
            'height' => $h,
            'crop' => $this->cropMode ?: 'fit',
            'quality' => $this->quality,
            'fetch_format' => $this->fetchFormat,
        ];

        if ($this->effect) {
            $transformation['effect'] = $this->effect;
        }

        if ($this->isCrop()) {
            $transformation['gravity'] = $this->gravity;
        }
        return $transformation;
    }

    public function getSource($id, $w, $h, $type = 'jpg')
    {
        $transformations = $this->getTransformations($w, $h);
        // Cloudinary
        if (!is_admin() && TFD\Image::isCloudinaryEnabled()) {
            $imageUrl = cloudinary_url($id, [
                'transform' => $transformations,
            ]);

            if ($imageUrl && false === strpos($imageUrl, get_home_url())) {
                $res = (object)[
                    'url' => $this->fetchFormat === 'auto' ? $imageUrl : $this->replaceExtension($imageUrl, $type),
                    'width' => $w,
                    'height' => $h,
                ];
                if (WP_ENV !== 'production') {
                    $live_domain = 'https://www.electronicbeats.net';
                    $len = strlen($live_domain);
                    $is_live_resource = (substr(get_the_guid($id), 0, $len) === $live_domain);
                    if ($is_live_resource) {
                        $url = str_ireplace('http://teb.localhost', $live_domain, $res->url);
                        $url = str_ireplace('https://res.cloudinary.com/tfd/', 'https://res.cloudinary.com/electronic-beats/', $url);
                        $url = str_ireplace('/teb-local/', '/stage/uploads/', $url);
                        $res->url = $url;
                    }
                }
                return $res;
            }
        }

        // FLY DYNAMIC RESIZER
        if (function_exists('fly_get_attachment_image_src')) {
            $crop = $this->isCrop();
            $image = fly_get_attachment_image_src($id, [$w, $h], $crop);

            if (isset($image) && !empty($image)) {
                return (object)[
                    'url' => $image['src'],
                    'width' => $image['width'],
                    'height' => $image['height'],
                ];
            }
        }

        // DEFAULT WORDPRES
        $image = wp_get_attachment_image_src($id, [$w, $h]);
        if (isset($image) && !empty($image)) {
            return (object)[
                'url' => $image[0],
                'width' => $image[1],
                'height' => $image[2],
            ];
        }

        return null;
    }

    public function getSrcset($id, $w, $h, $type = 'jpg', $descriptor = "dpr")
    {
        switch ($descriptor) {
            case 'width':
                $src = $this->getSource($id, $w, $h, $type);
                return "{$src->url} {$w}w";

            default:
                return array_map(
                    function ($dpr) use ($id, $w, $h, $type) {
                        $src = $this->getSource($id, $w*$dpr, $h*$dpr, $type);
                        $dprSuffix = $this->dprMediaSuffix($dpr);
                        return "{$src->url} {$dprSuffix}";
                    },
                    $this->dpr
                );
        }
    }

    // private function arrayFlatten($array = null)
    // {
    //     $result = [];
    //     if (!is_array($array)) {
    //         $array = func_get_args();
    //     }

    //     foreach ($array as $key => $value) {
    //         if (is_array($value)) {
    //             $result = array_merge($result, $this->arrayFlatten($value));
    //         } else {
    //             $result = array_merge($result, array($key => $value));
    //         }
    //     }
    //     return $result;
    // }


    public function parseSources($id)
    {
        $res = [];
        foreach ($this->sources as $source) {
            $mediaDirection = count($source) > 3 ? $source[3] : 'max';
            $media = "({$mediaDirection}-width: {$source[0]}px)";
            $sizes = '';
            $formatTypes =  $this->formatTypes;
            if ($this->fetchFormat === ' auto') {
                $info = pathinfo(wp_get_attachment_url($id));
                $extension = $info['extension'];
                $formatTypes = [$extension];
            }
            foreach ($formatTypes as $type) {
                $srcset = $this->getSrcset($id, $source[1], $source[2], $type);
                $res[] = [
                    'media' => $media,
                    'sizes' => $sizes,
                    'type' => TFD\Image::toMimeType($type),
                    'srcset' => $srcset,
                ];
            }
        }
        return $res;
    }


    public function parseDetailedSources($id)
    {
        $res = [];
        foreach ($this->detailedSources as $source) {
            $media = $source['media'];
            $sizes = $source['sizes'];
            foreach ($this->formatTypes as $type) {
                $srcset = [];
                if ($sizes && count($source['srcset']) && is_array($source['srcset'][0])) {
                    foreach ($source['srcset'] as $srcet) {
                        $srcset[] = $this->getSrcset($id, $srcet[0], $srcet[1], $type, 'width');
                    }
                } else {
                    list($width, $height) = $source['srcset'];
                    $srcset = $this->getSrcset($id, $width, $height, $type);
                }
                $res[] = [
                    'media' => $media,
                    'sizes' => $sizes,
                    'type' => TFD\Image::toMimeType($type),
                    'srcset' => $srcset,
                ];
            }
        }
        return $res;
    }


    public function getSources($id)
    {
        if (is_int($id)) {
            if ($this->sources) {
                return $this->parseSources($id);
            }

            if ($this->detailedSources) {
                return $this->parseDetailedSources($id);
            }
        }
    }


    // /**
    //  * Get current instance.
    //  *
    //  * @return object
    //  */
    // public static function getInstance()
    // {
    //     if (!self::$_instance) {
    //         self::$_instance = new self();
    //     }
    //     return self::$_instance;
    // }


    public function setup()
    {
        if (!TFD\Image::isCloudinaryEnabled()) {
            $this->addImageSizes();
        }
    }

    private function addImageSizes()
    {
        $sources = [];
        if ($this->sources) {
            $sources = $this->sources;
        } elseif ($this->detailedSources) {
            $sources = array_merge(array_map(
                function ($source) {
                    return array_map(
                        function ($size) {
                            $name = "$size[0]x$size[1]";
                            return array_merge([$name], $size);
                        },
                        $source['srcset']
                    );
                },
                $this->detailedSources
            ));
        }

        foreach ($sources as $source) {
            if (count($source) < 3) {
                break;
            }
            $breakpoint = $source[0];
            $name = get_class($this) . $breakpoint;
            $w = $source[1];
            $h = $source[2];
            $crop = $this->isCrop();

            foreach ($this->dpr as $dpr) {
                if (function_exists('fly_add_image_size')) {
                    fly_add_image_size($name . self::dprNameSuffix($dpr), $w * $dpr, $h * $dpr, $crop);
                } else {
                    add_image_size($name . self::dprNameSuffix($dpr), $w * $dpr, $h * $dpr, $crop);
                }
            }
        }
    }
}
