<?php

namespace Aero\Generator;

use Bitrix\Main\Loader;
Loader::includeModule("iblock");

\CJSCore::Init('jquery');

\CJSCore::RegisterExt(
	"aero_generator",
	array(
		'js' => '/bitrix/js/aero.generator/aero.generator.js'
	)
);