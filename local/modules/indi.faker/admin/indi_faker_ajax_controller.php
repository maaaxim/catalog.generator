<?
use Bitrix\Main\Loader;
use Indi\Faker\Controller;
use Indi\Faker\Valid;
use Indi\Faker\Iblock\Prototype as IblockDataManager;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

Loader::includeModule("indi.faker");

// получаем данные
$cController = new Controller();

// проверяем данные
$validResult = Valid::checkControllerData($cController);

// есть ошибки, выводим
if(is_array($validResult) && strlen($validResult['error'])) {
	echo $cController->constructResult(array('error' => $validResult['error']));
	return;
}

// выполняем действие в зависимости от требуемого действия
$cIblockDataManager = new IblockDataManager($cController->iblockId);
switch($cController->action) {
	// создание тестовых данных
	case 'generate': {
		$cIblockDataManager->generateData(
			array("SECTION_ID" => $cController->sectionId)
		);
		break;
	}
	// удаление тестовых данных
	case 'delete': {
		$cIblockDataManager->deleteData(array("SECTION_ID" => $cController->sectionId));
		break;
	}
}

echo $cController->constructResult(array('result' => array('SUCCESS' => 1)));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");