<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 02.09.2017
 * Time: 13:57
 */

namespace Aero\Generator\Types;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Faker\Factory;
use Bitrix\Iblock\TypeTable;
use Bitrix\Iblock\TypeLanguageTable;

abstract class Property
{
    /**
     * Variant for HL-type
     */
    const TYPE_REFERENCE = "REF";

    /**
     * Max enumeration count each property
     */
    const MAX_ENUM_COUNT = 10;

    /**
     * Max entity count each property
     */
    const MAX_ENTITY_COUNT = 10;

    /**
     * @var iblock id
     */
    protected $iblockId;

    /**
     * @var property name
     */
    private $name;

    /**
     * @var property code
     */
    private $code;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create('ru_RU');
        $this->setIblockId();
        $this->generatePropertyFields();
    }

    function generate()
    {
        // set types
        $types = [
            PropertyTable::TYPE_STRING,
            PropertyTable::TYPE_NUMBER,
            PropertyTable::TYPE_ELEMENT,
            PropertyTable::TYPE_LIST,
            static::TYPE_REFERENCE
        ];

        // choose random type
        $type = $types[rand(0, sizeof($types) - 1)];

        // gen prop
        switch($type) {
            // str
            case PropertyTable::TYPE_STRING:
                $this->generateString();
                break;
            // num
            case PropertyTable::TYPE_NUMBER:
                $this->generateNumeric();
                break;
            // element link
            case PropertyTable::TYPE_ELEMENT:
                $this->generateElementLink();
                break;
            // list
            case PropertyTable::TYPE_LIST:
                $this->generateList();
                break;
            // reference
            case static::TYPE_REFERENCE:
                $this->generateReference();
                break;
        }

        die();
    }

    /**
     * @param $parameters
     * @return int
     * @throws \Exception
     */
    private function addProperty($parameters)
    {
        $propertyResult = PropertyTable::add($parameters);
        if ($propertyResult->isSuccess()) {
            $id = (int) $propertyResult->getId();
        } else {
            throw new \Exception($propertyResult->getErrorMessages());
        }
        return $id;
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
        if($iblockFields = $iblockRes->fetch()){
            $this->iblockId = (int) $iblockFields["ID"];
        } else {
            throw new \Exception("Iblock is not created!");
        }
    }

    protected function generateString()
    {
        $propertyDescription = array(
            'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
            'USER_TYPE' => null,
            'NAME' => $this->name,
            'CODE' => "STRING_" . $this->code,
            'MULTIPLE' => 'N',
            'ACTIVE' => 'Y',
            "IBLOCK_ID" => $this->iblockId
        );
        return $this->addProperty($propertyDescription);
    }

    protected function generateNumeric()
    {
        $propertyDescription = array(
            'PROPERTY_TYPE' => PropertyTable::TYPE_NUMBER,
            'USER_TYPE' => null,
            'NAME' => $this->name,
            'CODE' => $this->code,
            'MULTIPLE' => 'N',
            'ACTIVE' => 'Y',
            "IBLOCK_ID" => $this->iblockId
        );
        return $this->addProperty($propertyDescription);
    }

    protected function generateElementLink()
    {
        $typeId = $iblockCode = $this->generateHelperType();

        global $CACHE_MANAGER;
        $CACHE_MANAGER->CleanAll();

        $iblockId = $this->generateHelperIblock($typeId, $iblockCode);

        $propertyDescription = array(
            'PROPERTY_TYPE' => PropertyTable::TYPE_ELEMENT,
            'USER_TYPE' => null,
            'NAME' => $this->name,
            'CODE' => $this->code,
            'MULTIPLE' => 'N',
            'ACTIVE' => 'Y',
            "LINK_IBLOCK_ID" => $iblockId,
            "IBLOCK_ID" => $this->iblockId
        );
        return $this->addProperty($propertyDescription);
    }

    protected function generateList()
    {
        $propertyDescription = array(
            'PROPERTY_TYPE' => PropertyTable::TYPE_LIST,
            'USER_TYPE' => null,
            'NAME' => $this->name,
            'CODE' => $this->code,
            'MULTIPLE' => 'Y',
            'ACTIVE' => 'Y',
            "IBLOCK_ID" => $this->iblockId
        );

        $propId = $this->addProperty($propertyDescription);
        $this->generateEnums($propId);

        return $propId;
    }

    private function generateEnums($propId)
    {
        for($i = 0; $i <= self::MAX_ENUM_COUNT; $i++){
            $sentence = $this->faker->sentence(rand(1, 3));
            $value = substr($sentence, 0, strlen($sentence) - 1);
            $noSpaced = str_replace(' ', '_', $value);
            $xmlId = strtoupper($noSpaced);
            $enumId = PropertyEnumerationTable::add([
                "PROPERTY_ID" => $propId,
                "VALUE" => $value,
                "XML_ID" => $xmlId
            ]);
            if($enumId <= 0)
                throw new \Exception("Prop enum is not set");
        }
    }

    protected function generateReference()
    {
        if(!Loader::includeModule("highloadblock"))
            throw new \Exception("hl iblock is not defined");

        $convertToCamelCase = function ($sentence) {
            $ucSentence = ucwords($sentence);
            return str_replace(" ", "", $ucSentence);
        };

        // Names for hl
        $tableName = strtolower($this->code);
        $entityName = $convertToCamelCase($this->name);

        // Add hl
        $hlIblockId = $this->addHlBlock($entityName, $tableName);
        if($hlIblockId <= 0)
            throw new \Exception("Can't create hl-iblock");

        // Add hl fields
        $arUserFields = $this->getDefaultHlFields($hlIblockId);
        $this->addUserFields($arUserFields);

        // Create iblock prop linked to hl
        $propertyDescription = array(
            'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
            'NAME' => $this->name,
            'CODE' => $this->code,
            'ACTIVE' => 'Y',
            "IBLOCK_ID" => $this->iblockId,
            "USER_TYPE" => "directory",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "Y",
            "USER_TYPE_SETTINGS" => serialize(
                array(
                    "size" => "1",
                    "width" => "0",
                    "group" => "N",
                    "multiple" => "N",
                    "TABLE_NAME" => "b_aero_generator_" . $tableName
                )
            )
        );

        $propId = $this->addProperty($propertyDescription);
        if($propId <= 0)
            throw new \Exception("Can't create property");

        $this->generateEntityItems($hlIblockId);

        return $propId;
    }

    /**
     * Create few hl data
     *
     * @param $hlIblockId
     * @throws \Exception
     */
    private function generateEntityItems($hlIblockId)
    {
        $hlBlock = HighloadBlockTable::getById($hlIblockId)->fetch();
        $entity = HighloadBlockTable::compileEntity($hlBlock);
        $entityClass = $entity->getDataClass();

        if(!class_exists($entityClass))
            throw new \Exception($entityClass . " class is not exist");

        for($i = 0; $i <= self::MAX_ENTITY_COUNT; $i++){
            $sentence = $this->faker->sentence(1,3);
            $sentence = substr($sentence, 0, strlen($sentence) - 1);
            $withUnderscores = str_replace(" ", "_", $sentence);
            $bitrixStyle = strtoupper($withUnderscores);
            $entityClass::add([
                "UF_NAME" => $sentence,
                "UF_LINK" => $withUnderscores,
                "UF_XML_ID" => $bitrixStyle
            ]);
        }
    }

    private function generatePropertyFields()
    {
        $sentence = $this->faker->sentence(rand(1, 3));
        $this->name = substr($sentence, 0, strlen($sentence) - 1);
        $noSpaced = str_replace(' ', '_', $this->name);
        $this->code = strtoupper($noSpaced);
    }

    /**
     * creates hl-block
     *
     * @param $entityPostfix
     * @param $tablePostfix
     * @return int
     * @throws \Exception
     */
    private function addHlBlock($entityPostfix, $tablePostfix)
    {
        $result = HighloadBlockTable::add(array(
            'NAME' => 'AeroGenerator' . $entityPostfix,
            'TABLE_NAME' => "b_aero_generator_" . $tablePostfix,
        ));
        if (!$result->isSuccess()) {
            throw new \Exception(implode(" ", $result->getErrorMessages()));
        } else {
            $hlIblockId = $result->getId();
        }
        return $hlIblockId;
    }

    /**
     * adding user fields for hl-block
     *
     * @param $arUserFields
     * @throws \Exception
     */
    private function addUserFields($arUserFields)
    {
        $obUserField  = new \CUserTypeEntity;
        foreach ($arUserFields as $arFields) {
            $dbRes = \CUserTypeEntity::GetList(
                Array(),
                Array("ENTITY_ID" => $arFields["ENTITY_ID"], "FIELD_NAME" => $arFields["FIELD_NAME"])
            );
            if ($dbRes->Fetch())
                continue;
            $userFieldId = $obUserField->Add($arFields);
            if($userFieldId <= 0)
                throw new \Exception("Can't create user type props");
        }
    }

    private function getDefaultHlFields($hlIblockId)
    {
        return array (
            array (
                'ENTITY_ID' => 'HLBLOCK_'.$hlIblockId,
                'FIELD_NAME' => 'UF_NAME',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_COLOR2_NAME',
                'SORT' => '100',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'Y',
            ),
            array (
                'ENTITY_ID' => 'HLBLOCK_'.$hlIblockId,
                'FIELD_NAME' => 'UF_FILE',
                'USER_TYPE_ID' => 'file',
                'XML_ID' => 'UF_COLOR2_FILE',
                'SORT' => '200',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'Y',
            ),
            array (
                'ENTITY_ID' => 'HLBLOCK_'.$hlIblockId,
                'FIELD_NAME' => 'UF_LINK',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_COLOR2_LINK',
                'SORT' => '300',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'Y',
            ),
            array (
                'ENTITY_ID' => 'HLBLOCK_'.$hlIblockId,
                'FIELD_NAME' => 'UF_SORT',
                'USER_TYPE_ID' => 'double',
                'XML_ID' => 'UF_COLOR2_SORT',
                'SORT' => '400',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
            ),
            array (
                'ENTITY_ID' => 'HLBLOCK_'.$hlIblockId,
                'FIELD_NAME' => 'UF_DEF',
                'USER_TYPE_ID' => 'boolean',
                'XML_ID' => 'UF_COLOR2_DEF',
                'SORT' => '500',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
            ),
            array (
                'ENTITY_ID' => 'HLBLOCK_'.$hlIblockId,
                'FIELD_NAME' => 'UF_XML_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_XML_ID',
                'SORT' => '600',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
            )
        );
    }

    /**
     * Creates iblock type for helper iblock
     *
     * @return int
     */
    public function generateHelperType()
    {
        // make helper type if not exist
        $typeId = "aero_generator_helper";
        $typesRes = TypeTable::getList(["filter" => ["ID" => $typeId]]);
        if(!$typeFields = $typesRes->fetch()){
            TypeTable::add(["ID" => $typeId]);
            TypeLanguageTable::add([
                "IBLOCK_TYPE_ID" => $typeId,
                "LANGUAGE_ID" => "en",
                "NAME" => "AERO Generator helpers",
                "SECTIONS_NAME" => "Section",
                "ELEMENTS_NAME" => "Element"
            ]);
        }

        return $typeId;
    }

    /**
     * Creates helper iblock
     *
     * @param $typeId
     * @param $iblockCode
     * @return int
     * @throws \Exception
     */
    public function generateHelperIblock($typeId, $iblockCode)
    {
        // make iblock helper if not exist
        $iblockRes = IblockTable::getList([
            "filter" => ["CODE" => $iblockCode],
            "select" => ["ID"],
            "limit" => 1
        ]);
        if(!$iblockFields = $iblockRes->Fetch()){
            $ib = new \CIBlock;
            $arFields = [
                "ACTIVE" => "Y",
                "NAME" => "AERO Generator helper",
                "CODE" => $iblockCode,
                "IBLOCK_TYPE_ID" => $typeId,
                "SITE_ID" => ["s1"], // @TODO get from existing site
                "SORT" => 500
            ];
            $iblockId = $ib->Add($arFields);
            if($iblockId <= 0)
                throw new \Exception($ib->LAST_ERROR . " error happened!");
        } else {
            $iblockId = (int) $iblockFields["ID"];
        }

        return $iblockId;
    }
}