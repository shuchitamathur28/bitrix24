<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/catalog.php");

class CCatalog extends CAllCatalog
{
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

		// For old-style execution
		if (!is_array($arOrder) && !is_array($arFilter))
		{
			$arOrder = (string)$arOrder;
			$arFilter = (string)$arFilter;
			$arOrder = ($arOrder != '' && $arFilter != '' ? array($arOrder => $arFilter) : array());
			$arFilter = (is_array($arGroupBy) ? $arGroupBy : array());
			$arGroupBy = false;
		}

		$arFields = array(
			"IBLOCK_ID" => array("FIELD" => "CI.IBLOCK_ID", "TYPE" => "int"),
			"YANDEX_EXPORT" => array("FIELD" => "CI.YANDEX_EXPORT", "TYPE" => "char"),
			"SUBSCRIPTION" => array("FIELD" => "CI.SUBSCRIPTION", "TYPE" => "char"),
			"VAT_ID" => array("FIELD" => "CI.VAT_ID", "TYPE" => "int"),
			"PRODUCT_IBLOCK_ID" => array("FIELD" => "CI.PRODUCT_IBLOCK_ID", "TYPE" => "int"),
			"SKU_PROPERTY_ID" => array("FIELD" => "CI.SKU_PROPERTY_ID", "TYPE" => "int"),
			"OFFERS_PROPERTY_ID" => array("FIELD" => "OFFERS.SKU_PROPERTY_ID", "TYPE" => "int", "FROM" => "LEFT JOIN B_CATALOG_IBLOCK OFFERS ON (CI.IBLOCK_ID = OFFERS.PRODUCT_IBLOCK_ID)"),
			"OFFERS_IBLOCK_ID" => array("FIELD" => "OFFERS.IBLOCK_ID", "TYPE" => "int", "FROM" => "LEFT JOIN B_CATALOG_IBLOCK OFFERS ON (CI.IBLOCK_ID = OFFERS.PRODUCT_IBLOCK_ID)"),
			"ID" => array("FIELD" => "I.ID", "TYPE" => "int", "FROM" => "INNER JOIN B_IBLOCK I ON (CI.IBLOCK_ID = I.ID)"),
			"IBLOCK_TYPE_ID" => array("FIELD" => "I.IBLOCK_TYPE_ID", "TYPE" => "string", "FROM" => "INNER JOIN B_IBLOCK I ON (CI.IBLOCK_ID = I.ID)"),
			"IBLOCK_ACTIVE" => array("FIELD" => "I.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN B_IBLOCK I ON (CI.IBLOCK_ID = I.ID)"),
			"LID" => array("FIELD" => "I.LID", "TYPE" => "string", "FROM" => "INNER JOIN B_IBLOCK I ON (CI.IBLOCK_ID = I.ID)"),
			"NAME" => array("FIELD" => "I.NAME", "TYPE" => "string", "FROM" => "INNER JOIN B_IBLOCK I ON (CI.IBLOCK_ID = I.ID)")
		);

		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM B_CATALOG_IBLOCK CI ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$arRes = $dbRes->Fetch();
			return (is_array($arRes) ? $arRes['CNT'] : false);
		}

		$strSql = "SELECT /*TOP*/ ".$arSqls["SELECT"]." FROM B_CATALOG_IBLOCK CI ".$arSqls["FROM"];
		if (!empty($arSqls["WHERE"]))
			$strSql .= " WHERE ".$arSqls["WHERE"];
		if (!empty($arSqls["GROUPBY"]))
			$strSql .= " GROUP BY ".$arSqls["GROUPBY"];
		if (!empty($arSqls["ORDERBY"]))
			$strSql .= " ORDER BY ".$arSqls["ORDERBY"];

		$intTopCount = 0;
		$boolNavStartParams = (!empty($arNavStartParams) && is_array($arNavStartParams));
		if ($boolNavStartParams && isset($arNavStartParams['nTopCount']))
			$intTopCount = (int)$arNavStartParams['nTopCount'];

		if ($boolNavStartParams && $intTopCount <= 0)
		{
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM B_CATALOG_IBLOCK CI ".$arSqls["FROM"];
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
			if ($boolNavStartParams && $intTopCount > 0)
				$strSql = str_replace("/*TOP*/", "TOP ".$intTopCount, $strSql);

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
}