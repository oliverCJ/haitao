<?php
/**
 * 自动加载类.
 * 
 * @author oliver <cgjp123@163.com>
 */

namespace BootStrap;

/**
 * 系统自动加载类.
 */
class Autoload
{

    public $className;
    public static $classPath = array(
            // 项目基础框架路径
            '../../',
            );
    
    public function load($name)
    {
        foreach (self::$classPath as $path) {
            if (is_dir($path)) {
                $pathArray = explode('\\', $name);
                $fileName = array_pop($pathArray);
                $filePath = implode('/', $pathArray);
                $filePath = $path.$filePath . '/' . $fileName . '.php';
                if (file_exists($filePath)) {
                    require $filePath;
                    if (class_exists($name)){
                        return true;
                        }
                    }
            }
        }
        return true;
    }
    
    public static function instance()
    {
        return new self();
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
        spl_autoload_register(array($this, 'load'));
    }

}
