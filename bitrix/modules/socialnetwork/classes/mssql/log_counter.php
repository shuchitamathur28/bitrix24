<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/log_counter.php");

class CSocNetLogCounter extends CAllSocNetLogCounter
{

	function Increment($log_id, $entity_type = false, $entity_id = false, $event_id = false, $created_by_id = false, $arOfEntities = false, $arAdmin = false, $transport = false, $visible = "Y", $type = "L")
	{
		global $DB;

		if (intval($log_id) <= 0)
			return false;

		$counter = new CSocNetLogCounter;

		$subSelect = $counter->GetSubSelect($log_id, $entity_type, $entity_id, $event_id, $created_by_id, $arOfEntities, $arAdmin, $transport, $visible, $type);
		if (strlen($subSelect) > 0)
		{
			$rsSubSelect = $DB->Query($subSelect);
			while($arCounter = $rsSubSelect->Fetch())
				CSocNetLogCounter::_Inc($arCounter["ID"], $arCounter["SITE_ID"], $arCounter["CODE"]);
		}

		$subSelect = $counter->GetSubSelect($log_id, $entity_type, $entity_id, $event_id, $created_by_id, $arOfEntities, $arAdmin, $transport, $visible, "group");
		if (strlen($subSelect) > 0)
		{
			$rsSubSelect = $DB->Query($subSelect);
			while($arCounter = $rsSubSelect->Fetch())
				CSocNetLogCounter::_Inc($arCounter["ID"], $arCounter["SITE_ID"], $arCounter["CODE"]);
		}
	}

	function _Inc($user_id, $site_id, $code)
	{
		global $DB;

		$user_id = intval($user_id);
		if ($user_id <= 0)
			return false;

		$rs = $DB->Query("
			SELECT CNT FROM b_sonet_log_counter
			WHERE USER_ID = ".$user_id."
			AND SITE_ID = '".$DB->ForSQL($site_id)."'
			AND CODE = '".$DB->ForSQL($code)."'
		");
		if ($rs->Fetch())
			$DB->Query("
				UPDATE b_sonet_log_counter SET
				CNT = CNT + 1
				WHERE USER_ID = ".$user_id."
				AND SITE_ID = '".$DB->ForSQL($site_id)."'
				AND CODE = '".$DB->ForSQL($code)."'
			");
		else
			$DB->Query("
				INSERT INTO b_sonet_log_counter
				(CNT, USER_ID, SITE_ID, CODE)
				VALUES
				(1, ".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."')
			", true);
	}

	function ClearByUser($user_id, $site_id = SITE_ID, $code = "**", $page_size = 0, $page_last_date_1 = "")
	{
		global $DB;

		$user_id = intval($user_id);
		if ($user_id <= 0)
			return false;

		$rs = $DB->Query("
			UPDATE b_sonet_log_counter SET
			CNT = 0, LAST_DATE = ".$DB->CurrentTimeFunction().(intval($page_size) > 0 ? ", PAGE_SIZE = ".$page_size : "").(strlen($page_last_date_1) > 0 ? ", PAGE_LAST_DATE_1 = ".$DB->CharToDateFunction($page_last_date_1) : "")."
			WHERE USER_ID = ".$user_id."
			AND SITE_ID = '".$DB->ForSQL($site_id)."'
			AND CODE = '".$DB->ForSQL($code)."'
		");
		if($rs->AffectedRowsCount() == 0)
			$DB->Query("
				INSERT INTO b_sonet_log_counter
				(CNT, LAST_DATE, USER_ID, SITE_ID, CODE, PAGE_SIZE, PAGE_LAST_DATE_1)
				VALUES
				(0, ".$DB->CurrentTimeFunction().", ".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."', ".(intval($page_size) > 0 ? $page_size : "NULL").", ".(strlen($page_last_date_1) > 0 ? $DB->CharToDateFunction($page_last_date_1) : "NULL").")
			", true);

		$rs = $DB->Query("
			DELETE FROM b_sonet_log_counter WHERE USER_ID = ".$user_id." AND CODE = '".$code."' AND SITE_ID = '**'
		");
	}

	function dbIF($condition, $yes, $no)
	{
		return "case when ".$condition." then ".$yes." else ".$no." end ";
	}

	public static function dbWeeksAgo($iWeeks)
	{
		return "DATEADD(week, -".intval($iWeeks).", GETDATE())";
	}

}
?>