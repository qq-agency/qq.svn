<?php

namespace QQ\Svn\Internal;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\UniqueValidator;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Exception;
use QQ\Svn\Commit;
use QQ\Svn\Task;

class CommitTable extends DataManager
{
    public static function getTableName()
    {
        return 'qq_svn_commits';
    }

    public static function getObjectClass()
    {
        return Commit::class;
    }

    public static function getMap()
    {
        try {
            return [
                new IntegerField(
                    'ID',
                    [
                        'primary' => true,
                        'autocomplete' => true,
                        'title' => Loc::getMessage('SVN_INTERNAL_COMMIT_ID')
                    ]
                ),
                new DatetimeField(
                    'CREATED_AT',
                    [
                        'required' => true,
                        'title' => Loc::getMessage('SVN_INTERNAL_COMMIT_CREATED_AT'),
                        'default_value' => new DateTime()
                    ]
                ),
                new StringField(
                    'SHA',
                    [
                        'required' => true,
                        'title' => Loc::getMessage('SVN_INTERNAL_COMMIT_SHA'),
                        'validation' => function () {
                            return [
                                new UniqueValidator()
                            ];
                        }
                    ]
                ),
                new StringField(
                    'URL',
                    [
                        'required' => true,
                        'title' => Loc::getMessage('SVN_INTERNAL_COMMIT_URL')
                    ]
                ),
                new TextField(
                    'DESCRIPTION',
                    [
                        'required' => true,
                        'title' => Loc::getMessage('SVN_INTERNAL_COMMIT_DESCRIPTION')
                    ]
                ),
                new StringField(
                    'USER_EMAIL',
                    [
                        'required' => true,
                        'title' => Loc::getMessage('SVN_INTERNAL_COMMIT_USER_EMAIL')
                    ]
                ),
                new Reference(
                    'USER',
                    UserTable::class,
                    Join::on('this.USER_EMAIL', 'ref.EMAIL')
                ),

                (new OneToMany('TASKS', TaskTable::class, 'COMMIT'))
                    ->configureJoinType('inner')
            ];
        } catch (Exception $e) {
        }

        return [];
    }

    public static function onAfterAdd(Event $event)
    {
        $result = new EventResult();

        $commitId = $event->getParameter('id');
        $fields = $event->getParameter('fields');

        preg_match_all('/\#([\d]+)/', $fields['DESCRIPTION'], $tasks);

        if (isset($tasks[1]) && $taskIdentifiers = $tasks[1]) {
            $taskCollection = new EO_Task_Collection();

            foreach ($taskIdentifiers as $taskId) {
                $task = new Task();
                $task->setCommitId($commitId);
                $task->setTaskId($taskId);

                $taskCollection->add($task);
            }

            $taskCollection->save();
        }

        return $result;
    }

    public static function onBeforeDelete(Event $event)
    {
        $result = new EventResult();
        $primary = $event->getParameter('promary');

        $taskCollection = TaskTable::getList(['filter' => ['COMMIT_ID' => $primary]])->fetchCollection();
        foreach ($taskCollection as $task) {
            $remove = $task->delete();
            if (!$remove->isSuccess()) {
                $result->setErrors($remove->getErrors());
            }
        }

        return $result;
    }
}
