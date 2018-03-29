<?php

/**
 * @brief 建行银行接口初始化秘钥的操作，获取银行公钥、对称秘钥，银行获取商户公钥
 * @author weipinglee
 * @date 2018/3/21
 */
namespace nainai\fund\jianshe;
class secretKey{

    protected $requestUrl = 'http://128.192.182.51:7001/merchant/KeyTransfer/jh8888';
	
    //protected $requestUrl = 'http://128.192.189.100:7001/DataSafety/NeyTransfer.do';
    public $desKey = '';

    protected $merchantNo = '4100000109';

    private $privateKeyPath = '/key/localPrivateKey.pfx';

    private $publicKeyPath = '/key/localpublicKey.cer';
    private $desKeyPath = '/key/desKey.cer';
    private $serverPubPath = '/key/serverpublicKey.cer';

    private $privKeyPass = '123456';//私钥提取密码

    public function __construct($url='',$merchantNo='')
    {
        if($url){
            $this->requestUrl = $url;
        }
       if($merchantNo){
           $this->merchantNo = $merchantNo;

       }

       $this->desKey = $this->merchantNo.date('ymd');
    }

    /**
     * 本地生成公钥，私钥
     */
    public function createKey(){
        try{
        $errorMsg = '';
        $dn = array(
            "countryName" => 'zh', //所在国家名称
            "stateOrProvinceName" => 'Shanxi', //所在省份名称
            "localityName" => 'Yangquan', //所在城市名称
            "organizationName" => 'weiping', //注册人姓名
            "organizationalUnitName" => 'nainai', //组织名称
            "commonName" => 'bbb', //公共名称
            "emailAddress" => '123@.qq.com' //邮箱
        );
        $numberofdays = 3650; //有效时长
        $privkeyPath = dirname(__FILE__).$this->privateKeyPath; //密钥文件路径//生成证书
        $cerpath = dirname(__FILE__).$this->publicKeyPath;

       $privkey = openssl_pkey_new(array('private_key_bits'=>512));

        $csr = openssl_csr_new($dn, $privkey);
        $sscert = openssl_csr_sign($csr, null, $privkey, $numberofdays);
        openssl_x509_export_to_file($sscert, $cerpath); //导出证书到文件
        openssl_pkcs12_export_to_file($sscert, $privkeyPath, $privkey, $this->privKeyPass); //生成密钥文件

        while ($msg = openssl_error_string())
           $errorMsg .= $msg . "<br />\n";
        if($errorMsg!=''){
            throw new \Exception($errorMsg);
        }

      }catch(\Exception $e){
            echo $e->getMessage();
      }

    }


	
	public function getPrivateKey(){
		$privkeyPath = dirname(__FILE__).$this->privateKeyPath;
		$pkcs12 = file_get_contents ( $privkeyPath );
		openssl_pkcs12_read ( $pkcs12, $certs, $this->privKeyPass );
		return $certs ['pkey'];
	}
    /**
     * 用于银行获取商户的公钥，经过des加密
     * @return bool|string
     */
    public function getLocalPublicKey(){
        try{
			$type = isset($_POST['type'])?$_POST['type'] : '';
			//if(strtolower($type)!='pub')
			//	throw new \Exception('type 参数错误');
            $cert_path = dirname(__FILE__).$this->publicKeyPath;
			$publicRes = openssl_pkey_get_public(file_get_contents ($cert_path ));
			$pubKey = openssl_pkey_get_details($publicRes);
			print_r($pubKey);
			echo $pubKey['key'];exit;
            if(!isset($pubKey['key'])){
				throw new \Exception('get failed');
			}
            $data = '000000'.base64_decode(openssl_encrypt($pubKey['key'],'des-ede',$this->desKey,0));
            $errorMsg = '';
            while ($msg = openssl_error_string())
                $errorMsg .= $msg . "<br />\n";
            if($errorMsg!=''){
              //  throw new \Exception($errorMsg);
            }
           return $data;
        }catch(\Exception $e){
            return '100001'.$e->getMessage();
        }

    }

    /**
     * http请求银行，获取银行的公钥和对称秘钥
     * @param string $type 秘钥类型 pub:公钥，des:对称秘钥
     */
    public function getBankSecretKey($type='pub'){
        try{
        $url = $this->requestUrl;
        $ch = curl_init($url);

        $type = $type=='des' ? 'des' : 'pub';
		$param = 'type='.$type;
        //  $header []= "Content-type:text/xml;charset=gbk";
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        //  curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$param);
        $output = curl_exec($ch);
        if(curl_errno($ch)){
            throw new \Exception(curl_errno($ch).'_'.curl_error($ch));
        }
//echo $output;exit;
        $resKey = '';
        $resCode = substr($output,0,6);
        $resMsg = substr($output,6);
        if($resCode=='000000'){
            $resKey = openssl_decrypt($resMsg,'DES-EDE',$this->desKey);
            if($type=='des'){
                $file = dirname(__FILE__).$this->desKeyPath;
            }else{
                $file = dirname(__FILE__).$this->serverPubPath;
            }
            $resource = fopen($file,'w');
            fwrite($resource,$resKey);
            fclose($resource);
        }
        else{
            throw new \Exception($resCode.$resMsg);
        }

        curl_close($ch);

        }catch(\Exception $e){
            echo $e->getMessage();
        }

    }



}
?>