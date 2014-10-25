<?php
/**
 * 扩展work,RPC接口调用.
 * 
 * @author chengjun <junc1@jumei.com>
 */
require_once WORKERMAN_ROOT_DIR . 'Statistics/Clients/StatisticClient.php';
require_once WORKERMAN_ROOT_DIR . 'Common/Protocols/JsonProtocol.php';

abstract class RpcWork extends Man\Core\SocketWorker
{

    /**
     * 压缩方法.
     */
    private $rpcCompressor;
    
    /**
    * 进程启动时初始化
    * 
    * @see Man\Core.SocketWorker::onStart()
    */
    protected function onStart()
    {
    	return false;
    }
    
    /**
     * 检查包是否接收完整.
     * 
     * @param unknown_type $recv_str
     */
    public function dealInput($recv_str)
    {
        return Man\Common\Protocols\JsonProtocol::check($recv_str);
    }
    
    /**
     * 处理数据.
     * 
     * @param unknown_type $recv_str
     */
    public function dealProcess($recv_str)
    {
        try {            
            if (($data = Man\Common\Protocols\JsonProtocol::decode($recv_str)) === false) {
                throw new \Exception ('RPCWork: parse data failed');
            }
            if ($data['data'] == 'PING') {
            	return $this->sendToClient(Man\Common\Protocols\JsonProtocol::encode('PONE'));
            }
            
            $this->rpcCompressor = null;
            
            $this->process($data);
            
        } catch (Exception $ex) {
        	$returnData = array(
        					'exception' => array(
        							'class' => get_class($ex),
        							'message' => $ex->getMessage(),
        							'code' => $ex->getCode(),
        							'file' => $ex->getFile(),
        							'line' => $ex->getLine(),
        							'traceAsString' => $ex->getTraceAsString(),
        					)
        			);
        	return $this->sendToClient(Man\Common\Protocols\JsonProtocol::encode(json_encode($returnData)));
        }
    }
    
    /**
     * 请求数据签名.
     *
     * @param string $data   待签名的数据.
     * @param string $secret 私钥.
     *
     * @return string
     */
    protected function encrypt($data, $secret)
    {
    	return md5($data . '&' . $secret);
    }
    
    /**
     * 处理数据
     */
    abstract public function process($data);
    
    /**
    * 进程结束时触发.
    * 
    * @see Man\Core.SocketWorker::onStop()
    */
    protected function onStop()
    {
        return false;
    }
}