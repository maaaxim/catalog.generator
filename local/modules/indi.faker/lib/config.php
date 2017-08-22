<?php
/**
 * Individ module
 *
 * @user        dadaev
 * @category    Individ
 * @link        http://individ.ru
 * @date        08.08.2016 13:39
 */

namespace Indi\Faker;

use Bitrix\Main\Config\Option;

/**
 * Individ module
 *
 * Class Valid
 *
 * Класс управления конфигруациями генерации данных
 *
 * @user        m.dadaev
 * @category    Individ
 * @link        http://individ.ru
 */

class Config
{
	public $defaultConfig;
	protected $moduleName;

	public function __construct()
	{
		$this->moduleName = 'indi.faker';
		$this->defaultConfig = array(
			"count" => Option::get($this->moduleName, "count"), // количество добавляемых элементов за один запрос
			"words_in_el_name" => Option::get($this->moduleName, "words_in_el_name"), // количество слов в названии элемента
			"preview_text_length" => Option::get($this->moduleName, "preview_text_length"), // длина текста анонса
			"detail_text_length" => Option::get($this->moduleName, "detail_text_length"), // длина текста (детального)
			"preview_picture_width" => Option::get($this->moduleName, "preview_picture_width"), // ширина картинки анонса
			"preview_picture_height" => Option::get($this->moduleName, "preview_picture_height"), // высота картинки анонса
			"detail_picture_width" => Option::get($this->moduleName, "detail_picture_width"), // ширина картинки детальной
			"detail_picture_height" => Option::get($this->moduleName, "detail_picture_height"), // высота картинки детальной
			"catalog_price_max_decimals" => Option::get($this->moduleName, "catalog_price_max_decimals"), // количество знаков после запятов у цены элемента
			"catalog_price_min" => Option::get($this->moduleName, "catalog_price_min"), // минимальная цена
			"catalog_price_max" => Option::get($this->moduleName, "catalog_price_max"), // максимальная цена
			"catalog_weight_min" => Option::get($this->moduleName, "catalog_weight_min"), // минимальный вес
			"catalog_weight_max" => Option::get($this->moduleName, "catalog_weight_max"), // максимальный вес
			"catalog_width_min" => Option::get($this->moduleName, "catalog_width_min"), // минимальная длина
			"catalog_width_max" => Option::get($this->moduleName, "catalog_width_max"), // максимальная длина
			"catalog_length_min" => Option::get($this->moduleName, "catalog_length_min"), // минимальная ширина
			"catalog_length_max" => Option::get($this->moduleName, "catalog_length_max"), // максимальная ширина
			"catalog_height_min" => Option::get($this->moduleName, "catalog_height_min"), // минимальная высота
			"catalog_height_max" => Option::get($this->moduleName, "catalog_height_max"), // максимальная высота
			"catalog_quantity_min" => Option::get($this->moduleName, "catalog_quantity_min"), // минимальное количество
			"catalog_quantity_max" => Option::get($this->moduleName, "catalog_quantity_max"), // максимальное количество
			"property_multiple_count" => Option::get($this->moduleName, "property_multiple_count"), // количество значений множественного свойства
			"property_string_length" => Option::get($this->moduleName, "property_string_length"), // длина свойства "Строка"
			"property_text_length" => Option::get($this->moduleName, "property_text_length"), // количество символов для значений свойства "Текст"
		);
	}

	/**
	 * Получение конфигурации для ИБ
	 * 
	 * @param integer $iblockId
	 * @return array|bool|null
	 */
	
	public function getConfig($iblockId)
	{
		$config = null;

		if (intval($iblockId)) {
			$config = $this->getConfigForIblock($iblockId);
		}

		if(!$config) {
			$config = $this->defaultConfig;
		}

		return $config;
	}

	/**
	 * getConfigForIblock
	 * 
	 * Получение конфига для инфоблока
	 * @param integer $iblockId
	 *
	 * @return bool
	 */
	private function getConfigForIblock($iblockId)
	{
		// TODO Create logic
		return false;
	}
}