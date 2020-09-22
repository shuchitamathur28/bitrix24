CREATE TABLE B_PERF_HIT
(
	ID int NOT NULL IDENTITY (1, 1),
	DATE_HIT datetime,
	IS_ADMIN char(1),
	REQUEST_METHOD varchar(50),
	SERVER_NAME varchar(50),
	SERVER_PORT int,
	SCRIPT_NAME varchar(2000),
	REQUEST_URI varchar(2000),
	INCLUDED_FILES int,
	MEMORY_PEAK_USAGE int,
	CACHE_TYPE char(1),
	CACHE_SIZE int,
	CACHE_COUNT_R int,
	CACHE_COUNT_W int,
	CACHE_COUNT_C int,
	QUERIES int,
	QUERIES_TIME float,
	COMPONENTS int,
	COMPONENTS_TIME float,
	SQL_LOG char(1),
	PAGE_TIME float,
	PROLOG_TIME float,
	PROLOG_BEFORE_TIME float,
	AGENTS_TIME float,
	PROLOG_AFTER_TIME float,
	WORK_AREA_TIME float,
	EPILOG_TIME float,
	EPILOG_BEFORE_TIME float,
	EVENTS_TIME float,
	EPILOG_AFTER_TIME float,
	MENU_RECALC int
)
GO
ALTER TABLE B_PERF_HIT ADD CONSTRAINT PK_B_PERF_HIT PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_PERF_HIT_0 ON B_PERF_HIT(DATE_HIT)
GO

CREATE TABLE B_PERF_COMPONENT
(
	ID int NOT NULL IDENTITY (1, 1),
	HIT_ID int,
	NN int,
	CACHE_TYPE char(1),
	CACHE_SIZE int,
	CACHE_COUNT_R int,
	CACHE_COUNT_W int,
	CACHE_COUNT_C int,
	COMPONENT_TIME float,
	QUERIES int,
	QUERIES_TIME float,
	COMPONENT_NAME varchar(2000)
)
GO
ALTER TABLE B_PERF_COMPONENT ADD CONSTRAINT PK_B_PERF_COMPONENT PRIMARY KEY (ID)
GO
CREATE UNIQUE INDEX IX_B_PERF_COMPONENT_0 ON B_PERF_COMPONENT(HIT_ID, NN)
GO

CREATE TABLE B_PERF_SQL
(
	ID int NOT NULL IDENTITY (1, 1),
	HIT_ID int,
	COMPONENT_ID int,
	NN int,
	QUERY_TIME float,
	NODE_ID int,
	MODULE_NAME varchar(2000),
	COMPONENT_NAME varchar(2000),
	SQL_TEXT text
)
GO
ALTER TABLE B_PERF_SQL ADD CONSTRAINT PK_B_PERF_SQL PRIMARY KEY (ID)
GO
CREATE UNIQUE INDEX IX_B_PERF_SQL_0 ON B_PERF_SQL(HIT_ID, NN)
GO
CREATE INDEX IX_B_PERF_SQL_1 ON B_PERF_SQL(COMPONENT_ID)
GO

CREATE TABLE B_PERF_SQL_BACKTRACE
(
	SQL_ID int not null,
	NN int not null,
	FILE_NAME varchar(500),
	LINE_NO int,
	CLASS_NAME varchar(500),
	FUNCTION_NAME varchar(500),
	CONSTRAINT PK_B_PERF_SQL_BACKTRACE PRIMARY KEY (SQL_ID, NN)
)
GO

CREATE TABLE B_PERF_CACHE
(
	ID int NOT NULL IDENTITY (1, 1),
	HIT_ID int,
	COMPONENT_ID int,
	NN int,
	CACHE_SIZE int,
	OP_MODE char(1),
	MODULE_NAME varchar(2000),
	COMPONENT_NAME varchar(2000),
	BASE_DIR varchar(2000),
	INIT_DIR varchar(2000),
	FILE_NAME varchar(2000),
	FILE_PATH varchar(2000),
	CONSTRAINT PK_B_PERF_CACHE PRIMARY KEY (ID)
)
GO
CREATE UNIQUE INDEX IX_B_PERF_CACHE_0 ON B_PERF_CACHE (HIT_ID, NN)
GO
CREATE INDEX IX_B_PERF_CACHE_1 ON B_PERF_CACHE (COMPONENT_ID)
GO

CREATE TABLE B_PERF_ERROR
(
	ID int NOT NULL IDENTITY (1, 1),
	HIT_ID int,
	ERRNO int,
	ERRSTR varchar(2000),
	ERRFILE varchar(2000),
	ERRLINE int
)
GO
ALTER TABLE B_PERF_ERROR ADD CONSTRAINT PK_B_PERF_ERROR PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_PERF_ERROR_0 ON B_PERF_ERROR(HIT_ID)
GO

CREATE TABLE B_PERF_TEST
(
	ID int NOT NULL IDENTITY (1, 1),
	REFERENCE_ID int,
	NAME varchar(200)
)
GO
ALTER TABLE B_PERF_TEST ADD CONSTRAINT PK_B_PERF_TEST PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_PERF_TEST_0 ON B_PERF_TEST(REFERENCE_ID)
GO

CREATE TABLE B_PERF_CLUSTER
(
	ID int NOT NULL IDENTITY (1, 1),
	TIMESTAMP_X datetime,
	THREADS int,
	HITS int,
	ERRORS int,
	PAGES_PER_SECOND float,
	PAGE_EXEC_TIME float,
	PAGE_RESP_TIME float
)
GO
ALTER TABLE B_PERF_CLUSTER ADD CONSTRAINT PK_B_PERF_CLUSTER PRIMARY KEY (ID)
GO

CREATE TABLE B_PERF_HISTORY
(
	ID int NOT NULL IDENTITY (1, 1),
	TIMESTAMP_X datetime,
	TOTAL_MARK float,
	ACCELERATOR_ENABLED char(1),
	CONSTRAINT PK_B_PERF_HISTORY PRIMARY KEY (ID)
)
GO