<?php

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;
use QQ\Svn\Internal\CommitTable;
use QQ\Svn\Internal\TaskTable;
use QQ\Svn\Rest\Svn;

Loc::loadMessages(__FILE__);

class qq_svn extends CModule
{
    var $MODULE_ID = 'qq.svn';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__.'/version.php';
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage('QQ_SVN_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('QQ_SVN_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('QQ_SVN_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('QQ_SVN_PARTNER_URI');
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);

        $this->InstallFiles();
        $this->InstallDB();
        $this->InstallEvents();

        return true;
    }

    public function InstallFiles()
    {
        CopyDirFiles(__DIR__.'/public/app/', Application::getDocumentRoot().'/local/rest/qq.svn/');
    }

    public function InstallDB()
    {
        try {
            Loader::includeSharewareModule('qq.git');

            foreach (
                [
                    CommitTable::getEntity(),
                    TaskTable::getEntity(),
                ] as $entity
            ) {
                /** @var $entity \Bitrix\Main\ORM\Entity */
                if (!Application::getConnection()->isTableExists($entity->getDBTableName())) {
                    $entity->createDbTable();
                }
            }
            // todo: add application
        } catch (ArgumentException $e) {
        } catch (SystemException $e) {
        }
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            Svn::class,
            'onRestServiceBuildDescription'
        );
    }

    public function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->UnInstallDB();
        $this->UnInstallFiles();

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            Svn::class,
            'onRestServiceBuildDescription'
        );
    }

    public function UnInstallDB()
    {
        try {
            Loader::includeSharewareModule('qq.git');

            $connection = Application::getConnection();

            foreach (
                [
                    CommitTable::getTableName(),
                    TaskTable::getTableName(),
                ] as $table
            ) {
                if ($connection->isTableExists($table)) {
                    Application::getConnection()->dropTable($table);
                }
            }
            // todo: remove application
        } catch (SqlQueryException $e) {
        }
    }

    public function UnInstallFiles()
    {
        DeleteDirFilesEx(Application::getDocumentRoot().'/local/rest/qq.svn/');
    }
}
