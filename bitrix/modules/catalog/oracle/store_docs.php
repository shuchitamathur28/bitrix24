<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/store_docs.php");

class CCatalogDocs
	extends CAllCatalogDocs
{
	public static function add($arFields)
	{
		global $DB;

		foreach(GetModuleEvents("catalog", "OnBeforeDocumentAdd", true) as $arEvent)
			if(ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				return false;

		if(array_key_exists('DATE_CREATE', $arFields))
			unset($arFields['DATE_CREATE']);
		if(array_key_exists('DATE_MODIFY', $arFields))
			unset($arFields['DATE_MODIFY']);

		$arFields['~DATE_MODIFY'] = $DB->GetNowFunction();
		$arFields['~DATE_CREATE'] = $DB->GetNowFunction();

		if (!self::checkFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("B_CATALOG_STORE_DOCS", $arFields);
		$ID = (int)$DB->NextID("SQ_B_CATALOG_STORE_DOCS");

		$strSql = "INSERT INTO B_CATALOG_STORE_DOCS(ID, ".$arInsert[0].") VALUES(".$ID.", ".$arInsert[1].")";
		if(!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;
		if(isset($arFields["ELEMENT"]))
		{
			foreach($arFields["ELEMENT"] as $arElement)
			{
				if(isset($arElement["ID"]))
					unset($arElement["ID"]);
				$arElement["DOC_ID"] = $ID;
				if(is_array($arElement))
					CCatalogStoreDocsElement::add($arElement);
			}
		}

		foreach(GetModuleEvents("catalog", "OnDocumentAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	public static function getList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (empty($arSelectFields))
			$arSelectFields = array("ID", "DOC_TYPE", "SITE_ID", "CONTRACTOR_ID", "CURRENCY", "STATUS", "DATE_DOCUMENT", "TOTAL", "DATE_STATUS", "COMMENTARY");

		$arFields = array(
			"ID" => array("FIELD" => "CD.ID", "TYPE" => "int"),
			"DOC_TYPE" => array("FIELD" => "CD.DOC_TYPE", "TYPE" => "char"),
			"SITE_ID" => array("FIELD" => "CD.SITE_ID", "TYPE" => "string"),
			"CURRENCY" => array("FIELD" => "CD.CURRENCY", "TYPE" => "string"),
			"CONTRACTOR_ID" => array("FIELD" => "CD.CONTRACTOR_ID", "TYPE" => "int"),
			"DATE_CREATE" => array("FIELD" => "CD.DATE_CREATE", "TYPE" => "datetime"),
			"DATE_MODIFY" => array("FIELD" => "CD.DATE_MODIFY", "TYPE" => "datetime"),
			"DATE_DOCUMENT" => array("FIELD" => "CD.DATE_DOCUMENT", "TYPE" => "datetime"),
			"DATE_STATUS" => array("FIELD" => "CD.DATE_STATUS", "TYPE" => "datetime"),
			"CREATED_BY" => array("FIELD" => "CD.CREATED_BY", "TYPE" => "int"),
			"MODIFIED_BY" => array("FIELD" => "CD.MODIFIED_BY", "TYPE" => "int"),
			"STATUS_BY" => array("FIELD" => "CD.STATUS_BY", "TYPE" => "int"),
			"STATUS" => array("FIELD" => "CD.STATUS", "TYPE" => "string"),
			"TOTAL" => array("FIELD" => "CD.TOTAL", "TYPE" => "double"),
			"COMMENTARY" => array("FIELD" => "CD.COMMENTARY", "TYPE" => "string"),

			"PRODUCTS_ID" => array("FIELD" => "DE.ID", "TYPE" => "int", "FROM" => "INNER JOIN B_CATALOG_DOCS_ELEMENT DE ON (CD.ID = DE.DOC_ID)"),
			"PRODUCTS_DOC_ID" => array("FIELD" => "DE.DOC_ID", "TYPE" => "int", "FROM" => "INNER JOIN B_CATALOG_DOCS_ELEMENT DE ON (CD.ID = DE.DOC_ID)"),
			"PRODUCTS_STORE_FROM" => array("FIELD" => "DE.STORE_FROM", "TYPE" => "int", "FROM" => "INNER JOIN B_CATALOG_DOCS_ELEMENT DE ON (CD.ID = DE.DOC_ID)"),
			"PRODUCTS_STORE_TO" => array("FIELD" => "DE.STORE_TO", "TYPE" => "int", "FROM" => "INNER JOIN B_CATALOG_DOCS_ELEMENT DE ON (CD.ID = DE.DOC_ID)"),
			"PRODUCTS_ELEMENT_ID" => array("FIELD" => "DE.ELEMENT_ID", "TYPE" => "int", "FROM" => "INNER JOIN B_CATALOG_DOCS_ELEMENT DE ON (CD.ID = DE.DOC_ID)"),
			"PRODUCTS_AMOUNT" => array("FIELD" => "DE.AMOUNT", "TYPE" => "double", "FROM" => "INNER JOIN B_CATALOG_DOCS_ELEMENT DE ON (CD.ID = DE.DOC_ID)"),
			"PRODUCTS_PURCHASING_PRICE" => array("FIELD" => "DE.PURCHASING_PRICE", "TYPE" => "double", "FROM" => "INNER JOIN B_CATALOG_DOCS_ELEMENT DE ON (CD.ID = DE.DOC_ID)"),
		);

		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if(empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM B_CATALOG_STORE_DOCS CD ".$arSqls["FROM"];
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

		$strSql = "SELECT ".$arSqls["SELECT"]." FROM B_CATALOG_STORE_DOCS CD ".$arSqls["FROM"];
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
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM b_catalog_store_docs CD ".$arSqls["FROM"];
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

	public static function synchronizeStockQuantity($storeId, $iblockId = 0)
	{
		global $DB;
		$storeId = (int)$storeId;
		if ($storeId <= 0)
			return false;

		$internalSql = 'select CP.QUANTITY + NVL(CP.QUANTITY_RESERVED, 0), CP.ID, '.$storeId.' ';
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			$internalSql .= 'from B_CATALOG_PRODUCT CP where 1 = 1';
		else
			$internalSql .= 'from B_CATALOG_PRODUCT CP inner join B_IBLOCK_ELEMENT IE on (CP.ID = IE.ID) where IE.IBLOCK_ID = '.$iblockId;

		return $DB->Query(
			"insert into B_CATALOG_STORE_PRODUCT (AMOUNT, PRODUCT_ID, STORE_ID) (".$internalSql.")",
			true,
			"File: ".__FILE__."<br>Line: ".__LINE__
		);
	}
}