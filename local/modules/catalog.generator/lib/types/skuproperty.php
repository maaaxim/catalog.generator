<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Catalog\Generator\Types;

use Bitrix\Main\Config\Option;

class SkuProperty extends Property implements Generateable
{
    /**
     * Iblock code for sku
     */
    const IBLOCK_CODE = "sku_catalog_generator";

    public function getStepSize():int
    {
        return (int) Option::get("catalog.generator", "types_skuproperty");
    }

    /**
     * Generate only if we need sku
     */
    public function generate()
    {
        $skuCount = (int) Option::get("catalog.generator", "sku_count");
        if($skuCount > 0)
            parent::generate();
    }
}