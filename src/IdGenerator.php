<?php namespace Common\Utility;

use Common\Logger\Logger;
use Common\Logger\LoggerFactory;


class IdGenerator
{
    const CFG_PREFIX = 'prefix';
    /**
     * 自增最大值，超过时抛错
     */
    const CFG_MAX = 'max';
    /**
     * 过期时间
     */
    const CFG_EXPIRE = 'expire';
    protected $redis;
    protected $config = [
        self::CFG_EXPIRE => 0,
        self::CFG_MAX => 0xFFFFFFFF,
        self::CFG_PREFIX => 'unknown'
    ];

    public function __construct(RedisClient $redis, $config = [])
    {
        $this->redis = $redis;
        $this->config = $config + $this->config;
    }

    public function generate()
    {
        $key = $this->makeKey();

        $value = $this->redis->incr($key);
        if ($value > $this->config[self::CFG_MAX]) {
            throw new \Exception('id overflow');
        }

        if ($value > 0.7 * $this->config[self::CFG_MAX]) {
            Logger::instance()->warning(__METHOD__, [
                'value' => $value,
            ]);
        }

        $sec = $this->config[self::CFG_EXPIRE];
        if ($sec > 0 && $value < 3) {
            $this->redis->expire($key, $sec + 86400); //多存一天
        }

        return $value;
    }

    /**
     * @return string
     */
    protected function makeKey()
    {
        $key = sprintf('idgen:%s', $this->config[self::CFG_PREFIX]);

        return $key;
    }
} 
