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

    public $userTable = 'user';


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

    public function getReplyStatusText($status){
        switch($status){
            case  self::REPLY_CREATE : {
                return '报名成功';
            }
            case self::REPLY_CERTED :
                return '已上传资质文件';
            case self::REPLY_CERT_VERIFYFAIL :
                return '资质审核被驳回';
            case self::REPLY_CERT_VERIFYSUCC :
                return '资质审核通过';
            case self::REPLY_DOC_UPLOADED :
                return '已上传投标书';
            case self::REPLY_DOC_PAYED:
                return '已支付标书费用';
            case self::REPLY_PACKAGE_SUBMIT:
                return '报价完成，等待开标';

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
        $query->join = ' left join '.$this->cateTable .' as pc on b.top_cate = pc.id left join '.$this->userTable.' as u on b.user_id = u.id';
        $query->fields = 'b.* , pc.name as cate_name,u.username,u.mobile,u.true_name';
        $query->page = $page;
        $query->order = 'b.id desc';
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
                $res['list'][$key]['mode_text'] = $res['list'][$key]['mode'] == 'gk' ? '公开招标' : '邀请招标';
            }
        }

        return $res;


    }


    /**
     * 获取招标详情
     * @param $id int 招标id
     * @param array $where 查询条件
     * @return array
     */
    public function getBidDetail($id,$where=array())
    {
        $query = new Query($this->bidTable .' as b');
        $query->join = ' left join '.$this->cateTable .' as pc on b.top_cate = pc.id left join '.$this->userTable.' as u on u.id = b.user_id';
        $query->fields = 'b.* , pc.name as cate_name,u.username,u.mobile,u.true_name';
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
            $data['mode_text'] = $data['mode'] == 'gk' ? '公开招标' : '邀请招标';
            $data['eq'] = unserialize($data['eq']);
            $packageObj = new M($this->bidPackageTable);
            $data['package'] = $packageObj->where(array('bid_id'=>$id))->select();
        }
        return $data;


    }

    /**
     *根据招标id和投标用户id获取资质数据
     * @param $user_id int 投标用户id
     * @param $bid_id int 招标id
     */
    public function getUserReplyCerts($user_id,$bid_id)
    {
        $Query = new Query($this->bidReplyTable.' as br');
        $Query->join = 'left join '.$this->bidReplyCertTable.' as c on c.reply_id = br.id';
        $Query->fields = 'c.*,br.status';
        $Query->where = 'br.bid_id='.$bid_id.' and br.reply_user_id='.$user_id;
        $certs = $Query->find();
        return $certs;

    }


    /**
     * 获取投标列表
     * @param array $where 查询条件
     * @return array
     */
    public function getReplyList($page=1,$where=array()){
        $Query = new searchQuery($this->bidReplyTable.' as br ');
        $Query->join = 'left join '.$this->bidTable .' as b on b.id = br.bid_id left join '.$this->userTable.' as u on br.reply_user_id = u.id';
        $Query->fields = ' br.*,u.true_name,b.no,b.mode,b.pro_name,b.pro_address,b.begin_time,b.open_time,b.end_time,b.pack_type,b.doc_begin,b.doc_price,b.supply_bail ';
        $Query->page = $page;
        $Query->order = 'br.id desc';
        if(!empty($where)){
            $Query->where = $where[0];
            $Query->bind = $where[1];
        }

        $res = $Query->find();
        if(!empty($res['list'])){
            foreach($res['list'] as $key=>$val){
                $res['list'][$key]['status_text'] = $this->getReplyStatusText($val['status']);
                $res['list'][$key]['pack_type_text'] = $this->getPackType($val['pack_type']);
                $res['list'][$key]['mode_text'] = $res['list'][$key]['mode'] == 'gk' ? '公开招标' : '邀请招标';
            }
        }
        return $res;
    }

    public function getReplyDetail($id){
        $Query = new Query($this->bidReplyTable.' as br ');
        $Query->join = 'left join '.$this->bidTable .' as b on b.id = br.bid_id ';
        $Query->fields = ' br.*,b.no,b.mode,b.pro_name,b.pro_address,b.begin_time,b.open_time,b.end_time,b.pack_type,b.doc_begin,b.doc_price,b.supply_bail ';
        $Query->where = 'br.id=:id';
        $Query->bind = array('id'=>$id);


        $res = $Query->getObj();
        $res['status_text'] = $this->getReplyStatusText($res['status']);
        $res['pack_type_text'] = $this->getPackType($res['pack_type']);
        $res['mode_text'] = $res['mode'] == 'gk' ? '公开招标' : '邀请招标';

        return $res;
    }


}