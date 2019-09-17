<?php

namespace TFD;

class Model extends \WP_Model
{
    /**
    * @return void
    */
    public function __isset($attribute)
    {
        error_log("ISSET {$attribute}");
        $value = $this->$attribute;
        return !(is_null($value) || $value = '');
    }
}
