<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (WIZARD_IS_RERUN === true)
	return;

if(!CModule::IncludeModule("statistic"))
	return;

$APPLICATION->SetGroupRight("statistic", WIZARD_EXTRANET_ADMIN_GROUP, "W");
?>