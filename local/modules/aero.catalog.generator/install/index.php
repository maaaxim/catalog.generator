<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class aero_catalog_generator extends CModule {

    public function __construct(){
        $arModuleVersion = array();
        
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_ID = 'aero.catalog.generator';
        $this->MODULE_NAME = Loc::getMessage('CATALOG_MAKER_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('CATALOG_MAKER_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = Loc::getMessage('CATALOG_MAKER_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'https://aeroidea.ru';
    }

    public function DoInstall(){
        $this->InstallFiles();
        $this->installDB();
        ModuleManager::registerModule($this->MODULE_ID);
    }

    public function DoUninstall(){
        $this->UnInstallFiles();
        $this->uninstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function InstallFiles(){
        $parts = array(
            '/admin' => array(
                'target' => '/bitrix/admin',
                'rewrite' => false,
            ),
            '/js' => array(
                'target' => '/bitrix/js',
                'rewrite' => false,
            ),
        );
        foreach ($parts as $dir => $config) {
            CopyDirFiles(
                __DIR__ . $dir,
                $_SERVER['DOCUMENT_ROOT'] . $config['target'],
                $config['rewrite'],
                true
            );
        }
        return true;
    }

    public function UnInstallFiles(){
        DeleteDirFilesEx('/bitrix/js/' . $this->MODULE_ID . '/');
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        );
    }

    public function installDB(){
        if (Loader::includeModule($this->MODULE_ID)) {}
    }

    public function uninstallDB(){
        if (Loader::includeModule($this->MODULE_ID)) {}
    }
}
