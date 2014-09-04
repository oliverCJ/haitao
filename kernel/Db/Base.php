<?php
/**
 * 数据库操作基类.
 * 
 * @author oliver <cgjp123@163.com>
 */

namespace Db;

/**
 * 基类.
 */
class Base
{

    protected static $instance;
    
    /**
     * 获取对象.
     * 
     * @return resource
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

}
