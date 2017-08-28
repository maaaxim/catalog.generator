<?php

namespace Aero\Generator;

use Aero\Generator\Entity\GeneratorTable;
use Bitrix\Main\DB\Exception;

class Steps
{
    protected $step;
    protected $stepSize;
    protected $stepCount;

    public function __construct(){

        $this->makePlan();

        $this->step = $this->getCurrentStepNumber();
        $this->stepCount = $this->getCountFromDb();

        $this->stepSize = 1; // @TODO set up automatically depending on data size

//        $this->cleanSteps();
    }

    /**
     * Makes a generation step.
     * Returns 0 if there is nothing to do.
     * @return int
     * @throws Exception
     */
    public function createNext(){

        $this->step = $this->getNextStepNumber();

        if(
            $this->step <= 0
            || $this->step > $this->stepCount
        ) return 0;

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

    public function setConfig(){}

    private function makePlan(){

        // Check data exist
        if($this->getCountFromDb() === 0){

            // Read config (main settings) - to getConfig
            $config["types_catalog"]         = \Bitrix\Main\Config\Option::get("aero.generator", "types_catalog");
            $config["types_product"]         = \Bitrix\Main\Config\Option::get("aero.generator", "types_product");
            $config["types_sku"]             = \Bitrix\Main\Config\Option::get("aero.generator", "types_sku");
            $config["types_price"]           = \Bitrix\Main\Config\Option::get("aero.generator", "types_price");
            $config["types_store"]           = \Bitrix\Main\Config\Option::get("aero.generator", "types_store");
            $config["types_productproperty"] = \Bitrix\Main\Config\Option::get("aero.generator", "types_productproperty");
            $config["types_skuproperty"]     = \Bitrix\Main\Config\Option::get("aero.generator", "types_skuproperty");

            $plan = [];
            foreach($config as $key => $setting){
                $exploded = explode("_", $key);
                $typeClassname = ucfirst($exploded[1]);
                $class = '\\Aero\\Generator\\Types\\' . $typeClassname;
                $plan[$class] = [
                    "order" => $class::ORDER,
                    "exploded" => $exploded,
                    "setting" => $setting
                ];
            }

            // Sort multidimensional array by one of values
            uasort($plan, function($a, $b) {
                $return = $a['order'] <=> $b['order'];
                return $return;
            });

            echo "<pre>"; var_dump($plan); echo "</pre>";

            // Make step for each entity and type of entity
        }

        /*
        for($i = 1; $i < 31; $i++){
            GeneratorTable::add([
                "STEP" => $i,
                "STATUS" => 0,
                "TYPE" => "product",
                "ITEMS_PER_STEP" => 1
            ]);
        }
        */
    }

    /**
     * Get step number from db
     *
     * @return int
     * @throws Exception
     */
    private function getNextStepNumber(){
        $stepRes = GeneratorTable::getList([
            "filter" => ["STATUS" => 0],
            "order" => ["ID" => "ASC"],
            "select" => ["ID", "STEP"],
            "limit" => 1
        ]);
        if($stepFields = $stepRes->fetch()){
            $result = GeneratorTable::update($stepFields["ID"], [
                "STATUS" => 1,
            ]);
            if (!$result->isSuccess()){
                throw new Exception($result->getErrorMessages());
            }
            return (int) $stepFields["STEP"];
        } else {
            return 0;
        }
    }

    /**
     * @return int
     */
    private function getCurrentStepNumber(){
        $stepRes = GeneratorTable::getList([
            "filter" => ["STATUS" => 0],
            "order" => ["ID" => "ASC"],
            "select" => ["STEP"],
            "limit" => 1
        ]);
        if($stepFields = $stepRes->fetch()){
            return (int) $stepFields["STEP"];
        } else {
            return 0;
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
    private function getCountFromDb(){
        $cntRes = GeneratorTable::getList(array(
            'select' => array('CNT'),
            'runtime' => array(
                new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
            )
        ));
        $result = $cntRes->fetch();
        return (int) $result["CNT"];
    }
}