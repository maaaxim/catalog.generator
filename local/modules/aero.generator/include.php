<?php

namespace Aero\Generator;

use Bitrix\Main\Loader;
Loader::includeModule("iblock");

require_once ($_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php");

\CJSCore::Init('jquery');

// @TODO put somewhere
define("MODULE_IMG_PATH", '/local/modules/aero.generator/images/');

\CJSCore::RegisterExt(
	"aero_generator",
	[
		'js' => '/bitrix/js/aero.generator/aero.generator.js'
    ]
);