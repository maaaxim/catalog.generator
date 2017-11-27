<?
namespace Catalog\Generator;
use Bitrix\Main\Loader;
use CJSCore;
Loader::includeModule("iblock");
CJSCore::Init('jquery');
CJSCore::RegisterExt(
	"catalog_generator",
	[
		'js' => '/bitrix/js/catalog.generator/catalog.generator.js'
    ]
);