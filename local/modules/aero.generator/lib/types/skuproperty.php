<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Aero\Generator\Types;

use Aero\Generator\Config;

class SkuProperty extends Property implements Generateable
{
    /**
     * Iblock code for sku
     */
    const IBLOCK_CODE = "sku_aero_generator";

    public function getStepSize()
    {
        $config = Config::getInstance();
        return $config->getOption("types_skuproperty");
    }
}