<?php
/**
 * 自动加载类.
 * 
 * @author chengjun <cgjp123@163.com>
 */

namespace BootStrap;

/**
 * 系统自动加载类.
 */
class Autoload
{

    public $className;
    protected static $instance;
    public static $classPath = array(
            // 项目基础框架路径
            '../../',
            );
    
    public function loadByNameSpace($name)
    {
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $name);
        foreach (self::$classPath as $path) {
            $filePath = $path.$classPath. '.php';
            if (file_exists($filePath)) {
                require $filePath;
                if (class_exists($name)){
                        return true;
                    }
                }
        }
        return false;
    }
    
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function setRoot($path)
    {
        if (is_dir($path)) {
            array_push(self::$classPath, $path);
        }
        return $this;
    }
    
    public function init()
    {
        spl_autoload_register(array($this, 'loadByNameSpace'));
    }

}
