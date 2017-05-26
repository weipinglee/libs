<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25 0025
 * Time: ���� 5:08
 */
namespace auto;
use Library\tool;
use Library\M;
class codeCreator
{
    protected $dbName = 'nn';
    protected $dbObj = null;
    public function __construct($db='')
    {
        $this->dbName = $db;
        $this->dbObj = new M($db);
    }

    protected function isTableExist($tableName){
        $sql = 'show tables from '.$this->dbName.' like "'.$tableName.'"';
        $res = $this->dbObj->query($sql);
        if(empty($res)){
            return false;
        }
        return true;
    }

    /**
     * ��ȡ���ֶ���Ϣ
     * @param $tableName string ����
     * @return array|\Library\DB\���ش�����
     */
    public function getTabelData($tableName){
        if($this->isTableExist($tableName)){
            $sql = 'show FULL FIELDS from '.$this->dbName.'.'.$tableName;
            $M = new \Library\M($tableName);
            $res = $M->query($sql);
            return $res;
        }
        else{
           return array();
        }


    }
}