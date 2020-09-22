<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/classes/general/sla.php");

class CTicketSLA extends CAllTicketSLA
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." <br>Class: CTicketSLA<br>File: ".__FILE__;
	}

	// get SLA list
	function GetList(&$arSort, $arFilter=Array(), &$is_filtered)
	{
		$err_mess = (CTicketSLA::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$is_filtered = false;

		// filter params correct
		if (CTicket::CheckFilter($arFilter)):

			$arSqlSearch = Array();

			if (is_array($arFilter) && count($arFilter)>0):

				$filterKeys = array_keys($arFilter);
				$filterKeysCount = count($filterKeys);
				for ($i=0; $i<$filterKeysCount; $i++):

					$key = $filterKeys[$i];
					$val = $arFilter[$filterKeys[$i]];
					if ((is_array($val) && count($val)<=0) || (!is_array($val) && (strlen($val)<=0 || $val==='NOT_REF')))
						continue;
					$match_value_set = (in_array($key."_EXACT_MATCH", $filterKeys)) ? true : false;
					$key = strtoupper($key);
					if (is_array($val)) $val = implode(" | ",$val);
					switch($key) :

						case "ID":
						case "SLA_ID":
							$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
							$arSqlSearch[] = GetFilterQuery("S.".$key,$val,$match);
							break;
						case "NAME":
						case "DESCRIPTION":
						case "DEADLINE_SOURCE":
							$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
							$arSqlSearch[] = GetFilterQuery("S.".$key, $val, $match);
							break;
						case "SITE":
							$val .= " | ALL";
							$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
							$arSqlSearch1[] = GetFilterQuery("SS.SITE_ID", $val, $match);
							$strSqlSearch1 = GetFilterSqlSearch($arSqlSearch1);
							$where = " and exists (SELECT 'x' FROM b_ticket_sla_2_site SS WHERE $strSqlSearch1 and S.ID = SS.SLA_ID) ";
							break;

					endswitch;
				endfor;
			endif;
		endif;

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

		$arSort = is_array($arSort) ? $arSort : array();
		if (count($arSort)>0)
		{
			$ar1 = array_merge($DB->GetTableFieldsList("b_ticket_sla"), array());
			$ar2 = array_keys($arSort);
			$arDiff = array_diff($ar2, $ar1);
			if (is_array($arDiff) && count($arDiff)>0)
			{
				foreach($arDiff as $value)
				{
					unset($arSort[$value]);
				}
			}
		}
		if (count($arSort)<=0) $arSort = array("PRIORITY" => "DESC");
		while(list($by, $order) = each($arSort))
		{
			if( strtoupper( $order ) != "DESC" )
			{
				$order="ASC";
			}
			if ($by=="RESPONSE_TIME")
			{
				$arSqlOrder[] = "DECODE(RESPONSE_TIME_UNIT, 'day', 3, 'hour', 2, 'minute', 1) $order";
				$arSqlOrder[] = $by." ".$order;
			}
			else
			{
				$arSqlOrder[] = $by." ".$order;
			}
		}
		if (is_array($arSqlOrder) && count($arSqlOrder)>0) $strSqlOrder = " ORDER BY ".implode(",", $arSqlOrder);

		$strSql = "
			SELECT
				S.*,
				DECODE(S.RESPONSE_TIME_UNIT,
					'day', S.RESPONSE_TIME*1440,
					'hour', S.RESPONSE_TIME*60,
					'minute', S.RESPONSE_TIME)					M_RESPONSE_TIME,
				DECODE(S.NOTICE_TIME_UNIT,
					'day', S.NOTICE_TIME*1440,
					'hour', S.NOTICE_TIME*60,
					'minute', S.NOTICE_TIME)					M_NOTICE_TIME,
				S.ID											REFERENCE_ID,
				S.NAME											REFERENCE,
				".$DB->DateToCharFunction("S.DATE_MODIFY")."	DATE_MODIFY_F,
				".$DB->DateToCharFunction("S.DATE_CREATE")."	DATE_CREATE_F
			FROM
				b_ticket_sla S
			WHERE
			$strSqlSearch
			$where
			$strSqlOrder
			";

		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $rs;
	}
}

?>
