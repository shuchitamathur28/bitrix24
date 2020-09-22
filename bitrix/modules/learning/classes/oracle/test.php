<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/classes/general/test.php");

class CTest extends CAllTest
{
	function GetRandFunction()
	{
		return " DBMS_RANDOM.RANDOM()";
	}
}
