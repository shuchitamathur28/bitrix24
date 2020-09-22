<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/user_relations.php");

class CSocNetUserRelations extends CAllSocNetUserRelations
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	function Add($arFields)
	{
		global $DB;

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetUserRelations::CheckFields("ADD", $arFields))
			return false;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetUserRelationsAdd");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
				return false;

		$dbResult = CSocNetUserRelations::GetList(
			array(),
			array(
				"FIRST_USER_ID" => $arFields["FIRST_USER_ID"],
				"SECOND_USER_ID" => $arFields["SECOND_USER_ID"]
			),
			false,
			false,
			array("ID")
		);

		$ID = false;

		if ($arResult = $dbResult->Fetch())
		{
			$ID = $arResult["ID"];
			$strUpdate = $DB->PrepareUpdate("b_sonet_user_relations", $arFields);
			\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

			if (strlen($strUpdate) > 0)
				$strSql =
					"UPDATE b_sonet_user_relations SET ".
					"	".$strUpdate." ".
					"WHERE ID = ".$ID." ";
		}
		else
		{
			$arInsert = $DB->PrepareInsert("b_sonet_user_relations", $arFields);
			\Bitrix\Socialnetwork\Util::processEqualityFieldsToInsert($arFields1, $arInsert);

			if (strlen($arInsert[0]) > 0)
			{
				$ID = IntVal($DB->NextID("SQ_B_SONET_USER_RELATIONS"));

				$strSql =
					"INSERT INTO b_sonet_user_relations(ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
			}
		}

		if ($ID)
		{
			$arBinds = Array();
			if (is_set($arFields, "MESSAGE"))
				$arBinds["MESSAGE"] = $arFields["MESSAGE"];
			$DB->QueryBind($strSql, $arBinds);

			$events = GetModuleEvents("socialnetwork", "OnSocNetUserRelationsAdd");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($ID, &$arFields));

			if (
				(
					!array_key_exists("SEND_MAIL", $arFields)
					|| $arFields["SEND_MAIL"] != "N"
				)
				&& !IsModuleInstalled("im")
			)
			{
				$mailType = "INVITE_FRIEND";
				if ($arFields["RELATION"] == SONET_RELATIONS_BAN)
					$mailType = "BAN_FRIEND";

				CSocNetUserRelations::SendEvent($ID, $mailType);
			}

			CSocNetSearch::OnUserRelationsChange($arFields["FIRST_USER_ID"]);
			CSocNetSearch::OnUserRelationsChange($arFields["SECOND_USER_ID"]);
		}

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetUserRelations::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetUserRelationsUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID, $arFields))===false)
				return false;

		$arUserRelationOld = CSocNetUserRelations::GetByID($ID);

		$strUpdate = $DB->PrepareUpdate("b_sonet_user_relations", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToUpdate($arFields1, $strUpdate);

		if (strlen($strUpdate) > 0)
		{
			$strSql =
				"UPDATE b_sonet_user_relations SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";

			$arBinds = Array();
			if (is_set($arFields, "MESSAGE"))
				$arBinds["MESSAGE"] = $arFields["MESSAGE"];
			$DB->QueryBind($strSql, $arBinds);

			$events = GetModuleEvents("socialnetwork", "OnSocNetUserRelationsUpdate");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			if (
				(!array_key_exists("SEND_MAIL", $arFields)
				|| $arFields["SEND_MAIL"] != "N")
				&& !IsModuleInstalled("im")
			)
			{
				$mailType = "";
				if ($arUserRelationOld["RELATION"] != SONET_RELATIONS_FRIEND && $arFields["RELATION"] == SONET_RELATIONS_FRIEND)
					$mailType = "AGREE_FRIEND";
				elseif ($arUserRelationOld["RELATION"] != SONET_RELATIONS_BAN && $arFields["RELATION"] == SONET_RELATIONS_BAN)
					$mailType = "BAN_FRIEND";
				elseif ($arUserRelationOld["RELATION"] != SONET_RELATIONS_REQUEST && $arFields["RELATION"] == SONET_RELATIONS_REQUEST)
					$mailType = "INVITE_FRIEND";

				if (StrLen($mailType) > 0)
					CSocNetUserRelations::SendEvent($ID, $mailType);
			}

			CSocNetSearch::OnUserRelationsChange($arUserRelationOld["FIRST_USER_ID"]);
			CSocNetSearch::OnUserRelationsChange($arUserRelationOld["SECOND_USER_ID"]);
		}
		else
			$ID = False;

		return $ID;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "FIRST_USER_ID", "SECOND_USER_ID", "RELATION", "DATE_CREATE", "DATE_UPDATE", "MESSAGE", "INITIATED_BY");

		$online_interval = (array_key_exists("ONLINE_INTERVAL", $arFilter) && intval($arFilter["ONLINE_INTERVAL"]) > 0 ? $arFilter["ONLINE_INTERVAL"] : 120);

		static $arFields = array(
			"ID" => Array("FIELD" => "UR.ID", "TYPE" => "int"),
			"FIRST_USER_ID" => Array("FIELD" => "UR.FIRST_USER_ID", "TYPE" => "int"),
			"SECOND_USER_ID" => Array("FIELD" => "UR.SECOND_USER_ID", "TYPE" => "int"),
			"USER_ID" => Array("FIELD" => "UR.FIRST_USER_ID, UR.SECOND_USER_ID", "TYPE" => "int", "WHERE_ONLY" => "Y", "WHERE" => array("CSocNetUserRelations", "PrepareSection4Where")),
			"RELATION" => Array("FIELD" => "UR.RELATION", "TYPE" => "string"),
			"DATE_CREATE" => Array("FIELD" => "UR.DATE_CREATE", "TYPE" => "datetime"),
			"DATE_UPDATE" => Array("FIELD" => "UR.DATE_UPDATE", "TYPE" => "datetime"),
			"MESSAGE" => Array("FIELD" => "UR.MESSAGE", "TYPE" => "string"),
			"INITIATED_BY" => Array("FIELD" => "UR.INITIATED_BY", "TYPE" => "string"),
			"FIRST_USER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_EMAIL" => Array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_PERSONAL_PHOTO" => Array("FIELD" => "U.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_PERSONAL_GENDER" => Array("FIELD" => "U.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"FIRST_USER_LID" => Array("FIELD" => "U.LID", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)"),
			"SECOND_USER_NAME" => Array("FIELD" => "U1.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_LAST_NAME" => Array("FIELD" => "U1.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_SECOND_NAME" => Array("FIELD" => "U1.SECOND_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_LOGIN" => Array("FIELD" => "U1.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_EMAIL" => Array("FIELD" => "U1.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_PERSONAL_PHOTO" => Array("FIELD" => "U1.PERSONAL_PHOTO", "TYPE" => "int", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_PERSONAL_GENDER" => Array("FIELD" => "U1.PERSONAL_GENDER", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"SECOND_USER_LID" => Array("FIELD" => "U1.LID", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)"),
			"RAND" => Array("FIELD" => "dbms_random.value(1,1000)", "TYPE" => "string"),
		);
		$arFields["FIRST_USER_IS_ONLINE"] = Array("FIELD" => "CASE WHEN U.LAST_ACTIVITY_DATE > (sysdate - ".$online_interval."/86400)	THEN 'Y' ELSE 'N' END", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)");
		$arFields["SECOND_USER_IS_ONLINE"] = Array("FIELD" => "CASE WHEN U1.LAST_ACTIVITY_DATE > (sysdate - ".$online_interval."/86400)	THEN 'Y' ELSE 'N' END", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)");

		if (array_key_exists("ACTIVE_ONLY", $arFilter) && $arFilter["ACTIVE_ONLY"] == "Y")
		{
			$arFields["FIRST_USER_IS_ACTIVE"] = Array("FIELD" => "U.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID)");
			$arFields["SECOND_USER_IS_ACTIVE"] = Array("FIELD" => "U1.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_user U1 ON (UR.SECOND_USER_ID = U1.ID)");
			$arFilter["FIRST_USER_IS_ACTIVE"] = "Y";
			$arFilter["SECOND_USER_IS_ACTIVE"] = "Y";
		}

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (
			is_array($arGroupBy)
			&& count($arGroupBy) == 0
		)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_user_relations UR ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sonet_user_relations UR ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
		{
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		}
		if (strlen($arSqls["GROUPBY"]) > 0)
		{
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		}
		if (strlen($arSqls["ORDERBY"]) > 0)
		{
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";
		}

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sonet_user_relations UR ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($arRes = $dbRes->Fetch())
				$cnt = $arRes["CNT"];

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
				$strSql = "SELECT * FROM (".$strSql.") WHERE ROWNUM<=".IntVal($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	function PrepareSection4Where($val, $key, $operation, $negative, $field, &$arField, &$arFilter)
	{
		$val = IntVal($val);
		if ($val <= 0)
			return False;

		return (($negative == "Y") ? "NOT " : "")."(UR.FIRST_USER_ID ".$operation." ".$val." OR UR.SECOND_USER_ID ".$operation." ".$val.")";
	}

	function GetListBirthday($userID, $number = 5, $online_interval = 120)
	{
		global $DB;

		$userID = IntVal($userID);
		$number = IntVal($number);

		$curYear = IntVal(Date('Y'));

		$strSql =
			($number > 0 ? "SELECT * FROM (" : "").
			"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL, U.PERSONAL_PHOTO, U.PERSONAL_GENDER, U.PERSONAL_BIRTHDAY as PB, ".
			"	CASE WHEN U.LAST_ACTIVITY_DATE > (sysdate - ".$online_interval."/86400) THEN 'Y' ELSE 'N' END as IS_ONLINE, ".
			"	CASE WHEN U.PERSONAL_BIRTHDAY IS NULL ".
			"		THEN '9999-99-99' ".
			"	WHEN TO_DATE('".$curYear."'||TO_CHAR(U.PERSONAL_BIRTHDAY, '-MM-DD'), 'YYYY-MM-DD') < trunc(SYSDATE) ".
			"		THEN '".($curYear + 1)."'||TO_CHAR(U.PERSONAL_BIRTHDAY, '-MM-DD') ".
			"	ELSE '".($curYear)."'||TO_CHAR(U.PERSONAL_BIRTHDAY, '-MM-DD') ".
			"	END as CB ".
			"FROM b_sonet_user_relations UR ".
			"	INNER JOIN b_user U ON (UR.FIRST_USER_ID = U.ID) ".
			"WHERE UR.SECOND_USER_ID = ".$userID." ".
			"	AND UR.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
			"UNION ".
			"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL, U.PERSONAL_PHOTO, U.PERSONAL_GENDER, U.PERSONAL_BIRTHDAY as PB, ".
			"	CASE WHEN U.LAST_ACTIVITY_DATE > (sysdate - ".$online_interval."/86400) THEN 'Y' ELSE 'N' END as IS_ONLINE, ".
			"	CASE WHEN U.PERSONAL_BIRTHDAY IS NULL ".
			"		THEN '9999-99-99' ".
			"	WHEN TO_DATE('".$curYear."'||TO_CHAR(U.PERSONAL_BIRTHDAY, '-MM-DD'), 'YYYY-MM-DD') < trunc(SYSDATE) ".
			"		THEN '".($curYear + 1)."'||TO_CHAR(U.PERSONAL_BIRTHDAY, '-MM-DD') ".
			"	ELSE '".($curYear)."'||TO_CHAR(U.PERSONAL_BIRTHDAY, '-MM-DD') ".
			"	END as CB ".
			"FROM b_sonet_user_relations UR ".
			"	INNER JOIN b_user U ON (UR.SECOND_USER_ID = U.ID) ".
			"WHERE UR.FIRST_USER_ID = ".$userID." ".
			"	AND UR.RELATION = '".$DB->ForSql(SONET_RELATIONS_FRIEND, 1)."' ".
			"ORDER BY PB ".
			($number > 0 ? ") WHERE ROWNUM <= ".$number."" : "");

		return $DB->Query($strSql, false, "File: ".__FILE__." Line: ".__LINE__);
	}

	function GetRelationsTop($userID, $number = 100)
	{
		global $DB;

		$userID = IntVal($userID);
		$number = IntVal($number);

		$strSql =
			($number > 0 ? "SELECT * FROM (" : "").
			"SELECT UR.RELATION, UR.FIRST_USER_ID, UR.SECOND_USER_ID ".
			"FROM b_sonet_user_relations UR ".
			"WHERE UR.FIRST_USER_ID = ".$userID." ".
			"UNION ".
			"SELECT UR.RELATION, UR.FIRST_USER_ID, UR.SECOND_USER_ID ".
			"FROM b_sonet_user_relations UR ".
			"WHERE UR.SECOND_USER_ID = ".$userID." ".
			($number > 0 ? ") WHERE ROWNUM <= ".$number."" : "");
			
		return $DB->Query($strSql);
	}
}
?>