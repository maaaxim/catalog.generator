<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Aero\Generator\Types;

use Bitrix\Catalog\GroupTable;
use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Faker\Factory;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\ExpressionField;
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
    protected $hasSku;

    public function __construct()
    {
         if((int) Option::get("aero.generator", "sku_count") > 0)
             $this->hasSku = true;
         else
             $this->hasSku = false;
         parent::__construct();
    }

    /**
     * Generate method
     */
    function generate()
    {
        // @TODO fix addIblockElement depending on sku needed
        $elementId = $this->addIblockElement();
        if (intval($elementId) > 0 && \CCatalog::GetByID($this->iblockId)) {
            $totalCount = $this->addStoresCount($elementId);
            $this->addCatalogProduct($elementId, $totalCount);
            $this->addPrices($elementId);
        }

        // @TODO sku
        // @TODO refactor
    }
}