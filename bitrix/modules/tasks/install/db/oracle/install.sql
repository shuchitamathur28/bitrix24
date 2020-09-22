CREATE TABLE b_tasks (
	ID number(11) NOT NULL,
	TITLE varchar(255 char),
	DESCRIPTION clob,
	DESCRIPTION_IN_BBCODE char(1 char) DEFAULT 'N' NOT NULL,
	PRIORITY char(1 char) DEFAULT '1' NOT NULL,
	STATUS char(1 char),
	RESPONSIBLE_ID number(11),
	DATE_START date,
	DURATION_PLAN number(11),
	DURATION_FACT number(11),
	DURATION_TYPE varchar(5 char) DEFAULT 'days' NOT NULL,
	TIME_ESTIMATE number(11) DEFAULT 0 NOT NULL,
	REPLICATE char(1 char) DEFAULT 'N' NOT NULL,
	DEADLINE date,
	START_DATE_PLAN date,
	END_DATE_PLAN date,
	CREATED_BY number(11),
	CREATED_DATE date,
	CHANGED_BY number(11),
	CHANGED_DATE date,
	STATUS_CHANGED_BY number(11),
	STATUS_CHANGED_DATE date,
	CLOSED_BY number(11),
	CLOSED_DATE date,
	GUID varchar(50 char),
	XML_ID varchar(50 char),
	EXCHANGE_ID varchar(196 char),
	EXCHANGE_MODIFIED varchar(196 char),
	OUTLOOK_VERSION number(11),
	MARK char(1 char),
	ALLOW_CHANGE_DEADLINE char(1 char) DEFAULT 'N' NOT NULL,
	ALLOW_TIME_TRACKING char(1 char) DEFAULT 'N' NOT NULL,
	MATCH_WORK_TIME char(1 char) DEFAULT 'N' NOT NULL,
	TASK_CONTROL char(1 char) DEFAULT 'N' NOT NULL,
	ADD_IN_REPORT char(1 char) DEFAULT 'N' NOT NULL,
	GROUP_ID number(11),
	PARENT_ID number(11),
	FORUM_TOPIC_ID number(20),
	MULTITASK char(1 char) DEFAULT 'N' NOT NULL,
	SITE_ID char(2 char) NOT NULL,
	DECLINE_REASON clob,
	FORKED_BY_TEMPLATE_ID number(11),
	ZOMBIE char(1 char) DEFAULT 'N' NOT NULL,
	DEADLINE_COUNTED number(11) DEFAULT 0 NOT NULL,
	PRIMARY KEY (ID),
	CONSTRAINT b_tasks_ibfk_2 FOREIGN KEY (PARENT_ID) REFERENCES b_tasks (ID)
)
/
CREATE INDEX b_tasks_forum_topic_id_ibpk ON b_tasks(FORUM_TOPIC_ID)
/
CREATE INDEX b_tasks_deadline_ibuk ON b_tasks(DEADLINE, DEADLINE_COUNTED)
/
CREATE INDEX ix_tasks_deadline_g ON b_tasks(GROUP_ID)
/
CREATE UNIQUE INDEX b_tasks_guid_ibuk ON b_tasks(GUID)
/
CREATE SEQUENCE sq_b_tasks INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_tasks_insert
BEFORE INSERT
ON b_tasks
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_tasks.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE TABLE b_tasks_files_temporary (
	USER_ID number(11) NOT NULL,
	FILE_ID number(11) NOT NULL,
	UNIX_TS number(11) NOT NULL,
	PRIMARY KEY (FILE_ID)
)
/
CREATE INDEX b_tasks_files_temp_uid_ibk ON b_tasks_files_temporary(USER_ID)
/
CREATE INDEX b_tasks_files_temp_uts_ibk ON b_tasks_files_temporary(UNIX_TS)
/
CREATE TABLE b_tasks_dependence (
	TASK_ID number(11) DEFAULT '0' NOT NULL,
	DEPENDS_ON_ID number(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (TASK_ID,DEPENDS_ON_ID),
	CONSTRAINT b_tasks_dependence_ibfk_1 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID),
	CONSTRAINT b_tasks_dependence_ibfk_2 FOREIGN KEY (DEPENDS_ON_ID) REFERENCES b_tasks (ID)
)
/
CREATE TABLE b_tasks_proj_dep (
	TASK_ID number(11) DEFAULT '0' NOT NULL,
	DEPENDS_ON_ID number(11) DEFAULT '0' NOT NULL,
	TYPE number(3) DEFAULT '2' NOT NULL,
	DIRECT number(3) DEFAULT '0',
	MPCITY number(11) DEFAULT '1',
  CREATOR_ID number(11),
	PRIMARY KEY (TASK_ID,DEPENDS_ON_ID)
)
/
CREATE INDEX IX_TASKS_PROJ_DEP_DOI ON b_tasks_proj_dep(DEPENDS_ON_ID)
/
CREATE INDEX IX_TASKS_PROJ_DEP_DIR ON b_tasks_proj_dep(DIRECT)
/
CREATE TABLE b_tasks_file (
	TASK_ID number(11) DEFAULT '0' NOT NULL,
	FILE_ID number(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (TASK_ID,FILE_ID),
	CONSTRAINT b_tasks_file_ibfk_1 FOREIGN KEY (FILE_ID) REFERENCES b_file (ID),
	CONSTRAINT b_tasks_file_ibfk_2 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID)
)
/
CREATE TABLE b_tasks_member (
	TASK_ID number(11) DEFAULT '0' NOT NULL,
	USER_ID number(11) DEFAULT '0' NOT NULL,
	TYPE char(1 char) DEFAULT '' NOT NULL,
	PRIMARY KEY (TASK_ID,USER_ID,TYPE),
	CONSTRAINT b_tasks_member_ibfk_1 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID)
)
/
CREATE INDEX b_tasks_member_id_status_ibk ON b_tasks_member(USER_ID, TYPE)
/
CREATE TABLE b_tasks_tag (
	TASK_ID number(11) NOT NULL,
	USER_ID number(11) NOT NULL,
	NAME varchar(255 char) NOT NULL,
	PRIMARY KEY (TASK_ID,USER_ID,NAME),
	CONSTRAINT b_tasks_tag_ibfk_1 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID),
	CONSTRAINT b_tasks_tag_ibfk_2 FOREIGN KEY (USER_ID) REFERENCES b_user (ID)
)
/
CREATE TABLE b_tasks_template (
	ID number(11) NOT NULL,
	TASK_ID number(11),
	TITLE varchar(255 char),
	DESCRIPTION clob,
	DESCRIPTION_IN_BBCODE char(1 char) DEFAULT 'N' NOT NULL,
	PRIORITY char(1 char) DEFAULT '1' NOT NULL,
	STATUS char(1 char) DEFAULT '1' NOT NULL,
	RESPONSIBLE_ID number(11),
	DEADLINE_AFTER number(11),
  START_DATE_PLAN_AFTER number(11),
  END_DATE_PLAN_AFTER number(11),
  TIME_ESTIMATE number(11) DEFAULT 0 NOT NULL,
	REPLICATE char(1 char) DEFAULT 'N' NOT NULL,
	REPLICATE_PARAMS clob,
	CREATED_BY number(11),
	XML_ID varchar(50 char),
	ALLOW_CHANGE_DEADLINE char(1 char) DEFAULT 'N' NOT NULL,
	ALLOW_TIME_TRACKING char(1 char) DEFAULT 'N' NOT NULL,
	TASK_CONTROL char(1 char) DEFAULT 'N' NOT NULL,
	ADD_IN_REPORT char(1 char) DEFAULT 'N' NOT NULL,
	GROUP_ID number(11),
	PARENT_ID number(11),
	MULTITASK char(1 char) DEFAULT 'N' NOT NULL,
	SITE_ID char(2 char) NOT NULL,
	ACCOMPLICES clob,
	AUDITORS clob,
	RESPONSIBLES clob,
	FILES clob,
	TAGS clob,
	DEPENDS_ON clob,
	TPARAM_TYPE number(18),
	TPARAM_REPLICATION_COUNT number(11) DEFAULT '0',
	PRIMARY KEY (ID),
	CONSTRAINT b_tasks_template_ibfk_1 FOREIGN KEY (PARENT_ID) REFERENCES b_tasks (ID),
	CONSTRAINT b_tasks_template_ibfk_2 FOREIGN KEY (CREATED_BY) REFERENCES b_user (ID),
	CONSTRAINT b_tasks_template_ibfk_3 FOREIGN KEY (RESPONSIBLE_ID) REFERENCES b_user (ID),
	CONSTRAINT b_tasks_template_ibfk_4 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID)
)
/
CREATE SEQUENCE sq_b_tasks_template INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_tasks_template_insert
BEFORE INSERT
ON b_tasks_template
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_tasks_template.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE TABLE b_tasks_template_dep (
	TEMPLATE_ID number(11) NOT NULL,
	PARENT_TEMPLATE_ID number(11) NOT NULL,
	DIRECT number(3) DEFAULT '0',

	PRIMARY KEY (TEMPLATE_ID,PARENT_TEMPLATE_ID)
)
/
CREATE INDEX IX_TASKS_TASK_DEP_DIR ON b_tasks_template_dep(DIRECT)
/
CREATE TABLE b_tasks_viewed (
	TASK_ID number(11) NOT NULL,
	USER_ID number(11) NOT NULL,
	VIEWED_DATE date DEFAULT SYSDATE NOT NULL,
	PRIMARY KEY (TASK_ID,USER_ID),
	CONSTRAINT b_tasks_viewed_ibfk_1 FOREIGN KEY (USER_ID) REFERENCES b_user (ID),
	CONSTRAINT b_tasks_viewed_ibfk_2 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID)
)
/
CREATE OR REPLACE TRIGGER b_tasks_viewed_update
BEFORE UPDATE
ON b_tasks_viewed
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
	IF :NEW.VIEWED_DATE IS NOT NULL THEN
		:NEW.VIEWED_DATE := SYSDATE;
	ELSE
		:NEW.VIEWED_DATE := :OLD.VIEWED_DATE;
	END IF;
END;
/
CREATE TABLE b_tasks_log (
  ID number(11) NOT NULL,
  CREATED_DATE date NOT NULL,
  USER_ID number(11) NOT NULL,
  TASK_ID number(11) NOT NULL,
  FIELD varchar(50 char) NOT NULL,
  FROM_VALUE clob,
  TO_VALUE clob,
  PRIMARY KEY (ID)
)
/
CREATE INDEX b_tasks_log1 ON b_tasks_log(TASK_ID, CREATED_DATE)
/
CREATE SEQUENCE sq_b_tasks_log
/
CREATE OR REPLACE TRIGGER b_tasks_log_insert
BEFORE INSERT
ON b_tasks_log
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_tasks_log.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE b_tasks_elapsed_time (
  ID number(11) NOT NULL,
  CREATED_DATE date DEFAULT SYSDATE NOT NULL,
  DATE_START date,
  DATE_STOP date,
  USER_ID number(11) NOT NULL,
  TASK_ID number(11) NOT NULL,
  MINUTES number(11) NOT NULL,
  SECONDS number(11) DEFAULT '0' NOT NULL,
  SOURCE number(11) DEFAULT '1' NOT NULL,
  COMMENT_TEXT clob,
  PRIMARY KEY (ID)
)
/
CREATE INDEX b_tasks_elapsed_time_ibpk_2 ON b_tasks_elapsed_time(TASK_ID)
/
CREATE INDEX b_tasks_elapsed_time_ibpk_3 ON b_tasks_elapsed_time(USER_ID)
/
CREATE SEQUENCE sq_b_tasks_elapsed_time
/
CREATE OR REPLACE TRIGGER b_tasks_elapsed_time_insert
BEFORE INSERT
ON b_tasks_elapsed_time
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_tasks_elapsed_time.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE OR REPLACE TRIGGER b_tasks_elapsed_time_update
BEFORE UPDATE
ON b_tasks_elapsed_time
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
	IF :NEW.CREATED_DATE IS NOT NULL THEN
		:NEW.CREATED_DATE := SYSDATE;
	ELSE
		:NEW.CREATED_DATE := :OLD.CREATED_DATE;
	END IF;
END;
/
CREATE TABLE b_tasks_reminder (
  ID number(11) NOT NULL,
  USER_ID number(11) NOT NULL,
  TASK_ID number(11) NOT NULL,
  REMIND_DATE date NOT NULL,
  TYPE char(1 char) NOT NULL,
  TRANSPORT char(1 char) NOT NULL,
  RECEPIENT_TYPE char(1 char) default 'S',
  CONSTRAINT b_tasks_reminder_ibfk_1 FOREIGN KEY (USER_ID) REFERENCES b_user (ID),
  CONSTRAINT b_tasks_reminder_ibfk_2 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID),
  PRIMARY KEY (ID)
)
/
CREATE INDEX IX_TASKS_REMINDER_RD ON b_tasks_reminder(REMIND_DATE)
/
CREATE SEQUENCE sq_b_tasks_reminder
/
CREATE OR REPLACE TRIGGER b_tasks_reminder_insert
BEFORE INSERT
ON b_tasks_reminder
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_tasks_reminder.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE b_tasks_filters (
	ID number(11) NOT NULL,
	USER_ID number(11) NOT NULL,
	PARENT number(11) NOT NULL,
	NAME varchar(255 char),
	SERIALIZED_FILTER clob,
	PRIMARY KEY (ID)
)
/
CREATE INDEX b_tasks_filters_user_id_ibpk ON b_tasks_filters(USER_ID)
/
CREATE SEQUENCE sq_b_tasks_filters INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_tasks_filters_insert
BEFORE INSERT
ON b_tasks_filters
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_tasks_filters.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE TABLE b_tasks_checklist_items (
	ID number(11) NOT NULL,
	TASK_ID number(11) NOT NULL,
	CREATED_BY number(11) NOT NULL,
	TOGGLED_BY number(11),
	TOGGLED_DATE date,
	TITLE varchar(255 char),
	IS_COMPLETE char(1 char) DEFAULT 'N' NOT NULL,
	SORT_INDEX number(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (ID)
)
/
CREATE INDEX b_tasks_checklist_items_taskid ON b_tasks_checklist_items(TASK_ID)
/
CREATE SEQUENCE sq_b_tasks_checklist_items INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_tasks_checklist_items_insert
BEFORE INSERT
ON b_tasks_checklist_items
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_tasks_checklist_items.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE TABLE b_tasks_template_chl_item (
	ID number(18) NOT NULL,
	TEMPLATE_ID number(18) NOT NULL,
	SORT number(18) DEFAULT '0',
	TITLE varchar(255 char) NOT NULL,
	CHECKED number(3) default '0'
)
/
CREATE INDEX ix_tasks_templ_chl_item_tid ON b_tasks_template_chl_item(TEMPLATE_ID)
/
CREATE SEQUENCE sq_b_tasks_template_chl_item INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_tasks_template_chl_item_ins
BEFORE INSERT
ON b_tasks_template_chl_item
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_tasks_template_chl_item.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE TABLE b_tasks_timer (
	TASK_ID number(11) NOT NULL,
	USER_ID number(11) NOT NULL,
	TIMER_STARTED_AT number(11) DEFAULT '0' NOT NULL,
	TIMER_ACCUMULATOR number(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (USER_ID)
)
/
CREATE INDEX b_tasks_timer_task_id_ibk ON b_tasks_timer(TASK_ID)
/
CREATE TABLE b_tasks_columns (
	ID number(11) NOT NULL,
	USER_ID number(11) NOT NULL,
	CONTEXT_ID number(11) NOT NULL,
	NAME varchar(255 char),
	SERIALIZED_COLUMNS clob,
	PRIMARY KEY (ID)
)
/
CREATE INDEX b_tasks_columns_user_id_ibpk ON b_tasks_columns(USER_ID)
/
CREATE SEQUENCE sq_b_tasks_columns INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_tasks_columns_insert
BEFORE INSERT
ON b_tasks_columns
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_tasks_columns.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE TABLE b_tasks_favorite (
	TASK_ID number(11) NOT NULL,
	USER_ID number(11) NOT NULL,
	PRIMARY KEY (TASK_ID, USER_ID)
)
/
CREATE TABLE b_tasks_msg_throttle (

	ID number(11) NOT NULL,
	TASK_ID number(11) NOT NULL,
	AUTHOR_ID number(11),
	INFORM_AUTHOR char(1 char) DEFAULT '0',
	STATE_ORIG clob,
	STATE_LAST clob,

	PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_tasks_msg_throttle INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_tasks_msg_throttle_insert
BEFORE INSERT
ON b_tasks_msg_throttle
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_tasks_msg_throttle.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE INDEX ix_tasks_msg_throttle_ti ON b_tasks_msg_throttle(TASK_ID)
/
CREATE TABLE b_tasks_sorting (
	ID number(11) NOT NULL,
	TASK_ID number(11) NOT NULL,
	SORT number(18,7) NOT NULL,
	USER_ID number(11) DEFAULT 0 NOT NULL,
	GROUP_ID number(11) DEFAULT 0 NOT NULL,
	PREV_TASK_ID number(11) DEFAULT 0 NOT NULL,
	NEXT_TASK_ID number(11) DEFAULT 0 NOT NULL,
	PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_tasks_sorting INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_tasks_sorting_insert
BEFORE INSERT
ON b_tasks_sorting
FOR EACH ROW
	BEGIN
		IF :NEW.ID IS NULL THEN
			SELECT sq_b_tasks_sorting.NEXTVAL INTO :NEW.ID FROM dual;
		END IF;
	END;
/
CREATE INDEX ix_tasks_sorting_tid_uid ON b_tasks_sorting(TASK_ID, USER_ID)
/
CREATE INDEX ix_tasks_sorting_tid_gid ON b_tasks_sorting(TASK_ID, GROUP_ID)
/
CREATE INDEX ix_tasks_sorting_sort ON b_tasks_sorting(SORT)
/
CREATE TABLE b_tasks_syslog (
	ID number(11) NOT NULL,
  TYPE number(3),
	CREATED_DATE date,
	MESSAGE varchar(255 char),
	ENTITY_ID number(11),
	ENTITY_TYPE number(3),
  PARAM_A number(11),
	ERROR clob,
	PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_tasks_syslog INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_tasks_syslog_insert
BEFORE INSERT
ON b_tasks_syslog
FOR EACH ROW
	BEGIN
		IF :NEW.ID IS NULL THEN
			SELECT sq_b_tasks_syslog.NEXTVAL INTO :NEW.ID FROM dual;
		END IF;
	END;
/
CREATE INDEX ix_tasks_syslog_etei ON b_tasks_syslog(ENTITY_TYPE, ENTITY_ID)
/
CREATE INDEX ix_tasks_syslog_d ON b_tasks_syslog(CREATED_DATE)
/

create table b_tasks_task_template_access
(
  ID number(11) not null,
  GROUP_CODE varchar(50 char) NOT NULL,
  ENTITY_ID number(11) not null,
  TASK_ID number(11) not null,

  PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_tasks_tt_acc INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_tasks_task_tt_acc_insert
BEFORE INSERT
ON b_tasks_task_template_access
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_tasks_tt_acc.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
create index ix_tasks_task_template_a_get on b_tasks_task_template_access (GROUP_CODE, ENTITY_ID, TASK_ID)
/
CREATE TABLE b_tasks_task_parameter (
  ID number(11) NOT NULL,
  TASK_ID number(11) NOT NULL,
  CODE number(3) NOT NULL,
  VALUE varchar(10 char),
  PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_tasks_task_parameter INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_tasks_task_parameter_insert
BEFORE INSERT
ON b_tasks_task_parameter
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_tasks_task_parameter.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE UNIQUE INDEX ix_tasks_task_parameter_ticv ON b_tasks_task_parameter(TASK_ID, CODE, VALUE)
/
CREATE TABLE b_tasks_task_dep (
  TASK_ID number(11) NOT NULL,
  PARENT_TASK_ID number(11) NOT NULL,
  DIRECT number(3) DEFAULT '0',

  PRIMARY KEY (TASK_ID, PARENT_TASK_ID)
)
/
CREATE UNIQUE INDEX IX_TASKS_T_DEP_DIR ON b_tasks_task_dep(TASK_ID, PARENT_TASK_ID, DIRECT)
/
CREATE UNIQUE INDEX IX_TASKS_T_DEP_DIR_R ON b_tasks_task_dep(PARENT_TASK_ID, TASK_ID, DIRECT)
/