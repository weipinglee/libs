<?php
namespace nainai\fund\jianshe;
class Common{

	protected static $private_key_path = '/key/localPrivateKey.pfx';
	protected static $public_key_path = '';
	protected static $des_key_path = '/key/';

	static $SDK_SIGN_CERT_PWD = '123456';
	/**
	 * 签名
	 *
	 * @param String $params_str
	 */
	static function sign(&$params)
	{
		$params_md5 = md5 ($params );
		// 签名证书路径
		$private_key = self::getPrivateKey ( self::$private_key_path );
		// 签名
		$sign_falg = openssl_sign ( $params_md5, $signature, $private_key, OPENSSL_ALGO_MD5 );
		if ($sign_falg)
		{
			return   $signature;
		}
		else
		{
			return false;
		}
	}

	/**
	 * DES加密
	 * @param $string
	 * @return string
	 */
	static function desEncryp($string){
		$key = self::getDesKey(self::$des_key_path);
		$res = openssl_encrypt($string,'DES-EDE',$key);
		return base64_encode($res);
	}

	/**
	 * DES解密
	 * @param $string
	 * @return string
	 */
	static function desdecryp($string){
		$key = self::getDesKey(self::$des_key_path);
		$res = openssl_decrypt($string,'DES-EDE',$key);
		return base64_decode($res);
	}

	//获取des key
	static function getDesKey($key_path){
		//TODO:
		return '';
	}


	/**
	 * 验签
	 *
	 * @param String $params_str        	
	 * @param String $signature_str        	
	 */
	static function verify($string,$sign)
	{
		$public_key = self::getPublicKey ( self::$public_key_path );
		$params_md5 = md5 ( $string );
		
		$isSuccess = openssl_verify ( $params_md5, $sign,$public_key, OPENSSL_ALGO_MD5 );
		return $isSuccess;
	}

	
	/**
	 * 取证书公钥 -验签
	 *
	 * @return string
	 */
	static function getPublicKey($cert_path)
	{
		return file_get_contents ($cert_path );
	}
	/**
	 * 返回(签名)证书私钥 -
	 *
	 * @return unknown
	 */
	static function getPrivateKey($cert_path)
	{
		$pkcs12 = file_get_contents ( dirname(__FILE__).'/'.$cert_path );
		openssl_pkcs12_read ( $pkcs12, $certs, self::$SDK_SIGN_CERT_PWD );
		return $certs ['pkey'];
	}

	static function getResponseInfo(&$xml,&$sign,$response){
		//TODO:
	}
	

}
?>