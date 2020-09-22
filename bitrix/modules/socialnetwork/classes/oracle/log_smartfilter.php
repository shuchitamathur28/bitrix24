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

		$strSQL = "
			MERGE INTO b_sonet_log_smartfilter USING (SELECT ".$user_id." USER_ID FROM dual)
			source ON
			(
				source.USER_ID = b_sonet_log_smartfilter.USER_ID
			)
			WHEN MATCHED THEN
				UPDATE SET b_sonet_log_smartfilter.TYPE = '".$type."' 
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, TYPE)
				VALUES (".$user_id.", '".$type."')
		";

		$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		
		if ($res)
			CPHPCache::Clean('sonet_smartfilter_default_'.$user_id, '/sonet/log_smartfilter/');
	}
}
?>