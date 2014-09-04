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
        $data = \Module\Test::instance()->test();
        return $data;
    }
}