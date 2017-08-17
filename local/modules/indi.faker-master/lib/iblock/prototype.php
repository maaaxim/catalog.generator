<?php
/**
 * Individ faker
 *
 * @category       Individ
 * @package        Iblock
 * @link           http://individ.ru
 * @revision    $Revision$
 * @date        $Date$
 */

namespace Indi\Faker\Iblock;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\ExpressionField;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use Faker\Factory;
use Indi\Faker\Config;
use Indi\Faker\Util;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('iblock')) {
    $GLOBALS['APPLICATION']->ThrowException("Infoblock module is't installed.");
}

Loader::includeModule("catalog");

/**
 * Класс для работы с инфоблоками
 *
 * @category       Individ
 * @package        Iblock
 */
class Prototype
{

    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Обработчик события формирования контекстного меню
     * @param array $items - массив данных для кнопок
     *
     */
    public static function onAdminContextMenuShow(&$items)
    {
        \CJSCore::Init(array("indi_faker")); // подключаем свое js-расширение

        // кнопка Создать тестовые данные
        $arPaths = array(
            '/bitrix/admin/iblock_section_admin.php',
            '/bitrix/admin/iblock_element_admin.php',
        );

        if (in_array($GLOBALS['APPLICATION']->GetCurPage(true), $arPaths)) {
            $items[] = array(
                'TEXT' => Loc::getMessage("INDI_FAKER_IBLOCK_PROTOTYPE_GENERATE_BUTTON"),
                'ICON' => '',
                'TITLE' => Loc::getMessage("INDI_FAKER_IBLOCK_PROTOTYPE_GENERATE_BUTTON_DESC"),
                'LINK' => '',
                'LINK_PARAM' => 'class="adm-btn js-demo-generate-default" data-url="/bitrix/admin/indi_faker_ajax_controller.php"',
            );
        }

        // кнопка Удалить тестовые данные
        $arPaths = array(
            '/bitrix/admin/iblock_section_admin.php',
            '/bitrix/admin/iblock_element_admin.php',
        );

        if (
        in_array($GLOBALS['APPLICATION']->GetCurPage(true), $arPaths)
        ) {
            $items[] = array(
                'TEXT' => Loc::getMessage("INDI_FAKER_IBLOCK_PROTOTYPE_DELETE_BUTTON"),
                'ICON' => '',
                'TITLE' => Loc::getMessage("INDI_FAKER_IBLOCK_PROTOTYPE_DELETE_BUTTON_DESC"),
                'LINK' => '',
                'LINK_PARAM' => 'class="adm-btn js-demo-delete" data-url="/bitrix/admin/indi_faker_ajax_controller.php"',
            );

        }
    }

    /**
     * getPropertyListItemId
     *
     * Получает id варианта значения свойства типа список
     *
     * @param string $propCode код свойства
     * @param string $value значение свойства
     *
     * @return integer
     */

    public function getPropertyListItemId($propCode, $value)
    {
        $rsProp = \CIBlockPropertyEnum::GetList(
            array(),
            array(
                "IBLOCK_ID" => $this->id,
                "CODE" => $propCode,
                "VALUE" => $value,
            )
        );
        $arProp = $rsProp->GetNext();

        return $arProp["ID"];
    }

    /**
     * addFakeDataProperty
     *
     * Добавление для инфоблока свойства "Тестовые данные"
     * Свойство добавляется, только если у инфоблока оно отсутствует
     *
     */

    public function addFakeDataProperty()
    {
        $rsProp = \CIBlockProperty::GetList(
            array(),
            array("ACTIVE" => "Y", "IBLOCK_ID" => $this->id, "CODE" => "INDI_FAKE_DATA")
        );
        if ($arProp = $rsProp->GetNext()) { // свойство уже создано
            return;
        }

        $arFields = Array(
            "NAME" => "Тестовые данные",
            "ACTIVE" => "Y",
            "SORT" => "100",
            "CODE" => "INDI_FAKE_DATA",
            "PROPERTY_TYPE" => "L",
            "LIST_TYPE" => "C",
            "IBLOCK_ID" => $this->id,
        );

        $arFields["VALUES"][0] = Array(
            "VALUE" => "Y",
            "DEF" => "N",
            "SORT" => "100",
        );

        $cIbP = new \CIBlockProperty;
        $cIbP->Add($arFields);

    }

    /**
     * generateDataFields
     * Формирует поля для тестовых данных инфоблока
     *
     * @param array $arParams
     *
     * @return array $arFields
     */

    public function getDataFields($arParams = array())
    {

        $config = $arParams["CONFIG"];
        $faker = $arParams["FAKER"];
        $iblockId = $this->id;

        $imgPath = Util::getRandomImage();
        $previewPicture = \CFile::MakeFileArray($imgPath);
        $detailPicture = $previewPicture;

        \CFile::ResizeImage(
            $previewPicture, // путь к изображению, сюда же будет записан уменьшенный файл
            array('width' => $config["preview_picture_width"], 'height' => $config["preview_picture_height"]),
            BX_RESIZE_IMAGE_EXACT // метод масштабирования. обрезать прямоугольник без учета пропорций
        );

        \CFile::ResizeImage(
            $detailPicture, // путь к изображению, сюда же будет записан уменьшенный файл
            array('width' => $config["detail_picture_width"], 'height' => $config["detail_picture_height"]),
            BX_RESIZE_IMAGE_EXACT // метод масштабирования. обрезать прямоугольник без учета пропорций
        );

        $elementName = $faker->sentence($config["words_in_el_name"]);

        $ibFields = \CIBlock::GetFields($iblockId);
        $sectionId = false;
        if (intval($arParams['SECTION_ID']) > 0) {
            $sectionId = $arParams['SECTION_ID'];
        }
        else if (!$sectionId && $ibFields['IBLOCK_SECTION']['IS_REQUIRED'] == "Y") { // привязка к разделу обязательное свойство. устанавливаем
            $arFilter = array('IBLOCK_ID' => $iblockId, 'GLOBAL_ACTIVE' => 'Y');
            $rsSections = \CIBlockSection::GetList(array("ID" => "ASC"), $arFilter, true, array('ID'), array('nTopCount' => 1));
            $arSection = $rsSections->GetNext();
            $sectionId = $arSection['ID'];
        }


        $arFields = Array(
            "IBLOCK_SECTION_ID" => $sectionId,          // элемент лежит в корне раздела
            "IBLOCK_ID" => $iblockId,
            "NAME" => $elementName,
            "DATE_ACTIVE_FROM" => Util::getCurDateSiteFormat(),
            "CODE" => \Cutil::translit($elementName, "ru"),
            "ACTIVE" => "Y",            // активен
            "PREVIEW_TEXT" => $faker->sentence($config["preview_text_length"]),
            "DETAIL_TEXT" => Util::nl2p($faker->text($config["detail_text_length"])),
            "DETAIL_TEXT_TYPE" => "html",
            "PREVIEW_PICTURE" => $previewPicture,
            "DETAIL_PICTURE" => $detailPicture,
        );

        return $arFields;
    }

    /**
     * getIblockProps
     * Получает свойства инфоблока
     *
     * @return array
     */

    public function getIblockProps()
    {
        $rsProps = \CIBlockProperty::GetList(
            array(),
            array("ACTIVE" => "Y", "IBLOCK_ID" => $this->id)
        );
        $arPropsData = array();
        while ($arProp = $rsProps->GetNext()) {
            $arPropsData[] = array(
                'ID' => $arProp['ID'],
                'IBLOCK_ID' => $arProp['LINK_IBLOCK_ID'],
                'CODE' => $arProp['CODE'],
                'MULTIPLE' => $arProp['MULTIPLE'],
                'PROPERTY_TYPE' => $arProp['PROPERTY_TYPE'],
                'USER_TYPE' => $arProp['USER_TYPE'],
                'USER_TYPE_TABLE_NAME' => $arProp['USER_TYPE_SETTINGS']['TABLE_NAME'],
            );
        }

        return $arPropsData;
    }

    /**
     * generateDataProps
     * Формирует свойства для тестовых данных инфоблока
     *
     * @param array $arParams мссив параметров с ключами:
     *                        FAKER - экзмемпляр класса faker
     *
     * @return array $arProps
     */

    public function getDataProps($arParams)
    {
        /*
         * Пометка о том, что элемент является "тестовыми данными"
         * */

        $arProps = array(
            "INDI_FAKE_DATA" => $this->getPropertyListItemId('INDI_FAKE_DATA', 'Y'),
        );

        $iblockProps = $this->getIblockProps(); // получаем свойства инфоблока
        $iblockPropsValues = $this->generateIblockPropsVals(
            array(
                "PROPERTIES" => $iblockProps,
                "FAKER" => $arParams['FAKER'],
                "CONFIG" => $arParams['CONFIG'],
            )
        );
        $arProps = array_merge($arProps, $iblockPropsValues);

        return $arProps;
    }

    /**
     * getDataProduct
     * Формирует поля продукта (вкладка торговый каталог) для тестовых данных инфоблока
     *
     * @param array $arParams
     *
     * @return array $arProduct
     */

    public function getDataProduct($arParams)
    {
        $faker = $arParams["FAKER"];
        $config = $arParams["CONFIG"];

        // id базовой цены каталога
        $arPriceType = \CCatalogGroup::GetBaseGroup();
        $arPriceTypeId = $arPriceType["ID"];

        $arPriceFields = Array(
            "PRODUCT_ID" => $arParams["ID"],
            "CATALOG_GROUP_ID" => $arPriceTypeId,
            "PRICE" => $faker->randomFloat(
                $config['catalog_price_max_decimals'],
                $config['catalog_price_min'],
                $config['catalog_price_max']
            ),
            "CURRENCY" => "RUB",
        );

        $weight = $faker->numberBetween($config['catalog_weight_min'], $config['catalog_weight_max']);
        $width = $faker->numberBetween($config['catalog_width_min'], $config['catalog_width_max']);
        $length = $faker->numberBetween($config['catalog_length_min'], $config['catalog_length_max']);
        $height = $faker->numberBetween($config['catalog_height_min'], $config['catalog_height_max']);

        $quantity = $faker->numberBetween($config['catalog_quantity_min'], $config['catalog_quantity_max']);
        $arProduct = array(
            'PRICE' => $arPriceFields,
            "WEIGHT" => $weight,
            "WIDTH" => $width,
            "LENGTH" => $length,
            "HEIGHT" => $height,
            "QUANTITY" => $quantity,
        );

        return $arProduct;
    }

    /**
     * generateData
     * Создание тестовых данных для инфоблока
     *
     * @param array $arParams
     */

    public function generateData($arParams = array())
    {
        // создаем свойство "Тестовые данные" для инфоблока (если ранее не создано)
        $this->addFakeDataProperty();

        // получение конфигурации генератора для ИБ
        $iblockId = $this->id;
        $cConfig = new Config();
        $config = $cConfig->getConfig($iblockId);

        // создаем экземпляр класса faker
        $faker = Factory::create('ru_RU');
        $el = new \CIBlockElement;

        for ($i = 0; $i < intval($config["count"]); $i++) {
            $arProps = $this->getDataProps(array('FAKER' => $faker, 'CONFIG' => $config));
            $arFields = $this->getDataFields(array('CONFIG' => $config, 'FAKER' => $faker, 'SECTION_ID' => $arParams["SECTION_ID"]));
            $arAdd = $arFields;
            $arAdd["PROPERTY_VALUES"] = $arProps;
            $elId = $el->Add($arAdd);

            // если инфоблок является торговым каталогом, то добавляем свойства каталога
            if (Loader::includeModule('catalog')) {
                if (intval($elId) && \CCatalog::GetByID($iblockId)) {
                    $productData = $this->getDataProduct(array('FAKER' => $faker, "ID" => $elId, "CONFIG" => $config));
                    \CCatalogProduct::Add(
                        array(
                            'ID' => $elId,
                            'QUANTITY' => $productData['QUANTITY'],
                            'WEIGHT' => $productData['WEIGHT'],
                            'WIDTH' => $productData['WIDTH'],
                            'LENGTH' => $productData['LENGTH'],
                            'HEIGHT' => $productData['HEIGHT'],
                        )
                    );
                    \CPrice::Add($productData['PRICE']);
                }
            }

        }
    }

    /**
     * generateData
     * Удаление тестовых данных для инфоблока
     *
     */

    public function deleteData($arParams = array())
    {
        $arSelect = array("ID");
        $arFilter = array("IBLOCK_ID" => $this->id, ">PROPERTY_INDI_FAKE_DATA" => 0);
        if (intval($arParams["SECTION_ID"]) > 0) {
            $arFilter["SECTION_ID"] = $arParams["SECTION_ID"];
        }
        $rsElements = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
        while ($arElement = $rsElements->GetNext()) {
            \CIBlockElement::Delete($arElement["ID"]);
        }

        // удаляем свойство "Тестовые данные"
        if (!intval($arParams["SECTION_ID"])) {
            $this->deleteFakeDataProperty();
        }
    }

    /**
     * deleteFakeDataProperty
     * Удаление свойства "Тестовые данные" для инфоблока
     */

    private function deleteFakeDataProperty()
    {
        $rsProp = \CIBlockProperty::GetList(
            array(),
            array("ACTIVE" => "Y", "IBLOCK_ID" => $this->id, "CODE" => "INDI_FAKE_DATA")
        );
        if ($arProp = $rsProp->GetNext()) {
            \CIBlockProperty::Delete($arProp["ID"]);
        }
    }

    /**
     * generateIblockPropValReference
     * Генерирует значения для свойства тип "Справочник"
     *
     * @param array $arParams
     *
     * массив параметров с ключами
     * <ul>
     *        <li>array PROPERTIES массив свойств, который возвращает метод getIblockProps</li>
     *        <li>array CONFIG конфиг генератора/li>
     *        <li> array PROP массив данных по свойству/li>
     * </ul>
     *
     * @return array | string
     */

    private function generateIblockPropValReference($arParams)
    {
        $config = $arParams["CONFIG"];
        $iblockProp = $arParams["PROP"];

        $rsHblocks = HighloadBlockTable::getList(
            array(
                "select" => array(
                    "ID",
                ),
                "filter" => array(
                    'TABLE_NAME' => $iblockProp['USER_TYPE_TABLE_NAME'],
                ),
            )
        );
        $arHlBlock = $rsHblocks->fetch();
        if (empty($arHlBlock)) {
            return false;
        }
        $hlblock = HighloadBlockTable::getById($arHlBlock['ID'])->fetch();
        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entityClass = $entity->getDataClass();
        if ($iblockProp['MULTIPLE'] == "Y") { // множественное
            $rsValue = $entityClass::getList(
                array(
                    'select' => array('ID', 'UF_XML_ID'),
                    'filter' => array('!UF_XML_ID' => false),
                    'limit' => $config['property_multiple_count'],
                    'order' => array('RAND' => 'ASC'),
                    'runtime' => array(
                        new ExpressionField('RAND', 'RAND()'),
                    ),
                )
            );
            $val = array();
            while ($arValue = $rsValue->fetch()) {
                if (!empty($arValue) && $arValue['UF_XML_ID']) {
                    $val[] = $arValue['UF_XML_ID'];
                }
            }
        }
        else {
            $rsValue = $entityClass::getList(
                array(
                    'select' => array('ID', 'UF_XML_ID'),
                    'filter' => array('!UF_XML_ID' => false),
                    'limit' => 1,
                    'order' => array('RAND' => 'ASC'),
                    'runtime' => array(
                        new ExpressionField('RAND', 'RAND()'),
                    ),
                )
            );
            $arValue = $rsValue->fetch();
            if (!empty($arValue) && $arValue['UF_XML_ID']) {
                $val = $arValue['UF_XML_ID'];
            }
        }

        return $val;
    }

    /**
     * generateIblockPropValTextHtml
     * Генерирует значения для свойства тип "Text/Html"
     *
     * @param array $arParams массив параметров с ключами
     * <ul>
     *        <li>array PROPERTIES массив свойств, который возвращает метод getIblockProps</li>
     *        <li>array CONFIG конфиг генератора</li>
     *        <li>array PROP массив данных по свойству/<li>
     *        <li>mixed FAKER объект класса генератора</li>
     * </ul>
     *
     * @return array | string
     */

    private function generateIblockPropValTextHtml($arParams)
    {
        $config = $arParams["CONFIG"];
        $faker = $arParams["FAKER"];
        $iblockProp = $arParams["PROP"];

        if ($iblockProp['MULTIPLE'] == "Y") { // множественное
            $val = array();
            for ($i = 0; $i < $config['property_multiple_count']; $i++) {
                $val[]['VALUE'] = array(
                    'TYPE' => 'html',
                    'TEXT' => Util::nl2p($faker->text($config['property_text_length'])),

                );
            }
        }
        else {
            $val['VALUE'] = array(
                'TYPE' => 'html',
                'TEXT' => Util::nl2p($faker->text($config['property_text_length']))
            );
        }

        return $val;
    }

    /**
     * generateIblockPropValDate
     * Генерирует значения для свойства тип "Дата"
     *
     * @param array $arParams массив параметров с ключами
     * <ul>
     *        <li>array PROPERTIES массив свойств, который возвращает метод getIblockProps</li>
     *        <li>array CONFIG конфиг генератора</li>
     *        <li>array PROP массив данных по свойству/<li>
     *        <li>mixed FAKER объект класса генератора</li>
     * </ul>
     *
     * @return array | string
     */

    private function generateIblockPropValDate($arParams)
    {
        $config = $arParams["CONFIG"];
        $faker = $arParams["FAKER"];
        $iblockProp = $arParams["PROP"];

        global $DB;
        $siteFormat = \CSite::GetDateFormat('SHORT');
        $phpFormat = $DB->DateFormatToPHP($siteFormat);

        if ($iblockProp['MULTIPLE'] == "Y") { // множественное
            $val = array();
            for ($i = 0; $i < $config['property_multiple_count']; $i++) {
                $val[] = $faker->date($phpFormat);
            }
        }
        else {
            $val = $faker->date($phpFormat);
        }

        return $val;
    }

    /**
     * generateIblockPropValDateTime
     * Генерирует значения для свойства тип "Дата со временем"
     *
     * @param array $arParams массив параметров с ключами
     * <ul>
     *        <li>array PROPERTIES массив свойств, который возвращает метод getIblockProps</li>
     *        <li>array CONFIG конфиг генератора</li>
     *        <li>array PROP массив данных по свойству/<li>
     *        <li>mixed FAKER объект класса генератора</li>
     * </ul>
     *
     * @return array | string
     */

    private function generateIblockPropValDateTime($arParams)
    {
        $config = $arParams["CONFIG"];
        $faker = $arParams["FAKER"];
        $iblockProp = $arParams["PROP"];

        global $DB;
        $siteFormat = \CSite::GetDateFormat('FULL');
        $phpFormat = $DB->DateFormatToPHP($siteFormat);

        if ($iblockProp['MULTIPLE'] == "Y") { // множественное
            $val = array();
            for ($i = 0; $i < $config['property_multiple_count']; $i++) {
                $val[] = $faker->date($phpFormat);
            }
        }
        else {
            $val = $faker->date($phpFormat);
        }

        return $val;
    }

    /**
     * generateIblockPropValString
     * Генерирует значения для свойства тип "Строка"
     *
     * @param array $arParams массив параметров с ключами
     * <ul>
     *        <li>array PROPERTIES массив свойств, который возвращает метод getIblockProps</li>
     *        <li>array CONFIG конфиг генератора</li>
     *        <li>array PROP массив данных по свойству/<li>
     *        <li>mixed FAKER объект класса генератора</li>
     * </ul>
     *
     * @return array | string
     */

    private function generateIblockPropValString($arParams)
    {
        $config = $arParams["CONFIG"];
        $faker = $arParams["FAKER"];
        $iblockProp = $arParams["PROP"];

        if ($iblockProp['MULTIPLE'] == "Y") { // множественное
            $val = array();
            for ($i = 0; $i < $config['property_multiple_count']; $i++) {
                $val[] = $faker->sentence($config['property_string_length']);
            }
        }
        else {
            $val = $faker->sentence($config['property_string_length']);
        }

        return $val;
    }

    /**
     * generateIblockPropValList
     * Генерирует значения для свойства тип "Список"
     *
     * @param array $arParams массив параметров с ключами
     * <ul>
     *        <li>array PROPERTIES массив свойств, который возвращает метод getIblockProps</li>
     *        <li>array CONFIG конфиг генератора</li>
     *        <li>array PROP массив данных по свойству/<li>
     * </ul>
     *
     * @return array | string
     */

    private function generateIblockPropValList($arParams)
    {
        $config = $arParams["CONFIG"];
        $iblockProp = $arParams["PROP"];

        // получаем варианты значения
        $rsPropVals = \CIBlockPropertyEnum::GetList(
            array("SORT" => "DESC"),
            array("IBLOCK_ID" => $this->id, "CODE" => $iblockProp["CODE"])
        );
        $arPropValIds = array(0 => 'false');
        while ($arPropVal = $rsPropVals->GetNext()) {
            $arPropValIds[] = $arPropVal['ID'];
        }
        if ($iblockProp['MULTIPLE'] == "Y") { // множественное
            $randKeys = array_rand($arPropValIds, $config['property_multiple_count']);
            $val = array();
            foreach ($randKeys as $index => $randKey) {
                $val[$index] = $arPropValIds[$randKey];
            }

        }
        else {
            $randKey = array_rand($arPropValIds);
            $val = $arPropValIds[$randKey];
        }

        return $val;
    }

    /**
     * generateIblockPropValNumber
     * Генерирует значения для свойства тип "Число"
     *
     * @param array $arParams массив параметров с ключами
     * <ul>
     *        <li>array PROPERTIES массив свойств, который возвращает метод getIblockProps</li>
     *        <li>array CONFIG конфиг генератора</li>
     *        <li>array PROP массив данных по свойству/<li>
     *        <li>mixed FAKER объект класса генератора</li>
     * </ul>
     *
     * @return array | string
     */

    private function generateIblockPropValNumber($arParams)
    {
        $config = $arParams["CONFIG"];
        $faker = $arParams["FAKER"];
        $iblockProp = $arParams["PROP"];

        if ($iblockProp['MULTIPLE'] == "Y") { // множественное
            $val = array();
            for ($i = 0; $i < $config['property_multiple_count']; $i++) {
                $val[] = $faker->randomDigit();
            }
        }
        else {
            $val = $faker->randomDigit();
        }

        return $val;
    }

    /**
     * generateIblockPropValFile
     * Генерирует значения для свойства тип "Файл"
     *
     * @param array $arParams массив параметров с ключами
     * <ul>
     *        <li>array PROPERTIES массив свойств, который возвращает метод getIblockProps</li>
     *        <li>array CONFIG конфиг генератора</li>
     *        <li>array PROP массив данных по свойству/<li>
     *        <li>mixed FAKER объект класса генератора</li>
     * </ul>
     * 
     * @return array|string
     */

    private function generateIblockPropValFile($arParams)
    {
        $config = $arParams["CONFIG"];
        $iblockProp = $arParams["PROP"];

        if ($iblockProp['MULTIPLE'] == "Y") { // множественное
            $val = array();
            for ($i = 0; $i < $config['property_multiple_count']; $i++) {
                $val[] = \CFile::MakeFileArray(Util::getRandomImage());
            }
        }
        else {
            $val = \CFile::MakeFileArray(Util::getRandomImage());
        }

        return $val;
    }

    /**
     * generateIblockPropValLink
     * Генерирует значения для свойства тип "Привязка к элементу"
     *
     * @param array $arParams массив параметров с ключами
     * <ul>
     *        <li>array PROPERTIES массив свойств, который возвращает метод getIblockProps</li>
     *        <li>array CONFIG конфиг генератора</li>
     *        <li>array PROP массив данных по свойству/<li>
     *        <li>mixed FAKER объект класса генератора</li>
     * </ul>
     *
     * @return array | string
     */

    private function generateIblockPropValLink($arParams)
    {
        $config = $arParams["CONFIG"];
        $iblockProp = $arParams["PROP"];

        if ($iblockProp['IBLOCK_ID']) {
            if ($iblockProp['MULTIPLE'] == "Y") { // множественное
                $val = array();
                $arSelect = array("ID");
                $arFilter = array("IBLOCK_ID" => $iblockProp['IBLOCK_ID'], "ACTIVE" => "Y");
                $rsElements = \CIBlockElement::GetList(array("RAND" => "ASC"), $arFilter, false, array("nTopCount" => $config['property_multiple_count']), $arSelect);
                while ($arElement = $rsElements->GetNext()) {
                    $val[] = $arElement['ID'];
                }
            }
            else {
                $arSelect = array("ID");
                $arFilter = array("IBLOCK_ID" => $iblockProp['IBLOCK_ID'], "ACTIVE" => "Y");
                $rsElements = \CIBlockElement::GetList(array("RAND" => "ASC"), $arFilter, false, array("nTopCount" => 1), $arSelect);
                $arElement = $rsElements->GetNext();
                $val = $arElement['ID'];
            }
        }

        return $val;
    }

    /**
     * generateIblockPropsVals
     *
     * @param array $arParams массив параметров с ключами
     * <ul>
     *        <li>array PROPERTIES массив свойств, который возвращает метод getIblockProps</li>
     *        <li>array CONFIG конфиг генератора</li>
     *        <li>array PROP массив данных по свойству/<li>
     *        <li>mixed FAKER объект класса генератора</li>
     * </ul>
     *
     * @return array
     */
    public function generateIblockPropsVals($arParams)
    {
        $iblockProps = $arParams["PROPERTIES"];
        $faker = $arParams["FAKER"];
        $config = $arParams["CONFIG"];
        $resultProps = array();
        foreach ($iblockProps as $iblockProp) {
            switch ($iblockProp['PROPERTY_TYPE']) {
                case "S": { // строковое свойство
                    if ($iblockProp['USER_TYPE'] == 'directory' && Loader::includeModule('highloadblock')) { // справочник
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValReference(array('PROP' => $iblockProp, 'CONFIG' => $config));
                    }
                    elseif ($iblockProp['USER_TYPE'] == 'HTML') { // текст / html
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValTextHtml(array('PROP' => $iblockProp, 'CONFIG' => $config, 'FAKER' => $faker));
                    }
                    elseif ($iblockProp['USER_TYPE'] == 'Date') { // Дата
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValDate(array('PROP' => $iblockProp, 'CONFIG' => $config, 'FAKER' => $faker));
                    }
                    elseif ($iblockProp['USER_TYPE'] == 'DateTime') { // Дата-Время
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValDateTime(array('PROP' => $iblockProp, 'CONFIG' => $config, 'FAKER' => $faker));
                    }
                    else { // обычная строка
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValString(array('PROP' => $iblockProp, 'CONFIG' => $config, 'FAKER' => $faker));
                    }
                    break;
                }
                case "L": { // список
                    if ($iblockProp['CODE'] != 'INDI_FAKE_DATA') {
                        $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValList(array('PROP' => $iblockProp, 'CONFIG' => $config));
                    }
                    break;
                }
                case "N": { // число
                    $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValNumber(array('PROP' => $iblockProp, 'CONFIG' => $config, 'FAKER' => $faker));
                    break;
                }
                case "F": { // файл
                    $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValFile(array('PROP' => $iblockProp, 'CONFIG' => $config));
                    break;
                }
                case "E": { // привязка к элементу
                    $resultProps[$iblockProp['CODE']] = $this->generateIblockPropValLink(array('PROP' => $iblockProp, 'CONFIG' => $config));
                    break;
                }
            }
        }
        $resultProps = (array)$resultProps;

        return $resultProps;
    }

}