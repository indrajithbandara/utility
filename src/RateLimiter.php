<?php namespace Common\Utility;

/**
 * Class RateLimiter
 * 频率限制
 *
 * @package Chi\Utility
 */
class RateLimiter
{
    /**
     * 前缀（动作类型）
     */
    const CFG_PREFIX = 'redis-prefix';
    /**
     * 最多尝试次数
     */
    const CFG_MAXTRY = 'max-try';
    /**
     * 提醒次数
     */
    const CFG_REMIND = 'remind-try';
    /**
     * 过期时间（毫秒）
     */
    const CFG_EXPIRE_MSEC = 'expire';

    /**
     * 本次操作和下次都不被限制
     */
    const STATUS_GOOD = 0;
    /**
     * 本次操作为限制允许范围最后一次
     */
    const STATUS_DANGER = 1;
    /**
     * 已经被限制
     */
    const STATUS_BAD = 2;


    const PREFIX = 'rate-limit';

    protected $client;
    protected $config = [
        self::CFG_EXPIRE_MSEC => 3600000,
        self::CFG_MAXTRY => 3,
        self::CFG_REMIND => 10,//10次之后提醒
        self::CFG_PREFIX => 'default',
    ];

    public function __construct(RedisClient $client, array $config = [])
    {
        $this->config = $config + $this->config;
        $this->client = $client;
    }

    /**
     * @param string $target 限制对象string(IP／UID等)
     * @return bool
     */
    public function isLimited($target)
    {
        $key = $this->key($target);

        $limit = intval($this->client->get($key));
        if ($limit >= $this->config[self::CFG_MAXTRY]) {
            return true;
        }

        return false;
    }


    /**
     * @param $target
     * @return bool
     * 功能：是否需要提醒
     */
    public function isReminded($target)
    {
        $key = $this->key($target);

        $limit = intval($this->client->get($key));
        if (!isset($this->config[self::CFG_REMIND])) {
            return false;
        }
        if ($limit >= $this->config[self::CFG_REMIND]) {
            return true;
        }

        return false;
    }

    /**
     * @param $target
     *
     * 慎用！ 手动移除限制
     */
    public function clear($target)
    {
        $key = $this->key($target);

        $this->client->del($key);
    }

    public function ttl($target){
        $key = $this->key($target);
        return $this->client->ttl($key);
    }

    public function increase($target)
    {
        $key = $this->key($target);

        $this->client->multi();
        $this->client->incr($key);
        $this->client->pexpire($key, $this->config[self::CFG_EXPIRE_MSEC]);
        $res = $this->client->exec();
        $limit = $res[0];

        if ($limit > $this->config[self::CFG_MAXTRY]) {
            return self::STATUS_BAD;
        } elseif ($limit === $this->config[self::CFG_MAXTRY]) {
            return self::STATUS_DANGER;
        } else {
            return self::STATUS_GOOD;
        }
    }


    public function setValue($target,$times)
    {
        $key = $this->key($target);

        $this->client->multi();
        $this->client->set($key,$times);
        $this->client->pexpire($key, $this->config[self::CFG_EXPIRE_MSEC]);
        $res = $this->client->exec();

        return self::STATUS_GOOD;

    }

    public function decrease($target)
    {
        $key = $this->key($target);

        $this->client->multi();
        $this->client->decr($key);
        $this->client->pexpire($key, $this->config[self::CFG_EXPIRE_MSEC]);
        $res = $this->client->exec();
        $limit = $res[0];

        if ($limit > $this->config[self::CFG_MAXTRY]) {
            return self::STATUS_BAD;
        } elseif ($limit === $this->config[self::CFG_MAXTRY]) {
            return self::STATUS_DANGER;
        } else {
            return self::STATUS_GOOD;
        }

    }


    /**
     * @param $target
     * @return string
     */
    protected function key($target)
    {
        return implode(
            ':',
            [
                self::PREFIX,
                $this->config[self::CFG_PREFIX],
                $target
            ]
        );
    }
}
