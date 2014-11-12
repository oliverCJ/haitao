<?php
namespace Model;
/**
 * 广告数据操作类.
 *
 * @author oliver <cgjp123@163.com>
 */
class Ad extends \Db\DbBase
{
    const TABLE_NAME = 'haiou_advs_con';
    
    protected $fields = array(
            'id', // int(4) NOT NULL auto_increment ID
            'userid', // int(11) default NULL 会员ID
            'group_id', // int(5) default NULL 组ID
            'name', // varchar(50) NOT NULL default 广告名称
            'type', // varchar(20) default NULL 广告类型
            'url', // varchar(200) default NULL 广告地址
            'con', // mediumtext 广告描述
            'isopen', // int(1) default 0 是否开启
            'province', // varchar(50) default NULL 省
            'city', // varchar(50) default NULL 市
            'area', // varchar(50) default NULL 区
            'width', // char(4) default NULL 广告宽度
            'height', // char(4) default NULL广告高度
            'catid', // int(8) default NULL 产品类别ID
            'unit', // enum(dayweekmonth) default NULL 广告单位
            'show_time', // tinyint(4) default 0 广告展出时间
            'status', // tinyint(1) default 0  0:待支付1:购买成功
            'shownum', // int(11) unsigned default 1 展示次数
            'stime', // int(10) unsigned default NULL开始时间
            'etime', // int(10) unsigned default NULL结束时间
            'create_user', // varchar(30)  NULL 发布人
            'create_time', // timestamp   NOT NULL 发布时间
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
     * 获取广告.
     * 
     * @param unknown_type $cond
     * @param unknown_type $num
     * @param unknown_type $fileld
     * @return boolean
     */
    public function getAd($cond, $num, $order = '`id` DESC', $field = '*')
    {
        if (empty($cond) || empty($order) || empty($num)) return false;
        if ($field == '*') {
            $field = implode(',', $this->getDb()->quoteObj($this->getFields()));
        }
        return $this->getDb()->select($field)->from(self::TABLE_NAME)->where($cond)->order($order)->limit($num)->queryAll();
    }
}