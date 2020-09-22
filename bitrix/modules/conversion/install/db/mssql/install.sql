
create table b_conv_context
(
	ID       int      not null identity (1, 1),
	SNAPSHOT char(64) not null
)
GO
alter table b_conv_context add constraint PK_B_CONV_CONTEXT primary key (ID)
GO
create unique index IX_B_CONV_CONTEXT_SNAPSHOT on b_conv_context (SNAPSHOT)
GO

create table b_conv_context_attribute
(
	CONTEXT_ID int         not null,
	NAME       varchar(30) not null,
	VALUE      varchar(30) not null
)
GO
alter table b_conv_context_attribute add constraint PK_B_CONV_CONTEXT_ATTRIBUTE primary key (CONTEXT_ID, NAME)
GO

create table b_conv_context_counter_day
(
	DAY        date        not null,
	CONTEXT_ID int         not null,
	NAME       varchar(30) not null,
	VALUE      float       not null
)
GO
alter table b_conv_context_counter_day add constraint PK_B_CONV_CONTEXT_COUNTER_DAY primary key (DAY, CONTEXT_ID, NAME)
GO

create table b_conv_context_entity_item
(
	ENTITY     varchar(30) not null,
	ITEM       varchar(30) not null,
	CONTEXT_ID int         not null
)
GO
alter table b_conv_context_entity_item add constraint PK_B_CONV_CONTEXT_ENTITY_ITEM primary key (ENTITY, ITEM)
GO
