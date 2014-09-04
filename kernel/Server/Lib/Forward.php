<?php
namespace Server\Lib;

class Forward
{
    public function __construct()
    {
        
    }
    
    public function boot()
    {
        print_r($GET);
    }
}