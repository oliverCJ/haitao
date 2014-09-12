<?php
namespace Model;

class Test extends \Model\Base
{
    
    const DB_NAME = 'default';
    const TABLE_NAME = 'giftcard_invalid_records';
    
    public static function instance()
    {
        return parent::instance();
    }
    
    public function Test(){
        \Db\Connection::instance()->write();
        var_dump(\Db\Connection::instance()->write());
        var_dump(\Db\Connection::instance()->write());
        return array('code' => 0, 'msg' => 'the data');
    }
}