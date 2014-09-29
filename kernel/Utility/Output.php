<?php
namespace Utility;

/**
 * 输出封装.
 * 
 * @author chengjun <cgjp123@163.com>
 *
 */
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
    
    public static function h_print_r($msg = ''){
        print_r(json_encode($msg));
        exit;
    }
}