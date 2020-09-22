create table b_calendar_type
(
	XML_ID varchar(255) not null,
	NAME  varchar(255) null,
	DESCRIPTION text null,
	EXTERNAL_ID varchar(100) null,
	ACTIVE char(1) not null default 'Y',
	primary key (XML_ID)
)
GO

create table b_calendar_section
(
	ID int not null IDENTITY (1, 1),
	NAME varchar(255) null,
	XML_ID varchar(100) null,
	EXTERNAL_ID varchar(100) null,
	GAPI_CALENDAR_ID varchar(100) null,
	ACTIVE char(1) not null default 'Y',
	DESCRIPTION text null,
	COLOR varchar(10) null,
	TEXT_COLOR varchar(10) null,
	EXPORT varchar(255) null,
	SORT int not null default 100,
	CAL_TYPE varchar(100) null,
	OWNER_ID int null,
	CREATED_BY int not null,
	PARENT_ID int null,
	DATE_CREATE  datetime null,
	TIMESTAMP_X  datetime null,
	DAV_EXCH_CAL varchar(255) null,
	DAV_EXCH_MOD varchar(255) null,
	CAL_DAV_CON varchar(255) null,
	CAL_DAV_CAL varchar(255) null,
	CAL_DAV_MOD varchar(255) null,
	IS_EXCHANGE char(1) null,
	primary key (ID)
)
GO

CREATE INDEX ix_cal_sect_owner ON b_calendar_section (CAL_TYPE, OWNER_ID)
GO

create table b_calendar_event
(
	ID int not null IDENTITY (1, 1),
	ACTIVE char(1) not null default 'Y',
	DELETED char(1) not null default 'N',
	CAL_TYPE varchar(100) null,
	OWNER_ID int not null,
	CREATED_BY int not null,
	DATE_CREATE  datetime null,
	TIMESTAMP_X  datetime null,
	NAME varchar(255) null,
	DESCRIPTION text null,
	DT_FROM datetime null,
	DT_TO datetime null,
	DT_SKIP_TIME char(1) null,
	DT_LENGTH bigint null,
	PRIVATE_EVENT varchar(10) null,
	ACCESSIBILITY varchar(10) null,
	IMPORTANCE varchar(10) null,
	IS_MEETING varchar(1) null,
	MEETING_HOST int null,
	MEETING text null,
	LOCATION varchar(255) null,
	REMIND varchar(255) null,
	COLOR varchar(10) null,
	TEXT_COLOR varchar(10) null,
	RRULE varchar(255) null,
	EXDATE text null,
	DAV_XML_ID varchar(255) null,
	DAV_EXCH_LABEL varchar(255) null,
	CAL_DAV_LABEL varchar(255) null,
	VERSION varchar(255) null,
	ATTENDEES_CODES varchar(255) null,
	PARENT_ID int null,
	DATE_FROM datetime null,
	DATE_TO datetime null,
	TZ_FROM varchar(50) null,
	TZ_TO varchar(50) null,
	TZ_OFFSET_FROM int null,
	TZ_OFFSET_TO int null,
	DATE_FROM_TS_UTC int null,
	DATE_TO_TS_UTC int null,
	MEETING_STATUS char null, /* H - host, Y-yes, N-no, Q-not answered, M-maybe */
	EVENT_TYPE varchar(50) null,
	RECURRENCE_ID int null,
	RELATIONS varchar(255) null,
	primary key (ID)
)
GO

CREATE INDEX ix_cal_event_date_utc ON b_calendar_event (DATE_FROM_TS_UTC, DATE_TO_TS_UTC)
GO

CREATE INDEX ix_cal_event_owner_id_date ON b_calendar_event (OWNER_ID, DATE_FROM_TS_UTC, DATE_TO_TS_UTC)
GO

CREATE INDEX ix_cal_event_parent_id ON b_calendar_event (PARENT_ID)
GO

CREATE INDEX ix_cal_event_created_by ON b_calendar_event (CREATED_BY)
GO

CREATE INDEX ix_cal_event_owner_id_accessibility ON b_calendar_event (ACCESSIBILITY,DATE_FROM_TS_UTC, DATE_TO_TS_UTC)
GO

create table b_calendar_event_sect
(
	EVENT_ID int not null,
	SECT_ID int not null,
	REL  varchar(10) null,
	primary key (EVENT_ID, SECT_ID)
)
GO

create table b_calendar_attendees
(
	EVENT_ID int not null,
	USER_KEY varchar(255) not null,
	USER_ID int null,
	USER_EMAIL varchar(255) null,
	USER_NAME varchar(255) null,

	STATUS varchar(10) not null default 'Q',
	DESCRIPTION varchar(255) null,
	ACCESSIBILITY varchar(10) null,
	REMIND varchar(255) null,
	SECT_ID int null,

	COLOR varchar(10) null,
	TEXT_COLOR varchar(10) null,

	primary key (EVENT_ID, USER_KEY)
)
GO

CREATE INDEX ix_cal_attendees_0 ON b_calendar_attendees (USER_KEY)
GO

create table b_calendar_access
(
	ACCESS_CODE varchar(100) not null,
	TASK_ID int not null,
	SECT_ID varchar(100) not null,
	primary key (ACCESS_CODE, TASK_ID, SECT_ID)
)
GO
