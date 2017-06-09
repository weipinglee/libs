<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file bidQuery.php
 * @brief 招标查询基础类
 * @author weipinglee
 * @date 2017-6-9
 * @version 1.0
 */

namespace nainai\bid\query;

use \Library\searchQuery;
use \Library\Query;
use nainai\bid\bidBase;
use \Library\M;

class bidQuery extends bidBase
{

    public $cateTable = 'product_category';


    public function getBidStatusText($status){
        switch($status){
            case  self::BID_INIT : {
                return '等待缴纳保证金';
            }
            case self::BID_RELEASE_WAITVERIFY :
                return '已发布，等待审核';
            case self::BID_RELEASE_VERIFYSUCC :
                return '发布成功';
            case self::BID_RELEASE_VERIFYFAIL :
                return '后台驳回';
            case self::BID_CANCLE :
                return '已撤销';
            case self::BID_CLOSE:
                return '已终止';

        }
        return '未知';
    }

    private function getPackType($type){
        if($type==1)
            return '分包';
        return '总包';
    }

    /**
     * @param int $page 页码
     * @param array $where 查询条件 [0]=>查询字符串，[1]=>绑定参数数组
     * @return array
     */
    public function getBidList($page=1,$where=array())
    {
        $query = new searchQuery($this->bidTable .' as b');
        $query->join = ' left join '.$this->cateTable .' as pc on b.top_cate = pc.id';
        $query->field = 'b.* , pc.name as cate_name';
        $query->page = $page;
        if(!empty($where)){
            $query->where = $where[0];
            if(isset($where[1])){
                $query->bind = $where[1];
            }
        }
        $res = $query->find();
        if(!empty($res['list'])){
            foreach($res['list'] as $key=>$val){
                $res['list'][$key]['status_text'] = $this->getBidStatusText($val['status']);
                $res['list'][$key]['pack_type_text'] = $this->getPackType($val['pack_type']);
                $res['list'][$key]['open_way_text'] =  $res['list'][$key]['open_way']==1 ? '线上' : '线下';
            }
        }

        return $res;


    }


    public function getBidDetail($id,$where=array())
    {
        $query = new Query($this->bidTable .' as b');
        $query->join = ' left join '.$this->cateTable .' as pc on b.top_cate = pc.id';
        $query->field = 'b.* , pc.name as cate_name';
        $query->limit = 1;
        if(!empty($where)){
            $query->where = 'b.id = :id and '.$where[0];
            $query->bind = array_merge(array('id'=>$id),$where[1]);
        }
        else{
            $query->where = 'b.id = :id';
            $query->bind = array('id'=>$id);
        }


        $data = $query->getObj();
        if(!empty($data)){
            $data['status_text'] = $this->getBidStatusText($data['status']);
            $data['pack_type_text'] = $this->getPackType($data['pack_type']);
            $data['open_way_text'] = $data['open_way'] == 1 ? '线上' : '线下';
            $packageObj = new M($this->bidPackageTable);
            $data['package'] = $packageObj->where(array('bid_id'=>$id))->select();
        }
        return $data;


    }
}