<?php
/**
 * ÓÃ»§ÕË»§¹ÜÀíÀà
 * author: weipinglee
 * Date: 2016/4/20
 * Time: 16:18
 */
namespace nainai\fund;
use nainai\order\Order;
abstract class account{

    /**
     * »ñÈ¡¿ÉÓÃÓà¶î
     * @param int $user_id
     */
    abstract protected function getActive($user_id);

    /**
     * »ñÈ¡¶³½á×Ê½ð½ð¶î
     * @param int $user_id ÓÃ»§id
     */
    abstract protected function getFeeze($user_id);
    /**
     * Èë½ð²Ù×÷
     * @param int $user_id ÓÃ»§id
     * @param $num float Èë½ð½ð¶î
     */
    abstract protected function in($user_id,$num);



    /**
     * ×Ê½ð¶³½á
     * @param int $user_id ÓÃ»§id
     * @param float $num ¶³½á½ð¶î
     */
    abstract protected function freeze($user_id,$num,$clientID='');

    /**
     * ¶³½á×Ê½ðÊÍ·Å
     * @param int $user_id
     * @param float $num ÊÍ·Å½ð¶î
     */
    abstract protected function freezeRelease($user_id,$num,$note,$freezeno='');

    /**
     * ¶³½á×Ê½ðÖ§¸¶
     * ½«¶³½á×Ê½ð½â¶³£¬Ö§¸¶¸øÁíÍâÒ»¸öÓÃ»§
     * @param int $from ¶³½áÕË»§ÓÃ»§id
     * @param int $to  ×ªµ½µÄÕË»§ÓÃ»§id
     * @param float $num ×ªÕËµÄ½ð¶î
     *
     */
    abstract protected function freezePay($from,$to,$num,$note='',$amount='');

    /**
     * ¿ÉÓÃÓà¶îÖ±½Ó¸¶¿î¸øÊÐ³¡
     * @param int $user_id Ö§¸¶ÓÃ»§id
     * @param float $num ×ªÕËµÄ½ð¶î
     */
    abstract protected function payMarket($user_id,$num);


}