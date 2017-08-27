<?php

namespace Aero\Generator;

class Steps
{
    protected $step;
    protected $stepSize;
    protected $stepCount = 10;

    public function __construct(){
        $this->step = 1; // @TODO get it from DB
        $this->stepSize = 1; // @TODO set up automatically depending on data size

        // $this->makePlan();
         $this->getNextStep();
    }

    /**
     * Makes a generation step.
     * Returns 0 if there is nothing to do.
     *
     * @return int
     */
    public function createNext(){

        if(!isset($this->step))
            $this->step = 1;
        else
            $this->step += $this->stepSize;

        if($this->step > $this->stepCount)
            return 0;

        $stepsComplete = $this->stepSize;

        return $stepsComplete;
    }

    /**
     * Returns total steps count
     *
     * @return int
     */
    public function getTotal(){
        return $this->stepCount;
    }

    public function getCurrent(){
        return $this->step;
    }

    public function setConfig(){}

    private function makePlan(){
        for($i = 0; $i < 30; $i++){
            \Aero\Generator\Entity\GeneratorTable::add([
                "STATUS" => 0,
                "TYPE" => "product",
                "ITEMS_PER_STEP" => 1
            ]);
        }
    }

    private function getNextStep(){
        $stepRes = \Aero\Generator\Entity\GeneratorTable::getList([
            "filter" => ["STATUS" => 0],
            "order" => ["ID" => "ASC"],
            "select" => ["ID", "STEP"],
            "limit" => 1
        ]);
        if($stepFields = $stepRes->fetch())
            echo "<pre>"; var_dump($stepFields); echo "</pre>";
        die();
    }
}