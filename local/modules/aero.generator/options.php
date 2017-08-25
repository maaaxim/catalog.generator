<?
$moduleID = 'aero.generator';
$moduleRight = $APPLICATION->GetGroupRight($moduleID);
if ($moduleRight >= "R"):
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
	IncludeModuleLangFile(__FILE__);
	$arTabs = array(
		array(
			'tab' => array(
				"DIV" => "Settings",
				"TAB" => GetMessage("AERO_CATALOG_CREATOR_TAB_SETTINGS"),
				"ICON" => $moduleID . "_settings",
				"TITLE" => GetMessage("AERO_CATALOG_CREATOR_TAB_SETTINGS_TITLE"),
			),
			'options' => array(
				array("total_products", GetMessage("AERO_CATALOG_CREATOR_TOTAL_PRODUCTS"), array("text", 24), 'default' => '1000'),
				array("total_prices", GetMessage("AERO_CATALOG_CREATOR_TOTAL_PRICES"), array("text", 24), 'default' => '10'),
				array("total_stores", GetMessage("AERO_CATALOG_CREATOR_TOTAL_STORES"), array("text", 24), 'default' => '100'),
				array("total_properties", GetMessage("AERO_CATALOG_CREATOR_TOTAL_PRODUCT_PROPERTIES"), array("text", 24), 'default' => '100'),
				array("total_properties", GetMessage("AERO_CATALOG_CREATOR_TOTAL_SKU_PROPERTIES"), array("text", 24), 'default' => '200'),
                array("sku_per_product", GetMessage("AERO_CATALOG_CREATOR_SKU_PER_PRODUCT"), array("text", 24), 'default' => '10'),

				array("count", GetMessage("AERO_CATALOG_CREATOR_OPT_COUNT"), array("text", 24), 'default' => '50'),
				array("words_in_el_name", GetMessage("AERO_CATALOG_CREATOR_OPT_WORDS_IN_EL_NAME"), array("text", 24), 'default' => '5'),
				array("preview_text_length", GetMessage("AERO_CATALOG_CREATOR_OPT_PREVIEW_TEXT_LENGTH"), array("text", 24), 'default' => '150'),
				array("detail_text_length", GetMessage("AERO_CATALOG_CREATOR_OPT_DETAIL_TEXT_LENGTH"), array("text", 24), 'default' => '600'),
				array("preview_picture_width", GetMessage("AERO_CATALOG_CREATOR_OPT_PREVIEW_PICTURE_WIDTH"), array("text", 24), 'default' => '300'),
				array("preview_picture_height", GetMessage("AERO_CATALOG_CREATOR_OPT_PREVIEW_PICTURE_HEIGHT"), array("text", 24), 'default' => '200'),
				array("detail_picture_width", GetMessage("AERO_CATALOG_CREATOR_OPT_DETAIL_PICTURE_WIDTH"), array("text", 24), 'default' => '800'),
				array("detail_picture_height", GetMessage("AERO_CATALOG_CREATOR_OPT_DETAIL_PICTURE_HEIGHT"), array("text", 24), 'default' => '600'),
				array("catalog_price_max_decimals", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_PRICE_MAX_DECIMALS"), array("text", 24), 'default' => '2'),
				array("catalog_price_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_PRICE_MIN"), array("text", 24), 'default' => '0'),
				array("catalog_price_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_PRICE_MAX"), array("text", 24), 'default' => '10000'),
				array("catalog_weight_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_WEIGHT_MIN"), array("text", 24), 'default' => '0'),
				array("catalog_weight_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_WEIGHT_MAX"), array("text", 24), 'default' => '100'),
				array("catalog_width_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_WIDTH_MIN"), array("text", 24), 'default' => '0'),
				array("catalog_width_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_WIDTH_MAX"), array("text", 24), 'default' => '100'),
				array("catalog_length_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_LENGTH_MIN"), array("text", 24), 'default' => '0'),
				array("catalog_length_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_LENGTH_MAX"), array("text", 24), 'default' => '100'),
				array("catalog_height_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_HEIGHT_MIN"), array("text", 24), 'default' => '0'),
				array("catalog_height_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_HEIGHT_MAX"), array("text", 24), 'default' => '100'),
				array("catalog_quantity_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_QUANTITY_MIN"), array("text", 24), 'default' => '0'),
				array("catalog_quantity_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_QUANTITY_MAX"), array("text", 24), 'default' => '100'),
				array("property_multiple_count", GetMessage("AERO_CATALOG_CREATOR_OPT_PROPERTY_MULTIPLE_COUNT"), array("text", 24), 'default' => '3'),
				array("property_string_length", GetMessage("AERO_CATALOG_CREATOR_OPT_PROPERTY_STRING_LENGTH"), array("text", 24), 'default' => '10'),
				array("property_text_length", GetMessage("AERO_CATALOG_CREATOR_OPT_PROPERTY_TEXT_LENGTH"), array("text", 24), 'default' => '600'),
			)
		),
		array(
			'tab' => array(
				"DIV" => "edit2",
				"TAB" => GetMessage("MAIN_TAB_RIGHTS"),
				"ICON" => $moduleID . "_settings",
				"TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")
			),
			'options' => array(),
			'require' => "/bitrix/modules/main/admin/group_rights.php",
		),
	);
	$arTabsConfig = array();
	foreach ($arTabs as $tab)
		$arTabsConfig[] = $tab['tab'];
	$tabControl = new CAdminTabControl("tabControl", $arTabsConfig);
	if ($moduleRight >= "W" && $_SERVER['REQUEST_METHOD'] == "POST" && strlen($_POST['RestoreDefaults'] . $_POST['Update']) > 0 && check_bitrix_sessid()) {
		if ($_POST['RestoreDefaults']) {
			COption::RemoveOption($moduleID);
			$rsGroups = CGroup::GetList($v1 = "id", $v2 = "asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
			while ($arGroup = $rsGroups->Fetch())
				$APPLICATION->DelGroupRight($moduleID, array($arGroup["ID"]));
		}
		if ($_POST['Update']) {
			foreach ($arTabs as $tab) {
				if (is_array($tab['options'])) {
					foreach ($tab['options'] as $option) {
						if (!is_array($option))
							continue;
						if (in_array($option[2][0], array('checkbox', 'text', 'textarea', 'selectbox'))) {
							$name = $option[0];
							$val = ${$name};
							if ($option[2][0] == "checkbox" && $val != "Y")
								$val = "N";
							if ($option[2][0] == "multiselectbox")
								$val = @implode(",", $val);
							if(is_numeric($val)) { // на данном этапе сохраняем только числовые значения
								COption::SetOptionString($moduleID, $name, $val, $option[1]);
							}
						}
					}
				}
			}
		}

		LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($moduleID) . "&lang=" . urlencode(LANGUAGE_ID) . "&" . $tabControl->ActiveTabParam());
	}
	CModule::IncludeModule($moduleID);
	?>
	<form method="post" action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($moduleID) ?>&amp;lang=<?= LANGUAGE_ID ?>">
		<?
		$tabControl->Begin();

		foreach ($arTabs as $tab) {
			$tabControl->BeginNextTab();
			if (is_array($tab['options'])) {
                foreach ($tab['options'] as $arOption) {
                    $val = COption::GetOptionString($moduleID, $arOption[0], $arOption['default']);
                    $type = $arOption[2];
                    if (in_array($type[0], array('checkbox', 'text', 'textarea', 'selectbox'))) {
                        ?>
                        <tr>
                            <td valign="top" width="50%">
                                <label for="<? echo htmlspecialchars($arOption[0]) ?>"><? echo $arOption[1] ?>:</label>
                            <td valign="top" width="50%">
                                <? if ($type[0] == "checkbox"): ?>
                                    <input type="checkbox" name="<? echo htmlspecialchars($arOption[0]) ?>"
                                           id="<? echo htmlspecialchars($arOption[0]) ?>"
                                           value="Y"<? if ($val == "Y") echo " checked"; ?>>
                                    <?
                                elseif ($type[0] == "text"):?>
                                    <input type="text" size="<? echo $type[1] ?>" maxlength="255"
                                           value="<? echo htmlspecialchars($val) ?>"
                                           name="<? echo htmlspecialchars($arOption[0]) ?>"
                                           id="<? echo htmlspecialchars($arOption[0]) ?>">
                                    <?
                                elseif ($type[0] == "textarea"):?>
                                    <textarea rows="<? echo $type[1] ?>" cols="<? echo $type[2] ?>"
                                              name="<? echo htmlspecialchars($arOption[0]) ?>"
                                              id="<? echo htmlspecialchars($arOption[0]) ?>"><? echo htmlspecialchars($val) ?>
                                    </textarea>
                                    <?
                                elseif ($type[0] == "selectbox"):?>
                                    <?= SelectBoxFromArray(
                                            $arOption[0],
                                            array(
                                                'REFERENCE' => array_values($arOption['values']),
                                                'REFERENCE_ID' => array_keys($arOption['values'])
                                            ),
                                            $val
                                    ); ?>
                                <? endif ?>
                            </td>
                        </tr>
                        <?
                    }
                }
			}
			if (isset($tab['require'])) {
				require_once($_SERVER["DOCUMENT_ROOT"] . $tab['require']);
			}
		}
		?>
		<? $tabControl->Buttons(); ?>
		<input <? if ($moduleRight < "W") echo "disabled" ?> type="submit" name="Update" value="<?= GetMessage("MAIN_SAVE") ?>">
		<? if (strlen($_REQUEST["back_url_settings"]) > 0):?>
			<input <? if ($moduleRight < "W") echo "disabled" ?> type="button" name="Cancel" value="<?= GetMessage("MAIN_OPT_CANCEL") ?>" title="<?= GetMessage("MAIN_OPT_CANCEL_TITLE") ?>" onclick="window.location='<? echo htmlspecialchars(CUtil::addslashes($_REQUEST["back_url_settings"])) ?>'">
			<input type="hidden" name="back_url_settings" value="<?= htmlspecialchars($_REQUEST["back_url_settings"]) ?>">
		<? endif ?>
		<input type="submit" name="RestoreDefaults" title="<? echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>" OnClick="confirm('<? echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')" value="<? echo GetMessage("MAIN_RESTORE_DEFAULTS") ?>">
		<?= bitrix_sessid_post(); ?>
		<? $tabControl->End(); ?>
	</form>
<? endif; ?>