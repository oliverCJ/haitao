<?php
namespace Module;

/**
 * 模块基类.
 * 
 * @author chengjun <cgjp123@163.com>
 *
 */
class ModuleBase
{

    protected static $instance = array();
    
    public static function instance()
    {
        $className = get_called_class();
        if (!isset(self::$instance[$className])) {
            self::$instance[$className] = new $className;
        }
        return self::$instance[$className];
    }
}