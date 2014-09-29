<?php
/**
 * 扩展work,RPC接口调用.
 * 
 * @author chengjun <junc1@jumei.com>
 */
class RpcTextWork extends Man\Core\SocketWorker
{

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
        StatisticClient::tick(__CLASS__, $interface);
    }
    
    /**
    * 进程结束时触发.
    * 
    * @see Man\Core.SocketWorker::onStop()
    */
    protected function onStop()
    {
        
    }
}