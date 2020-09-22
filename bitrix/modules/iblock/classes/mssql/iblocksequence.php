<?
class CIBlockSequence
{
	var $iblock_id = 0;
	var $property_id = 0;

	function __construct($iblock_id, $property_id = 0)
	{
		return $this->CIBlockSequence($iblock_id, $property_id);
	}

	function CIBlockSequence($iblock_id, $property_id = 0)
	{
		$this->iblock_id = $iblock_id;
		$this->property_id = $property_id;
	}

	function Drop($bAll = false)
	{
		global $DB;
		//OR part of the where is just for some cleanup
		$strSql = "
			DELETE
			FROM b_iblock_sequence
			WHERE IBLOCK_ID = ".intval($this->iblock_id)."
			".(!$bAll? "AND CODE = 'PROPERTY_".intval($this->property_id)."'": "")."
			OR NOT EXISTS (
				SELECT * FROM
				b_iblock_property
				WHERE 'PROPERTY_' + CAST(b_iblock_property.ID AS VARCHAR) = b_iblock_sequence.CODE
				AND b_iblock_property.IBLOCK_ID = b_iblock_sequence.IBLOCK_ID
			)
		";
		$rs = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		return $rs;
	}

	function GetCurrent()
	{
		global $DB;
		$strSql = "
			SELECT *
			FROM b_iblock_sequence
			WHERE IBLOCK_ID = ".intval($this->iblock_id)."
			AND CODE = 'PROPERTY_".intval($this->property_id)."'
		";
		$rs = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar)
			return $ar["SEQ_VALUE"];
		else
			return 0;
	}

	function GetNext()
	{
		global $DB;

		$DB->StartTransaction();

		$strSql = "
			SELECT SEQ_VALUE
			FROM b_iblock_sequence
			WITH (TABLOCKX)
			WHERE IBLOCK_ID = ".intval($this->iblock_id)."
			AND CODE = 'PROPERTY_".intval($this->property_id)."'
		";
		$rs = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar)
		{
			$strSql = "
				UPDATE b_iblock_sequence
				SET SEQ_VALUE = ".($ar["SEQ_VALUE"] + 1)."
				WHERE IBLOCK_ID = ".intval($this->iblock_id)."
				AND CODE = 'PROPERTY_".intval($this->property_id)."'
			";
			$rs = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			$DB->Commit();
			return $ar["SEQ_VALUE"] + 1;
		}
		else
		{
			$strSql = "
				INSERT INTO b_iblock_sequence (IBLOCK_ID, CODE, SEQ_VALUE)
				VALUES (".intval($this->iblock_id).", 'PROPERTY_".intval($this->property_id)."', 1)
			";
			$rs = $DB->Query($strSql, true);
			if($rs)
			{
				$DB->Commit();
				return 1;
			}
			else
			{
				$strSql = "
					SELECT *
					FROM b_iblock_sequence
					WITH (TABLOCKX)
					WHERE IBLOCK_ID = ".intval($this->iblock_id)."
					AND CODE = 'PROPERTY_".intval($this->property_id)."'
				";
				$rs = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
				$ar = $rs->Fetch();
				if($ar)
				{
					$strSql = "
						UPDATE b_iblock_sequence
						SET SEQ_VALUE = ".($ar["SEQ_VALUE"] + 1)."
						WHERE IBLOCK_ID = ".intval($this->iblock_id)."
						AND CODE = 'PROPERTY_".intval($this->property_id)."'
					";
					$rs = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

					$DB->Commit();
					return $ar["SEQ_VALUE"] + 1;
				}
				else
				{
					$DB->Commit();
					return 1;
				}
			}
		}
	}

	function SetNext($value)
	{
		global $DB;
		$value = intval($value);

		$DB->StartTransaction();

		$strSql = "
			SELECT SEQ_VALUE
			FROM b_iblock_sequence
			WITH (TABLOCKX)
			WHERE IBLOCK_ID = ".intval($this->iblock_id)."
			AND CODE = 'PROPERTY_".intval($this->property_id)."'
		";
		$rs = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar)
		{
			$strSql = "
				UPDATE b_iblock_sequence
				SET SEQ_VALUE = ".$value."
				WHERE IBLOCK_ID = ".intval($this->iblock_id)."
				AND CODE = 'PROPERTY_".intval($this->property_id)."'
			";
			$rs = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
		else
		{
			$strSql = "
				INSERT INTO b_iblock_sequence (IBLOCK_ID, CODE, SEQ_VALUE)
				VALUES (".intval($this->iblock_id).", 'PROPERTY_".intval($this->property_id)."', ".$value.")
			";
			$rs = $DB->Query($strSql, true);
			if(!$rs)
			{
				$strSql = "
					UPDATE b_iblock_sequence
					SET SEQ_VALUE = ".$value."
					WHERE IBLOCK_ID = ".intval($this->iblock_id)."
					AND CODE = 'PROPERTY_".intval($this->property_id)."'
				";
				$rs = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			}
		}

		$DB->Commit();

		return $value;
	}
}
?>
