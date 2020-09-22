<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

use Bitrix\Main\Loader,
	Bitrix\Main\ModuleManager;

if (!ModuleManager::isModuleInstalled("mail"))
	return;

if(!(WIZARD_SITE_ID == 's1' && !WIZARD_NEW_2011 && WIZARD_FIRST_INSTAL !== "Y") || WIZARD_B24_TO_CP)
{
	if (WIZARD_INSTALL_DEMO_DATA || WIZARD_FIRST_INSTAL !== "Y" || WIZARD_B24_TO_CP)
	{
		Loader::includeModule('mail');

		$module = CModule::CreateModuleObject("mail");
		if (is_object($module) && method_exists($module, "installMailService"))
		{
			$module->installMailService(WIZARD_SITE_ID);
		}
	}
}