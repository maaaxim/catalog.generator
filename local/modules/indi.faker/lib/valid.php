<?php
/**
 * Individ module
 *
 * @user        dadaev
 * @category    Individ
 * @link        http://individ.ru
 * @date        08.08.2016 13:39
 */

namespace Faker;

/**
 * Individ module
 *
 * Class Valid
 *
 * Класс для проверки данных, поступивших на контроллер
 *
 * @user        m.dadaev
 * @category    Individ
 * @link        http://individ.ru
 */

class Valid
{
	/**
	 * @static  checkControllerData
	 *
	 * Проверяет данные, возвращает либо признак об успешности операции или описание ошибки
	 *
	 * @param $controllerData
	 *
	 * @return array
	 */
	public static function checkControllerData($controllerData)
	{

		if (!strlen($controllerData->action)) {
			$result = array('error' => 'Ошибка! Не передан обязательный параметр "Действие"');
		} elseif (!strlen($controllerData->iblockId)) {
			$result = array('error' => 'Ошибка! Не передан обязательный параметр "ID Инфоблока"');
		}
		elseif (!self::checkIblockId($controllerData->iblockId)) {
			$result = array('error' => 'Ошибка! Инфоблок с id = "'.$controllerData->iblockId.'" не найден');
		}
		else {
			$result = array('success' => true);
		}

		return $result;
	}

	/**
	 * @static      checkIblockId
	 * Проверка id инфоблока на существование
	 *
	 * @param integer $iblockId id инфоблока
	 * @return boolean
	 *
	 */
	private static function checkIblockId($iblockId)
	{
		$rsIblock = \CIBlock::GetByID($iblockId);
		if($arIblock = $rsIblock->GetNext()) {
			return true;
		}

		return false;

	}
}