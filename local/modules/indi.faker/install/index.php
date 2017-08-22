<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Indi\Faker\Iblock\Prototype;

Loader::includeModule("iblock");

Loc::loadMessages(__FILE__);

if (class_exists('indi_faker')) {
	return;
}

class indi_faker extends \CModule
{
	/**
	 * ID модуля
	 *
	 * @var string
	 */
	public $MODULE_ID = 'indi.faker';

	/**
	 * Версия модуля
	 *
	 * @var string
	 */
	public $MODULE_VERSION = '';

	/**
	 * Дата выхода версии
	 *
	 * @var string
	 */
	public $MODULE_VERSION_DATE = '';

	/**
	 * Название модуля
	 *
	 * @var string
	 */
	public $MODULE_NAME = '';

	/**
	 * Описание модуля
	 *
	 * @var string
	 */
	public $MODULE_DESCRIPTION = '';

	/**
	 * Таблица стилей модуля
	 *
	 * @var string
	 */
	public $MODULE_CSS = '';

	/**
	 * Список обработчиков, устанавливаемых модулем
	 *
	 * @var array
	 */
	protected $eventHandlers = array();

	/**
	 * Конструктор модуля "Индивид"
	 *
	 * @return void
	 */
	public function __construct()
	{
		$version = include __DIR__ . '/version.php';

		$this->MODULE_VERSION = $version['VERSION'];
		$this->MODULE_VERSION_DATE = $version['VERSION_DATE'];

		$this->MODULE_NAME = Loc::getMessage('INDI_FAKER_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('INDI_FAKER_MODULE_DESC');

		$this->PARTNER_NAME = Loc::getMessage('INDI_FAKER_PARTNER_NAME');
		$this->PARTNER_URI = Loc::getMessage('INDI_FAKER_PARTNER_URI');

		$this->eventHandlers = array(
			array('main', 'OnAdminContextMenuShow', 'Indi\Faker\Iblock\Prototype', 'onAdminContextMenuShow'),
		);
	}

	/**
	 * Устанавливает данные модуля в БД
	 *
	 * @return boolean
	 */
	public function installDB()
	{
		global $DB;

		$DB->RunSQLBatch(__DIR__ . '/sql/install.sql');

		return true;
	}

	/**
	 * Удаляет таблицы модуля
	 *
	 * @return boolean
	 */
	public function unInstallDB()
	{
		global $DB;

		$DB->RunSQLBatch(__DIR__ . '/sql/uninstall.sql');

		return true;
	}

	/**
	 * Устанавливает события модуля
	 *
	 * @return boolean
	 */
	public function installEvents()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();

		foreach ($this->eventHandlers as $handler) {
			$eventManager->registerEventHandler(
				$handler[0],
				$handler[1],
				$this->MODULE_ID,
				$handler[2],
				$handler[3]
			);
		}

		return true;
	}

	/**
	 * Удаляет события модуля
	 *
	 * @return boolean
	 */
	public function unInstallEvents()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		foreach ($this->eventHandlers as $handler) {
			$eventManager->unRegisterEventHandler(
				$handler[0],
				$handler[1],
				$this->MODULE_ID,
				$handler[2],
				$handler[3]
			);
		}

		return true;
	}

	/**
	 * Устанавливает файлы модуля
	 *
	 * @return boolean
	 */
	public function installFiles($arParams = array())
	{
		$moduleDir = explode('/', __DIR__);
		array_pop($moduleDir);
		$moduleDir = implode('/', $moduleDir);

		$sourceRoot = $moduleDir . '/install/';
		$targetRoot = $_SERVER['DOCUMENT_ROOT'];

		$parts = array(
			'admin' => array(
				'target' => '/bitrix/admin',
				'rewrite' => false,
			),
			'js' => array(
				'target' => '/bitrix/js',
				'rewrite' => false,
			),
		);
		foreach ($parts as $dir => $config) {
			CopyDirFiles(
				$sourceRoot . $dir,
				$targetRoot . $config['target'],
				$config['rewrite'],
				true
			);
		}

		return true;
	}

	/**
	 * Удаляет файлы модуля
	 *
	 * @return boolean
	 */
	public function unInstallFiles()
	{
		DeleteDirFilesEx('/bitrix/js/' . $this->MODULE_ID . '/');
		DeleteDirFilesEx('/bitrix/admin/' . $this->MODULE_ID . '/');
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");

		return true;
	}

	/**
	 * Удаляет свойство "Тестовые данные" из всех инфоблоков
	 *
	 * @return boolean
	 */
	public function deleteFakeProperty()
	{
		$rsProp = \CIBlockProperty::GetList(
			array(),
			array("ACTIVE" => "Y", "CODE" => "INDI_FAKE_DATA")
		);
		while ($arProp = $rsProp->GetNext()) {
			\CIBlockProperty::Delete($arProp["ID"]);
		}

		return true;
	}

	/**
	 * Удаляет "Тестовые данные" из всех инфоблоков
	 *
	 * @return boolean
	 */
	public function deleteFakeData()
	{
		$rsProp = \CIBlockProperty::GetList(
			array(),
			array("ACTIVE" => "Y", "CODE" => "INDI_FAKE_DATA")
		);

		while ($arProp = $rsProp->GetNext()) {
			$arSelect = array("ID");
			$arFilter = array(">PROPERTY_INDI_FAKE_DATA" => 0, "IBLOCK_ID" => $arProp["IBLOCK_ID"]);
			$rsElements = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
			while ($arElement = $rsElements->GetNext()) {
				\CIBlockElement::Delete($arElement["ID"]);
			}
		}

		return true;
	}

	/**
	 * Устанавливает модуль
	 *
	 * @return void
	 */
	public function doInstall()
	{
		if ($this->installDB()
			&& $this->installEvents()
			&& $this->installFiles()
		) {
			\Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
		}
	}

	/**
	 * Удаляет модуль
	 *
	 * @return void
	 */
	public function doUninstall()
	{
		$this->deleteFakeData();
		$this->deleteFakeProperty();
		if ($this->unInstallDB()
			&& $this->unInstallEvents()
			&& $this->unInstallFiles()
		) {
			\Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
		}
	}
}