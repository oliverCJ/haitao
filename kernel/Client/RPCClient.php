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
    protected $ttl;
    
    protected $executionTimeStart;
    
    public static function config(array $config = array())
    {
        if (empty($config)) {
            return self::$_config;
        }
        self::$_config = $config;
        return self::$_config;
    }
    
    public static function instance($config = array())
    {
        $className = get_called_class();
        $key = $className.'_';
        if (empty($config)) {
            $key .= 'rpc';
        } else {
            $key .= md5(serialize($config));
        }
        if (!isset(self::$instance[$key])) {
            self::$instance[$key] = new $className($config);
        }
        return self::$instance[$key];
    }
    
    private function __construct($config = array())
    {
       $config = empty($config) ? self::config() : self::config($config) ;
       
        if (empty($config) && class_exists('\Config\Client')) {
            $config = self::config((array) new \Config\Client);
        }
        if (empty($config)) {
            throw new \Exception('Missing configuration');
        }
        $className = get_called_class();
        if (preg_match('/^CURLClient_([A-Za-z0-9]+)_([A-Za-z0-9]+)/', $className, $matches)) {
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
        //$paramString = '?';
        //if (!empty($arguments)) {
        //    $paramString .= http_build_query($arguments, 'pa_');
        //}
        //$callUrl .= $paramString;
        //$secrectUrl .= $paramString;
        $returnData = $this->remoteCall($callUrl, $secrectUrl, $arguments);
        if (!empty($returnData)) $returnData = json_decode($returnData, true);
        return $returnData;
    }
    
    protected function remoteCall($getUrl, $secrectUrl, $arguments)
    {
        $postFieldsString = '';
        if (!empty($arguments)) {
            $postFields = array(
                    'param' => json_encode($arguments),
            );
            $postFieldsString = http_build_query($postFields);
        }
        $this->executionTimeStart = microtime(true);
        $config = self::config();
        $requestHeaders = array(
            'Accept: application/xhtml+xml; application/json; charset=UTF-8',
            'Content-Type:application/x-www-form-urlencoded; charset=utf-8',
            'Connection: close',
        );
        $ch = curl_init();
        $curlOption = array(
                CURLOPT_URL => $getUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postFieldsString,
                CURLOPT_HTTPHEADER => $requestHeaders,
                CURLOPT_HEADER => false,
                CURLOPT_ENCODING => 'gzip',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => 'getapp',
                CURLOPT_CONNECTTIMEOUT => $config['connectTTL'],
                CURLOPT_TIMEOUT => $config['connectTTL'],
                CURLOPT_COOKIE => 'user=' . $this->user . ';password=' . $this->encrypt($this->user, $this->secrect) . ';signature=' . $this->encrypt($secrectUrl, $config['rpc_secrect_key']),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FORBID_REUSE => true,
                );
        curl_setopt_array($ch, $curlOption);
        $response = curl_exec($ch);
        curl_close($ch);
        $executTime = $this->executionTime();
        // TODO 记录日志
        if ($response === false) throw new \Exception('connection service ' . $this->host . ' failure');
        return $response;
    }
    
    public function getExectionTime()
    {
        return $this->executionTime();
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
    if(strpos($name, 'CURLClient_') !== 0) {
        return false;
    }
    eval(sprintf("class %s extends \Client\RPCClient {}", $name));
}
);