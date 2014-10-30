<?php
/**
 * Model层基类.
 *
 * @author oliver <cgjp123@163.com>
 */

namespace Db;

/**
 * Model层.
 * 
 * @author oliver <cgjp123@163.com>
 */
class Base
{

    protected static $instance = array();
    
    public static function instance()
    {
        $class = get_called_class();
        if (!isset(self::$instance[$class])) {
            self::$instance[$class] = new $class;
        }
        return self::$instance[$class];
    }
}