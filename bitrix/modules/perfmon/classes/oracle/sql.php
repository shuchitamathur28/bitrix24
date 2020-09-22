<?php

class CPerfomanceSQL extends CAllPerfomanceSQL
{
	public static function _console_explain($strSQL)
	{
	}

	public static function Clear()
	{
		global $DB;
		$res = $DB->Query("TRUNCATE TABLE b_perf_sql_backtrace");
		if ($res)
			$res = $DB->Query("TRUNCATE TABLE b_perf_sql");
		return $res;
	}
}
