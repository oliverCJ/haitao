<?php
namespace Module;
/**
 * 首页模块.
 *
 * @author oliver <cgjp123@163.com>
 */
class Index extends \Module\ModuleBase
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
}