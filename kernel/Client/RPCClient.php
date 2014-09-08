<?php
/**
 * 自动调应用类
 * 配置：
 * 'apptest' => array(
 *    'host' =>  'http://127.0.0.1:8000/',
 *    'user' => 'test',
 *    'secret' => '{1234-1234-1234-1234}'
 * );
 * 格式：
 * \RPCClient_apptest_Test::instance()->getSomeData();
 * 
 *
 * @author chengjun <cgjp123@163.com>
 */
class RPCClient
{

    protected static $instance = array();
    protected static $className;
    protected static $config;
    
    protected $appName;
    protected $class;
    protected $host;
    protected $user;
    protected $secrect;
    
    protected $functionName;
    protected $param;
    
    public static function instance()
    {
        self::$className = get_called_class();
        if (!isset(self::$instance[self::$className])) {
            self::$instance[self::$className] = new static();
        }
        return self::$instance[self::$className];
    }
    
    private function __construct()
    {
        if (!isset(self::$config)) {
            self::$config = \Config\Client::$clientConfig;
        }
        if (empty(self::$config)) {
            throw new \Exception('can not find the configuration for ' . self::$className);
        }
        if (strpos(self::$className, 'RPCClient_')) {
            $className = substr(self::$className, strpos(self::$className, 'RPCClient_'), -1);
            $appName = substr($className, 0, strpos($className, '_'));
            $className = substr($className, -1, strpos($className, '_'));
            if (!empty($appName)) {
                if (!isset(self::$config[$appName])) {
                    throw new \Exception('can not find the configuration for ' . $appName);
                }
            $this->init($appName, $className);
            }
        }
    }
    
    protected function init($appName, $className)
    {
        $this->appName = $appName;
        $this->class = $className;
        $this->host = self::$config[$this->appName]['host'];
        $this->user = self::$config[$this->appName]['user'];
        $this->secrect = self::$config[$this->appName]['secrect'];
    }
    
    
    public function __call($functionName, $param)
    {
        $this->functionName = $functionName;
        $this->param = $param;
        $this->remoteCall();
    }
    
    protected function remoteCall()
    {
         $callUrl = $this->host . $this->appName . '/' . $this->class . '/' . $this->functionName . '/';
         $paramString = '?';
         if (!empty($this->param)) {
             $paramString .= http_build_query($this->param, 'app_');
         }
         
        $ch = curl_init();
        $curlOption = array(
                CURLOPT_URL => $callUrl . $paramString,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => 'getapp',
                CURLOPT_CONNECTTIMEOUT => \Config\Client::$connectTTL,
                CURLOPT_TIMEOUT => \Config\Client::$connectTTL,
                CURLOPT_COOKIE => $this->user . '=' . $this->secrect,
                );
        curl_setopt_array($ch, $curlOption);
        $this->returnData = curl_exec($ch);
        if ($this->returnData === false) throw new \Exception('connection service ' . $this->host . 'failure');
        return true;
    }
}

spl_autoload_register(
function($name){
    if(substr($name, 0, 10) == 'RPCClient_') {
        eval(sprintf("class %s extends RPCClient {}", $name));
    }
}
);