<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/location.php");

use Bitrix\Sale\Location;

class CSaleLocation extends CAllSaleLocation
{
	public static function GetList($arOrder = array("SORT"=>"ASC", "COUNTRY_NAME_LANG"=>"ASC", "CITY_NAME_LANG"=>"ASC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (is_string($arGroupBy) && strlen($arGroupBy) == 2)
		{
			$arFilter["LID"] = $arGroupBy;
			$arGroupBy = false;

			$arSelectFields = array("ID", "COUNTRY_ID", "REGION_ID", "CITY_ID", "SORT", "COUNTRY_NAME_ORIG", "COUNTRY_SHORT_NAME", "COUNTRY_NAME_LANG", "CITY_NAME_ORIG", "CITY_SHORT_NAME", "CITY_NAME_LANG", "REGION_NAME_ORIG", "REGION_SHORT_NAME", "REGION_NAME_LANG", "COUNTRY_NAME", "CITY_NAME", "REGION_NAME", "LOC_DEFAULT");
		}

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "COUNTRY_ID", "REGION_ID", "CITY_ID", "SORT", "COUNTRY_NAME_ORIG", "COUNTRY_SHORT_NAME", "REGION_NAME_ORIG", "CITY_NAME_ORIG", "REGION_SHORT_NAME", "CITY_SHORT_NAME", "COUNTRY_LID", "COUNTRY_NAME", "REGION_LID", "CITY_LID", "REGION_NAME", "CITY_NAME", "LOC_DEFAULT");

		if(!is_array($arOrder))
			$arOrder = array();

		foreach ($arOrder as $key => $value)
		{
			if (!in_array($key, $arSelectFields)) $arSelectFields[] = $key;
		}

		$arFilter = self::getFilterForGetList($arFilter);
		$arFields = self::getFieldMapForGetList($arFilter);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_location L ".
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
			"SELECT /*TOP*/ ".$arSqls["SELECT"]." ".
			"FROM b_sale_location L ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sale_location L ".
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

			$strSql = str_replace("/*TOP*/", "", $strSql);
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"])>0)
				{ $strSql = str_replace("/*TOP*/", "TOP ".IntVal($arNavStartParams["nTopCount"]), $strSql); }

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	public static function GetByID($ID, $strLang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
			return parent::GetByID($ID, $strLang);

		global $DB;

		$ID = IntVal($ID);
		/*$strSql = "
			SELECT
				L.ID,
				L.COUNTRY_ID,
				L.CITY_ID,
				L.SORT,
				LC.NAME				COUNTRY_NAME_ORIG,
				LC.SHORT_NAME		COUNTRY_SHORT_NAME,
				LCL.NAME			COUNTRY_NAME_LANG,
				LG.NAME				CITY_NAME_ORIG,
				LG.SHORT_NAME		CITY_SHORT_NAME,
				LGL.NAME			CITY_NAME_LANG,
				case when LCL.ID is null then LC.NAME else LCL.NAME end		COUNTRY_NAME,
				case when LGL.ID is null then LG.NAME else LGL.NAME end		CITY_NAME
			FROM
				b_sale_location L
			INNER JOIN b_sale_location_country LC ON (L.COUNTRY_ID = LC.ID)
			LEFT JOIN b_sale_location_city LG ON (LG.ID = L.CITY_ID)
			LEFT JOIN b_sale_location_country_lang LCL ON (LCL.COUNTRY_ID = LC.ID and LCL.LID = '".$DB->ForSql($strLang, 2)."')
			LEFT JOIN b_sale_location_city_lang LGL ON (LGL.CITY_ID = LG.ID and LGL.LID = '".$DB->ForSql($strLang, 2)."')
			WHERE
				L.ID = $ID
			";*/
		$strSql = "
			SELECT
				L.ID,
				L.COUNTRY_ID,
				L.CITY_ID,
				L.SORT,
				LC.NAME				COUNTRY_NAME_ORIG,
				LC.SHORT_NAME		COUNTRY_SHORT_NAME,
				LCL.NAME			COUNTRY_NAME_LANG,
				LG.NAME				CITY_NAME_ORIG,
				LG.SHORT_NAME		CITY_SHORT_NAME,
				LGL.NAME			CITY_NAME_LANG,
				L.REGION_ID,
				LR.NAME REGION_NAME_ORIG,
				LR.SHORT_NAME REGION_SHORT_NAME,
				LRL.NAME REGION_NAME_LANG,
				case when LCL.ID is null then LC.NAME else LCL.NAME end		COUNTRY_NAME,
				case when LGL.ID is null then LG.NAME else LGL.NAME end		CITY_NAME,
				case when LRL.ID is null then LR.NAME else LRL.NAME end		REGION_NAME
			FROM
				b_sale_location L
			LEFT JOIN b_sale_location_country LC ON (L.COUNTRY_ID = LC.ID)
			LEFT JOIN b_sale_location_city LG ON (LG.ID = L.CITY_ID)
			LEFT JOIN b_sale_location_country_lang LCL ON (LCL.COUNTRY_ID = LC.ID and LCL.LID = '".$DB->ForSql($strLang, 2)."')
			LEFT JOIN b_sale_location_city_lang LGL ON (LGL.CITY_ID = LG.ID and LGL.LID = '".$DB->ForSql($strLang, 2)."')
			LEFT JOIN b_sale_location_region LR ON (L.REGION_ID = LR.ID)
			LEFT JOIN b_sale_location_region_lang LRL ON (LRL.REGION_ID = LR.ID and LRL.LID = '".$DB->ForSql($strLang, 2)."')
			WHERE
				L.ID = $ID
			";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	public static function GetCountryList($arOrder = Array("NAME_LANG"=>"ASC"), $arFilter=Array(), $strLang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
			return self::GetLocationTypeList('COUNTRY', $arOrder, $arFilter, $strLang);

		global $DB;
		$arSqlSearch = Array();

		if(!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$countFilterKey = count($filter_keys);
		for($i=0; $i < $countFilterKey; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if (strlen($val)<=0) continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch(ToUpper($key))
			{
			case "ID":
				$arSqlSearch[] = "C.ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
				break;
			case "NAME":
				$arSqlSearch[] = "C.NAME ".($bInvert?"<>":"=")." '".$val."' ";
				break;
			}
		}

		$strSqlSearch = "";
		$countSqlSearch = count($arSqlSearch);
		for($i=0; $i < $countSqlSearch; $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql = "
			SELECT
				C.ID,
				C.SHORT_NAME,
				C.NAME			NAME_ORIG,
				CL.NAME			NAME,
				case when CL.ID is null then C.NAME else CL.NAME end	NAME_LANG
			FROM
				b_sale_location_country C
			LEFT JOIN b_sale_location_country_lang CL ON (CL.COUNTRY_ID=C.ID and CL.LID='".$DB->ForSql($strLang, 2)."')
			WHERE
				1=1
			$strSqlSearch
			";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = ToUpper($by);
			$order = ToUpper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " C.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " C.NAME ".$order." ";
			elseif ($by == "SHORT_NAME") $arSqlOrder[] = " C.SHORT_NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " CL.NAME ".$order." ";
				$by = "NAME_LANG";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$countSqlOrder = count($arSqlOrder);
		for ($i=0; $i < $countSqlOrder; $i++)
		{
			if ($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ", ";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	/**
	* The function select all region
	* @param array $arOrder sorting an array of results
	* @param array $arFilter filtered an array of results
	* @param string $strLang language regions of the sample
	* @return true false
	*/
	public static function GetRegionList($arOrder = Array("NAME_LANG"=>"ASC"), $arFilter=Array(), $strLang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
			return self::GetLocationTypeList('REGION', $arOrder, $arFilter, $strLang);

		global $DB;
		$arSqlSearch = Array();

		if(!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$countFilterKey = count($filter_keys);
		for($i=0; $i < $countFilterKey; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if (strlen($val)<=0) continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch(ToUpper($key))
			{
				case "ID":
					$arSqlSearch[] = "C.ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
					break;
				case "NAME":
					$arSqlSearch[] = "C.NAME ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "COUNTRY_ID":
					$arSqlSearch[] = "SL.COUNTRY_ID ".($bInvert?"<>":"=")." '".$val."' ";
					break;
			}
		}

		$strSqlSearch = "";
		$countSqlSearch = count($arSqlSearch);
		for($i=0; $i < $countSqlSearch; $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql = "
			SELECT
				C.ID,
				C.SHORT_NAME,
				C.NAME			NAME_ORIG,
				CL.NAME			NAME,
				case when CL.ID is null then C.NAME else CL.NAME end	NAME_LANG
			FROM
				b_sale_location_region C
			LEFT JOIN b_sale_location_region_lang CL ON (CL.REGION_ID=C.ID and CL.LID='".$DB->ForSql($strLang, 2)."') ".
			" LEFT JOIN b_sale_location SL ON (SL.REGION_ID = C.ID AND (SL.CITY_ID = 0 OR SL.CITY_ID IS NULL)) ".
			" WHERE
				1=1
			$strSqlSearch
			";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = ToUpper($by);
			$order = ToUpper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " C.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " C.NAME ".$order." ";
			elseif ($by == "SHORT_NAME") $arSqlOrder[] = " C.SHORT_NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " CL.NAME ".$order." ";
				$by = "NAME_LANG";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$countSqlOrder = count($arSqlOrder);
		for ($i=0; $i < $countSqlOrder; $i++)
		{
			if ($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ", ";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	/**
	 * The function select all cities
	 * @param array $arOrder sorting an array of results
	 * @param array $arFilter filtered an array of results
	 * @param string $strLang language cities of the sample
	 * @return true false
	 */
	public static function GetCityList($arOrder = Array("NAME_LANG"=>"ASC"), $arFilter=Array(), $strLang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
			return self::GetLocationTypeList('CITY', $arOrder, $arFilter, $strLang);

		global $DB;
		$arSqlSearch = Array();

		if(!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$countFilterKey = count($filter_keys);
		for($i=0; $i < $countFilterKey; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if (strlen($val)<=0) continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch(ToUpper($key))
			{
				case "ID":
					$arSqlSearch[] = "C.ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
					break;
				case "NAME":
					$arSqlSearch[] = "C.NAME ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "REGION_ID":
					$arSqlSearch[] = "SL.REGION_ID ".($bInvert?"<>":"=")." '".$val."' ";
					break;
			}
		}

		$strSqlSearch = "";
		$countSqlSearch = count($arSqlSearch);
		for($i=0; $i < $countSqlSearch; $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql = "
			SELECT
				C.ID,
				C.SHORT_NAME,
				C.NAME			NAME_ORIG,
				CL.NAME			NAME,
				case when CL.ID is null then C.NAME else CL.NAME end	NAME_LANG
			FROM
				b_sale_location_city C
			LEFT JOIN b_sale_location_city_lang CL ON (CL.CITY_ID=C.ID and CL.LID='".$DB->ForSql($strLang, 2)."') ".
			" LEFT JOIN b_sale_location SL ON (SL.CITY_ID = C.ID ) ".
			" WHERE
				1=1
			$strSqlSearch
			";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = ToUpper($by);
			$order = ToUpper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " C.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " C.NAME ".$order." ";
			elseif ($by == "SHORT_NAME") $arSqlOrder[] = " C.SHORT_NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " CL.NAME ".$order." ";
				$by = "NAME_LANG";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$countSqlOrder = count($arSqlOrder);
		for ($i=0; $i < $countSqlOrder; $i++)
		{
			if ($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ", ";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	public static function AddCountry($arFields)
	{
		global $DB;

		if (!CSaleLocation::CountryCheckFields("ADD", $arFields))
			return false;

		if(self::isLocationProMigrated())
		{
			return self::AddLocationUnattached('COUNTRY', $arFields);
		}

		foreach (GetModuleEvents('sale', 'OnBeforeCountryAdd', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
				return false;
		}

		$arInsert = $DB->PrepareInsert("b_sale_location_country", $arFields);

		$IDENTITY_INSERT = '';
		$arX = explode(",", $arInsert[0]);
		if (is_array($arX))
		{
			TrimArr($arX, true); $arX = array_flip($arX);
		}
		if (is_array($arX) && is_set($arX, "ID"))
		{
			$IDENTITY_INSERT = "ON";
			$DB->Query("SET IDENTITY_INSERT b_sale_location_country ON", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$strSql =
			"INSERT INTO b_sale_location_country(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($IDENTITY_INSERT=="ON")
		{
			$IDENTITY_INSERT="OFF";
			$DB->Query("SET IDENTITY_INSERT b_sale_location_country OFF", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$ID = IntVal($DB->LastID());

		$b = "sort";
		$o="asc";
		$db_lang = CLangAdmin::GetList($b, $o, array("ACTIVE" => "Y"));
		while ($arLang = $db_lang->Fetch())
		{
			if ($arFields[$arLang["LID"]])
			{
				$arInsert = $DB->PrepareInsert("b_sale_location_country_lang", $arFields[$arLang["LID"]]);

				$arX = explode(",", $arInsert[0]);
				if (is_array($arX))
				{
					TrimArr($arX, true); $arX = array_flip($arX);
				}
				if (is_array($arX) && is_set($arX, "ID"))
				{
					$IDENTITY_INSERT = "ON";
					$DB->Query("SET IDENTITY_INSERT b_sale_location_country_lang ON", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}

				$strSql =
					"INSERT INTO b_sale_location_country_lang(COUNTRY_ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

				if ($IDENTITY_INSERT=="ON")
				{
					$IDENTITY_INSERT="OFF";
					$DB->Query("SET IDENTITY_INSERT b_sale_location_country_lang OFF", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}
			}
		}

		foreach (GetModuleEvents('sale', 'OnCountryAdd', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	public static function AddCity($arFields)
	{
		global $DB;

		if (!CSaleLocation::CityCheckFields("ADD", $arFields))
			return false;

		if(self::isLocationProMigrated())
		{
			return self::AddLocationUnattached('CITY', $arFields);
		}

		foreach (GetModuleEvents('sale', 'OnBeforeCityAdd', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
				return false;
		}

		$arInsert = $DB->PrepareInsert("b_sale_location_city", $arFields);

		$IDENTITY_INSERT = '';
		$arX = explode(",", $arInsert[0]);
		if (is_array($arX))
		{
			TrimArr($arX, true); $arX = array_flip($arX);
		}
		if (is_array($arX) && is_set($arX, "ID"))
		{
			$IDENTITY_INSERT = "ON";
			$DB->Query("SET IDENTITY_INSERT b_sale_location_city ON", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$strSql =
			"INSERT INTO b_sale_location_city(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($IDENTITY_INSERT=="ON")
		{
			$IDENTITY_INSERT="OFF";
			$DB->Query("SET IDENTITY_INSERT b_sale_location_city OFF", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$ID = IntVal($DB->LastID());

		$b = "sort";
		$o = "asc";
		$db_lang = CLangAdmin::GetList($b, $o, array("ACTIVE" => "Y"));
		while ($arLang = $db_lang->Fetch())
		{
			if ($arFields[$arLang["LID"]])
			{
				$arInsert = $DB->PrepareInsert("b_sale_location_city_lang", $arFields[$arLang["LID"]]);

				$arX = explode(",", $arInsert[0]);
				if (is_array($arX))
				{
					TrimArr($arX, true); $arX = array_flip($arX);
				}
				if (is_array($arX) && is_set($arX, "ID"))
				{
					$IDENTITY_INSERT = "ON";
					$DB->Query("SET IDENTITY_INSERT b_sale_location_city_lang ON", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}

				$strSql =
					"INSERT INTO b_sale_location_city_lang(CITY_ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

				if ($IDENTITY_INSERT=="ON")
				{
					$IDENTITY_INSERT="OFF";
					$DB->Query("SET IDENTITY_INSERT b_sale_location_city_lang OFF", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}
			}
		}

		foreach (GetModuleEvents('sale', 'OnCityAdd', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	/**
	* The function adds a new region
	* @param array $arFields array with parameters region
	* @return int $ID code region
	*/
	public static function AddRegion($arFields)
	{
		global $DB;

		if (!CSaleLocation::CityCheckFields("ADD", $arFields))
			return false;

		if(self::isLocationProMigrated())
		{
			return self::AddLocationUnattached('REGION', $arFields);
		}

		foreach (GetModuleEvents('sale', 'OnBeforeRegionAdd', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($arFields))===false)
				return false;
		}

		$arInsert = $DB->PrepareInsert("b_sale_location_region", $arFields);

		$IDENTITY_INSERT = '';
		$arX = explode(",", $arInsert[0]);
		if (is_array($arX))
		{
			TrimArr($arX, true); $arX = array_flip($arX);
		}
		if (is_array($arX) && is_set($arX, "ID"))
		{
			$IDENTITY_INSERT = "ON";
			$DB->Query("SET IDENTITY_INSERT b_sale_location_region ON", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$strSql =
			"INSERT INTO b_sale_location_region(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($IDENTITY_INSERT=="ON")
		{
			$IDENTITY_INSERT="OFF";
			$DB->Query("SET IDENTITY_INSERT b_sale_location_region OFF", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$ID = IntVal($DB->LastID());

		$b = "sort";
		$o = "asc";
		$db_lang = CLangAdmin::GetList($b, $o, array("ACTIVE" => "Y"));
		while ($arLang = $db_lang->Fetch())
		{
			if ($arFields[$arLang["LID"]])
			{
				$arInsert = $DB->PrepareInsert("b_sale_location_region_lang", $arFields[$arLang["LID"]]);

				$arX = explode(",", $arInsert[0]);
				if (is_array($arX))
				{
					TrimArr($arX, true); $arX = array_flip($arX);
				}
				if (is_array($arX) && is_set($arX, "ID"))
				{
					$IDENTITY_INSERT = "ON";
					$DB->Query("SET IDENTITY_INSERT b_sale_location_region_lang ON", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}

				$strSql =
					"INSERT INTO b_sale_location_region_lang(REGION_ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

				if ($IDENTITY_INSERT=="ON")
				{
					$IDENTITY_INSERT="OFF";
					$DB->Query("SET IDENTITY_INSERT b_sale_location_region_lang OFF", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}
			}
		}

		foreach (GetModuleEvents('sale', 'OnRegionAdd', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	public static function AddLocation($arFields)
	{
		global $DB;

		if (!CSaleLocation::LocationCheckFields("ADD", $arFields))
			return false;

		if(self::isLocationProMigrated())
		{
			return self::RebindLocationTriplet($arFields);
		}

		// make IX_B_SALE_LOC_CODE feel happy
		$arFields['CODE'] = 'randstr'.rand(999, 99999);

		foreach (GetModuleEvents('sale', 'OnBeforeLocationAdd', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
				return false;
		}

		$arInsert = $DB->PrepareInsert("b_sale_location", $arFields);

		$IDENTITY_INSERT = '';
		$arX = explode(",", $arInsert[0]);
		if (is_array($arX))
		{
			TrimArr($arX, true); $arX = array_flip($arX);
		}
		if (is_array($arX) && is_set($arX, "ID"))
		{
			$IDENTITY_INSERT = "ON";
			$DB->Query("SET IDENTITY_INSERT b_sale_location ON", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$strSql =
			"INSERT INTO b_sale_location(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($IDENTITY_INSERT=="ON")
		{
			$IDENTITY_INSERT="OFF";
			$DB->Query("SET IDENTITY_INSERT b_sale_location OFF", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$ID = IntVal($DB->LastID());

		// make IX_B_SALE_LOC_CODE feel happy
		Location\LocationTable::update($ID, array('CODE' => $ID));

		foreach (GetModuleEvents('sale', 'OnLocationAdd', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}
}