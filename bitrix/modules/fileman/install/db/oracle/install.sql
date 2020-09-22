create table b_medialib_collection
(
	ID int not null,
	NAME varchar2(255 CHAR) not null,
	DESCRIPTION clob null,
	ACTIVE char(1 CHAR) default 'Y' not null,
	DATE_UPDATE date not null,
	OWNER_ID int null,
	PARENT_ID int null,
	SITE_ID char(2 CHAR) null,
	KEYWORDS varchar2(255 CHAR) null,
	ITEMS_COUNT int null,
	ML_TYPE int default 0 not null,
	primary key (ID)
)
/
CREATE SEQUENCE SQ_b_medialib_collection INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

create table b_medialib_collection_item
(
	COLLECTION_ID int not null,
	ITEM_ID int not null,
	primary key (ITEM_ID,COLLECTION_ID)
)
/

create table b_medialib_item
(
	ID int not null,
	NAME varchar2(255 CHAR) not null,
	ITEM_TYPE char(30 CHAR) null,
	DESCRIPTION clob null,
	DATE_CREATE date not null,
	DATE_UPDATE date null,
	SOURCE_ID int not null,
	KEYWORDS varchar2(255 CHAR) null,
	SEARCHABLE_CONTENT CLOB null,
	primary key (ID)
)
/
CREATE SEQUENCE SQ_b_medialib_item INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

create table b_group_collection_task
(
	GROUP_ID int not null,
	TASK_ID int not null,
	COLLECTION_ID int not null,
	primary key (GROUP_ID,TASK_ID,COLLECTION_ID)
)
/

create table b_medialib_type
(
	ID int not null,
	NAME varchar2(255 CHAR) not null,
	CODE varchar2(255 CHAR) not null,
	EXT varchar2(255 CHAR) not null,
	SYSTEM char(1 CHAR) default 'N' not null,
	DESCRIPTION CLOB null,
	primary key (ID)
)
/

CREATE SEQUENCE SQ_b_medialib_type INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_medialib_type_INSERT
BEFORE INSERT
ON b_medialib_type
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_b_medialib_type.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

INSERT INTO b_medialib_type (NAME,CODE,EXT,SYSTEM,DESCRIPTION)
VALUES ('image_name', 'image', 'jpg,jpeg,gif,png', 'Y', 'image_desc')
/

INSERT INTO b_medialib_type (NAME,CODE,EXT,SYSTEM,DESCRIPTION)
VALUES ('video_name','video','flv,mp4,wmv','Y','video_desc')
/

INSERT INTO b_medialib_type (NAME,CODE,EXT,SYSTEM,DESCRIPTION)
VALUES ('sound_name','sound','mp3,wma,aac','Y','sound_desc')
/

create table b_file_search
(
	ID int not null,
	SESS_ID varchar2(255 CHAR) not null,
	TIMESTAMP_X date DEFAULT SYSDATE NOT NULL,

	F_PATH varchar2(255 CHAR) null,
	B_DIR int default 0 not null,
	F_SIZE int default 0 not null,
	F_TIME int default 0 not null,
	primary key (ID)
)
/

CREATE SEQUENCE SQ_b_file_search INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_file_search_INSERT
BEFORE INSERT
ON b_file_search
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_b_file_search.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_file_search_UPDATE
BEFORE UPDATE
ON b_file_search
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

create table b_sticker
(
	ID int not null,
	SITE_ID char(2 CHAR) null,
	PAGE_URL  varchar2(255 CHAR) not null ,
	PAGE_TITLE  varchar2(255 CHAR) not null ,
	DATE_CREATE  date not null,
	DATE_UPDATE  date not null,
	MODIFIED_BY int not null ,
	CREATED_BY int not null ,
	PERSONAL char(1 CHAR) default 'N' not null,
	CONTENT clob null,
	POS_TOP int,
	POS_LEFT int,
	WIDTH int,
	HEIGHT int,
	COLOR int,
	COLLAPSED char(1 CHAR) default 'N' not null,
	COMPLETED char(1 CHAR) default 'N' not null,
	CLOSED char(1 CHAR) default 'N' not null,
	DELETED char(1 CHAR) default 'N' not null,
	MARKER_TOP int,
	MARKER_LEFT int,
	MARKER_WIDTH int,
	MARKER_HEIGHT int,
	MARKER_ADJUST clob null,
	primary key (ID)
)
/

CREATE SEQUENCE SQ_b_sticker INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_sticker_INSERT
BEFORE INSERT
ON b_sticker
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_b_sticker.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

create table b_sticker_group_task
(
	GROUP_ID int not null,
	TASK_ID int not null,
	primary key (GROUP_ID,TASK_ID)
)
/
