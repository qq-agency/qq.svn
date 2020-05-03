<?php

namespace QQ\Svn;

use QQ\Svn\Provider\AbstractProvider;

class Provider
{
    public static function factory($provider)
    {
        $className = '\\QQ\\Svn\\Provider\\'.$provider;

        /** @var AbstractProvider $provider */
        $provider = new $className();

        if ($provider instanceof AbstractProvider) {
            return $provider;
        }

        throw new \RuntimeException('Unknown provider');
    }
}
