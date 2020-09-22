<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/catalog_import.php");

class CCatalogImport extends CAllCatalogImport
{
	public static function Add($arFields)
	{
		global $DB;

		if (!CCatalogImport::CheckFields("ADD", $arFields))
			return false;

		$ID = (int)$DB->NextID('SQ_B_CATALOG_EXPORT');
		$arInsert = $DB->PrepareInsert("B_CATALOG_EXPORT", $arFields);

		$strSql = "insert into B_CATALOG_EXPORT(ID, ".$arInsert[0].") values(".$ID.", ".$arInsert[1].")";

		$arBinds = array();
		if (array_key_exists("SETUP_VARS", $arFields))
			$arBinds["SETUP_VARS"] = $arFields["SETUP_VARS"];
		$DB->QueryBind($strSql, $arBinds);

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		if (!CCatalogImport::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("B_CATALOG_EXPORT", $arFields);

		if (!empty($strUpdate))
		{
			$strSql = "update B_CATALOG_EXPORT set ".$strUpdate." where ID = ".$ID." and IS_EXPORT = 'N'";

			$arBinds = array();
			if (array_key_exists("SETUP_VARS", $arFields))
				$arBinds["SETUP_VARS"] = $arFields["SETUP_VARS"];
			$DB->QueryBind($strSql, $arBinds);
		}

		return $ID;
	}
}