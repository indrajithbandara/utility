<?php namespace Common\Utility;

use Common\Dependency\Exceptions\KnownException;
use Common\Dependency\Interfaces\ISingleValue;
use Rhumsaa\Uuid\Uuid;

class RandomCode
{
    const CFG_LENGTH = 'len';
    const CFG_FORMAT = 'format';
    const CFG_FORMAT_HEX = 'hex';
    const CFG_FORMAT_NUM = 'num';
    const CFG_FORMAT_ALPHANUM = 'alphanum';
    const CFG_FORMAT_CHAR = 'char';
    const CFG_CHAR_TABLE = 'char_table';

    /**
     * @var ISingleValue
     */
    private $storage;
    /**
     * @var array
     */
    private $config = [
        self::CFG_LENGTH => 32,
        self::CFG_FORMAT => self::CFG_FORMAT_HEX,
    ];

    public function __construct(ISingleValue $storage, array $config = [])
    {
        $this->storage = $storage;
        $this->config = $config + $this->config;
    }

    public function is($value)
    {
        return $this->exists() && ($value === $this->get());
    }

    public function assert($value, $msg = '处理失败，请稍后重试')
    {
        if (!$this->is($value)) {
            throw new KnownException($msg);
        }
    }

    public function exists()
    {
        return $this->storage->exists();
    }

    public function get()
    {
        return $this->storage->get();
    }

    public function delete()
    {
        $this->storage->delete();
    }

    public function getOrNew()
    {
        if (!$this->exists()) {
            $value = $this->generateAndSave();
        } else {
            $value = $this->get();
        }

        return $value;
    }

    public function generateAndSave()
    {
        $value = $this->generate();
        $this->storage->set($value);

        return $value;
    }

    protected function config($key)
    {
        return $this->config[$key];
    }

    protected function generate()
    {
        $uuid = Uuid::uuid4();
        $len = $this->config(self::CFG_LENGTH);

        bcscale(0);//BigNumber的convert有bug
        switch ($this->config(self::CFG_FORMAT)) {
            case self::CFG_FORMAT_HEX:
                $string = $uuid->getHex();
                break;
            case self::CFG_FORMAT_NUM:
                $string = $uuid->getInteger()->round()->getValue();
                break;
            case self::CFG_FORMAT_ALPHANUM:
                $string = $uuid->getInteger()->round()->convertToBase(36);
                break;
            case self::CFG_FORMAT_CHAR:
                $table = $this->config(self::CFG_CHAR_TABLE);
                $base = mb_strlen($table);
                $num = $uuid->getInteger();
                $string = '';
                while($num->isGreaterThan(0)) {
                    $cur = (int)Utility::bigNumber($num)->mod($base)->getValue();
                    $string .= mb_substr($table, $cur, 1);
                    $num->divide($base)->floor();
                }

                break;
            default:
                throw new \Exception('unimplemented');
        }

        //从后取才可以取到首位0的情况
        return mb_substr($string, -$len);
    }
}
