<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Catalog\Generator\Types;

use Bitrix\Main\Config\Option;

class ProductProperty extends Property implements Generateable
{
    /**
     * Iblock code for product
     */
    const IBLOCK_CODE = "catalog_catalog_generator";

    public function getCountToGenerate():int
    {
        return (int) Option::get("catalog.generator", "types_productproperty");
    }
}