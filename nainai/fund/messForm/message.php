<?php
/**
 * 通信报文基类
 * User: weipinglee
 * Date: 2018/1/16
 *
 */

namespace nainai\fund\messForm;


abstract class message
{

     protected $encoding = 'GBK';
     protected $depth ;

    /**
     * 通信报文基类构造方法
     * @param string $encoding 编码
     * @param int $depth 报文结果的深度
     */
    public function __construct($encoding='GBK',$depth=0)
    {
        $this->encoding = $encoding;
        $this->depth = $depth;
    }

    /**
     * 生成报文
     * @param $message
     * @return mixed
     */
      abstract public function create($message);

    /**
     * 解析报文
     * @param $message
     * @return mixed
     */
      abstract public function parse($message);


}