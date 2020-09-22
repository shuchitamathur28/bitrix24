<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/tax.php");

class CSaleTax extends CAllSaleTax
{
	function Add($arFields)
	{
		global $DB;

		if (!CSaleTax::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_sale_tax", $arFields);

		$arX = explode(",", $arInsert[0]);
		if (is_array($arX)) { TrimArr($arX, true); $arX = array_flip($arX); }
		if (is_array($arX) && is_set($arX, "ID"))
		{
			$IDENTITY_INSERT = "ON";
			$DB->Query("SET IDENTITY_INSERT b_sale_tax ON", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		$strSql =
			"INSERT INTO b_sale_tax(".$arInsert[0].", TIMESTAMP_X) ".
			"VALUES(".$arInsert[1].", ".$DB->GetNowFunction().")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($IDENTITY_INSERT=="ON")
		{
			$IDENTITY_INSERT="OFF";
			$DB->Query("SET IDENTITY_INSERT b_sale_tax OFF", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		$ID = IntVal($DB->LastID());

		return $ID;
	}
}
?>