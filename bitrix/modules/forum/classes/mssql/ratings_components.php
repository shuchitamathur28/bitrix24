<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/forum/classes/general/ratings_components.php");
IncludeModuleLangFile(__FILE__);

class CRatingsComponentsForum extends CAllRatingsComponentsForum
{
	// Calc function
	function CalcUserVoteForumPost($arConfigs)
	{
		global $DB;

		$err_mess = (CRatings::err_mess())."<br>Function: CalcUserVoteForumTopic<br>Line: ";

		CRatings::AddComponentResults($arConfigs);

		$strSql = "DELETE FROM b_rating_component_results WHERE RATING_ID = '".IntVal($arConfigs['RATING_ID'])."' AND COMPLEX_NAME = '".$DB->ForSql($arConfigs['COMPLEX_NAME'])."'";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		$strSql = "INSERT INTO b_rating_component_results (RATING_ID, MODULE_ID, RATING_TYPE, NAME, COMPLEX_NAME, ENTITY_ID, ENTITY_TYPE_ID, CURRENT_VALUE)
					SELECT
						'".IntVal($arConfigs['RATING_ID'])."'  RATING_ID,
						'".$DB->ForSql($arConfigs['MODULE_ID'])."'  MODULE_ID,
						'".$DB->ForSql($arConfigs['RATING_TYPE'])."'  RATING_TYPE,
						'".$DB->ForSql($arConfigs['NAME'])."'  NAME,
						'".$DB->ForSql($arConfigs['COMPLEX_NAME'])."'  COMPLEX_NAME,
						FM.AUTHOR_ID as ENTITY_ID,
						'".$DB->ForSql($arConfigs['ENTITY_ID'])."'  ENTITY_TYPE_ID,
						SUM(RVE.VALUE)*".floatval($arConfigs['CONFIG']['COEFFICIENT'])."  CURRENT_VALUE
					FROM
						b_rating_voting RV LEFT JOIN b_forum_message FM ON RV.ENTITY_ID = FM.ID,
						b_rating_vote RVE
					WHERE
						RV.ENTITY_TYPE_ID = 'FORUM_POST' AND FM.AUTHOR_ID > 0
					AND RVE.RATING_VOTING_ID = RV.ID".(IntVal($arConfigs['CONFIG']['LIMIT']) > 0 ? " AND RVE.CREATED  > dateadd(day, -".IntVal($arConfigs['CONFIG']['LIMIT']).", getdate())" : "")."
					GROUP BY AUTHOR_ID";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return true;
	}

	function CalcUserVoteForumTopic($arConfigs)
	{
		global $DB;

		$err_mess = (CRatings::err_mess())."<br>Function: CalcUserVoteForumTopic<br>Line: ";

		CRatings::AddComponentResults($arConfigs);

		$strSql = "DELETE FROM b_rating_component_results WHERE RATING_ID = '".IntVal($arConfigs['RATING_ID'])."' AND COMPLEX_NAME = '".$DB->ForSql($arConfigs['COMPLEX_NAME'])."'";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		$strSql = "INSERT INTO b_rating_component_results (RATING_ID, MODULE_ID, RATING_TYPE, NAME, COMPLEX_NAME, ENTITY_ID, ENTITY_TYPE_ID, CURRENT_VALUE)
					SELECT
						'".IntVal($arConfigs['RATING_ID'])."'  RATING_ID,
						'".$DB->ForSql($arConfigs['MODULE_ID'])."'  MODULE_ID,
						'".$DB->ForSql($arConfigs['RATING_TYPE'])."'  RATING_TYPE,
						'".$DB->ForSql($arConfigs['NAME'])."'  NAME,
						'".$DB->ForSql($arConfigs['COMPLEX_NAME'])."'  COMPLEX_NAME,
						FT.USER_START_ID  ENTITY_ID,
						'".$DB->ForSql($arConfigs['ENTITY_ID'])."'  ENTITY_TYPE_ID,
						SUM(RVE.VALUE)*".floatval($arConfigs['CONFIG']['COEFFICIENT'])."  CURRENT_VALUE
					FROM
						b_rating_voting RV LEFT JOIN b_forum_topic FT ON RV.ENTITY_ID = FT.ID,
						b_rating_vote RVE
					WHERE
						RV.ENTITY_TYPE_ID = 'FORUM_TOPIC' AND FT.USER_START_ID > 0
					AND RVE.RATING_VOTING_ID = RV.ID".(IntVal($arConfigs['CONFIG']['LIMIT']) > 0 ? " AND RVE.CREATED  > dateadd(day, -".IntVal($arConfigs['CONFIG']['LIMIT']).", getdate())" : "")."
					GROUP BY USER_START_ID";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return true;
	}
	function CalcUserRatingForumActivity($arConfigs)
	{
		global $DB;

		$err_mess = (CRatings::err_mess())."<br>Function: CalcUserRatingForumActivity<br>Line: ";

		CRatings::AddComponentResults($arConfigs);

		$strSql = "DELETE FROM b_rating_component_results WHERE RATING_ID = '".IntVal($arConfigs['RATING_ID'])."' AND COMPLEX_NAME = '".$DB->ForSql($arConfigs['COMPLEX_NAME'])."'";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		$sqlAllTopic = '';
		if (isset($arConfigs['CONFIG']['ALL_TOPIC_COEF']) && $arConfigs['CONFIG']['ALL_TOPIC_COEF'] != 0) {
			$sqlAllTopic = "
				SELECT
					USER_START_ID as ENTITY_ID,
					COUNT(*)*".floatval($arConfigs['CONFIG']['ALL_TOPIC_COEF'])." as CURRENT_VALUE
				FROM b_forum_topic
				WHERE START_DATE  < dateadd(day, -30, getdate())
				GROUP BY USER_START_ID
				UNION ALL ";
		}
		$sqlAllMessage = '';
		if (isset($arConfigs['CONFIG']['ALL_POST_COEF']) && $arConfigs['CONFIG']['ALL_POST_COEF'] != 0) {
			$sqlAllMessage = "
				SELECT
					AUTHOR_ID as ENTITY_ID,
					COUNT(*)*".floatval($arConfigs['CONFIG']['ALL_POST_COEF'])." as CURRENT_VALUE
				FROM b_forum_message
				WHERE POST_DATE  < dateadd(day, -30, getdate())
				GROUP BY AUTHOR_ID
				UNION ALL ";
		}
		$strSql = "INSERT INTO b_rating_component_results (RATING_ID, MODULE_ID, RATING_TYPE, NAME, COMPLEX_NAME, ENTITY_ID, ENTITY_TYPE_ID, CURRENT_VALUE)
			SELECT
				'".IntVal($arConfigs['RATING_ID'])."'  RATING_ID,
				'".$DB->ForSql($arConfigs['MODULE_ID'])."'  MODULE_ID,
				'".$DB->ForSql($arConfigs['RATING_TYPE'])."'  RATING_TYPE,
				'".$DB->ForSql($arConfigs['NAME'])."'  NAME,
				'".$DB->ForSql($arConfigs['COMPLEX_NAME'])."'  COMPLEX_NAME,
				ENTITY_ID,
				'".$DB->ForSql($arConfigs['ENTITY_ID'])."'  ENTITY_TYPE_ID,
				SUM(CURRENT_VALUE) CURRENT_VALUE
			FROM (
				".$sqlAllMessage."
				SELECT
					AUTHOR_ID ENTITY_ID,
					SUM(case when POST_DATE > dateadd(day, -1, getdate()) then 1 else 0 end) *".floatval($arConfigs['CONFIG']['TODAY_POST_COEF'])." +
					SUM(case when POST_DATE > dateadd(day, -7, getdate()) then 1 else 0 end) *".floatval($arConfigs['CONFIG']['WEEK_POST_COEF'])."+
					COUNT(*)*".floatval($arConfigs['CONFIG']['MONTH_POST_COEF'])."  CURRENT_VALUE
				FROM b_forum_message
				WHERE POST_DATE  > dateadd(day, -30, getdate())
				GROUP BY AUTHOR_ID

				UNION ALL
				".$sqlAllTopic."

				SELECT
					USER_START_ID  ENTITY_ID,
					SUM(case when START_DATE > dateadd(day, -1, getdate()) then 1 else 0 end)*".floatval($arConfigs['CONFIG']['TODAY_TOPIC_COEF'])." +
					SUM(case when START_DATE > dateadd(day, -7, getdate()) then 1 else 0 end)*".floatval($arConfigs['CONFIG']['WEEK_TOPIC_COEF'])." +
					COUNT(*)*".floatval($arConfigs['CONFIG']['MONTH_TOPIC_COEF'])."  CURRENT_VALUE
				FROM b_forum_topic
				WHERE START_DATE  > dateadd(day, -30, getdate())
				GROUP BY USER_START_ID
			) q
			WHERE ENTITY_ID > 0
			GROUP BY ENTITY_ID";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return true;
	}

	// Exception function
	function ExceptionUserRatingForumActivity()
	{
		global $DB;
		$bIndex1 = $DB->IndexExists("B_FORUM_TOPIC", array("START_DATE", "USER_START_ID"));
		$bIndex2 = $DB->IndexExists("B_FORUM_MESSAGE", array("POST_DATE", "AUTHOR_ID"));

		if(!$bIndex1 || !$bIndex2)
		{
			$arIndex = Array();
			if (!$bIndex1)
				$arIndex[] = 'CREATE INDEX IX_FORUM_RATING_1 ON B_FORUM_TOPIC(START_DATE, USER_START_ID)';

			if (!$bIndex2)
				$arIndex[] = 'CREATE INDEX IX_FORUM_RATING_2 ON B_FORUM_MESSAGE(POST_DATE, AUTHOR_ID)';

			return GetMessage('EXCEPTION_USER_RATING_FORUM_ACTIVITY_TEXT').'<br>1. <b>'.$arIndex[0].'</b>'.(isset($arIndex[1]) ? '<br> 2. <b>'.$arIndex[1].'</b>' : '');
		}
		else
			return false;
	}
}


?>