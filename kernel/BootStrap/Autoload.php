<?php
/**
 * 自动加载类.
 * 
 * @author oliver
 */

namespace BootStrap;

/**
 * 系统自动加载类.
 */
class Autoload
{

    public $className;
    public $classPath = array();
    
    public function load($name)
    {
        $this->classPath[] = 'kernel';
        foreach ($this->classPath as $path) {
            if (if_dir($path)) {
                $filePath = realpath('/'.$path.'/'.$name.'.php');
                if (file_exists($filePath)) {
                    require $filePath;
                    break;
                }
            }
        }
        return true;
    }
    
    public static function instance($appPath)
    {
        $this->classPath[] = $appPath;
        return new self();
    }
    
    protected function __construct()
    {
        spl_autoload_register(array($this, 'load'));
    }

}
