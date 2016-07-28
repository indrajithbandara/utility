<?php

namespace Common\Utility;

class Utility
{

    const MALE = 1;

    const FEMALE = 0;

    public static $validPhonePrefix = [
        133,
        153,
        180,
        181,
        189,
        177,
        130,
        131,
        132,
        155,
        156,
        185,
        186,
        176,
        134,
        135,
        136,
        137,
        138,
        139,
        150,
        151,
        152,
        158,
        159,
        182,
        183,
        184,
        157,
        187,
        188,
        178,
        170,
        147
    ];

    public static function bigNumber($number, $scale = 50)
    {
        if (strval($number) !== filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) {
            throw new \Exception(__METHOD__ . '/' . __LINE__);
        }
        
        return new BigNumber($number, $scale);
    }

    public static function bigNumberFloor($number)
    {
        return self::bigNumber($number, 0)->getValue();
    }

    /**
     *
     * @param
     *            $left
     * @param
     *            $right
     * @param int $scale            
     * @return BigNumber
     * @throws \Exception
     */
    public static function bigNumberAdd($left, $right, $scale = 50)
    {
        return self::bigNumber($left, $scale)->add($right);
    }

    /**
     *
     * @param
     *            $left
     * @param
     *            $right
     * @param int $scale            
     * @return BigNumber
     * @throws \Exception
     */
    public static function bigNumberSubtract($left, $right, $scale = 50)
    {
        return self::bigNumber($left, $scale)->subtract($right);
    }

    /**
     *
     * @param
     *            $left
     * @param
     *            $right
     * @param int $scale            
     * @return BigNumber
     * @throws \Exception
     */
    public static function bigNumberMultiply($left, $right, $scale = 50)
    {
        return self::bigNumber($left, $scale)->multiply($right);
    }

    public static function bigNumberDivide($left, $right, $scale = 50)
    {
        return self::bigNumber($left, $scale)->divide($right);
    }

    /**
     * 从身份证获取用户性别 0：女 1：男
     * 
     * @param
     *            $identityCardId
     * @return int
     */
    public static function getSexFromIdentityCard($identityCardId)
    {
        $length = strlen($identityCardId);
        
        if (18 == $length) {
            $num = substr($identityCardId, 16,1);
        } else {
            throw new \Exception("身份证号只能18位");
        }
        
        return $num % 2 === 0 ? self::FEMALE : self::MALE;
    }

    /**
     * 根据生日时间戳获取用户几天后生日
     * @param int $birthday 用户生日时间戳
     * @return int 用户几天后生日
     */
    public static function getBirthdayInterval($birthday)
    {
        if ($birthday == '') { //没有生日。。
            return 9999;
        }
        $birthAt = strtotime(date('2000-m-d', $birthday));
        $nowAt = strtotime(date('2000-m-d'));
        if ($birthAt < $nowAt) {
            $birthAt = strtotime(date('2001-m-d', $birthAt));
        }
        return ($birthAt - $nowAt) / 86400;
    }


    /**
     * @param $identityCardId
     * @return bool|int
     * 根据身份证获得年龄（周岁）
     */
    public static function getAgeFromIdentityCard($identityCardId)
    {
        $length = strlen($identityCardId);

        if (18 == $length) {
            $born_date = substr($identityCardId, 6,8);
        } else {
            return false;
        }

        $diff_time = time() - strtotime($born_date) ;

        if ( $diff_time <= 0 ) {
            return 0;
        }

        $born_year = (int) date("Y",strtotime($born_date));//出生年
        $born_month = (int) date("n",strtotime($born_date));//出生月
        $born_day = (int) date("j",strtotime($born_date));//出生日

        $current_year = (int) date("Y",time());
        $current_month = (int) date("n",time());
        $current_day = (int) date("j",time());

        if ( $born_year == $current_year ) {//同一年
            return 0;
        }

        if ($current_month > $born_month ) {
            $age =  $current_year - $born_year;
        } elseif ($born_month = $current_month ) {//同一月，要注意年龄的判断
            if ($current_day > $born_day) {
                $age =  $current_year - $born_year;
            } else {//生日当天也不算周岁
                $age =  $current_year - $born_year - 1;
            }
        } else {
            $age = $current_year - $born_year - 1;
        }

        return $age > 0 ? $age : 0;

    }

    /**
     * 从身份证号获取生日 20150505
     * 
     * @param
     *            $identityCardId
     * @return string
     */
    public static function getBirthFromIdentityCard($identityCardId)
    {
        $length = strlen($identityCardId);
        
        if (15 == $length) {
            $birth = '19' . substr($identityCardId, 6, 6);
        } else {
            $birth = substr($identityCardId, 6, 8);
        }
        
        return $birth;
    }

    /**
     * 移除URL中的host、端口等等
     * 
     * @param
     *            $url
     * @return string
     */
    public static function filterUrl($url)
    {
        $parts = parse_url($url);
        
        $target = '';
        if (isset($parts['path'])) {
            $target .= $parts['path'];
        } else {
            $target .= '/';
        }
        
        if (isset($parts['query'])) {
            $target .= '?' . $parts['query'];
        }
        
        if (isset($parts['fragment'])) {
            $target .= '#' . $parts['fragment'];
        }
        
        return $target;
    }

    /**
     * check email format
     * 
     * @param string $email            
     * @return bool
     */
    public static function validMail($email)
    {
        $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
        return preg_match($pattern, $email) > 0 ? true : false;
    }

    /**
     * check valid phone
     * 
     * @param
     *            $phone
     * @return bool
     */
    public static function validPhone($phone)
    {
        return (ctype_digit($phone) && strlen($phone) == 11 && in_array(substr($phone, 0, 3), self::$validPhonePrefix));
    }

    public static function validateIdNum($idNum)
    {
        if (! preg_match('#[1-9]\d{16}[0-9X]#', $idNum)) {
            return false;
        }
        
        $num = substr($idNum, 0, 17);
        
        return $idNum{17} === self::iso7064_2_11($num);
    }

    /**
     * 转换元到分
     * 
     * @param
     *            $sYuan
     * @return string
     */
    public static function convertYuanToFen($sYuan)
    {
        return self::bigNumber($sYuan)->multiply(100)
            ->round()
            ->getValue();
    }

    /**
     * 转换分到元
     * 
     * @param
     *            $iFen
     * @return float
     */
    public static function convertFenToYuan($iFen, $scale = 2)
    {
        return (float) self::bigNumber($iFen, $scale)->divide(100)->getValue();
    }

    /**
     * 转换分到万元
     * 
     * @param
     *            $iFen
     * @return float
     */
    public static function convertFenToWan($iFen)
    {
        return (float) self::bigNumber($iFen, 2)->divide(1000000)->getValue();
    }

    /**
     * 转换分到亿元或者万元
     * 
     * @param
     *            $iFen
     * @return float
     */
    public static function convertFenToYiOrWan($iFen)
    {
        $iFen = Utility::convertFenToYuan($iFen);
        
        $num = new BigNumber($iFen, 2);
        if ($num->divide(100000000)->isGreaterThanOrEqualTo(1)) {
            return array(
                'num' => strval(floatval($num->getValue())),
                'unit' => '亿'
            );
        } else {
            $num2 = new BigNumber($iFen);
            return array(
                'num' => $num2->divide(10000)->getValue(),
                'unit' => '万'
            );
        }
    }

    public static function cn_truncate($string, $strlen = 20, $etc = '...', $charset = 'utf-8')
    {
        $slen = mb_strlen($string, $charset);
        if ($slen > $strlen) {
            $tstr = mb_substr($string, 0, $strlen, $charset);
            $matches = array();
            $mcount = preg_match_all("/[\x{4e00}-\x{9fa5}]/u", $tstr, $matches);
            unset($matches);
            $offset = ($strlen - $mcount) * 0.35; // 0;//intval((3*mb_strlen($tstr,$charset)-strlen($tstr))*0.35);
            return preg_replace('/\&\w*$/', '', mb_substr($string, 0, $strlen + $offset, $charset)) . $etc;
        } else {
            return $string;
        }
    }

    protected static function iso7064_2_11($numString, $char = '10X98765432')
    {
        $len = strlen($numString);
        
        $sum = 0;
        for ($i = 0; $i < $len; $i ++) {
            $factor = pow(2, $len - $i) % 11;
            $sum += $factor * intval($numString{$i});
        }
        
        return $char{$sum % 11};
    }

    /**
     * 将$arr中的数据复制至$to(可以是数组或对象)
     * 除$to以外参数同PHP原生extract
     * 返回实际成功复制的数据条数
     * 
     * @param Array $arr
     *            原数组
     * @param array|object $to
     *            目的数组/对象
     * @param int $type
     *            参考PHP原生extract函数的type参数
     * @param bool|string $prefix
     *            前缀
     * @return int
     */
    public static function extractTo(Array &$arr, &$to, $type = EXTR_OVERWRITE, $prefix = false)
    {
        if (is_array($to)) {
            $t = 0;
        } else {
            if (is_object($to)) {
                $t = 1;
            } else {
                return trigger_error("extract_to(): Second argument should be an array or object", E_USER_WARNING);
            }
        }
        
        if ($type == EXTR_PREFIX_SAME || $type == EXTR_PREFIX_ALL || $type == EXTR_PREFIX_INVALID || $type == EXTR_PREFIX_IF_EXISTS) {
            if ($prefix === false) {
                return trigger_error("extract_to(): Prefix expected to be specified", E_USER_WARNING);
            } else {
                $prefix .= '_';
            }
        }
        
        $i = 0;
        foreach ($arr as $key => $val) {
            $nkey = $key;
            $isset = $t == 0 ? isset($to[$key]) : isset($to->$key);
            
            if (($type == EXTR_SKIP && $isset) || ($type == EXTR_IF_EXISTS && ! $isset)) {
                continue;
            } else {
                if (($type == EXTR_PREFIX_SAME && $isset) || ($type == EXTR_PREFIX_ALL) || ($type == EXTR_PREFIX_INVALID && ! preg_match('#^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$#', $key))) {
                    $nkey = $prefix . $key;
                } else {
                    if ($type == EXTR_PREFIX_IF_EXISTS) {
                        if ($isset) {
                            $nkey = $prefix . $key;
                        } else {
                            continue;
                        }
                    }
                }
            }
            
            if (! preg_match('#^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$#', $nkey)) {
                continue;
            }
            
            if ($t == 1) {
                if ($type & EXTR_REFS) {
                    $to->$nkey = &$arr[$key];
                } else {
                    $to->$nkey = $val;
                }
            } else {
                if ($type & EXTR_REFS) {
                    $to[$nkey] = &$arr[$key];
                } else {
                    $to[$nkey] = $val;
                }
            }
            $i ++;
        }
        
        return $i;
    }

    /**
     *
     * @param array|\Traversable $models            
     * @param
     *            $fld
     * @return array
     */
    public static function makeArrayBy($models, $fld)
    {
        $result = [];
        foreach ($models as $model) {
            $result[$model->{$fld}] = $model;
        }
        
        return $result;
    }

    /**
     *
     * @param int timestamp
     * @param string
     *
     * @return string
     */
    public static function customizeFormatDate($originalTimestamp, $targetFormat="")
    {
        date_default_timezone_set('Asia/Shanghai');

        $targetDate = "";

        if(empty($targetFormat))
        {
            $targetFormat = "n月j日H点";
        }

        if(isset($originalTimestamp) && !empty($originalTimestamp)) {

            $targetDate = date($targetFormat, $originalTimestamp);

        }

        return $targetDate;
    }

    public static function getNumStyle($num)
    {
        if (!is_numeric($num)) {
            return 'zero';
        }
        if ($num > 0) {
            return 'positive';
        } elseif ($num < 0) {
            return 'negative';
        }
        return 'zero';
    }
}
