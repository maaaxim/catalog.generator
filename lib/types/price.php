<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 22.08.2017
 * Time: 23:25
 */

namespace Catalog\Generator\Types;

use CCatalogGroup;
use Exception;
use Faker\Factory;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

class Price implements Generateable
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var price name
     */
    protected $name;

    /**
     * @var price code
     */
    protected $code;

    /**
     * Price constructor
     */
    public function __construct()
    {
        $this->includeModules();
        $this->faker = Factory::create('ru_RU');
        $this->setPriceFields();
    }

    /**
     * Generates price type
     *
     * @throws Exception
     */
    function generate()
    {
        $arFields = [
            "NAME" => $this->code,
            "SORT" => 500,
            "USER_GROUP" => [2], // @TODO get it from db
            "USER_GROUP_BUY" => [2], // @TODO get it from db
            "XML_ID" => $this->code,
            "USER_LANG" => [
                "ru" => $this->name,
                "en" => $this->name
            ]
        ];
        $priceId = CCatalogGroup::Add($arFields);
        if ($priceId <= 0)
            throw new Exception("Add price error");
    }

    /**
     * Including modules
     *
     * @throws Exception
     */
    private function includeModules()
    {
        if(!Loader::includeModule("catalog"))
            throw new Exception("Can't include catalog module");
    }

    /**
     * Setting fields
     */
    private function setPriceFields()
    {
        $sentence = $this->faker->sentence(rand(1, 3));
        $this->name = substr($sentence, 0, strlen($sentence) - 1);
        $this->code = strtoupper(str_replace(' ', '_', $this->name));
    }

    public function getCountToGenerate():int
    {
        return Option::get("catalog.generator", "types_price");
    }
}