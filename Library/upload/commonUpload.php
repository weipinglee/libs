<?php
namespace Library\upload;
use \Library\upload;
class commonUpload{



	private $dir = 'upload';

	private $allowType = array('doc','xls','pdf');

	//构造函数
	function __construct($dir = '')
	{
		//设置默认路径地址
		if($dir == '')
		{
			$dir = $this->hashDir();
		}

		$this->setDir($dir);
	}

	public function setallowType($type){
		$this->allowType = $type;
	}
	/**
	 * @brief 获取图片散列目录
	 * @return string
	 */
	public  function hashDir()
	{
		$dir = $this->dir.'/'.date('Y/m/d');
		return $dir;
	}

	/**
	 * @brief 设置上传的目录
	 * @param string $dir
	 */
	public function setDir($dir)
	{
		$this->dir = $dir;
	}




	/**
	* 图片上传
	* @param boolean $isForge 是否伪造数据提交
	*/
	public function upload($isForge = false){
		//图片上传
		$filesize =\Library\tool::getConfig(array('application','uploadsize'));
		if(!$filesize)
			$filesize = 2048;
		$upObj = new Upload($filesize,$this->allowType);

		$upObj->isForge = $isForge;
		$upObj->setDir($this->dir);
		$upState = $upObj->execute();
		//检查上传状态
		foreach($upState as $key => $rs)
		{
			if(count($_FILES[$key]['name']) > 1)
			$isArray = true;
			else
			$isArray = false;
			foreach($rs as $innerKey => $val)
			{
				if($val['flag']==1)
				{
					//上传成功后图片信息
					$fileName = $val['dir'].$val['name'];

					$rs[$innerKey]['src'] = $fileName;

				}
				else{
					$rs[$innerKey]['errInfo'] = upload::errorMessage($val['flag']);
				}

				if($isArray == true)
				{
					$photoArray[$key] = $rs;
				}
				else
				{
					$photoArray[$key] = $rs[0];
				}
			}
		}
		return $photoArray;

	}

}