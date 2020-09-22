
create table b_conv_context
(
	ID       number(18     ) not null,
	SNAPSHOT char  (64 char) not null,
	constraint PK_B_CONV_CONTEXT primary key (ID)
)
/
create sequence sq_b_conv_context start with 1 increment by 1 nomaxvalue nocycle nocache noorder
/
create or replace trigger b_conv_context_ins_tr
before insert
on b_conv_context
for each row
begin
	if :new.ID is null then
		SELECT sq_b_conv_context.nextval into :new.ID from dual;
	end if;
end;
/
create unique index IX_B_CONV_CONTEXT_SNAPSHOT on b_conv_context (SNAPSHOT)
/

create table b_conv_context_attribute
(
	CONTEXT_ID number  (18     ) not null,
	NAME       varchar2(30 char) not null,
	VALUE      varchar2(30 char) not null,
	constraint PK_B_CONV_CONTEXT_ATTRIBUTE primary key (CONTEXT_ID, NAME)
)
/

create table b_conv_context_counter_day
(
	DAY        date              not null,
	CONTEXT_ID number  (18     ) not null,
	NAME       varchar2(30 char) not null,
	VALUE      float             not null,
	constraint PK_B_CONV_CONTEXT_COUNTER_DAY primary key (DAY, CONTEXT_ID, NAME)
)
/

create table b_conv_context_entity_item
(
	ENTITY     varchar2(30 char) not null,
	ITEM       varchar2(30 char) not null,
	CONTEXT_ID number  (18     ) not null,
	constraint PK_B_CONV_CONTEXT_ENTITY_ITEM primary key (ENTITY, ITEM)
)
/
