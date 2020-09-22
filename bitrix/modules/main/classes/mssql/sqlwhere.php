<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/sqlwhere.php");

class CSQLWhere extends CAllSQLWhere
{
	function _Upper($field)
	{
		return "UPPER(CONVERT(VARCHAR(8000), ".$field."))";
	}
	function _Empty($field)
	{
		return "(".$field." IS NULL OR DATALENGTH(".$field.") = 0)";
	}
	function _NotEmpty($field)
	{
		return "(".$field." IS NOT NULL AND DATALENGTH(".$field.") > 0)";
	}
	function _StringEQ($field, $sql_value)
	{
		return "CONVERT(VARCHAR(8000), ".$field.") = '".$sql_value."'";
	}
	function _StringNotEQ($field, $sql_value)
	{
		return "(".$field." IS NULL OR CONVERT(VARCHAR(8000), ".$field.") <> '".$sql_value."')";
	}
	function _StringIN($field, $sql_values)
	{
		return "CONVERT(VARCHAR(8000), ".$field.") in ('".implode("', '", $sql_values)."')";
	}
	function _StringNotIN($field, $sql_values)
	{
		return "(".$field." IS NULL OR CONVERT(VARCHAR(8000), ".$field.") not in ('".implode("', '", $sql_values)."'))";
	}
	function _ExprEQ($field, $val)
	{
		return "CONVERT(VARCHAR(8000), ".$field.") = ".$val->compile();
	}
	function _ExprNotEQ($field, $val)
	{
		return "(".$field." IS NULL OR CONVERT(VARCHAR(8000), ".$field.") <> ".$val->compile().")";
	}
	public function match($field, $fieldValue, $wildcard)
	{
		throw new \Bitrix\Main\NotImplementedException("MATCH not implemented for MSSQL.");
	}
}
