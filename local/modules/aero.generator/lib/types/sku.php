<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Aero\Generator\Types;


class Sku extends CatalogProduct implements Generateable
{
    const ORDER = 7;

    /**
     * Iblock code for sku
     */
    const IBLOCK_CODE = "sku_aero_generator";

    /**
     * Product id
     *
     * @var int
     */
    protected $productId;

    /**
     * Product name
     *
     * @var string
     */
    protected $productName;

    /**
     * Sku constructor.
     * @param int $productId
     */
    public function __construct(int $productId)
    {
        $this->productId = $productId;
        $this->setProductName();
        parent::__construct();
    }

    /**
     * Generate method
     */
    function generate()
    {
        $elementId = $this->addIblockElement();
        $totalCount = $this->addStoresCount($elementId);
        $this->addCatalogProduct($elementId, $totalCount);
        $this->addPrices($elementId);
    }

    /**
     * Returns filled fields
     *
     * @param array $arParams
     * @return array
     */
    public function getDataFields(array $arParams = []):array
    {
        $iblockId = $this->iblockId;
        $skuName = $this->faker->sentence($this->config["words_in_el_name"]);
        $name = $skuName . " (" . $this->productName . ")";
        $arFields = [
            "IBLOCK_ID" => $iblockId,
            "NAME" => $name,
            "DATE_ACTIVE_FROM" => self::getCurDateSiteFormat(),
            "CODE" => \Cutil::translit($skuName, "ru"),
            "ACTIVE" => "Y"
        ];
        return $arFields;
    }

    /**
     * Returns props and their values
     *
     * @return array
     */
    public function getDataProps():array
    {
        $props = parent::getDataProps();
        $props["CML2_LINK"] = $this->productId;
        return $props;
    }

    /**
     * Set product name
     */
    protected function setProductName()
    {
        $productRes = \CIBlockElement::GetByID($this->productId);
        $product = $productRes->Fetch();
        $this->productName = $product["NAME"];
    }
}