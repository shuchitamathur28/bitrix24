<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/database.php");

class CDatabase extends CAllDatabase
{
	var $version;
	var $transaction = OCI_COMMIT_ON_SUCCESS;
	var $XE;

	public
		$escL = '"',
		$escR = '"';

	public
		$alias_length = 30;

	function GetVersion()
	{
		if($this->version)
			return $this->version;

		$rs = $this->Query('SELECT BANNER as R FROM v$version', false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if($ar = $rs->Fetch())
		{
			$version = trim($ar["R"]);
			$this->XE = (strpos($version, "Express Edition")>0);
			preg_match("#[0-9]+\\.[0-9]+\\.[0-9]+#", $version, $arr);
			$version = $arr[0];
			$this->version = $version;
			return $version;
		}
		else
		{
			return false;
		}
	}

	function StartTransaction()
	{
		$this->transaction = OCI_DEFAULT;
	}

	function Commit()
	{
		$this->DoConnect();
		oci_commit($this->db_Conn);
		$this->transaction = OCI_COMMIT_ON_SUCCESS;
	}

	function Rollback()
	{
		$this->DoConnect();
		oci_rollback($this->db_Conn);
		$this->transaction = OCI_COMMIT_ON_SUCCESS;
	}

	//Connect to database
	function Connect($DBHost, $DBName, $DBLogin, $DBPassword)
	{
		$this->type="ORACLE";
		$this->DBHost = $DBHost;
		$this->DBName = $DBName;
		$this->DBLogin = $DBLogin;
		$this->DBPassword = $DBPassword;
		$this->bConnected = false;

		if (!defined("DBPersistent"))
			define("DBPersistent",true);

		if(defined("DELAY_DB_CONNECT") && DELAY_DB_CONNECT===true)
			return true;
		else
			return $this->DoConnect();
	}

	function ConnectInternal()
	{
		if (DBPersistent && !$this->bNodeConnection)
			$this->db_Conn = oci_pconnect($this->DBLogin, $this->DBPassword, $this->DBName);
		else
			$this->db_Conn = oci_connect($this->DBLogin, $this->DBPassword, $this->DBName);


		if(!$this->db_Conn)
		{
			$s = (DBPersistent && !$this->bNodeConnection? "oci_pconnect" : "oci_connect");
			$arError = oci_error();
			$s .= " Error:".$arError["message"];
			if($this->debug || (isset($_SESSION["SESS_AUTH"]["ADMIN"]) && $_SESSION["SESS_AUTH"]["ADMIN"]))
				echo "<br><font color=#ff0000>".$s."()</font><br>";

			SendError("Error! ".$s."()\n\n");

			return false;
		}

		return true;
	}

	//This function executes query against database
	function Query($strSql, $bIgnoreErrors=false, $error_position="", $arOptions=array())
	{
		global $DB;

		$this->DoConnect();
		$GLOBALS["prev_Query"][]=$strSql;
		$this->db_Error="";

		if($this->DebugToFile || $DB->ShowSqlStat)
			$start_time = microtime(true);

		$result = oci_parse($this->db_Conn, $strSql);

		if(!$result)
		{
			$error = oci_error($this->db_Conn);
			$this->db_Error = $error["message"];
			$this->db_ErrorSQL = $strSql;
			if(!$bIgnoreErrors)
			{
				if ($this->DebugToFile)
					$this->startSqlTracker()->writeFileLog("ERROR: ".$this->db_Error);

				if($this->debug || (isset($_SESSION["SESS_AUTH"]["ADMIN"]) && $_SESSION["SESS_AUTH"]["ADMIN"]))
					echo $error_position."<br><font color=#ff0000>Oracle Query Error: ".htmlspecialcharsbx($strSql)."</font>[".$error["message"]."]<br>";

				$error_position = preg_replace("/<br>/i", "\n", $error_position);
				SendError($error_position."\nOracle Query Error:\n".$strSql." \n [".$error["message"]."]\n---------------\n\n");
				AddMessage2Log($error_position." Oracle Query Error: ".$strSql." [".$error["message"]."]", "main");
				die("Oracle Query Error");
			}
			return false;
		}

		if($bIgnoreErrors)
		{
			$exec = @oci_execute($result, $this->transaction);
		}
		else
		{
			$exec = oci_execute($result, $this->transaction);
		}

		if(!$exec)
		{
			$error = oci_error($result);
			$this->db_Error = $error["message"];
			$this->db_ErrorSQL = $strSql;
			if(!$bIgnoreErrors)
			{
				AddMessage2Log($error_position." Oracle Query Error: ".$strSql." [".$error["message"]."]", "main");
				if ($this->DebugToFile)
					$this->startSqlTracker()->writeFileLog("ERROR: ".$this->db_Error);

				if($this->debug || (isset($_SESSION["SESS_AUTH"]["ADMIN"]) && $_SESSION["SESS_AUTH"]["ADMIN"]))
					echo $error_position."<br><font color=#ff0000>Oracle Query Error: ".htmlspecialcharsbx($strSql)."</font>[".htmlspecialcharsbx($error["message"])."]<br>";

				$error_position = preg_replace("/<br>/i", "\n", $error_position);
				SendError($error_position."\nOracle Query Error:\n".$strSql." \n [".$error["message"]."]\n---------------\n\n");

				if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbquery_error.php"))
					include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbquery_error.php");
				elseif(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/dbquery_error.php"))
					include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/dbquery_error.php");
				else
					die("Oracle Query Error!");

				die();
			}
			return false;
		}

		if($this->DebugToFile || $DB->ShowSqlStat)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$exec_time = round(microtime(true) - $start_time, 10);

			if($DB->ShowSqlStat)
				$DB->addDebugQuery($strSql, $exec_time, $this->node_id);

			if ($this->DebugToFile)
				$this->startSqlTracker()->writeFileLog($strSql, $exec_time, "CONN: ".$this->db_Conn);
		}

		$res = new CDBResult($result);
		$res->DB = $this;
		if($DB->ShowSqlStat)
			$res->SqlTraceIndex = count($DB->arQueryDebug) - 1;
		return $res;
	}

	function QueryLong($strSql, $bIgnoreErrors = false)
	{
		global $cache_clob;

		$strSql = trim($strSql);
		if ($strSql == '')
			return null;

		if (preg_match("/^\\s*(insert\\s+.+?)\\((.+?)\\)\\s*values\\s*\\((.+)\\)\\s*;*\\s*$/is", $strSql, $matches))
		{
			// Insert
			$tables = $matches[1];
			$fields = $matches[2];
			$values = $matches[3];

			$arFields = preg_split("/[\n\r\t ]*,[\n\r\t ]*/", trim($fields ,"\n\r\t "));

			$arValues = array();
			$iSqlLen = strlen($values);
			$bString = False;
			$bFunc = False;
			$string_start = "";

			for ($i=0; $i<$iSqlLen; $i++)
			{
				$ch = $values[$i];

				if ($bString)
				{
					while (true)
					{
						$i = strpos($values, $string_start, $i);

						if (!$i)
						{
							$arValues[] = $values;
							break 2;
						}
						elseif ($string_start == "`" || $values[$i-1] != "\\")
						{
							$string_start = '';
							$bString = False;
							break;
						}
						else
						{
							$j = 2;
							$escaped_backslash = False;
							while ($i-$j > 0 && $values[$i-$j] == "\\")
							{
								$escaped_backslash = !$escaped_backslash;
								$j++;
							}

							if ($escaped_backslash)
							{
								$string_start = '';
								$bString = False;
								break;
							}
							else
								$i++;
						}
					}	// end while
				} // end if (in string)
				elseif ($bFunc)
				{
					$i = strpos($values, ")", $i);

					if (!$i)
					{
						$arValues[] = $values;
						break;
					}
					else
					{
						$string_start = '';
						$bFunc = False;
					}
				} // end if (in string)
				elseif ($ch == ",") // We are not in a string, first check for delimiter...
				{
					$arValues[] = substr($values, 0, $i);

					$values = ltrim(substr($values, min($i + 1, $iSqlLen)));
					$iSqlLen = strlen($values);
					if ($iSqlLen)
						$i = -1;
					else
						break;
				}
				elseif (($ch == '"') || ($ch == '\'') || ($ch == '`')) // ... then check for start of a string,...
				{
					$bString = True;
					$string_start = $ch;
				}
				elseif ($ch == '(')
				{
					$bFunc = True;
				}
				else
				{
				}
			}

			if (strlen(trim($values))>0)
			{
				$arValues[] = $values;
			}

			if (count($arValues)!=count($arFields))
			{
				$this->db_Error = "Incorrect insert query (g5j27) ";
				return null;
			}
			else
			{
				$newStrSql = $tables." (".implode(",", $arFields).") VALUES (";
				$arBind = array();
				for ($i = 0, $c = count($arValues); $i < $c; $i++)
				{
					if ($i > 0)
						$newStrSql .= ",";
					// Check clob
					$cache_key_clob=md5($tables.$arFields[$i]);
					if (isset($cache_clob[$cache_key_clob]))
						$f_clob['A']=$cache_clob[$cache_key_clob];
					else
					{
						if (preg_match("# ([^ ]+)$#",trim($tables),$regs))
						{
							$table=$regs[1];
							$res_clob=$this->Query("select count(*) as A from user_tab_columns where table_name='".strtoupper($table)."' and column_name='".strtoupper($arFields[$i])."' and data_type='CLOB'");
							$f_clob=$res_clob->Fetch();
							$cache_clob[$cache_key_clob]=$f_clob['A'];
						}
						else
							$f_clob['A']=0;
					}

					//if (strlen($arValues[$i])>$iMaxStrLen)
					if (trim(strtolower($arValues[$i]))!='null' && $f_clob['A']>0)
					{
						$newStrSql .= "EMPTY_CLOB()";
						$arValues[$i] = trim($arValues[$i]," '".'"');
//						$arValues[$i] = substr($arValues[$i], 1, strlen($arValues[$i])-2);
						$arBind[$arFields[$i]] = str_replace("\\\\","\\",str_replace("''","'",$arValues[$i]));
					}
					else
						$newStrSql .= $arValues[$i];
				}
				$newStrSql .= ")";

				$rResult = $this->QueryBind($newStrSql, $arBind, $bIgnoreErrors);
			}
		}
		else
		{
			$rResult = $this->Query($strSql, $bIgnoreErrors);
		}
		return $rResult;
	}

	function CurrentTimeFunction()
	{
		return "SYSDATE";
	}

	function CurrentDateFunction()
	{
		return "TRUNC(SYSDATE)";
	}

	function DateFormatToDB($format, $field = false)
	{
		$format = str_replace("HH", "HH24", $format);
		$format = str_replace("GG", "HH24", $format);
		if (strpos($format, 'HH24') === false)
		{
			$format = str_replace("H", "HH", $format);
		}
		$format = str_replace("G", "HH", $format);

		$format = str_replace("MI", "II", $format);
		if (strpos($format, 'MMMM') !== false)
		{
			$format = str_replace("MMMM", "MONTH", $format);
		}
		elseif (strpos($format, 'MM') === false)
		{
			$format = str_replace("M", "MON", $format);
		}
		$format = str_replace("II", "MI", $format);

		$format = str_replace("TT", "AM", $format);

		$lowerAmPm = false;
		if(strpos($format, 'T') !== false)
		{
			//lowercase am/pm
			$lowerAmPm = true;
			$format = str_replace("T", "AM", $format);
		}

		if($field === false)
		{
			$field = "#FIELD#";
		}

		if($lowerAmPm)
		{
			return "REPLACE(REPLACE(TO_CHAR(".$field.", '".$format."'), 'PM', 'pm'), 'AM', 'am')";
		}

		return "TO_CHAR(".$field.", '".$format."')";
	}

	function DateToCharFunction($strFieldName, $strType="FULL", $lang=false, $bSearchInSitesOnly=false)
	{
		static $CACHE = array();

		$id = $strType.",".$lang.",".$bSearchInSitesOnly;
		if(!isset($CACHE[$id]))
		{
			$CACHE[$id] = $this->DateFormatToDB(CLang::GetDateFormat($strType, $lang, $bSearchInSitesOnly), false);
		}

		$sFieldExpr = $strFieldName;

		//time zone
		if($strType == "FULL" && CTimeZone::Enabled())
		{
			static $diff = false;
			if($diff === false)
				$diff = CTimeZone::GetOffset();

			if($diff <> 0)
				$sFieldExpr = $strFieldName."+(".$diff."/86400)";
		}

		return str_replace("#FIELD#", $sFieldExpr, $CACHE[$id]);
	}

	function CharToDateFunction($strValue, $strType="FULL", $lang=false)
	{
		$date = $this->FormatDate($strValue, CLang::GetDateFormat($strType, $lang), ($strType=="SHORT"? "DD.MM.YYYY":"DD.MM.YYYY HH:MI:SS"));

		//Oracle supports only 4digit year.
		//We decided to remove exceedeng digits
		$date = preg_replace("/^(\\d{2}.\\d{2}.\\d{4})(\\d+)( \\d{2}:\\d{2}:\\d{2})\$/", "\\1\\3", $date);

		$sFieldExpr = "TO_DATE('".$date."', 'DD.MM.YYYY HH24:MI:SS')";

		//time zone
		if($strType == "FULL" && CTimeZone::Enabled())
		{
			static $diff = false;
			if($diff === false)
				$diff = CTimeZone::GetOffset();

			if($diff <> 0)
				$sFieldExpr .= "-(".$diff."/86400)";
		}

		return $sFieldExpr;
	}

	function DatetimeToTimestampFunction($fieldName)
	{
		$timeZone = "";
		if (CTimeZone::Enabled())
		{
			static $diff = false;
			if($diff === false)
				$diff = CTimeZone::GetOffset();

			if($diff <> 0)
				$timeZone = $diff > 0? "+".$diff: $diff;
		}
		//Inspired by http://blog.watashii.com/2009/11/oracle-timezone-conversions-gmt-to-localtime/
		return "ROUND((".$fieldName." - CAST((FROM_TZ(CAST(TO_DATE('1970-01-01 00:00:00', 'YYYY-MM-DD HH24:MI:SS') AS TIMESTAMP), 'GMT') AT LOCAL) AS DATE)) * (86400), 0)".$timeZone;
	}

	function DatetimeToDateFunction($strValue)
	{
		return 'TRUNC('.$strValue.')';
	}

	//  1 if date1 > date2
	//  0 if date1 = date2
	// -1 if date1 < date2
	function CompareDates($date1, $date2)
	{
		$s_date1 = $this->CharToDateFunction($date1);
		$s_date2 = $this->CharToDateFunction($date2);
		$strSql = "SELECT sign($s_date1 - $s_date2) as RES FROM dual";
		$z = $this->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$zr = $z->Fetch();
		return $zr["RES"];
	}

	function NextID($sequence)
	{
		if(!empty($sequence))
		{
			$strGetNewID = "SELECT ".$sequence.".NEXTVAL FROM DUAL";
			$db_newid_set = $this->Query($strGetNewID) or die("Query Error! (NextID)");
			$db_newid = $db_newid_set->Fetch();
			return $db_newid["NEXTVAL"];
		}
		else
		{
			return false;
		}
	}

	//Closes database connection
	function Disconnect()
	{
		if(!DBPersistent && $this->bConnected)
		{
			$this->bConnected = false;

			$fl = true;
			if (!$this->bNodeConnection)
			{
				$app = \Bitrix\Main\Application::getInstance();
				if ($app != null)
				{
					$con = $app->getConnection();
					if ($con->isConnected())
					{
						$con->disconnect();
						$fl = false;
					}
				}
			}

			if ($fl)
				oci_close($this->db_Conn);
		}

		foreach(self::$arNodes as $i => $arNode)
		{
			if(is_array($arNode) && array_key_exists("DB", $arNode))
			{
				oci_close($arNode["DB"]->db_Conn);
				unset(self::$arNodes[$i]["DB"]);
			}
		}
	}

	function PrepareFields($strTableName, $strPrefix = "str_", $strSuffix = "")
	{
		$arColumns = $this->GetTableFields($strTableName);
		foreach($arColumns as $arColumn)
		{
			$column = $arColumn["NAME"];
			$type = $arColumn["TYPE"];
			global $$column;
			$var = $strPrefix.$column.$strSuffix;
			global $$var;
			switch ($type)
			{
				case "NUMBER":
					if(IntVal($arColumn["DATA_SCALE"])<=0)
						$$var = IntVal($$column);
					else
						$$var = roundEx(doubleval($$column), $arColumn["DATA_SCALE"]);

					if($arColumn["DATA_PRECISION"]>0 && strlen(IntVal($$column))>IntVal($arColumn["DATA_PRECISION"])-IntVal($arColumn["DATA_SCALE"]))
						$$var=IntVal(str_repeat('9',$arColumn["DATA_PRECISION"]-$arColumn["DATA_SCALE"]));
					break;
				case "VARCHAR2":
				case "CHAR":
					$$var=$this->ForSql($$column, $arColumn["CHAR_LENGTH"]);
					break;
				default:
					$$var=$this->ForSql($$column);
			}
		}
	}

	function PrepareInsert($strTableName, $arFields, $strFileDir="", $lang=false)
	{
		$strInsert1 = "";
		$strInsert2 = "";

		$arColumns = $this->GetTableFields($strTableName);
		foreach($arColumns as $strColumnName => $arColumnInfo)
		{
			$type = $arColumnInfo["TYPE"];
			if(isset($arFields[$strColumnName]))
			{
				$value = $arFields[$strColumnName];
				if($value === false)
				{
					$strInsert1 .= ", ".$strColumnName;
					$strInsert2 .= ", NULL ";
				}
				else
				{
					$strInsert1 .= ", ".$strColumnName;
					switch ($type)
					{
						case "DATE":
							if(strlen($value)>0)
								$strInsert2 .= ", ".$this->CharToDateFunction($value, "FULL", $lang);
							else
								$strInsert2 .= ", NULL ";
							break;
						case "CLOB":
							if(strlen($value)>0)
								$strInsert2 .= ", :$strColumnName ";
							else
								$strInsert2 .= ", NULL ";
							break;
						default:
							$strInsert2 .= ", '". $this->FormatValue($value, $arColumnInfo)."'";
					}
				}
			}
			elseif(array_key_exists("~".$strColumnName, $arFields))
			{
				$strInsert1 .= ", ".$strColumnName;
				$strInsert2 .= ", ".$arFields["~".$strColumnName];
			}
		}

		if($strInsert1!="")
		{
			$strInsert1 = substr($strInsert1, 2);
			$strInsert2 = substr($strInsert2, 2);
		}
		return array($strInsert1, $strInsert2);
	}


	function PrepareUpdate($strTableName, $arFields, $strFileDir="", $lang = false, $strTableAlias = "")
	{
		$arBinds = array();
		return $this->PrepareUpdateBind($strTableName, $arFields, $strFileDir, $lang, $arBinds, $strTableAlias);
	}

	function PrepareUpdateBind($strTableName, $arFields, $strFileDir, $lang, &$arBinds, $strTableAlias = "")
	{
		$arBinds = array();
		if ($strTableAlias != "")
			$strTableAlias .= ".";
		$strUpdate = "";
		$arColumns = $this->GetTableFields($strTableName);
		foreach($arColumns as $strColumnName => $arColumnInfo)
		{
			$type = $arColumnInfo["TYPE"];
			if(isset($arFields[$strColumnName]))
			{
				$value = $arFields[$strColumnName];
				if($value === false)
				{
					$strUpdate .= ", $strTableAlias".$strColumnName." = NULL";
				}
				else
				{
					if($type=="DATE")
					{
						if(strlen($value)>0)
							$strUpdate .= ", $strTableAlias".$strColumnName." = ".$this->CharToDateFunction($value, "FULL", $lang);
						else
							$strUpdate .= ", $strTableAlias".$strColumnName." = NULL";
					}
					elseif($type=="CLOB")
					{
						if(strlen($value)>0)
						{
							$strUpdate .= ", $strTableAlias".$strColumnName." = :$strColumnName";
							$arBinds[]=$strColumnName;
						}
						else
							$strUpdate .= ", $strTableAlias".$strColumnName." = NULL";
					}
					else
					{
						$value = $this->FormatValue($value, $arColumnInfo);
						$strUpdate .= ", $strTableAlias".$strColumnName." = '".$value."'";
					}
				}
			}
			elseif(is_set($arFields, "~".$strColumnName))
			{
				$strUpdate .= ", $strTableAlias".$strColumnName." = ".$arFields["~".$strColumnName];
			}
		}

		if($strUpdate!="")
			$strUpdate = substr($strUpdate, 2);

		return $strUpdate;
	}

	function Insert($table, $arFields, $error_position="", $DEBUG=false, $EXIST_ID="", $ignore_errors=false)
	{
		if (!is_array($arFields))
			return false;

		$str1 = "";
		$str2 = "";
		foreach ($arFields as $field => $value)
		{
			$str1 .= ($str1 <> ""? ", ":"").$field;
			if (strlen($value)<=0)
				$str2 .= ($str2 <> ""? ", ":"")."'".$value."'";
			else
				$str2 .= ($str2 <> ""? ", ":"").$value;
		}

		if (strlen($EXIST_ID) > 0)
		{
			$ID = $this->ForSql($EXIST_ID);
		}
		else
		{
			$ID = $this->NextID("sq_".$table);
		}

		$strSql = "INSERT INTO ".$table."(ID, ".$str1.") VALUES ('".$ID."', ".$str2.")";

		if ($DEBUG)
			echo "<br>".htmlspecialcharsEx($strSql)."<br>";

		$res = $this->Query($strSql, $ignore_errors, $error_position);

		if ($res === false)
			return false;
		else
			return $ID;
	}

	function Update($table, $arFields, $WHERE="", $error_position="", $DEBUG=false, $ignore_errors=false)
	{
		$rows = 0;
		if (is_array($arFields))
		{
			$ar = array();
			foreach ($arFields as $field => $value)
			{
				if (strlen($value))
					$ar[] = $field." = ".$value;
				else
					$ar[] = $field." = ''";
			}

			if (!empty($ar))
			{
				$strSql = "UPDATE ".$table." SET ".implode(", ", $ar)." ".$WHERE;
				if ($DEBUG)
					echo "<br>".htmlspecialcharsEx($strSql)."<br>";
				$q = $this->Query($strSql, $ignore_errors, $error_position);
				if (is_object($q))
					$rows = $q->AffectedRowsCount();
			}
		}
		return $rows;
	}

	public function Add($tablename, $arFields, $arCLOBFields = Array(), $strFileDir="", $ignore_errors=false, $error_position="", $arOptions=array())
	{
		global $DB;

		if(!isset($this) || !is_object($this) || !isset($this->type))
		{
			return $DB->Add($tablename, $arFields, $arCLOBFields, $strFileDir, $ignore_errors, $error_position, $arOptions);
		}
		else
		{
			if(!isset($arFields["ID"]) || intval($arFields["ID"])<=0)
				$arFields["ID"] = $this->NextID("sq_".$tablename);
			$arInsert = $this->PrepareInsert($tablename, $arFields, $strFileDir);

			$arBinds=Array();
			foreach($arCLOBFields as $name)
				if(is_set($arFields, $name))
					$arBinds[$name] = $arFields[$name];

			$strSql =
				"INSERT INTO ".$tablename."(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";

			if(count($arBinds)>0)
				$this->QueryBind($strSql, $arBinds, $ignore_errors, $error_position, $arOptions);
			else
				$this->Query($strSql, $ignore_errors, $error_position, $arOptions);

			return $arFields["ID"];
		}
	}

	function TopSql($strSql, $nTopCount)
	{
		$nTopCount = intval($nTopCount);
		if($nTopCount>0)
			return "SELECT * FROM (".$strSql.") WHERE ROWNUM<=".$nTopCount;
		else
			return $strSql;
	}

	function ForSql($strValue, $iMaxLength=0)
	{
		if ($iMaxLength <= 0 || $iMaxLength > 2000)
			$iMaxLength = 2000;

		$strValue = substr($strValue, 0, $iMaxLength);

		if (defined("BX_UTF"))
		{
			// From http://w3.org/International/questions/qa-forms-utf-8.html
			// This one can crash php with segmentation fault on large input data (over 20K)
			// https://bugs.php.net/bug.php?id=60423
			if (preg_match_all('%(
				[\x00-\x7E]                        # ASCII
				|[\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
				|\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
				|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
				|\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
				|\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
				|[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
				|\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
			)+%x', $strValue, $match))
				$strValue = implode(' ', $match[0]);
			else
				return ''; //There is no valid utf at all
		}

		return str_replace("'", "''", $strValue);
	}

	function ForSqlLike($strValue, $iMaxLength=0)
	{
		if ($iMaxLength <= 0 || $iMaxLength > 2000)
			$iMaxLength = 2000;

		$strValue = substr($strValue, 0, $iMaxLength);

		if(defined("BX_UTF"))
		{
			// From http://w3.org/International/questions/qa-forms-utf-8.html
			if(preg_match_all('%(
				[\x00-\x7E]                        # ASCII
				|[\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
				|\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
				|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
				|\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
				|\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
				|[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
				|\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
			)+%x', $strValue, $match))
				$strValue = implode('', $match[0]);
			else
				return ''; //There is no valid utf at all
		}

		return str_replace("'", "''", str_replace("\\", "\\\\\\\\", $strValue));
	}

	function InitTableVarsForEdit($tablename, $strIdentFrom = "str_", $strIdentTo="str_", $strSuffixFrom="", $bAlways=false)
	{
		$strSql = "SELECT COLUMN_NAME ".
				"FROM USER_TAB_COLUMNS ".
				"WHERE UPPER(TABLE_NAME) = UPPER('".$tablename."') ";

		if($db_result = $this->Query($strSql))
		{
			while($db_result_table_columns=$db_result->Fetch())
			{
				$varnameFrom=$strIdentFrom.$db_result_table_columns["COLUMN_NAME"].$strSuffixFrom;
				$varnameTo=$strIdentTo.$db_result_table_columns["COLUMN_NAME"];
				global ${$varnameFrom}, ${$varnameTo};
				if((isset(${$varnameFrom}) || $bAlways))
				{
					if(is_array(${$varnameFrom}))
					{
						${$varnameTo} = array();
						foreach(${$varnameFrom} as $k=>$v)
							${$varnameTo}[$k] = htmlspecialcharsbx($v);
					}
					else
					{
						${$varnameTo} = htmlspecialcharsbx(${$varnameFrom});
					}
				}
			}
		}
	}

	function GetTableFieldsList($table)
	{
		return array_keys($this->GetTableFields($table));
	}

	function GetTableFields($table)
	{
		if(!array_key_exists($table, $this->column_cache))
		{
			$this->column_cache[$table] = array();
			$strSql = "
				SELECT *
				FROM USER_TAB_COLUMNS
				WHERE UPPER(TABLE_NAME) = UPPER('".$table."')
			";
			$rs = $this->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			while($ar = $rs->Fetch())
			{
				$ar["NAME"] = $ar["COLUMN_NAME"];
				$ar["TYPE"] = $ar["DATA_TYPE"];
				$this->column_cache[$table][$ar["NAME"]] = $ar;
			}
		}
		return $this->column_cache[$table];
	}

	function Concat()
	{
		$str = "";
		$ar = func_get_args();
		if (is_array($ar)) $str .= implode(" || ", $ar);
		return $str;
	}

	function IsNull($expression, $result)
	{
		return "NVL(".$expression.", ".$result.")";
	}

	function Length($field)
	{
		return "length($field)";
	}

	function TableExists($tableName)
	{
		$tableName = preg_replace("/[^A-Za-z0-9%_]+/i", "", $tableName);
		$tableName = Trim($tableName);

		if (strlen($tableName) <= 0)
			return False;

		$dbResult = $this->Query("
			SELECT TABLE_NAME
			FROM USER_TABLES
			WHERE TABLE_NAME LIKE UPPER('".$this->ForSql($tableName)."')
		");
		if ($arResult = $dbResult->Fetch())
			return True;
		else
			return False;
	}

	public function GetIndexName($tableName, $arColumns, $bStrict = false)
	{
		if(!is_array($arColumns) || count($arColumns) <= 0)
			return "";

		$rs = $this->Query("SELECT * FROM USER_IND_COLUMNS WHERE TABLE_NAME=upper('".$this->ForSql($tableName)."')", true);
		if(!$rs)
			return "";

		$bFunc = false;
		$arIndexes = array();
		while($ar = $rs->Fetch())
		{
			$arIndexes[$ar["INDEX_NAME"]][$ar["COLUMN_POSITION"]-1] = $ar["COLUMN_NAME"];
			if(strncmp($ar["COLUMN_NAME"], "SYS_NC", 6)===0)
				$bFunc = true;
		}

		if($bFunc)
		{
			$rsFunc = $this->Query("SELECT * FROM USER_IND_EXPRESSIONS WHERE TABLE_NAME=upper('".$this->ForSql($tableName)."')", true);
			if($rsFunc)
			{
				while($arFunc = $rsFunc->Fetch())
					$arIndexes[$arFunc["INDEX_NAME"]][$arFunc["COLUMN_POSITION"]-1] = $arFunc["COLUMN_EXPRESSION"];
			}
		}

		$strColumns = implode(",", $arColumns);
		foreach($arIndexes as $Key_name => $arKeyColumns)
		{
			ksort($arKeyColumns);
			$strKeyColumns = implode(",", $arKeyColumns);
			if($bStrict)
			{
				if($strKeyColumns === $strColumns)
					return $Key_name;
			}
			else
			{
				if(substr($strKeyColumns, 0, strlen($strColumns)) === $strColumns)
					return $Key_name;
			}
		}

		return "";
	}

	function QueryBindSelect($strSql, $arBinds, $bIgnoreErrors=false, $error_position="", $arOptions=array())
	{
		global $DB;

		$this->DoConnect();
		global $prev_Query;
		$prev_Query[]=$strSql;
		$this->db_Error="";
		if ($this->DebugToFile || $DB->ShowSqlStat)
			$start_time = microtime(true);

		$result = oci_parse($this->db_Conn, $strSql);

		if(!$result)
		{
			$error=oci_error($this->db_Conn);
			$this->db_Error=$error["message"];
			$this->db_ErrorSQL = $strSql;
			if(!$bIgnoreErrors)
			{
				if($this->debug || (isset($_SESSION["SESS_AUTH"]["ADMIN"]) && $_SESSION["SESS_AUTH"]["ADMIN"]))
					echo "<br><font color=#ff0000>".$error_position."\n"."Parse Error: ".htmlspecialcharsbx($strSql)."</font>[".$error["message"]."]<br>";

				SendError("Parse Error:\n".$error_position."\n".$strSql." \n [".$error["message"]."]\n---------------\n\n");
				AddMessage2Log("Parse Error: ".$error_position."\n".$strSql." [".$error["message"]."]", "main");
				die("Query Error!");
			}
			return false;
		}

		foreach($arBinds as $key=>$value)
			oci_bind_by_name($result, ":".$key, $arBinds[$key], -1);

		$DB->cntQuery++;

		if($bIgnoreErrors)
		{
			$exec = @oci_execute($result, OCI_DEFAULT);
		}
		else
		{
			$exec = oci_execute($result, OCI_DEFAULT);
		}

		if(!$exec)
		{
			$error = oci_error($result);
			$this->db_Error = $error["message"];
			$this->db_ErrorSQL = $strSql;
			if(!$bIgnoreErrors)
			{
				AddMessage2Log("Query Error: ".$error_position."\n".$strSql." [".$error["message"]."]", "main");
				if ($this->DebugToFile)
					$this->startSqlTracker()->writeFileLog("ERROR: ".$this->db_Error);

				if($this->debug || (isset($_SESSION["SESS_AUTH"]["ADMIN"]) && $_SESSION["SESS_AUTH"]["ADMIN"]))
					echo $error_position."<br><font color=#ff0000>Oracle Query Error: ".htmlspecialcharsbx($strSql)."</font>[".htmlspecialcharsbx($error["message"])."]<br>";

				$error_position = preg_replace("/<br>/i", "\n", $error_position);
				SendError($error_position."\nOracle Query Error:\n".$strSql." \n [".$error["message"]."]\n---------------\n\n");

				if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbquery_error.php"))
					include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbquery_error.php");
				elseif(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/dbquery_error.php"))
					include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/dbquery_error.php");
				else
					die("Oracle Query Error!");

				die();
			}
			return false;
		}

		if($this->transaction == OCI_COMMIT_ON_SUCCESS)
			oci_commit($this->db_Conn);

		if ($this->DebugToFile || $DB->ShowSqlStat)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$exec_time = round(microtime(true) - $start_time, 10);

			if($DB->ShowSqlStat)
				$DB->addDebugQuery($strSql, $exec_time, $this->node_id);

			if ($this->DebugToFile)
				$this->startSqlTracker()->writeFileLog($strSql, $exec_time, "CONN: ".$this->db_Conn);
		}

		$res = new CDBResult($result);
		$res->DB = $this;
		if($DB->ShowSqlStat)
			$res->SqlTraceIndex = count($DB->arQueryDebug) - 1;
		return $res;
	}

	function QueryBind($strSql, $arBinds, $bIgnoreErrors=false, $error_position="", $arOptions=array())
	{
		global $DB;

		$this->DoConnect();
		global $prev_Query;
		$prev_Query[] = $strSql;
		$this->db_Error = "";

		if ($this->DebugToFile || $DB->ShowSqlStat)
			$start_time = microtime(true);

		$useReturningInto = preg_match ("/EMPTY_CLOB/i", $strSql) > 0;

		if ($useReturningInto)
		{
			$strBinds1 = $strBinds2 = "";
			foreach ($arBinds as $key => $value)
			{
				if (strlen($value) > 0)
				{
					if ($strBinds1 == "")
					{
						$strBinds1 = " RETURNING ";
						$strBinds2 = " INTO ";
					}
					else
					{
						$strBinds1 .= ",";
						$strBinds2 .= ",";
					}

					$strBinds1 .= $key;
					$strBinds2 .= ":".$key;
				}
			}
			$strSql .= $strBinds1.$strBinds2;
		}

		$result = oci_parse($this->db_Conn, $strSql);
		if (!$result)
		{
			$error = oci_error($this->db_Conn);
			$this->db_Error = $error["message"];
			$this->db_ErrorSQL = $strSql;
			if (!$bIgnoreErrors)
			{
				if($this->debug || (isset($_SESSION["SESS_AUTH"]["ADMIN"]) && $_SESSION["SESS_AUTH"]["ADMIN"]))
					echo "<br><font color=#ff0000>Parse Error: ".htmlspecialcharsbx($strSql)."</font>[".$error["message"]."]<br>";

				SendError("Parse Error:\n".$strSql." \n [".$error["message"]."]\n---------------\n\n");
				AddMessage2Log("Parse Error: ".$strSql." [".$error["message"]."]", "main");
				die("Query Error!");
			}
			return false;
		}

		/** @var \OCI_Lob[] $CLOB */
		$CLOB = array();
		foreach ($arBinds as $key => $value)
		{
			if (strlen($value) > 0)
			{
				$CLOB[$key] = oci_new_descriptor($this->db_Conn, OCI_DTYPE_LOB);
				oci_bind_by_name($result, ":".$key, $CLOB[$key], -1, OCI_B_CLOB);
				if (!$useReturningInto)
				{
					$CLOB[$key]->writeTemporary($value, OCI_TEMP_CLOB);
				}
			}
		}

		$DB->cntQuery++;

		if($bIgnoreErrors)
		{
			$exec = @oci_execute($result, OCI_DEFAULT);
		}
		else
		{
			$exec = oci_execute($result, OCI_DEFAULT);
		}

		if (!$exec)
		{
			$error = oci_error($result);
			$this->db_Error = $error["message"];
			$this->db_ErrorSQL = $strSql;
			if (!$bIgnoreErrors)
			{
				AddMessage2Log("Query Error: ".$error_position."\n".$strSql." [".$error["message"]."]", "main");
				if ($this->DebugToFile)
					$this->startSqlTracker()->writeFileLog("ERROR: ".$this->db_Error);

				if ($this->debug || (isset($_SESSION["SESS_AUTH"]["ADMIN"]) && $_SESSION["SESS_AUTH"]["ADMIN"]))
					echo $error_position."<br><font color=#ff0000>Oracle Query Error: ".htmlspecialcharsbx($strSql)."</font>[".htmlspecialcharsbx($error["message"])."]<br>";

				$error_position = preg_replace("/<br>/i", "\n", $error_position);
				SendError($error_position."\nOracle Query Error:\n".$strSql." \n [".$error["message"]."]\n---------------\n\n");

				if (file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbquery_error.php"))
					include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbquery_error.php");
				elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/dbquery_error.php"))
					include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/dbquery_error.php");
				else
					die("Oracle Query Error!");

				die();
			}
			return false;
		}

		if ($useReturningInto)
		{
			if (oci_num_rows($result) > 0)
			{
				foreach ($arBinds as $key => $value)
				{
					if ($value != "")
						$CLOB[$key]->save($value);
				}
			}
		}

		if ($this->transaction == OCI_COMMIT_ON_SUCCESS)
			oci_commit($this->db_Conn);

		foreach ($CLOB as $bindedClob)
		{
			if (!$useReturningInto)
				$bindedClob->close();
			$bindedClob->free();
		}

		if ($this->DebugToFile || $DB->ShowSqlStat)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$exec_time = round(microtime(true) - $start_time, 10);

			if ($DB->ShowSqlStat)
				$DB->addDebugQuery($strSql, $exec_time, $this->node_id);

			if ($this->DebugToFile)
				$this->startSqlTracker()->writeFileLog($strSql, $exec_time, "CONN: ".$this->db_Conn);
		}

		$res = new CDBResult($result);
		$res->DB = $this;
		if ($DB->ShowSqlStat)
			$res->SqlTraceIndex = count($DB->arQueryDebug) - 1;
		return $res;
	}

	function FormatValue($value, $arColumnInfo)
	{
		switch($arColumnInfo["DATA_TYPE"])
		{
			case "NUMBER":
				if(strlen($arColumnInfo["DATA_SCALE"])<=0)
				{
					$value = doubleval($value);
					if(!is_finite($value))
					{
						$value = 0;
					}
				}
				elseif(intval($arColumnInfo["DATA_SCALE"])<=0)
				{
					$value = intval($value);
				}
				else
				{
					$value = doubleval($value);
					if(!is_finite($value))
					{
						$value = 0;
					}
					$value = round($value, $arColumnInfo["DATA_SCALE"]);
				}

				if($arColumnInfo["DATA_PRECISION"]>0 && strlen(intval($value)) > intval($arColumnInfo["DATA_PRECISION"])-intval($arColumnInfo["DATA_SCALE"]))
					$value = intval(str_repeat('9', $arColumnInfo["DATA_PRECISION"] - $arColumnInfo["DATA_SCALE"]));

				return $value;

			case "VARCHAR2": case "CHAR":
				return str_replace("'","''",substr($value, 0, $arColumnInfo["CHAR_LENGTH"]));

			default:
				return str_replace("'", "''", $value);
		}
	}

	function Instr($str, $toFind)
	{
		return "INSTR($str, $toFind)";
	}
}

class CDBResult extends CAllDBResult
{
	var $arClobs=Array();
	var $bLast = false;
	var $bFetched;

	public function __construct($res=NULL)
	{
		parent::__construct($res);

		if($this->result)
		{
			$intNumFields = oci_num_fields($this->result);
			for($i=1; $i<=$intNumFields; $i++)
			{
				if (oci_field_type($this->result, $i)=="CLOB")
					$this->arClobs[] = oci_field_name($this->result, $i);
			}
		}
	}

	/** @deprecated */
	public function CDBResult($res=NULL)
	{
		self::__construct($res);
	}

	function Fetch()
	{
		global $DB;

		if($this->bNavStart || $this->bFromArray)
		{
			if(!is_array($this->arResult))
				return false;
			if($tmp = current($this->arResult))
				next($this->arResult);
			return $tmp;
		}
		elseif($this->bLast)
		{
			return false;
		}
		else
		{
			if($this->SqlTraceIndex)
				$start_time = microtime(true);

			$arr = $this->FetchInternal();

			if($this->SqlTraceIndex)
			{
				/** @noinspection PhpUndefinedVariableInspection */
				$exec_time = round(microtime(true) - $start_time, 10);
				$DB->addDebugTime($this->SqlTraceIndex, $exec_time);
				$DB->timeQuery += $exec_time;
			}

			return $arr;
		}
	}

	protected function FetchInternal()
	{
		if($this->resultObject !== null)
		{
			$arr = $this->resultObject->fetch();
		}
		else
		{
			if(oci_statement_type($this->result) == "SELECT")
			{
				$arr = oci_fetch_array($this->result, OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS);
			}
			else
			{
				return false;
			}

			if($arr === false)
			{
				$error = oci_error($this->result);
				if(intval($error["code"])!=0)
				{
					global $DB, $prev_Query;
					$error_msg = "Error in fetch [".$error["code"]."] "
						.$error["message"]."\n"
						."Previous queries: \n"
						.implode("\n\n", $prev_Query);
					if($DB->debug || (isset($_SESSION["SESS_AUTH"]["ADMIN"]) && $_SESSION["SESS_AUTH"]["ADMIN"]))
						echo "<br><font color=#ff0000>Fetch Error!</font>[".$error["message"]."<br>".$error_msg."]<br>";
					SendError($error_msg);
				}
				$this->bLast = true;
				return false;
			}

			foreach($this->arClobs as $FIELD_NAME)
			{
				/** @var \OCI_Lob $lob */
				if(is_object(($lob = $arr[$FIELD_NAME])))
					$arr[$FIELD_NAME] = $lob->load();
			}

			$this->AfterFetch($arr);
		}

		return $arr;
	}

	function SelectedRowsCount()
	{
		if($this->nSelectedCount !== false)
			return $this->nSelectedCount;

		return oci_num_rows($this->result);
	}

	function AffectedRowsCount()
	{
		return oci_num_rows($this->result);
	}

	function FieldsCount()
	{
		return oci_num_fields($this->result);
	}

	function FieldName($iCol)
	{
		return oci_field_name($this->result, $iCol+1);
	}

	function DBNavStart()
	{
		global $DB;

		if($this->bFetched === true || $this->bLast)
			return;

		$this->bFetched = true;
		$this->NavPageNomer = ($this->PAGEN < 1?($_SESSION[$this->SESS_PAGEN] < 1?1:$_SESSION[$this->SESS_PAGEN]):$this->PAGEN);

		if($this->NavShowAll)
		{
			$NavFirstRecordShow = 0;
			$NavLastRecordShow = 100000;
		}
		else
		{
			$NavFirstRecordShow = $this->NavPageSize*($this->NavPageNomer-1);
			$NavLastRecordShow = $this->NavPageSize*$this->NavPageNomer;
		}

		$temp_arrray = array();
		$num_rows = 0;
		$rsEnd = false;
		$cache_arrray = array();

		oci_set_prefetch($this->result, 100);

		if($this->SqlTraceIndex)
			$start_time = microtime(true);

		while($num_rows < $NavFirstRecordShow && !$rsEnd)
		{
			if(($db_result_array = $this->FetchInternal()))
			{
				$num_rows++;

				if(count($cache_arrray) == $this->NavPageSize)
					$cache_arrray = array();

				$cache_arrray[] = $db_result_array;
			}
			else
			{
				$rsEnd = true;
			}
		}

		if($rsEnd && !empty($cache_arrray))
		{
			$this->NavPageNomer = floor($num_rows / $this->NavPageSize);
			if($num_rows % $this->NavPageSize > 0)
				$this->NavPageNomer++;

			$temp_arrray = $cache_arrray;
		}

		$bFirst = true;
		while($num_rows < $NavLastRecordShow && !$rsEnd)
		{
			if(($db_result_array = $this->FetchInternal()))
			{
				$num_rows++;
				$temp_arrray[] = $db_result_array;
			}
			else
			{
				$rsEnd = true;
				if($bFirst && !empty($cache_arrray))
				{
					$this->NavPageNomer = floor($num_rows / $this->NavPageSize);
					if($num_rows % $this->NavPageSize > 0)
						$this->NavPageNomer++;

					$temp_arrray = $cache_arrray;
				}
			}
			$bFirst = false;
		}

		if(!$rsEnd)
		{
			while(oci_fetch($this->result))
			{
				$num_rows++;
			}
		}

		if($this->SqlTraceIndex)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$exec_time = round(microtime(true) - $start_time, 10);
			$DB->addDebugTime($this->SqlTraceIndex, $exec_time);
			$DB->timeQuery += $exec_time;
		}

		$this->arResult = $temp_arrray;

		$this->NavRecordCount = $num_rows;
		if($this->NavShowAll)
		{
			$this->NavPageSize = $this->NavRecordCount;
			$this->NavPageNomer = 1;
		}

		if($this->NavPageSize > 0)
			$this->NavPageCount = floor($this->NavRecordCount / $this->NavPageSize);
		else
			$this->NavPageCount = 0;

		if($this->NavPageSize <> 0 && $this->NavRecordCount % $this->NavPageSize > 0)
			$this->NavPageCount++;
	}

	function NavQuery($strSql, $cnt, $arNavStartParams, $bIgnoreErrors = false)
	{
		global $DB;

		if(isset($arNavStartParams["SubstitutionFunction"]))
		{
			$arNavStartParams["SubstitutionFunction"]($this, $strSql, $cnt, $arNavStartParams);
			return null;
		}

		if(isset($arNavStartParams["bDescPageNumbering"]))
			$bDescPageNumbering = $arNavStartParams["bDescPageNumbering"];
		else
			$bDescPageNumbering = false;

		$this->InitNavStartVars($arNavStartParams);
		$this->NavRecordCount = $cnt;

		if($this->NavShowAll)
			$this->NavPageSize = $this->NavRecordCount;

		//Number of pages (begin with 1)
		$this->NavPageCount = ($this->NavPageSize>0 ? floor($this->NavRecordCount/$this->NavPageSize) : 0);
		if($bDescPageNumbering)
		{
			$makeweight = 0;
			if($this->NavPageSize > 0)
				$makeweight = ($this->NavRecordCount % $this->NavPageSize);
			if($this->NavPageCount == 0 && $makeweight > 0)
				$this->NavPageCount = 1;

			//Number of page.
			$this->NavPageNomer =
			(
				$this->PAGEN < 1 || $this->PAGEN > $this->NavPageCount
				?
					($_SESSION[$this->SESS_PAGEN] < 1 || $_SESSION[$this->SESS_PAGEN] > $this->NavPageCount
					?
						$this->NavPageCount
					:
						$_SESSION[$this->SESS_PAGEN]
					)
				:
					$this->PAGEN
			);

			//Offset of RecordSet
			$NavFirstRecordShow = 0;
			if($this->NavPageNomer != $this->NavPageCount)
				$NavFirstRecordShow += $makeweight;

			$NavFirstRecordShow += ($this->NavPageCount - $this->NavPageNomer) * $this->NavPageSize;
			$NavLastRecordShow = $makeweight + ($this->NavPageCount - $this->NavPageNomer + 1) * $this->NavPageSize;
		}
		else
		{
			if($this->NavPageSize > 0 && ($this->NavRecordCount % $this->NavPageSize > 0))
				$this->NavPageCount++;

			//Number of page (begins with 1)
			$this->NavPageNomer = ($this->PAGEN < 1 || $this->PAGEN > $this->NavPageCount? ($_SESSION[$this->SESS_PAGEN] < 1 || $_SESSION[$this->SESS_PAGEN] > $this->NavPageCount? 1:$_SESSION[$this->SESS_PAGEN]):$this->PAGEN);

			//Offset of RecordSet
			$NavFirstRecordShow = $this->NavPageSize*($this->NavPageNomer-1);
			$NavLastRecordShow = $this->NavPageSize*$this->NavPageNomer;
		}

		$NavAdditionalRecords = 0;
		if(is_set($arNavStartParams, "iNavAddRecords"))
			$NavAdditionalRecords = $arNavStartParams["iNavAddRecords"];

		if(!$this->NavShowAll)
		{
			$strSql = "SELECT * FROM (SELECT T.*, ROWNUM as ROW_NUM_TMP FROM (".$strSql.") T  WHERE ROWNUM<=".($NavLastRecordShow+$NavAdditionalRecords).") WHERE ROW_NUM_TMP>".$NavFirstRecordShow;
		}

		if(is_object($this->DB))
			$res_tmp = $this->DB->Query($strSql, $bIgnoreErrors);
		else
			$res_tmp = $DB->Query($strSql, $bIgnoreErrors);

		// Return false on sql errors (if $bIgnoreErrors == true)
		if ($bIgnoreErrors && ($res_tmp === false))
			return false;

		$this->result = $res_tmp->result;
		$this->DB = $res_tmp->DB;

		$temp_arrray_add = array();
		$temp_arrray = array();
		$tmp_cnt = 0;

		while($ar = $this->FetchInternal())
		{
			$tmp_cnt++;
			if (intval($NavLastRecordShow - $NavFirstRecordShow) > 0 && $tmp_cnt > ($NavLastRecordShow - $NavFirstRecordShow))
				$temp_arrray_add[] = $ar;
			else
				$temp_arrray[] = $ar;
		}

		$this->arResult = $temp_arrray;
		$this->arResultAdd = (!empty($temp_arrray_add)? $temp_arrray_add : false);
		$this->nSelectedCount = $cnt;
		$this->bDescPageNumbering = $bDescPageNumbering;
		$this->bFromLimited = true;

		return null;
	}
}
