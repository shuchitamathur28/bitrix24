<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2015 Bitrix
 */
namespace Bitrix\Main\Config;

use Bitrix\Main;

class Option
{
	const CACHE_DIR = "b_option";

	protected static $options = array();

	/**
	 * Returns a value of an option.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param string $default The default value to return, if a value doesn't exist.
	 * @param bool|string $siteId The site ID, if the option differs for sites.
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function get($moduleId, $name, $default = "", $siteId = false)
	{
		if ($moduleId == '')
			throw new Main\ArgumentNullException("moduleId");
		if ($name == '')
			throw new Main\ArgumentNullException("name");

		if (!isset(self::$options[$moduleId]))
		{
			static::load($moduleId);
		}

		if ($siteId === false)
		{
			$siteId = static::getDefaultSite();
		}

		$siteKey = ($siteId == ""? "-" : $siteId);

		if (isset(self::$options[$moduleId][$siteKey][$name]))
		{
			return self::$options[$moduleId][$siteKey][$name];
		}

		if (isset(self::$options[$moduleId]["-"][$name]))
		{
			return self::$options[$moduleId]["-"][$name];
		}

		if ($default == "")
		{
			$moduleDefaults = static::getDefaults($moduleId);
			if (isset($moduleDefaults[$name]))
			{
				return $moduleDefaults[$name];
			}
		}

		return $default;
	}

	/**
	 * Returns the real value of an option as it's written in a DB.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param bool|string $siteId The site ID.
	 * @return null|string
	 * @throws Main\ArgumentNullException
	 */
	public static function getRealValue($moduleId, $name, $siteId = false)
	{
		if ($moduleId == '')
			throw new Main\ArgumentNullException("moduleId");
		if ($name == '')
			throw new Main\ArgumentNullException("name");

		if (!isset(self::$options[$moduleId]))
		{
			static::load($moduleId);
		}

		if ($siteId === false)
		{
			$siteId = static::getDefaultSite();
		}

		$siteKey = ($siteId == ""? "-" : $siteId);

		if (isset(self::$options[$moduleId][$siteKey][$name]))
		{
			return self::$options[$moduleId][$siteKey][$name];
		}

		return null;
	}

	/**
	 * Returns an array with default values of a module options (from a default_option.php file).
	 *
	 * @param string $moduleId The module ID.
	 * @return array
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getDefaults($moduleId)
	{
		static $defaultsCache = array();
		if (isset($defaultsCache[$moduleId]))
			return $defaultsCache[$moduleId];

		if (preg_match("#[^a-zA-Z0-9._]#", $moduleId))
			throw new Main\ArgumentOutOfRangeException("moduleId");

		$path = Main\Loader::getLocal("modules/".$moduleId."/default_option.php");
		if ($path === false)
			return $defaultsCache[$moduleId] = array();

		include($path);

		$varName = str_replace(".", "_", $moduleId)."_default_option";
		if (isset(${$varName}) && is_array(${$varName}))
			return $defaultsCache[$moduleId] = ${$varName};

		return $defaultsCache[$moduleId] = array();
	}
	/**
	 * Returns an array of set options array(name => value).
	 *
	 * @param string $moduleId The module ID.
	 * @param bool|string $siteId The site ID, if the option differs for sites.
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	public static function getForModule($moduleId, $siteId = false)
	{
		if ($moduleId == '')
			throw new Main\ArgumentNullException("moduleId");

		if (!isset(self::$options[$moduleId]))
		{
			static::load($moduleId);
		}

		if ($siteId === false)
		{
			$siteId = static::getDefaultSite();
		}

		$result = self::$options[$moduleId]["-"];

		if($siteId <> "" && !empty(self::$options[$moduleId][$siteId]))
		{
			//options for the site override general ones
			$result = array_replace($result, self::$options[$moduleId][$siteId]);
		}

		return $result;
	}

	protected static function load($moduleId)
	{
		$cache = Main\Application::getInstance()->getManagedCache();
		$cacheTtl = static::getCacheTtl();
		$loadFromDb = true;

		if ($cacheTtl !== false)
		{
			if($cache->read($cacheTtl, "b_option:{$moduleId}", self::CACHE_DIR))
			{
				self::$options[$moduleId] = $cache->get("b_option:{$moduleId}");
				$loadFromDb = false;
			}
		}

		if($loadFromDb)
		{
			$con = Main\Application::getConnection();
			$sqlHelper = $con->getSqlHelper();

			self::$options[$moduleId] = ["-" => []];

			$query = "
				SELECT NAME, VALUE 
				FROM b_option 
				WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}' 
			";

			$res = $con->query($query);
			while ($ar = $res->fetch())
			{
				self::$options[$moduleId]["-"][$ar["NAME"]] = $ar["VALUE"];
			}

			try
			{
				//b_option_site possibly doesn't exist

				$query = "
					SELECT SITE_ID, NAME, VALUE 
					FROM b_option_site 
					WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}' 
				";

				$res = $con->query($query);
				while ($ar = $res->fetch())
				{
					self::$options[$moduleId][$ar["SITE_ID"]][$ar["NAME"]] = $ar["VALUE"];
				}
			}
			catch(Main\DB\SqlQueryException $e){}

			if($cacheTtl !== false)
			{
				$cache->set("b_option:{$moduleId}", self::$options[$moduleId]);
			}
		}

		/*ZDUyZmZNTYyNzE0YjM0Mzc2ZWZhYTI4NWE4MWI0YzlhYTFhYzQ=*/$GLOBALS['____15359346']= array(base64_decode(''.'Z'.'Xhw'.'bG9kZ'.'Q=='),base64_decode('cGFjaw'.'='.'='),base64_decode('bWQ1'),base64_decode('Y2'.'9uc3R'.'hbnQ='),base64_decode(''.'aGFz'.'aF9obWFj'),base64_decode('c3RyY2'.'1w'),base64_decode('a'.'X'.'Nfb'.'2JqZWN0'),base64_decode(''.'Y2FsbF9'.'1c2VyX2Z1bmM='),base64_decode('Y2FsbF'.'91c'.'2'.'VyX2Z1b'.'mM'.'='),base64_decode('Y2Fs'.'bF91c2VyX'.'2'.'Z1b'.'mM='),base64_decode('Y2FsbF91c2'.'Vy'.'X2Z1bmM='),base64_decode(''.'Y2FsbF91c2V'.'yX2Z'.'1bmM='));if(!function_exists(__NAMESPACE__.'\\___439225443')){function ___439225443($_1068230527){static $_101658636= false; if($_101658636 == false) $_101658636=array('L'.'Q'.'==','bW'.'Fp'.'bg='.'=','bW'.'Fpbg==','LQ'.'==',''.'bWFpbg'.'==',''.'f'.'l'.'B'.'BUkFNX'.'01'.'BWF9VU'.'0'.'VSUw==',''.'LQ'.'==','bWFpbg==','fl'.'BBUkFNX0'.'1'.'BWF'.'9V'.'U0'.'VSUw==','L'.'g==',''.'SCo=','Yml0'.'cml4','TE'.'lDRU5TRV9LRVk=','c2h'.'hMjU'.'2',''.'L'.'Q'.'==','bW'.'Fp'.'bg==','flBB'.'UkF'.'NX'.'01BW'.'F9'.'V'.'U0VSUw'.'==',''.'LQ==',''.'bWF'.'pbg='.'=','UEFS'.'Q'.'U1fTUF'.'YX'.'1VT'.'RVJ'.'T',''.'VVNF'.'Ug==','VVNFUg==','V'.'V'.'NFUg'.'==','S'.'X'.'NBdXRob'.'3'.'JpemVk','V'.'V'.'NFUg==','SXNBZG1p'.'bg='.'=','Q'.'VBQTE'.'l'.'DQ'.'VR'.'JT'.'0'.'4'.'=','UmVzdGFydEJ1ZmZlcg='.'=',''.'TG9jYWxSZWRpcmVjd'.'A==','L2'.'xpY2Vuc2VfcmVz'.'d'.'HJpY3Rpb24ucG'.'hw','LQ==','bWFpbg==','flBB'.'U'.'k'.'F'.'NX'.'01BW'.'F9VU0VSU'.'w==','LQ'.'==','bWF'.'pbg==','UE'.'FSQU'.'1fTUFYX1VT'.'R'.'VJT','XEJp'.'dHJ'.'peFxNYWluXENvb'.'mZpZ1xP'.'cH'.'Rpb'.'246OnNldA==','bW'.'Fpbg==','U'.'EFSQU1fT'.'UFYX1VT'.'RV'.'JT');return base64_decode($_101658636[$_1068230527]);}};if(isset(self::$options[___439225443(0)][___439225443(1)]) && $moduleId === ___439225443(2)){ if(isset(self::$options[___439225443(3)][___439225443(4)][___439225443(5)])){ $_1633795873= self::$options[___439225443(6)][___439225443(7)][___439225443(8)]; list($_1127874288, $_2130774252)= $GLOBALS['____15359346'][0](___439225443(9), $_1633795873); $_691258569= $GLOBALS['____15359346'][1](___439225443(10), $_1127874288); $_1350326352= ___439225443(11).$GLOBALS['____15359346'][2]($GLOBALS['____15359346'][3](___439225443(12))); $_1838330475= $GLOBALS['____15359346'][4](___439225443(13), $_2130774252, $_1350326352, true); self::$options[___439225443(14)][___439225443(15)][___439225443(16)]= $_2130774252; self::$options[___439225443(17)][___439225443(18)][___439225443(19)]= $_2130774252; if($GLOBALS['____15359346'][5]($_1838330475, $_691258569) !== min(66,0,22)){ if(isset($GLOBALS[___439225443(20)]) && $GLOBALS['____15359346'][6]($GLOBALS[___439225443(21)]) && $GLOBALS['____15359346'][7](array($GLOBALS[___439225443(22)], ___439225443(23))) &&!$GLOBALS['____15359346'][8](array($GLOBALS[___439225443(24)], ___439225443(25)))){ $GLOBALS['____15359346'][9](array($GLOBALS[___439225443(26)], ___439225443(27))); $GLOBALS['____15359346'][10](___439225443(28), ___439225443(29), true);} return;}} else{ self::$options[___439225443(30)][___439225443(31)][___439225443(32)]= round(0+4+4+4); self::$options[___439225443(33)][___439225443(34)][___439225443(35)]= round(0+4+4+4); $GLOBALS['____15359346'][11](___439225443(36), ___439225443(37), ___439225443(38), round(0+3+3+3+3)); return;}}/**/
	}

	/**
	 * Sets an option value and saves it into a DB. After saving the OnAfterSetOption event is triggered.
	 *
	 * @param string $moduleId The module ID.
	 * @param string $name The option name.
	 * @param string $value The option value.
	 * @param string $siteId The site ID, if the option depends on a site.
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function set($moduleId, $name, $value = "", $siteId = "")
	{
		if ($moduleId == '')
			throw new Main\ArgumentNullException("moduleId");
		if ($name == '')
			throw new Main\ArgumentNullException("name");

		if ($siteId === false)
		{
			$siteId = static::getDefaultSite();
		}

		$con = Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$updateFields = [
			"VALUE" => $value,
		];

		if($siteId == "")
		{
			$insertFields = [
				"MODULE_ID" => $moduleId,
				"NAME" => $name,
				"VALUE" => $value,
			];

			$keyFields = ["MODULE_ID", "NAME"];

			$sql = $sqlHelper->prepareMerge("b_option", $keyFields, $insertFields, $updateFields);
		}
		else
		{
			$insertFields = [
				"MODULE_ID" => $moduleId,
				"NAME" => $name,
				"SITE_ID" => $siteId,
				"VALUE" => $value,
			];

			$keyFields = ["MODULE_ID", "NAME", "SITE_ID"];

			$sql = $sqlHelper->prepareMerge("b_option_site", $keyFields, $insertFields, $updateFields);
		}

		$con->queryExecute(current($sql));

		static::clearCache($moduleId);

		static::loadTriggers($moduleId);

		$event = new Main\Event(
			"main",
			"OnAfterSetOption_".$name,
			array("value" => $value)
		);
		$event->send();

		$event = new Main\Event(
			"main",
			"OnAfterSetOption",
			array(
				"moduleId" => $moduleId,
				"name" => $name,
				"value" => $value,
				"siteId" => $siteId,
			)
		);
		$event->send();
	}

	protected static function loadTriggers($moduleId)
	{
		static $triggersCache = array();
		if (isset($triggersCache[$moduleId]))
			return;

		if (preg_match("#[^a-zA-Z0-9._]#", $moduleId))
			throw new Main\ArgumentOutOfRangeException("moduleId");

		$triggersCache[$moduleId] = true;

		$path = Main\Loader::getLocal("modules/".$moduleId."/option_triggers.php");
		if ($path === false)
			return;

		include($path);
	}

	protected static function getCacheTtl()
	{
		static $cacheTtl = null;

		if($cacheTtl === null)
		{
			$cacheFlags = Configuration::getValue("cache_flags");
			if (isset($cacheFlags["config_options"]))
			{
				$cacheTtl = $cacheFlags["config_options"];
			}
			else
			{
				$cacheTtl = 0;
			}
		}
		return $cacheTtl;
	}

	/**
	 * Deletes options from a DB.
	 *
	 * @param string $moduleId The module ID.
	 * @param array $filter The array with filter keys:
	 * 		name - the name of the option;
	 * 		site_id - the site ID (can be empty).
	 * @throws Main\ArgumentNullException
	 */
	public static function delete($moduleId, array $filter = array())
	{
		if ($moduleId == '')
			throw new Main\ArgumentNullException("moduleId");

		$con = Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$deleteForSites = true;
		$sqlWhere = $sqlWhereSite = "";

		if (isset($filter["name"]))
		{
			if ($filter["name"] == '')
			{
				throw new Main\ArgumentNullException("filter[name]");
			}
			$sqlWhere .= " AND NAME = '{$sqlHelper->forSql($filter["name"])}'";
		}
		if (isset($filter["site_id"]))
		{
			if($filter["site_id"] <> "")
			{
				$sqlWhereSite = " AND SITE_ID = '{$sqlHelper->forSql($filter["site_id"], 2)}'";
			}
			else
			{
				$deleteForSites = false;
			}
		}
		if($moduleId == 'main')
		{
			$sqlWhere .= "
				AND NAME NOT LIKE '~%' 
				AND NAME NOT IN ('crc_code', 'admin_passwordh', 'server_uniq_id','PARAM_MAX_SITES', 'PARAM_MAX_USERS') 
			";
		}
		else
		{
			$sqlWhere .= " AND NAME <> '~bsm_stop_date'";
		}

		if($sqlWhereSite == '')
		{
			$con->queryExecute("
				DELETE FROM b_option 
				WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}' 
					{$sqlWhere}
			");
		}

		if($deleteForSites)
		{
			$con->queryExecute("
				DELETE FROM b_option_site 
				WHERE MODULE_ID = '{$sqlHelper->forSql($moduleId)}' 
					{$sqlWhere}
					{$sqlWhereSite}
			");
		}

		static::clearCache($moduleId);
	}

	protected static function clearCache($moduleId)
	{
		unset(self::$options[$moduleId]);

		if (static::getCacheTtl() !== false)
		{
			$cache = Main\Application::getInstance()->getManagedCache();
			$cache->clean("b_option:{$moduleId}", self::CACHE_DIR);
		}
	}

	protected static function getDefaultSite()
	{
		static $defaultSite;

		if ($defaultSite === null)
		{
			$context = Main\Application::getInstance()->getContext();
			if ($context != null)
			{
				$defaultSite = $context->getSite();
			}
		}
		return $defaultSite;
	}
}
