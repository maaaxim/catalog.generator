<?php

namespace Catalog\Generator;

use Catalog\Generator\Types\Plan as PlanType;
use Catalog\Generator\Entity\GeneratorTable;
use Catalog\Generator\Types\Generateable;
use Bitrix\Main\Entity\ExpressionField;

/**
 * Class Steps
 * responsible for making steps according to plan from db
 *
 * @package Catalog\Generator
 */
class Steps
{
    protected $plan;
    protected $step;
    protected $stepSize;
    protected $stepCount;

    protected $type;
    protected $id;
    protected $errors;

    public function __construct(){
        $this->setCount();
        $this->cleanSteps();
    }

    /**
     * Makes a generation step.
     * Returns count of completed steps.
     * Returns 0 if there is nothing to do.
     *
     * @return int
     * @throws Exception
     */
    public function createNext(){
        try {
            if ($this->initStep()) {
                for($i = 0; $i < $this->stepSize; $i++)
                    $this->type->generate();
                $this->finish();
            } else {
                return 0;
            }
        } catch (\Exception $exception) {
            $this->errors[] = $exception;
            // $exception->getMessage()
            return 0;
        }
        return $this->stepSize;
    }

    /**
     * Returns total steps count
     *
     * @return int
     */
    public function getCount(){
        return $this->stepCount;
    }

    /**
     * Returns current step
     *
     * @return int
     */
    public function getCurrent(){
        return $this->step;
    }

    /**
     * Initializes fields for step
     *
     * @return bool
     */
    private function initStep():bool {
        // get last non-updated
        // or get last updated
        // or get null
        $stepRes = GeneratorTable::getList([
            "order" => ["STATUS" => "ASC", "ID" => "ASC"],
            "select" => ["ID", "STEP", "TYPE", "STATUS", "ITEMS_PER_STEP"],
            "limit" => 1
        ]);
        $lastItem = $stepRes->fetch();

        // пусто - задаём всю структуру
        // и сразу создаём первый товар
        echo "<pre>"; var_dump($lastItem); echo "</pre>";
        if($lastItem["STATUS"] == 1){
            // echo "finished";
            return false;
        } else {

            $steps = Plan::getSteps();

            // если в бд сущность не из структуры - генерим её
            if (!in_array($lastItem["TYPE"], $steps) && !empty($lastItem["TYPE"])) {
                // echo "gen product";
                $this->step     = (int) $lastItem["STEP"];
                $this->id       = (int) $lastItem["ID"];
                $this->stepSize = (int) $lastItem["ITEMS_PER_STEP"];
                $this->type     = $this->createGenerateable($lastItem["TYPE"]);

            // если все шаги пройдены - отвечаем 0
            // } elseif {
            // если таблица пуста - создаём план
            } else {

                // echo "make plan";
                $catalog = new \Catalog\Generator\Types\Catalog();
                $catalog->generate();

                $productProperty = new \Catalog\Generator\Types\ProductProperty();
                $productProperty->generate();

                // @TODO учесть, что не всегда нужен
                //$skuProperty = new \Catalog\Generator\Types\SkuProperty(); // if needed
                //$skuProperty->generate();

                $price = new \Catalog\Generator\Types\Price();
                $price->generate();

                $store = new \Catalog\Generator\Types\Store();
                $store->generate();

                // add test product
                $plan = new Plan();
                $plan->initProductsPlan();

                // and start
                $this->initStep();
            }
        }
        return true;
    }

    /**
     * @param string $type
     * @return Generateable
     */
    private function createGenerateable(string $type):Generateable {
        if(!class_exists($type))
            throw new \InvalidArgumentException("$type is not a valid type!");
        return new $type();
    }

    /**
     * Update step in db
     *
     * @throws Exception
     */
    private function finish(){
        $result = GeneratorTable::update($this->id, [
            "STATUS" => 1,
        ]);
        if (!$result->isSuccess()){
            throw new Exception($result->getErrorMessages());
        }
    }

    /**
     * Clean table
     */
    private function cleanSteps(){
        $stepRes = GeneratorTable::getList([
            "select" => ["ID"]
        ]);
        while($stepFields = $stepRes->fetch())
            GeneratorTable::delete($stepFields["ID"]);
    }

    /**
     * Returns total steps quantity
     *
     * @return int
     */
    private function setCount(){
        $cntRes = GeneratorTable::getList([
            'select' => ['CNT'],
            'runtime' => [
                new ExpressionField('CNT', 'COUNT(*)')
            ]
        ]);
        $result = $cntRes->fetch();
        $this->stepCount = (int) $result["CNT"];
        if($this->stepCount == 0)
            $this->stepCount = sizeof(Plan::getSteps());
    }
}