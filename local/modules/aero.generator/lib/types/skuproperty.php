<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Aero\Generator\Types;

use Bitrix\Main\Config\Option;

class SkuProperty extends Property implements Generateable
{
    /**
     * Iblock code for sku
     */
    const IBLOCK_CODE = "sku_aero_generator";

    public function getStepSize():int
    {
        return (int) Option::get("aero.generator", "types_skuproperty");
    }
}