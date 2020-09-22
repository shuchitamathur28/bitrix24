<?php

class CControllerCounter extends CAllControllerCounter
{
	public static function GetMemberValues($CONTROLLER_MEMBER_ID)
	{
		global $DB;
		$CONTROLLER_MEMBER_ID = intval($CONTROLLER_MEMBER_ID);

		$rs = $DB->Query("
			SELECT
				cc.ID
				,cc.NAME
				,case
					when cc.COUNTER_TYPE = 'I' then CAST(ccv.VALUE_INT AS VARCHAR)
					when cc.COUNTER_TYPE = 'F' then CAST(ccv.VALUE_FLOAT AS VARCHAR)
					when cc.COUNTER_TYPE = 'D' then CAST(".$DB->DateToCharFunction("ccv.VALUE_DATE", "FULL")." AS VARCHAR)
					else ccv.VALUE_STRING
				end VALUE
				,cc.COUNTER_FORMAT
			FROM
				b_controller_member cm
				INNER JOIN b_controller_counter_group ccg ON ccg.CONTROLLER_GROUP_ID = cm.CONTROLLER_GROUP_ID
				INNER JOIN b_controller_counter cc ON cc.ID = ccg.CONTROLLER_COUNTER_ID
				LEFT JOIN b_controller_counter_value ccv ON ccv.CONTROLLER_MEMBER_ID = cm.ID AND ccv.CONTROLLER_COUNTER_ID = cc.ID
			WHERE
				cm.ID = ".$CONTROLLER_MEMBER_ID."
			ORDER BY
				cc.NAME
		");

		return new CControllerCounterResult($rs);
	}
}
