<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26 0026
 * Time: 下午 3:48
 */

namespace auto\html;


class adminHtml extends htmlBase
{

    public function getOperTags(){
        $methods = get_class_methods($this);
        $tags = array();
        foreach($methods as $key=>$item){
            if(strpos($item,'operation_')===0){
                $tags[] = $item;
            }
        }

        $tagsDetail = array();
        foreach($tags as $key=>$item){
            $tagsDetail[$item]['str'] = call_user_func(array($this,$item));
            $temp = preg_match_all('/\$\d+/',$tagsDetail[$item]['str'],$match);
            if($temp){
                $tagsDetail[$item]['arg'] =  implode(',',array_unique($match[0]));
            }

        }
        return $tagsDetail;
    }

    public function getListTags(){
        $methods = get_class_methods($this);
        $tags = array();
        foreach($methods as $key=>$item){
            if(strpos($item,'list_')===0){
                $tags[] = $item;
            }
        }
        $tagsDetail = array();
        foreach($tags as $key=>$item){
            $tagsDetail[$item]['str'] = call_user_func(array($this,$item));
            $temp = preg_match_all('/\$\d+/',$tagsDetail[$item]['str'],$match);
            if($temp){
                $tagsDetail[$item]['arg'] = implode(',', array_unique($match[0]));
            }

        }
        return $tagsDetail;
    }


    public function operation_edit(){
        return  <<< OEF
            <a title="编辑" href="{url:$1}" class="ml-5" style="text-decoration:none"><i class="icon-edit fa-edit"></i></a>
OEF;
    }

    public function operation_del(){
        return <<< OEF
            <a title="删除" href="javascript:;"  ajax_status=-1 ajax_url="{url:$1}" class="ml-5" style="text-decoration:none"><i class="icon-trash fa-trash"></i></a>
OEF;
    }

    public function operation_status(){
        return <<< OEF
            {if:$1 == 1}
                <a style="text-decoration:none" href="javascript:;" title="停用" ajax_status=0 ajax_url="{url:$2}"><i class="icon-pause fa-pause"></i></a>
            {elseif:$1 == 0}
                <a style="text-decoration:none" href="javascript:;" title="启用" ajax_status=1 ajax_url="{url:$$2}"><i class="icon-play fa-play"></i></a>
            {/if}
OEF;
    }

    public function list_common()
    {
        return '<td>$1</td>';
    }
    public function list_img(){
        return '<td><img width="180" height="180" src="{echo:\Library\thumb::get($1,180,180)}"/></td>';
    }

    public function list_areatext(){
        return '<td>{areatext:data=$1 id=$2}</td>';
    }

    public function list_status(){
        return  <<< OEF
                        <td class="td-status">{if:$1 == 1}
                        <span class="label label-success radius">已启用</span>
					    {else:}
						<span class="label label-error radius">停用</span>
					    {/if}</td>
OEF;
    }




    public function listPage(){

    }

    //详情
    public function detailPage(){

    }

    //表单
    public function formPage(){

    }
}

