<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace tests\src;

use \Library\M;
use \Library\Session;

trait ApplicationTrait
{
    public function withSession(array $data)
    {
        foreach ($data as $key => $value) {
            Session::set($key, $value);
        }
        return $this;
    }

    public function clearSession()
    {
        Session::clearAll();
    }


    protected function seeInDatabase($table, array $data)
    {
        $dbObj = new M($table);
        $res = $dbObj->where($data)->getObj();
        $count = empty($res) ? 0 : 1;
        $this->assertGreaterThan(0,$count, sprintf(
            'Unable to find row in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $res;
    }

    protected function notSeeInDatabase($table, array $data)
    {
        $dbObj = new M($table);
        $data = $dbObj->where($data)->getObj();
        $this->assertEmpty($data, sprintf(
            'Found unexpected records in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }

    protected static function clearTable($tableName){
        $dbObj = new M($tableName);
        $sql = 'truncate table  '.$tableName;
        return $dbObj->query($sql);
    }
}