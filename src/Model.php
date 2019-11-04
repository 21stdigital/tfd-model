<?php

namespace TFD;

class Model extends \WP_Model
{
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
