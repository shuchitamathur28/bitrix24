<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var \Bitrix\Disk\Internals\BaseComponent $component */

?>
<div class="bx-disk-bizproc-section">
<?
$APPLICATION->IncludeComponent("bitrix:bizproc.task.list", "", Array(
	"USER_ID" => "", 
	"WORKFLOW_ID" => "", 
	"TASK_EDIT_URL" => $arResult["PATH_TO_DISK_TASK"],
	"PAGE_ELEMENTS" => 0, 
	"PAGE_NAVIGATION_TEMPLATE" => "",
	"SET_TITLE" => "Y",
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>
</div>