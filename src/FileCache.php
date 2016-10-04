<?php

namespace Greg\Cache;

use Greg\Support\Accessor\AccessorTrait;

class FileCache implements CacheInterface
{
    use AccessorTrait, CacheTrait;

    private $path = null;

    private $schemaName = 'schema';

    private $storage = null;

    public function __construct($path = null, $schemaName = null)
    {
        if ($path !== null) {
            $this->setPath($path);
        }

        if ($schemaName !== null) {
            $this->setSchemaName($schemaName);
        }

        return $this;
    }

    protected function storageIsLoaded()
    {
        return $this->storage === null;
    }

    protected function loadStorage()
    {
        if (!$this->storageIsLoaded()) {
            $this->setAccessor($this->read($this->getSchemaName()));
        }

        return $this;
    }

    protected function fetchFileName($id)
    {
        return md5($id);
    }

    protected function getFilePath($name)
    {
        return $this->getPath() . DIRECTORY_SEPARATOR . $name;
    }

    protected function read($name)
    {
        $file = $this->getFilePath($name);

        if (!file_exists($file)) {
            return null;
        }

        if (!is_readable($file)) {
            throw new \Exception('Cache file `' . $name . '` from `' . $this->getSchemaName() . '` is not readable.');
        }

        return $this->unserialize(file_get_contents($file));
    }

    protected function write($name, $data)
    {
        $path = $this->getPath();

        if (!is_readable($path)) {
            throw new \Exception('Cache path for `' . $this->getSchemaName() . '` is not readable.');
        }

        if (!is_writable($path)) {
            throw new \Exception('Cache path for `' . $this->getSchemaName() . '` is not writable.');
        }

        $file = $this->getFilePath($name);

        if (file_exists($file) and !is_writable($file)) {
            throw new \Exception('Cache file `' . $file . '` from `' . $this->getSchemaName() . '` is not writable.');
        }

        file_put_contents($file, $this->serialize($data));

        return $this;
    }

    protected function remove($name)
    {
        $file = $this->getFilePath($name);

        if (file_exists($file)) {
            unlink($file);
        }

        return $this;
    }

    protected function add($id)
    {
        $this->loadStorage();

        $this->setToAccessor($id, time());

        $this->update();

        return $this;
    }

    protected function update()
    {
        if ($this->storageIsLoaded()) {
            $this->write($this->getSchemaName(), $this->getAccessor());
        }

        return $this;
    }

    public function save($id, $data = null)
    {
        $this->write($this->fetchFileName($id), $data);

        $this->add($id);

        return $this;
    }

    public function has($id)
    {
        $this->loadStorage();

        return array_key_exists($id, $this->getAccessor());
    }

    public function load($id)
    {
        return $this->read($this->fetchFileName($id));
    }

    public function getLastModified($id)
    {
        $this->loadStorage();

        return $this->getFromAccessor($id);
    }

    public function delete($ids = [])
    {
        $this->loadStorage();

        $ids = func_num_args() ? (array)$ids : array_keys($this->getAccessor());

        foreach($ids as $id) {
            $this->remove($this->fetchFileName($id));
        }

        $this->setAccessor(array_diff($this->getAccessor(), $ids));

        $this->update();

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($name)
    {
        $this->path = (string)$name;

        return $this;
    }

    public function getSchemaName()
    {
        return $this->schemaName;
    }

    public function setSchemaName($name)
    {
        $this->schemaName = (string)$name;

        return $this;
    }
}