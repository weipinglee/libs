<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file articleComment.php
 * @brief 文章评论类
 * @author weipinglee
 * @date 2017-5-27
 * @version 1.0
 */

namespace zixun;
use \Library\M;
use \Library\tool;
use \Library\Query;
use \Library\time;

class articleComment
{

    protected $comment_table = 'article_comment';

    protected $userinfo_table = 'user_info';

    protected $article_table = 'article';

    protected $favorite_table = 'article_comment_favorite';

    protected $comment_rules = array(
        array('id', 'number', 'id错误', 0, 'regex'),
        array('user_id', 'number', '', 0, 'regex'),
        array('article_id', 'number', '', 0, 'regex'),
        array('top_comment_id', 'number', '文章id错误', 0, 'regex'),
       // array('content', 'number', '文章id错误', 0, 'regex'),
        array('create_time', 'datetime', '日期错误', 0, 'regex'),
    );

    /**
     * 获取用户资讯系统的信息
     * @param $user_id int 用户id
     * @return array
     */
    protected function getUserInfo($user_id)
    {
        $userObj = new M($this->userinfo_table);
        if( $user_id){
            $userData = $userObj->where(array('user_id'=>$user_id))->getObj();
            return $userData;
        }
        return array();
    }

    /**
     * 判断文章是否存在
     * @param $id
     * @return bool
     */
    protected function existArticle($id)
    {
        if(is_int($id)){
            $articleObj = new M($this->article_table);
            if(!empty($articleObj->where(array('id'=>$id))->getObj())){
                return true;
            }
        }
        return false;


    }

    /**
     * 针对文章发表评论
     * @param $article_id int 文章id
     * @param $text string 评论内容
     * @return array
     */
    public function addComment($article_id,$text,$user_id=0)
    {

        if($this->existArticle($article_id) && $text){
            $userData = $this->getUserInfo($user_id);
            $data = array(
                'article_id'=> $article_id,
                'content' => $text,
				'user_id' => $user_id,
				'creat_time' => time::getDateTime(),
            );
            if(!empty($userData)){
                $data['curr_nick'] = $userData['nick'];
                $data['curr_head'] = $userData['head_pic'];
            }
            $commentObj = new M($this->comment_table);
            if($commentObj->data($data)->add()){
                return tool::getSuccInfo();
            }


        }
        return tool::getSuccInfo(0,'评论失败');
    }

    /**
     * 获取评论数据
     * @param $comment_id
     * @return array
     */
    public function getComment($comment_id)
    {
        $commentObj = new M($this->comment_table);
        $comData = $commentObj->where(array('id'=>$comment_id))->getObj();
        if(!empty($comData)){
            return $comData;
        }
        return array();
    }

    /**
     * 回复评论
     * @param $comment_id int 回复针对的评论id
     * @param $text string 评论内容
     * @param int $user_id 评论用户id
     * @return array
     */
    public function replyComment($comment_id,$text,$user_id=0)
    {
        $userData = $this->getUserInfo($user_id);
        $comData = $this->getComment($comment_id);
        if(!empty($comData)){
            $data = array(
                'article_id'=> $comData['article_id'],
                'content' => $text,
                'parent_id'=> $comment_id,
                //如果父评论top_comment_id为0，则为$comment_id,若不为0，则顶级评论id与父评论一样
                'top_comment_id' => $comData['top_comment_id'] ? $comData['top_comment_id'] : $comment_id,
                'reply_nick' => $comData['curr_nick']
            );
            if(!empty($userData)){
                $data['curr_nick'] = $userData['nick'];
                $data['curr_head'] = $userData['head_pic'];
            }
            $commentObj = new M($this->comment_table);
            if($commentObj->data($data)->add()){
                //给回复的评论增加回复次数
                $commentObj->where(array('id'=>$comment_id))->setInc('reply_times');
                return tool::getSuccInfo();
            }
        }

        return tool::getSuccInfo(0,'评论失败');

    }

    /**
     * 获取文章顶级评论列表
     * @param $article_id int 文章id
     * @param $page int 页码
     */
    public function commentList($article_id,$page=1)
    {
        //子评论显示数量
        $subNum = 3;
        $Q = new Query($this->comment_table .' as c');
        $Q->page = $page;
        $Q->pagesize = 10;
        $Q->where = 'c.article_id = :article_id and c.top_comment_id = 0';//查询顶级评论
        $Q->bind = array('article_id'=>$article_id);
        $data = $Q->find();
        if(!empty($data)){
            $commentObj = new M($this->comment_table);
            foreach($data as $key=>$item){
                if($item['reply_times']>0){
                    $data[$key]['reply'] = $commentObj->where(array('parent_id'=>$data['id']))->limit($subNum)->select();
                }
            }
            return $data;
        }
        return array();
    }

    /**
     * 获取某条评论的回复评论
     * @param $comment_id int 评论id
     * @param int $page 页码
     * @return array
     */
    public function replyCommentList($comment_id,$page=1)
    {
        $Q = new Query($this->comment_table .' as c');
        $Q->page = $page;
        $Q->pagesize = 10;//每次刷新获取10条
        $Q->where = 'c.top_comment_id=:comment_id';
        $Q->bind = array('top_comment_id'=>$comment_id);
        $data = $Q->find();
        return $data;
    }

    /**
     * 给评论点赞功能
     * @param $comment_id int 评论id
     * @param $user_id int 用户id
     * @return array
     */
    public function addFavorite($comment_id,$user_id){
        if($this->getComment($comment_id)){
            $favObj = new M($this->favorite_table);
            //如果已经评论过
            if($favObj->where(array('comment_id'=>$comment_id,'user_id'=>$user_id))->getField('id')){
                return tool::getSuccInfo(0,'已经点过赞了');
            }
            $data = array(
                'comment_id'=>$comment_id,
                'user_id'=>$user_id,
                'click_time'=>\Library\time::getDateTime()
            );
            if($favObj->data($data)->add()){
                $comObj = new M($this->comment_table);
                $comObj->where(array('id'=>$comment_id))->setInc('point');//评论的点赞数增加1
                return tool::getSuccInfo();
            }

        }
        return tool::getSuccInfo(0,'点赞失败');
    }


}