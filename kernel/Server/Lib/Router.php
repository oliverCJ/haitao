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
                
                $this->validCall();
                $this->param = $_GET;
                
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
    
    private function validCall()
    {
        // 验证参数名
        if (!empty($_GET)) {
            foreach ($_GET as $k => $v) {
                if (strpos($k, 'pa_') !== 0) {
                    throw new \Exception('The param name wrong');
                }
            }
        }
        // 验证签名
        if (isset($_COOKIE['signature'])) {
            $callURI = substr($_SERVER['REQUEST_URI'], 1);
            if ($this->encrypt(substr($_SERVER['REQUEST_URI'], 1), \Server\Lib\ServerConfig::get('rpc_secrect_key')) != $_COOKIE['signature']) {
                throw new \Exception('The signature valid failture');
            }
        } else {
            throw new \Exception('The secrect key valid failture');
        }
        // 验证app
        if (isset($_COOKIE['user']) && isset($_COOKIE['password'])) {
            $tokenKey = \Server\Config\AppSecrectKey::$tokenKey;
            if (isset($tokenKey[$this->appName][$_COOKIE['user']])) {
                if ($this->encrypt($_COOKIE['user'], $tokenKey[$this->appName][$_COOKIE['user']]) != $_COOKIE['password']) {
                    throw new \Exception('The user valid failture');
                }
            } else {
                throw new \Exception('The user is illegal');
            }
        } else {
            throw new \Exception('missing user secrect info');
        }
    }
    
    /**
     * 数据签名.
     *
     * @param string $data   待签名的数据.
     * @param string $secret 私钥.
     *
     * @return string
     */
    private function encrypt($data, $secret)
    {
        return md5($data . '&' . $secret);
    }

}
