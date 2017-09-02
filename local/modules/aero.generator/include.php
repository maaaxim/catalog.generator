<?php

namespace Aero\Generator;

use Bitrix\Main\Loader;
Loader::includeModule("iblock");

require_once ($_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php");

\CJSCore::Init('jquery');

\CJSCore::RegisterExt(
	"aero_generator",
	array(
		'js' => '/bitrix/js/aero.generator/aero.generator.js'
	)
);