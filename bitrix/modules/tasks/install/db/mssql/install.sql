CREATE TABLE b_tasks (
	ID int NOT NULL IDENTITY (1, 1),
	TITLE varchar(255) DEFAULT NULL,
	DESCRIPTION text,
	DESCRIPTION_IN_BBCODE char(1) NOT NULL DEFAULT 'N',
	PRIORITY char(1) NOT NULL DEFAULT '1',
	STATUS char(1) DEFAULT NULL,
	RESPONSIBLE_ID int DEFAULT NULL,
	DATE_START datetime DEFAULT NULL,
	DURATION_PLAN int DEFAULT NULL,
	DURATION_FACT int DEFAULT NULL,
	DURATION_TYPE varchar(5) NOT NULL DEFAULT 'days',
	TIME_ESTIMATE int NOT NULL DEFAULT 0,
	REPLICATE char(1) NOT NULL DEFAULT 'N',
	DEADLINE datetime DEFAULT NULL,
	START_DATE_PLAN datetime DEFAULT NULL,
	END_DATE_PLAN datetime DEFAULT NULL,
	CREATED_BY int DEFAULT NULL,
	CREATED_DATE datetime DEFAULT NULL,
	CHANGED_BY int DEFAULT NULL,
	CHANGED_DATE datetime DEFAULT NULL,
	STATUS_CHANGED_BY int DEFAULT NULL,
	STATUS_CHANGED_DATE datetime DEFAULT NULL,
	CLOSED_BY int DEFAULT NULL,
	CLOSED_DATE datetime DEFAULT NULL,
	GUID varchar(50) DEFAULT NULL,
	XML_ID varchar(50) DEFAULT NULL,
	EXCHANGE_ID varchar(196) DEFAULT NULL,
	EXCHANGE_MODIFIED varchar(196) DEFAULT NULL,
	OUTLOOK_VERSION int DEFAULT NULL,
	MARK char(1) DEFAULT NULL,
	ALLOW_CHANGE_DEADLINE char(1) NOT NULL DEFAULT 'N',
	ALLOW_TIME_TRACKING char(1) NOT NULL DEFAULT 'N',
	MATCH_WORK_TIME char(1) NOT NULL DEFAULT 'N',
	TASK_CONTROL char(1) NOT NULL DEFAULT 'N',
	ADD_IN_REPORT char(1) NOT NULL DEFAULT 'N',
	GROUP_ID int DEFAULT NULL,
	PARENT_ID int DEFAULT NULL,
	FORUM_TOPIC_ID int DEFAULT NULL,
	MULTITASK char(1) NOT NULL DEFAULT 'N',
	SITE_ID char(2) NOT NULL,
	DECLINE_REASON text,
	FORKED_BY_TEMPLATE_ID int DEFAULT NULL,
	ZOMBIE char(1) NOT NULL DEFAULT 'N',
	DEADLINE_COUNTED int NOT NULL DEFAULT 0,
	CONSTRAINT b_tasks_ibpk_1 PRIMARY KEY (ID),
	CONSTRAINT b_tasks_ibfk_2 FOREIGN KEY (PARENT_ID) REFERENCES b_tasks (ID)
)
GO
CREATE INDEX b_tasks_forum_topic_id_ibpk ON b_tasks(FORUM_TOPIC_ID)
GO
CREATE INDEX b_tasks_deadline_ibuk ON b_tasks(DEADLINE, DEADLINE_COUNTED)
GO
CREATE INDEX b_tasks_guid_ibuk ON b_tasks(GUID)
GO
CREATE INDEX ix_tasks_deadline_g ON b_tasks(GROUP_ID)
GO
CREATE TABLE b_tasks_files_temporary (
	USER_ID int NOT NULL,
	FILE_ID int NOT NULL,
	UNIX_TS int NOT NULL,
	PRIMARY KEY (FILE_ID)
)
GO
CREATE INDEX b_tasks_files_temp_uid_ibk ON b_tasks_files_temporary(USER_ID)
GO
CREATE INDEX b_tasks_files_temp_uts_ibk ON b_tasks_files_temporary(UNIX_TS)
GO
CREATE TABLE b_tasks_dependence (
	TASK_ID int NOT NULL DEFAULT '0',
	DEPENDS_ON_ID int NOT NULL DEFAULT '0',
	PRIMARY KEY (TASK_ID,DEPENDS_ON_ID),
	CONSTRAINT b_tasks_dependence_ibfk_1 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID),
	CONSTRAINT b_tasks_dependence_ibfk_2 FOREIGN KEY (DEPENDS_ON_ID) REFERENCES b_tasks (ID)
)
GO
CREATE TABLE b_tasks_proj_dep (
	TASK_ID int NOT NULL,
	DEPENDS_ON_ID int NOT NULL,
	TYPE tinyint NOT NULL,
	DIRECT tinyint,
	MPCITY int,
  CREATOR_ID int,
	CONSTRAINT b_tasks_proj_dep_ibpk_1 PRIMARY KEY (TASK_ID,DEPENDS_ON_ID)
)
GO
ALTER TABLE b_tasks_proj_dep ADD CONSTRAINT df_b_tasks_proj_dep_ti DEFAULT '0' FOR TASK_ID
GO
ALTER TABLE b_tasks_proj_dep ADD CONSTRAINT df_b_tasks_proj_dep_doi DEFAULT '0' FOR DEPENDS_ON_ID
GO
ALTER TABLE b_tasks_proj_dep ADD CONSTRAINT df_b_tasks_proj_dep_type DEFAULT '2' FOR TYPE
GO
ALTER TABLE b_tasks_proj_dep ADD CONSTRAINT df_b_tasks_proj_dep_direct DEFAULT '0' FOR DIRECT
GO
ALTER TABLE b_tasks_proj_dep ADD CONSTRAINT df_b_tasks_proj_dep_mpcity DEFAULT '1' FOR MPCITY
GO
CREATE INDEX IX_TASKS_PROJ_DEP_DOI ON b_tasks_proj_dep(DEPENDS_ON_ID)
GO
CREATE INDEX IX_TASKS_PROJ_DEP_DIR ON b_tasks_proj_dep(DIRECT)
GO
CREATE TABLE b_tasks_file (
	TASK_ID int NOT NULL DEFAULT '0',
	FILE_ID int NOT NULL DEFAULT '0',
	PRIMARY KEY (TASK_ID,FILE_ID),
	CONSTRAINT b_tasks_file_ibfk_1 FOREIGN KEY (FILE_ID) REFERENCES b_file (ID),
	CONSTRAINT b_tasks_file_ibfk_2 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID)
)
GO
CREATE TABLE b_tasks_member (
	TASK_ID int NOT NULL DEFAULT '0',
	USER_ID int NOT NULL DEFAULT '0',
	TYPE char(1) NOT NULL DEFAULT '',
	PRIMARY KEY (TASK_ID,USER_ID,TYPE),
	CONSTRAINT b_tasks_member_ibfk_1 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID)
)
GO
CREATE INDEX b_tasks_member_id_status_ibk ON b_tasks_member(USER_ID, TYPE)
GO
CREATE TABLE b_tasks_tag (
	TASK_ID int NOT NULL,
	USER_ID int NOT NULL,
	NAME varchar(255) NOT NULL,
	PRIMARY KEY (TASK_ID,USER_ID,NAME),
	CONSTRAINT b_tasks_tag_ibfk_1 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID),
	CONSTRAINT b_tasks_tag_ibfk_2 FOREIGN KEY (USER_ID) REFERENCES b_user (ID)
)
GO
CREATE TABLE b_tasks_template (
	ID int NOT NULL IDENTITY (1, 1),
	TASK_ID int DEFAULT NULL,
	TITLE varchar(255) DEFAULT NULL,
	DESCRIPTION text,
	DESCRIPTION_IN_BBCODE char(1) NOT NULL DEFAULT 'N',
	PRIORITY char(1) NOT NULL DEFAULT '1',
	STATUS char(1) NOT NULL DEFAULT '1',
	RESPONSIBLE_ID int DEFAULT NULL,
	DEADLINE_AFTER int DEFAULT NULL,
  START_DATE_PLAN_AFTER int DEFAULT NULL,
  END_DATE_PLAN_AFTER int DEFAULT NULL,
  TIME_ESTIMATE int NOT NULL DEFAULT 0,
	REPLICATE char(1) NOT NULL DEFAULT 'N',
	REPLICATE_PARAMS text,
	CREATED_BY int DEFAULT NULL,
	XML_ID varchar(50) DEFAULT NULL,
	ALLOW_CHANGE_DEADLINE char(1) NOT NULL DEFAULT 'N',
	ALLOW_TIME_TRACKING char(1) NOT NULL DEFAULT 'N',
	TASK_CONTROL char(1) NOT NULL DEFAULT 'N',
	ADD_IN_REPORT char(1) NOT NULL DEFAULT 'N',
	GROUP_ID int DEFAULT NULL,
	PARENT_ID int DEFAULT NULL,
	MULTITASK char(1) NOT NULL DEFAULT 'N',
	SITE_ID char(2) NOT NULL,
	ACCOMPLICES text,
	AUDITORS text,
	RESPONSIBLES text,
	FILES text,
	TAGS text,
	DEPENDS_ON text,
	TPARAM_TYPE int,
	TPARAM_REPLICATION_COUNT int,
	CONSTRAINT b_tasks_template_ibpk_1 PRIMARY KEY (ID),
	CONSTRAINT b_tasks_template_ibfk_1 FOREIGN KEY (PARENT_ID) REFERENCES b_tasks (ID),
	CONSTRAINT b_tasks_template_ibfk_2 FOREIGN KEY (CREATED_BY) REFERENCES b_user (ID),
	CONSTRAINT b_tasks_template_ibfk_3 FOREIGN KEY (RESPONSIBLE_ID) REFERENCES b_user (ID),
	CONSTRAINT b_tasks_template_ibfk_4 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID)
)
GO
ALTER TABLE b_tasks_template ADD CONSTRAINT df_b_tasks_template_repl_cnt DEFAULT '0' FOR TPARAM_REPLICATION_COUNT
GO
CREATE TABLE b_tasks_template_dep (
	TEMPLATE_ID int NOT NULL,
	PARENT_TEMPLATE_ID int NOT NULL,
	DIRECT tinyint,

	CONSTRAINT b_tasks_template_dep_pk PRIMARY KEY (TEMPLATE_ID,PARENT_TEMPLATE_ID)
)
GO
CREATE INDEX IX_TASKS_TASK_DEP_DIR ON b_tasks_template_dep(DIRECT)
GO
ALTER TABLE b_tasks_template_dep ADD CONSTRAINT df_b_tasks_template_dep_direct DEFAULT '0' FOR DIRECT
GO
CREATE TABLE b_tasks_viewed (
	TASK_ID int NOT NULL,
	USER_ID int NOT NULL,
	VIEWED_DATE datetime NOT NULL,
	PRIMARY KEY (TASK_ID,USER_ID),
	CONSTRAINT b_tasks_viewed_ibfk_1 FOREIGN KEY (USER_ID) REFERENCES b_user (ID),
	CONSTRAINT b_tasks_viewed_ibfk_2 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID)
)
GO
ALTER TABLE b_tasks_viewed ADD CONSTRAINT df_b_tasks_viewed_viewed_date DEFAULT GETDATE() FOR VIEWED_DATE
GO
CREATE TABLE b_tasks_log (
  ID int NOT NULL IDENTITY (1, 1),
  CREATED_DATE datetime NOT NULL,
  USER_ID int NOT NULL,
  TASK_ID int NOT NULL,
  FIELD varchar(50) NOT NULL,
  FROM_VALUE text,
  TO_VALUE text,
  PRIMARY KEY (ID)
)
GO

CREATE INDEX B_TASKS_LOG1 ON b_tasks_log(TASK_ID, CREATED_DATE)
GO



CREATE TABLE b_tasks_elapsed_time (
  ID int NOT NULL IDENTITY (1, 1),
  CREATED_DATE datetime NOT NULL,
  DATE_START datetime DEFAULT NULL,
  DATE_STOP datetime DEFAULT NULL,
  USER_ID int NOT NULL,
  TASK_ID int NOT NULL,
  MINUTES int NOT NULL,
  SECONDS int NOT NULL DEFAULT '0',
  SOURCE int NOT NULL DEFAULT '1',
  COMMENT_TEXT text NULL,
  CONSTRAINT b_tasks_elapsed_time_ibpk_1 PRIMARY KEY (ID)
)
GO
ALTER TABLE b_tasks_elapsed_time ADD CONSTRAINT df_b_tasks_elapsed_time_created_date DEFAULT GETDATE() FOR CREATED_DATE
GO
CREATE INDEX b_tasks_elapsed_time_ibpk_2 ON b_tasks_elapsed_time(TASK_ID)
GO
CREATE INDEX b_tasks_elapsed_time_ibpk_3 ON b_tasks_elapsed_time(USER_ID)
GO
CREATE TABLE b_tasks_reminder (
  ID int NOT NULL IDENTITY (1, 1),
  USER_ID int NOT NULL,
  TASK_ID int NOT NULL,
  REMIND_DATE datetime NOT NULL,
  TYPE char(1) NOT NULL,
  TRANSPORT char(1) NOT NULL,
  RECEPIENT_TYPE char(1),
  CONSTRAINT b_tasks_reminder_ibfk_1 FOREIGN KEY (USER_ID) REFERENCES b_user (ID),
  CONSTRAINT b_tasks_reminder_ibfk_2 FOREIGN KEY (TASK_ID) REFERENCES b_tasks (ID),
  PRIMARY KEY (ID)
)
GO
ALTER TABLE b_tasks_reminder ADD CONSTRAINT df_b_tasks_reminder_mt DEFAULT 'S' FOR RECEPIENT_TYPE
GO
CREATE INDEX IX_TASKS_REMINDER_RD ON b_tasks_reminder(REMIND_DATE)
GO
CREATE TABLE b_tasks_filters (
	ID int NOT NULL IDENTITY (1, 1),
	USER_ID int NOT NULL,
	PARENT int NOT NULL,
	NAME varchar(255) DEFAULT NULL,
	SERIALIZED_FILTER text,
	CONSTRAINT b_tasks_filters_ibpk_1 PRIMARY KEY (ID)
)
GO
CREATE INDEX b_tasks_filters_user_id_ibpk ON b_tasks_filters(USER_ID)
GO
CREATE TABLE b_tasks_checklist_items (
	ID int NOT NULL IDENTITY (1, 1),
	TASK_ID int NOT NULL,
	CREATED_BY int NOT NULL,
	TOGGLED_BY int DEFAULT NULL,
	TOGGLED_DATE datetime DEFAULT NULL,
	TITLE varchar(255) DEFAULT NULL,
	IS_COMPLETE char(1) NOT NULL DEFAULT 'N',
	SORT_INDEX int NOT NULL DEFAULT '0',
	CONSTRAINT b_tasks_checklist_items_ibpk PRIMARY KEY (ID)
)
GO
CREATE INDEX b_tasks_checklist_items_task_id_ibk ON b_tasks_checklist_items(TASK_ID)
GO
CREATE TABLE b_tasks_template_chl_item (
	ID int NOT NULL IDENTITY (1, 1),
	TEMPLATE_ID int NOT NULL,
	SORT int DEFAULT '0',
	TITLE varchar(255) NOT NULL,
	CHECKED tinyint default '0',

	CONSTRAINT b_tasks_template_chl_item_ibpk PRIMARY KEY (ID)
)
GO
CREATE INDEX ix_tasks_templ_chl_item_tid ON b_tasks_template_chl_item(TEMPLATE_ID)
GO
CREATE TABLE b_tasks_timer (
	TASK_ID int NOT NULL,
	USER_ID int NOT NULL,
	TIMER_STARTED_AT int NOT NULL DEFAULT '0',
	TIMER_ACCUMULATOR int NOT NULL DEFAULT '0',
	PRIMARY KEY (USER_ID)
)
GO
CREATE INDEX b_tasks_timer_task_id_ibk ON b_tasks_timer(TASK_ID)
GO
CREATE TABLE b_tasks_columns (
	ID int NOT NULL IDENTITY (1, 1),
	USER_ID int NOT NULL,
	CONTEXT_ID int NOT NULL,
	NAME varchar(255) DEFAULT NULL,
	SERIALIZED_COLUMNS text,
	CONSTRAINT b_tasks_columns_ibpk_1 PRIMARY KEY (ID)
)
GO
CREATE INDEX b_tasks_columns_user_id_ibpk ON b_tasks_columns(USER_ID)
GO
CREATE TABLE b_tasks_favorite (
	TASK_ID int NOT NULL,
	USER_ID int NOT NULL,
	CONSTRAINT b_tasks_favorite_ibpk_1 PRIMARY KEY (TASK_ID, USER_ID)
)
GO
CREATE TABLE b_tasks_msg_throttle (
	ID int NOT NULL IDENTITY (1, 1),
	TASK_ID int NOT NULL,
	AUTHOR_ID int,
	INFORM_AUTHOR char(1),
	STATE_ORIG text,
	STATE_LAST text,

	CONSTRAINT b_tasks_msg_throttle_ibpk PRIMARY KEY (ID)
)
GO
CREATE INDEX ix_tasks_msg_throttle_ti ON b_tasks_msg_throttle(TASK_ID)
GO
ALTER TABLE b_tasks_msg_throttle ADD CONSTRAINT df_b_tasks_msg_throttle DEFAULT '0' FOR INFORM_AUTHOR
GO
CREATE TABLE b_tasks_sorting (
	ID int NOT NULL IDENTITY (1, 1),
	TASK_ID int NOT NULL,
	SORT decimal(18,7) NOT NULL,
	USER_ID int NOT NULL DEFAULT 0,
	GROUP_ID int NOT NULL DEFAULT 0,
	PREV_TASK_ID int NOT NULL DEFAULT 0,
	NEXT_TASK_ID int NOT NULL DEFAULT 0,
	CONSTRAINT b_tasks_sorting_pk PRIMARY KEY (ID)
)
GO
CREATE INDEX ix_tasks_sorting_tid_uid ON b_tasks_sorting(TASK_ID, USER_ID)
GO
CREATE INDEX ix_tasks_sorting_tid_gid ON b_tasks_sorting(TASK_ID, GROUP_ID)
GO
CREATE INDEX ix_tasks_sorting_sort ON b_tasks_sorting(SORT)
GO
CREATE TABLE b_tasks_syslog (
  ID int NOT NULL IDENTITY (1, 1),
  TYPE tinyint,
  CREATED_DATE datetime,
  MESSAGE varchar(255),
  ENTITY_ID int,
  ENTITY_TYPE tinyint,
  PARAM_A int,
	ERROR text,
  CONSTRAINT b_tasks_syslog_ibpk_1 PRIMARY KEY (ID)
)
GO
CREATE INDEX ix_tasks_syslog_etei ON b_tasks_syslog(ENTITY_TYPE, ENTITY_ID)
GO
CREATE INDEX ix_tasks_syslog_d ON b_tasks_syslog(CREATED_DATE)
GO
create table b_tasks_task_template_access
(
  ID int not null IDENTITY (1, 1),
  GROUP_CODE varchar(50) NOT NULL,
  ENTITY_ID int not null,
  TASK_ID int not null,
  CONSTRAINT b_tasks_task_tt_acc_pk PRIMARY KEY (ID)
)
GO
create index ix_tasks_task_template_a_get on b_tasks_task_template_access (GROUP_CODE, ENTITY_ID, TASK_ID)
GO
CREATE TABLE b_tasks_task_parameter (
  ID int NOT NULL IDENTITY (1, 1),
  TASK_ID int not null,
  CODE tinyint not null,
  VALUE varchar(10) default null
)
GO
CREATE UNIQUE INDEX ix_tasks_task_parameter_ticv ON b_tasks_task_parameter(TASK_ID, CODE, VALUE)
GO
CREATE TABLE b_tasks_task_dep (
  TASK_ID int NOT NULL,
  PARENT_TASK_ID int NOT NULL,
  DIRECT tinyint
)
GO
ALTER TABLE b_tasks_task_dep ADD CONSTRAINT df_b_tasks_task_dep_dir DEFAULT '0' FOR DIRECT
GO
CREATE UNIQUE INDEX IX_TASKS_T_DEP_DIR ON b_tasks_task_dep(TASK_ID, PARENT_TASK_ID, DIRECT)
GO
CREATE UNIQUE INDEX IX_TASKS_T_DEP_DIR_R ON b_tasks_task_dep(PARENT_TASK_ID, TASK_ID, DIRECT)
GO