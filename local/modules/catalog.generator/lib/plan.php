<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 29.08.2017
 * Time: 0:15
 */

namespace Catalog\Generator;

use Catalog\Generator\Entity\GeneratorTable;
use Catalog\Generator\Types\Product;
use Bitrix\Main\Config\Option;

/**
 * Class Plan
 * responsible for making plan
 * (computing step size, writing to the db)
 *
 * @package Catalog\Generator
 */
class Plan
{
    /**
     * @var
     */
    private $structureCreated;

    /**
     * @var array things we can generate immediately
     */
    protected static $steps = [
        0 => "Plan",
        1 => "Catalog",
        2 => "ProductProperty",
        3 => "SkuProperty",
        4 => "Price",
        5 => "Store",
        6 => "Plan"
    ];

    private $plan;

    public function __construct()
    {
        $this->setStructureCreated();
        $this->plan = [];
    }

    /**
     * @return bool
     */
    public function getStructureCreated():bool
    {
        return $this->structureCreated;
    }

    /**
     * Check structure exist and set field $structureCreated
     */
    private function setStructureCreated()
    {
        $tableRes = GeneratorTable::getList(["limit" => 1]);
        $this->structureCreated = ($tableRes->fetch()) ? true : false;
    }

    /**
     * Calc step size for product and write it to the plan
     * refactor @TODO
     */
    public function initProductsPlan()
    {
        $timeElapsed = $this->getTimeElapsed();
        $itemsPerStep = ceil(5 / $timeElapsed);
        $productsNeeded = Option::get("catalog.generator", "types_product");
        $stepsCount = floor($productsNeeded / $itemsPerStep);
        $remainder = $productsNeeded % $itemsPerStep;
        for($i = 0; $i < $stepsCount; $i++){
            $data = [
                "STEP" => 7 + $i,
                "STATUS" => 0,
                "TYPE" => '\Catalog\Generator\Types\Product',
                "ITEMS_PER_STEP" => $itemsPerStep
            ];
            GeneratorTable::add($data);
            $lastStep = $i;
        }
        if($remainder > 0){
            $data = [
                "STEP" => 7 + $lastStep + 1,
                "STATUS" => 0,
                "TYPE" => '\Catalog\Generator\Types\Product',
                "ITEMS_PER_STEP" => $remainder
            ];
            GeneratorTable::add($data);
        }
    }

    private function getTimeElapsed():float
    {
        $start = microtime(true);
        $product = new Product();
        $product->generate();
        $timeElapsed = microtime(true) - $start;
        $product->remove();
        return $timeElapsed;
    }

    /**
     * @return array
     */
    public static function getSteps():array
    {
        $steps = [];
        foreach(self::$steps as $key => $name){
            $class = '\\Catalog\\Generator\\Types\\' . $name;
            $steps[] = $class;
        }
        return $steps;
    }
}