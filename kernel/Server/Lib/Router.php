<?php
namespace Server\Lib;

define('ROUTE_PATH_VAR_NAME','_r_');

/**
 * 请求路由解析处理.
 * 
 * @author chengjun <cgjp123@163.com>
 *
 */
class Router
{

    protected static $instance;
    
    protected $appName;
    
    protected $className;
    
    protected $functionName;
    
    protected $param = array();
    
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    protected function initRouter()
    {
        static $called;
        if (!$called) {
            $routerParam = array();
            if (!empty($_GET[ROUTE_PATH_VAR_NAME])) {
                $pathArray = explode('/',$_GET[ROUTE_PATH_VAR_NAME]);
                $pathArray = array_values(array_filter($pathArray));
                if (count($pathArray) > 3) {
                    throw new \Exception('Call wrong service, please check the call url');
                }
                if (empty($pathArray)) {
                    throw new \Exception('Call wrong service, please check the call url');
                }
                if (isset($pathArray[0]) && isset($pathArray[1]) && isset($pathArray[2])) {
                    $this->appName = $pathArray[0];
                    $this->className = $pathArray[1];
                    $this->functionName = $pathArray[2];
                } else {
                    throw new \Exception('Call wrong service, please check the call url');
                }
                
                unset($_GET[ROUTE_PATH_VAR_NAME]);
                
                if (!empty($_GET)) {
                    $this->param = $_GET;
                }
                
            }
            $called = true;
        }
        return true;
    }
    
    public function __get($name) {
        $this->initRouter();
        switch ($name) {
            case 'appName' :
                return $this->getAppName();
                break;
            case 'className' :
                return $this->getClassName();
                break;
            case 'functionName' :
                return $this->getFunctinName();
                break;
            case 'param' :
                return $this->getParam();
                break;
                
        }
        return null;
    }
    
    protected function getAppName()
    {
        return $this->appName;
    }
    
    protected function getClassName()
    {
        return $this->className;
    }
    
    protected function getFunctinName()
    {
        return $this->functionName;
    }
    
    protected function getParam()
    {
        return $this->param;
    }

}
