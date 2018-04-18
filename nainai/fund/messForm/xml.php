<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16 0016
 * Time: 上午 10:12
 */

namespace nainai\fund\messForm;


class xml extends message
{
    private $head = '';

    /**
     * xml constructor.
     * @param string $encoding
     * @param int $depth xml的深度
     * 设置深度为xml的实际深度，最深层的xml使用field格式<field name="字段名" attr="">值</field>，该值设置为0则所有xml字段都是普通格式
     */
    public function __construct($encoding='gbk',$depth=3)
    {
        parent::__construct($encoding,$depth);
        $this->head = '<?xml version="1.0" encoding="'.$this->encoding.'"?>';
    }

    /**
     * 将数组转换成xml
     * @param array $message 要转换的数组
     * @return mixed
     */
    public function create($message)
    {
        $res = $this->head.$this->dataToXml($message,1);
		return $res;
        //return iconv('UTF-8','GBK',$res);

    }

    /**
     * 将字符串xml按照层级解析成数组
     * @param $message
     * @return array
     */
    public function parse($message)
    {
       // $tags = '';
        $message = preg_replace('/\s/','',$message);
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $message, $tags);
        xml_parser_free($parser);

        $res = $this->xmlDataTrans($tags);
         return $res;
    }

    /**
     * 将数组转成xml字符串
     * @param array $xmlArr 原始数组
     * @param int $currDepth 当前深度
     * @return string
     */
    private function dataToXml($xmlArr,$currDepth=1){
        $tempXml = '';
        $nextDepth = $currDepth + 1;
        if(!is_array($xmlArr)){
            return $xmlArr;
        }
        else{
            foreach($xmlArr as $key=>$val){
                if( $this->depth==$currDepth){//建行的特殊处理，最底层的xml标签使用field格式

                    if(is_array($val)){
                        $tempXml .= '<field name="'.$key.'" ';
                        $value = isset($val['value']) ? $val['value'] : '';
                        unset($val['value']);
                        foreach($val as $attr=>$v){
                            $tempXml .= $attr.'="'.$v.'" ';
                        }
                        $tempXml.= '>'.$value.'</field>';
                        echo $tempXml.'</br>';
                    }
                    else{
                         $tempXml .= '<field name="'.$key.'">'.$val.'</field>';
                    }

                }
                else{
                    $tempXml .= '<'.$key.'>';
                    $tempXml .= $this->dataToXml($val,$nextDepth);
                    $tempXml .= '</'.$key.'>';
                }
            }
        }

        return $tempXml;
    }


    /**
     * 将函数xml_parse_into_struct转换的xml数组转换格式
     * @param array $tags 待转换的数组
     * @return array
     */
    private function xmlDataTrans($tags)
    {
        $res = array();
        $level = isset($tags[0]['level']) ? $tags[0]['level'] : 0;
        if(!$level)
            return array();
        $tempArr = array();
        $name = '';
        foreach($tags as $tag){
            if($tag['level']==$level){
                //完成类型的元素，直接获取值
                if($tag['type']=='complete'){
                    $name = $tag['tag'];
                    if(!empty($tag['attributes'])){
                        if(isset($tag['attributes']['name'])){
                            $name = $tag['attributes']['name'];
                            unset($tag['attributes']['name']);
                        }
                    }
                    if(!empty($tag['attributes'])){
                        $res[$name] = $tag['attributes'];
                        $res[$name]['value'] = $tag['value'];
                    }
                    else{
                        $res[$name] = $tag['value'];
                    }
                }
                //open类型的元素，获取键名
                if($tag['type']=='open' ){
                    $name = $tag['tag'];
                    if(!empty($tag['attributes'])){
                        if(isset($tag['attributes']['name'])){
                            $name = $tag['attributes']['name'];
                            unset($tag['attributes']['name']);
                        }

                    }
                    $tempArr = array();
                    if(!empty($tag['attributes'])){
                        $res[$name] = $tag['attributes'];
                        $res[$name]['value'] = '';
                    }
                    else{
                        $res[$name] = '';
                    }
                }
                //close类型的元素，递归获取下级元素
                if($tag['type']=='close'){
                    if(is_array($res[$name])){
                        $res[$name]['value'] = $this->xmlDataTrans($tempArr);
                    }
                    else{
                        $res[$name] = $this->xmlDataTrans($tempArr);
                    }

                    $tempArr = array();
                }
            }

            else{//非最外层的元素加入tempArr数组，待传入递归函数
                $tempArr[] = $tag;
            }

        }

        return $res;

    }

}