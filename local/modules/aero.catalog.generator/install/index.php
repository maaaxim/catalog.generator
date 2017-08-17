<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

class aero_catalog_generator extends CModule
{
    var $MODULE_ID = 'aero.catalog.generator';

    function __construct()
    {
        $arModuleVersion = array();

        include(__DIR__ . '/version.php');

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage('CATGEN_AH_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('CATGEN_AH_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('CATGEN_AH_PARTNER_NAME');
        $this->PARTNER_URI = 'https://www.facebook.com/loboda.maxim';
    }

    public function DoInstall()
    {
        global $DB;

        $DB->RunSQLBatch(__DIR__ . '/install.sql');
        ModuleManager::registerModule($this->MODULE_ID);
        Loader::includeModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        global $DB;
        
        Loader::includeModule($this->MODULE_ID);

        $DB->RunSQLBatch(__DIR__ . '/uninstall.sql');
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}