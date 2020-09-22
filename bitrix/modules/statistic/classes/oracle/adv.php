<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/classes/general/adv.php");

class CAdv extends CAllAdv
{
	public static function GetAnalysisGraphArray_SQL($strSqlSearch, $DATA_TYPE)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		switch ($DATA_TYPE)
		{
			case "EVENT_SUMMA":
			case "EVENT":
			case "EVENT_BACK":
			case "EVENT_MONEY_SUMMA":
			case "EVENT_MONEY":
			case "EVENT_MONEY_BACK":
				$strSql = "
					SELECT
						".$DB->DateToCharFunction("D.DATE_STAT","SHORT")." DATE_STAT,
						to_char(D.DATE_STAT,'dd') DAY,
						to_char(D.DATE_STAT,'mm') MONTH,
						to_char(D.DATE_STAT,'yyyy') YEAR,
						sum(D.COUNTER) EVENTS,
						sum(D.COUNTER_BACK) EVENTS_BACK,
						sum(D.MONEY) MONEY,
						sum(D.MONEY_BACK) MONEY_BACK,
						D.ADV_ID,
						A.REFERER1,
						A.REFERER2
					FROM
						b_stat_adv_event_day D
					INNER JOIN b_stat_event E ON (E.ID = D.EVENT_ID)
					INNER JOIN b_stat_adv A ON (A.ID = D.ADV_ID)
					WHERE
						$strSqlSearch
					GROUP BY
						D.DATE_STAT, D.ADV_ID, A.REFERER1, A.REFERER2
					ORDER BY
						D.DATE_STAT
					";
				break;
			default:
				$strSql = "
					SELECT
						".$DB->DateToCharFunction("D.DATE_STAT","SHORT")."	DATE_STAT,
						to_char(D.DATE_STAT,'dd')				DAY,
						to_char(D.DATE_STAT,'mm')				MONTH,
						to_char(D.DATE_STAT,'yyyy')				YEAR,
						max(D.GUESTS_DAY)					GUESTS,
						max(D.NEW_GUESTS)					NEW_GUESTS,
						max(D.FAVORITES)					FAVORITES,
						max(D.C_HOSTS_DAY)					C_HOSTS,
						max(D.SESSIONS)						SESSIONS,
						max(D.HITS)						HITS,
						max(D.GUESTS_DAY_BACK)					GUESTS_BACK,
						max(D.FAVORITES_BACK)					FAVORITES_BACK,
						max(D.HOSTS_DAY_BACK)					HOSTS_BACK,
						max(D.SESSIONS_BACK)					SESSIONS_BACK,
						max(D.HITS_BACK)					HITS_BACK,
						D.ADV_ID,
						A.REFERER1,
						A.REFERER2
					FROM
						b_stat_adv_day D
					INNER JOIN b_stat_adv A ON (A.ID = D.ADV_ID)
					WHERE
						$strSqlSearch
					GROUP BY
						D.DATE_STAT, D.ADV_ID, A.REFERER1, A.REFERER2
					ORDER BY
						D.DATE_STAT
					";
				break;
		}
		return $strSql;
	}

	public static function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered, $limit="", &$arrGROUP_DAYS, &$strSql_result)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$find_group = $arFilter["GROUP"];
		$arSqlSearch = Array();
		$arSqlSearch_h = Array();
		$strSqlSearch_h = "";
		$filter_period = false;
		$strSqlPeriod = "";
		$strT = "";
		$CURRENCY = "";

		if (is_array($arFilter))
		{
			$date1 = $arFilter["DATE1_PERIOD"];
			$date2 = $arFilter["DATE2_PERIOD"];
			$date_from = "TO_DATE('".ConvertDateTime($date1,"D.M.Y")." 00:00:00','dd.mm.yyyy hh24:mi:ss')";
			$date_to = "TO_DATE('".ConvertDateTime($date2,"D.M.Y")." 23:59:59','dd.mm.yyyy hh24:mi:ss')";
			if (strlen($date1)>0)
			{
				$filter_period = true;
				if (strlen($date2)>0)
				{
					$strSqlPeriod = "sum(decode(sign(D.DATE_STAT-$date_from),-1,0, decode(sign(D.DATE_STAT-$date_to),1,0,";
					$strT=")))";
				}
				else
				{
					$strSqlPeriod = "sum(decode(sign(D.DATE_STAT-$date_from),-1,0,";
					$strT="))";
				}
			}
			elseif (strlen($date2)>0)
			{
				$filter_period = true;
				$strSqlPeriod = "sum(decode(sign(D.DATE_STAT-$date_to),1,0,";
				$strT="))";
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
						$arSqlSearch[] = GetFilterQuery("A.".$key,$val,$match);
						break;
					case "DATE1_FIRST":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "C_TIME_FIRST >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2_FIRST":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "C_TIME_FIRST < ".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
					case "DATE1_LAST":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "C_TIME_LAST >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2_LAST":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "C_TIME_LAST < ".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
					case "REFERER1":
					case "REFERER2":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("A.".$key, $val, $match);
						break;
					case "PRIORITY1":
						$arSqlSearch[] = "A.PRIORITY>='".intval($val)."'";
						break;
					case "PRIORITY2":
						$arSqlSearch[] = "A.PRIORITY<='".intval($val)."'";
						break;
					case "NEW_GUESTS1":
						$arSqlSearch_h[] = "NEW_GUESTS>='".intval($val)."'";
						break;
					case "NEW_GUESTS2":
						$arSqlSearch_h[] = "NEW_GUESTS<='".intval($val)."'";
						break;
					case "GUESTS1":
						if ($arFilter["GUESTS_BACK"]=="Y")
							$arSqlSearch_h[] = "GUESTS_BACK>='".intval($val)."'";
						else
							$arSqlSearch_h[] = "GUESTS>='".intval($val)."'";
						break;
					case "GUESTS2":
						if ($arFilter["GUESTS_BACK"]=="Y")
							$arSqlSearch_h[] = "GUESTS_BACK<='".intval($val)."'";
						else
							$arSqlSearch_h[] = "GUESTS<='".intval($val)."'";
						break;
					case "FAVORITES1":
						if ($arFilter["FAVORITES_BACK"]=="Y")
							$arSqlSearch_h[] = "FAVORITES_BACK>='".intval($val)."'";
						else
							$arSqlSearch_h[] = "FAVORITES>='".intval($val)."'";
						break;
					case "FAVORITES2":
						if ($arFilter["FAVORITES_BACK"]=="Y")
							$arSqlSearch_h[] = "FAVORITES_BACK<='".intval($val)."'";
						else
							$arSqlSearch_h[] = "FAVORITES<='".intval($val)."'";
						break;
					case "HOSTS1":
						if ($arFilter["HOSTS_BACK"]=="Y")
							$arSqlSearch_h[] = "HOSTS_BACK>='".intval($val)."'";
						else
							$arSqlSearch_h[] = "C_HOSTS>='".intval($val)."'";
						break;
					case "HOSTS2":
						if ($arFilter["HOSTS_BACK"]=="Y")
							$arSqlSearch_h[] = "HOSTS_BACK<='".intval($val)."'";
						else
							$arSqlSearch_h[] = "C_HOSTS<='".intval($val)."'";
						break;
					case "SESSIONS1":
						if ($arFilter["SESSIONS_BACK"]=="Y")
							$arSqlSearch_h[] = "SESSIONS_BACK>='".intval($val)."'";
						else
							$arSqlSearch_h[] = "SESSIONS>='".intval($val)."'";
						break;
					case "SESSIONS2":
						if ($arFilter["SESSIONS_BACK"]=="Y")
							$arSqlSearch_h[] = "SESSIONS_BACK<='".intval($val)."'";
						else
							$arSqlSearch_h[] = "SESSIONS<='".intval($val)."'";
						break;
					case "HITS1":
						if ($arFilter["HITS_BACK"]=="Y")
							$arSqlSearch_h[] = "HITS_BACK>='".intval($val)."'";
						else
							$arSqlSearch_h[] = "HITS>='".intval($val)."'";
						break;
					case "HITS2":
						if ($arFilter["HITS_BACK"]=="Y")
							$arSqlSearch_h[] = "HITS_BACK<='".intval($val)."'";
						else
							$arSqlSearch_h[] = "HITS<='".intval($val)."'";
						break;
					case "COST1":
						$arSqlSearch_h[] = "COST>='".doubleval($val)."'";
						break;
					case "COST2":
						$arSqlSearch_h[] = "COST<='".doubleval($val)."'";
						break;
					case "REVENUE1":
						$arSqlSearch_h[] = "REVENUE>='".doubleval($val)."'";
						break;
					case "REVENUE2":
						$arSqlSearch_h[] = "REVENUE<='".doubleval($val)."'";
						break;
					case "BENEFIT1":
						$arSqlSearch_h[] = "BENEFIT>='".doubleval($val)."'";
						break;
					case "BENEFIT2":
						$arSqlSearch_h[] = "BENEFIT<='".doubleval($val)."'";
						break;
					case "ROI1":
						$arSqlSearch_h[] = "ROI>='".doubleval($val)."'";
						break;
					case "ROI2":
						$arSqlSearch_h[] = "ROI<='".doubleval($val)."'";
						break;
					case "ATTENT1":
						if ($arFilter["ATTENT_BACK"]=="Y")
							$arSqlSearch_h[] = "ATTENT_BACK>='".doubleval($val)."'";
						else
							$arSqlSearch_h[] = "ATTENT>='".doubleval($val)."'";
						break;
						break;
					case "ATTENT2":
						if ($arFilter["ATTENT_BACK"]=="Y")
							$arSqlSearch_h[] = "ATTENT_BACK<='".doubleval($val)."'";
						else
							$arSqlSearch_h[] = "ATTENT<='".doubleval($val)."'";
						break;
						break;
					case "VISITORS_PER_DAY1":
						$arSqlSearch_h[] = "VISITORS_PER_DAY>='".doubleval($val)."'";
						break;
					case "VISITORS_PER_DAY2":
						$arSqlSearch_h[] = "VISITORS_PER_DAY<='".doubleval($val)."'";
						break;
					case "DURATION1":
						$arSqlSearch_h[] = "ADV_TIME>=".doubleval($val)."*86400";
						break;
					case "DURATION2":
						$arSqlSearch_h[] = "ADV_TIME<=".doubleval($val)."*86400";
						break;
					case "CURRENCY":
						$CURRENCY = $val;
						break;
					case "DESCRIPTION":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("A.".$key, $val, $match);
						break;
				}
			}
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

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		foreach($arSqlSearch_h as $sqlWhere)
			$strSqlSearch_h .= " and (".$sqlWhere.") ";

		$group = false;
		$find_group = (strlen($find_group)<=0) ? "NOT_REF" : $find_group;

		$arrFields_1 = array(
				"C_TIME_FIRST", "C_TIME_LAST", "CURRENCY",
				"DATE_FIRST", "DATE_LAST", "ADV_TIME",
				"GUESTS", "NEW_GUESTS", "FAVORITES",
				"C_HOSTS", "SESSIONS", "HITS",
				"GUESTS_BACK", "FAVORITES_BACK", "HOSTS_BACK",
				"SESSIONS_BACK", "HITS_BACK", "ATTENT",
				"ATTENT_BACK", "NEW_VISITORS", "RETURNED_VISITORS",
				"VISITORS_PER_DAY", "COST", "REVENUE",
				"BENEFIT", "SESSION_COST", "VISITOR_COST", "ROI",
			);
		if ($find_group=="referer1") array_push($arrFields_1, "REFERER1");
		if ($find_group=="referer2") array_push($arrFields_1, "REFERER2");

		$arrFields_2 = array(
				"GUESTS_TODAY", "NEW_GUESTS_TODAY", "FAVORITES_TODAY",
				"C_HOSTS_TODAY", "SESSIONS_TODAY", "HITS_TODAY",
				"GUESTS_BACK_TODAY", "FAVORITES_BACK_TODAY", "HOSTS_BACK_TODAY",
				"SESSIONS_BACK_TODAY", "HITS_BACK_TODAY", "GUESTS_YESTERDAY",
				"NEW_GUESTS_YESTERDAY", "FAVORITES_YESTERDAY", "C_HOSTS_YESTERDAY",
				"SESSIONS_YESTERDAY", "HITS_YESTERDAY", "GUESTS_BACK_YESTERDAY",
				"FAVORITES_BACK_YESTERDAY", "HOSTS_BACK_YESTERDAY", "SESSIONS_BACK_YESTERDAY",
				"HITS_BACK_YESTERDAY", "GUESTS_BEF_YESTERDAY", "NEW_GUESTS_BEF_YESTERDAY",
				"FAVORITES_BEF_YESTERDAY", "C_HOSTS_BEF_YESTERDAY", "SESSIONS_BEF_YESTERDAY",
				"HITS_BEF_YESTERDAY", "GUESTS_BACK_BEF_YESTERDAY", "FAVORITES_BACK_BEF_YESTERDAY",
				"HOSTS_BACK_BEF_YESTERDAY", "SESSIONS_BACK_BEF_YESTERDAY", "HITS_BACK_BEF_YESTERDAY",
				"A.ID", "REFERER1", "REFERER2",
				"A.PRIORITY", "A.EVENTS_VIEW", "A.DESCRIPTION",
				"GUESTS_PERIOD", "C_HOSTS_PERIOD", "NEW_GUESTS_PERIOD",
				"FAVORITES_PERIOD", "SESSIONS_PERIOD", "HITS_PERIOD",
				"GUESTS_BACK_PERIOD", "HOSTS_BACK_PERIOD", "FAVORITES_BACK_PERIOD",
				"SESSIONS_BACK_PERIOD", "HITS_BACK_PERIOD",
			);

		$arrFields = $arrFields_1;
		if ($find_group=="NOT_REF")
			$arrFields = array_merge($arrFields, $arrFields_2);

		if ($order!="asc")
			$order = "desc";

		$key = array_search(strtoupper($by),$arrFields);
		if ($key===NULL || $key===false)
			$key = array_search("A.".strtoupper($by),$arrFields);
		if ($key!==NULL && $key!==false)
			$strSqlOrder = " ORDER BY ".$arrFields[$key];
		elseif ($by == "s_dropdown")
			$strSqlOrder = "ORDER BY A.ID desc, A.REFERER1, A.REFERER2";
		elseif ($by == "s_referers")
			$strSqlOrder = "ORDER BY A.REFERER1, A.REFERER2";
		else
		{
			if ($find_group=="NOT_REF")
			{
				$strSqlOrder = " ORDER BY SESSIONS_TODAY $order, SESSIONS_YESTERDAY $order, SESSIONS_BEF_YESTERDAY $order, SESSIONS_PERIOD $order, SESSIONS ";
			}
			else
			{
				$strSqlOrder = " ORDER BY SESSIONS ";
			}
			$by = "BY_DEFAULT";
		}
		$strSqlOrder .= " ".$order;

		$limit = (intval($limit)>0) ? intval($limit) : intval(COption::GetOptionString('statistic','RECORDS_LIMIT'));

		$sqlDays = "
			-- TODAY
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate),nvl(D.GUESTS_DAY,0),0))		GUESTS_TODAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate),nvl(D.NEW_GUESTS,0),0))		NEW_GUESTS_TODAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate),nvl(D.FAVORITES,0),0))			FAVORITES_TODAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate),nvl(D.C_HOSTS_DAY,0),0))		C_HOSTS_TODAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate),nvl(D.SESSIONS,0),0))			SESSIONS_TODAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate),nvl(D.HITS,0),0))				HITS_TODAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate),nvl(D.GUESTS_DAY_BACK,0),0))	GUESTS_BACK_TODAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate),nvl(D.FAVORITES_BACK,0),0))	FAVORITES_BACK_TODAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate),nvl(D.HOSTS_DAY_BACK,0),0))	HOSTS_BACK_TODAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate),nvl(D.SESSIONS_BACK,0),0))		SESSIONS_BACK_TODAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate),nvl(D.HITS_BACK,0),0))			HITS_BACK_TODAY,

			-- YESTERDAY
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-1),nvl(D.GUESTS_DAY,0),0))		GUESTS_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-1),nvl(D.NEW_GUESTS,0),0))		NEW_GUESTS_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-1),nvl(D.FAVORITES,0),0))		FAVORITES_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-1),nvl(D.C_HOSTS_DAY,0),0))		C_HOSTS_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-1),nvl(D.SESSIONS,0),0))		SESSIONS_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-1),nvl(D.HITS,0),0))			HITS_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-1),nvl(D.GUESTS_DAY_BACK,0),0))	GUESTS_BACK_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-1),nvl(D.FAVORITES_BACK,0),0))	FAVORITES_BACK_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-1),nvl(D.HOSTS_DAY_BACK,0),0))	HOSTS_BACK_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-1),nvl(D.SESSIONS_BACK,0),0))	SESSIONS_BACK_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-1),nvl(D.HITS_BACK,0),0))		HITS_BACK_YESTERDAY,

			-- THE DAY BEFORE YESTERDAY
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-2),nvl(D.GUESTS_DAY,0),0))		GUESTS_BEF_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-2),nvl(D.NEW_GUESTS,0),0))		NEW_GUESTS_BEF_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-2),nvl(D.FAVORITES,0),0))		FAVORITES_BEF_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-2),nvl(D.C_HOSTS_DAY,0),0))		C_HOSTS_BEF_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-2),nvl(D.SESSIONS,0),0))		SESSIONS_BEF_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-2),nvl(D.HITS,0),0))			HITS_BEF_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-2),nvl(D.GUESTS_DAY_BACK,0),0))	GUESTS_BACK_BEF_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-2),nvl(D.FAVORITES_BACK,0),0))	FAVORITES_BACK_BEF_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-2),nvl(D.HOSTS_DAY_BACK,0),0))		HOSTS_BACK_BEF_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-2),nvl(D.SESSIONS_BACK,0),0))	SESSIONS_BACK_BEF_YESTERDAY,
			sum(decode(trunc(D.DATE_STAT),trunc(sysdate-2),nvl(D.HITS_BACK,0),0))		HITS_BACK_BEF_YESTERDAY,
		";

		if ($find_group=="NOT_REF") // no grouping
		{
			$strSql =	"
				SELECT
					A.ID, A.REFERER1, A.REFERER2, A.PRIORITY, A.EVENTS_VIEW, A.DESCRIPTION,
					A.DATE_FIRST C_TIME_FIRST,
					A.DATE_LAST C_TIME_LAST,
					'".$DB->ForSql($view_currency)."' CURRENCY,
					".$DB->DateToCharFunction("A.DATE_FIRST","SHORT")." DATE_FIRST,
					".$DB->DateToCharFunction("A.DATE_LAST","SHORT")." DATE_LAST,
					ROUND(86400*(A.DATE_LAST-A.DATE_FIRST)) ADV_TIME,
					$sqlDays

				-- PERIOD
				".($filter_period ? $strSqlPeriod.'nvl(D.GUESTS,0)'.$strT : 'A.GUESTS')." GUESTS_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.C_HOSTS,0)'.$strT  : 'A.C_HOSTS')." C_HOSTS_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.NEW_GUESTS,0)'.$strT : 'A.NEW_GUESTS')." NEW_GUESTS_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.FAVORITES,0)'.$strT : 'A.FAVORITES')." FAVORITES_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.SESSIONS,0)'.$strT : 'A.SESSIONS')." SESSIONS_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.HITS,0)'.$strT : 'A.HITS')." HITS_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.GUESTS_DAY_BACK,0)'.$strT : 'A.GUESTS_BACK')." GUESTS_BACK_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.HOSTS_DAY_BACK,0)'.$strT : 'A.HOSTS_BACK')." HOSTS_BACK_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.FAVORITES_BACK,0)'.$strT : 'A.FAVORITES')." FAVORITES_BACK_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.SESSIONS_BACK,0)'.$strT : 'A.SESSIONS_BACK')." SESSIONS_BACK_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.HITS_BACK,0)'.$strT : 'A.HITS_BACK')." HITS_BACK_PERIOD,

				-- TOTAL
				A.GUESTS, A.NEW_GUESTS, A.FAVORITES, A.C_HOSTS, A.SESSIONS, A.HITS, A.GUESTS_BACK, A.FAVORITES_BACK, A.HOSTS_BACK, A.SESSIONS_BACK, A.HITS_BACK,

				-- AUDIENCE
				decode(sign(A.SESSIONS),1,round(A.HITS/A.SESSIONS,2),-1) ATTENT,
				decode(sign(A.SESSIONS_BACK),1,round(A.HITS_BACK/A.SESSIONS_BACK,2),-1) ATTENT_BACK,
				decode(sign(A.GUESTS),1,round((A.NEW_GUESTS/A.GUESTS)*100,2),-1) NEW_VISITORS,
				decode(sign(A.GUESTS),1,round((A.GUESTS_BACK/A.GUESTS)*100,2),-1) RETURNED_VISITORS,
				decode(sign(ROUND(A.DATE_LAST-A.DATE_FIRST)),
					1,round(A.GUESTS/ROUND(A.DATE_LAST-A.DATE_FIRST),2),
					'-1')	VISITORS_PER_DAY,

				-- FINANCES
				round(round(A.COST,2)*$rate,2) COST,
				round(round(A.REVENUE,2)*$rate,2) REVENUE,
				round(round(A.REVENUE-A.COST,2)*$rate,2) BENEFIT,
				round(round(decode(sign(A.SESSIONS),1,A.COST/A.SESSIONS,0),2)*$rate,2) SESSION_COST,
				round(round(decode(sign(A.GUESTS),1,A.COST/A.GUESTS,0),2)*$rate,2) VISITOR_COST,
				decode(sign(A.COST),1,round(((A.REVENUE-A.COST)/A.COST)*100,2),-1) ROI

				FROM
					b_stat_adv A
				LEFT JOIN b_stat_adv_day D ON (D.ADV_ID = A.ID)
				WHERE
					$strSqlSearch
				GROUP BY
					A.ID, A.REFERER1, A.REFERER2, A.COST, A.REVENUE, A.PRIORITY, A.EVENTS_VIEW, A.DESCRIPTION, A.DATE_FIRST, A.DATE_LAST, A.GUESTS, A.NEW_GUESTS, A.FAVORITES, A.C_HOSTS, A.SESSIONS, A.HITS, A.GUESTS_BACK, A.FAVORITES_BACK, A.HOSTS_BACK, A.SESSIONS_BACK, A.HITS_BACK
				$strSqlOrder
			";

		}
		else
		{
			if ($find_group=="referer1")
				$group = "REFERER1";
			else
				$group = "REFERER2";

			// total data
			$strSql =	"
				SELECT
					A.$group,
					min(A.DATE_LAST)											C_TIME_FIRST,
					max(A.DATE_LAST)											C_TIME_LAST,
					'".$DB->ForSql($view_currency)."'											CURRENCY,
					".$DB->DateToCharFunction("min(A.DATE_FIRST)","SHORT")."	DATE_FIRST,
					".$DB->DateToCharFunction("max(A.DATE_LAST)","SHORT")."		DATE_LAST,
					ROUND(86400*(max(A.DATE_LAST)-min(A.DATE_FIRST)))			ADV_TIME,

					-- TOTAL
					sum(A.GUESTS)			GUESTS,
					sum(A.NEW_GUESTS)		NEW_GUESTS,
					sum(A.FAVORITES)		FAVORITES,
					sum(A.C_HOSTS)			C_HOSTS,
					sum(A.SESSIONS)			SESSIONS,
					sum(A.HITS)				HITS,
					sum(A.GUESTS_BACK)		GUESTS_BACK,
					sum(A.FAVORITES_BACK)	FAVORITES_BACK,
					sum(A.HOSTS_BACK)		HOSTS_BACK,
					sum(A.SESSIONS_BACK)	SESSIONS_BACK,
					sum(A.HITS_BACK)		HITS_BACK,

				-- AUDIENCE
				decode(sign(sum(A.SESSIONS)),1,round(sum(A.HITS)/sum(A.SESSIONS),2),-1)					ATTENT,
				decode(sign(sum(A.SESSIONS_BACK)),1,round(sum(A.HITS_BACK)/sum(A.SESSIONS_BACK),2),-1)	ATTENT_BACK,
				decode(sign(sum(A.GUESTS)),1,round((sum(A.NEW_GUESTS)/sum(A.GUESTS))*100,2),-1)			NEW_VISITORS,
				decode(sign(sum(A.GUESTS)),1,round((sum(A.GUESTS_BACK)/sum(A.GUESTS))*100,2),-1)		RETURNED_VISITORS,
				decode(sign(round(max(A.DATE_LAST)-min(A.DATE_FIRST))),
					1,round(sum(A.GUESTS)/round(max(A.DATE_LAST)-min(A.DATE_FIRST)),2),
					'-1')	VISITORS_PER_DAY,

				-- FINANCES
				round(round(sum(A.COST),2)*$rate,2)														COST,
				round(round(sum(A.REVENUE),2)*$rate,2)													REVENUE,
				round(round((sum(A.REVENUE)-sum(A.COST)),2)*$rate,2)									BENEFIT,
				round(round(decode(sign(sum(A.SESSIONS)),1,sum(A.COST)/sum(A.SESSIONS),0),2)*$rate,2)	SESSION_COST,
				round(round(decode(sign(sum(A.GUESTS)),1,sum(A.COST)/sum(A.GUESTS),0),2)*$rate,2)		VISITOR_COST,
				decode(sign(sum(A.COST)),1,round(((sum(A.REVENUE)-sum(A.COST))/sum(A.COST))*100,2),-1)	ROI


				FROM
					b_stat_adv A
				WHERE
					$strSqlSearch
				GROUP BY
					A.$group
				$strSqlOrder
			";

			// period data
			$strSql_days = "
				SELECT
				A.$group,
				$sqlDays

				-- PERIOD
				".($filter_period ? $strSqlPeriod.'nvl(D.GUESTS,0)'.$strT : 'sum(A.GUESTS)')."					GUESTS_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.C_HOSTS,0)'.$strT : 'sum(A.C_HOSTS)')."				C_HOSTS_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.NEW_GUESTS,0)'.$strT : 'sum(A.NEW_GUESTS)')."			NEW_GUESTS_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.FAVORITES,0)'.$strT : 'sum(A.FAVORITES)')."			FAVORITES_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.SESSIONS,0)'.$strT : 'sum(A.SESSIONS)')."				SESSIONS_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.HITS,0)'.$strT : 'sum(A.HITS)')."						HITS_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.GUESTS_BACK,0)'.$strT : 'sum(A.GUESTS_BACK)')."		GUESTS_BACK_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.HOSTS_BACK,0)'.$strT : 'sum(A.HOSTS_BACK)')."			HOSTS_BACK_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.FAVORITES_BACK,0)'.$strT : 'sum(A.FAVORITES)')."		FAVORITES_BACK_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.SESSIONS_BACK,0)'.$strT : 'sum(A.SESSIONS_BACK)')."	SESSIONS_BACK_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(D.HITS_BACK,0)'.$strT : 'sum(A.HITS_BACK)')."			HITS_BACK_PERIOD
				FROM
					b_stat_adv_day D
				LEFT JOIN b_stat_adv A ON (D.ADV_ID = A.ID)
				GROUP BY
					A.$group
				";

			$z = $DB->Query($strSql_days, false, $err_mess.__LINE__);
			while ($zr = $z->Fetch()) $arrGROUP_DAYS[$zr[$group]] = $zr;

		}

		$strSql_result = $strSql;

		$strSql = "
			SELECT
				*
			FROM
				($strSql)
			WHERE
				1=1
			$strSqlSearch_h
			";
		$strSql = "SELECT * FROM ($strSql) WHERE ROWNUM<=$limit";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch) || strlen($strSqlSearch_h)>0 || $group || $filter_period);
		return $res;
	}

	public static function GetByID($ID)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$ID = intval($ID);
		$strSql = "
			SELECT
				A.*,
				round(A.COST,2)									COST,
				round(A.REVENUE,2)								REVENUE,
				".$DB->DateToCharFunction("A.DATE_FIRST")."		DATE_FIRST,
				".$DB->DateToCharFunction("A.DATE_LAST")."		DATE_LAST
			FROM
				b_stat_adv A
			WHERE
				A.ID = '$ID'
		";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetEventList($ID, &$by, &$order, $arFilter=Array(), &$is_filtered)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$find_group = $arFilter["GROUP"];
		$ID = intval($ID);
		$arSqlSearch = Array();
		$arSqlSearch_h = Array();
		$strSqlSearch_h = "";
		$filter_period = false;
		$strSqlPeriod = "";
		$strT = "";
		if (is_array($arFilter))
		{
			$date1 = $arFilter["DATE1_PERIOD"];
			$date2 = $arFilter["DATE2_PERIOD"];
			$date_from = "TO_DATE('".ConvertDateTime($date1,"D.M.Y")." 00:00:00','dd.mm.yyyy hh24:mi:ss')";
			$date_to = "TO_DATE('".ConvertDateTime($date2,"D.M.Y")." 23:59:59','dd.mm.yyyy hh24:mi:ss')";
			if (strlen($date1)>0)
			{
				$filter_period = true;
				if (strlen($date2)>0)
				{
					$strSqlPeriod = "sum(decode(sign(AE.DATE_STAT-$date_from),-1,0, decode(sign(AE.DATE_STAT-$date_to),1,0,";
					$strT=")))";
				}
				else
				{
					$strSqlPeriod = "sum(decode(sign(AE.DATE_STAT-$date_from),-1,0,";
					$strT="))";
				}
			}
			elseif (strlen($date2)>0)
			{
				$filter_period = true;
				$strSqlPeriod = "sum(decode(sign(AE.DATE_STAT-$date_to),1,0,";
				$strT="))";
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
						$arSqlSearch[] = GetFilterQuery("E.ID", $val, $match);
						break;
					case "EVENT1":
					case "EVENT2":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.".$key, $val, $match);
						break;
					case "KEYWORDS":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.DESCRIPTION, E.NAME", $val, $match);
						break;
					case "COUNTER_PERIOD_1":
						$arSqlSearch_h[] = "COUNTER_PERIOD>='".intval($val)."'";
						break;
					case "COUNTER_PERIOD_2":
						$arSqlSearch_h[] = "COUNTER_PERIOD<='".intval($val)."'";
						break;
					case "COUNTER_BACK_PERIOD_1":
						$arSqlSearch_h[] = "COUNTER_BACK_PERIOD>='".intval($val)."'";
						break;
					case "COUNTER_BACK_PERIOD_2":
						$arSqlSearch_h[] = "COUNTER_BACK_PERIOD<='".intval($val)."'";
						break;
					case "COUNTER_ADV_DYNAMIC_LIST":
						$arSqlSearch_h[] = "(COUNTER_PERIOD>='".intval($val)."' or COUNTER_BACK_PERIOD>='".intval($val)."')";
						break;
					case "MONEY1":
						$arSqlSearch_h[] = "(MONEY+MONEY_BACK)>='".roundDB($val)."'";
						break;
					case "MONEY2":
						$arSqlSearch_h[] = "(MONEY+MONEY_BACK)<='".roundDB($val)."'";
						break;
					case "MONEY_PERIOD_1":
						$arSqlSearch_h[] = "(MONEY_PERIOD+MONEY_BACK_PERIOD)>='".roundDB($val)."'";
						break;
					case "MONEY_PERIOD_2":
						$arSqlSearch_h[] = "(MONEY_PERIOD+MONEY_BACK_PERIOD)<='".roundDB($val)."'";
						break;
				}
			}
		}

		if ($by == "s_id")			$strSqlOrder = "ORDER BY E.ID";
		elseif ($by == "s_event1")		$strSqlOrder = "ORDER BY E.EVENT1";
		elseif ($by == "s_event2")		$strSqlOrder = "ORDER BY E.EVENT2";
		elseif ($by == "s_sort")		$strSqlOrder = "ORDER BY C_SORT";
		elseif ($by == "s_name")		$strSqlOrder = "ORDER BY E.NAME";
		elseif ($by == "s_description")		$strSqlOrder = "ORDER BY E.DESCRIPTION";
		elseif ($by == "s_counter")		$strSqlOrder = "ORDER BY COUNTER";
		elseif ($by == "s_counter_back")	$strSqlOrder = "ORDER BY COUNTER_BACK";
		elseif ($by == "s_counter_period")	$strSqlOrder = "ORDER BY COUNTER_PERIOD";
		elseif ($by == "s_counter_back_period")	$strSqlOrder = "ORDER BY COUNTER_BACK_PERIOD";
		elseif ($by == "s_counter_today")	$strSqlOrder = "ORDER BY COUNTER_TODAY";
		elseif ($by == "s_counter_back_today")	$strSqlOrder = "ORDER BY COUNTER_BACK_TODAY";
		elseif ($by == "s_counter_yestoday")	$strSqlOrder = "ORDER BY COUNTER_YESTERDAY";
		elseif ($by == "s_counter_back_yestoday")	$strSqlOrder = "ORDER BY COUNTER_BACK_YESTERDAY";
		elseif ($by == "s_counter_bef_yestoday")	$strSqlOrder = "ORDER BY COUNTER_BEF_YESTERDAY";
		elseif ($by == "s_counter_back_bef_yestoday")	$strSqlOrder = "ORDER BY COUNTER_BACK_BEF_YESTERDAY";
		elseif ($by == "s_def")
		{
			$strSqlOrder = "
			ORDER BY
				E.C_SORT desc,
				COUNTER_TODAY desc, COUNTER_BACK_TODAY desc,
				COUNTER_YESTERDAY desc, COUNTER_BACK_YESTERDAY desc,
				COUNTER_BEF_YESTERDAY desc, COUNTER_BACK_BEF_YESTERDAY desc,
				".($filter_period? "COUNTER_PERIOD desc, COUNTER_BACK_PERIOD desc,": "")."
				COUNTER desc, COUNTER_BACK
			";
		}
		else
		{
			$by = "s_counter";
			$strSqlOrder = "ORDER BY COUNTER";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		foreach($arSqlSearch_h as $sqlWhere)
			$strSqlSearch_h .= " and (".$sqlWhere.") ";

		$find_group = (strlen($find_group)<=0) ? "NOT_REF" : $find_group;

		$sqlDays = "
			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate),nvl(AE.COUNTER,0),0))			COUNTER_TODAY,
			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-1),nvl(AE.COUNTER,0),0))		COUNTER_YESTERDAY,
			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-2),nvl(AE.COUNTER,0),0))		COUNTER_BEF_YESTERDAY,
			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate),nvl(AE.COUNTER_BACK,0),0))	COUNTER_BACK_TODAY,
			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-1),nvl(AE.COUNTER_BACK,0),0))	COUNTER_BACK_YESTERDAY,
			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-2),nvl(AE.COUNTER_BACK,0),0))		COUNTER_BACK_BEF_YESTERDAY,
			".($filter_period ? $strSqlPeriod.'nvl(AE.COUNTER,0)'.$strT : 'sum(AE.COUNTER)')."	COUNTER_PERIOD,
			".($filter_period ? $strSqlPeriod.'nvl(AE.COUNTER_BACK,0)'.$strT : 'sum(AE.COUNTER_BACK)')."	COUNTER_BACK_PERIOD,

			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate),nvl(AE.MONEY,0),0))							MONEY_TODAY,
			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-1),nvl(AE.MONEY,0),0))							MONEY_YESTERDAY,
			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-2),nvl(AE.MONEY,0),0))							MONEY_BEF_YESTERDAY,
			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate),nvl(AE.MONEY_BACK,0),0))						MONEY_BACK_TODAY,
			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-1),nvl(AE.MONEY_BACK,0),0))					MONEY_BACK_YESTERDAY,
			sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-2),nvl(AE.MONEY_BACK,0),0))					MONEY_BACK_BEF_YESTERDAY,
			".($filter_period ? $strSqlPeriod.'nvl(AE.MONEY,0)'.$strT : 'sum(AE.MONEY)')."				MONEY_PERIOD,
			".($filter_period ? $strSqlPeriod.'nvl(AE.MONEY_BACK,0)'.$strT : 'sum(AE.MONEY_BACK)')."	MONEY_BACK_PERIOD,
		";

		if ($find_group=="NOT_REF") // no grouping
		{
			$strSql = "
				SELECT
					E.ID, E.EVENT1, E.EVENT2, E.C_SORT, E.NAME, E.DESCRIPTION,
					sum(AE.COUNTER) COUNTER,
					sum(AE.COUNTER_BACK) COUNTER_BACK,
					sum(AE.MONEY) MONEY,
					sum(AE.MONEY_BACK) MONEY_BACK,
					$sqlDays
					decode(E.NAME, null, nvl(E.EVENT1,'')||' / '||nvl(E.EVENT2,''), E.NAME)	EVENT
				FROM
					b_stat_event E,
					b_stat_adv_event_day AE
				WHERE
				$strSqlSearch
				and	E.ADV_VISIBLE = 'Y'
				and AE.ADV_ID = '$ID'
				and AE.EVENT_ID = E.ID
				GROUP BY E.ID, E.EVENT1, E.EVENT2, E.C_SORT, E.NAME, E.DESCRIPTION
				$strSqlOrder
				";
		}
		else
		{
			if ($find_group=="event1")
				$group = "E.EVENT1";
			else
				$group = "E.EVENT2";
			$strSql = "
				SELECT
					$group,
					sum(E.C_SORT) C_SORT,
					$sqlDays
					sum(AE.COUNTER) COUNTER,
					sum(AE.COUNTER_BACK) COUNTER_BACK,
					sum(AE.MONEY) MONEY,
					sum(AE.MONEY_BACK) MONEY_BACK
				FROM
					b_stat_event E,
					b_stat_adv_event_day AE
				WHERE
				$strSqlSearch
				and	E.ADV_VISIBLE = 'Y'
				and AE.ADV_ID = '$ID'
				and AE.EVENT_ID = E.ID
				GROUP BY $group
				$strSqlOrder
				";
		}

		$limit = intval(COption::GetOptionString('statistic','RECORDS_LIMIT'));
		$strSql = "SELECT * FROM ($strSql) WHERE 1=1 $strSqlSearch_h";
		$strSql = "SELECT * FROM ($strSql) WHERE ROWNUM<=$limit";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch) || $find_group!="NOT_REF");
		return $res;
	}

	public static function GetEventListByReferer($value, $arFilter)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		if ($arFilter["GROUP"]=="referer1")
			$group = "A.REFERER1";
		else $group =
			"A.REFERER2";

		$where = "";
		$filter_period = false;
		$strSqlPeriod = "";
		$strT = "";

		if (is_array($arFilter))
		{
			$date1 = $arFilter["DATE1_PERIOD"];
			$date2 = $arFilter["DATE2_PERIOD"];
			$date_from = "TO_DATE('".ConvertDateTime($date1,"D.M.Y")." 00:00:00','dd.mm.yyyy hh24:mi:ss')";
			$date_to = "TO_DATE('".ConvertDateTime($date2,"D.M.Y")." 23:59:59','dd.mm.yyyy hh24:mi:ss')";
			if (strlen($date1)>0)
			{
				$filter_period = true;
				if (strlen($date2)>0)
				{
					$strSqlPeriod = "sum(decode(sign(AE.DATE_STAT-$date_from),-1,0, decode(sign(AE.DATE_STAT-$date_to),1,0,";
					$strT=")))";
				}
				else
				{
					$strSqlPeriod = "sum(decode(sign(AE.DATE_STAT-$date_from),-1,0,";
					$strT="))";
				}
			}
			elseif (strlen($date2)>0)
			{
				$filter_period = true;
				$strSqlPeriod = "sum(decode(sign(AE.DATE_STAT-$date_to),1,0,";
				$strT="))";
			}
		}

		$arFilter["GROUP"]="NOT_REF";
		CAdv::GetList($by, $order, $arFilter, $is_filtered, "", $arrGROUP_DAYS, $strSql_res);
		if ($is_filtered)
		{
			$where = "and A.ID in (SELECT ID FROM ($strSql_res))";
		}

		$strSql = "
			SELECT
				E.ID, E.EVENT1, E.EVENT2, E.C_SORT, E.NAME, E.DESCRIPTION,
				sum(AE.COUNTER)																				COUNTER,
				sum(AE.COUNTER_BACK)																		COUNTER_BACK,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate),nvl(AE.COUNTER,0),0))							COUNTER_TODAY,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-1),nvl(AE.COUNTER,0),0))						COUNTER_YESTERDAY,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-2),nvl(AE.COUNTER,0),0))						COUNTER_BEF_YESTERDAY,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate),nvl(AE.COUNTER_BACK,0),0))					COUNTER_BACK_TODAY,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-1),nvl(AE.COUNTER_BACK,0),0))					COUNTER_BACK_YESTERDAY,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-2),nvl(AE.COUNTER_BACK,0),0))					COUNTER_BACK_BEF_YESTERDAY,
				".($filter_period ? $strSqlPeriod.'nvl(AE.COUNTER,0)'.$strT : 'sum(AE.COUNTER)')."			COUNTER_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(AE.COUNTER_BACK,0)'.$strT : 'sum(AE.COUNTER_BACK)')."	COUNTER_BACK_PERIOD,

				sum(AE.MONEY)																				MONEY,
				sum(AE.MONEY_BACK)																			MONEY_BACK,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate),nvl(AE.MONEY,0),0))							MONEY_TODAY,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-1),nvl(AE.MONEY,0),0))							MONEY_YESTERDAY,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-2),nvl(AE.MONEY,0),0))							MONEY_BEF_YESTERDAY,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate),nvl(AE.MONEY_BACK,0),0))						MONEY_BACK_TODAY,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-1),nvl(AE.MONEY_BACK,0),0))					MONEY_BACK_YESTERDAY,
				sum(decode(trunc(AE.DATE_STAT),trunc(sysdate-2),nvl(AE.MONEY_BACK,0),0))					MONEY_BACK_BEF_YESTERDAY,
				".($filter_period ? $strSqlPeriod.'nvl(AE.MONEY,0)'.$strT : 'sum(AE.MONEY)')."				MONEY_PERIOD,
				".($filter_period ? $strSqlPeriod.'nvl(AE.MONEY_BACK,0)'.$strT : 'sum(AE.MONEY_BACK)')."	MONEY_BACK_PERIOD,


				decode(E.NAME, null, nvl(E.EVENT1,'')||' / '||nvl(E.EVENT2,''), E.NAME)	EVENT
			FROM
				b_stat_adv A,
				b_stat_adv_event_day AE,
				b_stat_event E
			WHERE
				1=1
				$where
			and	$group='".$DB->ForSql($value,255)."'
			and AE.ADV_ID = A.ID
			and E.ID = AE.EVENT_ID
			and E.ADV_VISIBLE = 'Y'
			GROUP BY
				E.ID, E.EVENT1, E.EVENT2, E.C_SORT, E.NAME, E.DESCRIPTION
			ORDER BY
				E.C_SORT desc,
				COUNTER_TODAY desc, COUNTER_BACK_TODAY desc,
				COUNTER_YESTERDAY desc, COUNTER_BACK_YESTERDAY desc,
				COUNTER_BEF_YESTERDAY desc, COUNTER_BACK_BEF_YESTERDAY desc,
				COUNTER_PERIOD desc, COUNTER_BACK_PERIOD desc,
				COUNTER desc, COUNTER_BACK
			";

		$strSql = "SELECT * FROM ($strSql) WHERE ROWNUM<=".intval(COption::GetOptionString('statistic','RECORDS_LIMIT'));
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetDynamicList($ADV_ID, &$by, &$order, &$arMaxMin, $arFilter=Array())
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$ADV_ID = intval($ADV_ID);
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
							$arSqlSearch[] = "D.DATE_STAT>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "D.DATE_STAT<".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
				}
			}
		}

		foreach($arSqlSearch as $sqlWhere)
			$strSqlSearch .= " and (".$sqlWhere.") ";

		if ($by == "s_date")
			$strSqlOrder = "ORDER BY D.DATE_STAT";
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
				to_char(D.DATE_STAT,'dd')								DAY,
				to_char(D.DATE_STAT,'mm')								MONTH,
				to_char(D.DATE_STAT,'yyyy')								YEAR,
				max(D.GUESTS_DAY)		GUESTS,
				max(D.NEW_GUESTS)		NEW_GUESTS,
				max(D.FAVORITES)		FAVORITES,
				max(D.C_HOSTS_DAY)		C_HOSTS,
				max(D.SESSIONS)			SESSIONS,
				max(D.HITS)				HITS,
				max(D.GUESTS_DAY_BACK)	GUESTS_BACK,
				max(D.FAVORITES_BACK)	FAVORITES_BACK,
				max(D.HOSTS_DAY_BACK)	HOSTS_BACK,
				max(D.SESSIONS_BACK)	SESSIONS_BACK,
				max(D.HITS_BACK)		HITS_BACK
			FROM
				b_stat_adv_day D
			WHERE
				D.ADV_ID=$ADV_ID
			$strSqlSearch
			GROUP BY
				D.ADV_ID, D.DATE_STAT
			$strSqlOrder
		";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		$strSql = "
			SELECT
				max(D.DATE_STAT)					DATE_LAST,
				min(D.DATE_STAT)					DATE_FIRST,
				to_char(max(D.DATE_STAT),'dd')		MAX_DAY,
				to_char(max(D.DATE_STAT),'mm')		MAX_MONTH,
				to_char(max(D.DATE_STAT),'yyyy')	MAX_YEAR,
				to_char(min(D.DATE_STAT),'dd')		MIN_DAY,
				to_char(min(D.DATE_STAT),'mm')		MIN_MONTH,
				to_char(min(D.DATE_STAT),'yyyy')	MIN_YEAR
			FROM
				b_stat_adv_day D
			WHERE
				D.ADV_ID = $ADV_ID
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

	public static function GetDropDownList($strSqlOrder="ORDER BY REFERER1, REFERER2")
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				nvl(REFERER1,'')||' / '||nvl(REFERER2,'')||' ['||ID||']' as REFERENCE
			FROM
				b_stat_adv
			$strSqlOrder
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
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
						$arSqlSearch[] = GetFilterQuery("A.".$key, $val, $match);
						break;
					case "REFERER1":
					case "REFERER2":
					case "DESCRIPTION":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("A.".$key, $val, $match);
						break;
				}
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$order= ($order!="desc") ? "asc" : "desc";
		if ($by == "s_id")				$strSqlOrder = "ORDER BY A.ID ".$order;
		elseif ($by == "s_referer1")	$strSqlOrder = "ORDER BY A.REFERER1 ".$order.", A.REFERER2";
		elseif ($by == "s_referer2")	$strSqlOrder = "ORDER BY A.REFERER2 ".$order;
		elseif ($by == "s_description")	$strSqlOrder = "ORDER BY A.DESCRIPTION ".$order;
		else
		{
			$by = "s_referer1";
			$strSqlOrder = "ORDER BY A.REFERER1 ".$order.", A.REFERER2";
		}
		$strSql = "
			SELECT
				A.ID,
				A.REFERER1,
				A.REFERER2,
				A.DESCRIPTION
			FROM
				b_stat_adv A
			WHERE
			$strSqlSearch
			$strSqlOrder
			";

		$strSql = "SELECT * FROM ($strSql) WHERE ROWNUM<=".intval(COption::GetOptionString('statistic','RECORDS_LIMIT'));
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}
}
