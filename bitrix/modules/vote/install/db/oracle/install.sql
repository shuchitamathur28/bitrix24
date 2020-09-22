CREATE TABLE B_VOTE_CHANNEL (
	ID 				NUMBER(18) NOT NULL,
	SYMBOLIC_NAME 	VARCHAR2(255 CHAR) NOT NULL,
	C_SORT 			NUMBER(18) DEFAULT 100 NULL,
	FIRST_SITE_ID	CHAR(2 CHAR) NULL,
	ACTIVE 			CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	HIDDEN 			CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	TIMESTAMP_X 	DATE NOT NULL,
	TITLE 			VARCHAR2(255 CHAR) NOT NULL,
	VOTE_SINGLE 	CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
    USE_CAPTCHA     CHAR(1 CHAR) DEFAULT 'N' NOT NULL
)
/
ALTER TABLE B_VOTE_CHANNEL ADD CONSTRAINT PK_B_VOTE_CHANNEL_ID PRIMARY KEY (ID)
/
CREATE TABLE B_VOTE_CHANNEL_2_GROUP (
	ID 				NUMBER(18) NOT NULL,
	CHANNEL_ID 		NUMBER(18) NOT NULL,
	GROUP_ID 		NUMBER(18) NOT NULL,
	PERMISSION 		NUMBER(1) DEFAULT 0 NOT NULL
)
/
CREATE INDEX IX_VOTE_CHANNEL_ID_GROUP_ID ON B_VOTE_CHANNEL_2_GROUP (CHANNEL_ID, GROUP_ID)
/
ALTER TABLE B_VOTE_CHANNEL_2_GROUP ADD CONSTRAINT PK_B_VOTE_CHANNEL_GROUP_ID PRIMARY KEY (ID)
/
CREATE TABLE B_VOTE_CHANNEL_2_SITE (
	CHANNEL_ID 		NUMBER(18) NOT NULL,
	SITE_ID 		CHAR(2 CHAR) NOT NULL,
	PRIMARY KEY (CHANNEL_ID,SITE_ID)
)
/
CREATE TABLE B_VOTE (
	ID 				NUMBER(18) NOT NULL,
	CHANNEL_ID 		NUMBER(18) NOT NULL,
	C_SORT 			NUMBER(18) DEFAULT 100 NULL,
	ACTIVE 			CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	NOTIFY 			CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	AUTHOR_ID 		NUMBER(18) NULL,
	TIMESTAMP_X 	DATE NOT NULL,
	DATE_START 		DATE NOT NULL,
	DATE_END 		DATE NOT NULL,
	URL				VARCHAR2(255 CHAR) NULL,
	COUNTER 		NUMBER(18) DEFAULT 0 NOT NULL,
	TITLE 			VARCHAR2(255 CHAR) NULL,
	DESCRIPTION 	VARCHAR2(2000 CHAR) NULL,
	DESCRIPTION_TYPE VARCHAR2(4 CHAR) DEFAULT 'html' NOT NULL,
	IMAGE_ID 		NUMBER(18) NULL,
	EVENT1 			VARCHAR2(255 CHAR) NULL,
	EVENT2 			VARCHAR2(255 CHAR) NULL,
	EVENT3 			VARCHAR2(255 CHAR) NULL,
	UNIQUE_TYPE 	NUMBER(18) DEFAULT 2 NOT NULL,
	KEEP_IP_SEC		NUMBER(18) DEFAULT NULL NULL,
	TEMPLATE 		VARCHAR2(255 CHAR) NULL,
	RESULT_TEMPLATE	VARCHAR2(255 CHAR) NULL
)
/
CREATE INDEX IX_CHANNEL_ID ON B_VOTE(CHANNEL_ID)
/
ALTER TABLE B_VOTE ADD CONSTRAINT PK_B_VOTE_ID PRIMARY KEY (ID)
/
CREATE TABLE B_VOTE_QUESTION (
	ID 				NUMBER(18) NOT NULL,
	ACTIVE 			CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	TIMESTAMP_X 	DATE NOT NULL,
	VOTE_ID 		NUMBER(18) NOT NULL,
	C_SORT 			NUMBER(18) DEFAULT 100 NULL,
	COUNTER 		NUMBER(18) DEFAULT 0 NOT NULL,
	QUESTION 		CLOB NOT NULL,
	QUESTION_TYPE 	VARCHAR2(4 CHAR) DEFAULT 'html' NOT NULL,
	IMAGE_ID 		NUMBER(18) NULL,
	DIAGRAM 		CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	REQUIRED 		CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	DIAGRAM_TYPE 	VARCHAR2(10 CHAR) DEFAULT 'histogram' NOT NULL,
	TEMPLATE 		VARCHAR2(255 CHAR) NULL,
	TEMPLATE_NEW 	VARCHAR2(255 CHAR) NULL,
	CONSTRAINT FK_VOTE_QUESTION
	FOREIGN KEY (VOTE_ID)
	REFERENCES B_VOTE (ID)
	ON DELETE CASCADE
)
/
CREATE INDEX IX_VOTE_ID ON B_VOTE_QUESTION(VOTE_ID)
/
ALTER TABLE B_VOTE_QUESTION ADD CONSTRAINT PK_B_VOTE_QUESTION_ID PRIMARY KEY (ID)
/
CREATE TABLE B_VOTE_ANSWER (
	ID 				NUMBER(18) NOT NULL,
	ACTIVE 			CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	TIMESTAMP_X 	DATE NOT NULL,
	QUESTION_ID 	NUMBER(18) NOT NULL,
	C_SORT 			NUMBER(18) DEFAULT 100 NULL,
	MESSAGE 		CLOB NULL,
	MESSAGE_TYPE	VARCHAR2(4 CHAR) DEFAULT 'html' NOT NULL,
	COUNTER 		NUMBER(18) DEFAULT 0 NOT NULL,
	FIELD_TYPE 		NUMBER(5) DEFAULT 0 NOT NULL,
	FIELD_WIDTH 	NUMBER(18) NULL,
	FIELD_HEIGHT 	NUMBER(18) NULL,
	FIELD_PARAM 	VARCHAR2(255 CHAR) NULL,
	COLOR 			VARCHAR2(7 CHAR) NULL,
	CONSTRAINT FK_QUESTION_ANSWER
	FOREIGN KEY (QUESTION_ID)
	REFERENCES B_VOTE_QUESTION (ID)
	ON DELETE CASCADE
)
/
CREATE INDEX IX_QUESTION_ID ON B_VOTE_ANSWER(QUESTION_ID)
/
ALTER TABLE B_VOTE_ANSWER ADD CONSTRAINT PK_B_VOTE_ANSWER_ID PRIMARY KEY (ID)
/
CREATE TABLE B_VOTE_EVENT (
	ID 				NUMBER(18) NOT NULL,
	VOTE_ID 		NUMBER(18) NOT NULL,
	VOTE_USER_ID 	NUMBER(18) NOT NULL,
	DATE_VOTE 		DATE NOT NULL,
	STAT_SESSION_ID NUMBER(18) NULL,
	IP 				VARCHAR2(15 CHAR) NULL,
	VALID 			CHAR(1 CHAR) DEFAULT 'Y' NOT NULL
)
/
CREATE INDEX IX_USER_ID ON B_VOTE_EVENT(VOTE_USER_ID)
/
CREATE INDEX IX_B_VOTE_EVENT_2 ON B_VOTE_EVENT(VOTE_ID,IP)
/
ALTER TABLE B_VOTE_EVENT ADD CONSTRAINT PK_B_VOTE_EVENT_ID PRIMARY KEY (ID)
/
CREATE TABLE B_VOTE_EVENT_QUESTION 
(
	ID 				NUMBER(18) NOT NULL,
	EVENT_ID 		NUMBER(18) NOT NULL,
	QUESTION_ID 	NUMBER(18) NOT NULL,
	CONSTRAINT 		FK_VOTE_EVENT_QUESTION
	FOREIGN KEY (EVENT_ID)
	REFERENCES B_VOTE_EVENT (ID)
	ON DELETE CASCADE
)
/
CREATE INDEX IX_EVENT_ID ON B_VOTE_EVENT_QUESTION(EVENT_ID)
/
ALTER TABLE B_VOTE_EVENT_QUESTION ADD CONSTRAINT PK_B_VOTE_EVENT_QUESTION_ID PRIMARY KEY (ID)
/
CREATE TABLE B_VOTE_EVENT_ANSWER 
(
	ID 				NUMBER(18) NOT NULL,
	EVENT_QUESTION_ID NUMBER(18) NOT NULL,
	ANSWER_ID 		NUMBER(18) NOT NULL,
	MESSAGE 		VARCHAR2(2000 CHAR) NULL,
	CONSTRAINT FK_EVENT_QUESTION_ANSWER
	FOREIGN KEY (EVENT_QUESTION_ID)
	REFERENCES B_VOTE_EVENT_QUESTION (ID)
	ON DELETE CASCADE
)
/
CREATE INDEX IX_EVENT_QUESTION_ID ON B_VOTE_EVENT_ANSWER(EVENT_QUESTION_ID)
/
ALTER TABLE B_VOTE_EVENT_ANSWER ADD CONSTRAINT PK_B_VOTE_EVENT_ANSWER_ID PRIMARY KEY (ID)
/
CREATE TABLE B_VOTE_USER 
(
	ID 				NUMBER(18) NOT NULL,
	STAT_GUEST_ID 	NUMBER(18) NULL,
	AUTH_USER_ID 	NUMBER(18) NULL,
	COUNTER 		NUMBER(18) DEFAULT 0 NOT NULL,
	DATE_FIRST 		DATE NOT NULL,
	DATE_LAST 		DATE NOT NULL,
	LAST_IP 		VARCHAR2(15 CHAR) NULL
)
/
ALTER TABLE B_VOTE_USER ADD CONSTRAINT PK_B_VOTE_USER_ID PRIMARY KEY (ID)
/
CREATE SEQUENCE SQ_B_VOTE START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE SEQUENCE SQ_B_VOTE_ANSWER START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE SEQUENCE SQ_B_VOTE_CHANNEL START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE SEQUENCE SQ_B_VOTE_CHANNEL_2_GROUP START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE SEQUENCE SQ_B_VOTE_EVENT START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE SEQUENCE SQ_B_VOTE_EVENT_ANSWER START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE SEQUENCE SQ_B_VOTE_EVENT_QUESTION START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE SEQUENCE SQ_B_VOTE_QUESTION START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE SEQUENCE SQ_B_VOTE_USER START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER B_VOTE_DELETE
BEFORE DELETE 
ON B_VOTE
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW 
BEGIN
	DELFILE(:OLD.IMAGE_ID, NULL);
END;
/
CREATE OR REPLACE TRIGGER B_VOTE_UPDATE
BEFORE UPDATE 
ON B_VOTE
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW 
BEGIN
	DELFILE(:OLD.IMAGE_ID, :NEW.IMAGE_ID);
END;
/
CREATE OR REPLACE TRIGGER B_VOTE_QUESTION_DELETE
BEFORE DELETE 
ON B_VOTE_QUESTION
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW 
BEGIN
	DELFILE(:OLD.IMAGE_ID, NULL);
END;
/
CREATE OR REPLACE TRIGGER B_VOTE_QUESTION_UPDATE
BEFORE UPDATE 
ON B_VOTE_QUESTION
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW 
BEGIN
	DELFILE(:OLD.IMAGE_ID, :NEW.IMAGE_ID);
END;
/
CREATE TABLE B_VOTE_ATTACHED_OBJECT
(
	ID number(18) NOT NULL,
	OBJECT_ID NUMBER(18) not null,

	MODULE_ID VARCHAR2(32 CHAR) not null,
	ENTITY_TYPE VARCHAR2(100 CHAR) not null,
	ENTITY_ID NUMBER(18) not null,

	CREATE_TIME DATE not null,
	CREATED_BY NUMBER(18),

	CONSTRAINT PK_B_VOTE_ATTACHED_OBJECT PRIMARY KEY (ID)
)
/
CREATE SEQUENCE SQ_B_VOTE_ATTACHED_OBJECT START WITH 1 INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER B_VOTE_ATTACHED_OBJECT_ins_tr
BEFORE INSERT
ON B_VOTE_ATTACHED_OBJECT
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
	SELECT SQ_B_VOTE_ATTACHED_OBJECT.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE INDEX IX_VOTE_AO_1 ON B_VOTE_ATTACHED_OBJECT (OBJECT_ID)
/
CREATE INDEX IX_VOTE_AO_2 ON B_VOTE_ATTACHED_OBJECT (MODULE_ID, ENTITY_TYPE, ENTITY_ID)
/
