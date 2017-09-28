<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Catalog\Generator\Types;

/**
 * Class Plan
 * @package Catalog\Generator\Types
 */
class Plan implements Generateable
{

    /**
     * @var \Catalog\Generator\Plan
     */
    private $plan;

    /**
     * Plan constructor.
     */
    public function __construct()
    {
        $this->plan = new \Catalog\Generator\Plan();
    }

    /**
     * Generate plan
     */
    function generate()
    {
        if($this->plan->getStructureCreated()){
            $this->plan->initProductsPlan();
        } else {
            $this->plan->initStructurePlan();
        }
    }

    /**
     * @return int
     */
    public function getStepSize():int
    {
        return 1;
    }
}