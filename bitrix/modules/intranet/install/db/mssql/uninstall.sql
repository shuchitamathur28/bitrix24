ALTER TABLE B_INTRANET_SHAREPOINT DROP CONSTRAINT DF_B_INTRANET_SHAREPOINT_SYNC_DATE
GO
ALTER TABLE B_INTRANET_SHAREPOINT DROP CONSTRAINT DF_B_INTRANET_SHAREPOINT_SYNC_PERIOD
GO
ALTER TABLE B_INTRANET_SHAREPOINT DROP CONSTRAINT PK_B_INTRANET_SHAREPOINT
GO
DROP TABLE B_INTRANET_SHAREPOINT
GO

ALTER TABLE B_INTRANET_SHAREPOINT_FIELD DROP CONSTRAINT PK_B_INTRANET_SHAREPOINT_FIELD
GO
DROP TABLE B_INTRANET_SHAREPOINT_FIELD
GO

ALTER TABLE B_INTRANET_SHAREPOINT_QUEUE DROP CONSTRAINT PK_B_INTRANET_SHAREPOINT_QUEUE
GO
DROP TABLE B_INTRANET_SHAREPOINT_QUEUE
GO

ALTER TABLE B_INTRANET_SHAREPOINT_LOG DROP CONSTRAINT PK_B_INTRANET_SHAREPOINT_LOG
GO
DROP TABLE B_INTRANET_SHAREPOINT_LOG
GO

ALTER TABLE B_RATING_SUBORDINATE DROP CONSTRAINT PK_B_RAT_SUB
GO
DROP TABLE B_RATING_SUBORDINATE
GO

ALTER TABLE B_INTRANET_USTAT_HOUR DROP CONSTRAINT PK_B_INTRANET_USTAT_HOUR
GO
DROP TABLE B_INTRANET_USTAT_HOUR
GO

ALTER TABLE B_INTRANET_USTAT_DAY DROP CONSTRAINT PK_B_INTRANET_USTAT_DAY
GO
DROP TABLE B_INTRANET_USTAT_DAY
GO

ALTER TABLE B_INTRANET_DSTAT_HOUR DROP CONSTRAINT PK_B_INTRANET_DSTAT_HOUR
GO
DROP TABLE B_INTRANET_DSTAT_HOUR
GO

ALTER TABLE B_INTRANET_DSTAT_DAY DROP CONSTRAINT PK_B_INTRANET_DSTAT_DAY
GO
DROP TABLE B_INTRANET_DSTAT_DAY
GO

ALTER TABLE B_INTRANET_USERSUBORD DROP CONSTRAINT PK_B_INTRANET_USERSUBORD
GO
DROP TABLE B_INTRANET_USERSUBORD
GO

ALTER TABLE B_INTRANET_USER2DEP DROP CONSTRAINT PK_B_INTRANET_USER2DEP
GO
DROP TABLE B_INTRANET_USER2DEP
GO