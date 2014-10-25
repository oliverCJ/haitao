<?php
namespace Handler;

/**
 * 接口入口.
 *
 * @author chengjun <cgjp123@163.com>
 *
 */
class Test
{
    public function getSomeData($s, $s2)
    {
    	// 调用其他接口方法\RPCClient_接口服务名_接口类::instance()->接口方法(接口参数);
        $data = \RPCClient_apptest_Test::instance()->getData($s, $s2);

        return $data;
    }

    public function getData($t1, $t2)
    {
        $arg = func_get_args();
        return array('code' => 0, 'msg' => $arg, 'cookie' => $_COOKIE);
    }
}