<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

use Zend\ProgressBar\Adapter\JsPull;
use Zend\ProgressBar\ProgressBar;

if (isset($_REQUEST['session'])) {
    $iterator = (int) $_REQUEST["iterator"];
    if(empty($iterator)) die();
    $adapter = new JsPull();
    $progressBar = new ProgressBar($adapter, 0, 60, $_REQUEST['session']);
    if (60 === $iterator) {
        $progressBar->finish();
    } else {
        $progressBar->update($iterator);
    }
}

$APPLICATION->SetAdditionalCSS('/bitrix/panel/aero.generator/aero_generator.css');
\Bitrix\Main\Loader::includeModule("aero.generator");
\CJSCore::Init(array("aero_generator"));

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';
?>
<div id="progressbar-container">
    <form data-session="<?php echo md5(uniqid(rand())); ?>"  id="progress-starter" enctype="multipart/form-data" method="post" action="aero_generator_controller.php">
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