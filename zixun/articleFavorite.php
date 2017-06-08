<?php

/**
 * @copyright (c) 2017 nainaiwang.com
 * @file articleFavorite.php
 * @brief 文章收藏类
 * @author weipinglee
 * @date 2017-5-23
 * @version 1.0
 */
namespace zixun;
use Library\M;
use Library\tool;
use Library\Query;
class articleFavorite
{

    protected $favorite_table = 'user_favorite';
    protected $user_table = 'user';

    protected $rules = array(
        array('id', 'number', 'id错误', 0, 'regex'),
        array('user_id', 'number', '', 0, 'regex'),
        array('article_id', 'number', '文章id错误', 0, 'regex'),

        array('time', 'datetime', '日期错误', 0, 'regex'),
    );
    /**
     * 收藏数据写入数据库
     * @param $data array 收藏数据
     */
    public function add($data){
        $obj = new M($this->favorite_table);
        if($obj->data($data)->validate($this->rules,$data)){
           if(!$obj->where(array('user_id'=>$data['user_id'],'article_id'=>$data['article_id']))->getObj()){
                if( $obj->add()){
                    return tool::getSuccInfo();
                }
            }
        }
        return tool::getSuccInfo(0,$obj->getError());
    }

    /**
     * 收藏数据删除
     * @param $data array 收藏数据
     */
    public function cancle($data){
        $obj = new M($this->favorite_table);
        if($obj->data($data)->validate($this->rules,$data)){
            if($res = $obj->where(array('user_id'=>$data['user_id'],'article_id'=>$data['article_id']))->getObj()){
                if( $obj->where(array('id'=>$res['id']))->delete()){
                    return tool::getSuccInfo();
                }
            }
        }
        return tool::getSuccInfo(0,$obj->getError());
    }

    public function getList($user_id=0,$page=1){
        if($user_id){
            $obj = new Query($this->favorite_table .' as f');
            $obj->join = 'left join article as a on f.article_id = a.id left join article_cover as ac on a.id = ac.article_id';
            $obj->feilds = 'a.id,a.name,a.create_time,ac.url';
            $obj->where = ' f.user_id = '.$user_id.' and a.id is not null';
            $obj->distinct = 'f.id';
            $obj->page = $page;
			$obj->pagesize = 10;
            return $obj->find();
        }
        return array();
    }


}