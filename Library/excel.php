<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/5/30
 * Time: 21:36
 */
namespace Library;
require 'PHPExcel/PHPExcel.php';
require 'PHPExcel/PHPExcel/Reader/Excel2007.php';
class excel{

    private $dir = 'upload/excel';

    /**
     *
     * @param string $file 文件地址，如果为空获取上传的文件
     * @param int $beginRow 开始的行数
     * @param int $endRow 结束行数，小于开始行则获取不到
     * @param array $fields 各个栏目对应的字段，如果没有对应，显示栏目字母，如：A栏目对应username
     * @return array|string
     * @throws \PHPExcel_Reader_Exception
     */
    public function getExcelData($file='',$beginRow=1,$endRow=0,$fields=array()){

        if(!$file){
            //获取上传文件
            $file = $this->upload();
            if(is_array($file)){
                return $file;
            }
        }

        $PHPExcel = new \PHPExcel();
        $PHPReader=new \PHPExcel_Reader_Excel2007();
        $PHPExcel=$PHPReader->load($file);

        $currentSheet=$PHPExcel->getActiveSheet();

        $data = array();
        foreach ($currentSheet->getRowIterator() as $key=>$row) {
            if($key<$beginRow)
                continue;
            if($endRow>0 && $key>$endRow)
                break;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells,
            // even if it is not set.
            // By default, only cells
            // that are set will be
            // iterated.
            foreach ($cellIterator as $k=>$cell) {
                if(isset($fields[$k]) && $fields[$k]){
                    $data[$key][$fields[$k]] = $cell->getValue();
                }
                else{
                    $data[$key][$k] = $cell->getValue();
                }

            }

        }

        return $data;
    }

    /**
     * 图片上传
     * @param boolean $isForge 是否伪造数据提交
     */
    public function upload(){
        //图片上传
        $upObj = new upload(2048,array('xlsx','xls'));

        $upObj->setDir($this->hashDir());
        $upState = $upObj->execute();
        //检查上传状态
        foreach($upState as $key => $rs)
        {
            if(count($_FILES[$key]['name']) > 1)
                return tool::getSuccInfo(0,'文件数不能大于1');


            foreach($rs as $innerKey => $val)
            {
                if($val['flag']==1)
                {
                    //上传成功后图片信息
                    $fileName = $val['dir'].$val['name'];
                    $rs[$innerKey]['name'] = $val['name'];

                }
                else{
                    return tool::getSuccInfo(0,$rs[$innerKey]['errInfo'] = upload::errorMessage($val['flag']));
                }


               $photoArray[$key] = $rs[0];

            }
        }
        return $photoArray['no']['fileSrc'];

    }


    /**
     * @brief 获取图片散列目录
     * @return string
     */
    private  function hashDir()
    {
        $dir = $this->dir.'/'.date('Y/m/d');
        return $dir;
    }
}