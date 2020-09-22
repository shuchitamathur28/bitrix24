<?php

class CAutoDetect
{
	public static function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array();
		$arSqlSearch_h = Array();
		$strSqlSearch_h = "";
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
					case "LAST":
						$arSqlSearch[] = ($val=="Y") ? "datediff(day, S.DATE_STAT, getdate())=0" : "datediff(day, S.DATE_STAT, getdate())<>0";
						break;
					case "USER_AGENT":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("S.USER_AGENT",$val,$match);
						break;
					case "COUNTER1":
						$arSqlSearch_h[] = "count(S.ID)>=".intval($val);
						break;
					case "COUNTER2":
						$arSqlSearch_h[] = "count(S.ID)<=".intval($val);
						break;
				}
			}
			foreach($arSqlSearch_h as $sqlWhere)
				$strSqlSearch_h .= " and (".$sqlWhere.") ";
		}

		if ($by == "s_user_agent")		$strSqlOrder = "ORDER BY S.USER_AGENT";
		elseif ($by == "s_counter")		$strSqlOrder = "ORDER BY COUNTER";
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
		$strSql = "
			SELECT /*TOP*/
				S.USER_AGENT,
				count(S.ID) COUNTER
			FROM
				b_stat_session S
			WHERE
			$strSqlSearch
			and S.USER_AGENT is not null
			and S.NEW_GUEST='Y'
			and not exists(
				SELECT 'x'
				FROM b_stat_browser B
				WHERE
					len(B.USER_AGENT)>0
				and B.USER_AGENT is not null
				and	upper(S.USER_AGENT) like upper(B.USER_AGENT)
				)
			and not exists(
				SELECT 'x'
				FROM b_stat_searcher R
				WHERE
					len(R.USER_AGENT)>0
				and R.USER_AGENT is not null
				and	upper(S.USER_AGENT) like upper('%'+R.USER_AGENT+'%')
				)
			and not exists(
				SELECT 'x'
				FROM b_stat_session S2
				WHERE S2.USER_AGENT = S.USER_AGENT and S2.NEW_GUEST='N'
				)
			GROUP BY
				S.USER_AGENT
			HAVING
				'1'='1' $strSqlSearch_h
			$strSqlOrder
			";

		$strSql = str_replace("/*TOP*/", "TOP ".intval(COption::GetOptionString("statistic","RECORDS_LIMIT")), $strSql);
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch) || strlen($strSqlSearch_h)>0);
		return $res;
	}
}
