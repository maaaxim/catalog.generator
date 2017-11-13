<?php

namespace Catalog\Generator;

use Bitrix\Main\Loader;
Loader::includeModule("iblock");

require_once ($_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php");

\CJSCore::Init('jquery');

// @TODO put somewhere
define("MODULE_IMG_PATH", '/bitrix/modules/catalog.generator/images/');

\CJSCore::RegisterExt(
	"catalog_generator",
	[
		'js' => '/bitrix/js/catalog.generator/catalog.generator.js'
    ]
);