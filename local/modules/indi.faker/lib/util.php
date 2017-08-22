<?php
/**
 * Individ module
 *
 * @user        : dadaev
 * @category    Individ
 * @link        http://individ.ru
 * @date        09.08.2016 13:22
 */

namespace Indi\Faker;

class Util
{
	/**
	 * @static      getRandomImage
	 * Получение пути к случаной картинке из папки images модуля
	 * @return string
	 */
	public static function getRandomImage()
	{
		$dir = $_SERVER['DOCUMENT_ROOT'] . MODULE_IMG_PATH;
		$files = array_values(array_diff(scandir($dir), array('..', '.')));
		$randKey = array_rand($files);
		$randFile = $files[$randKey];
		$randImg = $dir . $randFile;

		return $randImg;
	}

	/**
	 * @static      getCurDateSiteFormat
	 * Получение текущей даты в формате сайта
	 *
	 * @param string $mode режим
	 *
	 * @return bool|string SHORT | FULL
	 */
	public static function getCurDateSiteFormat($mode = 'FULL')
	{
		global $DB;

		return date($DB->DateFormatToPHP(\CSite::GetDateFormat($mode)), time());
	}

	/**
	 * @static      nl2p
	 * Разбивает строку на абзацы (на основании переносов) и оборачивает в тег p
	 * @param string $str
	 *
	 * @return mixed|string текст
	 */
	public static function nl2p($str)
	{
		$str = '<p>' . preg_replace('/\r\n|\n|\r/', '</p>$0<p>', $str) . '</p>';
		$str = preg_replace('/<p><\/p>/', '', $str);
		return $str;
	}

}