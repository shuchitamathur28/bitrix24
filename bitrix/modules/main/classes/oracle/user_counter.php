<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/user_counter.php");

class CUserCounter extends CAllUserCounter
{
	public static function Set($user_id, $code, $value, $site_id = SITE_ID, $tag = '', $sendPull = true)
	{
		global $DB, $CACHE_MANAGER;

		$value = intval($value);
		$user_id = intval($user_id);
		if ($user_id < 0 || strlen($code) <= 0)
			return false;

		$ssql = "";
		if ($tag != "")
			$ssql = ", b_user_counter.TAG = '".$DB->ForSQL($tag)."'";

		$strSQL = "
			MERGE INTO b_user_counter USING (SELECT ".$user_id." USER_ID, '".$DB->ForSQL($site_id)."' SITE_ID, '".$DB->ForSQL($code)."' CODE FROM dual)
			source ON
			(
				source.USER_ID = b_user_counter.USER_ID
				AND source.SITE_ID = b_user_counter.SITE_ID
				AND source.CODE = b_user_counter.CODE
			)
			WHEN MATCHED THEN
				UPDATE SET b_user_counter.CNT = ".$value." ".$ssql.", b_user_counter.LAST_DATE = ".$DB->CurrentTimeFunction()."
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, SITE_ID, CODE, CNT, LAST_DATE, TAG)
				VALUES (".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."', ".$value.", ".$DB->CurrentTimeFunction().", '".$DB->ForSQL($tag)."')
		";
		$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (self::$counters && self::$counters[$user_id])
		{
			if ($site_id == self::ALL_SITES)
			{
				foreach(self::$counters[$user_id] as $key => $tmp)
				{
					self::$counters[$user_id][$key][$code] = $value;
				}
			}
			else
			{
				if (!isset(self::$counters[$user_id][$site_id]))
					self::$counters[$user_id][$site_id] = array();

				self::$counters[$user_id][$site_id][$code] = $value;
			}
		}

		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		if ($sendPull)
			self::SendPullEvent($user_id, $code);

		return true;
	}

	public static function Increment($user_id, $code, $site_id = SITE_ID, $sendPull = true, $increment = 1)
	{
		global $DB, $CACHE_MANAGER;

		$user_id = intval($user_id);
		if ($user_id < 0 || strlen($code) <= 0)
			return false;

		$increment = intval($increment);

		$strSQL = "
			MERGE INTO b_user_counter USING (SELECT ".$user_id." USER_ID, '".$DB->ForSQL($site_id)."' SITE_ID, '".$DB->ForSQL($code)."' CODE FROM dual)
			source ON
			(
				source.USER_ID = b_user_counter.USER_ID
				AND source.SITE_ID = b_user_counter.SITE_ID
				AND source.CODE = b_user_counter.CODE
			)
			WHEN MATCHED THEN
				UPDATE SET b_user_counter.CNT = b_user_counter.CNT + ".$increment.", b_user_counter.LAST_DATE = ".$DB->CurrentTimeFunction()."
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, SITE_ID, CODE, CNT, LAST_DATE)
				VALUES (".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."', ".$increment.", ".$DB->CurrentTimeFunction().")
		";
		$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (self::$counters && self::$counters[$user_id])
		{
			if ($site_id == self::ALL_SITES)
			{
				foreach(self::$counters[$user_id] as $key => $tmp)
				{
					if (isset(self::$counters[$user_id][$key][$code]))
						self::$counters[$user_id][$key][$code] = self::$counters[$user_id][$key][$code] + $increment;
					else
						self::$counters[$user_id][$key][$code] = $increment;
				}
			}
			else
			{
				if (!isset(self::$counters[$user_id][$site_id]))
					self::$counters[$user_id][$site_id] = array();

				if (isset(self::$counters[$user_id][$site_id][$code]))
					self::$counters[$user_id][$site_id][$code] = self::$counters[$user_id][$site_id][$code] + $increment;
				else
					self::$counters[$user_id][$site_id][$code] = $increment;
			}
		}
		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		if ($sendPull)
			self::SendPullEvent($user_id, $code);

		return true;
	}

	/**
	* @deprecated
	*/
	public static function Decrement($user_id, $code, $site_id = SITE_ID, $sendPull = true, $decrement = 1)
	{
		global $DB, $CACHE_MANAGER;

		$user_id = intval($user_id);
		if ($user_id < 0 || strlen($code) <= 0)
			return false;

		$decrement = intval($decrement);

		$strSQL = "
			MERGE INTO b_user_counter USING (SELECT ".$user_id." USER_ID, '".$DB->ForSQL($site_id)."' SITE_ID, '".$DB->ForSQL($code)."' CODE FROM dual)
			source ON
			(
				source.USER_ID = b_user_counter.USER_ID
				AND source.SITE_ID = b_user_counter.SITE_ID
				AND source.CODE = b_user_counter.CODE
			)
			WHEN MATCHED THEN
				UPDATE SET b_user_counter.CNT = b_user_counter.CNT - ".$decrement.", b_user_counter.LAST_DATE = ".$DB->CurrentTimeFunction()."
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, SITE_ID, CODE, CNT, LAST_DATE)
				VALUES (".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."', -".$decrement.", ".$DB->CurrentTimeFunction().")
		";
		$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (self::$counters && self::$counters[$user_id])
		{
			if ($site_id == self::ALL_SITES)
			{
				foreach(self::$counters[$user_id] as $key => $tmp)
				{
					if (isset(self::$counters[$user_id][$key][$code]))
						self::$counters[$user_id][$key][$code] = self::$counters[$user_id][$key][$code] - $decrement;
					else
						self::$counters[$user_id][$key][$code] = -$decrement;
				}
			}
			else
			{
				if (!isset(self::$counters[$user_id][$site_id]))
					self::$counters[$user_id][$site_id] = array();

				if (isset(self::$counters[$user_id][$site_id][$code]))
					self::$counters[$user_id][$site_id][$code] = self::$counters[$user_id][$site_id][$code] - $decrement;
				else
					self::$counters[$user_id][$site_id][$code] = -$decrement;
			}
		}

		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		if ($sendPull)
			self::SendPullEvent($user_id, $code);

		return true;
	}

	public static function IncrementWithSelect($sub_select, $sendPull = true, $arParams = array())
	{
		global $DB, $CACHE_MANAGER;

		if (strlen($sub_select) > 0)
		{
			$pullInclude = $sendPull && self::CheckLiveMode();
			$strSQL = "
				MERGE INTO b_user_counter USING (".$sub_select.")
				source ON (
					source.ID = b_user_counter.USER_ID
					AND source.SITE_ID = b_user_counter.SITE_ID
					AND source.CODE = b_user_counter.CODE
				)
				WHEN MATCHED THEN
					UPDATE SET b_user_counter.CNT = b_user_counter.CNT + source.CNT, SENT = source.SENT
				WHEN NOT MATCHED THEN
					INSERT (USER_ID, CNT, SITE_ID, CODE, SENT".(is_array($arParams) && isset($arParams["SET_TIMESTAMP"]) ? ", TIMESTAMP_X" : "").")
						VALUES (source.ID, source.CNT, source.SITE_ID, source.CODE, source.SENT".(is_array($arParams) && isset($arParams["SET_TIMESTAMP"]) ? ", source.TIMESTAMP_X" : "").")
			";
			$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			if (
				!is_array($arParams)
				|| !isset($arParams["CLEAN_CACHE"])
				|| $arParams["CLEAN_CACHE"] != "N"
			)
			{
				self::$counters = false;
				$CACHE_MANAGER->CleanDir("user_counter");
			}

			if ($pullInclude)
			{
				$arSites = Array();
				$res = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
				while($row = $res->Fetch())
					$arSites[] = $row['ID'];

				$strSQL = "
					SELECT pc.CHANNEL_ID, uc.USER_ID, uc.SITE_ID, uc.CODE, uc.CNT
					FROM b_user_counter uc
					INNER JOIN b_pull_channel pc ON pc.USER_ID = uc.USER_ID
					WHERE uc.SENT = '0'
				";
				$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

				$pullMessage = Array();
				while($row = $res->Fetch())
				{
					CUserCounter::addValueToPullMessage($row, $arSites, $pullMessage);
				}

				$DB->Query("UPDATE b_user_counter SET SENT = '1' WHERE SENT = '0' AND CODE NOT LIKE '**L%'");

				foreach ($pullMessage as $channelId => $arMessage)
				{
					CPullStack::AddByChannel($channelId, Array(
						'module_id' => 'main',
						'command' => 'user_counter',
						'expiry' => 3600,
						'params' => $arMessage,
					));
				}
			}
		}
	}

	public static function Clear($user_id, $code, $site_id = SITE_ID, $sendPull = true, $bMultiple = false)
	{
		global $DB, $CACHE_MANAGER;

		$user_id = intval($user_id);
		if (
			$user_id < 0
			|| strlen($code) <= 0
		)
		{
			return false;
		}

		if (!is_array($site_id))
		{
			$site_id = array($site_id);
		}

		if ($bMultiple)
		{
			$siteToDelete = "";
			$strUpsertSQL = "
				MERGE INTO b_user_counter USING (
					";

			foreach ($site_id as $i => $site_id_tmp)
			{
				if ($i > 0)
				{
					$strUpsertSQL .= " UNION ";
					$siteToDelete .= ",";
				}

				$siteToDelete .= "'".$DB->ForSQL($site_id_tmp)."'";
				$strUpsertSQL .= " SELECT ".$user_id." USER_ID, '".$DB->ForSQL($site_id_tmp)."' SITE_ID, '".$DB->ForSQL($code)."' CODE FROM dual ";
			}

			$strUpsertSQL .= "
				)
				source ON
				(
					source.USER_ID = b_user_counter.USER_ID
					AND source.SITE_ID = b_user_counter.SITE_ID
					AND source.CODE = b_user_counter.CODE
				)
				WHEN MATCHED THEN
					UPDATE SET b_user_counter.CNT = 0, b_user_counter.LAST_DATE = ".$DB->CurrentTimeFunction()."
				WHEN NOT MATCHED THEN
					INSERT (USER_ID, SITE_ID, CODE, CNT, LAST_DATE)
					VALUES (source.USER_ID, source.SITE_ID, source.CODE, 0, ".$DB->CurrentTimeFunction().")
			";

			$strDeleteSQL = "
				DELETE FROM b_user_counter
				WHERE
					USER_ID = ".$user_id."
					".(
						count($site_id) == 1
							? " AND SITE_ID = '".$site_id[0]."' "
							: " AND SITE_ID IN (".$siteToDelete.") "
					)."
					AND CODE LIKE '".$DB->ForSQL($code)."L%'
				";

			$DB->Query($strDeleteSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			$DB->Query($strUpsertSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
		else
		{
			$strSQL = "
				MERGE INTO b_user_counter USING (
					";

			foreach ($site_id as $i => $site_id_tmp)
			{
				if ($i > 0)
					$strSQL .= " UNION ";
				$strSQL .= " SELECT ".$user_id." USER_ID, '".$DB->ForSQL($site_id_tmp)."' SITE_ID, '".$DB->ForSQL($code)."' CODE FROM dual ";
			}

			$strSQL .= "
				)
				source ON
				(
					source.USER_ID = b_user_counter.USER_ID
					AND source.SITE_ID = b_user_counter.SITE_ID
					AND source.CODE = b_user_counter.CODE
				)
				WHEN MATCHED THEN
					UPDATE SET b_user_counter.CNT = 0, b_user_counter.LAST_DATE = ".$DB->CurrentTimeFunction()."
				WHEN NOT MATCHED THEN
					INSERT (USER_ID, SITE_ID, CODE, CNT, LAST_DATE)
					VALUES (source.USER_ID, source.SITE_ID, source.CODE, 0, ".$DB->CurrentTimeFunction().")
			";
			$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		if (self::$counters && self::$counters[$user_id])
		{
			foreach ($site_id as $site_id_tmp)
			{
				if ($site_id_tmp == self::ALL_SITES)
				{
					foreach(self::$counters[$user_id] as $key => $tmp)
						self::$counters[$user_id][$key][$code] = 0;
					break;
				}
				else
				{
					if (!isset(self::$counters[$user_id][$site_id_tmp]))
						self::$counters[$user_id][$site_id_tmp] = array();

					self::$counters[$user_id][$site_id_tmp][$code] = 0;
				}
			}
		}
		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		if ($sendPull)
			self::SendPullEvent($user_id, $code);

		return true;
	}

	public static function DeleteByCode($code)
	{
		global $DB, $APPLICATION, $CACHE_MANAGER;

		if (strlen($code) <= 0)
		{
			return false;
		}

		$pullMessage = Array();
		$bPullEnabled = false;

		if (self::CheckLiveMode())
		{
			$bPullEnabled = true;

			$arSites = Array();
			$res = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
			while($row = $res->Fetch())
			{
				$arSites[] = $row['ID'];
			}

			$strSQL = "
				SELECT pc.CHANNEL_ID, uc.USER_ID, uc.SITE_ID, uc.CODE, uc.CNT
				FROM b_user_counter uc
				INNER JOIN b_pull_channel pc ON pc.USER_ID = uc.USER_ID
				WHERE uc.CODE LIKE '**%'
			";

			$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			while($row = $res->Fetch())
			{
				if ($row["CODE"] == $code)
				{
					continue;
				}

				CUserCounter::addValueToPullMessage($row, $arSites, $pullMessage);
			}
		}

		$DB->Query("DELETE FROM b_user_counter WHERE CODE = '".$code."'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		self::$counters = false;
		$CACHE_MANAGER->CleanDir("user_counter");

		foreach ($pullMessage as $channelId => $arMessage)
		{
			CPullStack::AddByChannel($channelId, Array(
				'module_id' => 'main',
				'command' => 'user_counter',
				'expiry' => 3600,
				'params' => $arMessage,
			));
		}
	}

	protected static function dbIF($condition, $yes, $no)
	{
		return "case when ".$condition." then ".$yes." else ".$no." end ";
	}

	// legacy function
	public static function ClearByUser($user_id, $site_id = SITE_ID, $code = self::ALL_SITES, $bMultiple = false)
	{
		return self::Clear($user_id, $code, $site_id, true, $bMultiple);
	}
}

class CUserCounterPage extends CAllUserCounterPage
{
	public static function checkSendCounter()
	{
	}
}
?>