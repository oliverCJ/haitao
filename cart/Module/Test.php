<?php
namespace Module;

/**
 * 测试模块类.
 * 
 * @author chengjun <cgjp123@163.com>
 *
 */
class Test extends \Module\ModuleBase
{

    public static function instance()
    {
        return parent::instance();
    }
    
    public function test()
    {
        //\Model\Test::instance()->testInsert();
        print_r(\Model\Test::instance()->testShowTable());
        $data = \Model\Test::instance()->test();
        return $data;
    }

}
