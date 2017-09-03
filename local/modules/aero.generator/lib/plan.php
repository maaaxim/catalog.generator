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
    private $config;
    private $plan;
    
    public function __construct(){
        $this->plan = [];
    }

    public function init(){
        $this->setConfig();
        $this->fillPlan();
        $this->sortPlan();
        $this->writePlan();
    }

    private function setConfig(){
        $this->config["types_catalog"]         = 1; // 1 catalog is enough
        $this->config["types_product"]         = \Bitrix\Main\Config\Option::get("aero.generator", "types_product");
        $this->config["types_price"]           = \Bitrix\Main\Config\Option::get("aero.generator", "types_price");
        $this->config["types_store"]           = \Bitrix\Main\Config\Option::get("aero.generator", "types_store");
        $this->config["types_productproperty"] = \Bitrix\Main\Config\Option::get("aero.generator", "types_productproperty");
        $this->config["types_skuproperty"]     = \Bitrix\Main\Config\Option::get("aero.generator", "types_skuproperty");
    }
    
    private function fillPlan(){
        foreach($this->config as $key => $setting){
            $exploded = explode("_", $key);
            $typeClassname = ucfirst($exploded[1]);
            $class = '\\Aero\\Generator\\Types\\' . $typeClassname;
            $this->plan[$class] = [
                "order" => $class::ORDER,
                "exploded" => $exploded,
                "setting" => $setting
            ];
        }
    }

    private function sortPlan(){
        // Sort multidimensional array by one of values
        uasort($this->plan, function($a, $b) {
            $return = $a['order'] <=> $b['order'];
            return $return;
        });
    }

    private function writePlan(){
        // Make step for each entity and type of entity
        $iterator = 0;
        foreach($this->plan as $key => $item){
            $max = (int )$item["setting"];
            if($max <= 0)
                throw new Exception("Must have more than 0 entity");
            for ($i = 0; $i < $max; $i++) {
                $iterator++;
                $data = [
                    "STEP" => $iterator,
                    "STATUS" => 0,
                    "TYPE" => $key,
                    "ITEMS_PER_STEP" => 1
                ];
                GeneratorTable::add($data);
            }
        }
    }
}