<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Aero\Generator\Types;

use Bitrix\Main\Config\Option;

class Product extends CatalogProduct implements Generateable
{
    const ORDER = 6;

    // @TODO replace
    const MODULE_NAME = "aero.generator";

    /**
     * @var iblock id
     */
    protected $iblockId;

    /**
     * @var bool
     */
    protected $skuCount;

    public function __construct()
    {
        $this->skuCount =(int) Option::get("aero.generator", "sku_count");
        parent::__construct();
    }

    /**
     * Generate method
     */
    function generate()
    {
        $elementId = $this->addIblockElement();
        if($this->skuCount > 0){
            $sku = new Sku($elementId);
            for($i = 0; $i < $this->skuCount; $i++){
                $sku->generate();
            }
        } else {
            $totalCount = $this->addStoresCount($elementId);
            $this->addCatalogProduct($elementId, $totalCount);
            $this->addPrices($elementId);
        }
    }
}