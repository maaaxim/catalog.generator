<?php

namespace Aero\Generator;

use Aero\Generator\Entity\GeneratorTable;
use Aero\Generator\Types\Generateable;
use Bitrix\Main\Entity\ExpressionField;

/**
 * Class Steps
 * responsible for making steps according to plan from db
 *
 * @package Aero\Generator
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

        $this->setCountFromDb();

        // $this->cleanSteps();
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
        $steps = Plan::getSteps();
        // get last non-updated
        // or get last updated
        // or get null
        $stepRes = GeneratorTable::getList([
            "order" => ["STATUS" => "ASC", "ID" => "ASC"],
            "select" => ["ID", "STEP", "TYPE", "STATUS", "ITEMS_PER_STEP"],
            "limit" => 1
        ]);
        $lastItem = $stepRes->fetch();
        if($lastItem["STATUS"] == 1){
            // echo "finished";
            return false;
        } else {
            if ($lastItem["TYPE"] == "\Aero\Generator\Types\Product") {
                // echo "gen product";
                $this->step     = (int) $lastItem["STEP"];
                $this->id       = (int) $lastItem["ID"];
                $this->stepSize = (int) $lastItem["ITEMS_PER_STEP"];
                $this->type     = $this->createGenerateable($lastItem["TYPE"]);
            } elseif (in_array($lastItem["TYPE"], $steps)) {
                // echo "gen structure";
                $this->step     = (int) $lastItem["STEP"];
                $this->id       = (int) $lastItem["ID"];
                $this->stepSize = (int) $lastItem["ITEMS_PER_STEP"];
                $this->type     = $this->createGenerateable($lastItem["TYPE"]);
            } else {
                // echo "make plan";
                $this->plan = new Plan();
                $this->plan->initStructurePlan();
                return true;
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
     * Returns total steps quantity from database
     *
     * @return int
     */
    private function setCountFromDb(){
        $cntRes = GeneratorTable::getList([
            'select' => ['CNT'],
            // 'filter' => ['TYPE' => '\Aero\Generator\Types\Product'],
            'runtime' => [
                new ExpressionField('CNT', 'COUNT(*)')
            ]
        ]);
        $result = $cntRes->fetch();
        $this->stepCount = (int) $result["CNT"];
    }
}