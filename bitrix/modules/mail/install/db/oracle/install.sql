create table b_mail_mailbox
(
   ID number(18) not null,
   TIMESTAMP_X date DEFAULT SYSDATE not null,
   LID char(2 CHAR) not null,
   ACTIVE char(1 CHAR) default 'Y' not null,
   SERVICE_ID number(18) DEFAULT 0 NOT NULL,
   NAME varchar2(255 CHAR) not null,
   SERVER varchar2(255 CHAR) null,
   PORT number(18) default 110 not null,
   LINK varchar2(255 CHAR) null,
   LOGIN varchar2(255 CHAR) null,
   CHARSET varchar2(255 CHAR),
   PASSWORD varchar2(255 CHAR) null,
   DESCRIPTION varchar2(2000 CHAR),
   USE_MD5 char(1 CHAR) default 'N' not null,
   DELETE_MESSAGES char(1 CHAR) default 'N' not null,
   PERIOD_CHECK number(18),
   MAX_MSG_COUNT number(18) default 0,
   MAX_MSG_SIZE number(18) default 0,
   MAX_KEEP_DAYS number(18) default 0,
   USE_TLS char(1 CHAR) default 'N' not null,
   SERVER_TYPE varchar2(10 char) DEFAULT 'pop3' NOT NULL,
   DOMAINS varchar2(255 char) null,
   RELAY char(1 char) DEFAULT 'Y' NOT NULL,
   AUTH_RELAY char(1 char) DEFAULT 'Y' NOT NULL,
   USER_ID number(18) DEFAULT 0 NOT NULL,
   SYNC_LOCK number(18) NULL,
   OPTIONS varchar2(2000 CHAR) NULL,
   primary key (ID)
)
/
CREATE INDEX IX_B_MAIL_MAILBOX_USER_ID ON b_mail_mailbox (USER_ID)
/
CREATE SEQUENCE sq_b_mail_mailbox INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_mail_mailbox_insert
BEFORE INSERT
ON b_mail_mailbox
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_mail_mailbox.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_mail_mailbox_update
BEFORE UPDATE 
ON b_mail_mailbox
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW 
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/

create table b_mail_filter
(
   ID number(18) not null,
   TIMESTAMP_X date default SYSDATE not null,
   MAILBOX_ID number(18) not null,
   PARENT_FILTER_ID number(18),
   NAME varchar2(255 CHAR),
   DESCRIPTION varchar2(2000 CHAR),
   SORT number(18) default 500 not null,
   ACTIVE char(1 CHAR) default 'Y' not null,
   PHP_CONDITION CLOB,
   WHEN_MAIL_RECEIVED char(1 CHAR) default 'N' not null,
   WHEN_MANUALLY_RUN char(1 CHAR) default 'N' not null,
   SPAM_RATING number(9,4),
   SPAM_RATING_TYPE char(1 CHAR) default '<',
   MESSAGE_SIZE number(18),
   MESSAGE_SIZE_TYPE char(1 CHAR) default '<',
   MESSAGE_SIZE_UNIT char(1 CHAR),
   ACTION_STOP_EXEC char(1 CHAR) default 'N' not null,
   ACTION_DELETE_MESSAGE char(1 CHAR) default 'N' not null,
   ACTION_READ char(1 CHAR) default '-' not null,
   ACTION_PHP CLOB,
   ACTION_TYPE varchar2(50 CHAR),
   ACTION_VARS varchar2(2000 CHAR),
   ACTION_SPAM char(1 CHAR) default '-' not null,
   primary key (ID)
)
/
create index IX_MAIL_FILTER_MAILBOX on b_mail_filter(MAILBOX_ID)
/
CREATE SEQUENCE sq_b_mail_filter INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_mail_filter_insert
BEFORE INSERT
ON b_mail_filter
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_mail_filter.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_mail_filter_update
BEFORE UPDATE 
ON b_mail_filter
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW 
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/


create table b_mail_filter_cond
(
   ID number(18) not null,
   FILTER_ID number(18) not null,
   TYPE varchar2(50 CHAR) not null,
   STRINGS varchar2(2000 CHAR) not null,
   COMPARE_TYPE varchar2(30 CHAR) default 'CONTAIN' not null,
   primary key (ID)
)
/

CREATE SEQUENCE sq_b_mail_filter_cond INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_mail_filter_cond_insert
BEFORE INSERT
ON b_mail_filter_cond
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_mail_filter_cond.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

create table b_mail_message 
(
   ID number(18) not null,
   MAILBOX_ID number(18) not null,
   DATE_INSERT date default SYSDATE not null,
   FULL_TEXT CLOB,
   MESSAGE_SIZE number(18) not null,
   HEADER CLOB,
   FIELD_DATE date,
   FIELD_FROM varchar2(255 CHAR),
   FIELD_REPLY_TO varchar2(255 CHAR),
   FIELD_TO varchar2(255 CHAR),
   FIELD_CC varchar2(255 CHAR),
   FIELD_BCC varchar2(255 CHAR),
   FIELD_PRIORITY number(18) default 3 not null,
   SUBJECT varchar2(255 CHAR),
   BODY CLOB,
   ATTACHMENTS number(18) default '0',
   NEW_MESSAGE char(1 CHAR) default 'Y',
   SPAM char(1 CHAR) default '?' not null,
   SPAM_RATING decimal(18,4),
   SPAM_WORDS varchar2(255 CHAR),
   SPAM_LAST_RESULT char(1 CHAR) default 'N' not null,
   FOR_SPAM_TEST CLOB,
   EXTERNAL_ID varchar2(255 CHAR),
   MSG_ID varchar2(255 char) NULL,
   IN_REPLY_TO varchar2(255 char) NULL,
   primary key (ID)
)
/
create index IX_MAIL_MESSAGE on b_mail_message(MAILBOX_ID)
/
create index IX_MAIL_MESSAGE_DATE on b_mail_message(DATE_INSERT, MAILBOX_ID)
/

CREATE SEQUENCE sq_b_mail_message INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_mail_message_insert
BEFORE INSERT
ON b_mail_message
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_mail_message.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

create table b_mail_message_uid
(
	ID varchar2(32 CHAR) not null,
	MAILBOX_ID number(18) not null,
	HEADER_MD5 varchar2(32 CHAR) NULL,
	IS_SEEN char(1 char) DEFAULT 'N' NOT NULL,
	SESSION_ID varchar2(32 CHAR) not null,
	TIMESTAMP_X date default SYSDATE not null,
	DATE_INSERT date default SYSDATE not null,
	MESSAGE_ID number(18) not null,
	CONSTRAINT PK_B_MAIL_MESSAGE_UID PRIMARY KEY (ID, MAILBOX_ID)
)
/

create index IX_MAIL_MSG_UID on b_mail_message_uid(MAILBOX_ID)
/
create index IX_MAIL_MSG_UID_2 on b_mail_message_uid(HEADER_MD5)
/

CREATE OR REPLACE TRIGGER b_mail_message_uid_update
BEFORE UPDATE 
ON b_mail_message_uid
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW 
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/

create table b_mail_msg_attachment 
(
   ID number(18) not null,
   MESSAGE_ID number(18) not null,
   FILE_ID number(18) default 0 not null,
   FILE_NAME varchar2(255 CHAR),
   FILE_SIZE number(11) default 0 not null,
   FILE_DATA CLOB,
   CONTENT_TYPE varchar2(255 CHAR),
   IMAGE_WIDTH number(18),
   IMAGE_HEIGHT number(18),
   primary key (ID)
)
/

create index IX_MAIL_MESSATTACHMENT on b_mail_msg_attachment(MESSAGE_ID)
/
CREATE SEQUENCE sq_b_mail_msg_attachment INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_mail_msg_attachment_insert
BEFORE INSERT
ON b_mail_msg_attachment
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_mail_msg_attachment.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/


create table b_mail_spam_weight
(
   WORD_ID varchar2(32 CHAR) not null,
   WORD_REAL varchar2(50 CHAR) not null,
   GOOD_CNT number(18) default 0 not null,
   BAD_CNT number(18) default 0 not null,
   TOTAL_CNT number(18) default 0 not null,
   TIMESTAMP_X date default sysdate,
   primary key (WORD_ID)
)
/

CREATE OR REPLACE TRIGGER b_mail_spam_weight_update
BEFORE UPDATE 
ON b_mail_spam_weight
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW 
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/

create table b_mail_log 
(
   ID number(18) not null,
   MAILBOX_ID number(18) not null,
   FILTER_ID number(18),
   MESSAGE_ID number(18),
   LOG_TYPE varchar2(50 CHAR),
   DATE_INSERT date default sysdate not null,
   STATUS_GOOD char(1 CHAR) default 'Y' not null,
   MESSAGE varchar2(255 CHAR),
   primary key (ID)
)
/
create index IX_MAIL_MSGLOG_1 on b_mail_log(MAILBOX_ID)
/

create index IX_MAIL_MSGLOG_2 on b_mail_log(MESSAGE_ID)
/

CREATE SEQUENCE sq_b_mail_log INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_mail_log_insert
BEFORE INSERT
ON b_mail_log
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_mail_log.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/


create table b_mail_mailservices
(
   ID number(18) NOT NULL,
   SITE_ID varchar2(255 CHAR) NOT NULL,
   ACTIVE char(1 CHAR) DEFAULT 'Y' NOT NULL,
   SERVICE_TYPE varchar2(10 CHAR) DEFAULT 'imap' NOT NULL,
   NAME varchar2(255 CHAR) NOT NULL,
   SERVER varchar2(255 CHAR) NULL,
   PORT number(18) NULL,
   ENCRYPTION char(1 CHAR) NULL,
   LINK varchar2(255 CHAR) NULL,
   ICON number(18) NULL,
   TOKEN varchar2(255 CHAR) NULL,
   FLAGS number(18) DEFAULT 0 NOT NULL,
   SORT number(18) DEFAULT 100 NOT NULL,
   primary key (ID)
)
/
CREATE INDEX IX_B_MAIL_MAILSERVICE_ACTIVE ON b_mail_mailservices (ACTIVE)
/
CREATE SEQUENCE sq_b_mail_mailservices INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_mail_mailservices_insert
BEFORE INSERT
ON b_mail_mailservices
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_mail_mailservices.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/


create table b_mail_user_relations
(
   TOKEN varchar2(32 CHAR) NOT NULL,
   SITE_ID char(2 CHAR) NULL,
   USER_ID number(18) NOT NULL,
   ENTITY_TYPE varchar2(255 CHAR) NOT NULL,
   ENTITY_ID varchar2(255 CHAR) NULL,
   ENTITY_LINK varchar2(255 CHAR) NULL,
   BACKURL varchar2(255 CHAR) NULL,
   CONSTRAINT PK_B_MAIL_USER_RELATIONS PRIMARY KEY (TOKEN)
)
/
CREATE UNIQUE INDEX UX_B_MAIL_USER_RELATION ON b_mail_user_relations (USER_ID, ENTITY_TYPE, ENTITY_ID, SITE_ID)
/

create table b_mail_blacklist
(
   ID number(18) NOT NULL,
   SITE_ID char(2 CHAR) NOT NULL,
   MAILBOX_ID number(18) DEFAULT 0 NOT NULL,
   ITEM_TYPE number(18) NOT NULL,
   ITEM_VALUE varchar2(255 CHAR) NOT NULL,
   PRIMARY KEY (ID)
)
/
CREATE INDEX IX_B_MAIL_BLACKLIST ON b_mail_blacklist (MAILBOX_ID, SITE_ID)
/
CREATE SEQUENCE sq_b_mail_blacklist INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_mail_blacklist_insert
BEFORE INSERT
ON b_mail_blacklist
FOR EACH ROW
BEGIN
   IF :NEW.ID IS NULL THEN
      SELECT sq_b_mail_blacklist.NEXTVAL INTO :NEW.ID FROM dual;
   END IF;
END;
/

create table b_mail_domain_email
(
   DOMAIN varchar2(255 CHAR) NOT NULL,
   LOGIN varchar2(255 CHAR) NOT NULL,
   CONSTRAINT PK_B_MAIL_DOMAIN_EMAIL PRIMARY KEY (LOGIN, DOMAIN)
)
/
CREATE INDEX IX_B_MAIL_DOMAIN_EMAIL ON b_mail_domain_email (DOMAIN)
/
