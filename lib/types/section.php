<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 18.11.2017
 * Time: 10:16
 */

namespace Catalog\Generator\Types;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Type\Date;
use Exception;
use Faker\Factory;

/**
 * Class Section
 * @package Catalog\Generator\Types
 */
class Section implements Generateable
{
    /**
     * @var int Number of first level sections
     */
    protected static $firstLevel = 8;

    /**
     * @var int Number of subsections
     */
    protected static $subsections = 64;

    /**
     * @var
     */
    protected $iblockId;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Iblock code for product
     */
    const IBLOCK_CODE = "catalog_catalog_generator";

    /**
     * Section constructor.
     */
    public function __construct()
    {
        $this->setIblockId();
        $this->faker = Factory::create('ru_RU');
    }

    /**
     * Generate
     */
    public function generate()
    {
        $parentSections = $this->getAllParentSections();
        if(
            sizeof($parentSections) > (self::$firstLevel - 1)
            && is_array($parentSections)
        ){
            $rndKey = array_rand($parentSections, 1);
            $sectionId = $parentSections[$rndKey];
            $this->createSecondLevelItem($sectionId);
        } else {
            $this->createFirstLevelItem();
        }
    }

    /**
     * @return array returns all 1lvl sections
     */
    protected function getAllParentSections():array
    {
        $parentSections = [];
        $secRes = SectionTable::getList([
            "filter" => [
                "IBLOCK_ID" => $this->iblockId,
                "DEPTH_LEVEL" => 1
            ],
            "select" => ["ID"]
        ]);
        while($parentSectionFields = $secRes->fetch())
            $parentSections[] = (int) $parentSectionFields["ID"];
        return $parentSections;
    }

    /**
     * Creates first level item
     */
    protected function createFirstLevelItem()
    {
        SectionTable::add([
            "NAME" => $this->createName(),
            "IBLOCK_ID" => $this->iblockId,
            "TIMESTAMP_X" => new Date(),
            "DEPTH_LEVEL" => 1
        ]);
    }

    /**
     * Creates subsection
     *
     * @param $sectionId
     */
    protected function createSecondLevelItem($sectionId)
    {
        SectionTable::add([
            "NAME" => $this->createName(),
            "IBLOCK_ID" => $this->iblockId,
            "TIMESTAMP_X" => new Date(),
            "DEPTH_LEVEL" => 2,
            "IBLOCK_SECTION_ID" => $sectionId
        ]);
    }

    /**
     * @return int
     */
    public function getCountToGenerate()
    {
        return self::$firstLevel + self::$subsections;
    }

    /**
     * Set iblock id
     * @TODO DRY Catalog\Generator\Types\Product setIblockId
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
            throw new Exception("Iblock is not created!");
        }
    }

    /**
     * @return string Creates name without dot in the end
     */
    protected function createName():string
    {
        $sentence = $this->faker->sentence(rand(1, 3));
        $name = substr($sentence, 0, strlen($sentence) - 1);
        return $name;
    }
}