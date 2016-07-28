<?php namespace Common\Utility\Derived;

use Common\Dependency\Interfaces\IKeyValue;
use Common\Dependency\Traits\KeyValueTrait;
use Common\Utility\RedisClient;

class RedisKeyValue implements IKeyValue
{
    use KeyValueTrait;
    /**
     * @var RedisClient
     */
    protected $client;
    /**
     * @var int
     */
    protected $expire;
    /**
     * @var string
     */
    protected $prefix;

    protected function __construct(RedisClient $client, $prefix, $expire)
    {
        $this->client = $client;
        $this->expire = $expire;
        $this->prefix = $prefix;
    }

    /**
     * @param RedisClient $client
     * @param string $prefix key前缀，无需结尾冒号
     * @param int $expireMSec 过期（毫秒）
     * @return static
     */
    public static function wrap(RedisClient $client, $prefix = '', $expireMSec = null)
    {
        return new static($client, $prefix, $expireMSec);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        if ($this->expire !== null) {
            $this->client->psetex($this->key($key), $this->expire, $value);
        } else {
            $this->client->set($this->key($key), $value);
        }
    }

    /**
     * @param string $key
     * @return void
     */
    public function delete($key)
    {
        $this->client->del($this->key($key));
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->client->get($this->key($key));
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return $this->client->exists($this->key($key));
    }

    protected function key($key)
    {
        $prefix = empty($this->prefix) ? __CLASS__ : $this->prefix;
        return $prefix . ':' . $key;
    }
}
 
