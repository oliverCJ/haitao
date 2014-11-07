<?php
/**
 * 测试服务进程.
 * 
 * @author oliver
 */
require_once WORKERMAN_ROOT_DIR . 'Core/SocketWorker.php';
require_once WORKERMAN_ROOT_DIR . 'Common/Protocols/Http/Http.php';

class RpcTestWork extends Man\Core\SocketWorker
{
	
	public $application = array();
	
	public $serverConfig = array();
	
	public $executTime;
	
	/**
	 * 进程启动时初始化
	 *
	 * @see Man\Core.SocketWorker::onStart()
	 */
	protected function onStart()
	{
		// 初始化HttpCache
        Man\Common\Protocols\Http\HttpCache::init();
        
        $this->serverConfig = array(
	        		'rpc_secrect_key' => '769af463a39f077a0340a189e9c1ec28',
	        		'connectTTL' => 30,
	        		'compressor' => null,
	        		'apptest' => array(
	        				'user' => 'test',
	        				'host' => 'tcp://127.0.0.1:9527/',
	        				'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590000}',
	        				),
                'cart' => array(
                        'host' => 'tcp://127.0.0.1:9528/',
                        'user' => 'app',
                        'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590001}',
                        ),
                'item' => array(
                       'host' => 'tcp://127.0.0.1:9529/',
                        'user' => 'app',
                        'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590001}',
                ),
                'user' => array(
                        'host' => 'tcp://127.0.0.1:9530/',
                        'user' => 'app',
                        'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590001}',
                ),
                'sns' => array(
                        'host' => 'tcp://127.0.0.1:9531/',
                        'user' => 'app',
                        'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590001}',
                ),
        		);
        $this->application = array(
        		'apptest' =>'127.0.0.1:9527',
                'cart' =>'127.0.0.1:9528',
                'item' =>'127.0.0.1:9529',
                'user' =>'127.0.0.1:9530',
                'sns' =>'127.0.0.1:9531',
        		);
	}
	
	/**
	 * 检查包是否接收完整.
	 *
	 * @param unknown_type $recv_str
	 */
	public function dealInput($recv_buffer)
	{
		return Man\Common\Protocols\Http\http_input($recv_buffer);
	}
	
	/**
	 * 处理数据.
	 *
	 * @param unknown_type $recv_str
	 */
	public function dealProcess($recv_buffer)
	{
		// http请求处理开始。解析http协议，生成$_POST $_GET $_COOKIE
		Man\Common\Protocols\Http\http_start($recv_buffer);
		
		// 处理
		if (!empty($_POST)) {
			$this->processRequest($_POST);
		} else {
		    $this->displayHtml('', '');
		}
		
	}
	
	/**
	 * 处理请求
	 */
	public function processRequest($requestParam)
	{
		if (!empty($requestParam)) {
			if (!empty($requestParam['appname']) && array_key_exists($requestParam['appname'], $this->application) ) {
				$appName = $requestParam['appname'];
			}
			if (!empty($requestParam['class'])) {
                $class = $requestParam['class'];
            }
            if (!empty($requestParam['function'])) {
            	$function = $requestParam['function'];
            }
            $param = array();
            if (!empty($requestParam['param'])) {
            	if(get_magic_quotes_gpc() && is_array($requestParam['param']))
            	{
            		foreach($requestParam['param'] as $index => $value)
            		{
            			$requestParam['param'][$index] = stripslashes(trim($value));
            		}
            	}
            	$param = $requestParam['param'];
            	if ($param) {
            		foreach($param as $index => $value) {
            			if (stripos($value, 'array') === 0 || stripos($value, 'true') === 0 || stripos($value, 'false') === 0 || stripos($value, 'null') === 0 || stripos($value, 'object') === 0) {
            				eval('$param['.$index.']='.$value.';');
            			}
            		}
            	}
            }
            // 获取网络数据
            global $reqData;
            RPCSocketClient::on('send', function ($data) {
                global $reqData;
                $reqData = $data;
            });
            try {
            $call = '\RPCClient_'.$appName.'_'.$class;
            if (is_callable(array($call, 'instance'), true)) {
            	$client = $call::instance($this->serverConfig);
            	$response = call_user_func_array(array($client, $function), $param);
            	if (!empty($response)) {
            		$this->executTime = call_user_func_array(array($client, 'getExectionTime'), array());
            	}
            }
            } catch (Exception $e) {
            	$response = (string)$e;
            }
            $this->displayHtml($reqData, $response);
            return $response;
		}
		return false;
	}
	
	public function displayHtml($reqData, $response)
	{		
	    $response = !is_scalar($response) ? var_export($response, true) : $response;
		
		$appname = isset($_POST['appname']) ? $_POST['appname'] : 'apptest';
		$class = isset($_POST['class']) ? $_POST['class'] : 'Test';
		$function = isset($_POST['function']) ? $_POST['function'] : 'getSomeData';
		
		$html = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>接口测试</title>
<script type="text/javascript" src="http://lib.sinaapp.com/js/jquery/1.8/jquery.min.js"></script>
</head>
<body>
HTML;
		$html .= '<b>接口测试</b><br />';
		$html .= '<form action="" method="post">';
		$html .= '<table>';
		$html .= '<tr>';
		$html .= '<td>应用:</td>';
		$html .= '<td><select name="appname">';
		if (!empty($this->application)) {
			foreach ($this->application as $k => $v) {
			    $selected = '';
			    if ($appname == $k) $selected = "selected";
				$html .= '<option ' . $selected . '  value="'.$k.'">'.$k . '-' . $v .'</option>';
			}
		}
		$html .= '</select></td>';
		$html .= '</tr><tr>';
		$html .= '<td>类:</td>';
		$html .= '<td><input name="class" type="text" value="'.$class.'" style="width:400px"/></td>';
		$html .= '</tr><tr>';
		$html .= '<td>方法:</td>';
		$html .= '<td><input name="function" type="text" value="'.$function.'" style="width:400px"/></td>';
		if ($response && !empty($_POST['param'])) {
			$html .= '</tr><tbody id="parames">';
			foreach ($_POST['param'] as $v) {
				$html .= '<tr><td>参数:</td>';
				$html .= '<td><input name="param[]" type="text" value="' . $v . '" style="width:400px"/> <a href="javascript:void(0)" onclick="delParam(this)">删除本行</a></td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '</tr><tbody id="parames"><tr>';
			$html .= '<td>参数:</td>';
			$html .= '<td><input name="param[]" type="text" value="" placeholder="数组使用array(..)格式,bool直接使用true/false,null直接写null" style="width:400px"/> <a href="javascript:void(0)" onclick="delParam(this)">删除本行</a></td>';
			$html .= '</tr>';
		}
		$html .= '</tbody><tfoot><tr><td colspan="2"><a href="javascript:void(0)" onclick="addParam()">添加参数</a></td></tr>';
		$html .= '<tr><td colspan="2"><input type="submit" value="提交" /></td>';
		$html .= '</tr></tfoot>';
		$html .= '</table>';
		$html .= '</form>';
		
		if ($response) {
		    $html .= '<b>Return Data: </b>';
			$html .= '<pre>'.$response.'</pre>';
			$html .= '<table><tr><td>time cost: ' . $this->executTime . '</td></tr></table>';
		}
		
		if ($reqData) {
		    $html .= '<b>Request Data: </b>';
		    $html .= '<textarea style="width:98%;height:120px">'.$reqData.'</textarea>';
		}
		
		$html .= <<<HTML
<script type="text/javascript">
		
    function addParam() {
        $('#parames').append('<tr><td>参数:</td><td><input name="param[]" type="text" value="" placeholder="数组使用array(..)格式,bool直接使用true/false,null直接写null" style="width:400px"/> <a href="javascript:void(0)" onclick="delParam(this)">删除本行</a></td></tr>');
    }
		
    function delParam(obj) {
        $(obj).parent('td').parent('tr').remove();
    }
</script>
</body>
</html>
HTML;
		// 发送给客户端
		return $this->sendToClient(Man\Common\Protocols\Http\http_end($html));
		return true;
	}
}



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
require_once WORKERMAN_ROOT_DIR . 'Common/Protocols/JsonProtocol.php';

class RPCSocketClient
{

	protected static $instance = array();
	protected static $_config;
	protected static $events = array();

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
		 
		if (empty($config)) {
			throw new \Exception('Missing configuration');
		}
		$className = get_called_class();
		if (preg_match('/^RPCClient_([A-Za-z0-9]+)_([A-Za-z0-9]+)/', $className, $matches)) {
			$this->appName = $matches[1];
			$this->rpcClass = $matches[2];
			if (!empty($this->appName)) {
				if (!isset($config[$this->appName])) {
					throw new Exception('can not find the configuration for ' . $this->appName);
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
		$re = $this->remoteCall($packet);
		return $re;
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
// 		$this->connection = stream_socket_client($this->rpcUri, $errno, $errstr);
// 		if (!$this->connection) {
// 			throw new Exception(sprintf('RpcSocketClient: %s, %s', $this->rpcUri, $errstr));
// 		}
// 		@stream_set_timeout($this->connection, 60);
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
	
		throw new Exception($withExecutionTime
				? sprintf('RPCSocketClient: %s, %s(%.3fs)', $this->rpcUri, $errstr, $this->executionTime())
				: sprintf('RPCSocketClient: %s, %s', $this->rpcUri, $errstr));
	}

	/**
	 * 关闭网络链接.
	 *
	 * @return void
	 */
	private function closeConnection()
	{
// 		@fclose($this->connection);
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
			throw new Exception('RPCSoceketClient:cannot serilize $data with json_encode');
		}

		$this->openConnection();

		$client = $this->connection;
		// 发送 RPC 文本请求协议
		$bufferLength = strlen($data);
		$bufferTotalLength = 4 + $bufferLength;
		$buffer = pack('N', $bufferTotalLength) . $data;
		if (!@socket_write($client, $buffer, $bufferTotalLength)) {
			throw new Exception(sprintf('RPCSoceketClient: Network %s disconnected', $this->rpcUri));
		}

		// 调用回调函数
		self::emit('send', $data);
		
		// 读取首部4个字节，网络字节序int
		$lenBuffer = @socket_read($client, 4);
		$lenBufferData = unpack('Ntotal_length', $lenBuffer);
		$length = $lenBufferData['total_length'] - 4; // 去掉首部4个存储长度的字节
		
		if ($length === false)
			$this->raiseSocketException(null, true);
		if (!ctype_digit((string)$length)) {
			throw new Exception(sprintf('RPCSoceketClient: Got wrong protocol codes: %s', bin2hex($length)));
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
		if (!empty(self::$events[$eventName])) {
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
	eval(sprintf("class %s extends RPCSocketClient {}", $name));
}
);
