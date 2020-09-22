<?php
IncludeModuleLangFile(__FILE__);

class CCrmQuote extends CAllCrmQuote
{
	const TABLE_NAME = 'B_CRM_QUOTE';
	const ELEMENT_TABLE_NAME = 'B_CRM_QUOTE_ELEM';
	const DB_TYPE = 'ORACLE';

	public static function DoSaveElementIDs($ID, $storageTypeID, $arElementIDs)
	{
		global $APPLICATION, $DB;

		$ID = intval($ID);
		$storageTypeID = intval($storageTypeID);
		if($ID <= 0 || !CCrmQuoteStorageType::IsDefined($storageTypeID) || !is_array($arElementIDs))
		{
			$APPLICATION->throwException(GetMessage('CRM_QUOTE_ERR_INVALID_PARAMS'));
			return false;
		}

		$DB->Query(
			'DELETE FROM '.self::ELEMENT_TABLE_NAME.' WHERE QUOTE_ID = '.$ID,
			false,
			'File: '.__FILE__.'<br/>Line: '.__LINE__
		);

		if(empty($arElementIDs))
		{
			return true;
		}

		$arRows = array();
		foreach($arElementIDs as $elementID)
		{
			$arRows[] = array(
				'QUOTE_ID'=> $ID,
				'STORAGE_TYPE_ID' => $storageTypeID,
				'ELEMENT_ID' => $elementID
			);
		}

		$bulkColumns = '';
		$bulkValues = array();

		foreach($arRows as &$row)
		{
			$data = $DB->PrepareInsert(self::ELEMENT_TABLE_NAME, $row);
			if($bulkColumns === '')
			{
				$bulkColumns = $data[0];
			}

			$bulkValues[] = $data[1];
		}
		unset($row);

		$query = '';
		foreach($bulkValues as &$value)
		{
			$query .= ($query !== '' ? ' UNION ALL ' : '').'SELECT '.$value.' FROM dual';
		}

		if($query !== '')
		{
			$DB->Query(
				'INSERT INTO '.self::ELEMENT_TABLE_NAME.'('.$bulkColumns.') '.$query,
				false,
				'File: '.__FILE__.'<br/>Line: '.__LINE__
			);
		}

		return true;
	}
}
