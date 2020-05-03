<?php

namespace QQ\Svn\Rest;

use Bitrix\Main\Context;
use Bitrix\Rest\RestException;
use QQ\Svn\Provider;

class Svn
{
    const SCOPE_PLACEMENT = 'qq.svn';

    public static function onRestServiceBuildDescription()
    {
        return [
            static::SCOPE_PLACEMENT => [
                'git.push' => [
                    'callback' => [__CLASS__, 'push'],
                    'options' => []
                ],
            ],
        ];
    }

    public function push($query, $n, \CRestServer $server)
    {
        $request = Context::getCurrent()->getRequest();

        if ($request->getRequestMethod() !== 'POST') {
            throw new RestException('Only POST request currently support.', 400);
        }

        return Provider::factory('GitHub')->handleEvent($query);
    }
}
