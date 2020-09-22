CREATE TABLE b_report (
  ID number(11) NOT NULL,
  OWNER_ID varchar(20 char) NULL,
  TITLE varchar(255 char) NOT NULL,
  DESCRIPTION clob NULL,
  CREATED_DATE date NOT NULL,
  CREATED_BY number(11) NOT NULL,
  SETTINGS clob NULL,
  MARK_DEFAULT smallint NULL,
  PRIMARY KEY (ID)
)
/
CREATE INDEX ix_report_owner ON b_report(OWNER_ID)
/
CREATE SEQUENCE sq_b_report INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER b_report_insert
BEFORE INSERT
ON b_report
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_report.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE TABLE B_REPORT_SHARING
(
  ID int NOT NULL,
  REPORT_ID int default 0 NOT NULL,
  ENTITY varchar2(50 CHAR) NOT NULL,
  RIGHTS varchar2(50 CHAR) NOT NULL,
  primary key (ID)
)
/
CREATE INDEX IX_ENTITY ON B_REPORT_SHARING (ENTITY)
/
CREATE INDEX IX_REPORT_ID ON B_REPORT_SHARING (REPORT_ID)
/