<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/delivery_handler.php");

/** @deprecated */
class CSaleDeliveryHandler extends CAllSaleDeliveryHandler
{
	/** @deprecated */
	function err_mess()
	{
		$module_id = "sale";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CSaleDeliveryHandler<br>File: ".__FILE__;
	}
}
?>