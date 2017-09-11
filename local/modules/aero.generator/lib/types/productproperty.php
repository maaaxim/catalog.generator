<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Aero\Generator\Types;

use Aero\Generator\Config;

class ProductProperty extends Property implements Generateable
{
    /**
     * Iblock code for product
     */
    const IBLOCK_CODE = "catalog_aero_generator";

    public function getStepSize()
    {
        $config = Config::getInstance();
        return $config->getOption("types_productproperty");
    }
}