<?php

namespace TFD;

use TFD\Image\Sizes;

class Model extends \WP_Model
{
    public function featuredImageWithSize(Size\SizeGroup $size, Image $default = null)
    {
        $image = $this->featuredImage();
        if ($image) {
            $image->setSizeGroup($size);
        }
        return $image;
    }

    /**
     * Get model's featured image or return $default if it does not exist
     *
     * @param  string $default
     * @return string
     */
    public function featuredImageModel(Image $default = null)
    {
        $featuredImage = Image::findFeaturedImage($this->ID);
        return ($featuredImage !== false)? $featuredImage : $default;
    }

    public function hasParent()
    {
        return $this->_post->post_parent;
    }


    /**
    * @return void
    */
    public function __isset($attribute)
    {
        $value = $this->$attribute;
        return !(is_null($value) || $value = '');
    }

    public function field($key, $default = null)
    {
        $attribute = ($this->prefix.$key);
        if (function_exists('field')) {
            return field($attribute, $this->ID)->default($default)->get();
        } elseif (function_exists('get_field')) {
            return get_field($attribute, $this->ID) ?: $default;
        } else {
            return get_post_meta($this->ID, $attribute, true) ?: $default;
        }
    }
}
