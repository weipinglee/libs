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
    const BID_CLOSE   = 5;//终止招标
    const BID_STOP   = 6;//截标
    const BID_OVER   = 7;//评标结束
    const BID_ABORT  = 8;//流标


    const REPLY_CREATE = 1;//报名成功
    const REPLY_CERTED = 2;//已上传资质文件
    const REPLY_CERT_VERIFYSUCC = 3;//资质审核通过
    const REPLY_CERT_VERIFYFAIL = 4;//资质审核驳回
    const REPLY_DOC_PAYED = 5;//已支付标书费用
    const REPLY_DOC_UPLOADED = 6;//上传投标文件
    const REPLY_PACKAGE_SUBMIT = 7;//报价完成
    const REPLY_SELECTED = 8;//已中标
    const REPLY_UNSELECTED = 9;//未中标




    protected $bidTable = 'bid';
    protected $bidPackageTable = 'bid_package';
    protected $bidReplyTable = 'bid_reply';
    protected $bidReplyCertTable = 'bid_reply_cert';
    protected $bidReplyPackTable = 'bid_reply_package';
    protected $bidNoticeTable = 'bid_notice';

}