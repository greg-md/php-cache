<?php

namespace Greg\Cache;

trait CacheTrait
{
    public function fetch($id, callable $callable, $expire = 0)
    {
        if ($this->isExpired($id, $expire)) {
            $this->save($id, $result = call_user_func_array($callable, []));
        } else {
            $result = $this->load($id);
        }

        return $result;
    }

    public function isExpired($id, $expire = 0)
    {
        $modified = $this->getLastModified($id);

        if ($modified === null or $modified === false) {
            return true;
        }

        if (!ctype_digit((string)$expire)) {
            $expire = strtotime($expire, $modified) - $modified;
        }

        if (($expire > 0 and ($modified + $expire) <= time()) or $expire < 0) {
            $this->delete($id);

            return true;
        }

        return false;
    }

    protected function serialize($data)
    {
        return serialize($data);
    }

    protected function unserialize($data)
    {
        return unserialize($data);
    }
}