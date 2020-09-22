<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/sqlwhere.php");

class CSQLWhere extends CAllSQLWhere
{
	function _NumberIN($field, $sql_values)
	{
		if (in_array(0, $sql_values, true))
			$result = $field." IS NULL";
		else
			$result = "";

		do
		{
			if ($result)
				$result .= " or ";
			$result .= $field." in (".implode(", ", array_slice($sql_values, 0, 1000)).")";
			array_splice($sql_values, 0, 1000);
		}
		while (!empty($sql_values));

		return $result;
	}
	public function match($field, $fieldValue, $wildcard)
	{
		throw new \Bitrix\Main\NotImplementedException("MATCH not implemented for Oracle.");
	}
}
