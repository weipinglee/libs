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
     * @param string $file �ļ���ַ�����Ϊ�ջ�ȡ�ϴ����ļ�
     * @param int $beginRow ��ʼ������
     * @param int $endRow ����������С�ڿ�ʼ�����ȡ����
     * @param array $fields ������Ŀ��Ӧ���ֶΣ����û�ж�Ӧ����ʾ��Ŀ��ĸ���磺A��Ŀ��Ӧusername
     * @return array|string
     * @throws \PHPExcel_Reader_Exception
     */
    public function getExcelData($file='',$beginRow=1,$endRow=0,$fields=array()){

        if(!$file){
            //��ȡ�ϴ��ļ�
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