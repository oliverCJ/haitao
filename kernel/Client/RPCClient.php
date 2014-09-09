<?php
namespace Client;
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
    protected static $_config;
    
    protected $rpcClass;
    protected $appName;
    protected $host;
    protected $user;
    protected $secrect;
    protected $returnData;
    
    protected $executionTimeStart;
    
    public static function config(array $config = array())
    {
        if (empty($config)) {
            return self::$_config;
        }
        self::$_config = $config;
    }
    
    public static function instance()
    {
        $className = get_called_class();
        $key = $className.'_rpc';
        if (!isset(self::$instance[$key])) {
            self::$instance[$key] = new $className();
        }
        return self::$instance[$key];
    }
    
    private function __construct()
    {
        $config = self::config();
        if (empty($config) && class_exists('\Config\Client')) {
            $config = \Config\Client::$clientConfig;
            self::config($config);
        }
        if (empty($config)) {
            throw new \Exception('Missing configuration for ' . self::$className);
        }
        $className = get_called_class();
        if (preg_match('/^RPCClient_([A-Za-z0-9]+)_([A-Za-z0-9]+)/', $className, $matches)) {
            $this->appName = $matches[1];
            $this->rpcClass = $matches[2];
            if (!empty($this->appName)) {
                if (!isset($config[$this->appName])) {
                    throw new \Exception('can not find the configuration for ' . $this->appName);
                }
                $this->init($config[$this->appName]);
            }
        }
    }
    
    protected function init(array $config)
    {
        $this->host = $config['host'];
        $this->user = $config['user'];
        $this->secrect = $config['secrect'];
    }
    
    
    public function __call($method, $arguments)
    {
        $callUrl = $this->host . $this->appName . '/' . $this->rpcClass . '/' . $method . '/';
        $secrectUrl = $this->appName . '/' . $this->rpcClass . '/' . $method . '/';
        $paramString = '?';
        if (!empty($arguments)) {
            $paramString .= http_build_query($arguments, 'pa_');
        }
        $callUrl .= $paramString;
        $secrectUrl .= $paramString;
        $returnData = $this->remoteCall($callUrl, $secrectUrl);
        return $returnData;
    }
    
    protected function remoteCall($getUrl, $secrectUrl)
    {echo $secrectUrl;
        $this->executionTimeStart = microtime(true);
        $config = self::config();
        $ch = curl_init();
        $curlOption = array(
                CURLOPT_URL => $getUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => 'getapp',
                CURLOPT_CONNECTTIMEOUT => \Config\Client::$connectTTL,
                CURLOPT_TIMEOUT => \Config\Client::$connectTTL,
                CURLOPT_COOKIE => 'user=' . $this->user . ',password=' . $this->encrypt($this->user, $this->secrect) . ',signature=' . $this->encrypt($secrectUrl, \Config\Client::$rpc_secrect_key),
                );
        curl_setopt_array($ch, $curlOption);
        $returnData = curl_exec($ch);
        curl_close($ch);
        $executTime = $this->executionTime();
        // TODO 记录日志
        
        if ($returnData === false) throw new \Exception('connection service ' . $this->host . ' failure');
        return $returnData;
    }
    
    private function executionTime()
    {
        return microtime(true) - $this->executionTimeStart;
    }
    
    /**
     * 请求数据签名.
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

spl_autoload_register(
function($name){
    if(strpos($name, 'RPCClient_') !== 0) {
        return false;
    }
    eval(sprintf("class %s extends \Client\RPCClient {}", $name));
}
);