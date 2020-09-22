<?
class CFile extends CAllFile
{
	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if($ID <= 0)
			return;

		$res = CFile::GetByID($ID);
		if($res = $res->Fetch())
		{
			foreach(GetModuleEvents("main", "OnFileDelete", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($res));
		}
		$DB->Query("BEGIN DELFILE(".$ID.", NULL); END;");
	}

	public static function DoDelete($ID)
	{
		//nothing to do: images delete in triggers
	}

	public static function DoInsert($arFields)
	{
		global $DB;
		$NEW_IMAGE_ID = $DB->NextID("sq_b_file");
		$strSql =
			"INSERT INTO b_file(
				ID,
				HEIGHT,
				WIDTH,
				FILE_SIZE,
				CONTENT_TYPE,
				SUBDIR,
				FILE_NAME,
				MODULE_ID,
				ORIGINAL_NAME,
				DESCRIPTION,
				HANDLER_ID,
				EXTERNAL_ID
			) VALUES (
				".$NEW_IMAGE_ID.",
				".intval($arFields["HEIGHT"]).",
				".intval($arFields["WIDTH"]).",
				".round(floatval($arFields["FILE_SIZE"])).",
				'".$DB->ForSql($arFields["CONTENT_TYPE"], 255)."',
				'".$DB->ForSql($arFields["SUBDIR"], 255)."',
				'".$DB->ForSQL($arFields["FILE_NAME"], 255)."',
				'".$DB->ForSQL($arFields["MODULE_ID"], 50)."',
				'".$DB->ForSql($arFields["ORIGINAL_NAME"], 255)."',
				'".$DB->ForSQL($arFields["DESCRIPTION"], 255)."',
				".($arFields["HANDLER_ID"]? "'".$DB->ForSql($arFields["HANDLER_ID"], 50)."'": "null").",
				".($arFields["EXTERNAL_ID"] != ""? "'".$DB->ForSql($arFields["EXTERNAL_ID"], 50)."'": "null")."
			)";
		$DB->Query($strSql);
		return $NEW_IMAGE_ID;
	}
}
?>