<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/store_product.php");

class CCatalogStoreProduct
	extends CCatalogStoreProductAll
{
	public static function Add($arFields)
	{
		global $DB;

		foreach(GetModuleEvents("catalog", "OnBeforeStoreProductAdd", true) as $arEvent)
			if(ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				return false;

		if (!self::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("B_CATALOG_STORE_PRODUCT", $arFields);
		$ID = (int)$DB->NextID("SQ_B_CATALOG_STORE_PRODUCT");

		$strSql = "INSERT INTO b_catalog_store_product(ID, ".$arInsert[0].") VALUES(".$ID.", ".$arInsert[1].")";
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if(!$res)
			return false;

		foreach(GetModuleEvents("catalog", "OnStoreProductAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	/**
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param bool|array $arGroupBy
	 * @param bool|array $arNavStartParams
	 * @param array $arSelectFields
	 * @return bool|CDBResult
	 */
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		$arFields = array(
			"ID" => array("FIELD" => "CP.ID", "TYPE" => "int"),
			"PRODUCT_ID" => array("FIELD" => "CP.PRODUCT_ID", "TYPE" => "int"),
			"STORE_ID" => array("FIELD" => "CP.STORE_ID", "TYPE" => "int"),
			"AMOUNT" => array("FIELD" => "CP.AMOUNT", "TYPE" => "double"),
			"STORE_NAME" => array("FIELD" => "CS.TITLE", "TYPE" => "string", "FROM" => "RIGHT JOIN B_CATALOG_STORE CS ON (CS.ID = CP.STORE_ID)"),
			"STORE_ADDR" => array("FIELD" => "CS.ADDRESS", "TYPE" => "string", "FROM" => "RIGHT JOIN B_CATALOG_STORE CS ON (CS.ID = CP.STORE_ID)"),
			"STORE_DESCR" => array("FIELD" => "CS.DESCRIPTION", "TYPE" => "string", "FROM" => "RIGHT JOIN B_CATALOG_STORE CS ON (CS.ID = CP.STORE_ID)"),
			"STORE_GPS_N" => array("FIELD" => "CS.GPS_N", "TYPE" => "string", "FROM" => "RIGHT JOIN B_CATALOG_STORE CS ON (CS.ID = CP.STORE_ID)"),
			"STORE_GPS_S" => array("FIELD" => "CS.GPS_S", "TYPE" => "string", "FROM" => "RIGHT JOIN B_CATALOG_STORE CS ON (CS.ID = CP.STORE_ID)"),
			"STORE_IMAGE" => array("FIELD" => "CS.IMAGE_ID", "TYPE" => "int", "FROM" => "RIGHT JOIN B_CATALOG_STORE CS ON (CS.ID = CP.STORE_ID)"),
			"STORE_LOCATION" => array("FIELD" => "CS.LOCATION_ID", "TYPE" => "int", "FROM" => "RIGHT JOIN B_CATALOG_STORE CS ON (CS.ID = CP.STORE_ID)"),
			"STORE_PHONE" => array("FIELD" => "CS.PHONE", "TYPE" => "string", "FROM" => "RIGHT JOIN B_CATALOG_STORE CS ON (CS.ID = CP.STORE_ID)")
		);

		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM B_CATALOG_STORE_PRODUCT CP ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql = "SELECT ".$arSqls["SELECT"]." FROM B_CATALOG_STORE_PRODUCT CP ".$arSqls["FROM"];
		if (!empty($arSqls["WHERE"]))
			$strSql .= " WHERE ".$arSqls["WHERE"];
		if (!empty($arSqls["GROUPBY"]))
			$strSql .= " GROUP BY ".$arSqls["GROUPBY"];
		if (!empty($arSqls["ORDERBY"]))
			$strSql .= " ORDER BY ".$arSqls["ORDERBY"];

		$intTopCount = 0;
		$boolNavStartParams = (!empty($arNavStartParams) && is_array($arNavStartParams));
		if ($boolNavStartParams && array_key_exists('nTopCount', $arNavStartParams))
		{
			$intTopCount = intval($arNavStartParams["nTopCount"]);
		}
		if ($boolNavStartParams && 0 >= $intTopCount)
		{
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM B_CATALOG_STORE_PRODUCT CP ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql_tmp .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql_tmp .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($arRes = $dbRes->Fetch())
				$cnt = $arRes["CNT"];

			$dbRes = new CDBResult();

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if ($boolNavStartParams && 0 < $intTopCount)
			{
				$strSql = "SELECT * FROM (".$strSql.") WHERE ROWNUM<=".$intTopCount;
			}
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
}