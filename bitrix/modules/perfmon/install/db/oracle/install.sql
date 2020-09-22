CREATE TABLE B_PERF_HIT
(
	ID NUMBER(18) NOT NULL,
	DATE_HIT DATE,
	IS_ADMIN CHAR(1 CHAR),
	REQUEST_METHOD VARCHAR2(50 CHAR),
	SERVER_NAME VARCHAR2(50 CHAR),
	SERVER_PORT NUMBER(6),
	SCRIPT_NAME VARCHAR2(2000 CHAR),
	REQUEST_URI VARCHAR2(2000 CHAR),
	INCLUDED_FILES NUMBER(18),
	MEMORY_PEAK_USAGE NUMBER(18),
	CACHE_TYPE CHAR(1 CHAR),
	CACHE_SIZE NUMBER(11),
	CACHE_COUNT_R NUMBER(11),
	CACHE_COUNT_W NUMBER(11),
	CACHE_COUNT_C NUMBER(11),
	QUERIES NUMBER(11),
	QUERIES_TIME NUMBER,
	COMPONENTS NUMBER(11),
	COMPONENTS_TIME NUMBER,
	SQL_LOG CHAR(1 CHAR),
	PAGE_TIME NUMBER,
	PROLOG_TIME NUMBER,
	PROLOG_BEFORE_TIME NUMBER,
	AGENTS_TIME NUMBER,
	PROLOG_AFTER_TIME NUMBER,
	WORK_AREA_TIME NUMBER,
	EPILOG_TIME NUMBER,
	EPILOG_BEFORE_TIME NUMBER,
	EVENTS_TIME NUMBER,
	EPILOG_AFTER_TIME NUMBER,
	MENU_RECALC NUMBER(11),
	CONSTRAINT PK_B_PERF_HIT PRIMARY KEY (ID)
)
/
CREATE INDEX IX_B_PERF_HIT_0 ON B_PERF_HIT(DATE_HIT)
/
CREATE SEQUENCE SQ_B_PERF_HIT
/

CREATE TABLE B_PERF_COMPONENT
(
	ID NUMBER(18) NOT NULL,
	HIT_ID NUMBER(18),
	NN NUMBER(18),
	CACHE_TYPE CHAR(1 CHAR),
	CACHE_SIZE NUMBER(11),
	CACHE_COUNT_R NUMBER(11),
	CACHE_COUNT_W NUMBER(11),
	CACHE_COUNT_C NUMBER(11),
	COMPONENT_TIME NUMBER,
	QUERIES NUMBER(11),
	QUERIES_TIME NUMBER,
	COMPONENT_NAME VARCHAR2(2000 CHAR),
	CONSTRAINT PK_B_PERF_COMPONENT PRIMARY KEY (ID)
)
/
CREATE UNIQUE INDEX IX_B_PERF_COMPONENT_0 ON B_PERF_COMPONENT(HIT_ID, NN)
/
CREATE SEQUENCE SQ_B_PERF_COMPONENT
/

CREATE TABLE B_PERF_SQL
(
	ID NUMBER(18) NOT NULL,
	HIT_ID NUMBER(18),
	COMPONENT_ID NUMBER(18),
	NN NUMBER(18),
	QUERY_TIME NUMBER,
	NODE_ID NUMBER(18),
	MODULE_NAME VARCHAR2(2000 CHAR),
	COMPONENT_NAME VARCHAR2(2000 CHAR),
	SQL_TEXT CLOB,
	CONSTRAINT PK_B_PERF_SQL PRIMARY KEY (ID)
)
/
CREATE UNIQUE INDEX IX_B_PERF_SQL_0 ON B_PERF_SQL(HIT_ID, NN)
/
CREATE INDEX IX_B_PERF_SQL_1 ON B_PERF_SQL(COMPONENT_ID)
/
CREATE SEQUENCE SQ_B_PERF_SQL
/

CREATE TABLE B_PERF_SQL_BACKTRACE
(
	SQL_ID NUMBER(18) NOT NULL,
	NN NUMBER(18) NOT NULL,
	FILE_NAME VARCHAR2(500 CHAR),
	LINE_NO NUMBER(18),
	CLASS_NAME VARCHAR2(500 CHAR),
	FUNCTION_NAME VARCHAR2(500 CHAR),
	CONSTRAINT PK_B_PERF_SQL_BACKTRACE PRIMARY KEY (SQL_ID, NN)
)
/

CREATE TABLE B_PERF_CACHE
(
	ID NUMBER(18) NOT NULL,
	HIT_ID NUMBER(18) NULL,
	COMPONENT_ID NUMBER(18) NULL,
	NN NUMBER(18) NOT NULL,
	CACHE_SIZE NUMBER(18) NULL,
	OP_MODE CHAR(1 CHAR),
	MODULE_NAME VARCHAR2(2000 CHAR),
	COMPONENT_NAME VARCHAR2(2000 CHAR),
	BASE_DIR VARCHAR2(2000 CHAR),
	INIT_DIR VARCHAR2(2000 CHAR),
	FILE_NAME VARCHAR2(2000 CHAR),
	FILE_PATH VARCHAR2(2000 CHAR),
	CONSTRAINT PK_B_PERF_CACHE PRIMARY KEY (ID)
)
/
CREATE SEQUENCE SQ_B_PERF_CACHE
/
CREATE OR REPLACE TRIGGER B_PERF_CACHE_INSERT
BEFORE INSERT
ON B_PERF_CACHE
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT SQ_B_PERF_CACHE.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE UNIQUE INDEX IX_B_PERF_CACHE_0 ON B_PERF_CACHE (HIT_ID, NN)
/
CREATE INDEX IX_B_PERF_CACHE_1 ON B_PERF_CACHE (COMPONENT_ID)
/

CREATE TABLE B_PERF_ERROR
(
	ID NUMBER(18) NOT NULL,
	HIT_ID NUMBER(18) NULL,
	ERRNO NUMBER(18) NULL,
	ERRSTR VARCHAR2(2000 CHAR) NULL,
	ERRFILE VARCHAR2(2000 CHAR) NULL,
	ERRLINE NUMBER(18) NULL,
	CONSTRAINT PK_B_PERF_ERROR PRIMARY KEY (ID)
)
/
CREATE INDEX IX_B_PERF_ERROR_0 ON B_PERF_ERROR(HIT_ID)
/
CREATE SEQUENCE SQ_B_PERF_ERROR
/

CREATE TABLE B_PERF_TEST
(
	ID NUMBER(18) NOT NULL,
	REFERENCE_ID NUMBER(18),
	NAME VARCHAR2(200 CHAR),
	CONSTRAINT PK_B_B_PERF_TEST PRIMARY KEY (ID)
)
/
CREATE INDEX IX_B_PERF_TEST_0 ON B_PERF_TEST(REFERENCE_ID)
/
CREATE SEQUENCE SQ_B_PERF_TEST START WITH 1 MINVALUE 1 MAXVALUE 1000000 CYCLE
/
CREATE OR REPLACE TRIGGER B_PERF_TEST_INSERT
BEFORE INSERT
ON B_PERF_TEST
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT SQ_B_PERF_TEST.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE B_PERF_CLUSTER
(
	ID NUMBER(18) NOT NULL,
	TIMESTAMP_X DATE,
	THREADS NUMBER(11),
	HITS NUMBER(11),
	ERRORS NUMBER(11),
	PAGES_PER_SECOND NUMBER,
	PAGE_EXEC_TIME NUMBER,
	PAGE_RESP_TIME NUMBER,
	CONSTRAINT PK_B_PERF_CLUSTER PRIMARY KEY (ID)
)
/
CREATE SEQUENCE SQ_B_PERF_CLUSTER START WITH 1 MINVALUE 1 MAXVALUE 1000000 CYCLE
/
CREATE OR REPLACE TRIGGER B_PERF_CLUSTER_INSERT
BEFORE INSERT
ON B_PERF_CLUSTER
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT SQ_B_PERF_CLUSTER.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE B_PERF_HISTORY
(
	ID NUMBER(18) NOT NULL,
	TIMESTAMP_X DATE,
	TOTAL_MARK NUMBER,
	ACCELERATOR_ENABLED CHAR(1 CHAR),
	CONSTRAINT PK_B_PERF_HISTORY PRIMARY KEY (ID)
)
/
CREATE SEQUENCE SQ_B_PERF_HISTORY
/
