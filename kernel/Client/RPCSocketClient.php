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
class RPCSocketClient
{

    protected static $instance = array();
    protected static $_config;
    protected static $event = array();
    
    protected $connection;
    protected $rpcUri;
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
        $this->rpcUri = $config['host'];
        $this->user = $config['user'];
        $this->secrect = $config['secrect'];
        $this->rpcCompressor = !empty($config['compressor']) ? $config['compressor'] : null;
    }
    
    
    public function __call($method, $arguments)
    {
        $fn = null;
        if (!empty($arguments) && is_callable($arguments[count($arguments)-1])){
            $fn = array_pop($arguments);
        }
        $packet = array(
                'data' => array(
                        'version' => '1.0',
                        'user' => $this->user,
                        'password' => $this->encrypt($this->user, $this->secrect),
                        'timestamp' => microtime(true),
                        'class' => $this->rpcClass,
                        'method' => $method,
                        'params' => $arguments,
                        ),
                );
        $config = self::config();
        $packet['signature'] = $this->encrypt(json_encode($packet['data']), $config['rpc_secrect_key']);
        try {
            $re = $this->remoteCall($packet);
            return $re;
        } catch (\Exception $e) {
            
        }
    }
    
    /**
     * 创建网络链接.
     *
     * @throws Exception 抛出链接错误信息.
     *
     * @return void
     */
    private function openConnection()
    {
        $this->connection = stream_socket_client($this->rpcUri, $errno, $errstr);
        if (!$this->connection) {
            throw new \Exception(sprintf('RpcSocketClient: %s, %s', $this->rpcUri, $errstr));
        }
        @stream_set_timeout($this->connection, 60);
    }
    
    /**
     * 关闭网络链接.
     *
     * @return void
     */
    private function closeConnection()
    {
        @fclose($this->connection);
    }
    
    /**
     * 调用RPC
     * 
     * @param unknown_type $data
     * @throws \Exception
     * @throws Exception
     */
    protected function remoteCall($data)
    {
        $this->executionTimeStart = microtime(true);
        if (!$data = json_encode($data)) {
            throw new \Exception('RPCSoceketClient:cannot serilize $data with json_encode');
        }
        
        $this->openConnection();
        
        $fp = $this->connection;
        // 发送 RPC 文本请求协议
        $bufferLength = strlen($data);
        $bufferTotalLength = 4 + $bufferLength;
        $buffer = pack('N', $bufferTotalLength) . $data;
        if (!@fwrite($fp, $buffer)) {
            throw new Exception(sprintf('RPCSoceketClient: Network %s disconnected', $this->rpcUri));
        }
        
        // 调用回调函数
        $this->emit('send', $data);
        
        if (!$len = @fgets($fp)) {
            throw new \Exception(sprintf('RPCSoceketClient: Network %s maybe timeout(%.3fs), or have a fatal error on the server', $this->rpcUri, $this->getExectionTime()));
        }
        $len = trim($len);
        if (!preg_match('#^\d+$#', $len)) {
            throw new Exception(sprintf('RPCSoceketClient: Got wrong protocol codes: %s', bin2hex($len)));
        }
        $re = '';
        while (strlen($re) < $len ) {
            $re .= fgets($fp);
        }
        self::emit('recv', $re);
        $this->closeConnection();
        
        if ($re != '') {
            if ($this->rpcCompressor === 'GZ') {
                $re = @gzuncompress($re);
            }
            $re = json_decode($re, true);
            return $re;
        }
    }
    
    /**
     * 注册事件回调函数.
     * 
     * @param string $eventName
     * @param string $eventCallback
     *
     */
    public static function on($eventName, $eventCallback)
    {
        
    }
    
    /**
     * 调用回调函数
     * 
     * @param unknown_type $eventName
     */
    protected static function emit($eventName)
    {
        if (!empty(self::$event[$eventName])) {
            
        }
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
    if(strpos($name, 'RPCClient_') !== 0) {
        return false;
    }
    eval(sprintf("class %s extends \Client\RPCSocketClient {}", $name));
}
);