create table b_calendar_type
(
	XML_ID varchar2(255 CHAR) not null,
	NAME  varchar2(255 CHAR) null,
	DESCRIPTION clob null,
	EXTERNAL_ID varchar2(100 CHAR) null,
	ACTIVE char(1 CHAR) default 'Y' not null,
	primary key (XML_ID)
)
/
CREATE SEQUENCE SQ_b_calendar_type INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

create table b_calendar_section
(
	ID int not null,
	NAME varchar2(255 CHAR) null,
	XML_ID varchar2(100 CHAR) null,
	EXTERNAL_ID varchar2(100 CHAR) null,
	GAPI_CALENDAR_ID varchar2(100 CHAR) null,
	ACTIVE char(1 CHAR) default 'Y' not null,
	DESCRIPTION clob null,
	COLOR varchar2(10 CHAR) null,
	TEXT_COLOR varchar2(10 CHAR) null,
	EXPORT varchar2(255 CHAR) null,
	SORT int default 100 not null,
	CAL_TYPE varchar2(100 CHAR) null,
	OWNER_ID int null,
	CREATED_BY int not null,
	PARENT_ID int null,
	DATE_CREATE  date null,
	TIMESTAMP_X  date null,
	DAV_EXCH_CAL varchar2(255 CHAR) null,
	DAV_EXCH_MOD varchar2(255 CHAR) null,
	CAL_DAV_CON varchar2(255 CHAR) null,
	CAL_DAV_CAL varchar2(255 CHAR) null,
	CAL_DAV_MOD varchar2(255 CHAR) null,
	IS_EXCHANGE char(1 CHAR) null,
	primary key (ID)
)
/

CREATE SEQUENCE SQ_b_calendar_section INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE INDEX ix_cal_sect_owner ON b_calendar_section (CAL_TYPE, OWNER_ID)
/

CREATE OR REPLACE TRIGGER b_calendar_section_INSERT
BEFORE INSERT
ON b_calendar_section
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_b_calendar_section.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

create table b_calendar_event
(
	ID int not null,
	ACTIVE char(1 CHAR) default 'Y' not null,
	DELETED char(1 CHAR) default 'N' not null,
	CAL_TYPE varchar2(100 CHAR) null,
	OWNER_ID int not null,
	CREATED_BY int not null,
	DATE_CREATE  date null,
	TIMESTAMP_X  date null,
	NAME varchar2(255 CHAR) null,
	DESCRIPTION clob null,
	DT_FROM date null,
	DT_TO date null,
	DT_SKIP_TIME char(1 CHAR) null,
	DT_LENGTH NUMBER null,
	PRIVATE_EVENT varchar2(10 CHAR) null,
	ACCESSIBILITY varchar2(10 CHAR) null,
	IMPORTANCE varchar2(10 CHAR) null,
	IS_MEETING char(1 CHAR) null,
	MEETING_HOST int null,
	MEETING clob null,
	LOCATION varchar2(255 CHAR) null,
	REMIND varchar2(255 CHAR) null,
	COLOR varchar2(10 CHAR) null,
	TEXT_COLOR varchar2(10 CHAR) null,
	RRULE varchar2(255 CHAR) null,
	EXDATE clob null,
	DAV_XML_ID varchar2(255 CHAR) null,
	DAV_EXCH_LABEL varchar2(255 CHAR) null,
	CAL_DAV_LABEL varchar2(255 CHAR) null,
	VERSION varchar2(255 CHAR) null,
	ATTENDEES_CODES varchar2(255 CHAR) null,
	PARENT_ID int null,
	DATE_FROM date null,
	DATE_TO date null,
	TZ_FROM varchar2(50 CHAR) null,
	TZ_TO varchar2(50 CHAR) null,
	TZ_OFFSET_FROM int null,
	TZ_OFFSET_TO int null,
	DATE_FROM_TS_UTC int null,
	DATE_TO_TS_UTC int null,
	MEETING_STATUS char(1 CHAR) null, /* H - host, Y-yes, N-no, Q-not answered, M-maybe */
	EVENT_TYPE varchar2(50 CHAR) null,
	RECURRENCE_ID int null,
	RELATIONS varchar2(255 CHAR) null,
	primary key (ID)
)
/

CREATE SEQUENCE SQ_b_calendar_event INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE INDEX ix_cal_event_date_utc ON b_calendar_event (DATE_FROM_TS_UTC, DATE_TO_TS_UTC)
/

CREATE INDEX ix_cal_event_owner_id_date ON b_calendar_event (OWNER_ID, DATE_FROM_TS_UTC,DATE_TO_TS_UTC)
/

CREATE INDEX ix_cal_event_parent_id ON b_calendar_event (PARENT_ID)
/

CREATE INDEX ix_cal_event_created_by ON b_calendar_event (CREATED_BY)
/

CREATE INDEX ix_cal_event_owner_id_acc ON b_calendar_event (ACCESSIBILITY,DATE_FROM_TS_UTC, DATE_TO_TS_UTC)
/

CREATE OR REPLACE TRIGGER b_calendar_event_INSERT
BEFORE INSERT
ON b_calendar_event
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_b_calendar_event.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

create table b_calendar_event_sect
(
	EVENT_ID int not null,
	SECT_ID int not null,
	REL  varchar2(10 CHAR) null,
	primary key (EVENT_ID, SECT_ID)
)
/

create table b_calendar_attendees
(
	EVENT_ID int not null,
	USER_KEY varchar2(255 CHAR) not null,
	USER_ID int null,
	USER_EMAIL varchar2(255 CHAR) null,
	USER_NAME varchar2(255 CHAR) null,

	STATUS varchar2(10 CHAR) default 'Q' not null,
	DESCRIPTION varchar2(255 CHAR) null,
	ACCESSIBILITY varchar2(10 CHAR) null,
	REMIND varchar2(255 CHAR) null,
	SECT_ID int null,

	COLOR varchar2(10 CHAR) null,
	TEXT_COLOR varchar2(10 CHAR) null,

	primary key (EVENT_ID, USER_KEY)
)
/

CREATE INDEX ix_cal_attendees_0 ON b_calendar_attendees (USER_KEY)
/

create table b_calendar_access
(
	ACCESS_CODE varchar2(100 CHAR) not null,
	TASK_ID int not null,
	SECT_ID varchar2(100 CHAR) not null,
	primary key (ACCESS_CODE, TASK_ID, SECT_ID)
)
/
