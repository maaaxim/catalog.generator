<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Aero\Generator\Types;

/**
 * Class Plan
 * @package Aero\Generator\Types
 */
class Plan implements Generateable
{

    /**
     * @var \Aero\Generator\Plan
     */
    private $plan;

    /**
     * Plan constructor.
     */
    public function __construct()
    {
        $this->plan = new \Aero\Generator\Plan();
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