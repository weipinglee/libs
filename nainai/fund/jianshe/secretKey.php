<?php

/**
 * @brief �������нӿڳ�ʼ����Կ�Ĳ�������ȡ���й�Կ���Գ���Կ�����л�ȡ�̻���Կ
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

    private $privKeyPass = '123456';//˽Կ��ȡ����

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
     * �������ɹ�Կ��˽Կ
     */
    public function createKey(){
        try{
        $errorMsg = '';
        $dn = array(
            "countryName" => 'zh', //���ڹ�������
            "stateOrProvinceName" => 'Shanxi', //����ʡ������
            "localityName" => 'Yangquan', //���ڳ�������
            "organizationName" => 'weiping', //ע��������
            "organizationalUnitName" => 'nainai', //��֯����
            "commonName" => 'bbb', //��������
            "emailAddress" => '123@.qq.com' //����
        );
        $numberofdays = 3650; //��Чʱ��
        $privkeyPath = dirname(__FILE__).$this->privateKeyPath; //��Կ�ļ�·��//����֤��
        $cerpath = dirname(__FILE__).$this->publicKeyPath;

       $privkey = openssl_pkey_new(array('private_key_bits'=>512));

        $csr = openssl_csr_new($dn, $privkey);
        $sscert = openssl_csr_sign($csr, null, $privkey, $numberofdays);
        openssl_x509_export_to_file($sscert, $cerpath); //����֤�鵽�ļ�
        openssl_pkcs12_export_to_file($sscert, $privkeyPath, $privkey, $this->privKeyPass); //������Կ�ļ�

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
     * �������л�ȡ�̻��Ĺ�Կ������des����
     * @return bool|string
     */
    public function getLocalPublicKey(){
        try{
			$type = isset($_POST['type'])?$_POST['type'] : '';
			//if(strtolower($type)!='pub')
			//	throw new \Exception('type ��������');
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
     * http�������У���ȡ���еĹ�Կ�ͶԳ���Կ
     * @param string $type ��Կ���� pub:��Կ��des:�Գ���Կ
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