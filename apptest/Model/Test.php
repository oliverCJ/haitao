<?php
namespace Model;

class Test extends \Model\Base
{
    const TABLE_NAME = 'test';
    
    protected $fields = array(
            'id',
            'name',
            );
    
    public function getFields()
    {
        return $this->fields;
    }
    
    protected function getDb($master = false)
    {
        return $master ?  \Db\Connection::instance()->write() : \Db\Connection::instance()->read();
    }
    
    public static function instance()
    {
        return parent::instance();
    }
    
    public function testShowTable()
    {
        $this->getDb()->setAllowRealExec(true);
        $sql = 'show create table ' . self::TABLE_NAME;
        return $this->getDb()->queryExe($sql);
    }
    
    public function test(){
        $fields = implode(',', array_map(array($this->getDb(),'quoteObj'), $this->getFields()));
        return $this ->getDb()->select($fields)->from(self::TABLE_NAME)->queryAll();
    }
    
    public function testInsert(){
        
        $cond = array (
                        'name' => 'oiver'
        );
        return $this->getDb(true)->insert(self::TABLE_NAME, $cond);
    }
}