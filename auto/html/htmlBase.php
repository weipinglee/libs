<?php

/**
 * @copyright (c) nainaiwang.com
 * @file htmlBase.php
 * @brief html模板定义基类
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
    //列表html
    abstract public function listPage();

    //详情
    abstract public function detailPage();

    //表单
    abstract public function formPage();
}