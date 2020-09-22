<?
class CIBlockSection extends CAllIBlockSection
{
	public static function GetList($arOrder=array("SORT"=>"ASC"), $arFilter=array(), $bIncCnt = false, $arSelect = array(), $arNavStartParams=false)
	{
		global $DB, $USER, $USER_FIELD_MANAGER;

		if (!is_array($arOrder))
			$arOrder = array();

		$iblockFilterExist = (isset($arFilter['IBLOCK_ID']) && $arFilter['IBLOCK_ID'] > 0);

		$arSqlSearch = CIBlockSection::GetFilter($arFilter);
		$strSqlSearchProp = "";

		$bCheckPermissions = !array_key_exists("CHECK_PERMISSIONS", $arFilter) || $arFilter["CHECK_PERMISSIONS"]!=="N";
		$bIsAdmin = is_object($USER) && $USER->IsAdmin();
		$permissionsBy = null;
		if ($bCheckPermissions && isset($arFilter['PERMISSIONS_BY']))
		{
			$permissionsBy = (int)$arFilter['PERMISSIONS_BY'];
			if ($permissionsBy < 0)
				$permissionsBy = null;
		}
		if($bCheckPermissions && ($permissionsBy !== null || !$bIsAdmin))
			$arSqlSearch[] = self::_check_rights_sql($arFilter["MIN_PERMISSION"], $permissionsBy);
		unset($permissionsBy);

		if(array_key_exists("PROPERTY", $arFilter))
		{
			$val = $arFilter["PROPERTY"];
			foreach($val as $propID=>$propVAL)
			{
				$res = CIBlock::MkOperationFilter($propID);
				$propID = $res["FIELD"];
				$cOperationType = $res["OPERATION"];
				if($db_prop = CIBlockProperty::GetPropertyArray($propID, $arFilter["IBLOCK_ID"]))
				{

					if(!is_array($propVAL))
						$propVAL = Array($propVAL);

					if($db_prop["PROPERTY_TYPE"]=="N" || $db_prop["PROPERTY_TYPE"]=="G" || $db_prop["PROPERTY_TYPE"]=="E")
					{
						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$r = CIBlock::FilterCreate("FPV.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "number", $cOperationType);
						}
						else
							$r = CIBlock::FilterCreate("FPV.VALUE_NUM", $propVAL, "number", $cOperationType);
					}
					else
					{
						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$r = CIBlock::FilterCreate("FPV.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "string", $cOperationType);
						}
						else
							$r = CIBlock::FilterCreate("FPV.VALUE", $propVAL, "string", $cOperationType);
					}

					if(strlen($r)>0)
					{
						if(is_numeric(substr($propID, 0, 1)))
							$strPropsAdd = "FP".$iPropsAdd.".ID = ".(int)$propID." ";
						else
							$strPropsAdd = "FP".$iPropsAdd.".CODE = '".$DB->ForSql($propID, 100)."' ";

						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$strSqlSearchProp .= "
							AND EXISTS (
								SELECT *
								FROM b_iblock_element_prop_s".$db_prop["IBLOCK_ID"]." FPV
								WHERE FPV.IBLOCK_ELEMENT_ID=BE.ID
								AND ".$r."
							)
							";
						}
						else
						{
							if($db_prop["VERSION"]==2)
								$strTable = "b_iblock_element_prop_m".$db_prop["IBLOCK_ID"];
							else
								$strTable = "b_iblock_element_property";
							$strSqlSearchProp .= "
							AND EXISTS (
								SELECT *
								FROM b_iblock_property FP
									,".$strTable." FPV
								WHERE ".$strPropsAdd."
								AND FP.ID=FPV.IBLOCK_PROPERTY_ID
								AND FPV.IBLOCK_ELEMENT_ID=BE.ID
								AND ".$r."
							)
							";
						}

					}
				}
			}
		}

		$strSqlSearch = "";
		foreach($arSqlSearch as $r)
			if(strlen($r)>0)
				$strSqlSearch .= "\n\t\t\t\tAND  (".$r.") ";

		$strUserFieldsSelect = "";
		$strUserFieldsSelectJoin = "";
		$strUserFieldsWhere = "";
		$strUserFieldsWhereJoin = "";
		if($iblockFilterExist)
		{
			$obUserFieldsSql = new CUserTypeSQL;
			$obUserFieldsSql->SetEntity("IBLOCK_".$arFilter["IBLOCK_ID"]."_SECTION", "BSTEMP2.ID");
			$obUserFieldsSql->SetFilter($arFilter);
			$strUserFieldsWhere = $obUserFieldsSql->GetFilter();
			$strUserFieldsWhereJoin = $obUserFieldsSql->GetJoin("BSTEMP2.ID");

			$obUserFieldsSql = new CUserTypeSQL;
			$obUserFieldsSql->SetEntity("IBLOCK_".$arFilter["IBLOCK_ID"]."_SECTION", "BS.ID");
			$obUserFieldsSql->SetSelect($arSelect);
			$obUserFieldsSql->SetOrder($arOrder);

			$strUserFieldsSelect = $obUserFieldsSql->GetSelect();
			$strUserFieldsSelectJoin = $obUserFieldsSql->GetJoin("BS.ID");
		}
		else
		{
			foreach($arFilter as $key => $val)
			{
				$res = CIBlock::MkOperationFilter($key);
				if(preg_match("/^UF_/", $res["FIELD"]))
					trigger_error("arFilter parameter of the CIBlockSection::GetList contains user fields, but has no IBLOCK_ID field.", E_USER_WARNING);
			}
		}

		$arFields = array(
			"ID" => "BS.ID",
			"CODE" => "BS.CODE",
			"XML_ID" => "BS.XML_ID",
			"EXTERNAL_ID" => "BS.XML_ID",
			"IBLOCK_ID" => "BS.IBLOCK_ID",
			"IBLOCK_SECTION_ID" => "BS.IBLOCK_SECTION_ID",
			"TIMESTAMP_X" =>  $DB->DateToCharFunction("BS.TIMESTAMP_X"),
			"SORT" => "BS.SORT",
			"NAME" => "BS.NAME",
			"ACTIVE" => "BS.ACTIVE",
			"GLOBAL_ACTIVE" => "BS.GLOBAL_ACTIVE",
			"PICTURE" => "BS.PICTURE",
			"DESCRIPTION" => "BS.DESCRIPTION",
			"DESCRIPTION_TYPE" => "BS.DESCRIPTION_TYPE",
			"LEFT_MARGIN" => "BS.LEFT_MARGIN",
			"RIGHT_MARGIN" => "BS.RIGHT_MARGIN",
			"DEPTH_LEVEL" => "BS.DEPTH_LEVEL",
			"SEARCHABLE_CONTENT" => "BS.SEARCHABLE_CONTENT",
			"MODIFIED_BY" => "BS.MODIFIED_BY",
			"DATE_CREATE" =>  $DB->DateToCharFunction("BS.DATE_CREATE"),
			"CREATED_BY" => "BS.CREATED_BY",
			"DETAIL_PICTURE" => "BS.DETAIL_PICTURE",
			"TMP_ID" => "BS.TMP_ID",

			"LIST_PAGE_URL" => "B.LIST_PAGE_URL",
			"SECTION_PAGE_URL" => "B.SECTION_PAGE_URL",
			"IBLOCK_TYPE_ID" => "B.IBLOCK_TYPE_ID",
			"IBLOCK_CODE" => "B.CODE",
			"IBLOCK_EXTERNAL_ID" => "B.XML_ID",
			"SOCNET_GROUP_ID" => "BS.SOCNET_GROUP_ID",
		);

		$arSqlSelect = array();
		foreach($arSelect as $field)
		{
			$field = strtoupper($field);
			if(isset($arFields[$field]))
				$arSqlSelect[$field] = $arFields[$field]." AS ".$field;
		}
		if (empty($arSqlSelect))
		{
			foreach($arFields as $field => $sql)
				$arSqlSelect[$field] = $arFields[$field]." AS ".$field;
		}

		if(isset($arSqlSelect['DESCRIPTION']))
			$arSqlSelect["DESCRIPTION_TYPE"] = $arFields["DESCRIPTION_TYPE"]." AS DESCRIPTION_TYPE";

		if(isset($arSqlSelect['LIST_PAGE_URL']) || isset($arSqlSelect['SECTION_PAGE_URL']))
		{
			$arSqlSelect["ID"] = $arFields["ID"]." AS ID";
			$arSqlSelect["CODE"] = $arFields["CODE"]." AS CODE";
			$arSqlSelect["EXTERNAL_ID"] = $arFields["EXTERNAL_ID"]." AS EXTERNAL_ID";
			$arSqlSelect["IBLOCK_TYPE_ID"] = $arFields["IBLOCK_TYPE_ID"]." AS IBLOCK_TYPE_ID";
			$arSqlSelect["IBLOCK_ID"] = $arFields["IBLOCK_ID"]." AS IBLOCK_ID";
			$arSqlSelect["IBLOCK_CODE"] = $arFields["IBLOCK_CODE"]." AS IBLOCK_CODE";
			$arSqlSelect["IBLOCK_EXTERNAL_ID"] = $arFields["IBLOCK_EXTERNAL_ID"]." AS IBLOCK_EXTERNAL_ID";
			$arSqlSelect["GLOBAL_ACTIVE"] = $arFields["GLOBAL_ACTIVE"]." AS GLOBAL_ACTIVE";
			//$arr["LANG_DIR"],
		}

		if(!empty($arSqlSelect))
			$sSelect = implode(",\n", $arSqlSelect);
		else
			$sSelect = "
				BS.*,
				B.LIST_PAGE_URL,
				B.SECTION_PAGE_URL,
				B.IBLOCK_TYPE_ID,
				B.CODE as IBLOCK_CODE,
				B.XML_ID as IBLOCK_EXTERNAL_ID,
				BS.XML_ID as EXTERNAL_ID,
				".$DB->DateToCharFunction("BS.TIMESTAMP_X")." as TIMESTAMP_X,
				".$DB->DateToCharFunction("BS.DATE_CREATE")." as DATE_CREATE
			";

		if(!$bIncCnt)
		{
			$strSelect = $sSelect.$strUserFieldsSelect;

			$strSql = "
				FROM
					b_iblock B
					INNER JOIN b_iblock_section BS ON BS.IBLOCK_ID = B.ID
					".$strUserFieldsSelectJoin."
				WHERE 1 = 1
				".(strlen($strSqlSearchProp)>0?"
					AND EXISTS(
						SELECT *
						FROM b_iblock_element BE
							,b_iblock_section BSTEMP
							,b_iblock_section_element BSE
						WHERE BSE.IBLOCK_ELEMENT_ID=BE.ID
								AND BSTEMP.IBLOCK_ID = BS.IBLOCK_ID
								AND BSTEMP.LEFT_MARGIN >= BS.LEFT_MARGIN
								AND BSTEMP.RIGHT_MARGIN <= BS.RIGHT_MARGIN
								AND BSE.IBLOCK_SECTION_ID=BSTEMP.ID
								AND BE.IBLOCK_ID = BS.IBLOCK_ID
								AND ((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
							".($arFilter["CNT_ALL"]=="Y"?" OR BE.WF_NEW='Y' ":"").")
							".($arFilter["CNT_ACTIVE"]=="Y"?
								" AND BE.ACTIVE='Y' ".
								" AND (BE.ACTIVE_TO >= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_TO IS NULL) ".
								" AND (BE.ACTIVE_FROM <= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_FROM IS NULL)"
							:"")."
							".$strSqlSearchProp.") "
				:""
				)."
				".($strUserFieldsWhere? "
					AND EXISTS(
						SELECT *
						FROM
							b_iblock_section BSTEMP2
							".$strUserFieldsWhereJoin."
						WHERE BSTEMP2.ID = BS.ID
						AND ".$strUserFieldsWhere."
					)"
				:""
				)."
				".$strSqlSearch;
		}
		else
		{
			$strSelect =  $sSelect."
				,(
					SELECT COUNT(DISTINCT BE.ID)
					FROM b_iblock_section BS0
						,b_iblock_element BE
						,b_iblock_section BSTEMP
						,b_iblock_section_element BSE
					WHERE BSE.IBLOCK_ELEMENT_ID=BE.ID
						AND BSTEMP.IBLOCK_ID=BS0.IBLOCK_ID
						AND BSTEMP.LEFT_MARGIN >= BS0.LEFT_MARGIN
						AND BSTEMP.RIGHT_MARGIN <= BS0.RIGHT_MARGIN
						".($arFilter["CNT_ACTIVE"]=="Y"? "AND BSTEMP.GLOBAL_ACTIVE = 'Y'": "")."
						AND BSE.IBLOCK_SECTION_ID=BSTEMP.ID
						AND BE.IBLOCK_ID = BS0.IBLOCK_ID
						AND ((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
					".($arFilter["CNT_ALL"]=="Y"?" OR BE.WF_NEW='Y' ":"").")
					".($arFilter["CNT_ACTIVE"]=="Y"?
						" AND BE.ACTIVE='Y' ".
						" AND (BE.ACTIVE_TO >= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_TO IS NULL) ".
						" AND (BE.ACTIVE_FROM <= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_FROM IS NULL)"
					:"")."
					".$strSqlSearchProp."
					AND BS0.ID = BS.ID
				) ELEMENT_CNT
				".$strUserFieldsSelect;

			$strSql = "
				FROM
					b_iblock B
					INNER JOIN b_iblock_section BS ON BS.IBLOCK_ID = B.ID
					".$strUserFieldsSelectJoin."
				WHERE 1=1
				".($strUserFieldsWhere? "
					AND EXISTS(
						SELECT *
						FROM
							b_iblock_section BSTEMP2
							".$strUserFieldsWhereJoin."
						WHERE BSTEMP2.ID = BS.ID
						AND ".$strUserFieldsWhere."
					)"
				:""
				)."
				".$strSqlSearch."
			";
		}

		$arSqlOrder = array();
		foreach($arOrder as $by=>$order)
		{
			$by = strtolower($by);
			if(isset($arSqlOrder[$by]))
				continue;
			$order = strtolower($order);
			if($order!="asc")
				$order = "desc";

			if($by == "id") $arSqlOrder[$by] = " BS.ID ".$order." ";
			elseif($by == "section") $arSqlOrder[$by] = " BS.IBLOCK_SECTION_ID ".$order." ";
			elseif($by == "name") $arSqlOrder[$by] = " BS.NAME ".$order." ";
			elseif($by == "code") $arSqlOrder[$by] = " BS.CODE ".$order." ";
			elseif($by == "external_id") $arSqlOrder[$by] = " BS.XML_ID ".$order." ";
			elseif($by == "xml_id") $arSqlOrder[$by] = " BS.XML_ID ".$order." ";
			elseif($by == "active") $arSqlOrder[$by] = " BS.ACTIVE ".$order." ";
			elseif($by == "left_margin") $arSqlOrder[$by] = " BS.LEFT_MARGIN ".$order." ";
			elseif($by == "depth_level") $arSqlOrder[$by] = " BS.DEPTH_LEVEL ".$order." ";
			elseif($by == "sort") $arSqlOrder[$by] = " BS.SORT ".$order." ";
			elseif($by == "created") $arSqlOrder[$by] = " BS.DATE_CREATE ".$order." ";
			elseif($by == "created_by") $arSqlOrder[$by] = " BS.CREATED_BY ".$order." ";
			elseif($by == "modified_by") $arSqlOrder[$by] = " BS.MODIFIED_BY ".$order." ";
			elseif($bIncCnt && $by == "element_cnt")  $arSqlOrder[$by] = " ELEMENT_CNT ".$order." ";
			elseif(isset($obUserFieldsSql) && $s = $obUserFieldsSql->GetOrder($by))  $arSqlOrder[$by] = " ".$s." ".$order." ";
			else
			{
				$by = "timestamp_x";
				$arSqlOrder[$by] = " BS.TIMESTAMP_X ".$order." ";
			}
		}

		if(!empty($arSqlOrder))
			$strSqlOrder = "\n\t\t\t\tORDER BY ".implode(", ", $arSqlOrder);
		else
			$strSqlOrder = "";

		if(is_array($arNavStartParams))
		{
			$nTopCount = (isset($arNavStartParams['nTopCount']) ? (int)$arNavStartParams['nTopCount'] : 0);
			if($nTopCount > 0)
			{
				$res = $DB->Query($DB->TopSql(
					"SELECT ".$strSelect.$strSql.$strSqlOrder,
					$nTopCount
				));
				if($iblockFilterExist)
				{
					$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$arFilter["IBLOCK_ID"]."_SECTION"));
				}
			}
			else
			{
				$res_cnt = $DB->Query("SELECT COUNT(BS.ID) as C ".$strSql);
				$res_cnt = $res_cnt->Fetch();
				$res = new CDBResult();
				if($iblockFilterExist)
				{
					$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$arFilter["IBLOCK_ID"]."_SECTION"));
				}
				$res->NavQuery("SELECT ".$strSelect.$strSql.$strSqlOrder, $res_cnt["C"], $arNavStartParams);
			}
		}
		else
		{
			$res = $DB->Query("SELECT ".$strSelect.$strSql.$strSqlOrder, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			if($iblockFilterExist)
			{
				$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$arFilter["IBLOCK_ID"]."_SECTION"));
			}
		}

		$res = new CIBlockResult($res);
		if($iblockFilterExist)
		{
			$res->SetIBlockTag($arFilter["IBLOCK_ID"]);
		}

		return $res;
	}
	///////////////////////////////////////////////////////////////////
	// Update list of sections w/o any events
	///////////////////////////////////////////////////////////////////
	protected function UpdateList($arFields, $arFilter = array())
	{
		global $DB, $USER;

		$strUpdate = $DB->PrepareUpdate("b_iblock_section", $arFields, "iblock");
		if ($strUpdate == "")
			return false;

		$arSqlSearch = CIBlockSection::GetFilter($arFilter);
		$strSqlSearchProp = "";

		$bCheckPermissions = !array_key_exists("CHECK_PERMISSIONS", $arFilter) || $arFilter["CHECK_PERMISSIONS"]!=="N";
		$bIsAdmin = is_object($USER) && $USER->IsAdmin();
		$permissionsBy = null;
		if ($bCheckPermissions && isset($arFilter['PERMISSIONS_BY']))
		{
			$permissionsBy = (int)$arFilter['PERMISSIONS_BY'];
			if ($permissionsBy < 0)
				$permissionsBy = null;
		}
		if($bCheckPermissions && ($permissionsBy !== null || !$bIsAdmin))
			$arSqlSearch[] = self::_check_rights_sql($arFilter["MIN_PERMISSION"], $permissionsBy);
		unset($permissionsBy);

		if(array_key_exists("PROPERTY", $arFilter))
		{
			$val = $arFilter["PROPERTY"];
			foreach($val as $propID=>$propVAL)
			{
				$res = CIBlock::MkOperationFilter($propID);
				$propID = $res["FIELD"];
				$cOperationType = $res["OPERATION"];
				if($db_prop = CIBlockProperty::GetPropertyArray($propID, $arFilter["IBLOCK_ID"]))
				{

					if(!is_array($propVAL))
						$propVAL = Array($propVAL);

					if($db_prop["PROPERTY_TYPE"]=="N" || $db_prop["PROPERTY_TYPE"]=="G" || $db_prop["PROPERTY_TYPE"]=="E")
					{
						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$r = CIBlock::FilterCreate("FPV.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "number", $cOperationType);
						}
						else
							$r = CIBlock::FilterCreate("FPV.VALUE_NUM", $propVAL, "number", $cOperationType);
					}
					else
					{
						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$r = CIBlock::FilterCreate("FPV.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "string", $cOperationType);
						}
						else
							$r = CIBlock::FilterCreate("FPV.VALUE", $propVAL, "string", $cOperationType);
					}

					if(strlen($r)>0)
					{
						if(is_numeric(substr($propID, 0, 1)))
							$strPropsAdd = "FP".$iPropsAdd.".ID = ".(int)$propID." ";
						else
							$strPropsAdd = "FP".$iPropsAdd.".CODE = '".$DB->ForSql($propID, 100)."' ";

						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$strSqlSearchProp .= "
							AND EXISTS (
								SELECT *
								FROM b_iblock_element_prop_s".$db_prop["IBLOCK_ID"]." FPV
								WHERE FPV.IBLOCK_ELEMENT_ID=BE.ID
								AND ".$r."
							)
							";
						}
						else
						{
							if($db_prop["VERSION"]==2)
								$strTable = "b_iblock_element_prop_m".$db_prop["IBLOCK_ID"];
							else
								$strTable = "b_iblock_element_property";
							$strSqlSearchProp .= "
							AND EXISTS (
								SELECT *
								FROM b_iblock_property FP
									,".$strTable." FPV
								WHERE ".$strPropsAdd."
								AND FP.ID=FPV.IBLOCK_PROPERTY_ID
								AND FPV.IBLOCK_ELEMENT_ID=BE.ID
								AND ".$r."
							)
							";
						}

					}
				}
			}
		}

		$strSqlSearch = "";
		foreach($arSqlSearch as $r)
			if(strlen($r)>0)
				$strSqlSearch .= "\n\t\t\t\tAND  (".$r.") ";

		$strUserFieldsWhere = "";
		$strUserFieldsWhereJoin = "";
		if(isset($arFilter["IBLOCK_ID"]) && $arFilter["IBLOCK_ID"] > 0)
		{
			$obUserFieldsSql = new CUserTypeSQL;
			$obUserFieldsSql->SetEntity("IBLOCK_".$arFilter["IBLOCK_ID"]."_SECTION", "BSTEMP2.ID");
			$obUserFieldsSql->SetFilter($arFilter);
			$strUserFieldsWhere = $obUserFieldsSql->GetFilter();
			$strUserFieldsWhereJoin = $obUserFieldsSql->GetJoin("BSTEMP2.ID");
		}
		else
		{
			foreach($arFilter as $key => $val)
			{
				$res = CIBlock::MkOperationFilter($key);
				if(preg_match("/^UF_/", $res["FIELD"]))
				{
					trigger_error("arFilter parameter of the CIBlockSection::GetList contains user fields, but has no IBLOCK_ID field.", E_USER_WARNING);
					break;
				}
			}
		}

		$strSql = "
			UPDATE b_iblock_section SET ".$strUpdate."
			WHERE ID IN (
			SELECT BS.ID
			FROM
				b_iblock B
				INNER JOIN b_iblock_section BS ON BS.IBLOCK_ID = B.ID
				".$strUserFieldsSelectJoin."
			WHERE 1 = 1
			".(strlen($strSqlSearchProp)>0?"
				AND EXISTS(
					SELECT *
					FROM b_iblock_element BE
						,b_iblock_section BSTEMP
						,b_iblock_section_element BSE
					WHERE BSE.IBLOCK_ELEMENT_ID=BE.ID
							AND BSTEMP.IBLOCK_ID = BS.IBLOCK_ID
							AND BSTEMP.LEFT_MARGIN >= BS.LEFT_MARGIN
							AND BSTEMP.RIGHT_MARGIN <= BS.RIGHT_MARGIN
							AND BSE.IBLOCK_SECTION_ID=BSTEMP.ID
							AND BE.IBLOCK_ID = BS.IBLOCK_ID
							AND ((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
						".($arFilter["CNT_ALL"]=="Y"?" OR BE.WF_NEW='Y' ":"").")
						".($arFilter["CNT_ACTIVE"]=="Y"?
							" AND BE.ACTIVE='Y' ".
							" AND (BE.ACTIVE_TO >= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_TO IS NULL) ".
							" AND (BE.ACTIVE_FROM <= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_FROM IS NULL)"
						:"")."
						".$strSqlSearchProp.") "
			:""
			)."
			".($strUserFieldsWhere? "
				AND EXISTS(
					SELECT *
					FROM
						b_iblock_section BSTEMP2
						".$strUserFieldsWhereJoin."
					WHERE BSTEMP2.ID = BS.ID
					AND ".$strUserFieldsWhere."
				)"
			:""
			)."
			".$strSqlSearch."
			)
		";

		return $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	}
}