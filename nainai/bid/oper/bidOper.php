<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file stateBase.php
 * @brief �б��ʼ����
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\state;
use \Library\M;

class bidOper extends \nainai\bid\bidBase
{

    protected $bidModel = null;

    protected $rules = array(

    );
    public function __construct()
    {
        $this->bidModel = new M($this->bidTable);

    }

    public function createNewBid($arg=array())
    {

    }

    /**
     * �����б�,�����û���֤��
     * @param $bid_id int �б�id
     */
    public function releaseBid($bid_id)
    {

    }

    /**
     * �б귢�����
     * @param $bid_id int �б�id
     * @param $status int ���״̬��1��ͨ����0���ܾ�
     */
    public function verifyBid($bid_id,$status){

    }



    /**
     * ��ȡ��֤������
     */
    protected function getBidDeposit()
    {
        return 0;
    }

    /**
     * �����б����
     * @return string
     */
    protected function createBidNo()
    {
        return  'zb'.date('YmdHis') . rand(100000, 999999);
    }

}