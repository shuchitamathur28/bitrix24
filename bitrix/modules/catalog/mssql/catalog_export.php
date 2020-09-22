<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/catalog_export.php");

class CCatalogExport extends CAllCatalogExport
{
	public static function Add($arFields)
	{
		global $DB;

		if (!CCatalogExport::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("B_CATALOG_EXPORT", $arFields);

		$strSql = "insert into B_CATALOG_EXPORT(".$arInsert[0].") values(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = (int)$DB->LastID();

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		if (!CCatalogExport::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("B_CATALOG_EXPORT", $arFields);

		if (!empty($strUpdate))
		{
			$strSql = "update B_CATALOG_EXPORT set ".$strUpdate." where ID = ".$ID." and IS_EXPORT = 'Y'";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $ID;
	}
}