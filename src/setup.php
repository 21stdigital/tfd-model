<?php
    $path = get_template_directory() . '/app/ImageSizes/';
    collect(glob($path . '*.php'))->map(function ($file) {
        return require_once($file);
    });
