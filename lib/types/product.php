<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Catalog\Generator\Types;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Config\Option;
use Catalog\Generator\Entity\GeneratorTable;

class Product extends CatalogProduct implements Generateable
{
    // @TODO replace
    const MODULE_NAME = "catalog.generator";

    /**
     * Iblock code for product
     */
    const IBLOCK_CODE = "catalog_catalog_generator";

    /**
     * @var int
     */
    protected $skuCount;

    /**
     * @var iblock id
     */
    protected $iblockId;

    public function __construct()
    {
        $this->skuCount = (int) Option::get("catalog.generator", "sku_count");
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
            for($i = 0; $i < $this->skuCount; $i++)
                $sku->generate();
        } else {
            $totalCount = $this->addStoresCount($elementId);
            $this->addCatalogProduct($elementId, $totalCount);
            $this->addPrices($elementId);
        }
    }

    public function remove()
    {
        $elementRes = ElementTable::getList([
            "filter" => ["IBLOCK_ID" => $this->iblockId],
            "order" => ["ID" => "DESC"],
            "select" => ["ID"],
            "limit" => 1
        ]);
        if($elementFields = $elementRes->fetch()){
            $id = (int) $elementFields["ID"];
        }
        \CIBlockElement::Delete($id);
    }

    public function getCountToGenerate():int
    {
        $stepRes = GeneratorTable::getList([
            "order" => ["STATUS" => "ASC", "ID" => "ASC"],
            "select" => ["ID", "STEP", "STATUS", "ITEMS_PER_STEP"],
            "limit" => 1
        ]);
        $lastItem = $stepRes->fetch();
        return (int) $lastItem["ITEMS_PER_STEP"];
    }
}