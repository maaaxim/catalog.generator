<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class aero_catalog_generator extends CModule
{
    public function __construct()
    {
        $arModuleVersion = array();
        
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_ID = 'aero.catalog.generator';
        $this->MODULE_NAME = Loc::getMessage('CATALOG_MAKER_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('CATALOG_MAKER_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = Loc::getMessage('CATALOG_MAKER_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'https://www.facebook.com/loboda.maxim';
    }

    public function doInstall()
    {
        $this->installDB();
        ModuleManager::registerModule($this->MODULE_ID);
    }

    public function doUninstall()
    {
        $this->uninstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
            // Do smth
        }
    }

    public function uninstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
            $connection = Application::getInstance()->getConnection();
            // Do smth
        }
    }
}
