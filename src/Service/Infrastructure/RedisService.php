<?php

namespace App\Service\Infrastructure;

use App\Security\Domain\AuthUserInterface;
use App\Service\Exception\RedisQueueNotFoundException;
use App\Service\Infrastructure\Traits\LogTrait;
use App\Service\Utility\MomentHelper;
use App\User\Entity\Interfaces\AppUserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use SymfonyBundles\RedisBundle\Redis\ClientInterface;

class RedisService
{
    use LogTrait;

    private const LOG_PREFIX = 'REDIS_SERVICE. ';

    public function __construct(
        private bool                $redisIsEnable,
        private bool                $redisQueueIsEnable,
        private ClientInterface     $redis,
        private SerializerInterface $serializer,
        private readonly LogService $logger,
        private string              $prefix = RedisKeys::PREFIX_MAIN,
    ) {
    }

    public function isEnable(): bool
    {
        return $this->redisIsEnable;
    }

    public function isQueueEnable(): bool
    {
        return $this->redisQueueIsEnable;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function find(string $key, bool $withPrefix = true): ?string
    {
        if (!$this->isEnable()) {
            return null;
        }

        $redisKey = $withPrefix ? $this->getRedisKey($key) : $key;

        return $this->redis->get($redisKey);
    }

    public function get(string $key, bool $withPrefix = true): ?string
    {
        return $this->find($key, $withPrefix);
    }

    public function findArrayValue(string $key): ?array
    {
        if (!$this->isEnable()) {
            return null;
        }

        return $this->redis->hgetall($this->getRedisKey($key));
    }

    public function set(
        string $key,
        string $value,
        int    $ttl = MomentHelper::SECONDS_MINUTE,
        bool   $withPrefix = true
    ): void {
        if (!$this->isEnable()) {
            return;
        }

        if ($withPrefix) {
            $key = $this->getRedisKey($key);
        }

        $this->redis->set($key, $value);
        $this->redis->expire($key, $ttl);
    }

    public function setArrayObjects(string $key, array $value, int $ttl = MomentHelper::SECONDS_MINUTE): void
    {
        if (!$this->isEnable()) {
            return;
        }

        $serializeValue = json_encode($this->serializer->serialize($value, 'json'));
        $key = $this->getRedisKey($key);

        $this->redis->set($key, $serializeValue);
        $this->redis->expire($key, $ttl);
    }

    public function getArrayObjects(string $key): ?array
    {
        if (!$this->isEnable()) {
            return null;
        }

        $res = $this->redis->get($this->getRedisKey($key));
        if ($res === null) {
            return null;
        }

        $result = json_decode($res, true);

        return $result;
    }

    public function setArray(string $key, array $value, int $ttl = MomentHelper::SECONDS_MINUTE): void
    {
        if (!$this->isEnable()) {
            return;
        }

        $this->logDebug('setArray', ['key' => $key, 'value' => $value]);

        $serializeValue = serialize($value);

        $this->redis->set($key, $serializeValue);
        $this->redis->expire($key, $ttl);
    }

    public function getArray(string $key): ?array
    {
        if (!$this->isEnable()) {
            return null;
        }

        $res = $this->redis->get($key);
        $result = null;
        if ($res != null) {
            $result = unserialize($res);
        }

        $this->logDebug('getArray', ['key' => $key, 'value' => $result]);

        return $result;
    }

    public function pushToStack(string $stackName, mixed $payload): int
    {
        if (!is_array($payload)) {
            $payload = [$payload];
        }

        return $this->redis->rpush($stackName, $payload);
    }

    public function getStack(string $stackName): array
    {
        return $this->redis->lrange($stackName, 0, -1);
    }

    public function setObjects(
        string $key,
        array  $object,
        int    $ttl = MomentHelper::SECONDS_MINUTE,
        bool   $withPrefix = true
    ): void {
        if (!$this->isEnable()) {
            return;
        }

        if ($withPrefix) {
            $key = $this->getRedisKey($key);
        }

        $json = json_encode($object);
        $value = igbinary_serialize($json);

        $this->redis->set($key, $value);
        $this->redis->expire($key, $ttl);
    }

    public function getObjects(string $key, bool $withPrefix = true): ?array
    {
        $findResult = $this->find($key, $withPrefix);
        if ($findResult === null) {
            return null;
        }

        $json = igbinary_unserialize($findResult);

        $arrList = json_decode($json);


        return $arrList;
    }

    public function setObject(
        string $key,
        object $object,
        int    $ttl = MomentHelper::SECONDS_MINUTE,
        bool   $withPrefix = true
    ): void {
        if (!$this->isEnable()) {
            return;
        }

        $redisKey = $withPrefix ? $this->getRedisKey($key) : $key;

        $json = json_encode($object);
        $value = igbinary_serialize($json);

        $this->redis->set($redisKey, $value);
        $this->redis->expire($redisKey, $ttl);
    }

    public function getObject(string $key, bool $withPrefix = true): ?object
    {
        $findResult = $this->find($key, $withPrefix);
        if ($findResult === null) {
            return null;
        }

        $json = igbinary_unserialize($findResult);

        $result = json_decode($json);

        return $result;
    }

    public function remove(string $key, bool $withPrefix = true): void
    {
        if (!$this->isEnable()) {
            return;
        }

        $key = $withPrefix ? $this->getRedisKey($key) : $key;
        $this->redis->remove($key);
    }

    public function has(string $key): ?bool
    {
        if (!$this->isEnable()) {
            return null;
        }

        return (bool)$this->find($this->getRedisKey($key));
    }

    private function getRedisKey(string $key, ?string $prefix = null): string
    {
        if (!$prefix) {
            $prefix = $this->prefix;
        }

        return sprintf('%s:%s', $prefix, $key);
    }

    /**
     * @throws RedisQueueNotFoundException
     */
    private function checkQueue(string $queueName): void
    {
        if (!in_array($queueName, RedisKeys::AVAILABLE_QUEUES)) {
            throw new RedisQueueNotFoundException($queueName);
        }
    }

    /**
     * @throws RedisQueueNotFoundException
     */
    public function pushToQueue(string $queueName, mixed $payload): int
    {
        $this->checkQueue($queueName);

        if (!is_array($payload)) {
            $payload = [$payload];
        }

        return $this->redis->rpush($queueName, $payload);
    }

    /**
     * @throws RedisQueueNotFoundException
     */
    public function popFromQueue(string $queueName): mixed
    {
        $this->checkQueue($queueName);

        return $this->redis->lpop($queueName);
    }

    public function getKeys(string $pattern = '*'): ?array
    {
        if (!$this->isEnable()) {
            return null;
        }

        return $this->redis->keys($pattern);
    }

    public function removeByPattern(string $pattern, bool $withPrefix = true): void
    {
        if (!$this->isEnable()) {
            return;
        }

        $redisPattern = $withPrefix ? $this->getRedisKey($pattern) : $pattern;
        $keys = $this->getKeys($redisPattern);
        foreach ($keys as $key) {
            $this->remove($key, false);
        }
    }

    public function invalidateCacheByUser(AppUserInterface|AuthUserInterface $user): void
    {
        $redisKeys = [];

        $prefix = sprintf('%s:%s', RedisKeys::PREFIX_USER, RedisKeys::KEY_USER_INFO);
        $redisKeys[] = sprintf($prefix, $user->getUid());

        $prefix = sprintf('%s:%s', RedisKeys::PREFIX_USER, RedisKeys::KEY_USER_ITEM);
        $redisKeys[] = sprintf($prefix, $user->getUid());
        $redisKeys[] = sprintf($prefix, $user->getUsername());
        $redisKeys[] = sprintf($prefix, $user->getId());

        $redisKeys[] = sprintf('%s:%s', RedisKeys::PREFIX_JWT, $user->getEmail());
        foreach ($redisKeys as $redisKey) {
            $this->removeByPattern(
                pattern: $redisKey,
                withPrefix: false,
            );
        }
    }
}