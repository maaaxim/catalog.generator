<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
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

// @TODO methods to prop
class ProductProperty extends Property implements Generateable
{
    const ORDER = 2;

    const TYPE_REFERENCE = "REF";

    protected $iblockId;

    private $name;
    private $code;

    public function __construct()
    {
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
        $type = "E";

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

    protected function setIblockId()
    {
        $iblockRes = IblockTable::getList([
            "filter" => ["CODE" => "catalog_aero_generator_0"],
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

    // @TODO Refactor
    protected function generateElementLink()
    {
        // make helper type if not exist
        $typeId = $iblockCode = "aero_generator_helper";
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

        // cache
        global $CACHE_MANAGER;
        $CACHE_MANAGER->CleanAll();

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

        // and prop
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
            'NAME' => "list prop name",
            'CODE' => "LIST_CODE",
            'MULTIPLE' => 'Y',
            'ACTIVE' => 'Y',
            "IBLOCK_ID" => $this->iblockId
        );

        $propId = $this->addProperty($propertyDescription);

        // @TODO order it
        for($i = 0; $i <= 10; $i++){
            $enumId = PropertyEnumerationTable::add([
                "PROPERTY_ID" => $propId,
                "VALUE" => "VALUE " . $i,
                "XML_ID" => "XML_ID_" . $i
            ]);
            if($enumId <= 0)
                throw new \Exception("Prop enum is not set");
        }

        return $propId;
    }

    protected function generateReference()
    {
        if(!Loader::includeModule("highloadblock"))
            throw new \Exception("hl iblock is not defined");

        $name = "entity"; // @TODO faker

        $hlIblockId = $this->addHlBlock($name);

        $arUserFields = $this->getDefaultHlFields($hlIblockId);
        $this->addUserFields($arUserFields);

        // Create iblock prop
        $propertyDescription = array(
            'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
            'NAME' => "ref prop name",
            'CODE' => "REF_CODE_REF",
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
                    "TABLE_NAME" => "b_aero_generator_" . $name
                )
            )
        );
        return $this->addProperty($propertyDescription);
    }

    private function generatePropertyFields()
    {
        $faker = Factory::create('ru_RU');
        $sentence = $faker->sentence(rand(1, 3));
        $this->name = substr($sentence, 0, strlen($sentence) - 1);
        $noSpaced = str_replace(' ', '_', $this->name);
        $this->code = strtoupper($noSpaced);
    }

    // mk hl-block
    private function addHlBlock($name)
    {
        $result = HighloadBlockTable::add(array(
            'NAME' => 'AeroGenerator' . ucfirst($name),
            'TABLE_NAME' => "b_aero_generator_" . $name,
        ));
        if (!$result->isSuccess()) {
            throw new \Exception(implode(" ", $result->getErrorMessages()));
        } else {
            $hlIblockId = $result->getId();
        }
        return $hlIblockId;
    }

    // adding user fields
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
}