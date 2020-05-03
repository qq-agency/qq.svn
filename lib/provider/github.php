<?php


namespace QQ\Svn\Provider;

use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use QQ\Svn\Commit;

class GitHub extends AbstractProvider
{
    public function handleEvent($object)
    {
        $object = Json::decode($object['payload']);

        if ($_SERVER['HTTP_X_GITHUB_EVENT'] === 'push') {
            return $this->handlePushEvent($object);
        }

        throw new \RuntimeException('Only push events currently support.');
    }

    protected function handlePushEvent($object)
    {
        $result = new Result();

        foreach ($object['commits'] as $item) {
            $commit = new Commit();
            $commit->setSha($item['id']);
            $commit->setCreatedAt(DateTime::createFromTimestamp(strtotime($item['timestamp'])));
            $commit->setUserEmail($item['author']['email']);
            $commit->setDescription($item['message']);
            $commit->setUrl($item['url']);
            $save = $commit->save();

            if (!$save->isSuccess()) {
                $result->addErrors($save->getErrors());
            }
        }

        return $result;
    }
}
