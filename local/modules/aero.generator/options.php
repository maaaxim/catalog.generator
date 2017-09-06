<?
$moduleID = 'aero.generator';
$moduleRight = $APPLICATION->GetGroupRight($moduleID);
if ($moduleRight >= "R"):
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
	IncludeModuleLangFile(__FILE__);
	$arTabs = [
		[
			'tab' => [
				"DIV" => "Settings",
				"TAB" => GetMessage("AERO_CATALOG_CREATOR_TAB_SETTINGS"),
				"ICON" => $moduleID . "_settings",
				"TITLE" => GetMessage("AERO_CATALOG_CREATOR_TAB_SETTINGS_TITLE"),
            ],
			'options' => [
				["types_product",         GetMessage("AERO_CATALOG_CREATOR_TOTAL_PRODUCTS"), ["text", 24], 'default' => '1000'],
				["types_price",           GetMessage("AERO_CATALOG_CREATOR_TOTAL_PRICES"), ["text", 24], 'default' => '10'],
				["types_store",           GetMessage("AERO_CATALOG_CREATOR_TOTAL_STORES"), ["text", 24], 'default' => '100'],
				["types_productproperty", GetMessage("AERO_CATALOG_CREATOR_TOTAL_PRODUCT_PROPERTIES"), ["text", 24], 'default' => '100'],
				["types_skuproperty",     GetMessage("AERO_CATALOG_CREATOR_TOTAL_SKU_PROPERTIES"), ["text", 24], 'default' => '200'],
                ["sku_count",             GetMessage("AERO_CATALOG_CREATOR_SKU_PER_PRODUCT"), ["text", 24], 'default' => '10'],
				["count", GetMessage("AERO_CATALOG_CREATOR_OPT_COUNT"), ["text", 24], 'default' => '50'],
				["words_in_el_name", GetMessage("AERO_CATALOG_CREATOR_OPT_WORDS_IN_EL_NAME"), ["text", 24], 'default' => '5'],
				["preview_text_length", GetMessage("AERO_CATALOG_CREATOR_OPT_PREVIEW_TEXT_LENGTH"), ["text", 24], 'default' => '150'],
				["detail_text_length", GetMessage("AERO_CATALOG_CREATOR_OPT_DETAIL_TEXT_LENGTH"), ["text", 24], 'default' => '600'],
				["preview_picture_width", GetMessage("AERO_CATALOG_CREATOR_OPT_PREVIEW_PICTURE_WIDTH"), ["text", 24], 'default' => '300'],
				["preview_picture_height", GetMessage("AERO_CATALOG_CREATOR_OPT_PREVIEW_PICTURE_HEIGHT"), ["text", 24], 'default' => '200'],
				["detail_picture_width", GetMessage("AERO_CATALOG_CREATOR_OPT_DETAIL_PICTURE_WIDTH"), ["text", 24], 'default' => '800'],
				["detail_picture_height", GetMessage("AERO_CATALOG_CREATOR_OPT_DETAIL_PICTURE_HEIGHT"), ["text", 24], 'default' => '600'],
				["catalog_price_max_decimals", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_PRICE_MAX_DECIMALS"), ["text", 24], 'default' => '2'],
				["catalog_price_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_PRICE_MIN"), ["text", 24], 'default' => '0'],
				["catalog_price_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_PRICE_MAX"), ["text", 24], 'default' => '10000'],
				["catalog_weight_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_WEIGHT_MIN"), ["text", 24], 'default' => '0'],
				["catalog_weight_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_WEIGHT_MAX"), ["text", 24], 'default' => '100'],
				["catalog_width_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_WIDTH_MIN"), ["text", 24], 'default' => '0'],
				["catalog_width_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_WIDTH_MAX"), ["text", 24], 'default' => '100'],
				["catalog_length_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_LENGTH_MIN"), ["text", 24], 'default' => '0'],
				["catalog_length_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_LENGTH_MAX"), ["text", 24], 'default' => '100'],
				["catalog_height_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_HEIGHT_MIN"), ["text", 24], 'default' => '0'],
				["catalog_height_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_HEIGHT_MAX"), ["text", 24], 'default' => '100'],
				["catalog_quantity_min", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_QUANTITY_MIN"), ["text", 24], 'default' => '0'],
				["catalog_quantity_max", GetMessage("AERO_CATALOG_CREATOR_OPT_CATALOG_QUANTITY_MAX"), ["text", 24], 'default' => '100'],
				["property_multiple_count", GetMessage("AERO_CATALOG_CREATOR_OPT_PROPERTY_MULTIPLE_COUNT"), ["text", 24], 'default' => '3'],
				["property_string_length", GetMessage("AERO_CATALOG_CREATOR_OPT_PROPERTY_STRING_LENGTH"), ["text", 24], 'default' => '10'],
				["property_text_length", GetMessage("AERO_CATALOG_CREATOR_OPT_PROPERTY_TEXT_LENGTH"), ["text", 24], 'default' => '600'],
            ]
        ],
		[
			'tab' => [
				"DIV" => "edit2",
				"TAB" => GetMessage("MAIN_TAB_RIGHTS"),
				"ICON" => $moduleID . "_settings",
				"TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")
            ],
			'options' => [],
			'require' => "/bitrix/modules/main/admin/group_rights.php",
        ],
    ];
	$arTabsConfig = [];
	foreach ($arTabs as $tab)
		$arTabsConfig[] = $tab['tab'];
	$tabControl = new CAdminTabControl("tabControl", $arTabsConfig);
	if ($moduleRight >= "W" && $_SERVER['REQUEST_METHOD'] == "POST" && strlen($_POST['RestoreDefaults'] . $_POST['Update']) > 0 && check_bitrix_sessid()) {
		if ($_POST['RestoreDefaults']) {
			COption::RemoveOption($moduleID);
			$rsGroups = CGroup::GetList($v1 = "id", $v2 = "asc", ["ACTIVE" => "Y", "ADMIN" => "N"]);
			while ($arGroup = $rsGroups->Fetch())
				$APPLICATION->DelGroupRight($moduleID, [$arGroup["ID"]]);
		}
		if ($_POST['Update']) {
			foreach ($arTabs as $tab) {
				if (is_array($tab['options'])) {
					foreach ($tab['options'] as $option) {
						if (!is_array($option))
							continue;
						if (in_array($option[2][0], ['checkbox', 'text', 'textarea', 'selectbox'])) {
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
                    if (in_array($type[0], ['checkbox', 'text', 'textarea', 'selectbox'])) {
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
                                            [
                                                'REFERENCE' => array_values($arOption['values']),
                                                'REFERENCE_ID' => array_keys($arOption['values'])
                                            ],
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