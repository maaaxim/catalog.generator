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

        $this->plan = new Plan();
        $this->plan->initStructurePlan(); // @TODO run it on first ajax request only

        $this->setCurrentStepNumber();

        $this->stepSize = 1; // @TODO set up automatically depending on data size

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

        // check if steps are complete
//        if(
//            $this->step <= 0
//            || $this->step > $this->stepCount
//        ) return 0;

        // then generate
        try {
            if ($this->initStep()) {
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
            "filter" => ["STATUS" => 0],
            "order" => ["ID" => "ASC"],
            "select" => ["ID", "STEP", "TYPE"],
            "limit" => 1
        ]);
        if($stepFields = $stepRes->fetch()){
            $this->step = (int) $stepFields["STEP"];
            $this->id   = (int) $stepFields["ID"];
            $this->type = $this->createGenerateable($stepFields["TYPE"]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $type
     * @return Generateable
     */
    private function createGenerateable(string $type) {
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

    private function setCurrentStepNumber(){
        $stepRes = GeneratorTable::getList([
            "filter" => ["STATUS" => 0],
            "order" => ["ID" => "ASC"],
            "select" => ["STEP"],
            "limit" => 1
        ]);
        if($stepFields = $stepRes->fetch())
            $this->step = (int) $stepFields["STEP"];
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
            'filter' => [
                'TYPE' => '\Aero\Generator\Types\Product'
            ],
            'runtime' => [
                new ExpressionField('CNT', 'COUNT(*)')
            ]
        ]);
        $result = $cntRes->fetch();
        $this->stepCount = (int) $result["CNT"];
    }
}