<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

use Bitrix\Main\ModuleManager;

if (!ModuleManager::isModuleInstalled("im"))
	return;

if(!(WIZARD_SITE_ID == 's1' && !WIZARD_NEW_2011 && WIZARD_FIRST_INSTAL !== "Y") || WIZARD_B24_TO_CP)
{
	if (WIZARD_INSTALL_DEMO_DATA || WIZARD_FIRST_INSTAL !== "Y" || WIZARD_B24_TO_CP)
	{
		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/im/install/public/',
			WIZARD_SITE_PATH,
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false
		);
		WizardServices::SetFilePermission(Array(WIZARD_SITE_ID, WIZARD_SITE_DIR."desktop_app/"), Array("*" => "R"));
		WizardServices::SetFilePermission(Array(WIZARD_SITE_ID, WIZARD_SITE_DIR."online/"), Array("*" => "R"));
	}
}

if (WIZARD_INSTALL_DEMO_DATA || WIZARD_B24_TO_CP)
{
	$arUrlRewrite = array();
	if (file_exists(WIZARD_SITE_ROOT_PATH."/urlrewrite.php"))
	{
		include(WIZARD_SITE_ROOT_PATH."/urlrewrite.php");
	}

	$arNewUrlRewrite = array(
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."online/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#",
			"RULE" => "alias=\$1",
			"ID" => "",
			"PATH" => WIZARD_SITE_DIR."desktop_app/router.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."online/(/?)([^/]*)#",
			"RULE" => "",
			"ID" => "",
			"PATH" => WIZARD_SITE_DIR."desktop_app/router.php",
		)
	);

	foreach ($arNewUrlRewrite as $arUrl)
	{
		if (!in_array($arUrl, $arUrlRewrite))
		{
			\Bitrix\Main\UrlRewriter::add(WIZARD_SITE_ID, $arUrl);
		}
	}
}
