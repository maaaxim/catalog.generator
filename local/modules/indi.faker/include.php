<?php
/**
 * Faker module
 *
 * @category    Individ
 * @link        http://individ.ru
 * @revision    $Revision$
 * @date        $Date$
 */

namespace Indi\Faker;

use Bitrix\Main\Loader;

Loader::includeModule("iblock");

// подключаем внешние библиотеки из композера
require_once($_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php");
/**
 * Базовый каталог модуля
 */
const BASE_DIR = __DIR__;
define("MODULE_IMG_PATH", '/local/modules/indi.faker/images/');

\CJSCore::Init('jquery');

\CJSCore::RegisterExt(
	"indi_faker",
	array(
		'js' => '/bitrix/js/indi.faker/indi.faker.js'
	)
);

