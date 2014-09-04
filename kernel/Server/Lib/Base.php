<?php
namespace Server\Lib;

class Base
{

    protected $instance;
    
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
    }
}