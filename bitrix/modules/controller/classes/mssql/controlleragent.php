<?php

class CControllerAgent
{
	public static function CleanUp()
	{
		global $DB;
		$DB->Query("DELETE FROM b_controller_log WHERE TIMESTAMP_X < dateadd(day, -14, getdate())");
		$DB->Query("DELETE FROM b_controller_task WHERE STATUS<>'N' AND DATE_EXECUTE IS NOT NULL AND DATE_EXECUTE < dateadd(day, -14, getdate())");
		$DB->Query("DELETE FROM b_controller_command WHERE DATE_INSERT < dateadd(day, -14, getdate())");
		return "CControllerAgent::CleanUp();";
	}

	public static function _OrderBy($arOrder, $arFields)
	{
		$arOrderBy = array();
		foreach ($arOrder as $by => $order)
		{
			$by = strtoupper($by);
			if (isset($arFields[$by]))
				$arOrderBy[$by] = $arFields[$by]["FIELD_NAME"].' '.(strtolower($order) == 'desc'? 'desc': 'asc');
		}

		if (count($arOrderBy))
			return "ORDER BY ".implode(", ", $arOrderBy);
		else
			return "";
	}

	public static function _Lock($uniq)
	{
		global $DB;

		$i = 600; //10min lock
		$DB->Query("DELETE FROM B_OPTION WHERE MODULE_ID = 'controller' AND NAME = '".$DB->ForSQL($uniq)."_DBLock' AND SITE_ID IS NULL AND DATEDIFF(SECOND, CONVERT(DATETIME, DESCRIPTION), GETDATE()) > ".$i, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$DB->Query("SET LOCK_TIMEOUT 1", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$dbLock = $DB->Query("INSERT INTO B_OPTION(MODULE_ID, NAME, SITE_ID, VALUE, DESCRIPTION) VALUES ('controller', '".$DB->ForSQL($uniq)."_DBLock', NULL,  NULL, CONVERT(VARCHAR(128), GETDATE()))", true);
		$DB->Query("SET LOCK_TIMEOUT -1", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return ($dbLock !== false);
	}

	public static function _UnLock($uniq)
	{
		global $DB;
		$DB->Query("DELETE FROM B_OPTION WHERE MODULE_ID = 'controller' AND NAME = '".$DB->ForSQL($uniq)."_DBLock' AND SITE_ID IS NULL", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return true;
	}
}
