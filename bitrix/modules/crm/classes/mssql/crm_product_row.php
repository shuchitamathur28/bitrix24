<?php
class CCrmProductRow extends CAllCrmProductRow
{
	const TABLE_NAME = 'B_CRM_PRODUCT_ROW';
	const CONFIG_TABLE_NAME = 'B_CRM_PRODUCT_ROW_CFG';
	const DB_TYPE = 'MSSQL';

	// Contract -->
	public static function DeleteByOwner($ownerType, $ownerID)
	{
		$ownerType = strval($ownerType);
		$ownerID = intval($ownerID);

		global $DB;
		$ownerType = $DB->ForSql($ownerType);

		$tableName = self::TABLE_NAME;
		$DB->Query(
			"DELETE FROM {$tableName} WHERE OWNER_TYPE = '{$ownerType}' AND OWNER_ID = {$ownerID}", false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);
	}

	public static function DoSaveRows($ownerType, $ownerID, array $arRows)
	{
		global $DB;

		if(!is_int($ownerID))
		{
			$ownerID = (int)$ownerID;
		}

		$insertRows = array();
		$updateRows = array();
		$deleteRows = array();
		foreach($arRows as $row)
		{
			if(isset($row['ID']) && $row['ID'] > 0)
			{
				$updateRows[$row['ID']] = $row;
			}
			else
			{
				$insertRows[] = $row;
			}
		}

		$dbResult = self::GetList(
			array('ID'=>'ASC'),
			array('=OWNER_TYPE' => $ownerType, '=OWNER_ID' => $ownerID)
		);
		if(is_object($dbResult))
		{
			while($row = $dbResult->Fetch())
			{
				$ID = $row['ID'];
				if(!isset($updateRows[$ID]))
				{
					$deleteRows[] = $ID;
				}
				elseif(!self::NeedForUpdate($row, $updateRows[$ID]))
				{
					unset($updateRows[$ID]);
				}
			}
		}

		$tableName = self::TABLE_NAME;
		if(!empty($deleteRows))
		{
			$scriptValues = implode(',', $deleteRows);
			$DB->Query("DELETE FROM {$tableName} WHERE ID IN ({$scriptValues})", false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
		}
		if(!empty($updateRows))
		{
			foreach($updateRows as $ID => $row)
			{
				unset($row['ID'], $row['OWNER_TYPE'], $row['OWNER_ID']);
				$scriptValues = $DB->PrepareUpdate($tableName, $row);

				$DB->Query("UPDATE {$tableName} SET {$scriptValues} WHERE ID = {$ID}", false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
			}
		}
		if(!empty($insertRows))
		{
			$scriptColumns = '';
			$scriptValues = '';
			foreach($insertRows as $row)
			{
				unset($row['ID']);

				$row['OWNER_TYPE'] = $ownerType;
				$row['OWNER_ID'] = $ownerID;
				$data = $DB->PrepareInsert($tableName, $row);

				if($scriptColumns === '')
				{
					$scriptColumns = $data[0];
				}

				if($scriptValues !== '')
				{
					$scriptValues .= " UNION ALL SELECT {$data[1]}";
				}
				else
				{
					$scriptValues = "SELECT {$data[1]}";
				}
			}

			$DB->Query(
				"INSERT INTO {$tableName}({$scriptColumns}) {$scriptValues}",
				false,
				'File: '.__FILE__.'<br/>Line: '.__LINE__
			);
		}

		return true;
	}

	public static function LoadSettings($ownerType, $ownerID)
	{
		$ownerType = $ownerType;
		$ownerID = intval($ownerID);

		global $DB;
		$tableName = self::CONFIG_TABLE_NAME;
		$ownerType = $DB->ForSql($ownerType);
		$dbResult = $DB->Query("SELECT SETTINGS FROM {$tableName} WHERE OWNER_TYPE = '{$ownerType}' AND OWNER_ID = {$ownerID}", false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);
		$fields = is_object($dbResult) ? $dbResult->Fetch() : null;
		$s = is_array($fields) && isset($fields['SETTINGS']) ? $fields['SETTINGS'] : '';
		if($s === '')
		{
			return array();
		}

		return unserialize($s);
	}

	public static function SaveSettings($ownerType, $ownerID, $settings)
	{
		$ownerType = $ownerType;
		$ownerID = intval($ownerID);

		global $DB;
		$tableName = self::CONFIG_TABLE_NAME;
		$ownerType = $DB->ForSql($ownerType);
		$s = $DB->ForSql(serialize($settings));

		$updateSql = "UPDATE {$tableName} SET SETTINGS = '{$s}' WHERE OWNER_ID = {$ownerID} AND OWNER_TYPE = '{$ownerType}'";
		$dbResult = $DB->Query($updateSql, false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);
		if(is_object($dbResult) && $dbResult->AffectedRowsCount() == 0)
		{
			$insertSql = "INSERT INTO {$tableName}(OWNER_ID, OWNER_TYPE, SETTINGS)
				VALUES({$ownerID}, '{$ownerType}', '{$s}')";
			$DB->Query($insertSql, false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);
		}
	}
	// <-- Contract
}
