<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @brief ÕĞ±êÆÀÂÛÀà
 * @author weipinglee
 * @date 2017-6-22
 * @version 1.0
 */

namespace nainai\bid\comment;
use \Library\M;
use \Library\thumb;
class bidcomment extends \zixun\articleComment
{
    protected $comment_table = 'bid_comment';

    protected $userinfo_table = 'user';

    protected $article_table = 'bid';

    protected $favorite_table = 'bid_comment_favorite';

    protected function getUserInfo($user_id)
    {
        $userObj = new M($this->userinfo_table);
        if ($user_id) {
            $userData = $userObj->where(array('id' => $user_id))->fields('username as nick,head_photo as head_pic')->getObj();
            if(isset($userData['head_pic']) && $userData['head_pic'])
                $userData['head_pic'] = thumb::getOrigImg($userData['head_pic']);
            else
                $userData['head_pic'] = '';
            $userData['birth'] = '';
            $userData['sign'] = '';
            return $userData;
        }
        return array();
    }

}