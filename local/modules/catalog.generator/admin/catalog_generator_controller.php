<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';
\Bitrix\Main\Loader::includeModule("catalog.generator");

$steps = new \Catalog\Generator\Steps();

//$stepsCompleted = $steps->createNext();
//$progress = new \Catalog\Generator\JsonBar();
//$progress->advance($steps);
//$progress->finish();
//die();

if(\Catalog\Generator\JsonBar::isAjax()){
    $progress = new \Catalog\Generator\JsonBar();
    while($stepsCompleted = $steps->createNext()){
        $progress->advance($steps);
    }

    $progress->finish();
}

$APPLICATION->SetAdditionalCSS('/bitrix/panel/catalog.generator/catalog_generator.css');
\CJSCore::Init(["catalog_generator"]);

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';
?>
<div id="progressbar-container">
    <form data-step="<?=$steps->getCurrent();?>"  id="progress-starter" enctype="multipart/form-data" method="post" action="catalog_generator_controller.php">
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