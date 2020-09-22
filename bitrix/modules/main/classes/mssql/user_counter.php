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

		$rs = $DB->Query("
			SELECT CNT FROM b_user_counter
			WHERE USER_ID = ".$user_id."
			AND SITE_ID = '".$DB->ForSQL($site_id)."'
			AND CODE = '".$DB->ForSQL($code)."'
		");

		if ($rs->Fetch())
		{
			$ssql = "";
			if ($tag != "")
				$ssql = ", TAG = '".$DB->ForSQL($tag)."'";

			$DB->Query("
				UPDATE b_user_counter SET
				CNT = ".$value." ".$ssql."
				WHERE USER_ID = ".$user_id."
				AND SITE_ID = '".$DB->ForSQL($site_id)."'
				AND CODE = '".$DB->ForSQL($code)."'
			");
		}
		else
		{
			$DB->Query("
				INSERT INTO b_user_counter
				(CNT, USER_ID, SITE_ID, CODE, TAG)
				VALUES
				(".$value.", ".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."', '".$DB->ForSQL($tag)."')
			", true);
		}

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

		$rs = $DB->Query("
			UPDATE b_user_counter SET
			CNT = CNT + ".$increment."
			WHERE USER_ID = ".$user_id."
			AND SITE_ID = '".$DB->ForSQL($site_id)."'
			AND CODE = '".$DB->ForSQL($code)."'
		");
		if($rs->AffectedRowsCount() == 0)
			$DB->Query("
				INSERT INTO b_user_counter
				(CNT, USER_ID, SITE_ID, CODE)
				VALUES
				(".$increment.", ".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."')
			", true);

		if (self::$counters && self::$counters[$user_id])
		{
			$staticCacheCode = (strpos($code, '**') === 0 ? '**' : $code);

			if ($site_id == self::ALL_SITES)
			{
				foreach(self::$counters[$user_id] as $key => $tmp)
				{
					if (isset(self::$counters[$user_id][$key][$staticCacheCode]))
						self::$counters[$user_id][$key][$staticCacheCode] = self::$counters[$user_id][$key][$staticCacheCode] + $increment;
					else
						self::$counters[$user_id][$key][$staticCacheCode] = $increment;
				}
			}
			else
			{
				if (!isset(self::$counters[$user_id][$site_id]))
					self::$counters[$user_id][$site_id] = array();

				if (isset(self::$counters[$user_id][$site_id][$staticCacheCode]))
					self::$counters[$user_id][$site_id][$staticCacheCode] = self::$counters[$user_id][$site_id][$staticCacheCode] + $increment;
				else
					self::$counters[$user_id][$site_id][$staticCacheCode] = $increment;
			}
		}
		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		if ($sendPull)
		{
			if (
				strpos($code, '**') === 0
				&& $code != '**'
			)
			{
				self::SendPullEvent($user_id, '**', true);
			}
			else
			{
				self::SendPullEvent($user_id, $code);
			}
		}

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

		$rs = $DB->Query("
			UPDATE b_user_counter SET
			CNT = CNT - ".$decrement."
			WHERE USER_ID = ".$user_id."
			AND SITE_ID = '".$DB->ForSQL($site_id)."'
			AND CODE = '".$DB->ForSQL($code)."'
		");
		if($rs->AffectedRowsCount() == 0)
			$DB->Query("
				INSERT INTO b_user_counter
				(CNT, USER_ID, SITE_ID, CODE)
				VALUES
				(-".$decrement.", ".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."')
			", true);

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
		global $DB;

		if (strlen($sub_select) > 0)
		{
			$rsSubSelect = $DB->Query($sub_select);
			while($arCounter = $rsSubSelect->Fetch())
			{
				self::Increment($arCounter["ID"], $arCounter["CODE"], $arCounter["SITE_ID"], $sendPull);
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
			foreach ($site_id as $i => $site_id_tmp)
			{
				$rs = $DB->Query("
					DELETE FROM b_user_counter
					WHERE USER_ID = ".$user_id."
					AND SITE_ID = '".$DB->ForSQL($site_id_tmp)."'
					AND CODE LIKE '".$DB->ForSQL($code)."L%'
				");

				$rs = $DB->Query("
					UPDATE b_user_counter SET
					CNT = 0, LAST_DATE = ".$DB->CurrentTimeFunction()."
					WHERE USER_ID = ".$user_id."
					AND SITE_ID = '".$DB->ForSQL($site_id_tmp)."'
					AND CODE = '".$DB->ForSQL($code)."'
				");
				if($rs->AffectedRowsCount() == 0)
					$DB->Query("
						INSERT INTO b_user_counter
						(CNT, LAST_DATE, USER_ID, SITE_ID, CODE)
						VALUES
						(0, ".$DB->CurrentTimeFunction().", ".$user_id.", '".$DB->ForSQL($site_id_tmp)."', '".$DB->ForSQL($code)."')
					", true);
			}
		}
		else
		{
			foreach ($site_id as $i => $site_id_tmp)
			{
				$rs = $DB->Query("
					UPDATE b_user_counter SET
					CNT = 0, LAST_DATE = ".$DB->CurrentTimeFunction()."
					WHERE USER_ID = ".$user_id."
					AND SITE_ID = '".$DB->ForSQL($site_id_tmp)."'
					AND CODE = '".$DB->ForSQL($code)."'
				");
				if($rs->AffectedRowsCount() == 0)
					$DB->Query("
						INSERT INTO b_user_counter
						(CNT, LAST_DATE, USER_ID, SITE_ID, CODE)
						VALUES
						(0, ".$DB->CurrentTimeFunction().", ".$user_id.", '".$DB->ForSQL($site_id_tmp)."', '".$DB->ForSQL($code)."')
					", true);
			}
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
		global $DB, $CACHE_MANAGER;

		if (strlen($code) <= 0)
		{
			return false;
		}

		$arUserIdToPull = Array();

		if (self::CheckLiveMode())
		{
			$res = $DB->Query("SELECT USER_ID FROM b_user_counter WHERE CODE = '".$code."'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			while($row = $res->Fetch())
			{
				$arUserIdToPull[] = $row["USER_ID"];
			}
			$arUserIdToPull = array_unique($arUserIdToPull);
		}

		$DB->Query("DELETE FROM b_user_counter WHERE CODE = '".$code."'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		self::$counters = false;
		$CACHE_MANAGER->CleanDir("user_counter");

		foreach($arUserIdToPull as $userId)
		{
			self::SendPullEvent($userId, '**', true);
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