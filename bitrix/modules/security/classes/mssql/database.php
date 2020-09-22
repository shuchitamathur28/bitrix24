<?
global $SECURITY_SESSION_DBH;
$SECURITY_SESSION_DBH = false;

/**
 * @deprecated since 16.0.0
 */
class CSecurityDB
{
	public static function Init()
	{
		global $SECURITY_SESSION_DBH, $DB;

		if(is_resource($SECURITY_SESSION_DBH))
			return true;

		if(!is_object($DB))
			return false;

		$connectionInfo = array(
			'UID' => $DB->DBLogin,
			'PWD' => $DB->DBPassword,
			'Database'=> $DB->DBName,
			'ReturnDatesAsStrings'=> true,
			'ConnectionPooling' => 0,
		);

		$SECURITY_SESSION_DBH = @sqlsrv_connect($DB->DBHost, $connectionInfo);

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
			sqlsrv_close($SECURITY_SESSION_DBH);
			$SECURITY_SESSION_DBH = false;
		}
	}

	public static function CurrentTimeFunction()
	{
		return "getdate()";
	}

	public static function SecondsAgo($sec)
	{
		return "DATEADD(SECOND, -".intval($sec).", GETDATE())";
	}

	public static function Query($strSql, $error_position)
	{
		global $SECURITY_SESSION_DBH;
		if(is_resource($SECURITY_SESSION_DBH))
		{
			$result = @sqlsrv_query($SECURITY_SESSION_DBH, $strSql, array(), array("Scrollable" => 'forward'));
			if($result)
				return $result;
		}
		return false;
	}

	public static function QueryBind($strSql, $arBinds, $error_position)
	{
		foreach($arBinds as $key => $value)
			$strSql = str_replace(":".$key, "'".$value."'", $strSql);
		return CSecurityDB::Query($strSql, $error_position);
	}

	public static function Fetch($result)
	{
		if($result)
		{
			$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
			if($row)
				return $row;
		}
		return false;
	}

	public static function Lock($id, $timeout = 60)
	{
		global $SECURITY_SESSION_DBH;
		static $lock_id = "";

		if($id === false)
		{
			if(is_resource($SECURITY_SESSION_DBH) && $lock_id)
			{
				CSecurityDB::Query("SET LOCK_TIMEOUT -1", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
				sqlsrv_commit($SECURITY_SESSION_DBH);
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$lock_id = $id;
			sqlsrv_begin_transaction($SECURITY_SESSION_DBH);
			while(true)
			{
				CSecurityDB::Query("SET LOCK_TIMEOUT ".($timeout*1000), "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
				$rsLock = CSecurityDB::Query("
					update b_sec_session
					set TIMESTAMP_X = getdate()
					where SESSION_ID = '".$lock_id."'
				", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);

				if($rsLock)
				{
					$rsLock = CSecurityDB::Query("
						select *
						from b_sec_session
						where SESSION_ID = '".$lock_id."'
					", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
					if(CSecurityDB::Fetch($rsLock))
					{
						return true;
					}
					else
					{
						$rsLock = CSecurityDB::Query("
							insert into b_sec_session values
							('".$lock_id."', getdate(), null)
						", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
						if($rsLock)
							return true;
						else
							return false;
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
		global $SECURITY_SESSION_DBH;
		if(is_resource($SECURITY_SESSION_DBH))
		{
			sqlsrv_begin_transaction($SECURITY_SESSION_DBH);
			CSecurityDB::Query("SET LOCK_TIMEOUT 0", "Module: security; Class: CSecurityDB; Function: LockTable; File: ".__FILE__."; Line: ".__LINE__);
			$rsLock = CSecurityDB::Query("SELECT * FROM $table_name WITH (TABLOCKX)", "Module: security; Class: CSecurityDB; Function: LockTable; File: ".__FILE__."; Line: ".__LINE__);
			if($rsLock)
			{
				return true;
			}
			else
			{
				sqlsrv_commit($SECURITY_SESSION_DBH);
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
		global $DB, $SECURITY_SESSION_DBH;
		if($table_lock)
		{
			CSecurityDB::Query("SET LOCK_TIMEOUT -1", "Module: security; Class: CSecurityDB; Function: UnlockTable; File: ".__FILE__."; Line: ".__LINE__);
			sqlsrv_commit($SECURITY_SESSION_DBH);
		}
	}
}
?>