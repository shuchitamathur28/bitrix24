<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(file_exists(WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/saas_tmp"))
{
	CopyDirFiles(
		WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/saas_tmp",
		WIZARD_SITE_PATH,
		$rewrite = false,
		$recursive = true,
		$delete_after_copy = true,
		$exclude = "bitrix"
	);

	unlink(WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/saas_tmp");
}
if(!(WIZARD_SITE_ID == 's1' && !WIZARD_NEW_2011 && WIZARD_FIRST_INSTAL !== "Y") || WIZARD_B24_TO_CP)
{
	if (WIZARD_INSTALL_DEMO_DATA || WIZARD_FIRST_INSTAL !== "Y" || WIZARD_B24_TO_CP)
	{
		CopyDirFiles(
			WIZARD_ABSOLUTE_PATH."/site/public/",
			WIZARD_SITE_PATH,
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false,
			$exclude = "bitrix"
		);

		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/intranet/install/public/',
			WIZARD_SITE_PATH,
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false,
			$exclude = "bitrix24"
		);

		WizardServices::SetFilePermission(Array(WIZARD_SITE_ID, WIZARD_SITE_DIR."pub/"), Array("*" => "R"));

		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/rest/install/public/marketplace/',
			WIZARD_SITE_PATH."/marketplace/",
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false
		);

		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/rest/install/public/apconnect/',
			WIZARD_SITE_PATH."/apconnect/",
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false
		);

		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/rest/install/public/oauth/',
			WIZARD_SITE_PATH."/oauth/",
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false
		);

		if (WIZARD_B24_TO_CP)
		{
			if (file_exists(WIZARD_SITE_PATH."settings/"))
				DeleteDirFilesEx(WIZARD_SITE_DIR."settings/");
			if (file_exists(WIZARD_SITE_PATH."marketplace/"))
				DeleteDirFilesEx(WIZARD_SITE_DIR."marketplace/");
			if (file_exists(WIZARD_SITE_PATH."bitrix/templates/login"))
				DeleteDirFilesEx(WIZARD_SITE_DIR."bitrix/templates/login");
			if (file_exists(WIZARD_SITE_PATH."company/meeting"))
				DeleteDirFilesEx(WIZARD_SITE_DIR."company/meeting");
		}
	}
}

if (WIZARD_SITE_ID == 's1' && !WIZARD_NEW_2011)
	CopyDirFiles(WIZARD_ABSOLUTE_PATH."/site/public/.department.menu_ext.php", WIZARD_SITE_PATH."/.department.menu_ext.php", false);

$dateTimeFormat = (LANGUAGE_ID == "en") ? "F j, Y h:i a" : ((LANGUAGE_ID == "de") ? "j. F Y H:i:s" : "d.m.Y H:i:s");
$dateFormat = (LANGUAGE_ID == "en") ? "F j, Y" : ((LANGUAGE_ID == "de") ? "j. F Y" : "d.m.Y");
$dateFormatNoYear = (LANGUAGE_ID == "en") ? "F j" : ((LANGUAGE_ID == "de") ? "j. F" : "d.m");
CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("SITE_DIR" => WIZARD_SITE_DIR, "DATE_TIME_FORMAT" => $dateTimeFormat, "DATE_FORMAT" => $dateFormat, "DATE_FORMAT_NO_YEAR" => $dateFormatNoYear));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/desktop.php", Array("SITE_ID" => WIZARD_SITE_ID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", Array("SITE_ID" => WIZARD_SITE_ID));

if (WIZARD_INSTALL_DEMO_DATA || WIZARD_B24_TO_CP)
{
	$arUrlRewrite = array();
	if (file_exists(WIZARD_SITE_ROOT_PATH."/urlrewrite.php"))
	{
		include(WIZARD_SITE_ROOT_PATH."/urlrewrite.php");
	}

	$arNewUrlRewrite = array(
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."company/gallery/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:photogallery_user",
			"PATH"	=>	WIZARD_SITE_DIR."company/gallery/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."company/personal/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:socialnetwork_user",
			"PATH"	=>	WIZARD_SITE_DIR."company/personal.php",
		),
		/*array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."community/forum/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:forum",
			"PATH"	=>	WIZARD_SITE_DIR."community/forum.php",
		),*/
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."about/gallery/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:photogallery",
			"PATH"	=>	WIZARD_SITE_DIR."about/gallery/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."workgroups/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:socialnetwork_group",
			"PATH"	=>	WIZARD_SITE_DIR."workgroups/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."services/lists/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:lists",
			"PATH"	=>	WIZARD_SITE_DIR."services/lists/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."services/faq/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:support.faq",
			"PATH"	=>	WIZARD_SITE_DIR."services/faq/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."services/idea/#",
			"RULE" => "",
			"ID" => "bitrix:idea",
			"PATH" => WIZARD_SITE_DIR."services/idea/index.php"
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."tasks/getfile/(\\d+)/(\\d+)/([^/]+)#",
			"RULE" => "taskid=$1&fileid=$2&filename=$3",
			"PATH" => WIZARD_SITE_DIR."tasks/getfile.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."docs/pub/#",
			"RULE" => "",
			"ID" => "bitrix:disk.external.link",
			"PATH" => WIZARD_SITE_DIR."docs/pub/extlinks.php"
		),

		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."/docs/all#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:disk.aggregator",
			"PATH"	=>	WIZARD_SITE_DIR."docs/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."docs/sale/#",
			"RULE" => "",
			"ID" => "bitrix:disk.common",
			"PATH" => WIZARD_SITE_DIR."docs/sale/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."docs/shared#",
			"RULE" => "",
			"ID" => "bitrix:disk.common",
			"PATH" => WIZARD_SITE_DIR."docs/shared/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."docs/manage/#",
			"RULE" => "",
			"ID" => "bitrix:disk.common",
			"PATH" => WIZARD_SITE_DIR."docs/manage/index.php",
		),
		array(
			'CONDITION' => '#^'.WIZARD_SITE_DIR.'bizproc/processes/#',
			'RULE' => '',
			'ID' => 'bitrix:lists',
			'PATH' => WIZARD_SITE_DIR.'bizproc/processes/index.php'
		),
		array(
			'CONDITION' => '#^'.WIZARD_SITE_DIR.'shop/settings/#',
			'RULE' => '',
			'ID' => 'bitrix:crm.admin.page.controller',
			'PATH' => WIZARD_SITE_DIR.'shop/settings/index.php'
		),
		array(
			'CONDITION' => '#^'.WIZARD_SITE_DIR.'sites/#',
			'RULE' => '',
			'ID' => 'bitrix:landing.start',
			'PATH' => WIZARD_SITE_DIR.'sites/index.php',
		),
		array(
			'CONDITION' => '#^'.WIZARD_SITE_DIR.'shop/stores/#',
			'RULE' => '',
			'ID' => 'bitrix:landing.start',
			'PATH' => WIZARD_SITE_DIR.'shop/stores/index.php'
		),
		array(
			'CONDITION' => '#^'.WIZARD_SITE_DIR.'shop/orders/#',
			'RULE' => '',
			'ID' => 'bitrix:crm.order',
			'PATH' => WIZARD_SITE_DIR.'shop/orders/index.php'
		),
		array(
			'CONDITION' => '#^'.WIZARD_SITE_DIR.'pub/form/([0-9a-z_]+?)/([0-9a-z]+?)/.*#',
			'RULE' => 'form_code=$1&sec=$2',
			'ID' => '',
			'PATH' => WIZARD_SITE_DIR.'pub/form.php'
		),
		array(
			'CONDITION' => '#^'.WIZARD_SITE_DIR.'pub/document/([0-9a-zA-Z_-]+)/([0-9a-zA-Z]+)/([^/]*)#',
			'RULE' => 'id=$1&hash=$2',
			'ID' => 'bitrix:documentgenerator.view',
			'PATH' => WIZARD_SITE_DIR.'pub/document.php'
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."onec/#",
			"RULE" => "",
			"ID" => "bitrix:crm.1c.start",
			"PATH" => WIZARD_SITE_DIR."onec/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."settings/configs/userconsent/#",
			"RULE" => "",
			"ID" => "",
			"PATH" => WIZARD_SITE_DIR."configs/userconsent.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."marketplace/#",
			"RULE" => "",
			"ID" => "bitrix:rest.marketplace",
			"PATH" =>  WIZARD_SITE_DIR."marketplace/index.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."marketplace/local/#",
			"RULE" => "",
			"ID" => "bitrix:rest.marketplace.localapp",
			"PATH" =>  WIZARD_SITE_DIR."marketplace/local/index.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."marketplace/app/#",
			"RULE" => "",
			"ID" => "bitrix:app.layout",
			"PATH" =>  WIZARD_SITE_DIR."marketplace/app/index.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."marketplace/hook/#",
			"RULE" => "",
			"ID" => "bitrix:rest.hook",
			"PATH" =>  WIZARD_SITE_DIR."marketplace/hook/index.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."mail/#",
			"RULE" => "",
			"ID" => "bitrix:mail.client",
			"PATH" =>  WIZARD_SITE_DIR."mail/index.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."timeman/worktime/#",
			"RULE" => "",
			"ID" => "bitrix:timeman.worktime",
			"PATH" =>  WIZARD_SITE_DIR."timeman/worktime.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."timeman/settings/#",
			"RULE" => "",
			"ID" => "bitrix:timeman.settings",
			"PATH" =>  WIZARD_SITE_DIR."timeman/settings.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."timeman/schedules/#",
			"RULE" => "",
			"ID" => "bitrix:timeman.schedules",
			"PATH" =>  WIZARD_SITE_DIR."timeman/schedules.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."shop/orderform/#",
			"RULE" => "",
			"ID" => "bitrix:crm.order.matcher",
			"PATH" => WIZARD_SITE_DIR."shop/orderform/index.php"
		),
		array(
			'CONDITION' => '#^'.WIZARD_SITE_DIR.'shop/buyer_group/#',
			'RULE' => '',
			'ID' => 'bitrix:crm.order.buyer_group',
			'PATH' => WIZARD_SITE_DIR.'shop/buyer_group/index.php'
		),
		array(
			'CONDITION' => '#^'.WIZARD_SITE_DIR.'shop/buyer/#',
			'RULE' => '',
			'ID' => 'bitrix:crm.order.buyer',
			'PATH' => WIZARD_SITE_DIR.'shop/buyer/index.php'
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."knowledge/group/#",
			"RULE" => "",
			"ID" => "bitrix:landing.pub",
			"PATH" => WIZARD_SITE_DIR."knowledge/group/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."knowledge/#",
			"RULE" => "",
			"ID" => "bitrix:landing.pub",
			"PATH" => WIZARD_SITE_DIR."knowledge/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."mobile/knowledge/group/#",
			"RULE" => "",
			"ID" => "bitrix:landing.pub",
			"PATH" => WIZARD_SITE_DIR."mobile/knowledge/group/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."mobile/knowledge/#",
			"RULE" => "",
			"ID" => "bitrix:landing.pub",
			"PATH" => WIZARD_SITE_DIR."mobile/knowledge/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."kb/group/#",
			"RULE" => "",
			"ID" => "bitrix:landing.start",
			"PATH" => WIZARD_SITE_DIR."kb/group/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."kb/#",
			"RULE" => "",
			"ID" => "bitrix:landing.start",
			"PATH" => WIZARD_SITE_DIR."kb/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."rpa/#",
			"RULE" => "",
			"ID" => "bitrix:rpa.router",
			"PATH" => WIZARD_SITE_DIR."rpa/index.php",
		),
	);
	foreach ($arNewUrlRewrite as $arUrl)
	{
		if (!in_array($arUrl, $arUrlRewrite))
		{
			\Bitrix\Main\UrlRewriter::add(WIZARD_SITE_ID, $arUrl);
		}
	}
}
?>