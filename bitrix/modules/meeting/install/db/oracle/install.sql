CREATE TABLE b_meeting
(
	ID NUMBER(18) NOT NULL,
	TIMESTAMP_X DATE DEFAULT SYSDATE NOT NULL, 
	EVENT_ID NUMBER(18) NULL,
	DATE_START DATE NULL,
	DATE_FINISH DATE NULL,
	CURRENT_STATE CHAR(1 CHAR) DEFAULT 'P' NULL,
	DURATION NUMBER(5) DEFAULT 0 NULL,
	TITLE VARCHAR(255 CHAR) NOT NULL,
	GROUP_ID NUMBER(18) NULL,
	PARENT_ID NUMBER(18) NULL,
	DESCRIPTION CLOB NULL,
	PLACE VARCHAR(255 CHAR) NULL,
	PROTOCOL_TEXT CLOB NULL,
	CONSTRAINT PK_B_MEETING PRIMARY KEY (ID)
)
/
CREATE INDEX ix_b_meeting_1 ON b_meeting(GROUP_ID)
/
CREATE SEQUENCE sq_b_meeting
/

CREATE OR REPLACE TRIGGER b_meeting_insert
BEFORE INSERT
ON b_meeting
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_meeting.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE OR REPLACE TRIGGER b_meeting_update
BEFORE UPDATE
ON b_meeting
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

CREATE TABLE b_meeting_files
(
	MEETING_ID NUMBER(18) NOT NULL,
	FILE_ID NUMBER(18) NOT NULL,
	FILE_SRC NUMBER(18) NULL,
	CONSTRAINT PK_B_MEETING_FILE PRIMARY KEY (MEETING_ID, FILE_ID),
	CONSTRAINT fk_b_meeting_file_1 FOREIGN KEY (MEETING_ID) REFERENCES b_meeting(ID)
)
/
CREATE TABLE b_meeting_users
(
	MEETING_ID NUMBER(18) NOT NULL,
	USER_ID NUMBER(18) NOT NULL,
	USER_ROLE CHAR(1 CHAR) DEFAULT 'M' NULL,
	CONSTRAINT PK_B_MEETING_USERS PRIMARY KEY (MEETING_ID, USER_ID),
	CONSTRAINT fk_b_meeting_users_1 FOREIGN KEY (MEETING_ID) REFERENCES b_meeting(ID),
	CONSTRAINT fk_b_meeting_users_2 FOREIGN KEY (USER_ID) REFERENCES b_user(ID)
)
/
CREATE TABLE b_meeting_item
(
	ID NUMBER(18) NOT NULL,
	TITLE VARCHAR(255 CHAR) NULL,
	DESCRIPTION CLOB NULL,
	CONSTRAINT pk_b_meeting_item PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_meeting_item
/

CREATE OR REPLACE TRIGGER b_meeting_item_insert
BEFORE INSERT
ON b_meeting_item
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_meeting_item.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE b_meeting_item_files
(
	ITEM_ID NUMBER(18) NOT NULL,
	FILE_ID NUMBER(18) NOT NULL,
	FILE_SRC NUMBER(18) NULL,
	CONSTRAINT PK_B_MEETING_ITEM_FILES PRIMARY KEY (ITEM_ID, FILE_ID),
	CONSTRAINT fk_b_meeting_item_files_1 FOREIGN KEY (ITEM_ID) REFERENCES b_meeting_item(ID)
)
/
CREATE INDEX ix_b_meeting_item_files_1 ON b_meeting_item_files(FILE_SRC)
/

CREATE TABLE b_meeting_instance
(
	ID NUMBER(18) NOT NULL,
	ITEM_ID NUMBER(18) NOT NULL,
	MEETING_ID NUMBER(18) NOT NULL,
	INSTANCE_PARENT_ID NUMBER(18) NULL,
	INSTANCE_TYPE CHAR(1 CHAR) DEFAULT 'A' NULL,
	ORIGINAL_TYPE CHAR(1 CHAR) DEFAULT 'A' NULL,
	SORT NUMBER(18) DEFAULT 500 NULL,
	DURATION NUMBER(5) NULL,
	DEADLINE DATE NULL,
	TASK_ID NUMBER(18) NULL,
	CONSTRAINT pk_b_meeting_instance PRIMARY KEY (ID),
	CONSTRAINT fk_b_meeting_instance_1 FOREIGN KEY (ITEM_ID) REFERENCES b_meeting_item(ID),
	CONSTRAINT fk_b_meeting_instance_2 FOREIGN KEY (MEETING_ID) REFERENCES b_meeting(ID)
)
/
CREATE INDEX ix_b_meeting_instance_1 ON b_meeting_instance (MEETING_ID)
/
CREATE INDEX ix_b_meeting_instance_2 ON b_meeting_instance (ITEM_ID)
/
CREATE SEQUENCE sq_b_meeting_instance
/
CREATE OR REPLACE TRIGGER b_meeting_instance_insert
BEFORE INSERT
ON b_meeting_instance
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_meeting_instance.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE b_meeting_item_tasks
(
	ITEM_ID NUMBER(18) NOT NULL,
	INSTANCE_ID NUMBER(18) NULL,
	TASK_ID NUMBER(18) NOT NULL,
	CONSTRAINT PK_B_MEETING_ITEM_TASKS PRIMARY KEY (ITEM_ID, TASK_ID),
	CONSTRAINT fk_b_meeting_item_tasks_1 FOREIGN KEY (ITEM_ID) REFERENCES b_meeting_item(ID),
	CONSTRAINT fk_b_meeting_item_tasks_2 FOREIGN KEY (INSTANCE_ID) REFERENCES b_meeting_instance(ID)
)
/
CREATE INDEX ix_b_meeting_item_tasks_1 ON b_meeting_item_tasks(INSTANCE_ID)
/


CREATE TABLE b_meeting_instance_users
(
	USER_ID NUMBER(18) NOT NULL,
	INSTANCE_ID NUMBER(18) NOT NULL,
	ITEM_ID NUMBER(18) NOT NULL,
	MEETING_ID NUMBER(18) NOT NULL,
	CONSTRAINT pk_b_meeting_instance_users PRIMARY KEY (INSTANCE_ID, USER_ID),
	CONSTRAINT fk_b_meeting_instance_users_1 FOREIGN KEY (USER_ID) REFERENCES b_user(ID),
	CONSTRAINT fk_b_meeting_instance_users_2 FOREIGN KEY (INSTANCE_ID) REFERENCES b_meeting_instance(ID),
	CONSTRAINT fk_b_meeting_instance_users_3 FOREIGN KEY (ITEM_ID) REFERENCES b_meeting_item(ID),
	CONSTRAINT fk_b_meeting_instance_users_4 FOREIGN KEY (MEETING_ID) REFERENCES b_meeting(ID)
)
/

CREATE TABLE b_meeting_reports
(
	ID NUMBER(18) NOT NULL,
	USER_ID NUMBER(18) NOT NULL,
	INSTANCE_ID NUMBER(18) NOT NULL,
	ITEM_ID NUMBER(18) NOT NULL,
	MEETING_ID NUMBER(18) NOT NULL,
	REPORT CLOB,
	CONSTRAINT pk_b_meeting_reports PRIMARY KEY (ID),
	CONSTRAINT fk_b_meeting_reports_1 FOREIGN KEY (USER_ID) REFERENCES b_user(ID),
	CONSTRAINT fk_b_meeting_reports_2 FOREIGN KEY (INSTANCE_ID) REFERENCES b_meeting_instance(ID),
	CONSTRAINT fk_b_meeting_reports_3 FOREIGN KEY (ITEM_ID) REFERENCES b_meeting_item(ID),
	CONSTRAINT fk_b_meeting_reports_4 FOREIGN KEY (MEETING_ID) REFERENCES b_meeting(ID)
)
/
CREATE UNIQUE INDEX ix_b_meeting_reports_1 ON b_meeting_reports (INSTANCE_ID, USER_ID)
/
CREATE SEQUENCE sq_b_meeting_reports
/
CREATE OR REPLACE TRIGGER b_meeting_reports_insert
BEFORE INSERT
ON b_meeting_reports
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_meeting_reports.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE b_meeting_reports_files
(
	FILE_ID NUMBER(18) NOT NULL,
	FILE_SRC NUMBER(18) NULL,
	REPORT_ID NUMBER(18) NOT NULL,
	INSTANCE_ID NUMBER(18) NOT NULL,
	ITEM_ID NUMBER(18) NOT NULL,
	MEETING_ID NUMBER(18) NOT NULL,
	CONSTRAINT pk_b_meeting_reports_files PRIMARY KEY (REPORT_ID, FILE_ID),
	CONSTRAINT fk_b_meeting_reports_files_1 FOREIGN KEY (FILE_ID) REFERENCES b_file(ID),
	CONSTRAINT fk_b_meeting_reports_files_2 FOREIGN KEY (REPORT_ID) REFERENCES b_meeting_reports(ID),
	CONSTRAINT fk_b_meeting_reports_files_3 FOREIGN KEY (INSTANCE_ID) REFERENCES b_meeting_instance(ID),
	CONSTRAINT fk_b_meeting_reports_files_4 FOREIGN KEY (ITEM_ID) REFERENCES b_meeting_item(ID),
	CONSTRAINT fk_b_meeting_reports_files_5 FOREIGN KEY (MEETING_ID) REFERENCES b_meeting(ID)
)
/
