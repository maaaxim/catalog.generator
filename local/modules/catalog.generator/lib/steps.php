<?php

namespace Catalog\Generator;

use Catalog\Generator\Entity\GeneratorTable;
use Catalog\Generator\Types\Generateable;
use Bitrix\Main\Entity\ExpressionField;
use Catalog\Generator\Types\Product;

/**
 * Class Steps
 * responsible for making steps according to plan from db
 *
 * @package Catalog\Generator
 */
class Steps
{
    protected $step;
    protected $stepSize;
    protected $stepCount;

    protected $type;
    protected $id;
    protected $errors;

    public function __construct(){
        $this->setCount();
        // $this->cleanSteps(); die();
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

        $stepRes = GeneratorTable::getList([
            "order" => ["STATUS" => "ASC", "ID" => "ASC"],
            "select" => ["ID", "STEP", "STATUS", "ITEMS_PER_STEP"],
            "limit" => 1
        ]);

        $lastItem = $stepRes->fetch();

        // Последняя запись со статусом 1 - выгрузка завершена
        if($lastItem["STATUS"] == 1){
            return false;
        } else {

            // если таблица пуста - создаём план
            if ($lastItem == false) {

                // $steps = Plan::getSteps();

                // echo "make plan";
                $catalog = new \Catalog\Generator\Types\Catalog();
                $catalog->generate();

                $productProperty = new \Catalog\Generator\Types\ProductProperty();
                $productProperty->generate();

                $skuProperty = new \Catalog\Generator\Types\SkuProperty();
                $skuProperty->generate();

                $price = new \Catalog\Generator\Types\Price();
                $price->generate();

                $store = new \Catalog\Generator\Types\Store();
                $store->generate();

                // add test product
                $plan = new Plan();
                $plan->initProductsPlan();

                // and start
                $this->setCount();
                $this->initStep();

            } else {

                // echo "gen product";
                $this->step     = (int) $lastItem["STEP"];
                $this->id       = (int) $lastItem["ID"];
                $this->stepSize = (int) $lastItem["ITEMS_PER_STEP"];
                $this->type     = new Product();
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
    }
}