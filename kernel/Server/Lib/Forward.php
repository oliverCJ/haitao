<?php
namespace Server\Lib;

/**
 * 服务入口.
 * 
 * @author chengjun <cgjp123@163.com>
 *
 */
class Forward
{

    
    public static function boot()
    {
        // TODO 来路检测,权限校验等
        
        
        \Server\Lib\Delivery::instance();
    }

}

