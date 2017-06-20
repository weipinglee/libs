<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/5/30
 * Time: 21:36
 */
namespace Library;
class excel{

    private $dir = 'upload/excel';

    public function getExcelData($file=''){

        if(!$file){
            //��ȡ�ϴ��ļ�
            $file = $this->upload();
            if(is_array($file)){
                return $file;
            }
        }

        require 'PHPExcel/PHPExcel.php';
        require 'PHPExcel/PHPExcel/Reader/Excel2007.php';

        $PHPExcel = new \PHPExcel();
        $PHPReader=new \PHPExcel_Reader_Excel2007();
        $PHPExcel=$PHPReader->load($file);

        $currentSheet=$PHPExcel->getActiveSheet();


        echo '<table>';
        foreach ($currentSheet->getRowIterator() as $row) {
            echo '<tr>' . "\n";

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells,
            // even if it is not set.
            // By default, only cells
            // that are set will be
            // iterated.
            foreach ($cellIterator as $cell) {
                echo '<td>' . $cell->getValue() . '</td>' . "\n";
            }

            echo '</tr>' . "\n";
        }
        echo '</table>';


       // return $arr;
    }

    /**
     * ͼƬ�ϴ�
     * @param boolean $isForge �Ƿ�α�������ύ
     */
    public function upload(){
        //ͼƬ�ϴ�
        $upObj = new upload(2048,array('xlsx','xls'));

        $upObj->setDir($this->hashDir());
        $upState = $upObj->execute();
        //����ϴ�״̬
        foreach($upState as $key => $rs)
        {
            if(count($_FILES[$key]['name']) > 1)
                return tool::getSuccInfo(0,'�ļ������ܴ���1');


            foreach($rs as $innerKey => $val)
            {
                if($val['flag']==1)
                {
                    //�ϴ��ɹ���ͼƬ��Ϣ
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
     * @brief ��ȡͼƬɢ��Ŀ¼
     * @return string
     */
    private  function hashDir()
    {
        $dir = $this->dir.'/'.date('Y/m/d');
        return $dir;
    }
}