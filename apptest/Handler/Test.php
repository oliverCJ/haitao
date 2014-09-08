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
    public function getSomeData()
    {
        \RPCClient_apptest_Test::instance()->getData(213, 123);
//         $ch = curl_init('http://127.0.0.1:8000/apptest/Test/getData?ssd=2');
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//         $data = curl_exec($ch);
//         curl_close($ch);
        $data = \Module\Test::instance()->test();
        return $data;
    }
    
    public function getData()
    {
    	$arg = func_get_args();
    	return array('code' => 0, 'msg' => $arg);
    }
}