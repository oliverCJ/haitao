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
            if ($data['data'] = 'PING') return 'PONE';
            
            $this->rpcCompressor = null;
            
            $data = $data['data'];
        
        } catch (\Exception $e) {
            
        }
        //$interface = '';
        //StatisticClient::tick(__CLASS__, $interface);
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