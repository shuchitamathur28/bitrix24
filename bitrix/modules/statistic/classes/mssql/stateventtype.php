<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/classes/general/stateventtype.php");

class CStatEventType extends CAllStatEventType
{
	public static function GetByID($ID)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$ID = intval($ID);
		$strSql =	"
			SELECT
				E.*,
				".$DB->DateToCharFunction("E.DATE_ENTER")."		DATE_ENTER,
				case when E.NAME is null or len(E.NAME)<=0 then isnull(E.EVENT1,'')+' / '+isnull(E.EVENT2,'') else E.NAME end	EVENT
			FROM
				b_stat_event E
			WHERE
				E.ID = '$ID'
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered, $LIMIT=false)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');

		$find_group = $arFilter["GROUP"];
		if($find_group!="event1" && $find_group!="event2" && $find_group!="total")
			$find_group="";

		$arSqlSearch = Array();
		$arSqlSearch_h = Array();
		$strSqlSearch_h = "";
		$filter_period = false;
		$strSqlPeriod = "";
		$strT = "";
		$CURRENCY = "";

		if (is_array($arFilter))
		{
			while (list($key, $value) = each($arFilter))
			{
				if ($key=="CURRENCY") $CURRENCY = $value;
			}
			reset($arFilter);
		}
		$rate = 1;
		$base_currency = GetStatisticBaseCurrency();
		$view_currency = $base_currency;
		if (strlen($base_currency)>0)
		{
			if (CModule::IncludeModule("currency"))
			{
				if ($CURRENCY!=$base_currency && strlen($CURRENCY)>0)
				{
					$rate = CCurrencyRates::GetConvertFactor($base_currency, $CURRENCY);
					$view_currency = $CURRENCY;
				}
			}
		}

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
						(case when D.DATE_STAT not between $date_from and $date_to then 0 else ";
					$strT=" end)";
				}
				else
				{
					$strSqlPeriod = "
						(case when D.DATE_STAT < $date_from then 0 else ";
					$strT=" end)";
				}
			}
			elseif (CheckDateTime($date2) && strlen($date2)>0)
			{
				$filter_period = true;
				$strSqlPeriod = "
						(case when D.DATE_STAT > $date_to then 0 else ";
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
						$arSqlSearch[] = GetFilterQuery("E.".$key,$val,$match);
						break;
					case "DATE_ENTER_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "E.DATE_ENTER >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_ENTER_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "E.DATE_ENTER < dateadd(day, 1, ".$DB->CharToDateFunction($val, "SHORT").")";
						break;
					case "DATE_LAST_1":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "max(D.DATE_LAST) >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_LAST_2":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "max(D.DATE_LAST) < dateadd(day, 1, ".$DB->CharToDateFunction($val, "SHORT").")";
						break;
					case "EVENT1":
					case "EVENT2":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.".$key,$val,$match);
						break;
					case "COUNTER1":
						if ($find_group!="")
							$arSqlSearch_h[] = "(isnull(sum(D.COUNTER),0)+isnull(sum(E.COUNTER),0))>='".intval($val)."'";
						else
							$arSqlSearch_h[] = "(sum(isnull(D.COUNTER,0))+isnull(E.COUNTER,0))>='".intval($val)."'";
						break;
					case "COUNTER2":
						if ($find_group!="")
							$arSqlSearch_h[] = "(isnull(sum(D.COUNTER),0)+isnull(sum(E.COUNTER),0))<='".intval($val)."'";
						else
							$arSqlSearch_h[] = "(sum(isnull(D.COUNTER,0))+isnull(E.COUNTER,0))<='".intval($val)."'";
						break;
					case "MONEY1":
						if ($find_group!="")
							$arSqlSearch_h[] = "round(((isnull(sum(D.MONEY),0)+ isnull(sum(E.MONEY),0)))*$rate,2)>='".roundDB($val)."'";
						else
							$arSqlSearch_h[] = "round(((sum(isnull(D.MONEY,0))+ isnull(E.MONEY,0)))*$rate,2)>='".roundDB($val)."'";
						break;
					case "MONEY2":
						if ($find_group!="")
							$arSqlSearch_h[] = "round(((isnull(sum(D.MONEY),0)+ isnull(sum(E.MONEY),0)))*$rate,2)<='".roundDB($val)."'";
						else
							$arSqlSearch_h[] = "round(((sum(isnull(D.MONEY,0))+ isnull(E.MONEY,0)))*$rate,2)<='".roundDB($val)."'";
						break;
					case "ADV_VISIBLE":
					case "DIAGRAM_DEFAULT":
						$arSqlSearch[] = ($val=="Y") ? "E.".$key."='Y'" : "E.".$key."='N'";
						break;
					case "DESCRIPTION":
					case "NAME":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.".$key,$val,$match);
						break;
					case "KEEP_DAYS1":
						$arSqlSearch[] = "E.KEEP_DAYS>='".intval($val)."'";
						break;
					case "KEEP_DAYS2":
						$arSqlSearch[] = "E.KEEP_DAYS<='".intval($val)."'";
						break;
					case "DYNAMIC_KEEP_DAYS1":
						$arSqlSearch[] = "E.DYNAMIC_KEEP_DAYS>='".intval($val)."'";
						break;
					case "DYNAMIC_KEEP_DAYS2":
						$arSqlSearch[] = "E.DYNAMIC_KEEP_DAYS<='".intval($val)."'";
						break;
				}
			}
		}

		if ($by == "s_id" && $find_group=="")			$strSqlOrder = "ORDER BY E.ID";
		elseif ($by == "s_date_last")				$strSqlOrder = "ORDER BY E_DATE_LAST";
		elseif ($by == "s_date_enter")				$strSqlOrder = "ORDER BY DATE_ENTER";
		elseif ($by == "s_today_counter")			$strSqlOrder = "ORDER BY TODAY_COUNTER";
		elseif ($by == "s_yesterday_counter")			$strSqlOrder = "ORDER BY YESTERDAY_COUNTER";
		elseif ($by == "s_b_yesterday_counter")			$strSqlOrder = "ORDER BY B_YESTERDAY_COUNTER";
		elseif ($by == "s_total_counter")			$strSqlOrder = "ORDER BY TOTAL_COUNTER";
		elseif ($by == "s_period_counter")			$strSqlOrder = "ORDER BY PERIOD_COUNTER";
		elseif ($by == "s_name" && $find_group=="")		$strSqlOrder = "ORDER BY E.NAME";
		elseif ($by == "s_event1" && $find_group=="")		$strSqlOrder = "ORDER BY E.EVENT1";
		elseif ($by == "s_event1" && $find_group=="event1")	$strSqlOrder = "ORDER BY E.EVENT1";
		elseif ($by == "s_event2" && $find_group=="")		$strSqlOrder = "ORDER BY E.EVENT2";
		elseif ($by == "s_event2" && $find_group=="event2")	$strSqlOrder = "ORDER BY E.EVENT2";
		elseif ($by == "s_event12" && $find_group=="")		$strSqlOrder = "ORDER BY E.EVENT1, E.EVENT2";
		elseif ($by == "s_chart" && $find_group=="")		$strSqlOrder = "ORDER BY E.DIAGRAM_DEFAULT desc, TOTAL_COUNTER ";
		elseif ($by == "s_stat")				$strSqlOrder = "ORDER BY TODAY_COUNTER desc, YESTERDAY_COUNTER desc, B_YESTERDAY_COUNTER desc, TOTAL_COUNTER desc, PERIOD_COUNTER";
		else
		{
			$by = "s_today_counter";
			$strSqlOrder = "ORDER BY TODAY_COUNTER desc, YESTERDAY_COUNTER desc, B_YESTERDAY_COUNTER desc, TOTAL_COUNTER desc, PERIOD_COUNTER";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

		foreach($arSqlSearch_h as $sqlWhere)
			$strSqlSearch_h .= " and (".$sqlWhere.") ";

		if ($find_group=="") // если группировка не выбрана
		{
			$strSql =	"
				SELECT /*TOP*/
					E.ID,
					E.EVENT1,
					E.EVENT2,
					E.COUNTER,
					E.DIAGRAM_DEFAULT,
					".$DB->DateToCharFunction("E.DATE_ENTER")."								DATE_ENTER,
					".$DB->DateToCharFunction("max(D.DATE_LAST)")."								DATE_LAST,
					max(isnull(D.DATE_LAST,convert(datetime, '1980-01-01 00:00:00', 120)))					E_DATE_LAST,
					sum(isnull(D.COUNTER,0))+isnull(E.COUNTER,0)								TOTAL_COUNTER,
					sum(round(isnull(D.MONEY,0)*$rate,2))+round(isnull(E.MONEY,0)*$rate,2)					TOTAL_MONEY,

					sum(case when datediff(day, D.DATE_STAT, getdate())=0 then isnull(D.COUNTER,0) else 0 end)		TODAY_COUNTER,
					sum(case when datediff(day, D.DATE_STAT, getdate())=1 then isnull(D.COUNTER,0) else 0 end)		YESTERDAY_COUNTER,
					sum(case when datediff(day, D.DATE_STAT, getdate())=2 then isnull(D.COUNTER,0) else 0 end)		B_YESTERDAY_COUNTER,
					sum(".($filter_period ? $strSqlPeriod.'isnull(D.COUNTER,0)'.$strT : 0).")				PERIOD_COUNTER,

					sum(round(case when datediff(day, D.DATE_STAT, getdate())=0 then isnull(D.MONEY,0) else 0 end*$rate,2))	TODAY_MONEY,
					sum(round(case when datediff(day, D.DATE_STAT, getdate())=1 then isnull(D.MONEY,0) else 0 end*$rate,2))	YESTERDAY_MONEY,
					sum(round(case when datediff(day, D.DATE_STAT, getdate())=2 then isnull(D.MONEY,0) else 0 end*$rate,2))	B_YESTERDAY_MONEY,
					sum(round(".($filter_period ? $strSqlPeriod.'isnull(D.MONEY,0)'.$strT : 0)."*$rate,2))			PERIOD_MONEY,
					E.NAME,
					E.DESCRIPTION,
					case when E.NAME is null or len(E.NAME)<=0 then isnull(E.EVENT1,'')+' / '+isnull(E.EVENT2,'') else E.NAME end	EVENT
				FROM
					b_stat_event E
				LEFT JOIN b_stat_event_day D ON (D.EVENT_ID=E.ID)
				WHERE
				$strSqlSearch
				GROUP BY
					E.ID, E.EVENT1, E.EVENT2, E.COUNTER, E.MONEY, E.DATE_ENTER, E.ADV_VISIBLE, E.NAME, E.DESCRIPTION, E.KEEP_DAYS, E.DYNAMIC_KEEP_DAYS, E.DIAGRAM_DEFAULT
				HAVING
					'1'='1'
					$strSqlSearch_h
				$strSqlOrder
				";
			$limit_sql = intval(COption::GetOptionString('statistic','RECORDS_LIMIT'));
			if (intval($LIMIT)>0) $limit_sql = intval($LIMIT);
			$strSql = str_replace("/*TOP*/", "TOP ".$limit_sql, $strSql);
		}
		elseif($find_group=="total")
		{
			$strSql = "
				SELECT
					min(CURRENCY)			CURRENCY,
					sum(TOTAL_COUNTER)		TOTAL_COUNTER,
					sum(TOTAL_MONEY)		TOTAL_MONEY,
					sum(TODAY_COUNTER)		TODAY_COUNTER,
					sum(YESTERDAY_COUNTER)		YESTERDAY_COUNTER,
					sum(B_YESTERDAY_COUNTER)	B_YESTERDAY_COUNTER,
					sum(PERIOD_COUNTER)		PERIOD_COUNTER,
					sum(TODAY_MONEY)		TODAY_MONEY,
					sum(YESTERDAY_MONEY)		YESTERDAY_MONEY,
					sum(B_YESTERDAY_MONEY)		B_YESTERDAY_MONEY,
					sum(PERIOD_MONEY)		PERIOD_MONEY
				FROM (
				SELECT
					'".$DB->ForSql($view_currency)."'											CURRENCY,
					sum(isnull(D.COUNTER,0))										TOTAL_COUNTER,
					sum(round(isnull(D.MONEY,0)*$rate,2))									TOTAL_MONEY,
					sum(case when datediff(day, D.DATE_STAT, getdate())=0 then isnull(D.COUNTER,0) else 0 end)		TODAY_COUNTER,
					sum(case when datediff(day, D.DATE_STAT, getdate())=1 then isnull(D.COUNTER,0) else 0 end)		YESTERDAY_COUNTER,
					sum(case when datediff(day, D.DATE_STAT, getdate())=2 then isnull(D.COUNTER,0) else 0 end)		B_YESTERDAY_COUNTER,
					sum(".($filter_period ? $strSqlPeriod.'isnull(D.COUNTER,0)'.$strT : 0).")				PERIOD_COUNTER,
					sum(round(case when datediff(day, D.DATE_STAT, getdate())=0 then isnull(D.MONEY,0) else 0 end*$rate,2))	TODAY_MONEY,
					sum(round(case when datediff(day, D.DATE_STAT, getdate())=1 then isnull(D.MONEY,0) else 0 end*$rate,2))	YESTERDAY_MONEY,
					sum(round(case when datediff(day, D.DATE_STAT, getdate())=2 then isnull(D.MONEY,0) else 0 end*$rate,2))	B_YESTERDAY_MONEY,
					sum(round(".($filter_period ? $strSqlPeriod.'isnull(D.MONEY,0)'.$strT : 0)."*$rate,2))			PERIOD_MONEY
				FROM
					b_stat_event E
					LEFT JOIN b_stat_event_day D ON (D.EVENT_ID=E.ID)
				WHERE
					$strSqlSearch
				HAVING
					'1'='1'
					$strSqlSearch_h
				UNION ALL
				SELECT
					'".$DB->ForSql($view_currency)."'			CURRENCY,
					sum(isnull(E.COUNTER,0))			TOTAL_COUNTER,
					sum(round(isnull(E.MONEY,0)*$rate,2))	TOTAL_MONEY,
					0					TODAY_COUNTER,
					0					YESTERDAY_COUNTER,
					0					B_YESTERDAY_COUNTER,
					0					PERIOD_COUNTER,
					0					TODAY_MONEY,
					0					YESTERDAY_MONEY,
					0					B_YESTERDAY_MONEY,
					0					PERIOD_MONEY
				FROM
					b_stat_event E
				WHERE
				$strSqlSearch
				) E
				";
		}
		else
		{
			if ($find_group=="event1") $group = "E.EVENT1"; else $group = "E.EVENT2";
			$strSql ="
				SELECT /*TOP*/
					$group,
					".$DB->DateToCharFunction("min(DATE_ENTER)")."		DATE_ENTER,
					".$DB->DateToCharFunction("max(DATE_LAST)")."		DATE_LAST,
					".$DB->DateToCharFunction("max(E_DATE_LAST)")."		E_DATE_LAST,
					sum(TOTAL_COUNTER)					TOTAL_COUNTER,
					sum(TOTAL_MONEY)					TOTAL_MONEY,
					sum(TODAY_COUNTER)					TODAY_COUNTER,
					sum(YESTERDAY_COUNTER)					YESTERDAY_COUNTER,
					sum(B_YESTERDAY_COUNTER)				B_YESTERDAY_COUNTER,
					sum(PERIOD_COUNTER)					PERIOD_COUNTER,
					sum(TODAY_MONEY)					TODAY_MONEY,
					sum(YESTERDAY_MONEY)					YESTERDAY_MONEY,
					sum(B_YESTERDAY_MONEY)					B_YESTERDAY_MONEY,
					sum(PERIOD_MONEY)					PERIOD_MONEY
				FROM
				(
				SELECT
					$group,
					0													COUNTER,
					min(E.DATE_ENTER)											DATE_ENTER,
					max(D.DATE_LAST)											DATE_LAST,
					max(isnull(D.DATE_LAST,convert(datetime, '1980-01-01 00:00:00', 120)))					E_DATE_LAST,
					sum(isnull(D.COUNTER,0))										TOTAL_COUNTER,
					sum(round(isnull(D.MONEY,0)*$rate,2))									TOTAL_MONEY,
					sum(case when datediff(day, D.DATE_STAT, getdate())=0 then isnull(D.COUNTER,0) else 0 end)		TODAY_COUNTER,
					sum(case when datediff(day, D.DATE_STAT, getdate())=1 then isnull(D.COUNTER,0) else 0 end)		YESTERDAY_COUNTER,
					sum(case when datediff(day, D.DATE_STAT, getdate())=2 then isnull(D.COUNTER,0) else 0 end)		B_YESTERDAY_COUNTER,
					sum(".($filter_period ? $strSqlPeriod.'isnull(D.COUNTER,0)'.$strT : 0).")				PERIOD_COUNTER,
					sum(round(case when datediff(day, D.DATE_STAT, getdate())=0 then isnull(D.MONEY,0) else 0 end*$rate,2))	TODAY_MONEY,
					sum(round(case when datediff(day, D.DATE_STAT, getdate())=1 then isnull(D.MONEY,0) else 0 end*$rate,2))	YESTERDAY_MONEY,
					sum(round(case when datediff(day, D.DATE_STAT, getdate())=2 then isnull(D.MONEY,0) else 0 end*$rate,2))	B_YESTERDAY_MONEY,
					sum(round(".($filter_period ? $strSqlPeriod.'isnull(D.MONEY,0)'.$strT : 0)."*$rate,2))			PERIOD_MONEY
				FROM
					b_stat_event E
					LEFT JOIN b_stat_event_day D ON (D.EVENT_ID=E.ID)
				WHERE
					$strSqlSearch
				GROUP BY
					$group
				HAVING
					'1'='1'
					$strSqlSearch_h
				UNION ALL
				SELECT
					$group,
					sum(isnull(E.COUNTER,0))			COUNTER,
					min(E.DATE_ENTER)				DATE_ENTER,
					convert(datetime, '1980-01-01 00:00:00', 120)	DATE_LAST,
					convert(datetime, '1980-01-01 00:00:00', 120)	E_DATE_LAST,
					sum(isnull(E.COUNTER,0))			TOTAL_COUNTER,
					sum(round(isnull(E.MONEY,0)*$rate,2))		TOTAL_MONEY,
					0						TODAY_COUNTER,
					0						YESTERDAY_COUNTER,
					0						B_YESTERDAY_COUNTER,
					0						PERIOD_COUNTER,
					0						TODAY_MONEY,
					0						YESTERDAY_MONEY,
					0						B_YESTERDAY_MONEY,
					0						PERIOD_MONEY
				FROM
					b_stat_event E
				WHERE
					$strSqlSearch
				GROUP BY
					$group
				) E
				GROUP BY
					$group
				$strSqlOrder
				";
			$limit_sql = intval(COption::GetOptionString('statistic','RECORDS_LIMIT'));
			if (intval($LIMIT)>0) $limit_sql = intval($LIMIT);
			$strSql = str_replace("/*TOP*/", "TOP ".$limit_sql, $strSql);
		}

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch) || $filter_period || strlen($strSqlSearch_h)>0);
		return $res;
	}

	public static function GetSimpleList(&$by, &$order, $arFilter=Array(), &$is_filtered)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array();
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
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("E.".$key, $val, $match);
						break;
					case "EVENT1":
					case "EVENT2":
					case "NAME":
					case "DESCRIPTION":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.".$key, $val, $match);
						break;
					case "KEYWORDS":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.EVENT1, E.EVENT2, E.DESCRIPTION, E.NAME",$val, $match);
						break;
				}
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$order= ($order!="desc") ? "asc" : "desc";
		if ($by == "s_id")				$strSqlOrder = "ORDER BY E.ID ".$order;
		elseif ($by == "s_event1")		$strSqlOrder = "ORDER BY E.EVENT1 ".$order.", E.EVENT2";
		elseif ($by == "s_event2")		$strSqlOrder = "ORDER BY E.EVENT2 ".$order;
		elseif ($by == "s_name")		$strSqlOrder = "ORDER BY E.NAME ".$order;
		elseif ($by == "s_description")	$strSqlOrder = "ORDER BY E.DESCRIPTION ".$order;
		else
		{
			$by = "s_event1";
			$strSqlOrder = "ORDER BY E.EVENT1 ".$order.", E.EVENT2";
		}
		$strSql =	"
			SELECT /*TOP*/
				E.ID, E.EVENT1, E.EVENT2, E.NAME, E.DESCRIPTION,
				case when E.NAME is null or len(E.NAME)<=0 then isnull(E.EVENT1,'')+' / '+isnull(E.EVENT2,'') else E.NAME end	EVENT
			FROM
				b_stat_event E
			WHERE
			$strSqlSearch
			$strSqlOrder
			";

		$strSql = str_replace("/*TOP*/", "TOP ".intval(COption::GetOptionString("statistic","RECORDS_LIMIT")), $strSql);
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}

	public static function GetDropDownList($strSqlOrder="ORDER BY ID, EVENT1, EVENT2")
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				'('+isnull(EVENT1,'')+' / '+isnull(EVENT2,'')+')'+isnull(NAME,'')+' ['+cast(ID as varchar)+']' as REFERENCE
			FROM
				b_stat_event
			$strSqlOrder
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetDynamicList($EVENT_ID, &$by, &$order, &$arMaxMin, $arFilter=Array())
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$EVENT_ID = intval($EVENT_ID);
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
				D.COUNTER
			FROM
				b_stat_event_day D
			WHERE
				D.EVENT_ID = $EVENT_ID
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
				b_stat_event_day D
			WHERE
				D.EVENT_ID = $EVENT_ID
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

	function GetGraphArray_SQL($strSqlSearch)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$strSql = "
			SELECT
				".$DB->DateToCharFunction("D.DATE_STAT","SHORT")."	DATE_STAT,
				datepart(day, D.DATE_STAT)							DAY,
				datepart(month, D.DATE_STAT)						MONTH,
				datepart(year, D.DATE_STAT)							YEAR,
				D.COUNTER,
				D.MONEY,
				D.EVENT_ID,
				E.NAME,
				E.EVENT1,
				E.EVENT2
			FROM
				b_stat_event_day D
			INNER JOIN b_stat_event E ON (E.ID = D.EVENT_ID)
			WHERE
				$strSqlSearch
			ORDER BY
				D.DATE_STAT, D.EVENT_ID
			";
		return $strSql;
	}
}
