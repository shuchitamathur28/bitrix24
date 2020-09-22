<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/vat.php");

class CCatalogVat extends CAllCatalogVat
{
	public static function Add($arFields)
	{
		global $DB;

		if (!CCatalogVat::CheckFields('ADD', $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("B_CATALOG_VAT", $arFields);

		$strSql = "INSERT INTO B_CATALOG_VAT(".$arInsert[0].") VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ID = intval($DB->LastID());

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if (0 >= $ID)
			return false;

		if (!CCatalogVat::CheckFields('UPDATE', $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("B_CATALOG_VAT", $arFields);
		if (!empty($strUpdate))
		{
			$strSql = "UPDATE B_CATALOG_VAT SET ".$strUpdate." WHERE ID = ".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $ID;
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		if (0 >= $ID)
			return false;
		$DB->Query("DELETE FROM B_CATALOG_VAT WHERE ID=".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return true;
	}

	public static function GetListEx($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (empty($arSelectFields))
			$arSelectFields = array('ID', 'TIMESTAMP_X', 'ACTIVE', 'C_SORT', 'NAME', 'RATE');

		$arFields = array(
			'ID' => array("FIELD" => "CV.ID", "TYPE" => "int"),
			'TIMESTAMP_X' => array("FIELD" => "CV.TIMESTAMP_X", "TYPE" => "datetime"),
			'ACTIVE' => array("FIELD" => "CV.ACTIVE", "TYPE" => "char"),
			'C_SORT' => array("FIELD" => "CV.C_SORT", "TYPE" => "int"),
			'SORT' => array("FIELD" => "CV.C_SORT", "TYPE" => "int"),
			'NAME' => array("FIELD" => "CV.NAME", "TYPE" => "string"),
			'RATE' => array("FIELD" => "CV.RATE", "TYPE" => "double"),
		);

		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM B_CATALOG_VAT CV ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql = "SELECT /*TOP*/ ".$arSqls["SELECT"]." FROM B_CATALOG_VAT CV ".$arSqls["FROM"];
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
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM B_CATALOG_VAT CV ".$arSqls["FROM"];
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
				$strSql = str_replace("/*TOP*/", "TOP ".$intTopCount, $strSql);
			}
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
}