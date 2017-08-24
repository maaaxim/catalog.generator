<?php

namespace Aero\Generator;

\CJSCore::Init('jquery');

\CJSCore::RegisterExt(
	"aero_catalog_generator",
	array(
		'js' => '/bitrix/js/aero.catalog.generator/aero.catalog.generator.js'
	)
);