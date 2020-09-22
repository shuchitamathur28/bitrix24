CREATE TABLE b_hlblock_entity (
  ID int NOT NULL IDENTITY (1, 1),
  NAME varchar(100) NOT NULL,
  TABLE_NAME varchar(64) NOT NULL,
  CONSTRAINT hlblock_entity_ibpk_1 PRIMARY KEY (ID)
)
GO
CREATE TABLE b_hlblock_entity_lang (
  ID int NOT NULL,
  LID varchar(2) NOT NULL,
  NAME varchar(100) NOT NULL,
  CONSTRAINT hlblock_entity_ibpk_2 PRIMARY KEY (ID)
)
GO
CREATE TABLE b_hlblock_entity_rights (
  ID int NOT NULL IDENTITY (1, 1),
  HL_ID int NOT NULL,
  TASK_ID int NOT NULL,
  ACCESS_CODE varchar(50) not null,
  CONSTRAINT hlblock_entity_ibpk_3 PRIMARY KEY (ID)
)
GO