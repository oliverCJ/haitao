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
    
    /**
     * 获取继承类的单例.
     */
    public static function instance()
    {
        $className = get_called_class();
        if (!isset(self::$instance[$className])) {
            self::$instance[$className] = new $className;
        }
        return self::$instance[$className];
    }
    
    /**
     * 私有化构造函数,集成类不可被实例化.
     */
    private function __construct()
    {
        
    }
}