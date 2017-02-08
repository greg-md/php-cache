<?php

declare(strict_types=1);

namespace Greg\Cache;

class SqliteCache extends CacheAbstract
{
    private $adapter;

    public function __construct(\PDO $adapter, int $ttl = 300)
    {
        $this->adapter = $adapter;

        $this->ttl = $this->validateTTL($ttl);

        $this->checkAndBuildStructure();

        return $this;
    }

    public function has(string $key): bool
    {
        $stmt = $this->adapter->prepare('SELECT ExpiresAt FROM Cache WHERE Key = :Key');

        $stmt->bindValue(':Key', $key);

        $stmt->execute();

        $expiresAt = $stmt->fetchColumn();

        if ($expiresAt === false) {
            return false;
        }

        if ($this->isExpired((int) $expiresAt)) {
            $this->delete($key);

            return false;
        }

        return true;
    }

    public function hasMultiple(array $keys): bool
    {
        $stmt = $this->adapter->prepare('SELECT Key, ExpiresAt FROM Cache WHERE Key IN (' . $this->stmtKeys($keys) . ')');

        $this->stmtBindKeys($stmt, $keys);

        $stmt->execute();

        $expiredKeys = [];

        $count = 0;

        while ($row = $stmt->fetch()) {
            if ($this->isExpired((int) $row['ExpiresAt'])) {
                $expiredKeys[] = $row['Key'];
            }

            ++$count;
        }

        if ($expiredKeys) {
            $this->deleteMultiple($expiredKeys);

            return false;
        }

        return $count == count($keys);
    }

    public function get(string $key, $default = null)
    {
        $stmt = $this->adapter->prepare('SELECT Value, ExpiresAt FROM Cache WHERE Key = :Key');

        $stmt->bindValue(':Key', $key);

        $stmt->execute();

        if (!$row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $default;
        }

        if ($this->isExpired((int) $row['ExpiresAt'])) {
            $this->delete($key);

            return $default;
        }

        return $row['Value'] ? unserialize($row['Value']) : $default;
    }

    public function getMultiple(array $keys, $default = null)
    {
        $stmt = $this->adapter->prepare('SELECT Key, Value, ExpiresAt FROM Cache WHERE Key IN (' . $this->stmtKeys($keys) . ')');

        $this->stmtBindKeys($stmt, $keys);

        $stmt->execute();

        $expiredKeys = [];

        $rows = [];

        while ($row = $stmt->fetch()) {
            if ($this->isExpired((int) $row['ExpiresAt'])) {
                $expiredKeys[] = $row['Key'];

                continue;
            }

            if ($row['Value']) {
                $rows[$row['Key']] = unserialize($row['Value']);
            }
        }

        if ($expiredKeys) {
            $this->deleteMultiple($expiredKeys);
        }

        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $rows[$key] ?? $default;
        }

        return $values;
    }

    public function set(string $key, $value, ?int $ttl = null)
    {
        if ($this->has($key)) {
            $stmt = $this->adapter->prepare('UPDATE Cache SET Value = :Value, ExpiresAt = :ExpiresAt WHERE Key = :Key');
        } else {
            $stmt = $this->adapter->prepare('INSERT INTO Cache(Key, Value, ExpiresAt) VALUES (:Key, :Value, :ExpiresAt)');
        }

        $stmt->bindValue(':Key', $key);

        $stmt->bindValue(':Value', serialize($value), \PDO::PARAM_LOB);

        $stmt->bindValue(':ExpiresAt', $this->getExpiresAt($ttl));

        $stmt->execute();

        return $this;
    }

    public function delete(string $key)
    {
        $stmt = $this->adapter->prepare('DELETE FROM Cache WHERE Key = :Key');

        $stmt->bindValue(':Key', $key);

        $stmt->execute();

        return $this;
    }

    public function deleteMultiple(array $keys)
    {
        $stmt = $this->adapter->prepare('DELETE FROM Cache WHERE Key IN (' . $this->stmtKeys($keys) . ')');

        $this->stmtBindKeys($stmt, $keys);

        $stmt->execute();

        return $this;
    }

    public function clear()
    {
        $this->adapter->exec('DELETE FROM Cache');

        return $this;
    }

    protected function structureExists(): bool
    {
        $stmt = $this->adapter->query('SELECT 1 FROM sqlite_master WHERE type = "table" and name = "Cache"');

        return $stmt->fetch() ? true : false;
    }

    protected function buildStructure()
    {
        $this->adapter->exec('CREATE TABLE Cache (Key VARCHAR(255) PRIMARY KEY, Value BLOB, ExpiresAt INTEGER)');

        $this->adapter->exec('CREATE INDEX CacheExpiresAt ON Cache(ExpiresAt)');

        return $this;
    }

    protected function checkAndBuildStructure()
    {
        if (!$this->structureExists()) {
            $this->buildStructure();
        }

        return $this;
    }

    protected function stmtKeys(array $keys)
    {
        return implode(', ', array_fill(0, count($keys), '?'));
    }

    protected function stmtBindKeys(\PDOStatement $stmt, array $keys)
    {
        foreach (array_values($keys) as $k => $key) {
            $stmt->bindValue($k + 1, $key);
        }

        return $this;
    }
}
