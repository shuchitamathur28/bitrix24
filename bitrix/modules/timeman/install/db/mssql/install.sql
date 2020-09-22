CREATE TABLE B_TIMEMAN_ENTRIES
(
	ID int NOT NULL IDENTITY (1, 1),
	TIMESTAMP_X datetime NOT NULL,
	USER_ID int NOT NULL,
	MODIFIED_BY int NULL,
	ACTIVE char(1) NOT NULL,
	PAUSED char(1) NULL,
	DATE_START datetime,
	DATE_FINISH datetime,
	TIME_START int,
	TIME_FINISH int,
	DURATION int NULL,
	TIME_LEAKS int NULL,
	TASKS text NULL,
	IP_OPEN varchar(50) NULL,
	IP_CLOSE varchar(50) NULL,
	FORUM_TOPIC_ID int NULL,
	CONSTRAINT PK_B_TIMEMAN_ENTRIES PRIMARY KEY (ID)
)
GO
ALTER TABLE B_TIMEMAN_ENTRIES ADD CONSTRAINT DF_B_TIMEMAN_ENTRIES_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_TIMEMAN_ENTRIES ADD CONSTRAINT DF_B_TIMEMAN_ENTRIES_PAUSED DEFAULT 'N' FOR PAUSED
GO
ALTER TABLE B_TIMEMAN_ENTRIES ADD CONSTRAINT DF_B_TIMEMAN_ENTRIES_TIMESTAMP_X DEFAULT GETDATE() FOR TIMESTAMP_X
GO
CREATE INDEX IX_B_TIMEMAN_ENTRIES_1 ON B_TIMEMAN_ENTRIES (USER_ID, DATE_START)
GO

CREATE TABLE B_TIMEMAN_REPORTS
(
	ID int NOT NULL IDENTITY (1, 1),
	TIMESTAMP_X datetime NOT NULL,
	ENTRY_ID int NOT NULL,
	USER_ID int NOT NULL,
	ACTIVE char(1) NOT NULL,
	REPORT_TYPE varchar(50) NULL,
	REPORT text NULL,
	CONSTRAINT PK_B_TIMEMAN_REPORTS PRIMARY KEY (ID)
)
GO
ALTER TABLE B_TIMEMAN_REPORTS ADD CONSTRAINT DF_B_TIMEMAN_REPORTS_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_TIMEMAN_REPORTS ADD CONSTRAINT DF_B_TIMEMAN_REPORTS_REPORT_TYPE DEFAULT 'REPORT' FOR REPORT_TYPE
GO
ALTER TABLE B_TIMEMAN_REPORTS ADD CONSTRAINT FK_B_TIMEMAN_REPORTS_USER_ID FOREIGN KEY (USER_ID) REFERENCES B_USER(ID)
GO
ALTER TABLE B_TIMEMAN_REPORTS ADD CONSTRAINT FK_B_TIMEMAN_REPORTS_ENTRY_ID FOREIGN KEY (ENTRY_ID) REFERENCES B_TIMEMAN_ENTRIES(ID)
GO
ALTER TABLE B_TIMEMAN_REPORTS ADD CONSTRAINT DF_B_TIMEMAN_REPORTS_TIMESTAMP_X DEFAULT GETDATE() FOR TIMESTAMP_X
GO
CREATE INDEX IX_B_TIMEMAN_REPORTS_1 ON B_TIMEMAN_REPORTS (ENTRY_ID, REPORT_TYPE, ACTIVE)
GO

CREATE TABLE B_TIMEMAN_REPORT_DAILY
(
	ID int NOT NULL IDENTITY (1, 1),
	TIMESTAMP_X datetime NOT NULL,
	ACTIVE char(1) NOT NULL,
	USER_ID int NOT NULL,
	ENTRY_ID int NOT NULL,
	REPORT_DATE datetime,
	TASKS text null,
	EVENTS text null,
	REPORT text null,
	MARK int null,
	CONSTRAINT PK_B_TIMEMAN_REPORT_DAILY PRIMARY KEY (ID)
)
GO
ALTER TABLE B_TIMEMAN_REPORT_DAILY ADD CONSTRAINT DF_B_TIMEMAN_REPORT_DAILY_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_TIMEMAN_REPORT_DAILY ADD CONSTRAINT FK_B_TIMEMAN_REPORT_DAILY_USER_ID FOREIGN KEY (USER_ID) REFERENCES B_USER(ID)
GO
ALTER TABLE B_TIMEMAN_REPORT_DAILY ADD CONSTRAINT FK_B_TIMEMAN_REPORT_DAILY_ENTRY_ID FOREIGN KEY (ENTRY_ID) REFERENCES B_TIMEMAN_ENTRIES(ID)
GO
ALTER TABLE B_TIMEMAN_REPORT_DAILY ADD CONSTRAINT DF_B_TIMEMAN_REPORT_DAILY_TIMESTAMP_X DEFAULT GETDATE() FOR TIMESTAMP_X
GO
CREATE INDEX IX_B_TIMEMAN_REPORT_DAILY_2 ON B_TIMEMAN_REPORT_DAILY (ENTRY_ID)
GO
CREATE INDEX IX_B_TIMEMAN_REPORT_DAILY_3 ON B_TIMEMAN_REPORT_DAILY (USER_ID, REPORT_DATE)
GO


CREATE TABLE B_TIMEMAN_REPORT_FULL
(
	ID int NOT NULL IDENTITY (1, 1),
	TIMESTAMP_X datetime NOT NULL,
	ACTIVE char(1) NOT NULL,
	USER_ID int NOT NULL,
	REPORT_DATE datetime,
	DATE_FROM datetime,
	DATE_TO datetime,
	TASKS text null,
	EVENTS text null,
	FILES text null,
	REPORT text null,
	PLANS text null,
	MARK char(1) null,
	APPROVE char(1) null,
	APPROVER int null,
	APPROVE_DATE datetime,
	FORUM_TOPIC_ID int null,
	CONSTRAINT PK_B_TIMEMAN_REPORT_FULL PRIMARY KEY (ID)
)
GO
ALTER TABLE B_TIMEMAN_REPORT_FULL ADD CONSTRAINT DF_B_TIMEMAN_REPORT_FULL_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_TIMEMAN_REPORT_FULL ADD CONSTRAINT DF_B_TIMEMAN_REPORT_FULL_APPROVE DEFAULT 'N' FOR APPROVE
GO
ALTER TABLE B_TIMEMAN_REPORT_FULL ADD CONSTRAINT DF_B_TIMEMAN_REPORT_FULL_MARK DEFAULT 'N' FOR MARK
GO
ALTER TABLE B_TIMEMAN_REPORT_FULL ADD CONSTRAINT DF_B_TIMEMAN_REPORT_FULL_TIMESTAMP_X DEFAULT GETDATE() FOR TIMESTAMP_X
GO
CREATE INDEX IX_B_TIMEMAN_REPORT_FULL_1 ON B_TIMEMAN_REPORT_FULL (USER_ID)
GO
CREATE INDEX IX_B_TIMEMAN_REPORT_FULL_2 ON B_TIMEMAN_REPORT_FULL (ACTIVE, DATE_FROM)
GO