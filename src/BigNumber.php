<?php namespace Common\Utility;

/**
 *
 * @method $this add($number)
 * @method $this subtract($number)
 * @method $this multiply($number)
 * @method $this divide($number)
 */
class BigNumber extends \Moontoast\Math\BigNumber implements \JsonSerializable
{
    const DEFAULT_SCALE = 50;
    /**
     * @param $num
     * @param $comma
     * @param $fraction
     * @return string
     */
    private static function addComma(self $num, $comma, $fraction)
    {
        $f = '';
        if ($fraction > 0) {
            list($i, $f) = explode('.', $num->round46($fraction));
        } else {
            $i = Utility::bigNumber($num, 0)->getValue();
        }

        if ($i[0] === "-") {
            $i =  substr($i, 1, strlen($i));
            $pre = '-';
        }
        if ($comma) {
            $i = strrev(implode(',', str_split(strrev($i), 3)));
        }

        if (isset($pre)) {
            $i = "{$pre}$i";
        }

        if ($fraction > 0) {
            return "$i.$f";
        } else {
            return $i;
        }
    }


    public function toMoneyYuanString($comma = true, $fraction = 2)
    {
        return $this->toMoneyString($comma, $fraction) . ' 元';
    }
    public function toMoneyShareString($comma = true, $fraction = 2)
    {
        return $this->toShareString($comma, $fraction) . ' 份';
    }

    public function toMoneyYuanSymbolString($comma = true, $fraction = 2)
    {
        return '￥' . $this->toMoneyString($comma, $fraction);
    }

    /**
     *
     * 计算净值 格式化
     *
     * @param bool $comma
     * @param int $fraction
     * @return string
     */
    public function toMoneyYuanStringForNetValue($comma = true, $fraction = 4)
    {
        return self::addComma($this, $comma, $fraction) . ' 元';
    }

    /**
     *
     * 计算份额 格式化 就不要 divide 100
     *
     * @param bool $comma
     * @param int $fraction
     * @return string
     */
    public function toShareString($comma = true, $fraction = 2)
    {
        return self::addComma($this, $comma, $fraction);
    }


    public function toMoneyString($comma = true, $fraction = 2)
    {
        $num = new static($this, self::DEFAULT_SCALE);
        $num->divide(100);

        return self::addComma($num, $comma, $fraction);
    }

    public function toMoneyCommaString($comma = true, $fraction = 2, $signed = false)
    {
        $ret = self::addComma($this, $comma, $fraction);
        if ($signed && $this->isGreaterThan(0)) {
            $ret = '+' . $ret;
        }
        return $ret;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return preg_replace('/\.0*$/', '', $this->getValue());
    }

    /**
     * Rounds the current number to the nearest integer
     *
     * @return BigNumber for fluent interface
     * @todo Implement precision digits
     */
    public function round46($fraction = 2)
    {
        $exp = pow(10, $fraction - 1);

        $num = Utility::bigNumberMultiply($this, $exp);

        $original = $num->getValue();
        $floored = $num->floor()->getValue();
        $diff = bcsub($original, $floored, 20);
        $roundedDiff = round($diff, 1, PHP_ROUND_HALF_EVEN);
        $num->numberValue = bcadd(
            $floored,
            $roundedDiff,
            1
        );

        $num->setScale($fraction)->divide($exp);

        return $num;
    }

}
