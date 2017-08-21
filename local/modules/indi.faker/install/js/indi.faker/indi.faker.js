/**
 * Individ faker namespace
 */
indiFaker = new function () {
};

/**
 * Приложение
 */

indiFaker.app =
{
	/**
	 * Получает данные json от сервера, разбирает их, возвращает если все хорошо,
	 * иначе выдает ошибку
	 *
	 * @param response
	 * @returns {boolean}
	 */

	getServerData: function (response) {
		try {
			var responseJson = JSON.parse(response);
			if (responseJson.error) {
				alert(responseJson.error);
				return false;
			}
			else {
				return responseJson.result;
			}
			
		} catch (e) {
			alert('Ошибка чтения ответа от сервера');
		}
	}
};

/**
 * Утилиты для работы с URL
 */

indiFaker.utilsUrl =
{
	/**
	 * Получает параметр url
	 *
	 * @param sParam
	 * @returns {boolean}
	 */

	getUrlParameter: function (sParam) {
		var sPageURL = decodeURIComponent(window.location.search.substring(1)),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;
		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : sParameterName[1];
			}
		}
	}
};



$(document).ready(function () {
	/**
	 * Генерация тестовых данных по дефолту
	 */

	$(document).on('click', '.js-demo-generate-default', function (e) {
		var $btn = $(this);
		if($btn.attr('disabled')) {
			e.stopPropagation();
			return false;
		}

		var action = $btn.data('url');
		var iblockId = indiFaker.utilsUrl.getUrlParameter('IBLOCK_ID');
		var sectionId = indiFaker.utilsUrl.getUrlParameter('find_section_section');
		var wait = BX.showWait('xls_container');
		$btn.attr('disabled', 'disabled');
		$.ajax({
			url: action,
			data: {
				IBLOCK_ID: iblockId,
				SECTION_ID: sectionId,
				ACTION: 'generate'
			},
			success: function (response) {
				var data = indiFaker.app.getServerData(response);
				if(data) {
					BX.closeWait('xls_container', wait);
					location.reload();
				}

			}
		});
		e.stopPropagation();
		return false;
	});
	/**
	 * Удаление тестовых данных
	 */

	$(document).on('click', '.js-demo-delete', function (e) {
		var $btn = $(this);
		if($btn.attr('disabled')) {
			e.stopPropagation();
			return false;
		}

		var deleteConfirm = confirm("Вы действительно хотите удалить все тестовые данные?");
		var action = $btn.data('url');
		var iblockId = indiFaker.utilsUrl.getUrlParameter('IBLOCK_ID');
		var sectionId = indiFaker.utilsUrl.getUrlParameter('find_section_section');
		if(deleteConfirm) {
			var wait = BX.showWait('xls_container');
			$btn.attr('disabled', 'disabled');
			$.ajax({
				url: action,
				data: {
					IBLOCK_ID: iblockId,
					SECTION_ID: sectionId,
					ACTION: 'delete'
				},
				success: function (response) {
					var data = indiFaker.app.getServerData(response);
					if(data) {
						BX.closeWait('xls_container', wait);
						location.reload();
					}

				}
			});
		}
		e.stopPropagation();
		return false;
	});
});