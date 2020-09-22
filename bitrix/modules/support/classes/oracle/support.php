<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/classes/general/support.php");

class CTicket extends CAllTicket
{
	public static function isnull( $field, $alternative )
	{
		return "nvl(" . $field . "," . $alternative . ")";
	}

	public static function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." <br>Class: CTicket<br>File: ".__FILE__;
	}

	public static function AutoClose()
	{
		$err_mess = (CTicket::err_mess())."<br>Function: AutoClose<br>Line: ";
		global $DB;
		/*$strSql = "
			SELECT
				T.ID
			FROM
				b_ticket T
			WHERE
				T.AUTO_CLOSE_DAYS > 0
			and T.DATE_CLOSE is null
			and	(sysdate - T.TIMESTAMP_X) > T.AUTO_CLOSE_DAYS
			";*/

		$nowTime = $DB->CharToDateFunction(GetTime(time() + CTimeZone::GetOffset(),"FULL"));
		$strSql = "
		SELECT
			T.ID
		FROM
			b_ticket T
		WHERE
			T.AUTO_CLOSE_DAYS > 0
		and T.DATE_CLOSE is null
		and	($nowTime - T.LAST_MESSAGE_DATE) > T.AUTO_CLOSE_DAYS
		and T.LAST_MESSAGE_BY_SUPPORT_TEAM = 'Y'
		";//sysdate

		$rsTickets = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($arTicket = $rsTickets->Fetch())
		{
			$arFields = array(
				"TIMESTAMP_X"			=> $DB->GetNowFunction(),
				"DATE_CLOSE"			=> $DB->GetNowFunction(),
				"MODIFIED_USER_ID"		=> "null",
				"MODIFIED_GUEST_ID"		=> "null",
				"MODIFIED_MODULE_NAME"	=> "'auto closing'",
				//"AUTO_CLOSE_DAYS"		=> "null",
				"AUTO_CLOSED"			=> "'Y'"
				);
			$DB->Update("b_ticket",$arFields,"WHERE ID='".$arTicket["ID"]."'",$err_mess.__LINE__);
		}
		return "CTicket::AutoClose();";
	}

	public static function CleanUpOnline()
	{
		$err_mess = (CTicket::err_mess())."<br>Function: CleanUpOnline<br>Line: ";
		global $DB;
		$onlineInterval = intval(COption::GetOptionString("support", "ONLINE_INTERVAL"));
		$strSql = "
			DELETE FROM b_ticket_online WHERE
				TIMESTAMP_X < SYSDATE - $onlineInterval/86400
			";
		$DB->Query($strSql,false,$err_mess.__LINE__);
		return "CTicket::CleanUpOnline();";
	}

	public static function GetOnline($ticketID)
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetOnline<br>Line: ";
		global $DB;
		$ticketID = intval($ticketID);
		$onlineInterval = intval(COption::GetOptionString("support", "ONLINE_INTERVAL"));
		$strSql = "
			SELECT
				".$DB->DateToCharFunction("max(T.TIMESTAMP_X)")."	TIMESTAMP_X,
				T.USER_ID,
				T.CURRENT_MODE,
				U.EMAIL											USER_EMAIL,
				U.LOGIN											USER_LOGIN,
				nvl(U.NAME,'')||' '||nvl(U.LAST_NAME,'')		USER_NAME
			FROM
				b_ticket_online T,
				b_user U
			WHERE
				T.TICKET_ID = $ticketID
			and T.TIMESTAMP_X >= SYSDATE - $onlineInterval/86400
			and U.ID = T.USER_ID
			GROUP BY
				T.USER_ID, T.CURRENT_MODE, U.EMAIL, U.LOGIN, U.NAME, U.LAST_NAME
			ORDER BY
				T.USER_ID
			";

		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $z;
	}

	public static function DeleteMessage($id, $checkRights="Y")
	{
		$err_mess = (CTicket::err_mess())."<br>Function: DeleteMessage<br>Line: ";
		global $DB;
		$id = intval($id);
		if ($id<=0) return;

		$bAdmin = "N";
		if ($checkRights=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
		}
		else
		{
			$bAdmin = "Y";
		}

		if ($bAdmin=="Y")
		{
			$strSql = "
				SELECT
					F.ID FILE_ID,
					M.TICKET_ID
				FROM
					b_ticket_message M
				LEFT JOIN b_ticket_message_2_file MF ON (MF.MESSAGE_ID = M.ID)
				LEFT JOIN b_file F ON (F.ID = MF.FILE_ID)
				WHERE
					M.ID='$id'
				";

			$z = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($zr = $z->Fetch())
			{
				$ticketID = $zr["TICKET_ID"];
				if (intval($zr["FILE_ID"])>0)
				{
					CFile::Delete($zr["FILE_ID"]);
				}
			}

			$z = $DB->Query("DELETE FROM b_ticket_message WHERE ID='$id'", false, $err_mess.__LINE__);
			if (intval($z->AffectedRowsCount())>0)
			{
				//CTicket::UpdateLastParams($ticketID);
				//CTicket::UpdateLastParams2($ticketID, CTicket::DELETE);
				CTicket::UpdateLastParamsN($ticketID, array("EVENT"=>array(CTicket::DELETE)), true, true);

				if (CSupportSearch::isIndexExists())
				{
					CSupportSearch::reindexTicket($ticketID);
				}
			}
		}
	}

	public static function UpdateMessage($messageID, $arFields, $checkRights="Y")
	{
		$err_mess = (CTicket::err_mess())."<br>Function: UpdateMessage<br>Line: ";
		global $DB, $USER;

		$messageID = intval($messageID);

		$bAdmin = "N";
		$bSupportTeam = "N";
		if ($checkRights=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
			$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
			$uid = $USER->GetID();
		}
		else
		{
			$bAdmin = "Y";
			$bSupportTeam = "Y";
			$uid = 0;
		}

		if ($bAdmin=="Y")
		{
			$ownerSid = $arFields["OWNER_SID"];
			$ownerUserID = $arFields["OWNER_USER_ID"];
			$arFields_u = array(
				"C_NUMBER"			=> intval($arFields["C_NUMBER"]),
				"MESSAGE"			=> $arFields["MESSAGE"],
				"MESSAGE_SEARCH"	=> ToUpper($arFields["MESSAGE"]),
				"SOURCE_ID"			=> (intval($arFields["SOURCE_ID"]) ? intval($arFields["SOURCE_ID"]) : false),
				"OWNER_SID"			=> $ownerSid,
				"OWNER_USER_ID"		=> (intval($ownerUserID)>0 ? intval($ownerUserID) : false),
				"MODIFIED_USER_ID"	=> (intval($uid)>0 ? intval($uid) : false),
				"MODIFIED_GUEST_ID"	=> (intval($_SESSION["SESS_GUEST_ID"])>0 ? intval($_SESSION["SESS_GUEST_ID"]) : false),
				"EXTERNAL_ID"		=> (intval($arFields["EXTERNAL_ID"])>0 ? $arFields["EXTERNAL_ID"] : false),
				"TASK_TIME"		=> (intval($arFields["TASK_TIME"])>0 ? intval($arFields["TASK_TIME"]) : "null"),
				"EXTERNAL_FIELD_1"	=> $arFields["EXTERNAL_FIELD_1"],
				"IS_SPAM"			=> (strlen($arFields["IS_SPAM"])>0 ? $arFields["IS_SPAM"] : false),
				"IS_HIDDEN"			=> ($arFields["IS_HIDDEN"]=="Y" ? "Y" : "N"),
				"IS_LOG"			=> ($arFields["IS_LOG"]=="Y" ? "Y" : "N"),
				"IS_OVERDUE"		=> ($arFields["IS_OVERDUE"]=="Y" ? "Y" : "N"),
				"NOT_CHANGE_STATUS" => ($arFields["NOT_CHANGE_STATUS"]=="Y" ? "Y" : "N")
				);

			$notChangeStatus = (
				is_set($arFields, "NOT_CHANGE_STATUS") && $arFields["NOT_CHANGE_STATUS"]=="Y"
				? "Y"
				: "N"
			);


			$strUpdate = $DB->PrepareUpdate("b_ticket_message", $arFields_u, "support");
			$strSql = "UPDATE b_ticket_message SET ".$strUpdate.", TIMESTAMP_X=sysdate WHERE ID=".$messageID;
			$arBinds["MESSAGE"] = $arFields_u["MESSAGE"];
			$arBinds["MESSAGE_SEARCH"] = $arFields_u["MESSAGE_SEARCH"];
			$DB->QueryBind($strSql, $arBinds);
			$rsMessage = CTicket::GetMessageByID($messageID, $checkRights);
			if ($arMessage = $rsMessage->Fetch())
			{
				$ticketID = $arMessage["TICKET_ID"];

				// обновим прикрепленные файлы
				$not_image_extension_suffix = COption::GetOptionString("support", "NOT_IMAGE_EXTENSION_SUFFIX");
				$not_image_upload_dir = COption::GetOptionString("support", "NOT_IMAGE_UPLOAD_DIR");
				$max_size = COption::GetOptionString("support", "SUPPORT_MAX_FILESIZE");

				$arrFiles = $arFields["FILES"];
				if (is_array($arrFiles) && count($arrFiles)>0)
				{
					foreach ($arrFiles as $arFile)
					{
						if (strlen($arFile["name"])>0 || $arFile["del"]=="Y")
						{
							if ($bSupportTeam!="Y" && $bAdmin!="Y") $max_file_size = intval($max_size)*1024;
							$fes = "";
							$upload_dir = "support";
							if (!CFile::IsImage($arFile["name"], $arFile["type"]))
							{
								$fes = $not_image_extension_suffix;
								$arFile["name"] .= $fes;
								$upload_dir = $not_image_upload_dir;
							}

							if (!array_key_exists("MODULE_ID", $arFile) || strlen($arFile["MODULE_ID"]) <= 0)
								$arFile["MODULE_ID"] = "support";

							$fid = intval(CFile::SaveFile($arFile, $upload_dir, $max_file_size));

							// если стоял флаг "Удалить" то
							if ($arFile["del"]=="Y")
							{
								// удалим связку
								$strSql = "
									DELETE FROM
										b_ticket_message_2_file
									WHERE
										FILE_ID=".intval($arFile["old_file"])."
									";
								$DB->Query($strSql, false, $err_mess.__LINE__);
							}

							// если успешно загрузили файл то
							if ($fid>0)
							{
								// если это был новый файл то
								if (intval($arFile["old_file"])<=0)
								{
									// добавим связку
									$md5 = md5(uniqid(mt_rand(), true).time());
									$arFields_fi = array(
										"HASH"				=> "'".$DB->ForSql($md5, 255)."'",
										"MESSAGE_ID"		=> $messageID,
										"FILE_ID"			=> $fid,
										"TICKET_ID"			=> $ticketID,
										"EXTENSION_SUFFIX"	=> (strlen($fes)>0) ? "'".$DB->ForSql($fes, 255)."'" : "null"
										);
									$DB->Insert("b_ticket_message_2_file",$arFields_fi, $err_mess.__LINE__);
								}
								else // иначе
								{
									// обновим связку
									$arFields_fu = array(
										"FILE_ID"			=> $fid,
										"EXTENSION_SUFFIX"	=> (strlen($fes)>0) ? "'".$DB->ForSql($fes, 255)."'" : "null"
										);
									$DB->Update("b_ticket_message_2_file", $arFields_fu, "WHERE FILE_ID = ".intval($arFile["old_file"]),$err_mess.__LINE__);
								}
							}
						}
					}
				}
				if ($arFields["IS_SPAM"]=="Y")
					CTicket::MarkMessageAsSpam($messageID,"Y",$checkRights);
				elseif ($arFields["IS_SPAM"]=="N")
					CTicket::MarkMessageAsSpam($messageID,"N",$checkRights);
				elseif ($arFields["IS_SPAM"]!="Y" && $arFields["IS_SPAM"]!="N")
					CTicket::UnMarkMessageAsSpam($messageID,$checkRights);

				//if ($notChangeStatus != "Y")
				//CTicket::UpdateLastParams($ticketID);
				//if ($notChangeStatus!="Y" && $hidden!="Y" && $log!="Y")
				//{
					//CTicketReminder::Update($ticketID);
				//}

				if (CSupportSearch::isIndexExists())
				{
					CSupportSearch::reindexTicket($ticketID);
				}
			}
		}
	}

	public static function AddMessage($ticketID, $arFields, &$arrFILES, $checkRights="Y")
	{
		if (strlen($arFields["MESSAGE"])>0 || (is_array($arFields["FILES"]) && count($arFields["FILES"])>0))
		{
			$err_mess = (CTicket::err_mess())."<br>Function: AddMessage<br>Line: ";
			global $DB, $USER;

			$bAdmin = "N";
			$bSupportTeam = "N";
			$bSupportClient = "N";
			if ($checkRights=="Y")
			{
				$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
				$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
				$bSupportClient = (CTicket::IsSupportClient()) ? "Y" : "N";
				$uid = intval($USER->GetID());
			}
			else
			{
				$bAdmin = "Y";
				$bSupportTeam = "Y";
				$bSupportClient = "Y";
				if (is_object($USER)) $uid = intval($USER->GetID()); else $uid = -1;
			}
			if ($bAdmin!="Y" && $bSupportTeam!="Y" && $bSupportClient!="Y") return false;

			$ticketID = intval($ticketID);
			if ($ticketID<=0) return 0;

			$strSql = "SELECT RESPONSIBLE_USER_ID, LAST_MESSAGE_USER_ID, REOPEN, SITE_ID, TITLE FROM b_ticket WHERE ID='$ticketID'";
			$rsTicket = $DB->Query($strSql, false, $err_mess.__LINE__);
			$arTicket = $rsTicket->Fetch();
			$currientResponsibleUserID = $arTicket["RESPONSIBLE_USER_ID"];
			$siteID = $arTicket["SITE_ID"];
			$T_TITLE = $arTicket["TITLE"];

			$strSql = "SELECT max(C_NUMBER) MAX_NUMBER FROM b_ticket_message WHERE TICKET_ID='$ticketID'";
			$z = $DB->Query($strSql, false, $err_mess.__LINE__);
			$zr = $z->Fetch();
			$maxNumber = intval($zr['MAX_NUMBER']);

			if ((strlen(trim($arFields["MESSAGE_AUTHOR_SID"]))>0 || intval($arFields["MESSAGE_AUTHOR_USER_ID"])>0 || intval($arFields["MESSAGE_CREATED_USER_ID"])>0) && ($bSupportTeam=="Y" || $bAdmin=="Y"))
			{
				$ownerUserID = intval($arFields["MESSAGE_AUTHOR_USER_ID"]);
				$ownerSid = $arFields["MESSAGE_AUTHOR_SID"];
				$ownerGuestID = intval($arFields["MESSAGE_AUTHOR_GUEST_ID"])>0 ? intval($arFields["MESSAGE_AUTHOR_GUEST_ID"]) : false;

				$createUserID = intval($arFields["MESSAGE_CREATED_USER_ID"])>0 ? intval($arFields["MESSAGE_CREATED_USER_ID"]) : intval($uid);
				$createdGuestID = intval($arFields["MESSAGE_CREATED_GUEST_ID"])>0 ? intval($arFields["MESSAGE_CREATED_GUEST_ID"]) : intval($_SESSION["SESS_GUEST_ID"]);
			}
			else
			{
				$ownerUserID = intval($uid);
				$ownerSid = false;
				$ownerGuestID = intval($_SESSION["SESS_GUEST_ID"]);

				$createUserID = intval($uid);
				$createdGuestID = intval($_SESSION["SESS_GUEST_ID"]);
			}

			if (intval($ownerGuestID)<=0) $ownerGuestID = false;

			$messageBySupportTeam = false;
			if ($ownerUserID<=0) $ownerUserID = false;
			else
			{
				$messageBySupportTeam = "N";
				if (CTicket::IsSupportTeam($ownerUserID) || CTicket::IsAdmin($ownerUserID))
				{
					$messageBySupportTeam = "Y";
				}
			}

			if ($createUserID<=0) $createUserID = false;
			if (intval($createdGuestID)<=0) $createdGuestID = false;

			$createdModuleName = (strlen($arFields["MESSAGE_CREATED_MODULE_NAME"])>0) ? $arFields["MESSAGE_CREATED_MODULE_NAME"] : "support";

			$externalID = intval($arFields["EXTERNAL_ID"])>0 ? intval($arFields["EXTERNAL_ID"]) : false;
			$externalField1 = (strlen($arFields["EXTERNAL_FIELD_1"])>0) ? $DB->ForSql($arFields["EXTERNAL_FIELD_1"],2000) : "null";

			if (is_set($arFields, "HIDDEN")) $hidden = ($arFields["HIDDEN"]=="Y") ? "Y" : "N";
			elseif (is_set($arFields, "IS_HIDDEN")) $hidden = ($arFields["IS_HIDDEN"]=="Y") ? "Y" : "N";
			$hidden = ($hidden=="Y") ? "Y" : "N";

			$notChangeStatus = (
				is_set($arFields, "NOT_CHANGE_STATUS") && $arFields["NOT_CHANGE_STATUS"]=="Y"
				? "Y"
				: "N"
			);

			$changeLastMessageDate = true;
			if ($arTicket["LAST_MESSAGE_USER_ID"] == $uid && $arTicket["REOPEN"] != "Y")
				$changeLastMessageDate = false;

			$taskTime = intval($arFields["TASK_TIME"])>0 ? intval($arFields["TASK_TIME"]) : "null";

			if (is_set($arFields, "LOG")) $log = ($arFields["LOG"]=="Y") ? "Y" : "N";
			elseif (is_set($arFields, "IS_LOG")) $log = ($arFields["IS_LOG"]=="Y") ? "Y" : "N";
			$log = ($log=="Y") ? "Y" : "N";

			if (is_set($arFields, "OVERDUE")) $overdue = ($arFields["OVERDUE"]=="Y") ? "Y" : "N";
			elseif (is_set($arFields, "IS_OVERDUE")) $overdue = ($arFields["IS_OVERDUE"]=="Y") ? "Y" : "N";
			$overdue = ($overdue=="Y") ? "Y" : "N";

			$arFieldsI = array(
				"C_NUMBER"						=> $maxNumber + 1,
				"TICKET_ID"						=> $ticketID,
				"IS_HIDDEN"						=> $hidden,
				"IS_LOG"						=> $log,
				"IS_OVERDUE"					=> $overdue,
				"MESSAGE"						=> $arFields["MESSAGE"],
				"MESSAGE_SEARCH"				=> ToUpper($arFields["MESSAGE"]),
				"EXTERNAL_ID"					=> $externalID,
				"EXTERNAL_FIELD_1"				=> $externalField1,
				"OWNER_USER_ID"					=> $ownerUserID,
				"OWNER_GUEST_ID"				=> $ownerGuestID,
				"OWNER_SID"						=> $ownerSid,
				"SOURCE_ID"						=> intval($arFields["MESSAGE_SOURCE_ID"]),
				"CREATED_USER_ID"				=> $createUserID,
				"CREATED_GUEST_ID"				=> $createdGuestID,
				"CREATED_MODULE_NAME"			=> $createdModuleName,
				"MODIFIED_USER_ID"				=> $createUserID,
				"MODIFIED_GUEST_ID"				=> $createdGuestID,
				"MESSAGE_BY_SUPPORT_TEAM"		=> $messageBySupportTeam,
				"CURRENT_RESPONSIBLE_USER_ID"	=> $currientResponsibleUserID,
				"TASK_TIME" => $taskTime,
				"NOT_CHANGE_STATUS" => $notChangeStatus
				);
			
			$arFieldsI["DATE_CREATE"] = GetTime( time() ,"FULL" );

			/*if ($hidden!="Y" && $log!="Y" && $changeLastMessageDate == false)
			{
				if ($messageBySupportTeam == "'Y'" || ($maxNumber <= 0 && array_key_exists('SOURCE_SID', $arFields) && $arFields['SOURCE_SID'] === 'email'))
					$arFieldsI["NOT_CHANGE_STATUS"] = "N";
				else
					$arFieldsI["NOT_CHANGE_STATUS"] = "Y";
			}*/

			if (intval($currientResponsibleUserID)>0) $arFieldsI["CURRENT_RESPONSIBLE_USER_ID"] = $currientResponsibleUserID;


			$arInsert = $DB->PrepareInsert("b_ticket_message", $arFieldsI, "support");
			$MID = $DB->NextID("sq_b_ticket_message");
			$strSql =
				"INSERT INTO b_ticket_message(ID, ".$arInsert[0].", TIMESTAMP_X, DAY_CREATE) ".
				"VALUES(".$MID.", ".$arInsert[1].", ".$DB->GetNowFunction().", ".$DB->CurrentDateFunction().")";
			$arBinds["MESSAGE"] = $arFieldsI["MESSAGE"];
			$arBinds["MESSAGE_SEARCH"] = $arFieldsI["MESSAGE_SEARCH"];
			$DB->QueryBind($strSql, $arBinds);
			if (intval($MID)>0)
			{
				$not_image_extension_suffix = COption::GetOptionString("support", "NOT_IMAGE_EXTENSION_SUFFIX");
				$not_image_upload_dir = COption::GetOptionString("support", "NOT_IMAGE_UPLOAD_DIR");
				$max_size = COption::GetOptionString("support", "SUPPORT_MAX_FILESIZE");
				// сохраняем приаттаченные файлы
				$arFILES = $arFields["FILES"];
				if (is_array($arFILES) && count($arFILES)>0)
				{
					while (list($key, $arFILE) = each($arFILES))
					{
						if (strlen($arFILE["name"])>0)
						{
							if ($bSupportTeam!="Y" && $bAdmin!="Y") $max_file_size = intval($max_size)*1024;
							$fes = "";
							$upload_dir = "support";
							if (!CFile::IsImage($arFILE["name"], $arFILE["type"]))
							{
								$fes = $not_image_extension_suffix;
								$arFILE["name"] .= $fes;
								$upload_dir = $not_image_upload_dir;
							}

							if (!array_key_exists("MODULE_ID", $arFILE) || strlen($arFILE["MODULE_ID"]) <= 0)
								$arFILE["MODULE_ID"] = "support";

							$fid = intval(CFile::SaveFile($arFILE, $upload_dir, $max_file_size));
							if ($fid>0)
							{
								$md5 = md5(uniqid(mt_rand(), true).time());
								$arFILE["HASH"] = $md5;
								$arFILE["FILE_ID"] = $fid;
								$arFILE["MESSAGE_ID"] = $MID;
								$arFILE["TICKET_ID"] = $ticketID;
								$arFILE["EXTENSION_SUFFIX"] = $fes;
								$arFields_fi = array(
									"HASH"				=> "'".$DB->ForSql($md5, 255)."'",
									"MESSAGE_ID"		=> $MID,
									"FILE_ID"			=> $fid,
									"TICKET_ID"			=> $ticketID,
									"EXTENSION_SUFFIX"	=> (strlen($fes)>0) ? "'".$DB->ForSql($fes, 255)."'" : "null"
									);
								$link_id = $DB->Insert("b_ticket_message_2_file",$arFields_fi, $err_mess.__LINE__);
								if (intval($link_id)>0)
								{
									$arFILE["LINK_ID"] = $link_id;
									$arrFILES[] = $arFILE;
								}
							}
						}
					}
				}

				/*
				// если это не было скрытым сообщением или сообщение лога, то
				if ($notChangeStatus!="Y" && $hidden!="Y" && $log!="Y")
				{
					// обновим ряд параметров обращения
					if (!isset($arFields["AUTO_CLOSE_DAYS"])) $RESET_AUTO_CLOSE = "Y";

					CTicket::UpdateLastParams($ticketID, $RESET_AUTO_CLOSE, $changeLastMessageDate, true);

					// при необходимости создадим или удалим агенты-напоминальщики
					//CTicketReminder::Update($ticketID);
				}*/
				
				if ( $log!="Y" && CSupportSearch::isIndexExists())
				{
					CSupportSearch::reindexTicket($ticketID);
				}

				//если была установлена галочка "не изменять статус обращени" - пересчитаем количество собщений
				if ($notChangeStatus == "Y" || $hidden == "Y")
					CTicket::UpdateMessages($ticketID);
			}
		}
		return $MID;
	}

	public static function GetStatus($ticketID)
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetStatus<br>Line: ";
		global $DB, $USER;

		$ticketID = intval($ticketID);
		if ($ticketID<=0) return false;

		$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
		$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
		$bSupportClient = (CTicket::IsSupportClient()) ? "Y" : "N";
		$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
		$uid = intval($USER->GetID());

		if ($bSupportTeam=="Y" || $bAdmin=="Y" || $bDemo=="Y")
		{
			$lamp = "
				decode(nvl(T.DATE_CLOSE,to_date('1980','YYYY')),
					to_date('1980','YYYY'), decode(T.LAST_MESSAGE_USER_ID,
												'$uid', 'green',
												decode(nvl(T.OWNER_USER_ID,0),
													'$uid', 'red',
													decode(T.LAST_MESSAGE_BY_SUPPORT_TEAM,
														'N', decode(nvl(T.RESPONSIBLE_USER_ID,0),
																'$uid', 'red',
																'yellow'),
														'green_s')
													)
												),
					'grey')
				";
		}
		else
		{
			$lamp = "
				decode(nvl(T.DATE_CLOSE,to_date('01.01.1980','DD.MM.YYYY')),
					to_date('01.01.1980','DD.MM.YYYY'),
						decode(T.LAST_MESSAGE_USER_ID, '$uid', 'green', 'red'),
					'grey'
					)
				";
		}

		$strSql = "
			SELECT
				$lamp	LAMP
			FROM
				b_ticket T
			WHERE
				ID = $ticketID
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		if ($ar = $rs->Fetch()) return $ar["LAMP"];

		return false;
	}

	public static function GetList(&$by, &$order, $arFilter=Array(), &$isFiltered, $checkRights="Y", $getUserName="Y", $getExtraNames="Y", $siteID=false, $arParams = Array() )
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB, $USER, $USER_FIELD_MANAGER;

		$d_join = "";
		$bAdmin = "N";
		$bSupportTeam = "N";
		$bSupportClient = "N";
		$bDemo = "N";
		if ($checkRights=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
			$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
			$bSupportClient = (CTicket::IsSupportClient()) ? "Y" : "N";
			$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
			$uid = intval($USER->GetID());
		}
		else
		{
			$bAdmin = "Y";
			$bSupportTeam = "Y";
			$bSupportClient = "Y";
			$bDemo = "Y";
			if (is_object($USER)) $uid = intval($USER->GetID()); else $uid = -1;
		}
		if ($bAdmin!="Y" && $bSupportTeam!="Y" && $bSupportClient!="Y" && $bDemo!="Y") return false;

		if ($bSupportTeam=="Y" || $bAdmin=="Y" || $bDemo=="Y")
		{
			$lamp = "
				decode(nvl(T.DATE_CLOSE,to_date('1980','YYYY')),
					to_date('1980','YYYY'), decode(T.LAST_MESSAGE_USER_ID,
												'$uid', 'green',
												decode(nvl(T.OWNER_USER_ID,0),
													'$uid', 'red',
													decode(T.LAST_MESSAGE_BY_SUPPORT_TEAM,
														'N', decode(nvl(T.RESPONSIBLE_USER_ID,0),
																'$uid', 'red',
																'yellow'),
														'green_s')
													)
												),
					'grey')
				";
		}
		else
		{
			$lamp = "
				decode(
					nvl(T.DATE_CLOSE,to_date('01.01.1980','DD.MM.YYYY')),
					to_date('01.01.1980','DD.MM.YYYY'),
					decode(
						T.LAST_MESSAGE_BY_SUPPORT_TEAM, 'Y', 'red', 'green'),
					'grey'
					)
				";
		}

		$bJoinSupportTeamTbl = $bJoinClientTbl = false;

		$arSqlSearch = Array();
		$strSqlSearch = "";
		$mess_join = "";
		$searchJoin = '';

		$need_group = false;
		$arSqlHaving = array();

		if (is_array($arFilter))
		{
			ResetFilterLogic();
			$filterKeys = array_keys($arFilter);
			$filterKeysCount = count($filterKeys);
			for ($i=0; $i<$filterKeysCount; $i++)
			{
				$key = $filterKeys[$i];
				$val = $arFilter[$filterKeys[$i]];
				if ((is_array($val) && count($val)<=0) || (!is_array($val) && (strlen($val)<=0 || $val==='NOT_REF')))
					continue;
				$matchValueSet = (in_array($key."_EXACT_MATCH", $filterKeys)) ? true : false;
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.ID",$val,$match);
						break;
					case "HOLD_ON":
						$arSqlSearch[] = ($val=="Y") ? "T.HOLD_ON='Y'" : "T.HOLD_ON = 'N'";
						break;
					case "LID":
					case "SITE":
					case "SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.SITE_ID",$val,$match);
						break;
					case "LAMP":
						if (is_array($val))
						{
							if (count($val)>0)
							{
								$str = "";
								foreach ($val as $value)
								{
									$str .= ", '".$DB->ForSQL($value)."'";
								}
								$str = TrimEx($str, ",");
								$arSqlSearch[] = " ".$lamp." in (".$str.")";
							}
						}
						elseif (strlen($val)>0)
						{
							$arSqlSearch[] = " ".$lamp." = '".$DB->ForSQL($val)."'";
						}
						break;
					case "DATE_CREATE_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CREATE>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_CREATE_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CREATE<".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
					case "DATE_TIMESTAMP_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.TIMESTAMP_X>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_TIMESTAMP_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.TIMESTAMP_X<".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
					case "DATE_CLOSE_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CLOSE>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_CLOSE_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CLOSE<".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
					case "CLOSE":
						$arSqlSearch[] = ($val=="Y") ? "T.DATE_CLOSE is not null" : "T.DATE_CLOSE is null";
						break;
					case "AUTO_CLOSE_DAYS1":
						$arSqlSearch[] = "T.AUTO_CLOSE_DAYS>='".intval($val)."'";
						break;
					case "AUTO_CLOSE_DAYS2":
						$arSqlSearch[] = "T.AUTO_CLOSE_DAYS<='".intval($val)."'";
						break;
					case "TICKET_TIME_1":
						$arSqlSearch[] = "ROUND(86400*(T.DATE_CLOSE - T.DATE_CREATE))>='".(intval($val)*86400)."'";
						break;
					case "TICKET_TIME_2":
						$arSqlSearch[] = "ROUND(86400*(T.DATE_CLOSE - T.DATE_CREATE))<='".(intval($val)*86400)."'";
						break;
					case "TITLE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $matchValueSet) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.TITLE", $val, $match);
						break;
					case "MESSAGES1":
						$arSqlSearch[] = "T.MESSAGES>='".intval($val)."'";
						break;
					case "MESSAGES2":
						$arSqlSearch[] = "T.MESSAGES<='".intval($val)."'";
						break;

					case "PROBLEM_TIME1":
						$arSqlSearch[] = "T.PROBLEM_TIME>='".intval($val)."'";
						break;
					case "PROBLEM_TIME2":
						$arSqlSearch[] = "T.PROBLEM_TIME<='".intval($val)."'";
						break;

					case "OVERDUE_MESSAGES1":
						$arSqlSearch[] = "T.OVERDUE_MESSAGES>='".intval($val)."'";
						break;
					case "OVERDUE_MESSAGES2":
						$arSqlSearch[] = "T.OVERDUE_MESSAGES<='".intval($val)."'";
						break;
					case "AUTO_CLOSE_DAYS_LEFT1":
						$arSqlSearch[] = "CASE WHEN CAST (T.DATE_CLOSE AS TIMESTAMP) IS NULL AND T.LAST_MESSAGE_BY_SUPPORT_TEAM = 'Y' THEN
							ROUND(T.LAST_MESSAGE_DATE + T.AUTO_CLOSE_DAYS - sysdate) ELSE -1 END >='".intval($val)."'";
						break;
					case "AUTO_CLOSE_DAYS_LEFT2":
						$arSqlSearch[] = "CASE WHEN CAST (T.DATE_CLOSE AS TIMESTAMP) IS NULL AND T.LAST_MESSAGE_BY_SUPPORT_TEAM = 'Y' THEN
							ROUND(T.LAST_MESSAGE_DATE + T.AUTO_CLOSE_DAYS - sysdate) ELSE 999 END <='".intval($val)."'";
						break;
					case "OWNER":
						$getUserName = "Y";
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $matchValueSet) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.OWNER_USER_ID, UO.LOGIN, UO.LAST_NAME, UO.NAME", $val, $match, array("@", "."));
						break;
					case "OWNER_USER_ID":
					case "OWNER_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.".$key, $val, $match);
						break;
					case "SLA_ID":
					case "SLA":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.SLA_ID", $val, $match);
						break;
					case "CREATED_BY":
						$getUserName = "Y";
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $matchValueSet) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.CREATED_USER_ID, UC.LOGIN, UC.LAST_NAME, UC.NAME, T.CREATED_MODULE_NAME", $val, $match);
						break;
					case "RESPONSIBLE":
						$getUserName = "Y";
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $matchValueSet) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.RESPONSIBLE_USER_ID, UR.LOGIN, UR.LAST_NAME, UR.NAME", $val, $match);
						break;
					case "RESPONSIBLE_ID":
						if (intval($val)>0) $arSqlSearch[] = "T.RESPONSIBLE_USER_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.RESPONSIBLE_USER_ID is null or T.RESPONSIBLE_USER_ID=0)";
						break;
					case "CATEGORY_ID":
					case "CATEGORY":
						if (intval($val)>0) $arSqlSearch[] = "T.CATEGORY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CATEGORY_ID is null or T.CATEGORY_ID=0)";
						break;
					case "CATEGORY_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DC.SID", $val, $match);
						$d_join = "
			LEFT JOIN b_ticket_dictionary DC ON (DC.ID = T.CATEGORY_ID and DC.C_TYPE = 'C')";
						break;
					case "CRITICALITY_ID":
					case "CRITICALITY":
						if (intval($val)>0) $arSqlSearch[] = "T.CRITICALITY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CRITICALITY_ID is null or T.CRITICALITY_ID=0)";
						break;
					case "CRITICALITY_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DK.SID", $val, $match);
						break;
					case "STATUS_ID":
					case "STATUS":
						if (intval($val)>0) $arSqlSearch[] = "T.STATUS_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.STATUS_ID is null or T.STATUS_ID=0)";
						break;
					case "STATUS_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DS.SID", $val, $match);
						break;
					case "MARK_ID":
					case "MARK":
						if (intval($val)>0) $arSqlSearch[] = "T.MARK_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.MARK_ID is null or T.MARK_ID=0)";
						break;
					case "MARK_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DM.SID", $val, $match);
						break;
					case "SOURCE_ID":
					case "SOURCE":
						if (intval($val)>0) $arSqlSearch[] = "T.SOURCE_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.SOURCE_ID is null or T.SOURCE_ID=0)";
						break;
					case "SOURCE_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DSR.SID", $val, $match);
						break;

					case "DIFFICULTY_ID":
					case "DIFFICULTY":
						if (intval($val)>0) $arSqlSearch[] = "T.DIFFICULTY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.DIFFICULTY_ID is null or T.DIFFICULTY_ID=0)";
						break;
					case "DIFFICULTY_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DD.SID", $val, $match);
						break;

					case "MODIFIED_BY":
						$getUserName = "Y";
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $matchValueSet) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.MODIFIED_USER_ID, T.MODIFIED_MODULE_NAME, UM.LOGIN,UM.LAST_NAME,UM.NAME", $val, $match);
						break;
					case "MESSAGE":
						/*$arSqlSearch_m[] = GetFilterQuery("M.MESSAGE_SEARCH", ToUpper($val),"N",array(),"Y","Y","Y");
						$messSearch = "Y";
						break;*/
						
						global $strError;
						if( strlen( $val ) <= 0 ) break;

						if(CSupportSearch::checkModule() && CSupportSearch::isIndexExists())
						{
							$searchSqlParams = CSupportSearch::getSql($val);
							$searchOn = $searchSqlParams['WHERE'];
							$searchHaving = $searchSqlParams['HAVING'];

							if ($searchOn)
							{
								$searchJoin = 'INNER JOIN b_ticket_search TS ON TS.TICKET_ID = T.ID AND '.$searchOn;

								if (!empty($searchHaving))
								{
									// 2 or more search words
									$arSqlHaving[] = $searchHaving;
									$need_group = true;
								}
							}
						}
						else
						{
							if ($bSupportTeam=="Y" || $bAdmin=="Y" || $bDemo=="Y")
							{
								$mess_join = "INNER JOIN b_ticket_message M ON (M.TICKET_ID=T.ID)";
							}
							else
							{
								$mess_join = "INNER JOIN b_ticket_message M ON (M.TICKET_ID=T.ID and M.IS_HIDDEN='N' and M.IS_LOG='N')";
							}

							$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $matchValueSet) ? "N" : "Y";
							$f = new CFilterQuery("OR", "yes", $match, array(), "N", "Y", "N");
							$query = $f->GetQueryString( "T.TITLE,M.MESSAGE_SEARCH", $val );
							$error = $f->error;
							if (strlen(trim($error))>0)
							{
								$strError .= $error."<br>";
								$query = "0";
							}
							else $arSqlSearch[] = $query;
						}
						break;
					case "LAST_MESSAGE_USER_ID":
					case "LAST_MESSAGE_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.".$key, $val, $match);
						break;
					case "SUPPORT_COMMENTS":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $matchValueSet) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.SUPPORT_COMMENTS", $val, $match);
						break;
					case "IS_SPAM":
						$arSqlSearch[] = ($val=="Y") ? "T.IS_SPAM='Y'" : "(T.IS_SPAM='N' or T.IS_SPAM is null)";
						break;
					case "IS_SPAM_MAYBE":
						$arSqlSearch[] = ($val=="Y") ? "T.IS_SPAM='N'" : "(T.IS_SPAM='Y' or T.IS_SPAM is null)";
						break;

					case 'SUPPORTTEAM_GROUP_ID':
					case 'CLIENT_GROUP_ID':
						if ($key == 'SUPPORTTEAM_GROUP_ID')
						{
							$table = 'UGS';
							$bJoinSupportTeamTbl = true;
						}
						else
						{
							$table = 'UGC';
							$bJoinClientTbl = true;
						}
						if (is_array($val))
						{
							$val = array_map('intval', $val);
							$val = array_unique($val);
							$val = array_filter($val);
							if (count($val) > 0)
							{
								$arSqlSearch[] = '('.$table.'.GROUP_ID IS NOT NULL AND '.$table.'.GROUP_ID IN ('.implode(',', $val).'))';
							}
						}
						else
						{
							$val = intval($val);
							if ($val > 0)
							{
								$arSqlSearch[] = '('.$table.'.GROUP_ID IS NOT NULL AND '.$table.'.GROUP_ID=\''.$val.'\')';
							}
						}
						break;
					case 'COUPON':
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.".$key, $val, $match);
						break;

				}
			}
		}
		
		$obUserFieldsSql = new CUserTypeSQL;
		$obUserFieldsSql->SetEntity("SUPPORT", "T.ID");
		$obUserFieldsSql->SetSelect( $arParams["SELECT"] );
		$obUserFieldsSql->SetFilter( $arFilter );
		$obUserFieldsSql->SetOrder( array( $by => $order) );

		if ($by == "s_id")								$strSqlOrder = "ORDER BY T.ID";
		elseif ($by == "s_last_message_date")		$strSqlOrder = "ORDER BY T.LAST_MESSAGE_DATE";
		elseif ($by == "s_site_id" || $by == "s_lid")	$strSqlOrder = "ORDER BY T.SITE_ID";
		elseif ($by == "s_lamp")						$strSqlOrder = "ORDER BY LAMP";
		elseif ($by == "s_is_overdue")					$strSqlOrder = "ORDER BY T.IS_OVERDUE";
		elseif ($by == "s_is_notified")					$strSqlOrder = "ORDER BY T.IS_NOTIFIED";
		elseif ($by == "s_date_create")					$strSqlOrder = "ORDER BY T.DATE_CREATE";
		elseif ($by == "s_timestamp")					$strSqlOrder = "ORDER BY T.TIMESTAMP_X";
		elseif ($by == "s_date_close")					$strSqlOrder = "ORDER BY T.DATE_CLOSE";
		elseif ($by == "s_owner")						$strSqlOrder = "ORDER BY T.OWNER_USER_ID";
		elseif ($by == "s_modified_by")					$strSqlOrder = "ORDER BY T.MODIFIED_USER_ID";
		elseif ($by == "s_title")						$strSqlOrder = "ORDER BY T.TITLE ";
		elseif ($by == "s_responsible")					$strSqlOrder = "ORDER BY T.RESPONSIBLE_USER_ID";
		elseif ($by == "s_messages")					$strSqlOrder = "ORDER BY T.MESSAGES";
		elseif ($by == "s_category")					$strSqlOrder = "ORDER BY T.CATEGORY_ID";
		elseif ($by == "s_criticality")					$strSqlOrder = "ORDER BY T.CRITICALITY_ID";
		elseif ($by == "s_sla")							$strSqlOrder = "ORDER BY T.SLA_ID";
		elseif ($by == "s_status")						$strSqlOrder = "ORDER BY T.STATUS_ID";
		elseif ($by == "s_difficulty")					$strSqlOrder = "ORDER BY T.DIFFICULTY_ID";
		elseif ($by == "s_problem_time")				$strSqlOrder = "ORDER BY T.PROBLEM_TIME";
		elseif ($by == "s_mark")						$strSqlOrder = "ORDER BY T.MARK_ID";
		elseif ($by == "s_online")						$strSqlOrder = "ORDER BY USERS_ONLINE";
		elseif ($by == "s_support_comments")			$strSqlOrder = "ORDER BY T.SUPPORT_COMMENTS";
		elseif ($by == "s_auto_close_days_left")		$strSqlOrder = "ORDER BY AUTO_CLOSE_DAYS_LEFT";
		elseif ($by == 's_coupon')						$strSqlOrder = 'ORDER BY T.COUPON';
		elseif ($by == 's_deadline')					$strSqlOrder = 'ORDER BY T.SUPPORT_DEADLINE';
		elseif( $s = $obUserFieldsSql->GetOrder($by) )	$strSqlOrder = "ORDER BY ".strtoupper($s);
		else
		{
			$by = "s_default";
			//$strSqlOrder = "ORDER BY T.IS_OVERDUE desc, T.IS_NOTIFIED desc, T.TIMESTAMP_X";
			$strSqlOrder = "ORDER BY IS_SUPER_TICKET DESC, T.IS_OVERDUE DESC, T.IS_NOTIFIED DESC, T.LAST_MESSAGE_DATE";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		
		$arSqlSearch[] = $obUserFieldsSql->GetFilter();
		
		if ($getUserName=="Y")
		{
			$u_select = "
				,
				UO.LOGIN													OWNER_LOGIN,
				UO.EMAIL													OWNER_EMAIL,
				nvl(UO.NAME,'')||' '||nvl(UO.LAST_NAME,'')					OWNER_NAME,
				UR.LOGIN													RESPONSIBLE_LOGIN,
				UR.EMAIL													RESPONSIBLE_EMAIL,
				nvl(UR.NAME,'')||' '||nvl(UR.LAST_NAME,'')					RESPONSIBLE_NAME,
				UM.LOGIN													MODIFIED_BY_LOGIN,
				UM.EMAIL													MODIFIED_BY_EMAIL,
				nvl(UM.NAME,'')||' '||nvl(UM.LAST_NAME,'')					MODIFIED_BY_NAME,
				UM.LOGIN													MODIFIED_LOGIN,
				UM.EMAIL													MODIFIED_EMAIL,
				nvl(UM.NAME,'')||' '||nvl(UM.LAST_NAME,'')					MODIFIED_NAME,
				UL.LOGIN													LAST_MESSAGE_LOGIN,
				UL.EMAIL													LAST_MESSAGE_EMAIL,
				nvl(UL.NAME,'')||' '||nvl(UL.LAST_NAME,'')					LAST_MESSAGE_NAME,
				UC.LOGIN													CREATED_LOGIN,
				UC.EMAIL													CREATED_EMAIL,
				nvl(UC.NAME,'')||' '||nvl(UC.LAST_NAME,'')					CREATED_NAME
			";
			$u_join = "
			LEFT JOIN b_user UO ON (UO.ID = T.OWNER_USER_ID)
			LEFT JOIN b_user UR ON (UR.ID = T.RESPONSIBLE_USER_ID)
			LEFT JOIN b_user UM ON (UM.ID = T.MODIFIED_USER_ID)
			LEFT JOIN b_user UL ON (UL.ID = T.LAST_MESSAGE_USER_ID)
			LEFT JOIN b_user UC ON (UC.ID = T.CREATED_USER_ID)
			";
			$u_group = "
				,
				UO.LOGIN, UO.EMAIL, UO.NAME, UO.LAST_NAME,
				UR.LOGIN, UR.EMAIL, UR.NAME, UR.LAST_NAME,
				UM.LOGIN, UM.EMAIL, UM.NAME, UM.LAST_NAME,
				UL.LOGIN, UL.EMAIL, UL.NAME, UL.LAST_NAME,
				UC.LOGIN, UC.EMAIL, UC.NAME, UC.LAST_NAME
			";
		}

		if ($getExtraNames=="Y")
		{
			$d_select = "
				,
				DC.NAME														CATEGORY_NAME,
				DC.DESCR													CATEGORY_DESC,
				DC.SID														CATEGORY_SID,
				DK.NAME														CRITICALITY_NAME,
				DK.DESCR													CRITICALITY_DESC,
				DK.SID														CRITICALITY_SID,
				DS.NAME														STATUS_NAME,
				DS.DESCR													STATUS_DESC,
				DS.SID														STATUS_SID,
				DM.NAME														MARK_NAME,
				DM.DESCR													MARK_DESC,
				DM.SID														MARK_SID,
				DSR.NAME													SOURCE_NAME,
				DSR.DESCR													SOURCE_DESC,
				DSR.SID														SOURCE_SID,
				DD.NAME													DIFFICULTY_NAME,
				DD.DESCR													DIFFICULTY_DESC,
				DD.SID														DIFFICULTY_SID,
				SLA.NAME													SLA_NAME
			";
			$d_join = "
			LEFT JOIN b_ticket_dictionary DC ON (DC.ID = T.CATEGORY_ID and DC.C_TYPE = 'C')
			LEFT JOIN b_ticket_dictionary DK ON (DK.ID = T.CRITICALITY_ID and DK.C_TYPE = 'K')
			LEFT JOIN b_ticket_dictionary DS ON (DS.ID = T.STATUS_ID and DS.C_TYPE = 'S')
			LEFT JOIN b_ticket_dictionary DM ON (DM.ID = T.MARK_ID and DM.C_TYPE = 'M')
			LEFT JOIN b_ticket_dictionary DSR ON (DSR.ID = T.SOURCE_ID and DSR.C_TYPE = 'SR')
			LEFT JOIN b_ticket_dictionary DD ON (DD.ID = T.DIFFICULTY_ID and DD.C_TYPE = 'D')
			LEFT JOIN b_ticket_sla SLA ON (SLA.ID = T.SLA_ID)
			";
			$d_group = "
				,
				DC.NAME, DC.DESCR, DC.SID,
				DK.NAME, DK.DESCR, DK.SID,
				DS.NAME, DS.DESCR, DS.SID,
				DM.NAME, DM.DESCR, DM.SID,
				DSR.NAME, DSR.DESCR, DSR.SID,
				DD.NAME, DD.DESCR, DD.SID,
				SLA.NAME
			";

		}
		if (strlen($siteID)>0)
		{
			$dates_select = "
				".$DB->DateToCharFunction("T.DATE_CREATE","FULL",$siteID,true)."	DATE_CREATE_FULL,
				".$DB->DateToCharFunction("T.TIMESTAMP_X","FULL",$siteID,true)."	TIMESTAMP_X_FULL,
				".$DB->DateToCharFunction("T.LAST_MESSAGE_DATE","FULL",$siteID,true)." LAST_MESSAGE_DATE_FULL,
				".$DB->DateToCharFunction("T.DATE_CLOSE","FULL",$siteID,true)."	DATE_CLOSE_FULL,
				".$DB->DateToCharFunction("T.DATE_CREATE","SHORT",$siteID,true)."	DATE_CREATE_SHORT,
				".$DB->DateToCharFunction("T.TIMESTAMP_X","SHORT",$siteID,true)."	TIMESTAMP_X_SHORT,
				".$DB->DateToCharFunction("T.DATE_CLOSE","SHORT",$siteID,true)."	DATE_CLOSE_SHORT,
				".$DB->DateToCharFunction("T.SUPPORT_DEADLINE","FULL",$siteID,true)."	SUPPORT_DEADLINE_FULL,
				CASE WHEN CAST (T.DATE_CLOSE AS TIMESTAMP) IS NULL AND T.LAST_MESSAGE_BY_SUPPORT_TEAM = 'Y' THEN ".$DB->DateToCharFunction("
					T.LAST_MESSAGE_DATE + T.AUTO_CLOSE_DAYS","FULL",$siteID,true)."	ELSE NULL END	AUTO_CLOSE_DATE
			";
		}
		else
		{
			$dates_select = "
				".$DB->DateToCharFunction("T.DATE_CREATE","FULL")."		DATE_CREATE_FULL,
				".$DB->DateToCharFunction("T.TIMESTAMP_X","FULL")."		TIMESTAMP_X_FULL,
				".$DB->DateToCharFunction("T.LAST_MESSAGE_DATE","FULL")." LAST_MESSAGE_DATE_FULL,
				".$DB->DateToCharFunction("T.DATE_CLOSE","FULL")."		DATE_CLOSE_FULL,
				".$DB->DateToCharFunction("T.DATE_CREATE","SHORT")."	DATE_CREATE_SHORT,
				".$DB->DateToCharFunction("T.TIMESTAMP_X","SHORT")."	TIMESTAMP_X_SHORT,
				".$DB->DateToCharFunction("T.DATE_CLOSE","SHORT")."		DATE_CLOSE_SHORT,
				".$DB->DateToCharFunction("T.SUPPORT_DEADLINE","FULL")."	SUPPORT_DEADLINE_FULL,
				CASE WHEN CAST (T.DATE_CLOSE AS TIMESTAMP) IS NULL AND T.LAST_MESSAGE_BY_SUPPORT_TEAM = 'Y' THEN ".$DB->DateToCharFunction("
					T.LAST_MESSAGE_DATE + T.AUTO_CLOSE_DAYS","FULL")." ELSE NULL END		AUTO_CLOSE_DATE
			";
		}

		// dates aliases
		$arReplacedDateAliases = array(
			'DATE_CREATE_FULL' => 'DATE_CREATE',
			'TIMESTAMP_X_FULL' => 'TIMESTAMP_X',
			'LAST_MESSAGE_DATE_FULL' => 'LAST_MESSAGE_DATE',
			'DATE_CLOSE_FULL' => 'DATE_CLOSE',
			'SUPPORT_DEADLINE_FULL' => 'SUPPORT_DEADLINE'
		);

		$ugroupJoin = '';

		if ($bJoinSupportTeamTbl)
		{
			$ugroupJoin .= "
			LEFT JOIN b_ticket_user_ugroup UGS ON (UGS.USER_ID = T.RESPONSIBLE_USER_ID) ";
			$need_group = true;
		}

		if ($bJoinClientTbl)
		{
			$ugroupJoin .= "
			LEFT JOIN b_ticket_user_ugroup UGC ON (UGC.USER_ID = T.OWNER_USER_ID) ";
			$need_group = true;
		}

		/** @var string $_group SQL GROUP BY */
		$_group = "";

		if ($need_group || $d_group || $u_group)
		{
			$_group = "GROUP BY
				T.ID,
				T.SITE_ID,
				T.DATE_CREATE,
				T.DAY_CREATE,
				T.TIMESTAMP_X,
				T.DATE_CLOSE,
				T.AUTO_CLOSED,
				T.AUTO_CLOSE_DAYS,
				T.CATEGORY_ID,
				T.CRITICALITY_ID,
				T.STATUS_ID,
				T.MARK_ID,
				T.DIFFICULTY_ID,
				T.PROBLEM_TIME,
				T.SLA_ID,
				T.NOTIFY_AGENT_ID,
				T.EXPIRE_AGENT_ID,
				T.OVERDUE_MESSAGES,
				T.IS_NOTIFIED,
				T.IS_OVERDUE,
				T.SOURCE_ID,
				T.TITLE,
				T.MESSAGES,
				T.IS_SPAM,
				T.OWNER_USER_ID,
				T.OWNER_GUEST_ID,
				T.OWNER_SID,
				T.CREATED_USER_ID,
				T.CREATED_GUEST_ID,
				T.CREATED_MODULE_NAME,
				T.RESPONSIBLE_USER_ID,
				T.MODIFIED_USER_ID,
				T.MODIFIED_GUEST_ID,
				T.MODIFIED_MODULE_NAME,
				T.LAST_MESSAGE_USER_ID,
				T.LAST_MESSAGE_GUEST_ID,
				T.LAST_MESSAGE_SID,
				T.LAST_MESSAGE_BY_SUPPORT_TEAM,
				T.LAST_MESSAGE_DATE,
				T.SUPPORT_COMMENTS,
				T.HOLD_ON,
				T.REOPEN,
				T.COUPON,
				T.SUPPORT_DEADLINE,
				T.SUPPORT_DEADLINE_NOTIFY,
				T.D_1_USER_M_AFTER_SUP_M,
				T.ID_1_USER_M_AFTER_SUP_M,
				T.DEADLINE_SOURCE_DATE,
				case
					when T.COUPON is not null
						then 1
						else 0
				end
				$u_group
				$d_group
			";
		}

		// add permissions check
		if (!($bAdmin == 'Y' || $bDemo == 'Y'))
		{
			// a list of users who own or are responsible for tickets, which we can show to our current user
			$ticketUsers = array($uid);

			// check if user has groups
			$result = $DB->Query('SELECT GROUP_ID FROM b_ticket_user_ugroup WHERE USER_ID = '.$uid.' AND CAN_VIEW_GROUP_MESSAGES = \'Y\'');
			if ($result)
			{
				// collect members of these groups
				$uGroups = array();

				while ($row = $result->Fetch())
				{
					$uGroups[] = $row['GROUP_ID'];
				}

				if (!empty($uGroups))
				{
					$result = $DB->Query('SELECT USER_ID FROM b_ticket_user_ugroup WHERE GROUP_ID IN ('.join(',', $uGroups).')');
					if ($result)
					{
						while ($row = $result->Fetch())
						{
							$ticketUsers[] = $row['USER_ID'];
						}
					}
				}
			}

			// build sql
			$strSqlSearchUser = "";

			if($bSupportTeam == 'Y')
			{
				$strSqlSearchUser = 'T.RESPONSIBLE_USER_ID IN ('.join(',', $ticketUsers).')';
			}
			elseif ($bSupportClient == 'Y')
			{
				$strSqlSearchUser = 'T.OWNER_USER_ID IN ('.join(',', $ticketUsers).')';
			}

			$arSqlSearch[] = $strSqlSearchUser;
		}


		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$onlineInterval = intval(COption::GetOptionString("support", "ONLINE_INTERVAL"));

		$strSqlSelect = "
			SELECT
				T.*,
				T.SITE_ID												LID,
				$dates_select ,
				ROUND(86400*(T.DATE_CLOSE-T.DATE_CREATE))				TICKET_TIME,
				CASE WHEN CAST (T.DATE_CLOSE AS TIMESTAMP) IS NULL AND T.LAST_MESSAGE_BY_SUPPORT_TEAM = 'Y' THEN
					ROUND(T.TIMESTAMP_X + T.AUTO_CLOSE_DAYS - sysdate) ELSE -1 END		AUTO_CLOSE_DAYS_LEFT,
				(SELECT COUNT(DISTINCT USER_ID) FROM b_ticket_online WHERE TICKET_ID = T.ID AND TIMESTAMP_X >= SYSDATE - $onlineInterval/86400)		USERS_ONLINE,
				case
					when T.COUPON is not null
						then 1
						else 0
				end 													IS_SUPER_TICKET,
				$lamp													LAMP
				$u_select
				$d_select
				" . $obUserFieldsSql->GetSelect();

		$strSqlFrom = "
			FROM
				b_ticket T
			$u_join
			$d_join
			$mess_join
			$searchJoin
			$ugroupJoin
			" . $obUserFieldsSql->GetJoin("T.ID");

		$strSqlWhere = "
			WHERE
			$strSqlSearch
		";

		$strSqlGroup = $_group;

		$strSqlHaving = $arSqlHaving ? ' HAVING ' . join(' AND ', $arSqlHaving) . ' ' : '';

		$strSql = $strSqlSelect . $strSqlFrom . $strSqlWhere . $strSqlGroup . $strSqlHaving . $strSqlOrder;

		if (is_array($arParams) && isset($arParams["NAV_PARAMS"]) && is_array($arParams["NAV_PARAMS"]))
		{
			$nTopCount = isset($arParams['NAV_PARAMS']['nTopCount']) ? intval($arParams['NAV_PARAMS']['nTopCount']) : 0;

			if($nTopCount > 0)
			{
				$strSql = $DB->TopSql($strSql, $nTopCount);
				$res = $DB->Query($strSql, false, $err_mess.__LINE__);
				$res->SetUserFields( $USER_FIELD_MANAGER->GetUserFields("SUPPORT") );
			}
			else
			{
				$cntSql = "SELECT COUNT(T.ID) as C " . $strSqlFrom . $strSqlWhere . $strSqlGroup . $strSqlHaving;

				if (!empty($strSqlGroup))
				{
					$cntSql = 'SELECT COUNT(1) AS C FROM ('.$cntSql.') tt';
				}

				$res_cnt = $DB->Query($cntSql);
				$res_cnt = $res_cnt->Fetch();
				$res = new CDBResult();
				$res->SetUserFields( $USER_FIELD_MANAGER->GetUserFields("SUPPORT") );
				$res->arReplacedAliases = $arReplacedDateAliases;
				$res->NavQuery($strSql, $res_cnt["C"], $arParams["NAV_PARAMS"]);
			}
		}
		else
		{
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			$res->SetUserFields( $USER_FIELD_MANAGER->GetUserFields("SUPPORT") );
		}

		//and	(T.OWNER_USER_ID='$uid' or T.RESPONSIBLE_USER_ID='$uid' or '$bAdmin'='Y' or '$bDemo'='Y')
		$isFiltered = IsFiltered($strSqlSearch);

		// set dates aliases
		$res->arReplacedAliases = $arReplacedDateAliases;

		return $res;
	}

	public static function GetSupportTeamList()
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetSupportTeamList<br>Line: ";
		global $DB;
		$arrGid = CTicket::GetSupportTeamGroups();
		$arrAid = CTicket::GetAdminGroups();
		if (count($arrGid)>0)
		{
			$gid = implode(",",$arrGid);
		}
		else
		{
			$gid = 0;
		}
		if (count($arrAid)>0)
		{
			$aid = implode(",",$arrAid);
		}
		else
		{
			$aid = 0;
		}
		$strSql = "
			SELECT DISTINCT
				U.ID as REFERENCE_ID,
				nvl(U.LAST_NAME,'') || ' ' || nvl(U.NAME,'') || ' ('||U.LOGIN||') ' || '['||U.ID||']' as REFERENCE,
				nvl(U.LAST_NAME,''), nvl(U.NAME,''), U.LOGIN, U.ACTIVE
			FROM
				b_user U,
				b_user_group G
			WHERE
				U.ID = G.USER_ID
			and G.GROUP_ID in ($gid, $aid)
			ORDER BY nvl(U.LAST_NAME,''), nvl(U.NAME,''), U.LOGIN
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	/*function GetResponsibleList($user_id)
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetSupportTeamMailList<br>Line: ";
		global $DB;

		$strSql = "
			SELECT DISTINCT
				U.ID as ID,
				U.LOGIN as LOGIN,
				nvl(U.LAST_NAME,'') || ' ' || nvl(U.NAME,'') || ' ('||U.LOGIN||') ' || '['||U.ID||']' as NAME,
				U.EMAIL as EMAIL
			FROM
				b_user U,
				b_ticket_user_ugroup TUG,
				b_ticket_user_ugroup TUG2
			WHERE
				TUG.USER_ID = '".intval($user_id)."'
			and TUG2.GROUP_ID = TUG.GROUP_ID
			and U.ID = TUG2.USER_ID
			and TUG2.CAN_MAIL_GROUP_MESSAGES = 'Y'
			ORDER BY
				U.ID
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}*/

	public static function GetMessageList(&$by, &$order, $arFilter=Array(), &$isFiltered, $checkRights="Y", $getUserName="Y")
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetMessageList<br>Line: ";
		global $DB, $USER, $APPLICATION;

		$bAdmin = "N";
		$bSupportTeam = "N";
		$bSupportClient = "N";
		$bDemo = "N";
		if ($checkRights=="Y")
		{
			$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
			$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
			$bSupportClient = (CTicket::IsSupportClient()) ? "Y" : "N";
			$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
			$uid = intval($USER->GetID());
		}
		else
		{
			$bAdmin = "Y";
			$bSupportTeam = "Y";
			$bSupportClient = "Y";
			$bDemo = "Y";
			$uid = 0;
		}
		if ($bAdmin!="Y" && $bSupportTeam!="Y" && $bSupportClient!="Y" && $bDemo!="Y") return false;

		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			$filterKeys = array_keys($arFilter);
			$filterKeysCount = count($filterKeys);
			for ($i=0; $i<$filterKeysCount; $i++)
			{
				$key = $filterKeys[$i];
				$val = $arFilter[$filterKeys[$i]];
				if ((is_array($val) && count($val)<=0) || (!is_array($val) && (strlen($val)<=0 || $val==='NOT_REF')))
					continue;
				$matchValueSet = (in_array($key."_EXACT_MATCH", $filterKeys)) ? true : false;
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("M.ID",$val,$match);
						break;
					case "TICKET_ID":
						$arSqlSearch[] = "M.TICKET_ID = ".intval($val);
						break;
					case "TICKET":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("M.TICKET_ID",$val,$match);
						break;
					case "IS_MESSAGE":
						$arSqlSearch[] = ($val=="Y") ? "(M.IS_HIDDEN = 'N' and M.IS_LOG='N' and M.IS_OVERDUE='N')" : "(M.IS_HIDDEN = 'Y' or M.IS_LOG='Y' or M.IS_OVERDUE='Y')";
						break;
					case "IS_HIDDEN":
					case "IS_LOG":
					case "IS_OVERDUE":
					case "MESSAGE_BY_SUPPORT_TEAM":
						$arSqlSearch[] = ($val=="Y") ? "M.".$key."='Y'" : "M.".$key."='N'";
						break;
					case "EXTERNAL_FIELD_1":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $matchValueSet) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("M.EXTERNAL_FIELD_1", $val, $match);
						break;
				}
			}
		}

		if ($getUserName=="Y")
		{
			$u_select = "
				,
				UO.EMAIL												OWNER_EMAIL,
				UO.LOGIN												OWNER_LOGIN,
				nvl(UO.NAME,'')||' '||nvl(UO.LAST_NAME,'')				OWNER_NAME,
				UO.LOGIN												LOGIN,
				nvl(UO.NAME,'')||' '||nvl(UO.LAST_NAME,'')				NAME,
				UC.EMAIL												CREATED_EMAIL,
				UC.LOGIN												CREATED_LOGIN,
				nvl(UC.NAME,'')||' '||nvl(UC.LAST_NAME,'')				CREATED_NAME,
				UM.EMAIL												MODIFIED_EMAIL,
				UM.LOGIN												MODIFIED_LOGIN,
				nvl(UM.NAME,'')||' '||nvl(UM.LAST_NAME,'')				MODIFIED_NAME
				";
			$u_join = "
			LEFT JOIN b_user UO ON (UO.ID = M.OWNER_USER_ID)
			LEFT JOIN b_user UC ON (UC.ID = M.CREATED_USER_ID)
			LEFT JOIN b_user UM ON (UM.ID = M.MODIFIED_USER_ID)
			";
		}

		if ($bSupportTeam!="Y" && $bAdmin!="Y")
		{
			$arSqlSearch[] = "M.IS_HIDDEN='N'";
			$arSqlSearch[] = "M.IS_LOG='N'";
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

		if ($by == "s_id")			$strSqlOrder = "ORDER BY M.ID";
		elseif ($by == "s_number")	$strSqlOrder = "ORDER BY M.C_NUMBER";
		else
		{
			$by = "s_number";
			$strSqlOrder = "ORDER BY M.C_NUMBER";
		}
		if ($order=="desc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		else
		{
			$strSqlOrder .= " asc ";
			$order="asc";
		}

		$strSql = "
			SELECT
				M.*,
				T.SLA_ID,
				".$DB->DateToCharFunction("M.DATE_CREATE")."	DATE_CREATE,
				".$DB->DateToCharFunction("M.TIMESTAMP_X")."	TIMESTAMP_X,
				DS.NAME											SOURCE_NAME
				$u_select
			FROM
				b_ticket_message M
			INNER JOIN b_ticket T ON (T.ID = M.TICKET_ID)
			LEFT JOIN b_ticket_dictionary DS ON (DS.ID = M.SOURCE_ID)
			$u_join
			WHERE
				$strSqlSearch
			$strSqlOrder
			";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetDynamicList(&$by, &$order, $arFilter=Array())
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetDynamicList<br>Line: ";
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			$filterKeys = array_keys($arFilter);
			$filterKeysCount = count($filterKeys);
			for ($i=0; $i<$filterKeysCount; $i++)
			{
				$key = $filterKeys[$i];
				$val = $arFilter[$filterKeys[$i]];
				if ((is_array($val) && count($val)<=0) || (!is_array($val) && (strlen($val)<=0 || $val==='NOT_REF')))
					continue;
				$matchValueSet = (in_array($key."_EXACT_MATCH", $filterKeys)) ? true : false;
				$key = strtoupper($key);
				switch($key)
				{
					case "DATE_CREATE_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CREATE>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_CREATE_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "T.DATE_CREATE<".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
					case "RESPONSIBLE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $matchValueSet) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("T.RESPONSIBLE_USER_ID, UR.LOGIN, UR.LAST_NAME, UR.NAME", $val, $match);
						break;
					case "RESPONSIBLE_ID":
						if (intval($val)>0) $arSqlSearch[] = "T.RESPONSIBLE_USER_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.RESPONSIBLE_USER_ID is null or T.RESPONSIBLE_USER_ID=0)";
						break;
					case "SLA_ID":
					case "SLA":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.SLA_ID", $val, $match);
						break;
					case "CATEGORY_ID":
					case "CATEGORY":
						if (intval($val)>0) $arSqlSearch[] = "T.CATEGORY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CATEGORY_ID is null or T.CATEGORY_ID=0)";
						break;
					case "CRITICALITY_ID":
					case "CRITICALITY":
						if (intval($val)>0) $arSqlSearch[] = "T.CRITICALITY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CRITICALITY_ID is null or T.CRITICALITY_ID=0)";
						break;
					case "STATUS_ID":
					case "STATUS":
						if (intval($val)>0) $arSqlSearch[] = "T.STATUS_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.STATUS_ID is null or T.STATUS_ID=0)";
						break;
					case "MARK_ID":
					case "MARK":
						if (intval($val)>0) $arSqlSearch[] = "T.MARK_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.MARK_ID is null or T.MARK_ID=0)";
						break;
					case "SOURCE_ID":
					case "SOURCE":
						if (intval($val)>0) $arSqlSearch[] = "T.SOURCE_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.SOURCE_ID is null or T.SOURCE_ID=0)";
						break;
					case "DIFFICULTY_ID":
					case "DIFFICULTY":
						if (intval($val)>0) $arSqlSearch[] = "T.DIFFICULTY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.DIFFICULTY_ID is null or T.DIFFICULTY_ID=0)";
						break;
				}
			}
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if ($by == "s_date_create") $strSqlOrder = "ORDER BY max(T.DATE_CREATE)";
		else
		{
			$by = "s_date_create";
			$strSqlOrder = "ORDER BY max(T.DATE_CREATE)";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		$strSql = "
			SELECT
				count(T.ID)												ALL_TICKETS,
				decode(nvl(max(T.DATE_CLOSE),to_date('1980','YYYY')),
					to_date('1980','YYYY'),1,0)							OPEN_TICKETS,
				decode(nvl(max(T.DATE_CLOSE),to_date('1980','YYYY')),
					to_date('1980','YYYY'),0,1)							CLOSE_TICKETS,
				to_char(max(T.DAY_CREATE),'dd')							CREATE_DAY,
				to_char(max(T.DAY_CREATE),'mm')							CREATE_MONTH,
				to_char(max(T.DAY_CREATE),'yyyy')						CREATE_YEAR
			FROM
				b_ticket T
			LEFT JOIN b_user UR ON (T.RESPONSIBLE_USER_ID = UR.ID)
			WHERE
			$strSqlSearch
			and	T.DAY_CREATE is not null
			GROUP BY
				trunc(T.DAY_CREATE)
			$strSqlOrder
			";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetMessageDynamicList(&$by, &$order, $arFilter=Array())
	{
		$err_mess = (CTicket::err_mess())."<br>Function: GetMessageDynamicList<br>Line: ";
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			$filterKeys = array_keys($arFilter);
			$filterKeysCount = count($filterKeys);
			for ($i=0; $i<$filterKeysCount; $i++)
			{
				$key = $filterKeys[$i];
				$val = $arFilter[$filterKeys[$i]];
				if ((is_array($val) && count($val)<=0) || (!is_array($val) && (strlen($val)<=0 || $val==='NOT_REF')))
					continue;
				$matchValueSet = (in_array($key."_EXACT_MATCH", $filterKeys)) ? true : false;
				$key = strtoupper($key);
				switch($key)
				{
					case "SITE":
					case "SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.SITE_ID",$val,$match);
						break;
					case "DATE_CREATE_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "M.DATE_CREATE>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_CREATE_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "M.DATE_CREATE<".$DB->CharToDateFunction($val, "SHORT")."+1";
						break;
					case "OWNER":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $matchValueSet) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("M.OWNER_USER_ID, U.LOGIN, U.LAST_NAME, U.NAME", $val, $match);
						break;
					case "OWNER_ID":
						if (intval($val)>0) $arSqlSearch[] = "M.OWNER_USER_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(M.OWNER_USER_ID is null or M.OWNER_USER_ID=0)";
						break;
					case "IS_HIDDEN":
					case "IS_LOG":
					case "IS_OVERDUE":
						$arSqlSearch[] = ($val=="Y") ? "M.".$key."='Y'" : "M.".$key."='N'";
						break;
					case "SLA_ID":
					case "SLA":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("T.SLA_ID", $val, $match);
						break;
					case "CATEGORY_ID":
					case "CATEGORY":
						if (intval($val)>0) $arSqlSearch[] = "T.CATEGORY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CATEGORY_ID is null or T.CATEGORY_ID=0)";
						break;
					case "CRITICALITY_ID":
					case "CRITICALITY":
						if (intval($val)>0) $arSqlSearch[] = "T.CRITICALITY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.CRITICALITY_ID is null or T.CRITICALITY_ID=0)";
						break;
					case "STATUS_ID":
					case "STATUS":
						if (intval($val)>0) $arSqlSearch[] = "T.STATUS_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.STATUS_ID is null or T.STATUS_ID=0)";
						break;
					case "MARK_ID":
					case "MARK":
						if (intval($val)>0) $arSqlSearch[] = "T.MARK_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.MARK_ID is null or T.MARK_ID=0)";
						break;
					case "SOURCE_ID":
					case "SOURCE":
						if (intval($val)>0) $arSqlSearch[] = "T.SOURCE_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.SOURCE_ID is null or T.SOURCE_ID=0)";
						break;

					case "DIFFICULTY_ID":
					case "DIFFICULTY":
						if (intval($val)>0) $arSqlSearch[] = "T.DIFFICULTY_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(T.DIFFICULTY_ID is null or T.DIFFICULTY_ID=0)";
						break;
				}
			}
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if ($by == "s_date_create") $strSqlOrder = "ORDER BY max(M.DATE_CREATE)";
		else
		{
			$by = "s_date_create";
			$strSqlOrder = "ORDER BY max(M.DATE_CREATE)";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		$strSql = "
			SELECT
				count(M.ID)									COUNTER,
				sum(decode(M.EXPIRE_AGENT_DONE, 'Y', 1, 0))	COUNTER_OVERDUE,
				to_char(max(M.DAY_CREATE),'dd')				CREATE_DAY,
				to_char(max(M.DAY_CREATE),'mm')				CREATE_MONTH,
				to_char(max(M.DAY_CREATE),'yyyy')			CREATE_YEAR
			FROM
				b_ticket_message M
			INNER JOIN b_ticket T ON (T.ID = M.TICKET_ID)
			LEFT JOIN b_user U ON (M.OWNER_USER_ID = U.ID)
			WHERE
			$strSqlSearch
			GROUP BY
				trunc(M.DAY_CREATE)
			$strSqlOrder
			";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}
}
