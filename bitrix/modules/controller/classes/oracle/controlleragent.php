<?php

class CControllerAgent
{
	public static function CleanUp()
	{
		global $DB;
		$DB->Query("DELETE FROM b_controller_log WHERE TIMESTAMP_X < (SYSDATE - 14)");
		$DB->Query("DELETE FROM b_controller_task WHERE STATUS<>'N' AND DATE_EXECUTE IS NOT NULL AND DATE_EXECUTE < (SYSDATE - 14)");
		$DB->Query("DELETE FROM b_controller_command WHERE DATE_INSERT < (SYSDATE - 14)");
		return "CControllerAgent::CleanUp();";
	}

	public static function _OrderBy($arOrder, $arFields)
	{
		$arOrderBy = array();
		foreach ($arOrder as $by => $order)
		{
			$by = strtoupper($by);
			if (isset($arFields[$by]))
				$arOrderBy[$by] = $arFields[$by]["FIELD_NAME"].' '.(strtolower($order) == 'desc'? 'desc NULLS LAST': 'asc NULLS FIRST');
		}

		if (count($arOrderBy))
			return "ORDER BY ".implode(", ", $arOrderBy);
		else
			return "";
	}

	public static function _Lock($uniq)
	{
		global $DB, $APPLICATION;
		$db_lock = $DB->Query("
			declare
				my_lock_id number;
				my_result number;
				lock_failed exception;
				pragma exception_init(lock_failed, -54);
			begin
				my_lock_id:=dbms_utility.get_hash_value(to_char('".$DB->ForSQL($uniq)."'), 0, 1024);
				my_result:=dbms_lock.request(my_lock_id, dbms_lock.x_mode, 0, true);
				--  Return value:
				--    0 - success
				--    1 - timeout
				--    2 - deadlock
				--    3 - parameter error
				--    4 - already own lock specified by 'id' or 'lockhandle'
				--    5 - illegal lockhandle
				if(my_result<>0 and my_result<>4)then
					raise lock_failed;
				end if;
			end;
		", true);
		if (!$db_lock && (strpos($DB->GetErrorMessage(), "ORA-00054") === false))
		{
			$APPLICATION->ThrowException($DB->GetErrorMessage());
		}
		return $db_lock !== false;
	}

	public static function _UnLock($uniq)
	{
		//lock released on commit
		return true;
	}

}
