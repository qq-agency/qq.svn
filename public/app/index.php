<?php

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use QQ\Svn\Internal\TaskTable;

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

Loader::includeSharewareModule('qq.svn');

Loc::loadMessages(__FILE__);

$request = Context::getCurrent()->getRequest();
$placementOptions = Json::decode($request->get('PLACEMENT_OPTIONS'));

$taskId = $placementOptions['taskId'];

$list = [];
$res = TaskTable::getList(
    [
        'order' => ['COMMIT.CREATED_AT' => 'DESC'],
        'filter' => [
            'TASK_ID' => $taskId
        ],
        'select' => [
            '*',
            'COMMIT.USER',
            'COMMIT.USER_EMAIL',
            'COMMIT.CREATED_AT',
            'COMMIT.SHA',
            'COMMIT.URL',
            'COMMIT.DESCRIPTION',
        ]
    ]
);

$APPLICATION->ShowHead();
?>

<table class="qq-git-table">
    <tbody>
    <tr>
        <th class="qq-git-date-column"><?= Loc::getMessage('QQ_SVN_PUBLIC_APP_DATE') ?></th>
        <th class="qq-git-author-column"><?= Loc::getMessage('QQ_SVN_PUBLIC_APP_AUTHOR') ?></th>
        <th class="qq-git-description-column"><?= Loc::getMessage('QQ_SVN_PUBLIC_APP_DESCRIPTION') ?></th>
        <th class="qq-git-commit-column"><?= Loc::getMessage('QQ_SVN_PUBLIC_APP_COMMIT') ?></th>
    </tr>

    <?php foreach ($res->fetchCollection() as $item) { ?>
        <tr class="qq-git-row">
            <td class="qq-git-date-column">
                <span class="qq-git-date">
                    <?= $item->getCommit()->getCreatedAt()->format('d.m.Y\<\b\r\/\>H:i') ?>
                </span>
            </td>
            <td class="qq-git-author-column">
                <?php if ($user = $item->getCommit()->getUser()) { ?>
                    <a href="/company/personal/user/<?= $user->getId() ?>/" target="_top" class="qq-git-author">
                        <?= $user->getName().' '.$user->getLastName() ?>
                    </a>
                <?php } ?>
                <?php if ($position = $item->getCommit()->getUser()->getWorkPosition()) { ?>
                    <div class="qq-git-position"><?= $position ?></div>
                <?php } ?>
            </td>
            <td class="qq-git-description-column">
                <?= nl2br($item->getCommit()->getDescription()) ?>
            </td>
            <td class="qq-git-commit-column">
                <a href="<?= $item->getCommit()->getUrl() ?>" target="_blank">
                    <?= substr($item->getCommit()->getSha(), 0, 8) ?>
                </a>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
