<?php
/**
 * ͨ�ű��Ļ���
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
     * ͨ�ű��Ļ��๹�췽��
     * @param string $encoding ����
     * @param int $depth ���Ľ�������
     */
    public function __construct($encoding='GBK',$depth=0)
    {
        $this->encoding = $encoding;
        $this->depth = $depth;
    }

    /**
     * ���ɱ���
     * @param $message
     * @return mixed
     */
      abstract public function create($message);

    /**
     * ��������
     * @param $message
     * @return mixed
     */
      abstract public function parse($message);


}