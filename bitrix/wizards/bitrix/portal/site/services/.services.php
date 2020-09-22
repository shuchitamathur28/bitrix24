<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arServices = Array(

	"search" => Array(
		"MODULE_ID" => "search",
		"NAME" => GetMessage("SERVICE_SEARCH"),
	),

	"files" => Array(
		"MODULE_ID" => "main",
		"NAME" => GetMessage("SERVICE_FILES"),
		"STAGES" => Array(
			"files.php",
			"bitrix.php",
		),
	),

	"main" => Array(
		"NAME" => GetMessage("SERVICE_MAIN_SETTINGS"),
		"STAGES" => Array(
			"site.php",
			"template.php", //Install template
			"theme.php", //Install theme
			"groups.php", //Create user groups
			"options.php", //Install module options
			"rating.php",
			"event.php", // install mail events
		),
	),

	"forum" => Array(
		"NAME" => GetMessage("SERVICE_FORUM"),
	),

	"support" => Array(
		"NAME" => GetMessage("SERVICE_SUPPORT"),
	),

	"iblock" => Array(
		"NAME" => GetMessage("SERVICE_COMPANY_STRUCTURE"),
		"STAGES" => Array(
			"types.php", //IBlock types

			"absence.php",
			"honour.php",
			"departments.php",
			"state_history.php",

			"clients.php",
			"master.php",
			"res.php",

			"user_photogallery.php",
			"group_photogallery.php",
		),
	),

	"iblock_demo_data" => Array(
		"MODULE_ID" => "iblock",
		"NAME" => GetMessage("SERVICE_IBLOCK_DEMO_DATA"),
		"STAGES" => Array(
			"absence.php",
			"state_history.php",
			"honour.php",
		),
	),

	"advertising" => Array(
		"NAME" => GetMessage("SERVICE_ADVERTISING"),
	),

	"vote" => Array(
		"NAME" => GetMessage("SERVICE_VOTE"),
	),

	"blog" => Array(
		"NAME" => GetMessage("SERVICE_BLOG"),
		"STAGES" => Array(
			"index.php",
		),
	),

	"intranet" => Array(
		"NAME" => GetMessage("SERVICE_INTRANET"),
		"STAGES" => Array(
			"index.php",
			"rating.php",
		)
	),

	"socialnetwork" => Array(
		"NAME" => GetMessage("SERVICE_SOCIALNETWORK"),
	),

	"tasks" => Array(
		"NAME" => GetMessage("SERVICE_TASKS"),
	),

	"workflow" => Array(
		"NAME" => GetMessage("SERVICE_WORKFLOW"),
	),

	"fileman" => Array(
		"NAME" => GetMessage("SERVICE_FILEMAN"),
	),

	"medialibrary" => Array(
		"NAME" => GetMessage("SERVICE_MEDIALIBRARY"),
		"MODULE_ID" => Array("fileman"),
		"STAGES" => Array("index.php"),
		"DESCRIPTION" => GetMessage("SERVICE_MEDIALIBRARY_DESC")
	),

	"statistic" => Array(
		"NAME" => GetMessage("SERVICE_STATISTIC"),
	),

	"lists" => Array(
		"NAME" => GetMessage("SERVICE_LISTS"),
	),
	
	"crm" => Array(
		"NAME" => GetMessage("SERVICE_CRM"),
	),
	"documentgenerator" => [
		"NAME" => GetMessage("SERVICE_DOCUMENTGENERATOR"),
	],
	"meeting" => Array(
		"NAME" => GetMessage("SERVICE_MEETING"),
	),
	"timeman" => Array(
		"NAME" => GetMessage("SERVICE_TIMEMAN"),
	),
	"xdimport" => Array(
		"NAME" => GetMessage("SERVICE_XDIMPORT"),
		"MODULE_ID" => Array("xdimport"),
		"STAGES" => Array("index.php"),
	),
	"calendar" => Array(
		"NAME" => GetMessage("SERVICE_CALENDAR"),
		"MODULE_ID" => Array("calendar")
	),
	"disk" => array(
		"NAME" => GetMessage("SERVICE_DISK"),
		"MODULE_ID" => Array("disk"),
		"STAGES" => Array("index.php"),
	),
	"marketing" => array(
		"NAME" => GetMessage("SERVICE_SENDER"),
		"MODULE_ID" => Array("sender"),
		"STAGES" => Array("index.php"),
	),
	"im" => array(
		"NAME" => GetMessage("SERVICE_IM"),
	),
	"mail" => array(
		"NAME" => GetMessage("SERVICE_MAIL"),
	),
);
?>