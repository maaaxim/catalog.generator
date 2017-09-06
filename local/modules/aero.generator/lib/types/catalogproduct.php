<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Aero\Generator\Types;

use Bitrix\Catalog\GroupTable;
use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Faker\Factory;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Config\Option;

abstract class CatalogProduct
{
    // @TODO replace
    const MODULE_NAME = "aero.generator";

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var iblock id
     */
    protected $iblockId;

    /**
     * @var option data for content settings
     */
    protected $config;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->setConfig();
        $this->setIblockId();
        $this->faker = Factory::create('ru_RU');
        if (!Loader::includeModule('catalog'))
            throw new \Exception("Catalog module is not included");
    }

    /**
     * Returns props and their values
     *
     * @return array
     */
    public function getDataProps():array
    {
        $arProps = [];
        $iblockProps = $this->getIblockProps();
        $iblockPropsValues = $this->generateIblockPropsValues($iblockProps);
        $arProps = array_merge($arProps, $iblockPropsValues);
        return $arProps;
    }

    /**
     * Returns iblock props
     *
     * @return array
     */
    public function getIblockProps():array
    {
        $rsProps = \CIBlockProperty::GetList(
            [],
            ["ACTIVE" => "Y", "IBLOCK_ID" => $this->iblockId]
        );
        $arPropsData = [];
        while ($arProp = $rsProps->GetNext()) {
            $arPropsData[] = [
                'ID' => $arProp['ID'],
                'IBLOCK_ID' => $arProp['LINK_IBLOCK_ID'],
                'CODE' => $arProp['CODE'],
                'MULTIPLE' => $arProp['MULTIPLE'],
                'PROPERTY_TYPE' => $arProp['PROPERTY_TYPE'],
                'USER_TYPE' => $arProp['USER_TYPE'],
                'USER_TYPE_TABLE_NAME' => $arProp['USER_TYPE_SETTINGS']['TABLE_NAME'],
            ];
        }

        return $arPropsData;
    }

    /**
     * Returns filled fields
     *
     * @param array $arParams
     * @return array
     */
    public function getDataFields(array $arParams = []):array
    {
        $iblockId = $this->iblockId;

        $imgPath = $this->getRandomImage();
        $previewPicture = \CFile::MakeFileArray($imgPath);
        $detailPicture = $previewPicture;

        \CFile::ResizeImage(
            $previewPicture,
            ['width' => $this->config["preview_picture_width"], 'height' => $this->config["preview_picture_height"]],
            BX_RESIZE_IMAGE_EXACT
        );

        \CFile::ResizeImage(
            $detailPicture,
            ['width' => $this->config["detail_picture_width"], 'height' => $this->config["detail_picture_height"]],
            BX_RESIZE_IMAGE_EXACT
        );

        $elementName = $this->faker->sentence($this->config["words_in_el_name"]);

        $ibFields = \CIBlock::GetFields($iblockId);
        $sectionId = false;
        if (intval($arParams['SECTION_ID']) > 0) {
            $sectionId = $arParams['SECTION_ID'];
        } elseif (!$sectionId && $ibFields['IBLOCK_SECTION']['IS_REQUIRED'] == "Y") {
            $arFilter = ['IBLOCK_ID' => $iblockId, 'GLOBAL_ACTIVE' => 'Y'];
            $rsSections = \CIBlockSection::GetList(["ID" => "ASC"], $arFilter, true, ['ID'], ['nTopCount' => 1]);
            $arSection = $rsSections->GetNext();
            $sectionId = $arSection['ID'];
        }

        $arFields = [
            "IBLOCK_SECTION_ID" => $sectionId,
            "IBLOCK_ID" => $iblockId,
            "NAME" => $elementName,
            "DATE_ACTIVE_FROM" => self::getCurDateSiteFormat(),
            "CODE" => \Cutil::translit($elementName, "ru"),
            "ACTIVE" => "Y",
            "PREVIEW_TEXT" => $this->faker->sentence($this->config["preview_text_length"]),
            "DETAIL_TEXT" => self::nl2p($this->faker->text($this->config["detail_text_length"])),
            "DETAIL_TEXT_TYPE" => "html",
            "PREVIEW_PICTURE" => $previewPicture,
            "DETAIL_PICTURE" => $detailPicture,
        ];

        return $arFields;
    }

    /**
     * Generates values for all property types
     *
     * @param $iblockProps
     * @return array
     */
    public function generateIblockPropsValues($iblockProps):array
    {
        $resultProps = [];
        foreach ($iblockProps as $iblockProp) {
            switch ($iblockProp['PROPERTY_TYPE']) {
                // String
                case "S": {
                    if ($iblockProp['USER_TYPE'] == 'directory' && Loader::includeModule('highloadblock')) {
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValReference($iblockProp);
                        // html/text
                    } elseif ($iblockProp['USER_TYPE'] == 'HTML') {
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValTextHtml($iblockProp);
                        // date
                    } elseif ($iblockProp['USER_TYPE'] == 'Date') {
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValDate($iblockProp);
                        // date/time
                    } elseif ($iblockProp['USER_TYPE'] == 'DateTime') {
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValDateTime($iblockProp);
                        // simple string
                    } else {
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValString($iblockProp);
                    }
                    break;
                }
                // List
                case "L": {
                    if ($iblockProp['CODE'] != 'INDI_FAKE_DATA')
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValList($iblockProp);
                    break;
                }
                // Number
                case "N": {
                    $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValNumber($iblockProp);
                    break;
                }
                // File
                case "F": {
                    $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValFile($iblockProp);
                    break;
                }
                // Link to element
                case "E": {
                    $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValLink($iblockProp);
                    break;
                }
            }
        }
        return $resultProps;
    }

    /**
     * Returns path to rnd picture
     *
     * @return string
     */
    public function getRandomImage():string
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] . MODULE_IMG_PATH;
        $files = array_values(array_diff(scandir($dir), ['..', '.']));
        $randKey = array_rand($files);
        $randFile = $files[$randKey];
        $randImg = $dir . $randFile;
        return $randImg;
    }

    /**
     * returns date in current format
     *
     * @param string $mode
     * @return string
     */
    public static function getCurDateSiteFormat($mode = 'FULL')
    {
        global $DB;
        return date($DB->DateFormatToPHP(\CSite::GetDateFormat($mode)), time());
    }

    /**
     * Format string into paragraphs and wrap it with <p> tag
     *
     * @param $string
     * @return string
     */
    public static function nl2p($string)
    {
        $string = '<p>' . preg_replace('/\r\n|\n|\r/', '</p>$0<p>', $string) . '</p>';
        $string = preg_replace('/<p><\/p>/', '', $string);
        return $string;
    }

    /**
     * Set iblock id
     *
     * @throws \Exception
     */
    protected function setIblockId()
    {
        $calledClass = get_called_class();
        $iblockRes = IblockTable::getList([
            "filter" => ["CODE" => $calledClass::IBLOCK_CODE],
            "select" => ["ID"],
            "limit" => 1
        ]);
        if ($iblockFields = $iblockRes->fetch()) {
            $this->iblockId = (int)$iblockFields["ID"];
        } else {
            throw new \Exception("Iblock is not created!");
        }
    }

    /**
     * Returns random XML_ID items from linked highload iblock
     *
     * @param $iblockProp
     * @return array
     * @throws \Exception
     */
    private function generateIblockPropValReference($iblockProp):array
    {
        $hlRes = HighloadBlockTable::getList(
            [
                "select" => [
                    "ID",
                ],
                "filter" => [
                    'TABLE_NAME' => $iblockProp['USER_TYPE_TABLE_NAME'],
                ],
            ]
        );
        $arHlBlock = $hlRes->fetch();

        if (empty($arHlBlock))
            throw new \Exception("Hl iblock shouldn't be emppty!");

        $hlBlock = HighloadBlockTable::getById($arHlBlock['ID'])->fetch();
        $entity = HighloadBlockTable::compileEntity($hlBlock);
        $entityClass = $entity->getDataClass();
        if ($iblockProp['MULTIPLE'] == "Y") {
            $rsValue = $entityClass::getList(
                [
                    'select' => ['ID', 'UF_XML_ID'],
                    'filter' => ['!UF_XML_ID' => false],
                    'limit' => $this->config['property_multiple_count'],
                    'order' => ['RAND' => 'ASC'],
                    'runtime' => [
                        new ExpressionField('RAND', 'RAND()'),
                    ],
                ]
            );
            $val = [];
            while ($arValue = $rsValue->fetch()) {
                if (!empty($arValue) && $arValue['UF_XML_ID']) {
                    $val[] = $arValue['UF_XML_ID'];
                }
            }
        } else {
            $rsValue = $entityClass::getList(
                [
                    'select' => ['ID', 'UF_XML_ID'],
                    'filter' => ['!UF_XML_ID' => false],
                    'limit' => 1,
                    'order' => ['RAND' => 'ASC'],
                    'runtime' => [
                        new ExpressionField('RAND', 'RAND()'),
                    ],
                ]
            );
            $arValue = $rsValue->fetch();
            if (!empty($arValue) && $arValue['UF_XML_ID']) {
                $val = $arValue['UF_XML_ID'];
            }
        }

        return $val;
    }


    /**
     * Generates values form html/text
     *
     * @param $iblockProp
     * @return array
     */
    private function generateIblockPropValTextHtml($iblockProp):array
    {
        if ($iblockProp['MULTIPLE'] == "Y") {
            $val = [];
            for ($i = 0; $i < $this->config['property_multiple_count']; $i++) {
                $val[]['VALUE'] = [
                    'TYPE' => 'html',
                    'TEXT' => self::nl2p($this->faker->text($this->config['property_text_length'])),
                ];
            }
        } else {
            $val['VALUE'] = [
                'TYPE' => 'html',
                'TEXT' => self::nl2p($this->faker->text($this->config['property_text_length']))
            ];
        }

        return $val;
    }

    /**
     * Generates date type value
     *
     * @param $iblockProp
     * @return array|string
     */
    private function generateIblockPropValDate($iblockProp)
    {
        global $DB;
        $siteFormat = \CSite::GetDateFormat('SHORT');
        $phpFormat = $DB->DateFormatToPHP($siteFormat);

        if ($iblockProp['MULTIPLE'] == "Y") {
            $val = [];
            for ($i = 0; $i < $this->config['property_multiple_count']; $i++) {
                $val[] = $this->faker->date($phpFormat);
            }
        } else {
            $val = $this->faker->date($phpFormat);
        }

        return $val;
    }

    /**
     * Generates datetime values
     *
     * @param $iblockProp
     * @return array|string
     */
    private function generateIblockPropValDateTime($iblockProp)
    {
        global $DB;
        $siteFormat = \CSite::GetDateFormat('FULL');
        $phpFormat = $DB->DateFormatToPHP($siteFormat);

        if ($iblockProp['MULTIPLE'] == "Y") { // множественное
            $val = [];
            for ($i = 0; $i < $this->config['property_multiple_count']; $i++) {
                $val[] = $this->faker->date($phpFormat);
            }
        } else {
            $val = $this->faker->date($phpFormat);
        }

        return $val;
    }

    /**
     * Generates values for string
     *
     * @param $iblockProp
     * @return array|string
     */
    private function generateIblockPropValString($iblockProp)
    {
        if ($iblockProp['MULTIPLE'] == "Y") {
            $val = [];
            for ($i = 0; $i < $this->config['property_multiple_count']; $i++) {
                $val[] = $this->faker->sentence($this->config['property_string_length']);
            }
        } else {
            $val = $this->faker->sentence($this->config['property_string_length']);
        }
        return $val;
    }

    /**
     * Generates values for list
     *
     * @param $iblockProp
     * @return array
     */
    private function generateIblockPropValList($iblockProp):array
    {
        $rsPropVals = \CIBlockPropertyEnum::GetList(
            ["SORT" => "DESC"],
            ["IBLOCK_ID" => $this->iblockId, "CODE" => $iblockProp["CODE"]]
        );
        $arPropValIds = [0 => 'false'];
        while ($arPropVal = $rsPropVals->GetNext()) {
            $arPropValIds[] = $arPropVal['ID'];
        }
        if ($iblockProp['MULTIPLE'] == "Y") {
            $randKeys = array_rand($arPropValIds, $this->config['property_multiple_count']);
            $val = [];
            foreach ($randKeys as $index => $randKey) {
                $val[$index] = $arPropValIds[$randKey];
            }
        } else {
            $randKey = array_rand($arPropValIds);
            $val = $arPropValIds[$randKey];
        }
        return $val;
    }

    /**
     * Generates values for number
     *
     * @param $arParams
     * @return array
     */
    private function generateIblockPropValNumber($arParams)
    {
        $iblockProp = $arParams["PROP"];
        if ($iblockProp['MULTIPLE'] == "Y") {
            $val = [];
            for ($i = 0; $i < $this->config['property_multiple_count']; $i++) {
                $val[] = $this->faker->randomDigit();
            }
        } else {
            $val = $this->faker->randomDigit();
        }
        return $val;
    }

    /**
     * Generates values for file property
     *
     * @param $arParams
     * @return array
     */
    private function generateIblockPropValFile($arParams):array
    {
        $iblockProp = $arParams["PROP"];

        if ($iblockProp['MULTIPLE'] == "Y") {
            $val = [];
            for ($i = 0; $i < $this->config['property_multiple_count']; $i++) {
                $val[] = \CFile::MakeFileArray(self::getRandomImage());
            }
        } else {
            $val = \CFile::MakeFileArray(self::getRandomImage());
        }

        return $val;
    }

    /**
     * Generates values for link to element property
     *
     * @param $iblockProp
     * @return array|int
     */
    private function generateIblockPropValLink($iblockProp)
    {
        if ($iblockProp['IBLOCK_ID']) {
            if ($iblockProp['MULTIPLE'] == "Y") {
                $val = [];
                $arSelect = ["ID"];
                $arFilter = ["IBLOCK_ID" => $iblockProp['IBLOCK_ID'], "ACTIVE" => "Y"];
                $rsElements = \CIBlockElement::GetList(
                    ["RAND" => "ASC"], $arFilter,
                    false,
                    ["nTopCount" => $this->config['property_multiple_count']],
                    $arSelect
                );
                while ($arElement = $rsElements->GetNext()) {
                    $val[] = $arElement['ID'];
                }
            } else {
                $arSelect = ["ID"];
                $arFilter = ["IBLOCK_ID" => $iblockProp['IBLOCK_ID'], "ACTIVE" => "Y"];
                $rsElements = \CIBlockElement::GetList(
                    ["RAND" => "ASC"],
                    $arFilter,
                    false,
                    ["nTopCount" => 1],
                    $arSelect
                );
                $arElement = $rsElements->GetNext();
                $val = $arElement['ID'];
            }
        }
        return $val;
    }

    /**
     * Set config
     */
    private function setConfig()
    {
        $this->config = [
            "count"                      => Option::get(self::MODULE_NAME, "count"),
            "words_in_el_name"           => Option::get(self::MODULE_NAME, "words_in_el_name"),
            "preview_text_length"        => Option::get(self::MODULE_NAME, "preview_text_length"),
            "detail_text_length"         => Option::get(self::MODULE_NAME, "detail_text_length"),
            "preview_picture_width"      => Option::get(self::MODULE_NAME, "preview_picture_width"),
            "preview_picture_height"     => Option::get(self::MODULE_NAME, "preview_picture_height"),
            "detail_picture_width"       => Option::get(self::MODULE_NAME, "detail_picture_width"),
            "detail_picture_height"      => Option::get(self::MODULE_NAME, "detail_picture_height"),
            "catalog_price_max_decimals" => Option::get(self::MODULE_NAME, "catalog_price_max_decimals"),
            "catalog_price_min"          => Option::get(self::MODULE_NAME, "catalog_price_min"),
            "catalog_price_max"          => Option::get(self::MODULE_NAME, "catalog_price_max"),
            "catalog_weight_min"         => Option::get(self::MODULE_NAME, "catalog_weight_min"),
            "catalog_weight_max"         => Option::get(self::MODULE_NAME, "catalog_weight_max"),
            "catalog_width_min"          => Option::get(self::MODULE_NAME, "catalog_width_min"),
            "catalog_width_max"          => Option::get(self::MODULE_NAME, "catalog_width_max"),
            "catalog_length_min"         => Option::get(self::MODULE_NAME, "catalog_length_min"),
            "catalog_length_max"         => Option::get(self::MODULE_NAME, "catalog_length_max"),
            "catalog_height_min"         => Option::get(self::MODULE_NAME, "catalog_height_min"),
            "catalog_height_max"         => Option::get(self::MODULE_NAME, "catalog_height_max"),
            "catalog_quantity_min"       => Option::get(self::MODULE_NAME, "catalog_quantity_min"),
            "catalog_quantity_max"       => Option::get(self::MODULE_NAME, "catalog_quantity_max"),
            "property_multiple_count"    => Option::get(self::MODULE_NAME, "property_multiple_count"),
            "property_string_length"     => Option::get(self::MODULE_NAME, "property_string_length"),
            "property_text_length"       => Option::get(self::MODULE_NAME, "property_text_length"),
            "types_price"                => Option::get(self::MODULE_NAME, "types_price")
        ];
    }

    /**
     * Generates catalog data
     *
     * @param $productId
     * @return array
     * @internal param $arParams
     */
    public function getDataProduct($productId):array
    {
        $arPriceType = \CCatalogGroup::GetBaseGroup();
        $arPriceTypeId = $arPriceType["ID"];
        $arPriceFields = [
            "PRODUCT_ID" => $productId,
            "CATALOG_GROUP_ID" => $arPriceTypeId,
            "PRICE" => $this->faker->randomFloat(
                $this->config['catalog_price_max_decimals'],
                $this->config['catalog_price_min'],
                $this->config['catalog_price_max']
            ),
            "CURRENCY" => "RUB",
        ];
        $weight = $this->faker->numberBetween($this->config['catalog_weight_min'], $this->config['catalog_weight_max']);
        $width = $this->faker->numberBetween($this->config['catalog_width_min'], $this->config['catalog_width_max']);
        $length = $this->faker->numberBetween($this->config['catalog_length_min'], $this->config['catalog_length_max']);
        $height = $this->faker->numberBetween($this->config['catalog_height_min'], $this->config['catalog_height_max']);
        $quantity = $this->faker->numberBetween($this->config['catalog_quantity_min'], $this->config['catalog_quantity_max']);
        $arProduct = [
            'PRICE' => $arPriceFields,
            "WEIGHT" => $weight,
            "WIDTH" => $width,
            "LENGTH" => $length,
            "HEIGHT" => $height,
            "QUANTITY" => $quantity,
        ];
        return $arProduct;
    }

    /**
     * @param $elementId
     * @throws \Exception
     */
    protected function addPrices(int $elementId)
    {
        $priceCount = (int)$this->config["types_price"];
        if ($priceCount <= 0)
            throw new \Exception("We need more then 0 prices");
        $priceTypesRes = GroupTable::getList([
            "select" => ["ID", "BASE"],
            "limit" => $priceCount
        ]);
        while ($priceTypesFields = $priceTypesRes->fetch()) {
            $price = $this->faker->randomFloat(
                $this->config["catalog_price_max_decimals"],
                $this->config["catalog_price_min"],
                $this->config["catalog_price_max"]
            );
            $fields = [
                "PRODUCT_ID" => $elementId,
                "CATALOG_GROUP_ID" => $priceTypesFields["ID"],
                "PRICE" => $price,
                "PRICE_SCALE" => $price,
                "CURRENCY" => "RUB"
            ];
            $result = PriceTable::add($fields);
            if (!$result->isSuccess()) {
                throw new \Exception(implode(" ", $result->getErrorMessages()));
            }
        }
    }

    /**
     * @param $elementId
     * @return int
     */
    protected function addStoresCount(int $elementId): int
    {
        $totalCount = 0;
        $storesRes = StoreTable::getList([
            "filter" => ["ID"]
        ]);
        while ($storesFields = $storesRes->fetch()) {
            $count = $this->faker->numberBetween(
                $this->config['catalog_quantity_min'],
                $this->config['catalog_quantity_max']
            );
            StoreProductTable::add([
                "PRODUCT_ID" => $elementId,
                "AMOUNT" => $count,
                "STORE_ID" => $storesFields["ID"]
            ]);
            $totalCount += $count;
        }
        return $totalCount;
    }

    /**
     * @return int
     */
    protected function addIblockElement():int
    {
        // create element
        $element = new \CIBlockElement;

        // get props array like code => val
        $arProps = $this->getDataProps();

        // get prop fields
        $arFields = $this->getDataFields();
        $arFields["PROPERTY_VALUES"] = $arProps;

        // add iblock element
        $elementId = $element->Add($arFields);

        return $elementId;
    }

    /**
     * @param int $elementId
     * @param int $totalCount
     * @return int
     */
    protected function addCatalogProduct(int $elementId, int $totalCount)
    {
        // get product data
        $productData = $this->getDataProduct($elementId);

        // add product
        \CCatalogProduct::Add(
            [
                'ID' => $elementId,
                'QUANTITY' => $totalCount,
                'WEIGHT' => $productData['WEIGHT'],
                'WIDTH' => $productData['WIDTH'],
                'LENGTH' => $productData['LENGTH'],
                'HEIGHT' => $productData['HEIGHT'],
            ]
        );
    }
}