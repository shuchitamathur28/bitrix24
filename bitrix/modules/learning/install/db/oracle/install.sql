CREATE TABLE b_learn_exceptions_log (
  DATE_REGISTERED DATE DEFAULT SYSDATE NOT NULL,
  CODE NUMBER(11) NOT NULL,
  MESSAGE CLOB NOT NULL,
  FFILE CLOB NOT NULL,
  LINE NUMBER(11) NOT NULL,
  BACKTRACE CLOB NOT NULL
)
/

CREATE TABLE b_learn_publish_prohibition
(
	COURSE_LESSON_ID NUMBER(11) NOT NULL ,
	PROHIBITED_LESSON_ID NUMBER(11) NOT NULL ,
	PRIMARY KEY ( COURSE_LESSON_ID , PROHIBITED_LESSON_ID )
)
/

CREATE TABLE b_learn_rights
(
	LESSON_ID NUMBER(11) NOT NULL ,
	SUBJECT_ID VARCHAR2(100 CHAR) NOT NULL,
	TASK_ID NUMBER(11) NOT NULL,
	PRIMARY KEY(LESSON_ID, SUBJECT_ID)
)
/

CREATE TABLE b_learn_rights_all
(
	SUBJECT_ID VARCHAR2(100 CHAR) NOT NULL,
	TASK_ID NUMBER(11) NOT NULL,
	PRIMARY KEY(SUBJECT_ID)
)
/

CREATE TABLE b_learn_lesson_edges
(
	SOURCE_NODE NUMBER(11) NOT NULL,
	TARGET_NODE NUMBER(11) NOT NULL,
	SORT NUMBER(11) DEFAULT 500 NOT NULL,
	PRIMARY KEY(SOURCE_NODE, TARGET_NODE)
)
/
CREATE INDEX ix_learn_lesson_edges_t_node ON b_learn_lesson_edges(TARGET_NODE)
/

CREATE TABLE b_learn_course
(
	ID NUMBER(11) NOT NULL,
	TIMESTAMP_X DATE DEFAULT SYSDATE NOT NULL,
	ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	CODE VARCHAR2(50 CHAR),
	NAME VARCHAR2(255 CHAR) DEFAULT 'NAME' NOT NULL,
	SORT NUMBER(11) DEFAULT 500 NOT NULL,
	PREVIEW_PICTURE NUMBER(18),
	PREVIEW_TEXT CLOB,
	PREVIEW_TEXT_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	DESCRIPTION CLOB,
	DESCRIPTION_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	ACTIVE_FROM DATE,
	ACTIVE_TO DATE,
	RATING CHAR(1 CHAR) NULL,
	RATING_TYPE VARCHAR2(50 CHAR) NULL,	
	SCORM CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	LINKED_LESSON_ID NUMBER(11) NULL,
	JOURNAL_STATUS NUMBER(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY(ID)
)
/
CREATE INDEX ix_learn_course_lesson ON b_learn_course(LINKED_LESSON_ID)
/
CREATE SEQUENCE sq_b_learn_course INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_learn_course_insert
BEFORE INSERT
ON b_learn_course
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_learn_course.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_learn_course_update
BEFORE UPDATE 
ON b_learn_course
FOR EACH ROW 
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/

CREATE TABLE b_learn_course_site
(
	COURSE_ID NUMBER(11) NOT NULL,
	SITE_ID CHAR(2 CHAR) NOT NULL,
	PRIMARY KEY(COURSE_ID, SITE_ID),
	CONSTRAINT fk_b_learn_course1 FOREIGN KEY (COURSE_ID) REFERENCES b_learn_course(ID),
	CONSTRAINT fk_b_learn_course2 FOREIGN KEY (SITE_ID) REFERENCES b_lang(LID)
)
/

CREATE TABLE b_learn_chapter
(
	ID NUMBER(11) NOT NULL,
	TIMESTAMP_X DATE DEFAULT SYSDATE NOT NULL,
	ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	COURSE_ID NUMBER(11) NOT NULL,
	CHAPTER_ID NUMBER(11),
	NAME VARCHAR2(255 CHAR) NOT NULL,
	CODE VARCHAR2(50 CHAR),
	SORT NUMBER(11) DEFAULT 500 NOT NULL,
	PREVIEW_PICTURE NUMBER(18),
	PREVIEW_TEXT CLOB,
	PREVIEW_TEXT_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	DETAIL_PICTURE NUMBER(18),
	DETAIL_TEXT CLOB,
	DETAIL_TEXT_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	JOURNAL_STATUS NUMBER(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY(ID)
)
/

CREATE SEQUENCE sq_b_learn_chapter INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_learn_chapter_insert
BEFORE INSERT 
ON b_learn_chapter
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_learn_chapter.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_learn_chapter_update
BEFORE UPDATE 
ON b_learn_chapter
FOR EACH ROW 
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/

CREATE TABLE b_learn_lesson
(
	ID NUMBER(11) NOT NULL,
	TIMESTAMP_X DATE DEFAULT SYSDATE NOT NULL,
	DATE_CREATE DATE,
	CREATED_BY NUMBER(18),
	ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	COURSE_ID NUMBER(11) DEFAULT '0' NOT NULL,
	CHAPTER_ID NUMBER(11),
	NAME VARCHAR2(255 CHAR) DEFAULT 'name' NOT NULL,
	SORT NUMBER(11) DEFAULT 500 NOT NULL,
	PREVIEW_PICTURE NUMBER(18),
	KEYWORDS CLOB,
	PREVIEW_TEXT CLOB,
	PREVIEW_TEXT_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	DETAIL_PICTURE NUMBER(18),
	DETAIL_TEXT CLOB,
	DETAIL_TEXT_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	LAUNCH VARCHAR2(2000 CHAR),

	CODE VARCHAR( 50 CHAR ) NULL,
	WAS_CHAPTER_ID NUMBER(11) NULL,
	WAS_PARENT_CHAPTER_ID NUMBER(11) NULL,
	WAS_PARENT_COURSE_ID NUMBER(11) NULL,
	WAS_COURSE_ID NUMBER(11) NULL,
	JOURNAL_STATUS NUMBER(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY(ID)
)
/

CREATE SEQUENCE sq_b_learn_lesson INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_learn_lesson_insert
BEFORE INSERT 
ON b_learn_lesson
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_learn_lesson.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_learn_lesson_update
BEFORE UPDATE 
ON b_learn_lesson
FOR EACH ROW 
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/


CREATE TABLE b_learn_question
(
	ID NUMBER(11) NOT NULL,
	ACTIVE char(1 CHAR) default 'Y' not null,
	TIMESTAMP_X DATE DEFAULT SYSDATE NOT NULL,
	LESSON_ID NUMBER(11) NOT NULL,
	QUESTION_TYPE CHAR(1 CHAR) DEFAULT 'S' NOT NULL,
	NAME VARCHAR2(255 CHAR) NOT NULL,
	SORT NUMBER(11) DEFAULT 500 NOT NULL,
	DESCRIPTION CLOB,
	DESCRIPTION_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	COMMENT_TEXT CLOB,
	FILE_ID NUMBER(18),
	SELF CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	POINT NUMBER(11) DEFAULT 10 NOT NULL,
	DIRECTION CHAR(1 CHAR) DEFAULT 'V' NOT NULL,
	CORRECT_REQUIRED CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	EMAIL_ANSWER CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	INCORRECT_MESSAGE CLOB,
	PRIMARY KEY(ID),
	CONSTRAINT fk_b_learn_question1 FOREIGN KEY (LESSON_ID) REFERENCES b_learn_lesson(ID)
)
/

CREATE INDEX ix_b_learn_question1 ON b_learn_question(LESSON_ID)
/

CREATE SEQUENCE sq_b_learn_question INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_learn_question_insert
BEFORE INSERT 
ON b_learn_question
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_learn_question.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_learn_question_update
BEFORE UPDATE 
ON b_learn_question
FOR EACH ROW 
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/


CREATE TABLE b_learn_answer
(
	ID NUMBER(11) NOT NULL,
	QUESTION_ID NUMBER(11) NOT NULL,
	SORT NUMBER(11) DEFAULT 10 NOT NULL,
	ANSWER CLOB NOT NULL,
	CORRECT CHAR(1 CHAR) NOT NULL,
	FEEDBACK CLOB,
	MATCH_ANSWER CLOB,
	PRIMARY KEY(ID),
	CONSTRAINT fk_b_learn_answer1 FOREIGN KEY (QUESTION_ID) REFERENCES b_learn_question(ID)
)
/

CREATE INDEX ix_b_learn_answer1 ON b_learn_answer(QUESTION_ID)
/

CREATE SEQUENCE sq_b_learn_answer INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_learn_answer_insert
BEFORE INSERT 
ON b_learn_answer
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_learn_answer.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/



CREATE TABLE b_learn_test
(
	ID NUMBER(11) NOT NULL,
	COURSE_ID NUMBER(11) NOT NULL,
	TIMESTAMP_X DATE DEFAULT SYSDATE NOT NULL,
	SORT NUMBER(11) DEFAULT 500 NOT NULL,
	ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	NAME VARCHAR2(255 CHAR) NOT NULL,
	DESCRIPTION CLOB,
	DESCRIPTION_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	ATTEMPT_LIMIT NUMBER(11) DEFAULT 0 NOT NULL,
	TIME_LIMIT NUMBER(11) DEFAULT '0',
	COMPLETED_SCORE NUMBER(11),
	QUESTIONS_FROM CHAR(1 CHAR) DEFAULT 'A' NOT NULL,
	QUESTIONS_FROM_ID NUMBER(11) DEFAULT '0' NOT NULL,
	QUESTIONS_AMOUNT NUMBER(11) DEFAULT '0' NOT NULL,
	RANDOM_QUESTIONS CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	RANDOM_ANSWERS CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	APPROVED CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	INCLUDE_SELF_TEST CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	PASSAGE_TYPE CHAR(1 CHAR) DEFAULT '0' NOT NULL,
	PREVIOUS_TEST_ID NUMBER(11),
	PREVIOUS_TEST_SCORE NUMBER(11) DEFAULT '0',
	INCORRECT_CONTROL CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	CURRENT_INDICATION NUMBER(11) DEFAULT '0' NOT NULL,
	FINAL_INDICATION NUMBER(11) DEFAULT '0' NOT NULL,
	MIN_TIME_BETWEEN_ATTEMPTS NUMBER(11) DEFAULT '0' NOT NULL,
	SHOW_ERRORS CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	NEXT_QUESTION_ON_ERROR CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	PRIMARY KEY (ID),
	CONSTRAINT fk_b_learn_test1 FOREIGN KEY (COURSE_ID) REFERENCES b_learn_course(ID),
	CONSTRAINT fk_b_learn_test2 FOREIGN KEY (PREVIOUS_TEST_ID) REFERENCES b_learn_test(ID)
)
/

CREATE SEQUENCE sq_b_learn_test INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE INDEX ix_b_learn_test1 ON b_learn_test(COURSE_ID)
/

CREATE INDEX ix_b_learn_test2 ON b_learn_test(PREVIOUS_TEST_ID)
/

CREATE OR REPLACE TRIGGER b_learn_test_insert
BEFORE INSERT 
ON b_learn_test
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_learn_test.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_learn_test_update
BEFORE UPDATE 
ON b_learn_test
FOR EACH ROW 
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/

CREATE TABLE b_learn_attempt
(
	ID NUMBER(11) NOT NULL,
	TEST_ID NUMBER(11) NOT NULL,
	STUDENT_ID NUMBER(11) NOT NULL,
	DATE_START DATE NOT NULL,
	DATE_END DATE,
	STATUS CHAR(1 CHAR) DEFAULT 'B' NOT NULL,
	COMPLETED char(1 CHAR) DEFAULT 'N' NOT NULL,
	SCORE NUMBER(11) DEFAULT '0',
	MAX_SCORE NUMBER(11) DEFAULT '0',
	QUESTIONS NUMBER(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (ID),
	CONSTRAINT fk_b_learn_attempt1 FOREIGN KEY (TEST_ID) REFERENCES b_learn_test(ID)
)
/

CREATE SEQUENCE sq_b_learn_attempt INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE INDEX ix_b_learn_attempt1 ON b_learn_attempt(STUDENT_ID, TEST_ID)
/

CREATE OR REPLACE TRIGGER b_learn_attempt
BEFORE INSERT 
ON b_learn_attempt
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_learn_attempt.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE b_learn_test_result
(
	ID NUMBER(11) NOT NULL,
	ATTEMPT_ID NUMBER(11) NOT NULL,
	QUESTION_ID NUMBER(11) NOT NULL,
	RESPONSE CLOB,
	POINT NUMBER(11) DEFAULT '0' NOT NULL,
	CORRECT char(1 CHAR) DEFAULT 'N' NOT NULL,
	ANSWERED CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	PRIMARY KEY (ID),
	CONSTRAINT fk_b_learn_test_result1 FOREIGN KEY (ATTEMPT_ID) REFERENCES b_learn_attempt(ID),
	CONSTRAINT fk_b_learn_test_result2 FOREIGN KEY (QUESTION_ID) REFERENCES b_learn_question(ID)
)
/

CREATE INDEX ix_b_learn_test_result1 ON b_learn_test_result(ATTEMPT_ID,QUESTION_ID)
/
CREATE INDEX ix_b_learn_test_result2 ON b_learn_test_result(QUESTION_ID, ANSWERED, CORRECT)
/

CREATE SEQUENCE sq_b_learn_test_result INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_learn_test_result
BEFORE INSERT 
ON b_learn_test_result
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_learn_test_result.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE b_learn_gradebook
(
	ID NUMBER(11) NOT NULL,
	STUDENT_ID NUMBER(11) NOT NULL,
	TEST_ID NUMBER(11) NOT NULL,
	RESULT NUMBER(11),
	MAX_RESULT NUMBER(11),
	ATTEMPTS NUMBER(11) DEFAULT '1' NOT NULL,
	COMPLETED CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	EXTRA_ATTEMPTS NUMBER(11) DEFAULT '0' NOT NULL,
	CONSTRAINT fk_b_learn_gradebook1 FOREIGN KEY (TEST_ID) REFERENCES b_learn_test(ID)
)
/

CREATE UNIQUE INDEX ux_b_learn_gradebook1 ON b_learn_gradebook(STUDENT_ID,TEST_ID)
/

CREATE SEQUENCE sq_b_learn_gradebook INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_learn_gradebook
BEFORE INSERT 
ON b_learn_gradebook
FOR EACH ROW 
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_learn_gradebook.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE b_learn_student
(
	USER_ID NUMBER(18) NOT NULL,
	TRANSCRIPT NUMBER(11) NOT NULL,
	PUBLIC_PROFILE CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	RESUME CLOB,
	PRIMARY KEY(USER_ID)
)
/


CREATE TABLE b_learn_certification
(
	ID NUMBER(11) NOT NULL,
	STUDENT_ID NUMBER(18) NOT NULL,
	COURSE_ID NUMBER(11) NOT NULL,
	TIMESTAMP_X DATE DEFAULT SYSDATE NOT NULL,
	DATE_CREATE DATE NULL,
	ACTIVE CHAR(1 CHAR)  DEFAULT 'Y' NOT NULL,
	SORT NUMBER(11)  DEFAULT '500' NOT NULL,
	FROM_ONLINE CHAR(1 CHAR)  DEFAULT 'Y' NOT NULL,
	PUBLIC_PROFILE CHAR(1 CHAR)  DEFAULT 'Y' NOT NULL,
	SUMMARY NUMBER(11)  DEFAULT '0' NOT NULL,
	MAX_SUMMARY NUMBER(11)  DEFAULT '0' NOT NULL,
	PRIMARY KEY (ID),
	CONSTRAINT fk_b_learn_student1 FOREIGN KEY (COURSE_ID) REFERENCES b_learn_course(ID)
)
/

CREATE INDEX ix_b_learn_certification1 ON b_learn_certification(STUDENT_ID, COURSE_ID)
/

CREATE SEQUENCE sq_b_learn_certification INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_learn_certification_insert BEFORE INSERT ON b_learn_certification FOR EACH ROW BEGIN IF :NEW.ID IS NULL THEN SELECT sq_b_learn_certification.NEXTVAL INTO :NEW.ID FROM dual; END IF; END;
/

CREATE OR REPLACE TRIGGER b_learn_certification_update BEFORE UPDATE ON b_learn_certification FOR EACH ROW BEGIN IF :NEW.TIMESTAMP_X IS NOT NULL THEN :NEW.TIMESTAMP_X := SYSDATE; ELSE :NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X; END IF; END;
/

CREATE TABLE b_learn_site_path
(
  ID int not null,
  SITE_ID char(2 CHAR) not null,
  PATH varchar(255 CHAR) not null,
  TYPE char(1 CHAR) null,
  primary key (ID)
)
/
CREATE SEQUENCE SQ_B_LEARN_SITE_PATH INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER B_LEARN_SITE_PATH_INSERT
BEFORE INSERT
ON B_LEARN_SITE_PATH
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_B_LEARN_SITE_PATH.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE UNIQUE INDEX IX_LEARN_SITE_PATH_2 ON b_learn_site_path(SITE_ID, TYPE)
/

CREATE TABLE b_learn_test_mark
(
  ID int not null,
  TEST_ID int not null,
  SCORE int not null,
  MARK varchar(50 CHAR) not null,
  DESCRIPTION clob null,
  PRIMARY KEY (ID),
  CONSTRAINT fk_b_learn_test_mark1 FOREIGN KEY (TEST_ID) REFERENCES b_learn_test(ID)
)
/
CREATE SEQUENCE SQ_B_LEARN_TEST_MARK INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE OR REPLACE TRIGGER B_LEARN_TEST_MARK_INSERT
BEFORE INSERT
ON B_LEARN_TEST_MARK
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_B_LEARN_TEST_MARK.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE TABLE b_learn_groups
(
	ID NUMBER(11) NOT NULL,
	ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	TITLE VARCHAR2(255 CHAR) DEFAULT ' ' NOT NULL,
	CODE VARCHAR2(50 CHAR),
	SORT NUMBER(11) DEFAULT 500 NOT NULL,
	ACTIVE_FROM DATE,
	ACTIVE_TO DATE,
	COURSE_LESSON_ID NUMBER(11) NOT NULL,
	PRIMARY KEY(ID)
)
/
CREATE SEQUENCE sq_b_learn_groups INCREMENT BY 1 NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE TABLE b_learn_groups_member (
	LEARNING_GROUP_ID number(11) DEFAULT '0' NOT NULL,
	USER_ID number(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (LEARNING_GROUP_ID, USER_ID)
)
/
CREATE INDEX B_LEARN_GROUPS_MEMBER_UID_IBK ON b_learn_groups_member(USER_ID)
/

CREATE TABLE b_learn_groups_lesson (
	LEARNING_GROUP_ID number(11) DEFAULT '0' NOT NULL,
	LESSON_ID number(11) DEFAULT '0' NOT NULL,
	DELAY number(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (LEARNING_GROUP_ID, LESSON_ID)
)
/
CREATE INDEX B_LEARN_GROUPS_LESSON_L_ID_IBK ON b_learn_groups_lesson(LESSON_ID)
/
