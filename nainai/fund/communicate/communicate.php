<?php
/**
 * ���н��׵ķ����������
 * User: weipinglee
 * Date: 2018/1/16
 * Time: ���� 4:32
 */

namespace nainai\fund\communicate;


abstract class communicate
{
     public function __construct($encode='')
     {

     }

     abstract public function sendRequest($param,$url);

     abstract public function receiveMessage();
}