<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/agent.php");

class CAgent extends CAllAgent
{
	public static function CheckAgents()
	{
		global $DB, $CACHE_MANAGER, $pPERIOD;

		if(defined("NO_AGENT_CHECK") && NO_AGENT_CHECK===true)
			return null;

		$agents_use_crontab = COption::GetOptionString("main", "agents_use_crontab", "N");
		$str_crontab = "";
		if($agents_use_crontab=="Y" || (defined("BX_CRONTAB_SUPPORT") && BX_CRONTAB_SUPPORT===true))
		{
			if(defined("BX_CRONTAB") && BX_CRONTAB==true)
				$str_crontab = " AND IS_PERIOD='N' ";
			else
				$str_crontab = " AND IS_PERIOD='Y' ";
		}

		$cache_id = "agents".$str_crontab;
		if(CACHED_b_agent !== false && $CACHE_MANAGER->Read(CACHED_b_agent, $cache_id, "agents"))
		{
			$saved_time = $CACHE_MANAGER->Get($cache_id);
			if(time() < $saved_time)
				return "";
		}

		$logFunction = defined("BX_AGENTS_LOG_FUNCTION") && function_exists(BX_AGENTS_LOG_FUNCTION)? BX_AGENTS_LOG_FUNCTION: false;

		//This array will prevent multiple agent execution on one hit
		$arExecuted = array();
		do
		{
			$DB->StartTransaction();
			//Try to accuire the lock
			$strSql=
				"SELECT ID, NAME, AGENT_INTERVAL, IS_PERIOD, MODULE_ID ".
				"FROM b_agent ".
				"WHERE ACTIVE='Y'  ".
				"	AND NEXT_EXEC<=SYSDATE ".
				$str_crontab.
				"FOR UPDATE NOWAIT ".
				"ORDER BY SORT desc";

			$db_result_agents = $DB->Query($strSql, true);
			if(!$db_result_agents)
			{
				$DB->Commit();
				break;
			}

			//Check if there is at least one agent to execute
			while($arAgent = $db_result_agents->Fetch())
			{
				//Check if we already executed this one agent
				if(array_key_exists($arAgent["ID"], $arExecuted))
					continue;
				//No we did not
				//so check if module is OK
				if(strlen($arAgent["MODULE_ID"]) <= 0)
					break;
				if($arAgent["MODULE_ID"] == "main")
					break;
				if(CModule::IncludeModule($arAgent["MODULE_ID"]))
					break;
			}

			//All work is done for this hit
			if(!$arAgent)
			{
				$DB->Commit();
				break;
			}

			if ($logFunction)
				$logFunction($arAgent, "start");

			if(count($arExecuted) <= 0)
			{
				@set_time_limit(0);
				ignore_user_abort(true);
			}

			//Make the mark of execution
			$arExecuted[$arAgent["ID"]] = true;

			//these vars can be assigned within agent code
			$pPERIOD = $arAgent["AGENT_INTERVAL"];

			CTimeZone::Disable();

			global $USER;
			unset($USER);
			try
			{
				$eval_result = "";
				$e = eval("\$eval_result=".$arAgent["NAME"]);
			}
			catch (Exception $e)
			{
				CTimeZone::Enable();
				$DB->Commit();

				$application = \Bitrix\Main\Application::getInstance();
				$exceptionHandler = $application->getExceptionHandler();
				$exceptionHandler->writeToLog($e);

				continue;
			}
			unset($USER);

			CTimeZone::Enable();

			if ($logFunction)
				$logFunction($arAgent, "finish", $eval_result, $e);

			if($e === false)
			{
				$DB->Commit();
				continue;
			}
			elseif(strlen($eval_result)<=0)
			{
				$strSql="DELETE FROM b_agent WHERE ID=".$arAgent["ID"];
			}
			else
			{
				if($arAgent["IS_PERIOD"]=="Y")
					$strSql="UPDATE b_agent SET NAME='".$DB->ForSQL($eval_result, 2000)."', LAST_EXEC=SYSDATE, NEXT_EXEC=NEXT_EXEC+".$pPERIOD."/86400 WHERE ID=".$arAgent["ID"];
				else
					$strSql="UPDATE b_agent SET NAME='".$DB->ForSQL($eval_result, 2000)."', LAST_EXEC=SYSDATE, NEXT_EXEC=SYSDATE+".$pPERIOD."/86400 WHERE ID=".$arAgent["ID"];
			}
			$DB->Query($strSql);

			$DB->Commit();
		}
		while(true);

		if((count($arExecuted) <= 0) && (CACHED_b_agent !== false))
		{
			$rs = $DB->Query("SELECT round((MIN(NEXT_EXEC) - SYSDATE)*86400) DATE_DIFF FROM b_agent WHERE ACTIVE='Y'");
			$ar = $rs->Fetch();
			if(!$ar || $ar["DATE_DIFF"] < 0)
				$date_diff = 0;
			elseif($ar["DATE_DIFF"] > CACHED_b_agent)
				$date_diff = CACHED_b_agent;
			else
				$date_diff = $ar["DATE_DIFF"];

			if(isset($saved_time))
			{
				$CACHE_MANAGER->Clean($cache_id, "agents");
				$CACHE_MANAGER->Read(CACHED_b_agent, $cache_id, "agents");
			}
			$CACHE_MANAGER->Set($cache_id, intval(time()+$date_diff));
		}
		return null;
	}
}
