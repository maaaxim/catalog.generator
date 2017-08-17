<?php
/**
 * Individ faker module
 * 
 * @user: dadaev
 * @category    Individ 
 * @link        http://individ.ru
 * @date        08.08.2016 13:22
 */

namespace Indi\Faker;
use Bitrix\Main\Application;

/**
 * Individ module
 *
 * Class Controller
 *
 * Класс для построения маршрутов
 *
 * @user        m.dadaev
 * @category    Individ
 * @link        http://individ.ru
 */

class Controller {

	public $iblockId; // id инфоблока
	public $action; // id действие

	/**
	 * Controller constructor
	 * Формируется экземпляр класса, параметры которого - это данные переданные на контроллер
	 */

	public function __construct() {
		$request = Application::getInstance()->getContext()->getRequest();

		$iblockId = htmlspecialchars($request->getQuery("IBLOCK_ID"));
		$sectionId = htmlspecialchars($request->getQuery("SECTION_ID"));
		$action = htmlspecialchars($request->getQuery("ACTION"));

		$this->iblockId = htmlspecialchars($iblockId);
		$this->sectionId = htmlspecialchars($sectionId);
		$this->action = htmlspecialchars($action);
	}

	/**
	 * constructResult
	 * Формирует JSON - строку, содержащую результат выполнения операции или описание ошибки
	 * @param array $params
	 *
	 * @return array|bool|string
	 */

	public function constructResult($params = array()) {
		if(empty($params)) {
			return false;
		}

		$result = array(
			'result' => $params["result"],
			'error' => $params["error"]
		);

		$result = json_encode($result);
		return $result;
	}
}