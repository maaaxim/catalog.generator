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
     * @var array things we can generate immediately
     * @TODO to class Structure
     */
    protected static $steps = [
        0 => "Catalog",
        1 => "ProductProperty",
        2 => "SkuProperty",
        3 => "Price",
        4 => "Store",
        5 => "Section"
    ];

    private $plan;

    public function __construct()
    {
        $this->plan = [];
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

        for($i = 1; $i < $stepsCount + 1; $i++){
            $data = [
                "STEP" => $i,
                "STATUS" => 0,
                "ITEMS_PER_STEP" => $itemsPerStep
            ];
            GeneratorTable::add($data);
            $lastStep = $i;
        }
        if($remainder > 0){
            $data = [
                "STEP" => $lastStep + 1,
                "STATUS" => 0,
                "ITEMS_PER_STEP" => $remainder
            ];
            GeneratorTable::add($data);
        }
    }

    /**
     * @return float
     */
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