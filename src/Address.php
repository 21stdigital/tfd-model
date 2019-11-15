<?php

namespace TFD;

class Address
{
    public $name = null;
    public $street_name = null;
    public $street_name_short = null;
    public $street_number = null;
    public $post_code = null;
    public $city = null;
    public $country = null;
    public $country_short = null;
    public $lat = null;
    public $lng = null;

    public function __construct($args)
    {
        foreach ($args as $prop => $value) {
            if (property_exists(__CLASS__, $prop)) {
                $this->$prop = $value;
                dlog($prop, $value, $this->$prop);
            }
        }
    }

    public function draw()
    {
        echo "
            {$this->name}</br>
            {$this->street} {$this->streetNumber}</br>
            {$this->zipcode} {$this->city}
        ";
    }
}
