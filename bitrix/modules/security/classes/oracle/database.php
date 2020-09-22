<?
//We can not use object here due to PHP architecture
global $SECURITY_SESSION_DBH;
$SECURITY_SESSION_DBH = false;
global $SECURITY_SESSION_DB_TRANS;
$SECURITY_SESSION_DB_TRANS = OCI_COMMIT_ON_SUCCESS;

/**
 * @deprecated since 16.0.0
 */
class CSecurityDB
{
	public static function Init($bDoConnect = false)
	{
		global $SECURITY_SESSION_DBH, $DB;
		static $DBLogin, $DBPassword, $DBName;

		if(is_resource($SECURITY_SESSION_DBH))
			return true;

		if(!is_object($DB))
			return false;

		if($bDoConnect)
		{
			if(function_exists("oci_new_connect"))
				$SECURITY_SESSION_DBH = @oci_new_connect($DB->DBLogin, $DB->DBPassword, $DB->DBName);
			else
				$SECURITY_SESSION_DBH = @OCILogon($DB->DBLogin, $DB->DBPassword, $DB->DBName);
		}
		else
		{
			$DBLogin = $DB->DBLogin;
			$DBPassword = $DB->DBPassword;
			$DBName = $DB->DBName;
			return true;
		}

		//In case of error just skip it over
		if(!is_resource($SECURITY_SESSION_DBH))
			return false;

		return true;
	}

	public static function Disconnect()
	{
		global $SECURITY_SESSION_DBH;
		if(is_resource($SECURITY_SESSION_DBH))
		{
			OCILogOff($SECURITY_SESSION_DBH);
			$SECURITY_SESSION_DBH = false;
		}
	}

	public static function CurrentTimeFunction()
	{
		return "SYSDATE";
	}

	public static function SecondsAgo($sec)
	{
		return "(SYSDATE - ".intval($sec)."/24/3600)";
	}

	public static function Query($strSql, $error_position)
	{
		global $SECURITY_SESSION_DBH, $SECURITY_SESSION_DB_TRANS;

		if(!is_resource($SECURITY_SESSION_DBH))
			CSecurityDB::Init(true);

		if(is_resource($SECURITY_SESSION_DBH))
		{
			$statement = @OCIParse($SECURITY_SESSION_DBH, $strSql);
			if($statement)
			{
				$result = @OCIExecute($statement, $SECURITY_SESSION_DB_TRANS);
				if($result)
					return $statement;
			}
		}
		return false;
	}

	public static function QueryBind($strSql, $arBinds, $error_position)
	{
		global $SECURITY_SESSION_DBH, $SECURITY_SESSION_DB_TRANS;

		if(!is_resource($SECURITY_SESSION_DBH))
			CSecurityDB::Init(true);

		if(is_resource($SECURITY_SESSION_DBH))
		{
			$strBinds1 = "";
			$strBinds2 = "";
			foreach($arBinds as $key => $value)
			{
				if($strBinds1 == "")
				{
					$strBinds1 = " RETURNING ".$key;
					$strBinds2 = " INTO :".$key;
				}
				else
				{
					$strBinds1 .= ", ".$key;
					$strBinds2 .= ", :".$key;
				}
				$strSql = str_replace(":".$key, "empty_clob()", $strSql);
			}
			$strSql .= $strBinds1.$strBinds2;

			$statement = @OCIParse($SECURITY_SESSION_DBH, $strSql);
			if($statement)
			{
				$CLOB = array();
				foreach($arBinds as $key => $value)
				{
					$CLOB[$key] = OCINewDescriptor($SECURITY_SESSION_DBH, OCI_D_LOB);
					OCIBindByName($statement, ":".$key, $CLOB[$key], -1, OCI_B_CLOB);
				}

				$result = @OCIExecute($statement, OCI_DEFAULT);
				if($result)
				{
					foreach($arBinds as $key => $value)
						$CLOB[$key]->save($arBinds[$key]);

					if($SECURITY_SESSION_DB_TRANS == OCI_COMMIT_ON_SUCCESS)
						OCICommit($SECURITY_SESSION_DBH);

					return $statement;
				}
			}
		}
		return false;
	}

	public static function Fetch($result)
	{
		if($result)
		{
			$row = @OCIFetchInto($result, $arRow, OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS);
			if($row && is_array($arRow))
			{
				foreach($arRow as $FIELD_NAME => $FIELD_VALUE)
					if(is_object($FIELD_VALUE))
						$arRow[$FIELD_NAME] = $FIELD_VALUE->load();
				return $arRow;
			}
		}
		return false;
	}

	public static function Lock($id, $timeout = 60)
	{
		global $SECURITY_SESSION_DBH, $SECURITY_SESSION_DB_TRANS;
		static $lock_id = "";

		if(!is_resource($SECURITY_SESSION_DBH))
			CSecurityDB::Init(true);

		if($id === false)
		{
			if(is_resource($SECURITY_SESSION_DBH) && $lock_id)
			{
				OCICommit($SECURITY_SESSION_DBH);
				$SECURITY_SESSION_DB_TRANS = OCI_COMMIT_ON_SUCCESS;
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$SECURITY_SESSION_DB_TRANS = OCI_DEFAULT;
			while(true)
			{
				$rsLock = CSecurityDB::Query("
					select *
					from b_sec_session
					where SESSION_ID = '".$id."'
					for update wait ".$timeout."
				", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);

				if($rsLock)
				{
					if(CSecurityDB::Fetch($rsLock))
					{
						$lock_id = $id;
						return true;
					}
					else
					{
						$rsLock = CSecurityDB::Query("
							insert into b_sec_session values
							('".$id."', sysdate, null)
						", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
						if($rsLock)
							OCICommit($SECURITY_SESSION_DBH);
					}
				}
				else
				{
					return false;
				}
			}
		}
	}

	public static function LockTable($table_name, $lock_id)
	{
		global $SECURITY_SESSION_DBH, $SECURITY_SESSION_DB_TRANS;

		if(!is_resource($SECURITY_SESSION_DBH))
			CSecurityDB::Init(true);

		if(is_resource($SECURITY_SESSION_DBH))
		{
			$SECURITY_SESSION_DB_TRANS = OCI_DEFAULT;
			$rsLock = CSecurityDB::Query("SELECT * FROM $table_name FOR UPDATE NOWAIT", "Module: security; Class: CSecurityDB; Function: LockTable; File: ".__FILE__."; Line: ".__LINE__);
			if($rsLock)
			{
				return true;
			}
			else
			{
				OCICommit($SECURITY_SESSION_DBH);
				$SECURITY_SESSION_DB_TRANS = OCI_COMMIT_ON_SUCCESS;
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	public static function UnlockTable($table_lock)
	{
		global $SECURITY_SESSION_DBH, $SECURITY_SESSION_DB_TRANS;
		if($table_lock)
		{
			OCICommit($SECURITY_SESSION_DBH);
			$SECURITY_SESSION_DB_TRANS = OCI_COMMIT_ON_SUCCESS;
		}
	}
}
?>