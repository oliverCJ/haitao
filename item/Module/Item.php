<?php
namespace Module;
/**
 * 首页模块.
 *
 * @author oliver <cgjp123@163.com>
 */
class Item extends \Module\ModuleBase
{
    public static function instance()
    {
        return parent::instance();
    }
    
    /**
     * 获取当前可用的广告.
     * 
     * @param unknown_type $adPosId
     * @param unknown_type $num
     */
    public function getUseableAd($adPosId, $num)
    {
        $time = time();
        $cond = array(
                'group_id' => $adPosId,
                "`stime` <= {$time}",
                "`etime` >= {$time}",
                '`isopen` = 1' // 字段值待定
                );
        $order = '`create_time` DESC';
        return \Model\Ad::instance()->getAd($cond, $num, $order);
    }
    
    /**
     * 根据条件获取特卖.
     * 
     * @param unknown_type $cond
     * @param unknown_type $order
     * @param unknown_type $limit
     */
    public function getActivityByCond($cond, $order =  'displayorder DESC', $limit = 20)
    {
        return \Model\Activity::instance()->getActivityByCond($cond, $order, $limit);
    }
    
    /**
     * 根据特卖ID获取商品.
     * 
     * @param unknown_type $activityId
     * @param unknown_type $order
     * @param unknown_type $page
     * @param unknown_type $pageSize
     */
    public function getProductByActivityId($activityId, $mustStock = true, $order = 'displayorder DESC', $page = 1, $pageSize = 20)
    {
        $cond = array(
                'activity_id' => $activityId,
                'status' => 0,
        );
        if ($mustStock) {
            $cond[] = 'stock > 0';
        }
        
        return \Model\Activity::instance()->getProductByCond($cond, $order, $page, $pageSize);
    }
    
    public function getBrandList($cond, $order =  'displayorder DESC', $page = 1, $pageSize = 20)
    {
        return \Model\Brand::instance()->getBrandListByCond($cond, $order, $page, $pageSize);
    }
}