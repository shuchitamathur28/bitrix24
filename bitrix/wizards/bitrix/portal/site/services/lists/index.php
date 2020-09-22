<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("lists") || !CModule::IncludeModule("iblock"))
	return;

CLists::SetPermission('lists', array(1, WIZARD_PORTAL_ADMINISTRATION_GROUP));
CLists::SetPermission('bitrix_processes', array(1, WIZARD_PORTAL_ADMINISTRATION_GROUP));

if (!defined("ADDITIONAL_INSTALL"))
{
	COption::SetOptionString("lists", "livefeed_url", "/bizproc/processes/");
	COption::SetOptionString("lists", "livefeed_iblock_type_id", "bitrix_processes");
	COption::SetOptionString("lists", "socnet_iblock_type_id", "lists_socnet");
}

\Bitrix\Lists\Importer::installProcesses(LANGUAGE_ID);

CLists::EnableSocnet(true);
?>