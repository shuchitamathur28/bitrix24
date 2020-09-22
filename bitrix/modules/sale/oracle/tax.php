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
		$ID = IntVal($DB->NextID("SQ_B_SALE_TAX"));
		$strSql =
			"INSERT INTO b_sale_tax(ID, ".$arInsert[0].", TIMESTAMP_X) ".
			"VALUES(".$ID.", ".$arInsert[1].", ".$DB->GetNowFunction().")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $ID;
	}
}
?>