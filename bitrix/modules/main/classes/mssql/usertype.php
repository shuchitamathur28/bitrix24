<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/usertype.php");

class CUserTypeEntity extends CAllUserTypeEntity
{
	//TODO
	function CreatePropertyTables($entity_id)
	{
		global $DB, $APPLICATION;
		if(!$DB->TableExists("B_UTM_".$entity_id))
		{
			$rs = $DB->Query("
				CREATE TABLE B_UTM_".$entity_id." (
					ID INT NOT NULL IDENTITY (1, 1),
					VALUE_ID INT NOT NULL,
					FIELD_ID INT NOT NULL,
					VALUE TEXT,
					VALUE_INT INT,
					VALUE_DOUBLE FLOAT,
					VALUE_DATE DATETIME
				)
			", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("ALTER TABLE B_UTM_".$entity_id." ADD CONSTRAINT PK_UTM_".$entity_id." PRIMARY KEY(ID)", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("CREATE INDEX IX_UTM_".$entity_id."_1 ON B_UTM_".$entity_id." (FIELD_ID)", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("CREATE INDEX IX_UTM_".$entity_id."_2 ON B_UTM_".$entity_id." (VALUE_ID)", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if(!$rs)
			{
				$APPLICATION->ThrowException(GetMessage("USER_TYPE_TABLE_CREATION_ERROR",array(
					"#ENTITY_ID#"=>htmlspecialcharsbx($entity_id),
				)));
				return false;
			}
		}
		if(!$DB->TableExists("B_UTS_".$entity_id))
		{
			$rs = $DB->Query("
				CREATE TABLE B_UTS_".$entity_id." (
					VALUE_ID int not null
				)
			", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("ALTER TABLE B_UTS_".$entity_id." ADD CONSTRAINT PK_UTS_".$entity_id." PRIMARY KEY(VALUE_ID)", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if(!$rs)
			{
				$APPLICATION->ThrowException(GetMessage("USER_TYPE_TABLE_CREATION_ERROR",array(
					"#ENTITY_ID#"=>htmlspecialcharsbx($entity_id),
				)));
				return false;
			}
		}
		return true;
	}

	function DropColumnSQL($strTable, $arColumns)
	{
		global $DB;

		$res = array();
		foreach($arColumns as $strColumn)
		{
			$rs = $DB->Query("
				select distinct si.name index_name
				from sysindexkeys s
				inner join syscolumns c on s.id = c.id and s.colid = c.colid
				inner join sysobjects o on s.id = o.Id and o.xtype='U'
				left join sysindexes si on si.indid = s.indid and si.id=s.id
				where o.name=upper('".$strTable."')
				and c.name='".$strColumn."'
			", true);

			while($ar = $rs->Fetch())
				$res[] = "DROP INDEX ".$ar["index_name"]." ON ".$strTable;

			$res[]="ALTER TABLE ".$strTable." DROP COLUMN ".$strColumn;
		}
		return $res;
	}
}

/**
 * Ёта переменна€ содержит экземпл€р класса через API которого
 * и происходит работа с пользовательскими свойствами.
 * @global CUserTypeManager $GLOBALS['USER_FIELD_MANAGER']
 * @name $USER_FIELD_MANAGER
 */
$GLOBALS['USER_FIELD_MANAGER'] = new CUserTypeManager;
