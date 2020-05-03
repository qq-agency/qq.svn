<?php

namespace QQ\Svn\Internal;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Tasks\TaskTable as BitrixTaskTable;
use Exception;
use QQ\Svn\Task;

class TaskTable extends DataManager
{
    public static function getTableName()
    {
        return 'qq_git_tasks';
    }

    public static function getObjectClass()
    {
        return Task::class;
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
                        'title' => Loc::getMessage('QQ_SVN_INTERNAL_TASK_ID')
                    ]
                ),
                new IntegerField(
                    'COMMIT_ID',
                    [
                        'title' => Loc::getMessage('QQ_SVN_INTERNAL_TASK_COMMIT_ID')
                    ]
                ),

                new IntegerField(
                    'TASK_ID',
                    [
                        'title' => Loc::getMessage('QQ_SVN_INTERNAL_TASK_TASK_ID')
                    ]
                ),

                new Reference(
                    'COMMIT',
                    CommitTable::class,
                    Join::on('this.COMMIT_ID', 'ref.ID')
                ),

                new Reference(
                    'TASK',
                    BitrixTaskTable::class,
                    Join::on('this.TASK_ID', 'ref.ID')
                ),
            ];
        } catch (Exception $e) {
        }

        return [];
    }

    public static function onBeforeAdd(Event $event)
    {
        $result = new EventResult();

        $fields = $event->getParameter('fields');

        if (static::getList(
                [
                    'filter' => ['TASK_ID' => $fields['TASK_ID'], 'COMMIT_ID' => $fields['COMMIT_ID']]
                ]
            )->getSelectedRowsCount() > 0) {
            $result->addError(new EntityError('Entity exists'));
        }

        return $result;
    }
}
