<?php
namespace Server\Lib;

class Output 
{
    public static function returnJsonVal($data)
    {
        echo json_encode($data);
    }
    
    public static function returnTextVal($msg)
    {
        echo $msg;
    }
    
    public static function returnWelcome()
    {
        echo 'welcome to service';
    }
}