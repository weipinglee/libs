<?php

/**
 * @copyright (c) nainaiwang.com
 * @file htmlBase.php
 * @brief htmlģ�嶨�����
 * @author weipinglee
 * @date 2017-05-26
 * @version 1.0
 */
namespace auto\html;
abstract class htmlBase
{
    public $listTags = array();

    public $detailTags = array();

    public $formTags = array();
    //�б�html
    abstract public function listPage();

    //����
    abstract public function detailPage();

    //��
    abstract public function formPage();
}