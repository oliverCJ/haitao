<?php
namespace Client;

require_once WORKERMAN_ROOT_DIR . 'Common/Protocols/JsonProtocol.php';
/**
 * 客户端调用类
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
                        'classname' => $this->rpcClass,
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
     * 抛出 Socket 异常信息.
     *
     * @param resource $client            Socket 句柄.
     * @param boolean  $withExecutionTime 是否记录执行时间.
     *
     * @throw Exception
     *
     * @return void
     */
    private function raiseSocketException($client = null, $withExecutionTime = false)
    {
    	if ($client === null) $client = $this->connection;
    
    	$errstr = socket_strerror(socket_last_error($client));
    	@socket_close($client);
    
    	throw new \Exception($withExecutionTime
    			? sprintf('RPCSocketClient: %s, %s(%.3fs)', $this->rpcUri, $errstr, $this->executionTime())
    			: sprintf('RPCSocketClient: %s, %s', $this->rpcUri, $errstr));
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
        $client = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if (!$client)
			$this->raiseSocketException();
		list( $tcp, $host, $port) = explode(':', $this->rpcUri);
		$host = str_replace('/', '', $host);
		if (!@socket_connect($client, $host, $port))
			$this->raiseSocketException($client);
		
		$this->connection = $client;
    }
    
    /**
     * 关闭网络链接.
     *
     * @return void
     */
    private function closeConnection()
    {
        @socket_close($this->connection);
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
        
        $client = $this->connection;
        // 发送 RPC 文本请求协议
        $bufferLength = strlen($data);
        $bufferTotalLength = 4 + $bufferLength;
        $buffer = pack('N', $bufferTotalLength) . $data;
    	if (!@socket_write($client, $buffer, $bufferTotalLength)) {
			throw new \Exception(sprintf('RPCSoceketClient: Network %s disconnected', $this->rpcUri));
		}
        
        // 调用回调函数
       // self::emit('send', $data);
       
		// 读取首部4个字节，网络字节序int
		$lenBuffer = @socket_read($client, 4);
		$lenBufferData = unpack('Ntotal_length', $lenBuffer);
		$length = $lenBufferData['total_length'] - 4; // 去掉首部4个存储长度的字节
		
		if ($length === false)
			$this->raiseSocketException(null, true);
		if (!ctype_digit((string)$length)) {
			throw new \Exception(sprintf('RPCSoceketClient: Got wrong protocol codes: %s', bin2hex($length)));
		}
		
    	// 读取返回数据
		$re = '';
		while($length > 0) {
			$buffer = socket_read($client, 4096);
			if ($buffer === false)
				$this->raiseSocketException(null, true);
			$re .= $buffer;
			$length -= strlen($buffer);
		}
		
        //self::emit('recv', $re);
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
    	if (empty(self::$events[$eventName])) {
    		self::$events[$eventName] = array();
    	}
    	array_push(self::$events[$eventName], $eventCallback);
    }
    
    /**
     * 调用回调函数
     * 
     * @param unknown_type $eventName
     */
    protected static function emit($eventName)
    {
        if (!empty(self::$event[$eventName])) {
        	if (!empty(self::$events[$eventName])) {
        		$args = array_slice(func_get_args(), 1);
        		foreach (self::$events[$eventName] as $callback) {
        			@call_user_func_array($callback, $args);
        		}
        	}
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