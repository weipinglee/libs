<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file bidBase.php
 * @brief 招投标基础类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid;


abstract class bidBase
{
    const BID_UNINIT = -1;
    const BID_INIT = 0;//发布初始化，未缴纳保证金
    const BID_RELEASE_WAITVERIFY = 1;//发布待审核
    const BID_RELEASE_VERIFYSUCC = 2;//发布审核成功
    const BID_RELEASE_VERIFYFAIL = 3;//发布审核驳回
    const BID_CANCLE = 4; //招标撤销


    protected $bidTable = 'bid';
    protected $bidPackageTable = 'bid_package';
    protected $bidReplyTable = 'bid_reply';
    protected $bidReplyCertTable = 'bid_reply_cert';
    protected $bidReplyPackTable = 'bid_reply_package';

}