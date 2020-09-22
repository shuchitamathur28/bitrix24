<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/quota.php");
class CDiskQuota extends CAllDiskQuota
{
	function SetDBSize()
	{
		$DBSize = 0;
		if (($_SESSION["SESS_RECOUNT_DB"] == "Y") && (COption::GetOptionInt("main", "disk_space") > 0))
		{
			global $DB;
			$strSql = "select sum(BYTES) as DATABASE_SIZE from USER_SEGMENTS";
			$db_res = $DB->Query($strSql);
			if ($db_res && ($res = $db_res->Fetch()))
			{
				$DBSize = $res["DATABASE_SIZE"];
			}
			COption::SetOptionString("main_size", "~db", $DBSize);
			$params = array("status" => "d", "time" => time());
			COption::SetOptionString("main_size", "~db_params", serialize($params));
			unset($_SESSION["SESS_RECOUNT_DB"]);
		}
		else
		{
			$params = array("status" => "d", "time" => false);
		}

		return array("status" => "done", "size" => $DBSize, "time" => $params["time"]);
	}
}
?>