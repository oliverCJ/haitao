<?php
namespace Handler;

/**
 * 首页接口.
 *
 * @author chengjun <cgjp123@163.com>
 *
 */
class Item
{
    /**
     * 返回码预定义.
     *
     * @var array
     */
    public static $reponse = array(
            'CATCH_EXCEPTION'                 => array('code' => '-2', 'msg' => 'sorry, system catch exception'),
            'SYSTEM_BUSY'                         => array('code' => '-1', 'msg' => 'system busy, please try again later'),
            'SUCCESS'                                  => array('code' => '0', 'msg' => 'success'),
            'REGISTER_DATA_ERROR'          => array('code' => '20001', 'msg' => 'register data error: %s'),
            'ILLEGAL_ADPOSID'                   => array('code' => '20002', 'msg' => 'illegal ad position id'),
            'ILLEGAL_NUM'                          => array('code' => '20003', 'msg' => 'the number must be positive integer'),
            'EMAIL_USED'                             => array('code' => '20004', 'msg' => 'email had used'),
            'MOBILE_USED'                          => array('code' => '20005', 'msg' => 'mobile had used'),
            'ILLEGAL_CATEID'                       => array('code' => '20006', 'msg' => 'illegal category id'),
            'ILLEGAL_BRANDID'                    => array('code' => '20007', 'msg' => 'illegal brand id'),
            'ILLEGAL_ACTIVITY'                     => array('code' => '20008', 'msg' => 'illegal activity id'),
            'ILLEGAL_SORT_KEY'                   => array('code' => '20009', 'msg' => 'illegal sort key'),
            'ILLEGAL_COUNTRYID'                => array('code' => '20010', 'msg' => 'illegal country id'),
            );
    
    /**
     * 获取广告.
     * 
     * @param unknown_type $adPosId
     * @param unknown_type $num
     */
    public function getUseableAd($adPosId, $num = 4)
    {
        if (empty($adPosId) || !ctype_digit((string)$adPosId) || $adPosId < 1) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_ADPOSID']);
        }
        if (!ctype_digit((string)$num) || $num < 1) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_NUM']);
        }
        
        $result = \Module\Item::instance()->getUseableAd($adPosId, $num);
        if (!empty($result)) {
            return \Helper\Helper::reponseData(self::$reponse['SUCCESS'], $result);
        }
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS']);
    }
    
    /**
     * 获取当前正在进行到特卖.
     * 
     * @param unknown_type $brandId
     * @param unknown_type $num
     */
    public function getUseableActivity($cateId = 0, $brandId = 0, $num = 10)
    {
        if (!ctype_digit((string)$cateId) || $cateId < 0 ) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_CATEID']);
        }
        if (!ctype_digit((string)$brandId) || $brandId < 0 ) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_BRANDID']);
        }
        if (!ctype_digit((string)$num) || $num < 1 ) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_NUM']);
        }
        $time = time();
        $cond = array(
                "`start_time` <= $time",
                "`end_time` >= $time",
                'status' => 0,
        );
        if (!empty($cateId)) {
            $cond['catid'] = $cateId;
        }
        if (!empty($brandId)) {
            $cond['brandid'] = $brandId;
        }
        $orderBy = '`displayorder` ASC';
        
        $result = \Module\Item::instance()->getActivityByCond($cond, $orderBy, $num);
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS'], $result);
    }
    
    /**
     * 获取即将进行到特卖.
     *
     * @param unknown_type $brandId
     * @param unknown_type $num
     */
    public function getWillStartActivity($cateId = 0, $brandId = 0, $num = 10)
    {
        if (!ctype_digit((string)$cateId) || $cateId < 0 ) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_CATEID']);
        }
        if (!ctype_digit((string)$brandId) || $brandId < 0 ) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_BRANDID']);
        }
        if (!ctype_digit((string)$num) || $num < 1 ) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_NUM']);
        }
        $time = time();
        $cond = array(
                "`start_time` > $time",
                'status' => 0,
        );
        if (!empty($cateId)) {
            $cond['catid'] = $cateId;
        }
        if (!empty($brandId)) {
            $cond['brandid'] = $brandId;
        }
        $orderBy = '`start_time` DESC';
        
        $result = \Module\Item::instance()->getActivityByCond($cond, $orderBy, $num);
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS'], $result);
    }
    
    /**
     * 获取已经结束的特卖.
     *
     * @param unknown_type $brandId
     * @param unknown_type $num
     */
    public function getTheEndActivity($cateId = 0, $brandId = 0, $num = 10)
    {
        if (!ctype_digit((string)$cateId) || $cateId < 0 ) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_CATEID']);
        }
        if (!ctype_digit((string)$brandId) || $brandId < 0 ) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_BRANDID']);
        }
        if (!ctype_digit((string)$num) || $num < 1 ) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_NUM']);
        }
        $time = time();
        $cond = array(
                "`end_time` < $time",
                'status' => 0,
        );
        if (!empty($cateId)) {
            $cond['catid'] = $cateId;
        }
        if (!empty($brandId)) {
            $cond['brandid'] = $brandId;
        }
        $orderBy = '`end_time` DESC';
        
        $result = \Module\Item::instance()->getActivityByCond($cond, $orderBy, $num);
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS'], $result);
    }
    
    /**
     * 根据特卖ID获取商品,带分页.
     * 
     * @param unknown_type $activityId
     * @param unknown_type $sortBy
     * @param unknown_type $sort
     * @param unknown_type $page
     * @param unknown_type $pageSize
     */
    public function getProductByActivityId($activityId, $mustStock = true , $sortBy = 'displayorder', $sort = 'DESC', $page = 1, $pageSize = 20)
    {
        $mustStock = !!$mustStock;
        if (!ctype_digit((string)$activityId) || $activityId < 0 ) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_ACTIVITY']);
        }
        $keyArray = \Model\Activity::instance()->getFieldsProduct();
        if (!in_array($sortBy, $keyArray)) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_SORT_KEY']);
        }
        
        $order = $sortBy . ' ' . $sort;
        $result = \Module\Item::instance()->getProductByActivityId($activityId, $mustStock, $order, $page, $pageSize);
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS'], $result);
    }
    
    /**
     * 获取品牌列表
     * 
     * @param unknown_type $countryId
     * @param unknown_type $page
     * @param unknown_type $pageSize
     */
    public function getBrandList($countryId = 0, $page = 1, $pageSize = 20)
    {
        if (!ctype_digit((string)$countryId) || $countryId < 0 ) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_COUNTRYID']);
        }
        if (!empty($countryId)) {
            $cond = array(
                    'catid' => $countryId,
                    'status' => 0,
                    );
        }
        $order = 'displayorder DESC';
       $result =  \Module\Item::instance()->getBrandList($cond, $order, $page, $pageSize);
       return \Helper\Helper::reponseData(self::$reponse['SUCCESS'], $result);
    }
    
    
    
    /**
     * 获取分类.
     * 
     * @param unknown_type $catId
     */
    public function getCategory($catId = 0)
    {
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS']);
    }
    
}