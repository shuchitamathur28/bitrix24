CREATE TABLE B_DISK_STORAGE
(
	ID int NOT NULL IDENTITY (1, 1),
	NAME varchar(100),
	CODE varchar(32),
	XML_ID varchar(50),

	MODULE_ID varchar(32) not null,
	ENTITY_TYPE varchar(100) not null,
	ENTITY_ID varchar(32) not null,

	ENTITY_MISC_DATA text,
	ROOT_OBJECT_ID int,
	USE_INTERNAL_RIGHTS tinyint,

	SITE_ID CHAR(2)
)
GO
ALTER TABLE B_DISK_STORAGE ADD CONSTRAINT PK_B_DISK_STORAGE PRIMARY KEY (ID)
GO
CREATE UNIQUE INDEX IX_DISK_PH_1 ON B_DISK_STORAGE (MODULE_ID, ENTITY_TYPE, ENTITY_ID)
GO
CREATE INDEX IX_DISK_PH_2 ON B_DISK_STORAGE (XML_ID)
GO

CREATE TABLE B_DISK_OBJECT
(
	ID int NOT NULL IDENTITY (1, 1),
	NAME varchar(255) not null,
	TYPE int not null,
	CODE varchar(50),
	XML_ID varchar(50),
	STORAGE_ID int not null,
	REAL_OBJECT_ID int,
	PARENT_ID int,
	CONTENT_PROVIDER varchar(10),

	CREATE_TIME datetime not null,
	UPDATE_TIME datetime,
	SYNC_UPDATE_TIME datetime,
	DELETE_TIME datetime,

	CREATED_BY int,
	UPDATED_BY int,
	DELETED_BY int,

	GLOBAL_CONTENT_VERSION int,
	FILE_ID int,
	TYPE_FILE int,
	SIZE bigint,
	EXTERNAL_HASH varchar(255),
	DELETED_TYPE int
)
GO
ALTER TABLE B_DISK_OBJECT ADD CONSTRAINT PK_B_DISK_OBJECT PRIMARY KEY (ID)
GO
ALTER TABLE B_DISK_OBJECT ADD CONSTRAINT DF_B_DISK_OBJECT_NAME DEFAULT '' FOR NAME
GO
ALTER TABLE B_DISK_OBJECT ADD CONSTRAINT DF_B_DISK_OBJECT_DELETED_BY DEFAULT 0 FOR DELETED_BY
GO
ALTER TABLE B_DISK_OBJECT ADD CONSTRAINT DF_B_DISK_OBJECT_DELETED_TYPE DEFAULT 0 FOR DELETED_TYPE
GO

CREATE INDEX IX_DISK_O_1 ON B_DISK_OBJECT (REAL_OBJECT_ID)
GO
CREATE INDEX IX_DISK_O_2 ON B_DISK_OBJECT (PARENT_ID, DELETED_TYPE, TYPE)
GO
CREATE INDEX IX_DISK_O_3 ON B_DISK_OBJECT (STORAGE_ID, CODE)
GO
CREATE INDEX IX_DISK_O_4 ON B_DISK_OBJECT (STORAGE_ID, DELETED_TYPE)
GO
CREATE INDEX IX_DISK_O_5 ON B_DISK_OBJECT (NAME, PARENT_ID)
GO
CREATE INDEX IX_DISK_O_6 ON B_DISK_OBJECT (STORAGE_ID, XML_ID)
GO
CREATE INDEX IX_DISK_O_7 ON B_DISK_OBJECT (UPDATE_TIME)
GO
CREATE INDEX IX_DISK_O_8 ON B_DISK_OBJECT (SYNC_UPDATE_TIME)
GO

CREATE TABLE B_DISK_OBJECT_LOCK
(
	ID int NOT NULL IDENTITY (1, 1),
	TOKEN varchar(255) not null,
	OBJECT_ID int not null,
	CREATED_BY int not null,
	CREATE_TIME datetime not null,
	EXPIRY_TIME datetime,
	TYPE int not null,
	IS_EXCLUSIVE tinyint,
)
GO
ALTER TABLE B_DISK_OBJECT_LOCK ADD CONSTRAINT PK_B_DISK_OBJECT_LOCK PRIMARY KEY (ID)
GO

CREATE INDEX IX_DISK_OL_1 ON B_DISK_OBJECT_LOCK (OBJECT_ID, IS_EXCLUSIVE)
GO
CREATE UNIQUE INDEX IX_DISK_OL_2 ON B_DISK_OBJECT_LOCK (TOKEN)
GO

CREATE TABLE B_DISK_OBJECT_TTL
(
	ID int NOT NULL IDENTITY (1, 1),
	OBJECT_ID int not null,
	CREATE_TIME datetime not null,
	DEATH_TIME datetime not null
)
GO
ALTER TABLE B_DISK_OBJECT_TTL ADD CONSTRAINT PK_B_DISK_OBJECT_TTL PRIMARY KEY (ID)
GO

CREATE INDEX IX_DISK_OTTL_1 ON B_DISK_OBJECT_TTL (DEATH_TIME, OBJECT_ID)
GO

CREATE TABLE B_DISK_OBJECT_PATH
(
	ID int NOT NULL IDENTITY (1, 1),
	PARENT_ID int not null,
	OBJECT_ID int not null,
	DEPTH_LEVEL int
)
GO
ALTER TABLE B_DISK_OBJECT_PATH ADD CONSTRAINT PK_B_DISK_OBJECT_PATH PRIMARY KEY (ID)
GO
CREATE UNIQUE INDEX IX_DISK_OP_1 ON B_DISK_OBJECT_PATH (PARENT_ID, DEPTH_LEVEL, OBJECT_ID)
GO
CREATE UNIQUE INDEX IX_DISK_OP_2 ON B_DISK_OBJECT_PATH (OBJECT_ID, PARENT_ID, DEPTH_LEVEL)
GO

CREATE TABLE B_DISK_VERSION
(
	ID int NOT NULL IDENTITY (1, 1),
	OBJECT_ID int not null,
	FILE_ID int not null,
	SIZE bigint,
	NAME varchar(255),

	CREATE_TIME datetime not null,
	CREATED_BY int,

	OBJECT_CREATE_TIME datetime,
	OBJECT_CREATED_BY int,
	OBJECT_UPDATE_TIME datetime,
	OBJECT_UPDATED_BY int,
	GLOBAL_CONTENT_VERSION int,

	MISC_DATA text
)
GO
ALTER TABLE B_DISK_VERSION ADD CONSTRAINT PK_B_DISK_VERSION PRIMARY KEY (ID)
GO
CREATE INDEX IX_DISK_V_1 ON B_DISK_VERSION (OBJECT_ID)
GO

CREATE TABLE B_DISK_RIGHT
(
	ID int NOT NULL IDENTITY (1, 1),
	OBJECT_ID int not null,
	TASK_ID int not null,
	ACCESS_CODE varchar(50) not null,
	DOMAIN varchar(50),
	NEGATIVE tinyint not null,
)
GO
ALTER TABLE B_DISK_RIGHT ADD CONSTRAINT PK_B_DISK_RIGHT PRIMARY KEY (ID)
GO
ALTER TABLE B_DISK_RIGHT ADD CONSTRAINT DF_B_DISK_RIGHT_NEGATIVE DEFAULT 0 FOR NEGATIVE
GO
CREATE INDEX IX_DISK_R_1 ON B_DISK_RIGHT (OBJECT_ID, NEGATIVE)
GO
CREATE INDEX IX_DISK_R_2 ON B_DISK_RIGHT (ACCESS_CODE, TASK_ID)
GO

CREATE TABLE B_DISK_SIMPLE_RIGHT
(
	ID int NOT NULL IDENTITY (1, 1),
	OBJECT_ID int not null,
	ACCESS_CODE varchar(50) not null
)
GO
ALTER TABLE B_DISK_SIMPLE_RIGHT ADD CONSTRAINT PK_B_DISK_SIMPLE_RIGHT PRIMARY KEY (ID)
GO
CREATE INDEX IX_DISK_SR_1 ON B_DISK_SIMPLE_RIGHT (OBJECT_ID)
GO
CREATE INDEX IX_DISK_SR_2 ON B_DISK_SIMPLE_RIGHT (ACCESS_CODE)
GO

CREATE TABLE B_DISK_ATTACHED_OBJECT
(
	ID int NOT NULL IDENTITY (1, 1),
	OBJECT_ID int not null,
	VERSION_ID int,

	IS_EDITABLE tinyint not null,
	ALLOW_EDIT tinyint not null,
	ALLOW_AUTO_COMMENT tinyint,

	MODULE_ID varchar(32) not null,
	ENTITY_TYPE varchar(100) not null,
	ENTITY_ID int not null,

	CREATE_TIME datetime not null,
	CREATED_BY int
)
GO
ALTER TABLE B_DISK_ATTACHED_OBJECT ADD CONSTRAINT PK_B_DISK_ATTACHED_OBJECT PRIMARY KEY (ID)
GO
ALTER TABLE B_DISK_ATTACHED_OBJECT ADD CONSTRAINT DF_B_DISK_ATT_OBJECT_IS_EDITABLE DEFAULT 0 FOR IS_EDITABLE
GO
ALTER TABLE B_DISK_ATTACHED_OBJECT ADD CONSTRAINT DF_B_DISK_ATT_OBJECT_ALLOW_EDIT DEFAULT 0 FOR ALLOW_EDIT
GO

CREATE INDEX IX_DISK_AO_1 ON B_DISK_ATTACHED_OBJECT (OBJECT_ID, VERSION_ID)
GO
CREATE INDEX IX_DISK_AO_2 ON B_DISK_ATTACHED_OBJECT (MODULE_ID, ENTITY_TYPE, ENTITY_ID)
GO

CREATE TABLE B_DISK_EXTERNAL_LINK
(
	ID int NOT NULL IDENTITY (1, 1),
	OBJECT_ID int not null,
	VERSION_ID int,
	HASH varchar(32) not null,
	PASSWORD varchar(32),
	SALT varchar(32),
	DEATH_TIME datetime,
	DESCRIPTION text,
	DOWNLOAD_COUNT int,
	TYPE int,

	CREATE_TIME datetime not null,
	CREATED_BY int
)
GO
ALTER TABLE B_DISK_EXTERNAL_LINK ADD CONSTRAINT PK_B_DISK_EXTERNAL_LINK PRIMARY KEY (ID)
GO
CREATE INDEX IX_DISK_EL_1 ON B_DISK_EXTERNAL_LINK (OBJECT_ID)
GO
CREATE INDEX IX_DISK_EL_2 ON B_DISK_EXTERNAL_LINK (HASH)
GO
CREATE INDEX IX_DISK_EL_3 ON B_DISK_EXTERNAL_LINK (CREATED_BY)
GO

CREATE TABLE B_DISK_SHARING
(
	ID int NOT NULL IDENTITY (1, 1),
  PARENT_ID int,
  CREATED_BY int,

  FROM_ENTITY VARCHAR(50) not null,
	TO_ENTITY VARCHAR(50) not null,

	LINK_STORAGE_ID int,
	LINK_OBJECT_ID int,

	REAL_OBJECT_ID int not null,
	REAL_STORAGE_ID int not null,

	DESCRIPTION text,
	CAN_FORWARD tinyint,
	STATUS int not null,
	TYPE int not null,

	TASK_NAME VARCHAR(50),
	IS_EDITABLE tinyint not null
)
GO
ALTER TABLE B_DISK_SHARING ADD CONSTRAINT PK_B_DISK_SHARING PRIMARY KEY (ID)
GO
ALTER TABLE B_DISK_SHARING ADD CONSTRAINT DF_B_DISK_SHARING_IS_EDITABLE DEFAULT 0 FOR IS_EDITABLE
GO
CREATE INDEX IX_DISK_S_1 ON B_DISK_SHARING (REAL_STORAGE_ID, REAL_OBJECT_ID)
GO
CREATE INDEX IX_DISK_S_2 ON B_DISK_SHARING (FROM_ENTITY)
GO
CREATE INDEX IX_DISK_S_3 ON B_DISK_SHARING (TO_ENTITY)
GO
CREATE INDEX IX_DISK_S_4 ON B_DISK_SHARING (LINK_STORAGE_ID, LINK_OBJECT_ID)
GO
CREATE INDEX IX_DISK_S_5 ON B_DISK_SHARING (TYPE, PARENT_ID)
GO

CREATE TABLE B_DISK_EDIT_SESSION
(
	ID int NOT NULL IDENTITY (1, 1),
	OBJECT_ID int,
	VERSION_ID int,
	USER_ID int not null,
	OWNER_ID int not null,
	IS_EXCLUSIVE tinyint,
	SERVICE VARCHAR(10) not null,
	SERVICE_FILE_ID VARCHAR(255) not null,
	SERVICE_FILE_LINK text not null,
	CREATE_TIME datetime not null
)
GO
ALTER TABLE B_DISK_EDIT_SESSION ADD CONSTRAINT PK_B_DISK_EDIT_SESSION PRIMARY KEY (ID)
GO
CREATE INDEX IX_DISK_ES_1 ON B_DISK_EDIT_SESSION (OBJECT_ID, VERSION_ID)
GO
CREATE INDEX IX_DISK_ES_2 ON B_DISK_EDIT_SESSION (USER_ID)
GO

CREATE TABLE B_DISK_SHOW_SESSION
(
	ID int NOT NULL IDENTITY (1, 1),
	OBJECT_ID int,
	VERSION_ID int,
	USER_ID int not null,
	OWNER_ID int not null,
	SERVICE VARCHAR(10) not null,
	SERVICE_FILE_ID VARCHAR(255) not null,
	SERVICE_FILE_LINK text not null,
	ETAG VARCHAR(255),
	CREATE_TIME datetime not null
)
GO
ALTER TABLE B_DISK_SHOW_SESSION ADD CONSTRAINT PK_B_DISK_SHOW_SESSION PRIMARY KEY (ID)
GO
CREATE INDEX IX_DISK_SS_1 ON B_DISK_SHOW_SESSION (OBJECT_ID, VERSION_ID, USER_ID)
GO
CREATE INDEX IX_DISK_SS_2 ON B_DISK_SHOW_SESSION (CREATE_TIME)
GO

CREATE TABLE B_DISK_TMP_FILE
(
	ID int NOT NULL IDENTITY (1, 1),
	TOKEN VARCHAR(32) not null,
	FILENAME VARCHAR(255),
	CONTENT_TYPE VARCHAR(255),
	PATH VARCHAR(255),
	BUCKET_ID int,
	SIZE bigint,
	WIDTH int,
	HEIGHT int,
	IS_CLOUD tinyint,
	CREATED_BY int,
	CREATE_TIME datetime not null
)
GO
ALTER TABLE B_DISK_TMP_FILE ADD CONSTRAINT PK_B_DISK_TMP_FILE PRIMARY KEY (ID)
GO
CREATE INDEX IX_DISK_TF_1 ON B_DISK_TMP_FILE (TOKEN)
GO

CREATE TABLE B_DISK_DELETED_LOG
(
	ID int NOT NULL IDENTITY (1, 1),
	USER_ID int not null,
	STORAGE_ID int not null,
	OBJECT_ID int not null,
	TYPE int not null,
	CREATE_TIME datetime not null
)
GO
ALTER TABLE B_DISK_DELETED_LOG ADD CONSTRAINT PK_B_DISK_DELETED_LOG PRIMARY KEY (ID)
GO
CREATE INDEX IX_DISK_DL_1 ON B_DISK_DELETED_LOG (STORAGE_ID, CREATE_TIME)
GO
CREATE INDEX IX_DISK_DL_2 ON B_DISK_DELETED_LOG (OBJECT_ID)
GO

CREATE TABLE B_DISK_CLOUD_IMPORT
(
	ID int NOT NULL IDENTITY (1, 1),
	OBJECT_ID int,
	VERSION_ID int,
	TMP_FILE_ID int,
	DOWNLOADED_CONTENT_SIZE bigint,
	CONTENT_SIZE bigint,
	CONTENT_URL text,
	MIME_TYPE VARCHAR(255),
	USER_ID int not null,
	SERVICE VARCHAR(10) not null,
	SERVICE_OBJECT_ID text not null,
	ETAG VARCHAR(255),
	CREATE_TIME datetime not null
)
GO
ALTER TABLE B_DISK_CLOUD_IMPORT ADD CONSTRAINT PK_B_DISK_CLOUD_IMPORT PRIMARY KEY (ID)
GO
CREATE INDEX IX_DISK_CI_1 ON B_DISK_CLOUD_IMPORT (OBJECT_ID, VERSION_ID)
GO
CREATE INDEX IX_DISK_CI_2 ON B_DISK_CLOUD_IMPORT (TMP_FILE_ID)
GO

CREATE TABLE B_DISK_RECENTLY_USED
(
	ID int NOT NULL IDENTITY (1, 1),
	USER_ID int not null,
	OBJECT_ID int not null,
	CREATE_TIME datetime not null
)
GO
ALTER TABLE B_DISK_RECENTLY_USED ADD CONSTRAINT PK_B_DISK_RECENTLY_USED PRIMARY KEY (ID)
GO
CREATE INDEX IX_DISK_RU_1 ON B_DISK_RECENTLY_USED (USER_ID, OBJECT_ID, CREATE_TIME)
GO
