<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 29.08.2017
 * Time: 0:15
 */

namespace Aero\Generator;

use Aero\Generator\Entity\GeneratorTable;

/**
 * Class Plan
 * responsible for making plan
 * (computing step size, writing to the db)
 *
 * @package Aero\Generator
 */
class Plan
{
    /**
     * @var array things we can generate immediately
     */
    protected static $steps = [
        0 => "Catalog",
        1 => "ProductProperty",
        2 => "SkuProperty",
        3 => "Price",
        4 => "Store",
        5 => "Plan"
    ];

    private $plan;

    public function __construct()
    {
        $this->plan = [];
    }

    public function initStructurePlan()
    {
        $this->fillPlan();
        $this->writePlan();
    }

    public function initProductsPlan()
    {

    }

    private function fillPlan()
    {
        foreach(self::$steps as $key => $name){
            $class = '\\Aero\\Generator\\Types\\' . $name;
            $object = new $class();
            if(!method_exists($class,'getStepSize'))
                throw new \Exception("Method getStepSize is not exist in $class class");
            $this->plan[$class] = [
                "count" => $object->getStepSize()
            ];
        }
    }

    private function writePlan()
    {
        // Make step for each entity and type of entity
        $iterator = 0;
        foreach ($this->plan as $key => $item) {
            $max = (int)$item["count"];
            if ($max <= 0)
                throw new \Exception("Must have more than 0 entity");
            $iterator++;
            $data = [
                "STEP" => $iterator,
                "STATUS" => 0,
                "TYPE" => $key,
                "ITEMS_PER_STEP" => $max
            ];
            GeneratorTable::add($data);
        }
    }
}