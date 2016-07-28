<?php namespace Common\Utility;


class StringUtils
{//以后有其他字符串工具也可以加到这里
    /**
     * 过滤用户输入的特殊字符
     * @param $post
     * @return mixed|string
     */
    public static function stringFilter($str) {
        $str = addslashes($str);
        $str = str_replace("_", "\_", $str);
        $str = str_replace("%", "\%", $str);
        $str = nl2br($str);
        $str = htmlspecialchars($str);
        return $str;
    }

    /**
     * pengbin
     * 有group by的不能使用
     * @param $sql
     * @param int $type 1:mysql 2:oracle
     */
    public static function sqlToCount($sql,$type = 1){
        if (strstr($sql, "group by")) {
            if($type == 1){
                //mysql 采用found_rows 取数据行数
                //select found_rows() todo
            }else{
                //oracle 采用子查询count(*) 取数据行数
                $sql = "select count(*) from (".$sql.")";
            }
        } else {
            $sql = preg_replace('/select([\s\S]*?)from/i', 'select count(*) from ', $sql, 1);
            $sql = preg_replace('/limit([\s\S]*?)$/i', ' ', $sql, 1);
        }
        return $sql;
    }

    /**
     * 对象转数组
     * pengbin
     * @param $obj
     * @return array1
     */
    public static function objectToArray($obj) {
        $arr = array();
        if (is_object($obj) || is_array($obj)) {
            foreach ($obj as $key => $val) {
                if (!is_object($val)) {
                    if (is_array($val)) {
                        $arr[$key] = self::objectToArray($val);
                    } else {
                        $arr[$key] = $val;
                    }
                } else {
                    $arr[$key] = self::objectToArray($val);
                }
            }
        }
        return $arr;
    }

    public static function underlineCaseReplace($string){
        return preg_replace_callback(
            "/(_([a-z]))/",
            function($match){
                return strtoupper($match[2]);
            },
            $string
        );
    }
}
