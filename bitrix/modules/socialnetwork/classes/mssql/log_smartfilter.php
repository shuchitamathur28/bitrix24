<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/log_smartfilter.php");

class CSocNetLogSmartFilter extends CAllSocNetLogSmartFilter
{
	function Set($user_id, $type)
	{
		global $DB;

		$user_id = intval($user_id);

		if ($user_id <= 0)
			return false;
			
		if ($type != "Y")
			$type = "N";

		$rs = $DB->Query("
			SELECT USER_ID FROM b_sonet_log_smartfilter
			WHERE USER_ID = ".$user_id."
		");
		if ($rs->Fetch())
			$res = $DB->Query("
				UPDATE b_sonet_log_smartfilter SET
				TYPE = '".$type."' 
				WHERE USER_ID = ".$user_id."
			");
		else
			$res = $DB->Query("
				INSERT INTO b_sonet_log_smartfilter
				(USER_ID, TYPE)
				VALUES
				(".$user_id.", '".$type."')
			", true);

		if ($res)
			CPHPCache::Clean('sonet_smartfilter_default_'.$user_id, '/sonet/log_smartfilter/');
	}
}
?>