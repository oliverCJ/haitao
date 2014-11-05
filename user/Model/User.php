<?php
namespace Model;
/**
 * 用户表数据操作类.
 *
 * @author oliver <cgjp123@163.com>
 */
class User extends \Db\DbBase
{
    const TABLE_NAME = 'haiou_member';
    
    protected $fields = array(
            'userid', //int(8) NOT NULL AUTO_INCREMENT,
            'phoneid', //varchar(30) DEFAULT NULL,
            'username', // char(30) DEFAULT NULL,
            'password', //char(32) DEFAULT NULL,
            'open_type', //int(3) DEFAULT NULL,
            'open_secretid', //varchar(80) DEFAULT NULL,
            'name',  //varchar(30) DEFAULT NULL,
            'sex', //tinyint(1) DEFAULT NULL,
            'mobile', //varchar(18) DEFAULT NULL,
            'email', //varchar(50) DEFAULT NULL,
            'provinceid', //int(11) DEFAULT NULL,
            'cityid', //int(11) DEFAULT NULL,
            'areaid', //int(11) DEFAULT NULL,
            'area', //varchar(255) DEFAULT NULL,
            'logo', //varchar(120) DEFAULT NULL,
            'ip', //char(15) DEFAULT NULL,
            'points', //int(10) DEFAULT NULL,
            'status', //tinyint(1) DEFAULT NULL,
            'grade', //int(3) DEFAULT '1' COMMENT '等级',
            'update_date', //int(10) DEFAULT '0' COMMENT '升级日期',
            'regtime', //datetime DEFAULT NULL,
            'lastlogintime', //int(10) DEFAULT NULL,
            );
    
    public function getFields()
    {
        return $this->fields;
    }
    /**
     * 获取实例
     */
    public static function instance()
    {
        return parent::instance();
    }
    
    /**
     * 获取数据库操作实例.
     * 
     * @param unknown_type $isMaster 是否操作主库.
     * 
     * @return Ambigous <resource, multitype:>
     */
    public function getDb($isMaster = false)
    {
        return $isMaster ? \Db\Connection::instance()->write() : \Db\Connection::instance()->read();
    }
    
    /**
     * 插入一条用户信息.
     * 
     * @param unknown_type $insertData
     * @return boolean
     */
    public function insertMember($insertData = array())
    {
        if (empty($insertData)) throw new \Exception\UserException('empty insert data');
        return $this->getDb(true)->insert(self::TABLE_NAME, $insertData);
    }
    
    /**
     * 检查字段值是否已经存在与数据库中.
     * 
     * @param unknown_type $column 字段.
     * @param unknown_type $value     字段值.
     * 
     * @return boolean
     */
    public function checkColumnValueIsExists($column, $value)
    {
        if (!$column || !$value) return false;
        if (!in_array($column, $this->fields)) return false;
        $cond = array(
                $column => $value
                );
        $result = $this->getDb()->select('userid')->from(self::TABLE_NAME)->where($cond)->queryRow();
        if (empty($result)) return true;
        return false;
    }
    
    /**
     * 根据条件获取用户信息.
     * 
     * @param unknown_type $cond 条件.
     * @param unknown_type $fileld 需要获取的字段.
     * 
     */
    public function getUserInfoByCond($cond = array(), $fileld = '*')
    {
        if (empty($cond)) return false;;
        if ($fileld == '*') {
            $field = implode(',', $this->getDb()->quoteObj($this->getFields()));
        }
        return $this->getDb()->select($field)->from(self::TABLE_NAME)->where($cond)->queryRow();
    }
    
    /**
     * 根据条件更新数据.
     * 
     * @param unknown_type $param 要更新的数据.
     * @param unknown_type $cond    条件.
     * 
     * @return boolean
     */
    public function updateUserInfo($param = array(), $cond = array())
    {
        if (empty($cond) || empty($param)) return false;
        return $this->getDb(true)->update(self::TABLE_NAME, $param, $cond);
    }
}