<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/classes/general/advertising.php");

/*****************************************************************
				����� "��������� ��������"
*****************************************************************/

class CAdvContract extends CAdvContract_all
{
	public static function err_mess()
	{
		$module_id = "advertising";
		return "<br>Module: ".$module_id."<br>Class: CAdvContract<br>File: ".__FILE__;
	}

	// �������� ������ ����������
	public static function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAdvContract::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB, $USER, $APPLICATION, $strError;
		if ($CHECK_RIGHTS=="Y")
		{
			$USER_ID = intval($USER->GetID());
			$isAdmin = CAdvContract::IsAdmin();
			$isDemo = CAdvContract::IsDemo();
			$isManager = CAdvContract::IsManager();
			$isAdvertiser = CAdvContract::IsAdvertiser();
		}
		else
		{
			if (is_object($USER)) $USER_ID = intval($USER->GetID()); else $USER_ID = 0;
			$isAdmin = true;
			$isDemo = true;
			$isManager = true;
			$isAdvertiser = true;
		}
		$arSqlSearch = array();
		$arSqlSearch_1 = array();
		$strSqlSearch = "";
		$strSqlSearch_1 = "";
		$lamp = "
		decode (sign(sysdate - nvl(C.DATE_SHOW_FROM, sysdate-1)),
			1, decode(sign(sysdate - nvl(C.DATE_SHOW_TO, sysdate+1)),
					-1, decode(C.ACTIVE,
							'Y', decode(sign(C.SHOW_COUNT - nvl(C.MAX_SHOW_COUNT,999999999999999999)),
									-1, decode(sign(C.VISITOR_COUNT - nvl(C.MAX_VISITOR_COUNT,999999999999999999)),
											-1, decode(sign(C.CLICK_COUNT - nvl(C.MAX_CLICK_COUNT,999999999999999999)),
													-1, 'green',
													'red'),
											'red'),
									'red'),
							'red'),
					'red'),
			'red')
			";

		if (CAdvContract::CheckFilter($arFilter))
		{
			if (is_array($arFilter))
			{
				$filter_keys = array_keys($arFilter);
				for ($i=0, $n = count($filter_keys); $i < $n; $i++)
				{
					$key = $filter_keys[$i];
					$val = $arFilter[$filter_keys[$i]];
					if (strlen($val)<=0 || "$val"=="NOT_REF") continue;
					if (is_array($val) && count($val)<=0) continue;
					$match_value_set = (in_array($key."_EXACT_MATCH", $filter_keys)) ? true : false;
					$key = strtoupper($key);
					switch($key)
					{
						case "ID":
							$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
							$arSqlSearch[] = GetFilterQuery("C.ID",$val,$match);
							break;
						case "SITE":
							if (is_array($val)) $val = implode(" | ", $val);
							$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
							$arSqlSearch_1[] = GetFilterQuery("CS.SITE_ID", $val, $match);
							$strSqlSearch_1 = GetFilterSqlSearch($arSqlSearch_1);
							$where = " and exists (SELECT 'x' FROM b_adv_contract_2_site CS WHERE $strSqlSearch_1 and C.ID = CS.CONTRACT_ID) ";
							break;
						case "DATE_MODIFY_1":
							$arSqlSearch[] = "C.DATE_MODIFY>=".$DB->CharToDateFunction($val, "SHORT");
							break;
						case "DATE_MODIFY_2":
							$arSqlSearch[] = "C.DATE_MODIFY<".$DB->CharToDateFunction($val, "SHORT")."+1";
							break;
						case "NAME":
						case "DESCRIPTION":
						case "ADMIN_COMMENTS":
							$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
							$arSqlSearch[] = GetFilterQuery("C.".$key, $val, $match);
							break;
						case "LAMP":
							$arSqlSearch[] = " ".$lamp." = '".$DB->ForSQL($val)."'";
							break;
						case "OWNER":
							$from = " INNER JOIN b_user U ON (U.ID = CU.USER_ID) ";
							$admin_from_1 = " INNER JOIN b_adv_contract_2_user CU ON (CU.CONTRACT_ID=C.ID) ";
							$admin_from_2 = " INNER JOIN b_user U ON (U.ID = CU.USER_ID) ";
							$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
							$arSqlSearch[] = GetFilterQuery("CU.USER_ID, U.LOGIN, U.NAME, U.LAST_NAME", $val, $match);
							break;
						case "BANNER_COUNT_1":
							$arSqlSearch_h[] = "count(distinct B.ID)>='".intval($val)."'";
							break;
						case "BANNER_COUNT_2":
							$arSqlSearch_h[] = "count(distinct B.ID)<='".intval($val)."'";
							break;
						case "SHOW_COUNT_1":
							$arSqlSearch[] = "C.SHOW_COUNT>='".intval($val)."'";
							break;
						case "SHOW_COUNT_2":
							$arSqlSearch[] = "C.SHOW_COUNT<='".intval($val)."'";
							break;
						case "EMAIL_COUNT_1":
							$arSqlSearch[] = "C.EMAIL_COUNT>='".intval($val)."'";
							break;
						case "EMAIL_COUNT_2":
							$arSqlSearch[] = "C.EMAIL_COUNT<='".intval($val)."'";
							break;
						case "VISITOR_COUNT_1":
							$arSqlSearch[] = "C.VISITOR_COUNT>='".intval($val)."'";
							break;
						case "VISITOR_COUNT_2":
							$arSqlSearch[] = "C.VISITOR_COUNT<='".intval($val)."'";
							break;
						case "CLICK_COUNT_1":
							$arSqlSearch[] = "C.CLICK_COUNT>='".intval($val)."'";
							break;
						case "CLICK_COUNT_2":
							$arSqlSearch[] = "C.CLICK_COUNT<='".intval($val)."'";
							break;
						case "CTR_1":
							$arSqlSearch[] = "decode(C.SHOW_COUNT,0,0,round((C.CLICK_COUNT*100)/C.SHOW_COUNT,2))>='".DoubleVal(str_replace(',', '.', $val))."'";
							break;
						case "CTR_2":
							$arSqlSearch[] = "decode(C.SHOW_COUNT,0,0,round((C.CLICK_COUNT*100)/C.SHOW_COUNT,2))<='".DoubleVal(str_replace(',', '.', $val))."'";
							break;
						case "USER_PERMISSIONS":
							$admin_from_1 = " INNER JOIN b_adv_contract_2_user CU ON (CU.CONTRACT_ID=C.ID) ";
							$arSqlSearch[] = GetFilterQuery("CU.PERMISSION", $val, "N");
							break;
					}
				}
			}
		}

		if ($by == "s_id")						$strSqlOrder = "ORDER BY C.ID";
		elseif ($by == "s_lamp")				$strSqlOrder = "ORDER BY LAMP";
		elseif ($by == "s_date_modify")			$strSqlOrder = "ORDER BY C.DATE_MODIFY";
		elseif ($by == "s_name")				$strSqlOrder = "ORDER BY C.NAME";
		elseif ($by == "s_description")			$strSqlOrder = "ORDER BY C.DESCRIPTION";
		elseif ($by == "s_modified_by")			$strSqlOrder = "ORDER BY C.MODIFIED_BY";
		elseif ($by == "s_active")				$strSqlOrder = "ORDER BY C.ACTIVE";
		elseif ($by == "s_weight")				$strSqlOrder = "ORDER BY C.WEIGHT";
		elseif ($by == "s_sort")				$strSqlOrder = "ORDER BY nvl(C.SORT,0)";
		elseif ($by == "s_banner_count")		$strSqlOrder = "ORDER BY BANNER_COUNT";
		elseif ($by == "s_ctr")					$strSqlOrder = "ORDER BY CTR";
		elseif ($by == "s_show_count")			$strSqlOrder = "ORDER BY C.SHOW_COUNT";
		elseif ($by == "s_max_show_count")		$strSqlOrder = "ORDER BY nvl(C.MAX_SHOW_COUNT,0)";
		elseif ($by == "s_click_count")			$strSqlOrder = "ORDER BY C.CLICK_COUNT";
		elseif ($by == "s_max_click_count")		$strSqlOrder = "ORDER BY nvl(C.MAX_CLICK_COUNT,0)";
		elseif ($by == "s_visitor_count")		$strSqlOrder = "ORDER BY C.VISITOR_COUNT";
		elseif ($by == "s_max_visitor_count")	$strSqlOrder = "ORDER BY nvl(C.MAX_VISITOR_COUNT,0)";
		else
		{
			$by = "s_sort";
			$strSqlOrder = "ORDER BY nvl(C.SORT,0)";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSqlSearch_h = GetFilterSqlSearch($arSqlSearch_h);

		if ($isAdmin || $isDemo || $isManager)
		{
			$strSql = "
				SELECT
					$lamp LAMP,
					C.ID, C.ACTIVE, C.NAME, C.DESCRIPTION, C.ADMIN_COMMENTS, C.WEIGHT, C.SORT, C.MAX_SHOW_COUNT, C.SHOW_COUNT, C.EMAIL_COUNT, C.CREATED_BY, C.MODIFIED_BY, C.MAX_CLICK_COUNT, C.CLICK_COUNT, C.MAX_VISITOR_COUNT, C.VISITOR_COUNT, C.DEFAULT_STATUS_SID, C.KEYWORDS,
					decode(C.SHOW_COUNT,0,0,round((C.CLICK_COUNT*100)/C.SHOW_COUNT,2))	CTR,
					".$DB->DateToCharFunction("C.DATE_SHOW_FROM")."		DATE_SHOW_FROM,
					".$DB->DateToCharFunction("C.DATE_SHOW_TO")."			DATE_SHOW_TO,
					".$DB->DateToCharFunction("C.DATE_CREATE")."					DATE_CREATE,
					".$DB->DateToCharFunction("C.DATE_MODIFY")."					DATE_MODIFY,
					count(distinct B.ID)									BANNER_COUNT
				FROM
					b_adv_contract C
				LEFT JOIN b_adv_banner B ON (B.CONTRACT_ID=C.ID)
				$admin_from_1
				$admin_from_2
				WHERE
				$strSqlSearch
				$where
				GROUP BY
					C.ID, C.ACTIVE, C.NAME, C.DESCRIPTION, C.ADMIN_COMMENTS, C.WEIGHT, C.SORT, C.MAX_SHOW_COUNT,
					C.SHOW_COUNT, C.MAX_CLICK_COUNT, C.CLICK_COUNT, C.MAX_VISITOR_COUNT, C.VISITOR_COUNT, C.EMAIL_COUNT, C.CREATED_BY, C.MODIFIED_BY, C.DATE_SHOW_FROM, C.DATE_SHOW_TO, C.DATE_CREATE, C.DATE_MODIFY, C.DEFAULT_STATUS_SID, C.KEYWORDS
				HAVING
				$strSqlSearch_h
				$strSqlOrder
				";
		}
		else
		{
			$strSql = "
				SELECT
					$lamp LAMP,
					C.ID, C.ACTIVE, C.NAME, C.DESCRIPTION, C.ADMIN_COMMENTS, C.WEIGHT, C.SORT, C.MAX_SHOW_COUNT, C.SHOW_COUNT, C.MAX_CLICK_COUNT, C.CLICK_COUNT, C.EMAIL_COUNT, C.CREATED_BY, C.MODIFIED_BY, C.DEFAULT_STATUS_SID, C.MAX_VISITOR_COUNT, C.VISITOR_COUNT, C.KEYWORDS,
					decode(C.SHOW_COUNT,0,0,round((C.CLICK_COUNT*100)/C.SHOW_COUNT,2))	CTR,
					".$DB->DateToCharFunction("C.DATE_SHOW_FROM")."		DATE_SHOW_FROM,
					".$DB->DateToCharFunction("C.DATE_SHOW_TO")."			DATE_SHOW_TO,
					".$DB->DateToCharFunction("C.DATE_CREATE")."					DATE_CREATE,
					".$DB->DateToCharFunction("C.DATE_MODIFY")."					DATE_MODIFY,
					count(distinct B.ID)									BANNER_COUNT
				FROM
					b_adv_contract C
				LEFT JOIN b_adv_banner B ON (B.CONTRACT_ID=C.ID)
				INNER JOIN b_adv_contract_2_user CU ON (CU.CONTRACT_ID=C.ID and CU.USER_ID=$USER_ID)
				$from
				WHERE
				$strSqlSearch
				$where
				GROUP BY
					C.ID, C.ACTIVE, C.NAME, C.DESCRIPTION, C.ADMIN_COMMENTS, C.WEIGHT, C.SORT, C.MAX_SHOW_COUNT,
					C.SHOW_COUNT, C.MAX_CLICK_COUNT, C.CLICK_COUNT, C.MAX_VISITOR_COUNT, C.VISITOR_COUNT, C.EMAIL_COUNT, C.CREATED_BY, C.MODIFIED_BY, C.DATE_SHOW_FROM, C.DATE_SHOW_TO, C.DATE_CREATE, C.DATE_MODIFY, C.DEFAULT_STATUS_SID, C.KEYWORDS
				HAVING
				$strSqlSearch_h
				$strSqlOrder
				";
		}
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}
}

/*****************************************************************
					����� "��������� ������"
*****************************************************************/

class CAdvBanner extends CAdvBanner_all
{
	public static function err_mess()
	{
		$module_id = "advertising";
		return "<br>Module: ".$module_id."<br>Class: CAdvBanner<br>File: ".__FILE__;
	}

	public static function Update($arFields, $BANNER_ID)
	{
		$err_mess = (CAdvBanner::err_mess())."<br>Function: Update<br>Line: ";
		global $DB;
		$str = "";
		while (list($field,$value)=each($arFields))
		{
			if (strlen($value)<=0) $str .= " $field = NULL, ";
			else
			{
				$str .= ($field=="CODE" || $field=="TEMPLATE") ? " $field = EMPTY_CLOB(), " : " $field = $value, ";
			}
		}
		$str = TrimEx($str,",");
		$strSql = "UPDATE b_adv_banner SET ".$str." WHERE ID='".$DB->ForSql($BANNER_ID, 255)."'";

		$arBinds = array(
			"CODE" => $arFields["CODE"],
			"TEMPLATE" => $arFields["TEMPLATE"]
		);

		$DB->QueryBind($strSql, $arBinds);
	}

	public static function getCTRSQL()
	{
		return 'DECODE(SUM(D.SHOW_COUNT),0, 0, round((SUM(D.CLICK_COUNT)*100)/SUM(D.SHOW_COUNT),2))	CTR';
	}

	public static function addBindField($field, $bannerField, &$modifyStatus)
	{
		if ($field != $bannerField)
		{
			$modifyStatus = 'Y';
		}
		
		return $field;
	}

	public static function Add($arFields)
	{
		$err_mess = (CAdvBanner::err_mess())."<br>Function: Add<br>Line: ";
		global $DB;
		$str1 = "";
		$str2 = "";
		while (list($field,$value)=each($arFields))
		{
			$str1 .= $field.", ";
			if (strlen($value)<=0) $str2 .= " NULL, ";
			else
			{
				$str2 .= ($field=="CODE" || $field=="TEMPLATE") ? " EMPTY_CLOB(), " : " $value, ";
			}
		}
		$str1 = TrimEx($str1,",");
		$str2 = TrimEx($str2,",");
		$BANNER_ID = $DB->NextID("sq_b_adv_banner");
		$strSql = "INSERT INTO b_adv_banner (ID, ".$str1.") VALUES ($BANNER_ID, ".$str2.")";

		$arBinds = array(
			"CODE" => $arFields["CODE"],
			"TEMPLATE" => $arFields["TEMPLATE"]
		);
		
		$DB->QueryBind($strSql, $arBinds);

		return $BANNER_ID;
	}

	public static function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAdvBanner::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB, $USER, $APPLICATION;
		if ($CHECK_RIGHTS=="Y")
		{
			$USER_ID = intval($USER->GetID());
			$isAdmin = CAdvContract::IsAdmin();
			$isDemo = CAdvContract::IsDemo();
			$isManager = CAdvContract::IsManager();
			$isAdvertiser = CAdvContract::IsAdvertiser();
		}
		else
		{
			if (is_object($USER)) $USER_ID = intval($USER->GetID()); else $USER_ID = 0;
			$isAdmin = true;
			$isDemo = true;
			$isManager = true;
			$isAdvertiser = true;
		}
		$arSqlSearch = array();
		$arSqlSearch_1 = array();
		$strSqlSearch = "";

		$lamp = "
		decode(sign(sysdate - nvl(C.DATE_SHOW_FROM,sysdate-1)),
			1, decode(sign(sysdate - nvl(B.DATE_SHOW_FROM,sysdate-1)),
				1, decode(sign(sysdate - nvl(C.DATE_SHOW_TO, sysdate+1)),
					-1, decode(sign(sysdate - nvl(B.DATE_SHOW_TO, sysdate+1)),
						-1, decode(C.ACTIVE,
							'Y', decode(B.ACTIVE,
								'Y', decode(T.ACTIVE,
									'Y', decode(B.STATUS_SID,
										'PUBLISHED',decode(sign(C.SHOW_COUNT - nvl(C.MAX_SHOW_COUNT,999999999999999999)),
											-1, decode(sign(C.CLICK_COUNT - nvl(C.MAX_CLICK_COUNT,999999999999999999)),
												-1, decode(sign(C.VISITOR_COUNT - nvl(C.MAX_VISITOR_COUNT,999999999999999999)),
													-1, decode(sign(B.SHOW_COUNT - nvl(B.MAX_SHOW_COUNT,999999999999999999)),
														-1, decode(sign(B.CLICK_COUNT - nvl(B.MAX_CLICK_COUNT,999999999999999999)),
															-1, decode(sign(B.VISITOR_COUNT - nvl(B.MAX_VISITOR_COUNT,999999999999999999)),
																-1, 'green',
																'red'),
															'red'),
														'red'),
													'red'),
												'red'),
											'red'),
										'red'),
									'red'),
								'red'),
							'red'),
						'red'),
					'red'),
				'red'),
			'red')
		";


		$DONT_USE_CONTRACT = COption::GetOptionString("advertising", "DONT_USE_CONTRACT", "N");

		if ($DONT_USE_CONTRACT == "Y")
		{
			$lamp = "
			case
				when
					sysdate > nvl(B.DATE_SHOW_FROM,sysdate-1)
					and sysdate < nvl(B.DATE_SHOW_TO, sysdate+1)
					and B.ACTIVE='Y'
					and T.ACTIVE='Y'
					and B.STATUS_SID='PUBLISHED'
					and (B.MAX_SHOW_COUNT is null or B.SHOW_COUNT < B.MAX_SHOW_COUNT)
					and (B.MAX_CLICK_COUNT is null or B.CLICK_COUNT < B.MAX_CLICK_COUNT)
					and (B.MAX_VISITOR_COUNT is null or B.VISITOR_COUNT < B.MAX_VISITOR_COUNT)
				then
					'green'
				else
					'red'
			end
			";
		}
		else
		{
			$lamp = "
			case
				when
					sysdate > nvl(C.DATE_SHOW_FROM,sysdate-1)
					and sysdate > nvl(B.DATE_SHOW_FROM,sysdate-1)
					and sysdate < nvl(C.DATE_SHOW_TO, sysdate+1)
					and sysdate < nvl(B.DATE_SHOW_TO, sysdate+1)
					and C.ACTIVE='Y'
					and B.ACTIVE='Y'
					and T.ACTIVE='Y'
					and B.STATUS_SID='PUBLISHED'
					and (C.MAX_SHOW_COUNT is null or C.SHOW_COUNT < C.MAX_SHOW_COUNT)
					and (C.MAX_CLICK_COUNT is null or C.CLICK_COUNT < C.MAX_CLICK_COUNT)
					and (C.MAX_VISITOR_COUNT is null or C.VISITOR_COUNT < C.MAX_VISITOR_COUNT)
					and (B.MAX_SHOW_COUNT is null or B.SHOW_COUNT < B.MAX_SHOW_COUNT)
					and (B.MAX_CLICK_COUNT is null or B.CLICK_COUNT < B.MAX_CLICK_COUNT)
					and (B.MAX_VISITOR_COUNT is null or B.VISITOR_COUNT < B.MAX_VISITOR_COUNT)
					then
					'green'
				else
					'red'
			end
			";
		}



		if (is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for ($i=0, $n = count($filter_keys); $i < $n; $i++)
			{
				$key = $filter_keys[$i];
				$val = $arFilter[$filter_keys[$i]];
				if(is_array($val) && count($val)<=0)
					continue;
				if($val == '' || $val == "NOT_REF")
					continue;
				$match_value_set = (in_array($key."_EXACT_MATCH", $filter_keys)) ? true : false;
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("B.ID",$val,$match);
						break;
					case "LAMP":
						$arSqlSearch[] = " ".$lamp." = '".$DB->ForSQL($val)."'";
						break;
					case "SITE":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch_1[] = GetFilterQuery("BS.SITE_ID", $val, $match);
						$strSqlSearch_1 = GetFilterSqlSearch($arSqlSearch_1);
						$where = " and exists (SELECT 'x' FROM b_adv_banner_2_site BS WHERE $strSqlSearch_1 and B.ID = BS.BANNER_ID) ";
						break;
					case "DATE_MODIFY_1":
						$arSqlSearch[] = "B.DATE_MODIFY>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_MODIFY_2":
						$arSqlSearch[] = "B.DATE_MODIFY<".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
					case "DATE_CREATE_1":
						$arSqlSearch[] = "B.DATE_CREATE>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_CREATE_2":
						$arSqlSearch[] = "B.DATE_CREATE<".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
					case "DATE_SHOW_FROM_1":
						$arSqlSearch[] = "B.DATE_SHOW_FROM>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_SHOW_FROM_2":
						$arSqlSearch[] = "B.DATE_SHOW_FROM<".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
					case "DATE_SHOW_TO_1":
						$arSqlSearch[] = "B.DATE_SHOW_TO>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_SHOW_TO_2":
						$arSqlSearch[] = "B.DATE_SHOW_TO<".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
					case "ACTIVE":
					case "FIX_SHOW":
						$arSqlSearch[] = ($val=="Y") ? "B.".$key."='Y'" : "B.".$key."='N'";
						break;
					case "WEIGHT_1":
						$arSqlSearch[] = "nvl(B.WEIGHT,0)>='".intval($val)."'";
						break;
					case "WEIGHT_2":
						$arSqlSearch[] = "nvl(B.WEIGHT,0)<='".intval($val)."'";
						break;
					case "MAX_VISITOR_COUNT_1":
						$arSqlSearch[] = "nvl(B.MAX_VISITOR_COUNT,0)>='".intval($val)."'";
						break;
					case "MAX_VISITOR_COUNT_2":
						$arSqlSearch[] = "nvl(B.MAX_VISITOR_COUNT,0)<='".intval($val)."'";
						break;
					case "VISITOR_COUNT_1":
						$arSqlSearch[] = "nvl(B.VISITOR_COUNT,0)>='".intval($val)."'";
						break;
					case "VISITOR_COUNT_2":
						$arSqlSearch[] = "nvl(B.VISITOR_COUNT,0)<='".intval($val)."'";
						break;
					case "MAX_SHOW_COUNT_1":
						$arSqlSearch[] = "nvl(B.MAX_SHOW_COUNT,0)>='".intval($val)."'";
						break;
					case "MAX_SHOW_COUNT_2":
						$arSqlSearch[] = "nvl(B.MAX_SHOW_COUNT,0)<='".intval($val)."'";
						break;
					case "SHOW_COUNT_1":
						$arSqlSearch[] = "nvl(B.SHOW_COUNT,0)>='".intval($val)."'";
						break;
					case "SHOW_COUNT_2":
						$arSqlSearch[] = "nvl(B.SHOW_COUNT,0)<='".intval($val)."'";
						break;
					case "MAX_CLICK_COUNT_1":
						$arSqlSearch[] = "nvl(B.MAX_CLICK_COUNT,0)>='".intval($val)."'";
						break;
					case "MAX_CLICK_COUNT_2":
						$arSqlSearch[] = "nvl(B.MAX_CLICK_COUNT,0)<='".intval($val)."'";
						break;
					case "CLICK_COUNT_1":
						$arSqlSearch[] = "nvl(B.CLICK_COUNT,0)>='".intval($val)."'";
						break;
					case "CLICK_COUNT_2":
						$arSqlSearch[] = "nvl(B.CLICK_COUNT,0)<='".intval($val)."'";
						break;
					case "CTR_1":
						$arSqlSearch[] = "decode(B.SHOW_COUNT,0,0,round((B.CLICK_COUNT*100)/B.SHOW_COUNT,2))>='".DoubleVal(str_replace(',', '.', $val))."'";
						break;
					case "CTR_2":
						$arSqlSearch[] = "decode(B.SHOW_COUNT,0,0,round((B.CLICK_COUNT*100)/B.SHOW_COUNT,2))<='".DoubleVal(str_replace(',', '.', $val))."'";
						break;
					case "GROUP":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("B.GROUP_SID", $val, $match);
						break;
					case "STATUS":
					case "STATUS_SID":
						if (is_array($val)) $val = implode(" | ",$val);
						$arSqlSearch[] = GetFilterQuery("B.STATUS_SID", $val, "N");
						break;
					case "CONTRACT_ID":
						if (is_array($val)) $val = implode(" | ",$val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("B.CONTRACT_ID", $val, $match);
						break;
					case "CONTRACT":
						if (is_array($val)) $val = implode(" | ",$val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("B.CONTRACT_ID, C.NAME, C.DESCRIPTION", $val, $match);
						break;
					case "TYPE_SID":
						if (is_array($val)) $val = implode(" | ",$val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("B.TYPE_SID", $val, $match);
						break;
					case "TYPE":
						if (is_array($val)) $val = implode(" | ",$val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("B.TYPE_SID, T.NAME, T.DESCRIPTION", $val, $match);
						break;
					case "SHOW_USER_GROUP":
						if($val=="Y")
							$arSqlSearch[] = "B.SHOW_USER_GROUP='Y'";
						else
							$arSqlSearch[] = "B.SHOW_USER_GROUP <> 'Y'";
						break;
					case "NAME":
					case "CODE":
					case "COMMENTS":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("B.".$key, $val, $match);
						break;

					// ������������� �� ������ �������
					case "LANG":
					case "FIRST_SITE_ID":
						$arSqlSearch[] = GetFilterQuery("B.FIRST_SITE_ID",$val,"N");
						break;

				}
			}
		}

		if ($by == "s_id") $strSqlOrder = " ORDER BY B.ID ";
		elseif ($by == "s_lamp") $strSqlOrder = " ORDER BY LAMP ";
		elseif ($by == "s_name") $strSqlOrder = " ORDER BY B.NAME ";
		elseif ($by == "s_type_sid") $strSqlOrder = " ORDER BY B.TYPE_SID ";
		elseif ($by == "s_contract_id") $strSqlOrder = " ORDER BY B.CONTRACT_ID ";
		elseif ($by == "s_group_sid") $strSqlOrder = " ORDER BY B.GROUP_SID ";
		elseif ($by == "s_visitor_count") $strSqlOrder = " ORDER BY B.VISITOR_COUNT ";
		elseif ($by == "s_max_visitor_count") $strSqlOrder = " ORDER BY nvl(B.MAX_VISITOR_COUNT,0) ";
		elseif ($by == "s_show_count") $strSqlOrder = " ORDER BY B.SHOW_COUNT ";
		elseif ($by == "s_max_show_count") $strSqlOrder = " ORDER BY nvl(B.MAX_SHOW_COUNT,0) ";
		elseif ($by == "s_date_last_show") $strSqlOrder = " ORDER BY B.DATE_LAST_SHOW ";
		elseif ($by == "s_click_count") $strSqlOrder = " ORDER BY B.CLICK_COUNT ";
		elseif ($by == "s_max_click_count") $strSqlOrder = " ORDER BY nvl(B.MAX_CLICK_COUNT,0) ";
		elseif ($by == "s_date_last_click") $strSqlOrder = " ORDER BY B.DATE_LAST_CLICK ";
		elseif ($by == "s_active") $strSqlOrder = " ORDER BY B.ACTIVE ";
		elseif ($by == "s_weight") $strSqlOrder = " ORDER BY B.WEIGHT ";
		elseif ($by == "s_status_sid") $strSqlOrder = " ORDER BY B.STATUS_SID ";
		elseif ($by == "s_date_show_from") $strSqlOrder = " ORDER BY B.DATE_SHOW_FROM ";
		elseif ($by == "s_date_show_to") $strSqlOrder = " ORDER BY B.DATE_SHOW_TO ";
		elseif ($by == "s_dropdown") $strSqlOrder = " ORDER BY B.CONTRACT_ID desc, B.ID ";
		elseif ($by == "s_ctr") $strSqlOrder = " ORDER BY CTR ";
		elseif ($by == "s_date_create") $strSqlOrder = " ORDER BY B.DATE_CREATE ";
		elseif ($by == "s_date_modify") $strSqlOrder = " ORDER BY B.DATE_MODIFY ";
		else
		{
			$strSqlOrder = " ORDER BY B.ID ";
			$by = "s_id";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order = "desc";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

		if ($isAdmin || $isDemo || $isManager)
		{
			$strSql = "
				SELECT
					$lamp																LAMP,
					B.*,
					B.FIRST_SITE_ID,
					B.FIRST_SITE_ID														LID,
					decode(B.SHOW_COUNT,0,0,round((B.CLICK_COUNT*100)/B.SHOW_COUNT,2))	CTR,
					".$DB->DateToCharFunction("B.DATE_LAST_SHOW")."						DATE_LAST_SHOW,
					".$DB->DateToCharFunction("B.DATE_LAST_CLICK")."					DATE_LAST_CLICK,
					".$DB->DateToCharFunction("B.DATE_SHOW_FROM")."			DATE_SHOW_FROM,
					".$DB->DateToCharFunction("B.DATE_SHOW_TO")."				DATE_SHOW_TO,
					".$DB->DateToCharFunction("B.DATE_SHOW_FIRST")."			DATE_SHOW_FIRST,
					".$DB->DateToCharFunction("B.DATE_CREATE")."						DATE_CREATE,
					".$DB->DateToCharFunction("B.DATE_MODIFY")."						DATE_MODIFY,
					C.NAME																CONTRACT_NAME,
					T.NAME																TYPE_NAME
				FROM
					b_adv_banner B
				INNER JOIN b_adv_type T ON (T.SID = B.TYPE_SID)
				INNER JOIN b_adv_contract C ON (C.ID = B.CONTRACT_ID)
				WHERE
				$strSqlSearch
				$where
				$strSqlOrder
				";
		}
		else
		{
			$strSql = "
				SELECT
					$lamp																LAMP,
					B.*,
					B.FIRST_SITE_ID,
					B.FIRST_SITE_ID														LID,
					decode(B.SHOW_COUNT,0,0,round((B.CLICK_COUNT*100)/B.SHOW_COUNT,2))	CTR,
					".$DB->DateToCharFunction("B.DATE_LAST_SHOW")."						DATE_LAST_SHOW,
					".$DB->DateToCharFunction("B.DATE_LAST_CLICK")."					DATE_LAST_CLICK,
					".$DB->DateToCharFunction("B.DATE_SHOW_FROM")."			DATE_SHOW_FROM,
					".$DB->DateToCharFunction("B.DATE_SHOW_TO")."				DATE_SHOW_TO,
					".$DB->DateToCharFunction("B.DATE_SHOW_FIRST")."			DATE_SHOW_FIRST,
					".$DB->DateToCharFunction("B.DATE_CREATE")."						DATE_CREATE,
					".$DB->DateToCharFunction("B.DATE_MODIFY")."						DATE_MODIFY,
					C.NAME																CONTRACT_NAME,
					T.NAME																TYPE_NAME
				FROM
					b_adv_banner B
				INNER JOIN b_adv_type T ON (T.SID = B.TYPE_SID)
				INNER JOIN b_adv_contract C ON (C.ID = B.CONTRACT_ID)
				WHERE
				$strSqlSearch
				and exists (SELECT 'x' FROM b_adv_contract_2_user CU WHERE CU.CONTRACT_ID=C.ID and CU.USER_ID=$USER_ID)
				$where
				$strSqlOrder
				";
		}
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}

	// ��������� ���� �� ����������� �������
	public static function Click($BANNER_ID)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: Click<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0) return false;

		$strSql = "
			SELECT
				B.FIX_CLICK,
				B.CONTRACT_ID
			FROM
				b_adv_banner B
			WHERE
				B.ID = $BANNER_ID
			";
		$rsBanner = $DB->Query($strSql, false, $err_mess.__LINE__);
		if ($arBanner = $rsBanner->Fetch())
		{
			if ($arBanner["FIX_CLICK"]=="Y")
			{

				/********************
					������� ������
				********************/

				// ��������� �������
				$arFields = Array(
						"CLICK_COUNT"		=> "CLICK_COUNT + 1",
						"DATE_LAST_CLICK"	=> $DB->GetNowFunction(),
						);
				$rows = $DB->Update("b_adv_banner",$arFields,"WHERE ID = $BANNER_ID",$err_mess.__LINE__);
				if (intval($rows)>0)
				{
					foreach (getModuleEvents('advertising', 'onBannerClick', true) as $arEvent)
						executeModuleEventEx($arEvent, array($BANNER_ID, $arFields));

					// ������� �� ����
					$strSql = "
						UPDATE b_adv_banner_2_day SET
							CLICK_COUNT = CLICK_COUNT + 1
						WHERE
							BANNER_ID = $BANNER_ID
						and	DATE_STAT = ".$DB->GetNowDate()."
						";
					$z = $DB->Query($strSql, false, $err_mess.__LINE__);
					$rows2 = $z->AffectedRowsCount();
					if (intval($rows2)<=0)
					{
						$strSql = "
							INSERT INTO b_adv_banner_2_day (DATE_STAT, BANNER_ID, CLICK_COUNT) VALUES (
								".$DB->GetNowDate().",
								$BANNER_ID,
								1)
							";
						$DB->Query($strSql, true, $err_mess.__LINE__);
					}
				}

				/*************************
					������� ��������
				*************************/

				$DONT_USE_CONTRACT = COption::GetOptionString("advertising", "DONT_USE_CONTRACT", "N");

				$CONTRACT_ID = intval($arBanner["CONTRACT_ID"]);
				if ($CONTRACT_ID>0 && $DONT_USE_CONTRACT == "N")
				{
					$arFields = Array("CLICK_COUNT" => "CLICK_COUNT + 1");
					$DB->Update("b_adv_contract",$arFields,"WHERE ID = $CONTRACT_ID",$err_mess.__LINE__);
				}
			}
		}
	}

	// ��������� ������ ����� ���� ��������� �������� ��� ������� ��������
	public static function GetPageWeights_RS()
	{
		$err_mess = (CAdvBanner::err_mess())."<br>Function: GetPageWeights_RS<br>Line: ";
		global $APPLICATION, $DB, $USER;

		$stat_adv_id = intval($_SESSION["SESS_LAST_ADV_ID"]);
		$stat_country_id = trim($_SESSION["SESS_COUNTRY_ID"]);
		$stat_city_id = intval($_SESSION["SESS_CITY_ID"]);
		if($stat_city_id > 0 && CModule::IncludeModule('statistic'))
		{
			$rsCity = CCity::GetList(array(), array("=CITY_ID" => $stat_city_id));
			if($arCity = $rsCity->Fetch())
				$stat_region = $arCity["REGION_NAME"];
		}
		$new_guest = ($_SESSION["SESS_GUEST_NEW"]=="N") ? "N" : "Y";
		$url = CAdvBanner::GetCurUri();
		$arrTime = getdate();
		$weekday = strtoupper($arrTime["weekday"]);
		$hour = intval($arrTime["hours"]);
		$strUserGroups = $USER->GetGroups();

		$DONT_USE_CONTRACT = COption::GetOptionString("advertising", "DONT_USE_CONTRACT", "N");

		if ($DONT_USE_CONTRACT == "N")
		{
			$strSql = "
			SELECT DISTINCT
				B.TYPE_SID,
				B.ID					BANNER_ID,
				B.WEIGHT				BANNER_WEIGHT,
				B.SHOWS_FOR_VISITOR,
				B.FIX_CLICK,
				B.FIX_SHOW,
				B.KEYWORDS				BANNER_KEYWORDS,
				".$DB->DateToCharFunction("B.DATE_SHOW_FIRST")."		DATE_SHOW_FIRST,
				".$DB->DateToCharFunction("B.DATE_SHOW_FROM")."			DATE_SHOW_FROM,
				".$DB->DateToCharFunction("B.DATE_SHOW_TO")."			DATE_SHOW_TO,
				B.FLYUNIFORM			FLYUNIFORM,
				B.MAX_SHOW_COUNT		MAX_SHOW_COUNT,
				B.SHOW_COUNT			SHOW_COUNT,
				C.ID					CONTRACT_ID,
				C.WEIGHT				CONTRACT_WEIGHT,
				C.KEYWORDS				CONTRACT_KEYWORDS
			FROM
				b_adv_type T

			INNER JOIN b_adv_banner B ON (
					B.ACTIVE='Y'
				and	B.TYPE_SID = T.SID
				and	B.STATUS_SID = 'PUBLISHED'
				and (B.FOR_NEW_GUEST is null or B.FOR_NEW_GUEST = :FOR_NEW_GUEST)
				and	(nvl(B.MAX_SHOW_COUNT,0)>nvl(B.SHOW_COUNT,0) or nvl(B.MAX_SHOW_COUNT,0)=0)
				and (nvl(B.MAX_CLICK_COUNT,0)>nvl(B.CLICK_COUNT,0) or nvl(B.MAX_CLICK_COUNT,0)=0)
				and (nvl(B.MAX_VISITOR_COUNT,0)>nvl(B.VISITOR_COUNT,0) or nvl(B.MAX_VISITOR_COUNT,0)=0)
				and (B.DATE_SHOW_FROM<=trunc(sysdate) or B.DATE_SHOW_FROM is null)
				and (B.DATE_SHOW_TO>=trunc(sysdate) or B.DATE_SHOW_TO is null))

			INNER JOIN b_adv_banner_2_site BS ON (
					BS.BANNER_ID = B.ID
				and BS.SITE_ID = :SITE_ID)

			INNER JOIN b_adv_contract C ON (
					C.ID = B.CONTRACT_ID
				and C.ACTIVE='Y'
				and	(nvl(C.MAX_SHOW_COUNT,0)>nvl(C.SHOW_COUNT,0) or nvl(C.MAX_SHOW_COUNT,0)=0)
				and (nvl(C.MAX_CLICK_COUNT,0)>nvl(C.CLICK_COUNT,0) or nvl(C.MAX_CLICK_COUNT,0)=0)
				and (nvl(C.MAX_VISITOR_COUNT,0)>nvl(C.VISITOR_COUNT,0) or nvl(C.MAX_VISITOR_COUNT,0)=0)
				and (C.DATE_SHOW_FROM<=trunc(sysdate) or C.DATE_SHOW_FROM is null)
				and (C.DATE_SHOW_TO>=trunc(sysdate) or C.DATE_SHOW_TO is null))

			INNER JOIN b_adv_contract_2_site CS ON (
					CS.CONTRACT_ID = B.CONTRACT_ID
				and CS.SITE_ID = :SITE_ID)

			INNER JOIN b_adv_contract_2_type CT ON (
					CT.CONTRACT_ID = C.ID
				and (CT.TYPE_SID = 'ALL' or CT.TYPE_SID = T.SID))

			INNER JOIN b_adv_banner_2_weekday BW ON (
					BW.BANNER_ID = B.ID
				and BW.C_WEEKDAY= :weekday
				and BW.C_HOUR = :hour)

			INNER JOIN b_adv_contract_2_weekday CW ON (
					CW.CONTRACT_ID = C.ID
				and CW.C_WEEKDAY= :weekday
				and CW.C_HOUR = :hour)

			LEFT JOIN b_adv_banner_2_page		BP1	ON (BP1.BANNER_ID = B.ID and BP1.SHOW_ON_PAGE='Y')
			LEFT JOIN b_adv_contract_2_page		CP1	ON (CP1.CONTRACT_ID = C.ID and CP1.SHOW_ON_PAGE='Y')
			LEFT JOIN b_adv_banner_2_stat_adv	BA	ON BA.BANNER_ID = B.ID
			LEFT JOIN b_adv_banner_2_country BC ON BC.BANNER_ID = B.ID AND (
				(
					(B.STAT_TYPE is null OR length(B.STAT_TYPE)=0 OR B.STAT_TYPE='COUNTRY')
					AND BC.COUNTRY_ID='".$DB->ForSql($stat_country_id,2)."'
				) OR (
					B.STAT_TYPE='REGION'
					AND BC.COUNTRY_ID='".$DB->ForSql($stat_country_id,2)."'
					AND BC.REGION='".$DB->ForSql($stat_region)."'
				) OR (
					B.STAT_TYPE='CITY'
					AND BC.CITY_ID='".intval($stat_city_id)."'
				)
			)

			WHERE
				T.ACTIVE = 'Y'

			and (
				B.STAT_COUNT is null
				or B.STAT_COUNT = 0
				or BC.BANNER_ID is not null
			)
			and (BP1.ID is null or :url like concat(BP1.PAGE, '%'))
			and (CP1.ID is null or :url like concat(CP1.PAGE, '%'))
			and not exists (
				SELECT
					'x'
				FROM
					b_adv_banner_2_page BP2
				WHERE
					BP2.BANNER_ID = B.ID
				and BP2.SHOW_ON_PAGE = 'N'
				and :url like concat(BP2.PAGE, '%')
				)
			and not exists (
				SELECT
					'x'
				FROM
					b_adv_contract_2_page CP2
				WHERE
					CP2.CONTRACT_ID = C.ID
				and CP2.SHOW_ON_PAGE = 'N'
				and :url like concat(CP2.PAGE, '%')
				)
			and (BA.STAT_ADV_ID is null or BA.STAT_ADV_ID= :stat_adv_id)
			and (BC.COUNTRY_ID is null or BC.COUNTRY_ID= :stat_country_id)


			and
			(
				(B.SHOW_USER_GROUP = 'Y' and EXISTS(SELECT 'x' FROM b_adv_banner_2_group UG1 WHERE UG1.BANNER_ID = B.ID and UG1.GROUP_ID in (".$strUserGroups.") and UG1.GROUP_ID<>2))
				or
				(B.SHOW_USER_GROUP <> 'Y' and NOT EXISTS(SELECT 'x' FROM b_adv_banner_2_group UG2 WHERE UG2.BANNER_ID = B.ID and UG2.GROUP_ID in (".$strUserGroups.") and UG2.GROUP_ID<>2))
			)

			ORDER BY B.TYPE_SID desc, C.ID desc
			";
		}
		else
		{
			$strSql = "
			SELECT DISTINCT
				B.TYPE_SID,
				B.ID					BANNER_ID,
				B.WEIGHT				BANNER_WEIGHT,
				B.SHOWS_FOR_VISITOR,
				B.FIX_CLICK,
				B.FIX_SHOW,
				B.KEYWORDS				BANNER_KEYWORDS
			FROM
				b_adv_type T

			INNER JOIN b_adv_banner B ON (
					B.ACTIVE='Y'
				and	B.TYPE_SID = T.SID
				and	B.STATUS_SID = 'PUBLISHED'
				and (B.FOR_NEW_GUEST is null or B.FOR_NEW_GUEST = :FOR_NEW_GUEST)
				and	(nvl(B.MAX_SHOW_COUNT,0)>nvl(B.SHOW_COUNT,0) or nvl(B.MAX_SHOW_COUNT,0)=0)
				and (nvl(B.MAX_CLICK_COUNT,0)>nvl(B.CLICK_COUNT,0) or nvl(B.MAX_CLICK_COUNT,0)=0)
				and (nvl(B.MAX_VISITOR_COUNT,0)>nvl(B.VISITOR_COUNT,0) or nvl(B.MAX_VISITOR_COUNT,0)=0)
				and (B.DATE_SHOW_FROM<=trunc(sysdate) or B.DATE_SHOW_FROM is null)
				and (B.DATE_SHOW_TO>=trunc(sysdate) or B.DATE_SHOW_TO is null))

			INNER JOIN b_adv_banner_2_site BS ON (
					BS.BANNER_ID = B.ID
				and BS.SITE_ID = :SITE_ID)


			INNER JOIN b_adv_banner_2_weekday BW ON (
					BW.BANNER_ID = B.ID
				and BW.C_WEEKDAY= :weekday
				and BW.C_HOUR = :hour)


			LEFT JOIN b_adv_banner_2_page		BP1	ON (BP1.BANNER_ID = B.ID and BP1.SHOW_ON_PAGE='Y')
			LEFT JOIN b_adv_banner_2_stat_adv	BA	ON BA.BANNER_ID = B.ID
			LEFT JOIN b_adv_banner_2_country BC ON BC.BANNER_ID = B.ID AND (
				(
					(B.STAT_TYPE is null OR length(B.STAT_TYPE)=0 OR B.STAT_TYPE='COUNTRY')
					AND BC.COUNTRY_ID='".$DB->ForSql($stat_country_id,2)."'
				) OR (
					B.STAT_TYPE='REGION'
					AND BC.COUNTRY_ID='".$DB->ForSql($stat_country_id,2)."'
					AND BC.REGION='".$DB->ForSql($stat_region)."'
				) OR (
					B.STAT_TYPE='CITY'
					AND BC.CITY_ID='".intval($stat_city_id)."'
				)
			)

			WHERE
				T.ACTIVE = 'Y'

			and (
				B.STAT_COUNT is null
				or B.STAT_COUNT = 0
				or BC.BANNER_ID is not null
			)
			and (BP1.ID is null or :url like concat(BP1.PAGE, '%'))
			and not exists (
				SELECT
					'x'
				FROM
					b_adv_banner_2_page BP2
				WHERE
					BP2.BANNER_ID = B.ID
				and BP2.SHOW_ON_PAGE = 'N'
				and :url like concat(BP2.PAGE, '%')
				)
			and (BA.STAT_ADV_ID is null or BA.STAT_ADV_ID= :stat_adv_id)
			and (BC.COUNTRY_ID is null or BC.COUNTRY_ID= :stat_country_id)
			and
			(
				(B.SHOW_USER_GROUP = 'Y' and EXISTS(SELECT 'x' FROM b_adv_banner_2_group UG1 WHERE UG1.BANNER_ID = B.ID and UG1.GROUP_ID in (".$strUserGroups.") and UG1.GROUP_ID<>2))
				or
				(B.SHOW_USER_GROUP <> 'Y' and NOT EXISTS(SELECT 'x' FROM b_adv_banner_2_group UG2 WHERE UG2.BANNER_ID = B.ID and UG2.GROUP_ID in (".$strUserGroups.") and UG2.GROUP_ID<>2))
			)

			ORDER BY B.TYPE_SID desc
			";
		}

		$arrBind = array(
			"FOR_NEW_GUEST"	=> $new_guest,
			"SITE_ID"			=> SITE_ID,
			"weekday"			=> $weekday,
			"hour"			=> $hour,
			"url"			=> $url,
			"stat_adv_id"		=> $stat_adv_id,
			"stat_country_id"	=> $stat_country_id,
			);
		$rs = $DB->QueryBindSelect($strSql, $arrBind, false, $err_mess.__LINE__);
		return $rs;
	}

	public static function CleanUpDynamics()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = (CAdvBanner::err_mess())."<br>Function: CleanUpDynamics<br>Line: ";
		global $DB;
		$DAYS = intval(COption::GetOptionString("advertising", "BANNER_DAYS"));
		$strSql = "DELETE FROM b_adv_banner_2_day WHERE TRUNC(DATE_STAT)<=TRUNC(SYSDATE)-$DAYS";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return "CAdvBanner::CleanUpDynamics();";
	}

	public static function CleanUpAllDynamics()
	{
		set_time_limit(0);
		ignore_user_abort(true);
		$err_mess = (CAdvBanner::err_mess())."<br>Function: CleanUpAllDynamics<br>Line: ";
		global $DB;
		$strSql = "DELETE FROM b_adv_banner_2_day WHERE 1 = 1";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return "CAdvBanner::CleanUpAllDynamics();";
	}

	public static function GetDynamicList_SQL($strSqlSearch)
	{
		global $DB;
		$strSql = "
			SELECT
				".$DB->DateToCharFunction("D.DATE_STAT","SHORT")."		DATE_STAT,
				to_char(D.DATE_STAT,'dd')								DAY,
				to_char(D.DATE_STAT,'mm')								MONTH,
				to_char(D.DATE_STAT,'yyyy')								YEAR,
				D.SHOW_COUNT,
				D.CLICK_COUNT,
				D.VISITOR_COUNT,
				D.BANNER_ID,
				B.CONTRACT_ID,
				B.GROUP_SID,
				C.NAME													CONTRACT_NAME,
				C.SORT													CONTRACT_SORT,
				B.NAME													BANNER_NAME,
				B.TYPE_SID												BANNER_TYPE_SID
			FROM
				b_adv_banner_2_day D
			INNER JOIN b_adv_banner B ON (D.BANNER_ID = B.ID)
			INNER JOIN b_adv_contract C ON (B.CONTRACT_ID = C.ID)
			WHERE
			$strSqlSearch
			ORDER BY
				D.DATE_STAT, B.CONTRACT_ID, B.GROUP_SID, D.BANNER_ID
			";
		return $strSql;
	}
}

/*****************************************************************
					����� "��� �������"
*****************************************************************/

class CAdvType extends CAdvType_all
{
	public static function err_mess()
	{
		$module_id = "advertising";
		return "<br>Module: ".$module_id."<br>Class: CAdvType<br>File: ".__FILE__;
	}
}
