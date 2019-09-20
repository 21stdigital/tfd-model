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
}
