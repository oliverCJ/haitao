<?php
/**
 * JSON协议的数据收发work
 * 
 * @author chengjun <junc1@jumei.com>
 */
require_once WORKERMAN_ROOT_DIR . 'work/RpcWork.php';

class JsonWork extends RpcWork
{
	public static $appName = 'apptest';
	/**
	 * 初始化
	 *
	 * @param unknown_type $recv_str
	 */
	public function onStart()
	{
		// 加载入口文件
		$initFile = KERNEL_ROOT_PATH . Man\Core\Lib\Config::get($this->workerName . '.app_init_file');
		if (is_file($initFile)) {
			require_once $initFile;
		}
		
		if (defined('APP_NAME')) {
			self::$appName = APP_NAME;
		} elseif ($appName = Man\Core\Lib\Config::get($this->workerName . '.app_name')) {
			self::$appName = $appName;
		} else {
			self::$appName = $$this->workerName;
		}
		
		//日志
		
		// 初始化上报地址
		$reportAdress = Man\Core\Lib\Config::get($this->workerName . '.report_address');
		if ($reportAdress) {
			StatisticClient::setReportAdress($reportAdress);
		}
	}
	
    public function process($data)
    {
    	$requestParam = $data['data'];
    	$signature = $data['signature'];
    	
    	// 统计
    	StatisticClient::tick($requestParam['classname'], $requestParam['method']);
    	
    	// 检查版本
    	if ($requestParam['version'] != '1.0') return $this->sendToClient(Man\Common\Protocols\JsonProtocol::encode('call wrong version interface, please check it'));
    	// 验证身份
    	$userRpcSecrectKey = Man\Core\Lib\Config::get($this->workerName . '.rpc_secrect_key');
    	$userList = Man\Core\Lib\Config::get($this->workerName . '.user_list');
    	
    	if (array_key_exists($requestParam['user'], $userList)) {
    		$userSecrect = $userList[$requestParam['user']];
    		if ($requestParam['password'] == $this->encrypt($requestParam['user'], $userSecrect)) {
    			// 验证数据签名
    			if ($signature == $this->encrypt(json_encode($requestParam), $userRpcSecrectKey)) {
    				// 验证通过,开始获取接口数据
    				$class_name = '\\Handler\\'.ucfirst($requestParam['classname']);
    				$_SERVER['REMOTE_ADDR'] = $this->getRemoteIp();
    				try {
    					$retrunData = '';
    					// 执行接口调用初始化函数
    					if (function_exists('on_phpserver_request_start')) {
    						\on_phpserver_request_start();
    					}
    					// 调用接口
    					if (class_exists($class_name)) {
    						$call_back = array(new $class_name, $requestParam['method']);
    						if(is_callable($call_back)) {
    							$retrunData = call_user_func_array($call_back, $requestParam['params']);
    							$success = true;
    						} else {
    							throw new Exception("method $class_name::{$requestParam['method']} not exist");
    						}
    					} else {
    						throw new Exception("class $class_name not exist");
    					}
    				} catch (Exception $ex) {
    					$retrunData = array(
			                'exception' => array(
			                    'class' => get_class($ex),
			                    'message' => $ex->getMessage(),
			                    'code' => $ex->getCode(),
			                    'file' => $ex->getFile(),
			                    'line' => $ex->getLine(),
			                    'traceAsString' => $ex->getTraceAsString(),
			                )
    					);
    					$success = false;
    				}
    				
    				// 执行接口调用结束函数
    				if (function_exists('on_phpserver_request_finish')) {
    					\on_phpserver_request_finish();
    				}
    				// 统计上报
    				StatisticClient::report($requestParam['classname'], $requestParam['method'], $success, 1, json_encode($retrunData));
    				return $this->sendToClient(Man\Common\Protocols\JsonProtocol::encode($retrunData));
    			}
    			// 上报统计
    			StatisticClient::report($requestParam['classname'], $requestParam['method'], false, 0, 'Worng signature');
    			return $this->sendToClient(Man\Common\Protocols\JsonProtocol::encode('Worng signature'));
    		}
    		// 上报统计
    		StatisticClient::report($requestParam['classname'], $requestParam['method'], false, 0, 'Worng password');
    		return $this->sendToClient(Man\Common\Protocols\JsonProtocol::encode('Worng password'));
    	}
    	// 上报统计
    	StatisticClient::report($requestParam['classname'], $requestParam['method'], false, 0, 'Permission denied');
    	return $this->sendToClient(Man\Common\Protocols\JsonProtocol::encode('Permission denied'));
    }
    
}