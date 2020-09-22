<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/classes/general/title.php");

class CSearchTitle extends CAllSearchTitle
{
	function searchTitle($phrase = "", $nTopCount = 5, $arParams = array(), $bNotFilter = false, $order = "")
	{
		$DB = CDatabase::GetModuleConnection('search');
		$bOrderByRank = ($order == "rank");

		$sqlHaving = array();
		$sqlWords = array();
		if (!empty($this->_arPhrase))
		{
			$last = true;
			foreach (array_reverse($this->_arPhrase, true) as $word => $pos)
			{
				if ($last && !preg_match("/[\\n\\r \\t]$/", $phrase))
				{
					$last = false;
					if (strlen($word) >= $this->minLength)
						$s = $sqlWords[] = "ct.WORD like '".$DB->ForSQL($word)."%'";
					else
						$s = "";
				}
				else
				{
					$s = $sqlWords[] = "ct.WORD = '".$DB->ForSQL($word)."'";
				}

				if ($s)
					$sqlHaving[] = "(sum(case when ".$s." then 1 else 0 end) > 0)";
			}
		}

		if (!empty($sqlWords))
		{
			$bIncSites = false;
			$strSqlWhere = CSearch::__PrepareFilter($arParams, $bIncSites);
			if ($bNotFilter)
			{
				if (!empty($strSqlWhere))
					$strSqlWhere = "NOT (".$strSqlWhere.")";
				else
					$strSqlWhere = "1=0";
			}

			$strSql = "
			SELECT TOP ".($nTopCount + 1)."
				Q.ID
				,sc1.MODULE_ID
				,sc1.ITEM_ID
				,sc1.TITLE
				,sc1.PARAM1
				,sc1.PARAM2
				,sc1.DATE_CHANGE
				,L.DIR
				,L.SERVER_NAME
				,sc1.URL as URL
				,scsite1.URL as SITE_URL
				,scsite1.SITE_ID
				,case
					when charindex(upper(cast(sc1.TITLE as varchar)), '".$DB->ForSQL(ToUpper($phrase))."') > 0 then
						1
					else
						0
				end RANK1
				,Q.RANK2
				,Q.RANK3
			FROM (
				SELECT
					sc.ID
					,scsite.SITE_ID
					,count(1) RANK2
					,min(ct.POS) RANK3
				FROM
					b_search_content_title ct
					inner join b_search_content sc on sc.ID = ct.SEARCH_CONTENT_ID
					INNER JOIN b_search_content_site scsite ON sc.ID = scsite.SEARCH_CONTENT_ID and ct.SITE_ID = scsite.SITE_ID
				WHERE
					".CSearch::CheckPermissions("sc.ID")."
					AND ct.SITE_ID = '".SITE_ID."'
					AND (".implode(" OR ", $sqlWords).")
					".(!empty($strSqlWhere)? "AND ".$strSqlWhere: "")."
				GROUP BY
					sc.ID
					,scsite.SITE_ID
				".(count($sqlHaving) > 1? "HAVING ".implode(" AND ", $sqlHaving): "")."
			) Q
			INNER JOIN b_search_content sc1 ON Q.ID = sc1.ID
			INNER JOIN b_search_content_site scsite1 ON sc1.ID = scsite1.SEARCH_CONTENT_ID and Q.SITE_ID = scsite1.SITE_ID
			INNER JOIN b_lang L ON scsite1.SITE_ID = L.LID
			ORDER BY ".(
				$bOrderByRank?
					"RANK1 DESC, RANK2 DESC, RANK3 ASC":
					"DATE_CHANGE DESC, RANK1 DESC, RANK2 DESC, RANK3 ASC"
				);

			$r = $DB->Query($strSql);
			parent::CDBResult($r);
			return true;
		}
		else
		{

			return false;
		}
	}

	function getRankFunction($phrase)
	{
		$DB = CDatabase::GetModuleConnection('search');
		return "case when charindex(upper(cast(sc.TITLE as varchar)), '".$DB->ForSQL(ToUpper($phrase))."') > 0 then 1 else 0 end";
	}

	function getSqlOrder($bOrderByRank)
	{
		if ($bOrderByRank)
			return "RANK1 DESC";
		else
			return "DATE_CHANGE DESC, RANK1 DESC";
	}
}
