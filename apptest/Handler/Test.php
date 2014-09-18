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
//         \Utility\Output::h_print_r('test');
        $data = \RPCClient_apptest_Test::instance()->getData($s, $s2);

        //         $ch = curl_init('http://127.0.0.1:8000/apptest/Test/getData?ssd=2');
        //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //         $data = curl_exec($ch);
        //         curl_close($ch);
        //    throw new \Exception\AppException('sdsdsdsdsfsdfsdf');
        //         $data = \Module\Test::instance()->test();
        //         $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        //         print_r($trace);
        return $data;
    }

    public function getData($t1, $t2)
    {
        $arg = func_get_args();
        return array('code' => 0, 'msg' => $arg, 'cookie' => $_COOKIE);
    }
}