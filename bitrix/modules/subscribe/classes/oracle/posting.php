<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/classes/general/posting.php");

class CPosting extends CPostingGeneral
{
	function GetList($aSort=Array(), $arFilter=Array())
	{
		global $DB;
		$this->LAST_ERROR = "";
		$arSqlSearch = Array();
		$arSqlSearch_h = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			foreach($arFilter as $key=>$val)
			{
				if (!is_array($val) && (strlen($val)<=0 || $val=="NOT_REF"))
					continue;

				switch(strtoupper($key))
				{
				case "MSG_CHARSET":
					$arSqlSearch[] = "P.MSG_CHARSET = '".$DB->ForSql($val)."'";
					break;
				case "ID":
					$arSqlSearch[] = GetFilterQuery("P.ID",$val,"N");
					break;
				case "TIMESTAMP_1":
					if($DB->IsDate($val))
						$arSqlSearch[] = "P.TIMESTAMP_X>=".$DB->CharToDateFunction($val, "SHORT");
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_TIMESTAMP_FROM")."<br>";
					break;
				case "TIMESTAMP_2":
					if($DB->IsDate($val))
						$arSqlSearch[] = "P.TIMESTAMP_X<(".$DB->CharToDateFunction($val, "SHORT")."+1)";
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_TIMESTAMP_TILL")."<br>";
					break;
				case "DATE_SENT_1":
					if($DB->IsDate($val))
						$arSqlSearch[] = "P.DATE_SENT>=".$DB->CharToDateFunction($val, "SHORT");
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_DATE_SENT_FROM")."<br>";
					break;
				case "DATE_SENT_2":
					if($DB->IsDate($val))
						$arSqlSearch[] = "P.DATE_SENT<(".$DB->CharToDateFunction($val, "SHORT")."+1)";
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_DATE_SENT_TILL")."<br>";
					break;
				case "STATUS":
					$arSqlSearch_h[] = GetFilterQuery("STATUS_TITLE, P.STATUS",$val);
					break;
				case "STATUS_ID":
					$arSqlSearch[] = GetFilterQuery("P.STATUS",$val,"N");
					break;
				case "SUBJECT":
					$arSqlSearch[] = GetFilterQuery("P.SUBJECT",$val);
					break;
				case "FROM":
					$arSqlSearch[] = GetFilterQuery("P.FROM_FIELD",$val,"Y",array("@","_","."));
					break;
				case "TO":
					$r = GetFilterQuery("PE.EMAIL",$val,"Y",array("@","_","."));
					if(strlen($r) > 0)
						$arSqlSearch[] = "EXISTS (SELECT * FROM b_posting_email PE WHERE PE.POSTING_ID=P.ID AND PE.STATUS='N' AND ".$r.")";
					break;
				case "BODY_TYPE":
					$arSqlSearch[] = ($val=="html") ? "P.BODY_TYPE='html'" : "P.BODY_TYPE='text'";
					break;
				case "BODY":
					$arSqlSearch[] = GetFilterQuery("P.BODY",$val);
					break;
				case "RUB_ID":
					if(is_array($val))
					{
						$rub_id = array();
						foreach($val as $i => $v)
						{
							$v = intval($v);
							if($v > 0)
								$rub_id[$v] = $v;
						}
						if(count($rub_id))
							$arSqlSearch[] = "EXISTS (SELECT * from b_posting_rubric PR WHERE PR.POSTING_ID = P.ID AND PR.LIST_RUBRIC_ID in (".implode(", ", $rub_id)."))";
					}
					break;
				case "AUTO_SEND_TIME_1":
					if($DB->IsDate($val, false, false, "FULL"))
						$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME>=".$DB->CharToDateFunction($val, "FULL")." )";
					elseif($DB->IsDate($val, false, false, "SHORT"))
						$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME>=".$DB->CharToDateFunction($val, "SHORT")." )";
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_AUTO_FROM")."<br>";
					break;
				case "AUTO_SEND_TIME_2":
					if($DB->IsDate($val, false, false, "FULL"))
						$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME<=".$DB->CharToDateFunction($val, "FULL")." )";
					elseif($DB->IsDate($val, false, false, "SHORT"))
						$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME<=".$DB->CharToDateFunction($val, "SHORT")." )";
					else
						$this->LAST_ERROR .= GetMessage("POST_WRONG_AUTO_TILL")."<br>";
					break;
				}
			}
		}

		$arOrder = array();
		foreach($aSort as $key => $ord)
		{
			$key = strtoupper($key);
			$ord = (strtoupper($ord) <> "ASC"? "DESC": "ASC");
			switch($key)
			{
				case "ID":		$arOrder[$key] = "P.ID ".$ord; break;
				case "TIMESTAMP":	$arOrder[$key] = "P.TIMESTAMP_X ".$ord; break;
				case "SUBJECT":		$arOrder[$key] = "P.SUBJECT ".$ord; break;
				case "BODY_TYPE":	$arOrder[$key] = "P.BODY_TYPE ".$ord; break;
				case "STATUS":		$arOrder[$key] = "P.STATUS ".$ord; break;
				case "DATE_SENT":	$arOrder[$key] = "P.DATE_SENT ".$ord; break;
				case "AUTO_SEND_TIME":	$arOrder[$key] = "P.AUTO_SEND_TIME ".$ord; break;
				case "FROM_FIELD":	$arOrder[$key] = "P.FROM_FIELD ".$ord; break;
				case "TO_FIELD":	$arOrder[$key] = "P.TO_FIELD ".$ord; break;
			}
		}
		if(count($arOrder) <= 0)
		{
			$arOrder["ID"] = "P.ID DESC";
		}
		$strSqlOrder = " ORDER BY ".implode(", ", $arOrder);

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				CASE
				WHEN P.STATUS='S' THEN '".$DB->ForSql(GetMessage("POST_STATUS_SENT"))."'
				WHEN P.STATUS='P' THEN '".$DB->ForSql(GetMessage("POST_STATUS_PART"))."'
				WHEN P.STATUS='E' THEN '".$DB->ForSql(GetMessage("POST_STATUS_ERROR"))."'
				WHEN P.STATUS='W' THEN '".$DB->ForSql(GetMessage("POST_STATUS_WAIT"))."'
				ELSE '".$DB->ForSql(GetMessage("POST_STATUS_DRAFT"))."'
				END as STATUS_TITLE
				,P.ID
				,P.STATUS
				,P.FROM_FIELD
				,P.TO_FIELD
				,P.EMAIL_FILTER
				,P.SUBJECT
				,P.BODY_TYPE
				,P.DIRECT_SEND
				,P.CHARSET
				,P.MSG_CHARSET
				,P.SUBSCR_FORMAT
				,".$DB->DateToCharFunction("P.TIMESTAMP_X")."	TIMESTAMP_X
				,".$DB->DateToCharFunction("P.DATE_SENT")."	DATE_SENT
			FROM b_posting P
			WHERE
			".$strSqlSearch."
		";
		if(count($arSqlSearch_h)>0)
		{
			$strSqlSearch_h = GetFilterSqlSearch($arSqlSearch_h);
			$strSql = "SELECT * FROM (".$strSql.") P WHERE ".$strSqlSearch_h;
		}
		$strSql.=$strSqlOrder;

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$res->is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}

	public static function Lock($ID=0)
	{
		global $DB, $APPLICATION;
		$ID = intval($ID);
		$uniq = $APPLICATION->GetServerUniqID();
		$db_lock = $DB->Query("
			declare
				my_lock_id number;
				my_result number;
				lock_failed exception;
				pragma exception_init(lock_failed, -54);
			begin
				my_lock_id:=dbms_utility.get_hash_value(to_char('".$uniq."_post_".$ID."'), 0, 1024);
				my_result:=dbms_lock.request(my_lock_id, dbms_lock.x_mode, 0, true);
				--  Return value:
				--    0 - success
				--    1 - timeout
				--    2 - deadlock
				--    3 - parameter error
				--    4 - already own lock specified by 'id' or 'lockhandle'
				--    5 - illegal lockhandle
				if(my_result<>0 and my_result<>4)then
					raise lock_failed;
				end if;
			end;
		", true);
		if(!$db_lock && (strpos($DB->db_Error, "ORA-00054") === false))
		{
			$APPLICATION->ThrowException($DB->db_Error);
		}
		return $db_lock!==false;
	}

	public static function UnLock($ID=0)
	{
		//lock released on commit
		return true;
	}
}
?>