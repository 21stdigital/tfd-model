<?php

namespace TFD;

class Address
{
    public $name;
    public $street_name;
    public $street_name_short;
    public $street_number;
    public $post_code;
    public $city;
    public $country;
    public $country_short;
    public $lat;
    public $lng;

    public function __construct($args)
    {
        $this->name = array_key_exists('name', $args) ? $args['name'] : null;
        $this->street_name = array_key_exists('street_name', $args) ? $args['street_name'] : null;
        $this->street_name_short = array_key_exists('street_name_short', $args) ? $args['street_name_short'] : null;
        $this->street_number = array_key_exists('street_number', $args) ? $args['street_number'] : null;
        $this->post_code = array_key_exists('post_code', $args) ? $args['post_code'] : null;
        $this->city = array_key_exists('city', $args) ? $args['city'] : null;
        $this->country = array_key_exists('country', $args) ? $args['country'] : null;
        $this->country_short = array_key_exists('country_short', $args) ? $args['country_short'] : null;
        $this->lat = array_key_exists('lat', $args) ? $args['lat'] : null;
        $this->lng = array_key_exists('lng', $args) ? $args['lng'] : null;
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
