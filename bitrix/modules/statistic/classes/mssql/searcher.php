<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/classes/general/searcher.php");

class CSearcher extends CAllSearcher
{
	public static function GetGraphArray_SQL($strSqlSearch)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$strSql = "
			SELECT
				".$DB->DateToCharFunction("D.DATE_STAT","SHORT")." DATE_STAT,
				datepart(day, D.DATE_STAT) DAY,
				datepart(month, D.DATE_STAT) MONTH,
				datepart(year, D.DATE_STAT) YEAR,
				D.SEARCHER_ID,
				D.TOTAL_HITS,
				C.NAME
			FROM
				b_stat_searcher_day D
			INNER JOIN b_stat_searcher C ON (C.ID = D.SEARCHER_ID)
			WHERE
				$strSqlSearch
			ORDER BY
				D.DATE_STAT, D.SEARCHER_ID
			";
		return $strSql;
	}

	public static function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered, $LIMIT=false)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array("S.ID <> 1");
		$arSqlSearch_h = Array();
		$strSqlSearch_h = "";
		$filter_period = false;
		$strSqlPeriod = "";
		$strT = "";
		if (is_array($arFilter))
		{
			ResetFilterLogic();
			$date1 = $arFilter["DATE1_PERIOD"];
			$date2 = $arFilter["DATE2_PERIOD"];
			$date_from = $DB->CharToDateFunction($date1, "SHORT");
			$date_to = "dateadd(second, 86400-1, ".$DB->CharToDateFunction($date2, "SHORT").")";
			if (CheckDateTime($date1) && strlen($date1)>0)
			{
				$filter_period = true;
				if (CheckDateTime($date2) && strlen($date2)>0)
				{
					$strSqlPeriod = "
						sum(case when D.DATE_STAT not between $date_from and $date_to then 0 else ";
					$strT=" end)";
				}
				else
				{
					$strSqlPeriod = "
						sum(case when D.DATE_STAT < $date_from then 0 else ";
					$strT=" end)";
				}
			}
			elseif (CheckDateTime($date2) && strlen($date2)>0)
			{
				$filter_period = true;
				$strSqlPeriod = "
						sum(case when D.DATE_STAT > $date_to then 0 else ";
				$strT=" end)";
			}

			foreach ($arFilter as $key => $val)
			{
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				else
				{
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
						continue;
				}
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("S.ID",$val,$match);
						break;
					case "ACTIVE":
					case "SAVE_STATISTIC":
					case "DIAGRAM_DEFAULT":
						$arSqlSearch[] = ($val=="Y") ? "S.".$key."='Y'" : "S.".$key."='N'";
						break;
					case "HITS1":
						$arSqlSearch_h[] = "(sum(isnull(D.TOTAL_HITS,0))+isnull(S.TOTAL_HITS,0))>='".intval($val)."'";
						break;
					case "HITS2":
						$arSqlSearch_h[] = "(sum(isnull(D.TOTAL_HITS,0))+isnull(S.TOTAL_HITS,0))<='".intval($val)."'";
						break;
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "max(D.DATE_LAST) >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "max(D.DATE_LAST) < dateadd(day, 1, ".$DB->CharToDateFunction($val, "SHORT").")";
						break;
					case "NAME":
					case "USER_AGENT":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("S.".$key, $val, $match);
						break;
				}
			}
		}

		if ($by == "s_id")
			$strSqlOrder = "ORDER BY S.ID";
		elseif ($by == "s_date_last")
			$strSqlOrder = "ORDER BY S_DATE_LAST";
		elseif ($by == "s_today_hits")
			$strSqlOrder = "ORDER BY TODAY_HITS";
		elseif ($by == "s_yesterday_hits")
			$strSqlOrder = "ORDER BY YESTERDAY_HITS";
		elseif ($by == "s_b_yesterday_hits")
			$strSqlOrder = "ORDER BY B_YESTERDAY_HITS";
		elseif ($by == "s_total_hits")
			$strSqlOrder = "ORDER BY TOTAL_HITS";
		elseif ($by == "s_period_hits")
			$strSqlOrder = "ORDER BY PERIOD_HITS";
		elseif ($by == "s_name")
			$strSqlOrder = "ORDER BY S.NAME";
		elseif ($by == "s_user_agent")
			$strSqlOrder = "ORDER BY S.USER_AGENT";
		elseif ($by == "s_chart")
			$strSqlOrder = "ORDER BY S.DIAGRAM_DEFAULT desc, TOTAL_HITS ";
		elseif ($by == "s_stat")
			$strSqlOrder = "ORDER BY TODAY_HITS desc, YESTERDAY_HITS desc, B_YESTERDAY_HITS desc, TOTAL_HITS desc, PERIOD_HITS";
		else
		{
			$by = "s_today_hits";
			$strSqlOrder = "ORDER BY TODAY_HITS desc, YESTERDAY_HITS desc, B_YESTERDAY_HITS desc, TOTAL_HITS desc, PERIOD_HITS";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		foreach($arSqlSearch_h as $sqlWhere)
			$strSqlSearch_h .= " and (".$sqlWhere.") ";

		$strSql = "
			SELECT /*TOP*/
				S.ID,
				S.USER_AGENT,
				S.DIAGRAM_DEFAULT,
				".$DB->DateToCharFunction("max(D.DATE_LAST)")." DATE_LAST,
				max(isnull(D.DATE_LAST,convert(datetime, '1980-01-01 00:00:00', 120))) S_DATE_LAST,
				sum(isnull(D.TOTAL_HITS,0))+isnull(S.TOTAL_HITS,0) TOTAL_HITS,
				sum(case when datediff(day, D.DATE_STAT, getdate())=0 then isnull(D.TOTAL_HITS,0) else 0 end) TODAY_HITS,
				sum(case when datediff(day, D.DATE_STAT, getdate())=1 then isnull(D.TOTAL_HITS,0) else 0 end) YESTERDAY_HITS,
				sum(case when datediff(day, D.DATE_STAT, getdate())=2 then isnull(D.TOTAL_HITS,0) else 0 end) B_YESTERDAY_HITS,
				".($filter_period ? $strSqlPeriod.'isnull(D.TOTAL_HITS,0)'.$strT.' PERIOD_HITS, ': '0 PERIOD_HITS,')."
				S.NAME
			FROM
				b_stat_searcher S
			LEFT JOIN b_stat_searcher_day D ON (D.SEARCHER_ID = S.ID)
			WHERE
			$strSqlSearch
			and S.ID<>1
			GROUP BY S.ID, S.NAME, S.ACTIVE, S.TOTAL_HITS, S.SAVE_STATISTIC, S.DIAGRAM_DEFAULT, S.USER_AGENT
			HAVING
				'1'='1'
				$strSqlSearch_h
			$strSqlOrder
		";

		$limit_sql = intval(COption::GetOptionString('statistic','RECORDS_LIMIT'));
		if (intval($LIMIT)>0) $limit_sql = intval($LIMIT);
		$strSql = str_replace("/*TOP*/", "TOP ".$limit_sql, $strSql);
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch) || $filter_period || strlen($strSqlSearch_h)>0);
		return $res;
	}

	public static function GetDropDownList($strSqlOrder="ORDER BY NAME, ID")
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				isnull(NAME,'')+' ['+cast(ID as varchar)+']' as REFERENCE
			FROM
				b_stat_searcher
			WHERE
				ID <> 1
			$strSqlOrder
			";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetDynamicList($SEARCHER_ID, &$by, &$order, &$arMaxMin, $arFilter=Array())
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$SEARCHER_ID = intval($SEARCHER_ID);
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				else
				{
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
						continue;
				}

				$key = strtoupper($key);
				switch($key)
				{
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "D.DATE_STAT >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "D.DATE_STAT < dateadd(day, 1, ".$DB->CharToDateFunction($val, "SHORT").")";
						break;
				}
			}
		}

		foreach($arSqlSearch as $sqlWhere)
			$strSqlSearch .= " and (".$sqlWhere.") ";

		if ($by == "s_date") $strSqlOrder = "ORDER BY D.DATE_STAT";
		else
		{
			$by = "s_date";
			$strSqlOrder = "ORDER BY D.DATE_STAT";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		$strSql =	"
			SELECT
				".$DB->DateToCharFunction("D.DATE_STAT","SHORT")."		DATE_STAT,
				datepart(day, D.DATE_STAT)								DAY,
				datepart(month, D.DATE_STAT)							MONTH,
				datepart(year, D.DATE_STAT)								YEAR,
				D.TOTAL_HITS
			FROM
				b_stat_searcher_day D
			WHERE
				D.SEARCHER_ID = $SEARCHER_ID
			$strSqlSearch
			$strSqlOrder
			";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		$strSql = "
			SELECT
				max(D.DATE_STAT)					DATE_LAST,
				min(D.DATE_STAT)					DATE_FIRST,
				datepart(day, max(D.DATE_STAT))		MAX_DAY,
				datepart(month, max(D.DATE_STAT))	MAX_MONTH,
				datepart(year, max(D.DATE_STAT))	MAX_YEAR,
				datepart(day, min(D.DATE_STAT))		MIN_DAY,
				datepart(month, min(D.DATE_STAT))	MIN_MONTH,
				datepart(year, min(D.DATE_STAT))	MIN_YEAR
			FROM
				b_stat_searcher_day D
			WHERE
				D.SEARCHER_ID = $SEARCHER_ID
			$strSqlSearch
			";
		$a = $DB->Query($strSql, false, $err_mess.__LINE__);
		$ar = $a->Fetch();
		$arMaxMin["MAX_DAY"]	= $ar["MAX_DAY"];
		$arMaxMin["MAX_MONTH"]	= $ar["MAX_MONTH"];
		$arMaxMin["MAX_YEAR"]	= $ar["MAX_YEAR"];
		$arMaxMin["MIN_DAY"]	= $ar["MIN_DAY"];
		$arMaxMin["MIN_MONTH"]	= $ar["MIN_MONTH"];
		$arMaxMin["MIN_YEAR"]	= $ar["MIN_YEAR"];
		return $res;
	}
}
