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
        //$data = \Module\Test::instance()->test();
        return $data;
    }
    
    public function getData()
    {
    	$arg = func_get_args();
    	return array('code' => 0, 'msg' => $arg);
    }
}