create table b_ldap_server
(
	ID				NUMBER(18) 			not null,
	TIMESTAMP_X		date	default SYSDATE not null,
	NAME			varchar2(255 CHAR)	not null,
	DESCRIPTION		varchar2(2000 CHAR),
	CODE			varchar2(255 CHAR),
	ACTIVE			char(1 CHAR)	default 'Y' not null,
	SERVER			varchar2(255 CHAR)	not null,
	PORT			NUMBER(18)		default 389	not null,
	ADMIN_LOGIN		varchar2(255 CHAR)	not null,
	ADMIN_PASSWORD	varchar2(255 CHAR)	not null,
	BASE_DN			varchar2(255 CHAR)	not null,
	GROUP_FILTER	varchar2(255 CHAR)	not null,
	GROUP_ID_ATTR	varchar2(255 CHAR)	not null,
	GROUP_NAME_ATTR	varchar2(255 CHAR),
	GROUP_MEMBERS_ATTR	varchar2(255 CHAR),
	USER_FILTER 	varchar2(255 CHAR)	not null,
	USER_ID_ATTR	varchar2(255 CHAR)	not null,
	USER_NAME_ATTR	varchar2(255 CHAR),
	USER_LAST_NAME_ATTR	varchar2(255 CHAR),
	USER_EMAIL_ATTR	varchar2(255 CHAR),
	USER_GROUP_ATTR	varchar2(255 CHAR),
	USER_GROUP_ACCESSORY	char(1 CHAR)	default 'N',
	USER_DEPARTMENT_ATTR varchar2(255 CHAR),
	USER_MANAGER_ATTR varchar2(255 CHAR),
	CONVERT_UTF8	char(1 CHAR)	default 'Y',
	SYNC_PERIOD 	NUMBER(18),
	FIELD_MAP 		varchar2(2000 CHAR),
	ROOT_DEPARTMENT	NUMBER(18),
	DEFAULT_DEPARTMENT_NAME varchar2(255 CHAR),
	IMPORT_STRUCT	char(1 CHAR)	default 'N',
	STRUCT_HAVE_DEFAULT	char(1 CHAR),
	SYNC 			char(1 CHAR),
	SYNC_ATTR 		varchar2(255 CHAR),
	SYNC_LAST 		date,
	MAX_PAGE_SIZE	NUMBER(18),
	SYNC_USER_ADD 			char(1 CHAR),
	primary key(ID)
)
/

CREATE SEQUENCE sq_b_ldap_server INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_ldap_server_insert
BEFORE INSERT
ON b_ldap_server
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_ldap_server.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_ldap_server_update
BEFORE UPDATE
ON b_ldap_server
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/

create table b_ldap_group
(
	LDAP_SERVER_ID	NUMBER(18)				not null,
	GROUP_ID		NUMBER(18)				not null,
	LDAP_GROUP_ID	varchar2(255 CHAR)	not null,
	primary key (LDAP_SERVER_ID, GROUP_ID, LDAP_GROUP_ID)
)
/
