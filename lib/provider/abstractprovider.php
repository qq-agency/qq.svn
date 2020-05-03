<?php

namespace QQ\Svn\Provider;

use Bitrix\Main\ORM\Data\Result;

abstract class AbstractProvider
{
    public function __construct()
    {
    }

    /** @return Result */
    abstract public function handleEvent($object);

    /** @return Result */
    abstract protected function handlePushEvent($object);
}
