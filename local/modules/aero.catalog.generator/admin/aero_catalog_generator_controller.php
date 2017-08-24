<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

$APPLICATION->SetAdditionalCSS('/bitrix/panel/aero.catalog.generator/aero_catalog_generator.css');
\Bitrix\Main\Loader::includeModule("aero.catalog.generator");
\CJSCore::Init(array("aero_catalog_generator"));

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';
?>
<div id="progressbar-container">
    <form data-session="<?php echo md5(uniqid(rand())); ?>"  id="progress-starter" enctype="multipart/form-data" method="post" action="up.php">
        <input type="submit" value="Upload!" />
    </form>
    <div id="progressbar">
        <div class="pg-progressbar">
            <div class="pg-progress" id="pg-percent">
                <div class="pg-progressstyle"></div>
                <div class="pg-invertedtext" id="pg-text-1"></div>
            </div>
            <div class="pg-text" id="pg-text-2"></div>
        </div>
    </div>
    <div id="progressBar">
        <div id="progressDone"></div>
    </div>
</div>
<?

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';