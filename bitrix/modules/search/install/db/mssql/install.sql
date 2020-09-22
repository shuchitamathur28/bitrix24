CREATE TABLE b_search_content
(
	ID INT NOT NULL IDENTITY(1,1),
	DATE_CHANGE DATETIME NOT NULL,
	MODULE_ID VARCHAR(50) NOT NULL,
	ITEM_ID VARCHAR(255) NOT NULL,
	CUSTOM_RANK INT NOT NULL,
	USER_ID INT,
	ENTITY_TYPE_ID VARCHAR(50),
	ENTITY_ID VARCHAR(255),
	URL TEXT,
	TITLE TEXT,
	BODY TEXT,
	TAGS TEXT,
	PARAM1 VARCHAR(1000),
	PARAM2 VARCHAR(1000),
	UPD VARCHAR(32),
	DATE_FROM DATETIME,
	DATE_TO DATETIME
)
GO
ALTER TABLE b_search_content ADD CONSTRAINT PK_B_SEARCH_CONTENT PRIMARY KEY (ID)
GO
CREATE UNIQUE INDEX UX_B_SEARCH_CONTENT ON b_search_content (MODULE_ID, ITEM_ID)
GO
CREATE INDEX IX_B_SEARCH_CONTENT_2 ON b_search_content (ENTITY_ID, ENTITY_TYPE_ID)
GO
ALTER TABLE b_search_content ADD CONSTRAINT DF_B_SEARCH_CONTENT_RANK DEFAULT 0 FOR CUSTOM_RANK
GO

CREATE TABLE b_search_content_text
(
	SEARCH_CONTENT_ID INT NOT NULL,
	SEARCH_CONTENT_MD5 CHAR(32) NOT NULL,
	SEARCHABLE_CONTENT TEXT	NULL
)
GO
ALTER TABLE b_search_content_text ADD CONSTRAINT PK_B_SEARCH_CONTENT_TEXT PRIMARY KEY (SEARCH_CONTENT_ID)
GO

CREATE TABLE b_search_content_param
(
	SEARCH_CONTENT_ID INT NOT NULL
	,PARAM_NAME VARCHAR(100) NOT NULL
	,PARAM_VALUE VARCHAR(100) NOT NULL
)
GO
CREATE INDEX IX_B_SEARCH_CONTENT_PARAM ON b_search_content_param (SEARCH_CONTENT_ID, PARAM_NAME)
GO
CREATE INDEX IX_B_SEARCH_CONTENT_PARAM_1 ON b_search_content_param(PARAM_NAME, PARAM_VALUE, SEARCH_CONTENT_ID);
GO

CREATE TABLE b_search_content_right
(
	SEARCH_CONTENT_ID INT NOT NULL
	,GROUP_CODE VARCHAR(100) NOT NULL
)
GO
CREATE UNIQUE INDEX UX_B_SEARCH_CONTENT_RIGHT ON b_search_content_right (SEARCH_CONTENT_ID, GROUP_CODE)
GO

CREATE TABLE b_search_user_right
(
	USER_ID INT NOT NULL
	,GROUP_CODE VARCHAR(100) NOT NULL
)
GO
CREATE UNIQUE INDEX UX_B_SEARCH_USER_RIGHT ON b_search_user_right (USER_ID, GROUP_CODE)
GO

CREATE TABLE b_search_content_site
(
	SEARCH_CONTENT_ID	INT	NOT NULL
	,SITE_ID			CHAR(2)	NOT NULL
	,URL			TEXT	NULL
)
GO
ALTER TABLE b_search_content_site ADD CONSTRAINT PK_B_SRCH_CONT_SITE PRIMARY KEY (SEARCH_CONTENT_ID, SITE_ID)
GO

CREATE TABLE b_search_content_stem
(
	SEARCH_CONTENT_ID	INT		NOT NULL
	,LANGUAGE_ID		CHAR(2)		NOT NULL
	,STEM			VARCHAR(50)	NOT NULL
	,TF			FLOAT		NOT NULL
	,PS			FLOAT		NOT NULL
)
GO
CREATE UNIQUE INDEX UX_B_SEARCH_CONTENT_STEM ON b_search_content_stem (STEM, LANGUAGE_ID, TF, PS, SEARCH_CONTENT_ID)
GO
CREATE INDEX IND_B_SEARCH_CONTENT_STEM ON b_search_content_stem (SEARCH_CONTENT_ID)
GO

CREATE TABLE b_search_stem
(
	ID			INT		NOT NULL IDENTITY(1,1),
	STEM			VARCHAR(50)	NOT NULL
)
GO
ALTER TABLE b_search_stem ADD CONSTRAINT PK_B_SEARCH_STEM PRIMARY KEY (ID)
GO
CREATE UNIQUE INDEX UX_B_SEARCH_STEM ON b_search_stem (STEM)
GO

CREATE TABLE b_search_content_title
(
	SEARCH_CONTENT_ID	INT		NOT NULL,
	SITE_ID			CHAR(2)		NOT NULL,
	WORD			VARCHAR(100)	NOT NULL,
	POS			INT		NOT NULL
)
GO
CREATE UNIQUE INDEX UX_B_SEARCH_CONTENT_TITLE ON b_search_content_title (SITE_ID, WORD, SEARCH_CONTENT_ID, POS)
GO
CREATE INDEX IND_B_SEARCH_CONTENT_TITLE ON b_search_content_title (SEARCH_CONTENT_ID)
GO

CREATE TABLE b_search_content_freq
(
	STEM		INT	NOT NULL
	,LANGUAGE_ID	CHAR(2)		NOT NULL
	,SITE_ID	CHAR(2)
	,FREQ		FLOAT		NOT NULL
	,TF		FLOAT
)
GO
CREATE UNIQUE INDEX UX_B_SEARCH_CONTENT_FREQ ON b_search_content_freq (STEM, LANGUAGE_ID, SITE_ID)
GO

CREATE TABLE b_search_custom_rank
(
	ID			INT		NOT NULL IDENTITY(1,1),
	SITE_ID			CHAR(2)		NOT NULL,
	MODULE_ID		VARCHAR(200)	NOT NULL,
	PARAM1			VARCHAR(2000)	NULL,
	PARAM2			VARCHAR(2000)	NULL,
	ITEM_ID			VARCHAR(255)	NULL,
	RANK			INT		NOT NULL,
	APPLIED			CHAR(1)		NOT NULL,
	CONSTRAINT PK_B_SEARCH_CUSTOM_RANK PRIMARY KEY (ID)
)
GO
ALTER TABLE b_search_custom_rank ADD CONSTRAINT DF_B_SEARCH_CUSTOM_RANK_RANK DEFAULT 0 FOR RANK
GO
ALTER TABLE b_search_custom_rank ADD CONSTRAINT DF_B_SEARCH_CUSTOM_RANK_APPLIED DEFAULT 'N' FOR APPLIED
GO
CREATE INDEX IND_B_SEARCH_CUSTOM_RANK ON b_search_custom_rank (SITE_ID,MODULE_ID)
GO

CREATE TABLE b_search_tags
(
	SEARCH_CONTENT_ID INT NOT NULL,
	SITE_ID CHAR(2) NOT NULL,
	NAME VARCHAR(255) NOT NULL,
	CONSTRAINT PK_B_SEARCH_TAGS PRIMARY KEY (SEARCH_CONTENT_ID, SITE_ID, NAME)
)
GO
CREATE INDEX IX_B_SEARCH_TAGS_0 ON b_search_tags (NAME)
GO

CREATE TABLE b_search_suggest
(
	ID			INT		NOT NULL IDENTITY(1,1),
	SITE_ID			CHAR(2)		NOT NULL,
	FILTER_MD5		VARCHAR(32)	NOT NULL,
	PHRASE			VARCHAR(250)	NOT NULL,
	RATE			FLOAT		NOT NULL,
	TIMESTAMP_X		DATETIME	NOT NULL,
	RESULT_COUNT		INT		NOT NULL
)
GO
ALTER TABLE b_search_suggest ADD CONSTRAINT PK_B_SEARCH_SUGGEST PRIMARY KEY (ID)
GO
CREATE INDEX IND_B_SEARCH_SUGGEST ON b_search_suggest (FILTER_MD5, PHRASE, RATE)
GO
CREATE INDEX IND_B_SEARCH_SUGGEST_PHRASE ON b_search_suggest (PHRASE, RATE)
GO
CREATE INDEX IND_B_SEARCH_SUGGEST_TIME ON b_search_suggest (TIMESTAMP_X)
GO