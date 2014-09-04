<?php
namespace Model;

class Test extends \Model\Base
{
    public static function instance()
    {
        return parent::instance();
    }
    
    public function Test(){
        return array('code' => 0, 'msg' => 'the data');
    }
}