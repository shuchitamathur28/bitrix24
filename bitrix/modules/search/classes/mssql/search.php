<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/classes/general/search.php");

class CSearch extends CAllSearch
{
	function Fetch()
	{
		if ($this->offset !== false)
		{
			do
			{
				$r = parent::fetch();
				$this->offset--;
			} while ($this->offset >= 0 && is_array($r));
		}
		else
		{
			$r = parent::fetch();
		}

		return $r;
	}

	function MakeSQL($query, $strSqlWhere, $strSort, $bIncSites, $bStem)
	{
		global $USER;
		$DB = CDatabase::GetModuleConnection('search');

		$bDistinct = false;
		$arSelect = array(
			"ID" => "sc.ID",
			"MODULE_ID" => "sc.MODULE_ID",
			"ITEM_ID" => "sc.ITEM_ID",
			"TITLE" => "sc.TITLE",
			"TAGS" => "sc.TAGS",
			"BODY" => "sc.BODY",
			"PARAM1" => "sc.PARAM1",
			"PARAM2" => "sc.PARAM2",
			"UPD" => "sc.UPD",
			"DATE_FROM" => "sc.DATE_FROM",
			"DATE_TO" => "sc.DATE_TO",
			"URL" => "sc.URL",
			"CUSTOM_RANK" => "sc.CUSTOM_RANK",
			"FULL_DATE_CHANGE" => $DB->DateToCharFunction("sc.DATE_CHANGE")." as FULL_DATE_CHANGE",
			"DATE_CHANGE" => $DB->DateToCharFunction("sc.DATE_CHANGE", "SHORT")." as DATE_CHANGE",
		);
		if (BX_SEARCH_VERSION > 1)
		{
			if ($this->Query->bText)
				$arSelect["SEARCHABLE_CONTENT"] = "sct.SEARCHABLE_CONTENT";
			$arSelect["USER_ID"] = "sc.USER_ID";
		}
		else
		{
			$arSelect["LID"] = "sc.LID";
			$arSelect["SEARCHABLE_CONTENT"] = "sc.SEARCHABLE_CONTENT";
		}

		if (strpos($strSort, "TITLE_RANK") !== false)
		{
			if ($bStem)
			{
				$strSelect = "";
				foreach ($this->Query->m_stemmed_words as $stem)
				{
					if (strlen($strSelect) > 0)
						$strSelect .= " + ";
					$strSelect .= "case when CHARINDEX('".$stem."', upper(convert(varchar(2000), sc.TITLE))) > 0 then 1 else 0 end";
				}
				$arSelect["TITLE_RANK"] = $strSelect." as TITLE_RANK";
			}
			else
			{
				$strSelect = "";
				foreach ($this->Query->m_words as $word)
				{
					if (strlen($strSelect) > 0)
						$strSelect .= " + ";
					$strSelect .= "case when CHARINDEX('".$DB->ForSql(ToUpper($word))."', upper(convert(varchar(2000), sc.TITLE))) > 0 then 1 else 0 end";
				}
				$arSelect["TITLE_RANK"] = $strSelect." as TITLE_RANK";
			}
		}

		$strStemList = '';
		if ($bStem)
		{
			if (BX_SEARCH_VERSION > 1)
				$strStemList = implode(", ", $this->Query->m_stemmed_words_id);
			else
				$strStemList = "'".implode("' ,'", $this->Query->m_stemmed_words)."'";
		}

		$bWordPos = false;//BX_SEARCH_VERSION > 1 && COption::GetOptionString("search", "use_word_distance") == "Y";

		if ($bIncSites && $bStem)
		{
			$arSelect["SITE_URL"] = "scsite.URL as SITE_URL";
			$arSelect["SITE_ID"] = "scsite.SITE_ID";
			$arSelect["RANK"] = "st.RANK as RANK";

			$strSql = "
			FROM b_search_content sc
				".($this->Query->bText? "INNER JOIN b_search_content_text sct ON sct.SEARCH_CONTENT_ID = sc.ID": "")."
				INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
				".(count($this->Query->m_stemmed_words) > 1?
					",(
						select search_content_id, max(st0.TF) TF, ".($bWordPos? "if(STDDEV(st.PS)-".$this->normdev(count($this->Query->m_stemmed_words))." between -0.000001 and 1, 1/STDDEV(st.PS), 0) + ": "")."sum(st0.TF/sf0.FREQ) as RANK
						from b_search_content_stem st0, b_search_content_freq sf0
						where st0.language_id = '".$this->Query->m_lang."'
						and st0.stem = sf0.stem
						and sf0.language_id = st0.language_id
						and st0.stem in (".$strStemList.")
						".($this->tf_hwm > 0? "and st0.TF >= ".number_format($this->tf_hwm, 2, ".", ""): "")."
						".(strlen($this->tf_hwm_site_id) > 0? "and sf0.SITE_ID = '".$DB->ForSQL($this->tf_hwm_site_id, 2)."'": "and sf0.SITE_ID IS NULL")."
						group by st0.search_content_id) st"
					:
					",(
						select st0.search_content_id, st0.TF, st0.TF RANK
						from b_search_content_stem st0
						where st0.language_id = '".$this->Query->m_lang."'
						and st0.stem in (".$strStemList.")
						".($this->tf_hwm > 0? "and st0.TF >= ".number_format($this->tf_hwm, 2, ".", ""): "")."
					) st"
				)."
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND ".$query."
				AND st.search_content_id = sc.id
				".$strSqlWhere."
			";
		}
		elseif ($bIncSites && !$bStem)
		{
			$arSelect["SITE_URL"] = "scsite.URL as SITE_URL";
			$arSelect["SITE_ID"] = "scsite.SITE_ID";
			$arSelect["RANK"] = "1 as RANK";

			$strSql = "
			FROM b_search_content sc
				".($this->Query->bText? "INNER JOIN b_search_content_text sct ON sct.SEARCH_CONTENT_ID = sc.ID": "")."
				INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND ".$query."
				".$strSqlWhere."
			";
		}
		elseif (!$bIncSites && $bStem)
		{
			if (BX_SEARCH_VERSION <= 1)
				$arSelect["SITE_ID"] = "sc.LID as SITE_ID";
			$arSelect["RANK"] = "st.RANK as RANK";

			$strSql = "
			FROM b_search_content sc
				".($this->Query->bText? "INNER JOIN b_search_content_text sct ON sct.SEARCH_CONTENT_ID = sc.ID": "")."
				".(count($this->Query->m_stemmed_words) > 1?
					",(
						select search_content_id, max(st0.TF) TF, ".($bWordPos? "if(STDDEV(st.PS)-".$this->normdev(count($this->Query->m_stemmed_words))." between -0.000001 and 1, 1/STDDEV(st.PS), 0) + ": "")."sum(st0.TF/sf0.FREQ) as RANK
						from b_search_content_stem st0, b_search_content_freq sf0
						where st0.language_id = '".$this->Query->m_lang."'
						and st0.stem = sf0.stem
						and sf0.language_id = st0.language_id
						and st0.stem in (".$strStemList.")
						".($this->tf_hwm > 0? "and st0.TF >= ".number_format($this->tf_hwm, 2, ".", ""): "")."
						".(strlen($this->tf_hwm_site_id) > 0? "and sf0.SITE_ID = '".$DB->ForSQL($this->tf_hwm_site_id, 2)."'": "and sf0.SITE_ID IS NULL")."
						group by st0.search_content_id) st"
					:
					",(
						select st0.search_content_id, st0.TF, st0.TF RANK
						from b_search_content_stem st0
						where st0.language_id = '".$this->Query->m_lang."'
						and st0.stem in (".$strStemList.")
						".($this->tf_hwm > 0? "and st0.TF >= ".number_format($this->tf_hwm, 2, ".", ""): "")."
					) st"
				)."
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND ".$query."
				AND st.search_content_id = sc.id
				".$strSqlWhere."
			";
		}
		else //if(!$bIncSites && !$bStem)
		{
			if (BX_SEARCH_VERSION <= 1)
				$arSelect["SITE_ID"] = "sc.LID as SITE_ID";
			$arSelect["RANK"] = "1 as RANK";

			$strSql = "
			FROM b_search_content sc
				".($this->Query->bText? "INNER JOIN b_search_content_text sct ON sct.SEARCH_CONTENT_ID = sc.ID": "")."
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND ".$query."
				".$strSqlWhere."
			";
		}

		//Text fields must be last
		foreach (array("SITE_URL", "URL", "TITLE", "TAGS", "BODY") as $text_field)
		{
			if (isset($arSelect[$text_field]))
			{
				$s = $arSelect[$text_field];
				unset($arSelect[$text_field]);
				$arSelect[$text_field] = $s;
			}
		}

		$strRatingJoin = "";
		$RATING_MAX = 0;
		$RATING_MIN = 0;
		if (
			($this->flagsUseRatingSort & 0x01)
			&& COption::GetOptionString("search", "use_social_rating") == "Y"
			&& BX_SEARCH_VERSION == 2
			&& COption::GetOptionString("search", "dbnode_id") <= 0
		)
		{
			$rsMinMax = $DB->Query("select max(TOTAL_VALUE) RATING_MAX, min(TOTAL_VALUE) RATING_MIN from b_rating_voting");
			$arMinMax = $rsMinMax->Fetch();
			if ($arMinMax)
			{
				$RATING_MAX = doubleval($arMinMax["RATING_MAX"]);
				if ($RATING_MAX < 0)
					$RATING_MAX = 0;

				$RATING_MIN = doubleval($arMinMax["RATING_MIN"]);
				if ($RATING_MIN > 0)
					$RATING_MIN = 0;
			}

			if ($RATING_MAX != 0 || $RATING_MIN != 0)
			{
				return "
					with sc as (
						"."SELECT TOP ".($this->offset + $this->limit)." ".($bDistinct? "DISTINCT": "")."
						sc.ENTITY_TYPE_ID, sc.ENTITY_ID,
						".implode("\n,", $arSelect)."
						".$strSql.$strSort."
					)
					SELECT sc.RANK + case
						when rv.TOTAL_VALUE > 0 then ".($RATING_MAX > 0? "rv.TOTAL_VALUE/".$RATING_MAX: "0")."
						when rv.TOTAL_VALUE < 0 then ".($RATING_MIN < 0? "rv.TOTAL_VALUE/".abs($RATING_MIN): "0")."
						else 0
					end SRANK
					,".$DB->IsNull('rvv.VALUE', '0')." RATING_USER_VOTE_VALUE
					,sc.ENTITY_TYPE_ID RATING_TYPE_ID
					,sc.ENTITY_ID RATING_ENTITY_ID
					,rv.TOTAL_VOTES RATING_TOTAL_VOTES
					,rv.TOTAL_POSITIVE_VOTES RATING_TOTAL_POSITIVE_VOTES
					,rv.TOTAL_NEGATIVE_VOTES RATING_TOTAL_NEGATIVE_VOTES
					,rv.TOTAL_VALUE RATING_TOTAL_VALUE
					,sc.*
					FROM sc
					LEFT JOIN b_rating_voting rv ON rv.ENTITY_TYPE_ID = sc.ENTITY_TYPE_ID AND rv.ENTITY_ID = sc.ENTITY_ID
					LEFT JOIN b_rating_vote rvv ON rvv.ENTITY_TYPE_ID = sc.ENTITY_TYPE_ID AND rvv.ENTITY_ID = sc.ENTITY_ID AND rvv.USER_ID = ".intval($USER->GetId())."
				".str_replace(" RANK", " SRANK", $strSort);
			}
		}

		if ($this->offset === false)
		{
			return "SELECT TOP ".$this->limit." ".($bDistinct? "DISTINCT": "")."\n".implode("\n,", $arSelect)."\n".$strSql.$strSort;
		}
		else
		{
			return "SELECT TOP ".($this->offset + $this->limit)." ".($bDistinct? "DISTINCT": "")."\n".implode("\n,", $arSelect)."\n".$strSql.$strSort;
		}
	}

	function tagsMakeSQL($query, $strSqlWhere, $strSort, $bIncSites, $bStem, $limit = 100)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$limit = intVal($limit);


		if ($bIncSites && $bStem)
			$strSql = "
			SELECT /*TOP*/
				stags.NAME
				,COUNT(DISTINCT stags.SEARCH_CONTENT_ID) as CNT
				,MAX(sc.DATE_CHANGE) DC_TMP
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)", "SHORT")." as DATE_CHANGE
			FROM b_search_tags stags
				INNER JOIN b_search_content sc ON (stags.SEARCH_CONTENT_ID=sc.ID)
				".($this->Query->bText? "INNER JOIN b_search_content_text sct ON sct.SEARCH_CONTENT_ID = sc.ID": "")."
				INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND ".$query."
				".$strSqlWhere."
				AND stags.SITE_ID = scsite.SITE_ID
			GROUP BY
				stags.NAME
			".$strSort."
			";
		elseif ($bIncSites && !$bStem)
			$strSql = "
			SELECT /*TOP*/
				stags.NAME
				,COUNT(DISTINCT stags.SEARCH_CONTENT_ID) as CNT
				,MAX(sc.DATE_CHANGE) DC_TMP
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)", "SHORT")." as DATE_CHANGE
			FROM b_search_tags stags
				INNER JOIN b_search_content sc ON (stags.SEARCH_CONTENT_ID=sc.ID)
				".($this->Query->bText? "INNER JOIN b_search_content_text sct ON sct.SEARCH_CONTENT_ID = sc.ID": "")."
				INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND ".$query."
				".$strSqlWhere."
				AND stags.SITE_ID = scsite.SITE_ID
			GROUP BY
				stags.NAME
			".$strSort."
			";
		elseif (!$bIncSites && $bStem)
			$strSql = "
			SELECT /*TOP*/
				stags.NAME
				,COUNT(DISTINCT stags.SEARCH_CONTENT_ID) as CNT
				,MAX(sc.DATE_CHANGE) DC_TMP
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)", "SHORT")." as DATE_CHANGE
			FROM b_search_tags stags
				INNER JOIN b_search_content sc ON (stags.SEARCH_CONTENT_ID=sc.ID)
				".($this->Query->bText? "INNER JOIN b_search_content_text sct ON sct.SEARCH_CONTENT_ID = sc.ID": "")."
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND ".$query."
				".$strSqlWhere."
			GROUP BY
				stags.NAME
			".$strSort."
			";
		else //if(!$bIncSites && !$bStem)
			$strSql = "
			SELECT /*TOP*/
				stags.NAME
				,COUNT(DISTINCT stags.SEARCH_CONTENT_ID) as CNT
				,MAX(sc.DATE_CHANGE) DC_TMP
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)", "SHORT")." as DATE_CHANGE
			FROM b_search_tags stags
				INNER JOIN b_search_content sc ON (stags.SEARCH_CONTENT_ID=sc.ID)
				".($this->Query->bText? "INNER JOIN b_search_content_text sct ON sct.SEARCH_CONTENT_ID = sc.ID": "")."
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND ".$query."
				".$strSqlWhere."
			GROUP BY
				stags.NAME
			".$strSort."
			";

		if ($limit < 1)
			$limit = 150;

		return str_replace("/*TOP*/", "TOP ".$limit, $strSql);
	}

	public static function ReindexLock()
	{
	}

	public static function OnLangDelete($lang)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$DB->Query("
			DELETE FROM b_search_content_site
			WHERE SITE_ID='".$DB->ForSql($lang)."'
		", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		CSearchTags::CleanCache();
	}

	public static function FormatDateString($strField)
	{
		$DB = CDatabase::GetModuleConnection('search');
		return $DB->DateFormatToDB("DD.MM.YYYY HH24:MI:SS", $strField);
	}

	public static function FormatLimit($strSql, $limit)
	{
		return str_replace("/*TOP*/", "TOP ".intval($limit), $strSql);
	}

	public static function CleanFreqCache($ID)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$DB->Query("
			UPDATE b_search_content_freq
			SET TF = null
			FROM
				b_search_content_stem
			WHERE
				b_search_content_freq.LANGUAGE_ID = b_search_content_stem.LANGUAGE_ID
				AND b_search_content_freq.STEM = b_search_content_stem.STEM
				AND b_search_content_stem.SEARCH_CONTENT_ID = ".intval($ID)."
				AND b_search_content_freq.TF is not null
		");
	}

	public static function IndexTitle($arLID, $ID, $sTitle)
	{
		$DB = CDatabase::GetModuleConnection('search');
		static $CACHE_SITE_LANGS = array();
		$ID = intval($ID);

		$arLang = array();
		if (!is_array($arLID))
			$arLID = Array();
		foreach ($arLID as $site => $url)
		{
			$sql_site = $DB->ForSql($site);

			if (!array_key_exists($site, $CACHE_SITE_LANGS))
			{
				$db_site_tmp = CSite::GetByID($site);
				if ($ar_site_tmp = $db_site_tmp->Fetch())
					$CACHE_SITE_LANGS[$site] = array(
						"LANGUAGE_ID" => $ar_site_tmp["LANGUAGE_ID"],
						"CHARSET" => $ar_site_tmp["CHARSET"],
						"SERVER_NAME" => $ar_site_tmp["SERVER_NAME"]
					);
				else
					$CACHE_SITE_LANGS[$site] = false;
			}

			if (is_array($CACHE_SITE_LANGS[$site]))
			{
				$lang = $CACHE_SITE_LANGS[$site]["LANGUAGE_ID"];

				$arTitle = stemming_split($sTitle, $lang);
				if (!empty($arTitle))
				{
					$maxValuesLen = 0;
					$strSqlPrefix = "
							insert into b_search_content_title
							(SEARCH_CONTENT_ID, SITE_ID, WORD, POS)
							values
					";
					$strSqlValues = "";
					$strSqlSuffix = "";
					foreach ($arTitle as $word => $pos)
					{
						$strSqlValues .= ",\n(".$ID.", '".$sql_site."', '".$DB->ForSql($word)."', ".$pos.")";
						if (strlen($strSqlValues) > $maxValuesLen)
						{
							$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, "File: ".__FILE__."<br>Line: ".__LINE__);
							$strSqlValues = "";
						}
					}
					if (strlen($strSqlValues) > 0)
					{
						$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, "File: ".__FILE__."<br>Line: ".__LINE__);
						$strSqlValues = "";
					}
				}
			}
		}
	}

	public static function RegisterStem($stem)
	{
		$DB = CDatabase::GetModuleConnection('search');
		static $cache = array();

		if (is_array($stem)) //This is batch check of the already exist stems
		{
			ksort($stem);

			$strSqlPrefix = "select * from b_search_stem where stem in (";
			$maxValuesLen = 4096;
			$maxValuesCnt = 1500;
			$strSqlValues = "";
			$i = 0;
			foreach ($stem as $word => $count)
			{
				$strSqlValues .= ",'".$DB->ForSQL($word)."'";
				$i++;

				if (strlen($strSqlValues) > $maxValuesLen || $i > $maxValuesCnt)
				{
					$rs = $DB->Query($strSqlPrefix.substr($strSqlValues, 1).")", false, "File: ".__FILE__."<br>Line: ".__LINE__);
					while ($ar = $rs->Fetch())
						$cache[$ar["STEM"]] = $ar["ID"];

					$strSqlValues = "";
					$i = 0;
				}
			}

			if (strlen($strSqlValues) > 0)
			{
				$rs = $DB->Query($strSqlPrefix.substr($strSqlValues, 1).")", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				while ($ar = $rs->Fetch())
					$cache[$ar["STEM"]] = $ar["ID"];
			}

			return;
		}

		if (!isset($cache[$stem]))
		{
			$rs = $DB->Query("insert into b_search_stem (STEM) values ('".$DB->ForSQL($stem)."')", true);
			if ($rs === false)
			{
				$rs = $DB->Query("select ID from b_search_stem WHERE STEM = '".$DB->ForSQL($stem)."'");
				$ar = $rs->Fetch();
				$cache[$stem] = $ar["ID"];
			}
			else
			{
				$cache[$stem] = $DB->LastID();
			}
		}

		return $cache[$stem];
	}

	public static function StemIndex($arLID, $ID, $sContent)
	{
		$DB = CDatabase::GetModuleConnection('search');
		static $CACHE_SITE_LANGS = array();
		$ID = intval($ID);

		$arLang = array();
		if (!is_array($arLID))
			$arLID = Array();
		foreach ($arLID as $site => $url)
		{
			if (!array_key_exists($site, $CACHE_SITE_LANGS))
			{
				$db_site_tmp = CSite::GetByID($site);
				if ($ar_site_tmp = $db_site_tmp->Fetch())
					$CACHE_SITE_LANGS[$site] = array(
						"LANGUAGE_ID" => $ar_site_tmp["LANGUAGE_ID"],
						"CHARSET" => $ar_site_tmp["CHARSET"],
						"SERVER_NAME" => $ar_site_tmp["SERVER_NAME"]
					);
				else
					$CACHE_SITE_LANGS[$site] = false;
			}
			if (is_array($CACHE_SITE_LANGS[$site]))
				$arLang[$CACHE_SITE_LANGS[$site]["LANGUAGE_ID"]]++;
		}
		$sContent = str_replace("\x00", "", $sContent);
		foreach ($arLang as $lang => $value)
		{
			$sql_lang = $DB->ForSql($lang);

			$arDoc = stemming($sContent, $lang);
			$docLength = array_sum($arDoc);

			if (BX_SEARCH_VERSION > 1)
			{
				$arPos = stemming($sContent, $lang, /*$bIgnoreStopWords*/
					false, /*$bReturnPositions*/
					true);
				CSearch::RegisterStem($arDoc);
			}

			if ($docLength > 0)
			{
				$doc = "";
				$logDocLength = log($docLength < 20? 20: $docLength);
				$strSqlPrefix = "
						insert into b_search_content_stem
						(SEARCH_CONTENT_ID, LANGUAGE_ID, STEM, TF".(BX_SEARCH_VERSION > 1? ",PS": "").")
						values
				";
				$maxValuesLen = 0;
				$strSqlValues = "";
				foreach ($arDoc as $word => $count)
				{
					$strSqlValues .= ",\n("
						.$ID
						.", '".$sql_lang."'"
						.", ".(BX_SEARCH_VERSION > 1? CSearch::RegisterStem($word): "'".$DB->ForSQL($word)."'")
						.", ".number_format(log($count + 1) / $logDocLength, 4, ".", "")
						.(BX_SEARCH_VERSION > 1?
							", ".number_format($arPos[$word] / $count, 4, ".", ""):
							""
						)
						.")";

					if (strlen($strSqlValues) > $maxValuesLen)
					{
						$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, "File: ".__FILE__."<br>Line: ".__LINE__);
						$strSqlValues = "";
					}
				}
				if (strlen($strSqlValues) > 0)
				{
					$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, "File: ".__FILE__."<br>Line: ".__LINE__);
					$strSqlValues = "";
				}
			}
		}
	}

	public static function TagsIndex($arLID, $ID, $sContent)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$ID = intval($ID);

		if (!is_array($arLID))
			$arLID = Array();
		$sContent = str_replace("\x00", "", $sContent);

		foreach ($arLID as $site_id => $url)
		{
			$sql_site_id = $DB->ForSQL($site_id);

			$arTags = tags_prepare($sContent, $site_id);
			if (!empty($arTags))
			{
				$strSqlPrefix = "
						insert into b_search_tags
						(SEARCH_CONTENT_ID, SITE_ID, NAME)
						values
				";
				$maxValuesLen = 0;
				$strSqlValues = "";
				CSearchTags::CleanCache($arTags);
				foreach ($arTags as $tag)
				{
					$strSqlValues .= ",\n(".$ID.", '".$sql_site_id."', '".$DB->ForSql($tag, 255)."')";
					if (strlen($strSqlValues) > $maxValuesLen)
					{
						$DB->Query($strSqlPrefix.substr($strSqlValues, 2), true, "File: ".__FILE__."<br>Line: ".__LINE__);
						$strSqlValues = "";
					}
				}
				if (strlen($strSqlValues) > 0)
				{
					$DB->Query($strSqlPrefix.substr($strSqlValues, 2), true, "File: ".__FILE__."<br>Line: ".__LINE__);
					$strSqlValues = "";
				}
			}
		}
	}
}

class CSearchQuery extends CAllSearchQuery
{
	var $cnt = 0;

	function BuildWhereClause($word)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$this->cnt++;
		if ($this->cnt > 10)
			return "1=1";

		if (isset($this->m_kav[$word]))
		{
			$word = $this->m_kav[$word];
			$bInQuotes = true;
		}
		else
		{
			$bInQuotes = false;
		}
		$this->m_words[] = $word;
		$word = $DB->ForSql($word, 100);

		if ($this->bTagsSearch)
		{
			if (strpos($word, "%") === false)
			{
				//We can optimize query by doing range scan
				if (is_array($this->m_tags_words))
					$this->m_tags_words[] = $word;
				$op = "=";
			}
			else
			{
				//Optimization is not possible
				$this->m_tags_words = false;
				$op = "like";
			}
			return " exists (
					select * from b_search_tags st
					where st.name ".$op." '".$word."'
					and st.search_content_id = sc.id)\n";
		}
		elseif ($this->bStemming && !$bInQuotes)
		{
			$word = ToUpper($word);
			$this->m_stemmed_words[] = $word;

			if (BX_SEARCH_VERSION > 1)
			{
				$rs = $DB->Query("select ID from b_search_stem where STEM='".$DB->ForSQL($word)."'");
				$ar = $rs->Fetch();
				$this->m_stemmed_words_id[] = intval($ar["ID"]);

				return " exists (
						select * from b_search_content_stem st
						where st.language_id='".$this->m_lang."'
						and st.stem = ".intval($ar["ID"])."
						and st.search_content_id = sc.id)\n";
			}
			else
			{
				return " exists (
						select * from b_search_content_stem st
						where st.language_id='".$this->m_lang."'
						and st.stem = '".$word."'
						and st.search_content_id = sc.id)\n";
			}
		}
		else
		{
			if (BX_SEARCH_VERSION > 1)
				$this->bText = true;

			return " ".$this->m_fields[0]." like '%".str_replace(array("%", "_"), array("[%]", "[_]"), ToUpper($word))."%' ";
		}
	}
}
