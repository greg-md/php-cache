<?php

namespace Greg\Cache;

use Greg\Support\Accessor\AccessorTrait;
use Greg\Support\Obj;

class CacheManager implements CacheInterface
{
    use AccessorTrait;

    protected $defaultContainerName = null;

    public function set($name, CacheInterface $driver)
    {
        $this->setToAccessor($name, $driver);

        return $this;
    }

    /**
     * @param $name
     *
     * @throws \Exception
     *
     * @return CacheInterface
     */
    public function get($name)
    {
        if (!$cache = $this->getFromAccessor($name)) {
            throw new \Exception('Cache container `' . $name . '` was not defined.');
        }

        if (is_callable($cache)) {
            $cache = Obj::callCallable($cache);

            if (!($cache instanceof CacheInterface)) {
                throw new \Exception('Cache container `' . $name . '` must be an instance of `' . CacheInterface::class . '`');
            }

            $this->setToAccessor($name, $cache);
        }

        return $cache;
    }

    public function register($name, callable $callable)
    {
        $this->setToAccessor($name, $callable);

        return $this;
    }

    public function setDefaultContainerName($name)
    {
        $this->defaultContainerName = (string) $name;

        return $this;
    }

    public function getDefaultContainerName()
    {
        return $this->defaultContainerName;
    }

    public function defaultContainer()
    {
        if (!$name = $this->getDefaultContainerName()) {
            throw new \Exception('Default cache container was not defined.');
        }

        return $this->get($name);
    }

    public function fetch($id, callable $callable, $expire = 0)
    {
        return $this->defaultContainer()->{__FUNCTION__}(...func_get_args());
    }

    public function save($id, $data = null)
    {
        $this->defaultContainer()->{__FUNCTION__}(...func_get_args());

        return $this;
    }

    public function has($id)
    {
        return $this->defaultContainer()->{__FUNCTION__}(...func_get_args());
    }

    public function load($id)
    {
        return $this->defaultContainer()->{__FUNCTION__}(...func_get_args());
    }

    public function getLastModified($id)
    {
        return $this->defaultContainer()->{__FUNCTION__}(...func_get_args());
    }

    public function isExpired($id, $expire = 0)
    {
        return $this->defaultContainer()->{__FUNCTION__}(...func_get_args());
    }

    public function delete($ids = [])
    {
        $this->defaultContainer()->{__FUNCTION__}(...func_get_args());

        return $this;
    }
}
