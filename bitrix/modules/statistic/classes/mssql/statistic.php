<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/classes/general/statistic.php");

class CStatistics extends CAllStatistics
{
	public static function CleanUpTableByDate($cleanup_date, $table_name, $date_name)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		if (strlen($cleanup_date)>0 && $DB->IsDate($cleanup_date))
		{
			$strSql = "DELETE FROM $table_name WHERE $date_name<".$DB->CharToDateFunction($cleanup_date, "SHORT");
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function GetSessionDataByMD5($GUEST_MD5)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$php_session_time = intval(ini_get("session.gc_maxlifetime"));
		$strSql = "
			SELECT TOP 1
				ID,
				SESSION_DATA
			FROM
				b_stat_session_data
			WHERE
				GUEST_MD5 = '".$DB->ForSql($GUEST_MD5)."'
			and	DATE_LAST > dateadd(second, -$php_session_time, getdate())
			";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function CleanUpPathDynamic()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = intval(COption::GetOptionString("statistic", "PATH_DAYS"));
		$STEPS = intval(COption::GetOptionString("statistic", "MAX_PATH_STEPS"));
		if ($DAYS>=0)
		{
			$strSql = "
				DELETE FROM b_stat_path WHERE
				(
					DATE_STAT <= dateadd(day, -$DAYS, getdate()) or
					DATE_STAT is null or
					STEPS>$STEPS
				)
				";
			$DB->Query($strSql, false, $err_mess.__LINE__);
			$strSql = "
				DELETE FROM b_stat_path_adv WHERE
				(
					DATE_STAT <= dateadd(day, -$DAYS, getdate()) or
					DATE_STAT is null or
					STEPS>$STEPS
				)
				";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpPathCache()
	{
		__SetNoKeepStatistics();
		if ($_SESSION["SESS_NO_AGENT_STATISTIC"]!="Y" && !defined("NO_AGENT_STATISTIC"))
		{
			set_time_limit(0);
			ignore_user_abort(true);
			$err_mess = "File: ".__FILE__."<br>Line: ";
			$DB = CDatabase::GetModuleConnection('statistic');
			$php_session_time = intval(ini_get("session.gc_maxlifetime"));
			$strSql = "
				DELETE FROM b_stat_path_cache WHERE
					DATE_HIT < dateadd(SECOND, -$php_session_time, getdate()) or
					DATE_HIT is null
					";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
		return "CStatistics::CleanUpPathCache();";
	}

	public static function CleanUpSessionData()
	{
		__SetNoKeepStatistics();
		if ($_SESSION["SESS_NO_AGENT_STATISTIC"]!="Y" && !defined("NO_AGENT_STATISTIC"))
		{
			set_time_limit(0);
			ignore_user_abort(true);
			$err_mess = "File: ".__FILE__."<br>Line: ";
			$DB = CDatabase::GetModuleConnection('statistic');
			$php_session_time = intval(ini_get("session.gc_maxlifetime"));
			$strSql = "
				DELETE FROM b_stat_session_data WHERE
					DATE_LAST < dateadd(SECOND, -$php_session_time, getdate()) or
					DATE_LAST is null
					";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
		return "CStatistics::CleanUpSessionData();";
	}

	public static function CleanUpSearcherDynamic()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = intval(COption::GetOptionString("statistic", "SEARCHER_DAYS"));
		if ($DAYS>=0)
		{
			$strSql = "
				SELECT
					ID,
					isnull(DYNAMIC_KEEP_DAYS,'$DAYS') as DYNAMIC_KEEP_DAYS
				FROM
					b_stat_searcher
				";
			$w = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($wr = $w->Fetch())
			{
				$SDAYS = intval($wr["DYNAMIC_KEEP_DAYS"]);
				$SID = intval($wr["ID"]);
				$strSql = "
					SELECT
						ID,
						TOTAL_HITS
					FROM
						b_stat_searcher_day
					WHERE
						SEARCHER_ID = $SID
					and DATEDIFF(day, DATE_STAT, getdate())>=$SDAYS
				";
				$z = $DB->Query($strSql, false, $err_mess.__LINE__);
				while ($zr=$z->Fetch())
				{
					$ID = $zr["ID"];
					if (intval($zr["TOTAL_HITS"])>0)
					{
						$arFields = Array(
							"DATE_CLEANUP"	=> $DB->GetNowFunction(),
							"TOTAL_HITS"	=> "TOTAL_HITS + ".intval($zr["TOTAL_HITS"]),
							);
						$DB->Update("b_stat_searcher",$arFields,"WHERE ID='$SID'",$err_mess.__LINE__);
					}
					$strSql = "DELETE FROM b_stat_searcher_day WHERE ID='$ID'";
					$DB->Query($strSql, false, $err_mess.__LINE__);
				}
			}
		}
	}

	public static function CleanUpEventDynamic()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = intval(COption::GetOptionString("statistic", "EVENT_DYNAMIC_DAYS"));
		if ($DAYS>=0)
		{
			$strSql = "
				SELECT
					ID,
					isnull(DYNAMIC_KEEP_DAYS,'$DAYS') as DYNAMIC_KEEP_DAYS
				FROM
					b_stat_event
				";
			$w = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($wr = $w->Fetch())
			{
				$EDAYS = intval($wr["DYNAMIC_KEEP_DAYS"]);
				$EID = intval($wr["ID"]);
				$strSql = "
					SELECT
						ID,
						COUNTER,
						MONEY
					FROM
						b_stat_event_day
					WHERE
						EVENT_ID = $EID
					and DATEDIFF(day, DATE_STAT, getdate())>=$EDAYS
				";
				$z = $DB->Query($strSql, false, $err_mess.__LINE__);
				while ($zr=$z->Fetch())
				{
					$ID = $zr["ID"];
					if (intval($zr["COUNTER"])>0)
					{
						$arFields = Array(
							"DATE_CLEANUP"	=> $DB->GetNowFunction(),
							"COUNTER"		=> "COUNTER + ".intval($zr["COUNTER"]),
							"MONEY"			=> "MONEY + ".roundDB($zr["MONEY"])
							);
						$DB->Update("b_stat_event",$arFields,"WHERE ID='$EID'",$err_mess.__LINE__);
					}
					$strSql = "DELETE FROM b_stat_event_day WHERE ID='$ID'";
					$DB->Query($strSql, false, $err_mess.__LINE__);
				}
			}
		}
	}

	public static function CleanUpAdvDynamic()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = intval(COption::GetOptionString("statistic", "ADV_DAYS"));
		if ($DAYS>=0)
		{
			$strSql = "
				DELETE FROM b_stat_adv_day WHERE
					DATEDIFF(day, DATE_STAT, getdate()) >= $DAYS or
					DATE_STAT is null
					";
			$DB->Query($strSql, false, $err_mess.__LINE__);

			$strSql = "
				DELETE FROM b_stat_adv_event_day WHERE
					DATEDIFF(day, DATE_STAT, getdate()) >= $DAYS or
					DATE_STAT is null
					";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpPhrases()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = COption::GetOptionString("statistic", "PHRASES_DAYS");
		$DAYS = intval($DAYS);
		if ($DAYS>=0)
		{
			$strSql = "
				DELETE FROM b_stat_phrase_list WHERE
					DATEDIFF(day, DATE_HIT, getdate()) >= $DAYS or
					DATE_HIT is null
					";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpRefererList()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = COption::GetOptionString("statistic", "REFERER_LIST_DAYS");
		$DAYS = intval($DAYS);
		if ($DAYS>=0)
		{
			$strSql = "
				DELETE FROM b_stat_referer_list WHERE
					DATEDIFF(day, DATE_HIT, getdate()) >= $DAYS or
					DATE_HIT is null
					";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpReferer()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = intval(COption::GetOptionString("statistic", "REFERER_DAYS"));
		$TOP = intval(COption::GetOptionString("statistic", "REFERER_TOP"));
		$DAYS = intval($DAYS);
		if ($DAYS>=0)
		{
			$strSql = "
				DELETE FROM b_stat_referer
				WHERE
					(
						DATEDIFF(day, DATE_LAST, getdate()) >= $DAYS or
						DATE_LAST is null
					)
				and ID not in (
					SELECT TOP $TOP RT.ID FROM b_stat_referer RT ORDER BY RT.SESSIONS desc
					)
				";

			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpVisits()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = COption::GetOptionString("statistic", "VISIT_DAYS");
		$DAYS = intval($DAYS);
		if ($DAYS>=0)
		{
			$strSql = "
				DELETE FROM b_stat_page WHERE
				(
					DATEDIFF(day, DATE_STAT, getdate()) >= $DAYS or
					DATE_STAT is null
				)
				";
			$DB->Query($strSql, false, $err_mess.__LINE__);
			$strSql = "
				DELETE FROM b_stat_page_adv WHERE
				(
					DATEDIFF(day, DATE_STAT, getdate()) >= $DAYS or
					DATE_STAT is null
				)
				";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpCities()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = intval(COption::GetOptionString("statistic", "CITY_DAYS"));
		if($DAYS >= 0)
		{
			$strSql = "
				DELETE FROM b_stat_city_day WHERE
					DATEDIFF(day, DATE_STAT, getdate()) >= $DAYS
			";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpCountries()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = intval(COption::GetOptionString("statistic", "COUNTRY_DAYS"));
		if($DAYS >= 0)
		{
			$strSql = "
				DELETE FROM b_stat_country_day WHERE
					DATEDIFF(day, DATE_STAT, getdate()) >= $DAYS or
					DATE_STAT is null
					";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpGuests()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = COption::GetOptionString("statistic", "GUEST_DAYS");
		$DAYS = intval($DAYS);
		if ($DAYS>=0)
		{
			$strSql = "
				DELETE FROM b_stat_guest WHERE
					DATEDIFF(day, LAST_DATE, getdate()) >= $DAYS or
					LAST_DATE is null
					";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpSessions()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = COption::GetOptionString("statistic", "SESSION_DAYS");
		$DAYS = intval($DAYS);
		if ($DAYS>=0)
		{
			$strSql = "
				DELETE FROM b_stat_session WHERE
					DATEDIFF(day, DATE_LAST, getdate()) >= $DAYS or
					DATE_LAST is null
					";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpHits()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = COption::GetOptionString("statistic", "HIT_DAYS");
		$DAYS = intval($DAYS);
		if ($DAYS>=0)
		{
			$strSql = "
				DELETE FROM b_stat_hit WHERE
					DATEDIFF(day, DATE_HIT, getdate()) >= $DAYS or
					DATE_HIT is null
				";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpSearcherHits()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = intval(COption::GetOptionString("statistic", "SEARCHER_HIT_DAYS"));
		$strSql = "
			DELETE FROM b_stat_searcher_hit WHERE
				DATEDIFF(day, DATE_HIT, getdate()) >= isnull(HIT_KEEP_DAYS,$DAYS) or
				DATE_HIT is null
			";
		$DB->Query($strSql, false, $err_mess.__LINE__);
	}

	public static function CleanUpAdvGuests()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = COption::GetOptionString("statistic", "ADV_GUEST_DAYS");
		$DAYS = intval($DAYS);
		if ($DAYS>=0)
		{
			$strSql = "
				DELETE FROM b_stat_adv_guest WHERE
				(
					DATEDIFF(day, DATE_GUEST_HIT, getdate()) >= $DAYS or
					DATE_GUEST_HIT is null
				)
				and
				(
					DATEDIFF(day, DATE_HOST_HIT, getdate()) >= $DAYS or
					DATE_HOST_HIT is null
				)
				";
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
	}

	public static function CleanUpEvents()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$DAYS = intval(COption::GetOptionString("statistic", "EVENTS_DAYS"));
		$strSql = "
			DELETE FROM b_stat_event_list WHERE
				DATEDIFF(day, DATE_ENTER, getdate()) >= isnull(KEEP_DAYS,$DAYS) or
				DATE_ENTER is null
			";
		$DB->Query($strSql, false, $err_mess.__LINE__);
	}

	public static function SetNewDayForSite($SITE_ID=false, $HOSTS=0, $TOTAL_HOSTS=0, $SESSIONS=0, $HITS=0)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');

		if ($SITE_ID===false)
		{
			$SITE_ID = "";
			if (!(defined("ADMIN_SECTION") && ADMIN_SECTION===true) && defined("SITE_ID"))
			{
				$SITE_ID = SITE_ID;
			}
		}
		if (strlen($SITE_ID)>0)
		{
			$strSql = "
				INSERT INTO b_stat_day_site(
					SITE_ID,
					DATE_STAT,
					TOTAL_HOSTS,
					C_HOSTS,
					SESSIONS,
					HITS
					)
				SELECT
					'".$DB->ForSql($SITE_ID, 2)."',
					".$DB->CurrentDateFunction().",
					isnull(PREV.MAX_TOTAL_HOSTS,0) + ".intval($TOTAL_HOSTS).",
					".intval($HOSTS).",
					".intval($SESSIONS).",
					".intval($HITS)."
				FROM
					(
						SELECT
							max(TOTAL_HOSTS) as MAX_TOTAL_HOSTS
						FROM
							b_stat_day_site
						WHERE
							SITE_ID = '".$DB->ForSql($SITE_ID, 2)."'
					) as PREV
				";
			if ($DB->Query($strSql, true, $err_mess.__LINE__))
			{
				$strSql = "
					SELECT TOP 1
						D.ID,
						".$DB->DateToCharFunction("D.DATE_STAT","SHORT")."		DATE_STAT
					FROM
						b_stat_day_site D
					WHERE
						D.DATE_STAT <> ".$DB->CurrentDateFunction()."
					and D.SITE_ID = '".$DB->ForSql($SITE_ID, 2)."'
					ORDER BY
						D.DATE_STAT desc
					";
				$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
				if ($ar = $rs->Fetch())
				{
					$arF = CSession::GetAttentiveness($ar["DATE_STAT"], $SITE_ID);
					if (is_array($arF)) $DB->Update("b_stat_day_site", $arF, "WHERE ID='".$ar["ID"]."'", $err_mess.__LINE__);
				}
			}
		}
	}

	public static function SetNewDay($HOSTS=0, $TOTAL_HOSTS=0, $SESSIONS=0, $HITS=0, $NEW_GUESTS=0, $GUESTS=0, $FAVORITES=0)
	{
		__SetNoKeepStatistics();
		if ($_SESSION["SESS_NO_AGENT_STATISTIC"]!="Y" && !defined("NO_AGENT_STATISTIC"))
		{
			$err_mess = "File: ".__FILE__."<br>Line: ";
			$DB = CDatabase::GetModuleConnection('statistic');
			$strSql = "
				INSERT INTO b_stat_day(
					DATE_STAT,
					TOTAL_HOSTS,
					C_HOSTS,
					SESSIONS,
					GUESTS,
					FAVORITES,
					HITS,
					NEW_GUESTS
					)
				SELECT
					".$DB->CurrentDateFunction().",
					isnull(PREV.MAX_TOTAL_HOSTS,0) + ".intval($TOTAL_HOSTS).",
					".intval($HOSTS).",
					".intval($SESSIONS).",
					".intval($GUESTS).",
					".intval($FAVORITES).",
					".intval($HITS).",
					".intval($NEW_GUESTS)."
				FROM
					(SELECT	max(TOTAL_HOSTS) AS MAX_TOTAL_HOSTS	FROM b_stat_day) as PREV
				";
			if ($DB->Query($strSql, true, $err_mess.__LINE__))
			{
				$strSql = "
					SELECT TOP 1
						D.ID,
						".$DB->DateToCharFunction("D.DATE_STAT","SHORT")."		DATE_STAT
					FROM
						b_stat_day D
					WHERE
						D.DATE_STAT <> ".$DB->CurrentDateFunction()."
					ORDER BY
						D.DATE_STAT desc
					";

				$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
				if ($ar = $rs->Fetch())
				{
					$arF = CSession::GetAttentiveness($ar["DATE_STAT"]);
					if (is_array($arF)) $DB->Update("b_stat_day", $arF, "WHERE ID='".$ar["ID"]."'", $err_mess.__LINE__);
				}
			}
		}
		return "CStatistics::SetNewDay();";
	}

	public static function DBDateAdd($date, $days=1)
	{
		return "dateadd(day, ".$days.", ".$date.")";
	}

	public static function DBTopSql($strSql, $nTopCount=false)
	{
		if($nTopCount===false)
			$nTopCount = intval(COption::GetOptionString("statistic","RECORDS_LIMIT"));
		else
			$nTopCount = intval($nTopCount);
		if($nTopCount>0)
			return str_replace("/*TOP*/", "TOP ".$nTopCount, $strSql);
		else
			return str_replace("/*TOP*/", "", $strSql);
	}

	public static function DBFirstDate($strSql)
	{
		return "isnull(".$strSql.", convert(datetime, '1980-01-01 00:00:00', 120))";
	}

	public static function DBDateDiff($date1, $date2)
	{
		return "datediff(second, ".$date2.", ".$date1.")";
	}
}
