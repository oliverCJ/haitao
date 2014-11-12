<?php
namespace Model;
/**
 * 广告数据操作类.
 *
 * @author oliver <cgjp123@163.com>
 */
class Brand extends \Db\DbBase
{
    const TABLE_NAME = 'haiou_brand';
    const TABLE_COUNTRY_NAME = 'haiou_brand';
    
    protected $fields = array(
        'id', // int(11) NOT NULL AUTO_INCREMENT,
        'name', // varchar(80) NOT NULL,
        'char_index', // char(1) NOT NULL,
        'catid', // int(11) NOT NULL,
        'catname', // varchar(20) NOT NULL,
        'logo', // varchar(150) NOT NULL,
        'displayorder', // smallint(6) NOT NULL DEFAULT '0',
        'pic', // varchar(500) DEFAULT NULL,
        'story', // text,
        'status', // tinyint(1) NOT NULL DEFAULT '1',
        'create_user', // varchar(30) DEFAULT NULL,
        'create_time', // timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    );
    
    protected $countryFields = array(
        'id', // int(11) NOT NULL AUTO_INCREMENT,
        'parent_id', // int(11) NOT NULL DEFAULT '0',
        'displayorder', // smallint(6) NOT NULL DEFAULT '0',
        'catname', // varchar(100) NOT NULL,
        'short_code', // char(2) NOT NULL,
        'create_user', // varchar(30) DEFAULT NULL,
        'create_time', // timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    );
    
    public function getFields()
    {
        return $this->fields;
    }
    
    public function getCountryFields()
    {
        return $this->countryFields;
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
     * 获取品牌,带分页.
     * 
     * @param unknown_type $cond
     * @param unknown_type $num
     * @param unknown_type $fileld
     * @return boolean
     */
    public function getBrandListByCond($cond, $order, $page = 1, $pageSize = 20, $field = '*')
    {
        if (empty($cond) || empty($order)) return false;
        if ($field == '*') {
            $field = implode(',', $this->getDb()->quoteObj($this->getFields()));
        }
        $limitStart = ($page - 1) * $pageSize;
        $limitEnd = $pageSize;
        return $this->getDb()->select($field)->from(self::TABLE_NAME)->where($cond)->order($order)->limit($limitStart, $limitEnd)->queryAll();
    }
    
}