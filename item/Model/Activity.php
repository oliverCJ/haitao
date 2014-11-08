<?php
namespace Model;

/**
 * 特卖数据操作.
 *
 * @author oliver <cgjp123@163.com>
 */
class Activity extends \Db\DbBase
{
    const TABLE_NAME = 'haiou_activity';
    const TABLE_PRODUCT_NAME = 'haiou_activity_product_list';
    
    protected $fields = array(
            'id', // int(10) NOT NULL auto_increment ID
            'title', // varchar(100) NOT NULL 标题
            'desc', // text 描述
            'pic', // varchar(200) NOT NULL 图片地址	
            'discount', // varchar(20) NULL 折扣
            'start_time', // int(10) NOT NULL 开始时间
            'end_time', // int(10) NOT NULL 结束时间
            'catid', // int(10) NOT NULL 分类id
            'brandid', // int(10) NOT NULL 品牌id
            'status', // tinyint(1) NOT NULL default 0 状态 
            'displayorder', // smallint( 6 ) NOT NULL default 0 排序
            'create_user', // varchar(30)  NULL 发布人
            'create_time', // timestamp   NOT NULL 发布时间
    );
    
    protected $fieldsProduct = array(
            'id', // int(10) NOT NULL auto_increment ID
            'activity_id', // int(10) NOT NULL 特卖ID
            'product_id', // int(10) NOT NULL 产品ID
            'product_name', // varchar(100) NOT NULL 产品名称
            'price', // decimal(10,2) default NULL 价格
            'stock', // int(10) default NULL 数量
            'start_time', // int(10) NOT NULL 开始时间
            'end_time', // int(10) NOT NULL 结束时间
            'status', // tinyint(1) NOT NULL 状态
            'displayorder', // smallint( 6 ) NOT NULL default 0 排序
            'create_user', // varchar(30)  NULL 发布人
            'create_time', // timestamp   NOT NULL 发布时间
    );
    
    public function getFields()
    {
        return $this->fields;
    }
    
    public function getFieldsProduct()
    {
        return $this->fieldsProduct;
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
}