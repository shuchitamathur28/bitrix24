CREATE TABLE b_hlblock_entity (
  ID number(11) NOT NULL,
  NAME varchar(100 char) NOT NULL,
  TABLE_NAME varchar(64 char) NOT NULL,
  PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_hlblock_entity INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_hlblock_entity_insert
BEFORE INSERT
ON b_hlblock_entity
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_hlblock_entity.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE TABLE b_hlblock_entity_lang (
  ID number(11) NOT NULL,
  LID varchar(2 char) NOT NULL,
  NAME varchar(100 char) NOT NULL
)
/
CREATE TABLE b_hlblock_entity_rights (
  ID number(11) NOT NULL,
  HL_ID number(11) NOT NULL,
  TASK_ID number(11) NOT NULL,
  ACCESS_CODE varchar(50 char) NOT NULL,
  PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_hlblock_entity_rights INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_hlblock_ent_rights_insert
BEFORE INSERT
ON b_hlblock_entity_rights
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_hlblock_entity_rights.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/