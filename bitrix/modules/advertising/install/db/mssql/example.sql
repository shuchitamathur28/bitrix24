SET IDENTITY_INSERT B_ADV_CONTRACT ON
GO
INSERT INTO B_ADV_CONTRACT (ID,	ACTIVE,	NAME, SORT, DESCRIPTION, EMAIL_COUNT, DATE_MODIFY) VALUES (1, 'Y', 'Default', '10000', 'all site without any restrictions', 1, GETDATE()) 
GO
SET IDENTITY_INSERT B_ADV_CONTRACT OFF
GO

INSERT INTO B_ADV_TYPE (SID, ACTIVE, SORT, NAME, DATE_MODIFY) VALUES ('ALL', 'Y', 0, null, GETDATE()) 
GO
INSERT INTO B_ADV_TYPE (SID, ACTIVE, SORT, NAME, DATE_MODIFY) VALUES ('TOP', 'Y', 100, 'Top banner', GETDATE()) 
GO
INSERT INTO B_ADV_TYPE (SID, ACTIVE, SORT, NAME, DATE_MODIFY) VALUES ('LEFT', 'Y', 200, 'Left banner', GETDATE()) 
GO
INSERT INTO B_ADV_TYPE (SID, ACTIVE, SORT, NAME, DATE_MODIFY) VALUES ('BOTTOM', 'Y', 300, 'Bottom banner', GETDATE()) 
GO

INSERT INTO B_ADV_CONTRACT_2_TYPE (CONTRACT_ID, TYPE_SID) VALUES (1, 'ALL') 
GO

INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 0) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 1) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 2) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 3) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 4) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 5) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 6) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 7) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 8) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 9) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 10) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 11) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 12) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 13) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 14) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 15) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 16) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 17) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 18) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 19) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 20) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 21) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 22) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 23) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 0) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 1) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 2) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 3) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 4) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 5) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 6) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 7) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 8) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 9) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 10) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 11) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 12) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 13) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 14) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 15) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 16) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 17) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 18) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 19) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 20) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 21) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 22) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 23) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 0) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 1) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 2) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 3) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 4) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 5) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 6) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 7) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 8) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 9) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 10) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 11) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 12) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 13) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 14) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 15) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 16) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 17) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 18) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 19) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 20) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 21) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 22) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 23) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 0) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 1) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 2) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 3) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 4) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 5) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 6) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 7) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 8) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 9) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 10) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 11) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 12) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 13) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 14) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 15) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 16) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 17) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 18) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 19) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 20) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 21) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 22) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 23) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 0) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 1) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 2) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 3) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 4) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 5) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 6) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 7) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 8) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 9) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 10) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 11) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 12) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 13) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 14) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 15) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 16) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 17) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 18) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 19) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 20) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 21) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 22) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 23) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 0) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 1) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 2) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 3) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 4) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 5) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 6) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 7) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 8) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 9) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 10) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 11) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 12) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 13) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 14) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 15) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 16) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 17) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 18) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 19) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 20) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 21) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 22) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 23) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 0) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 1) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 2) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 3) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 4) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 5) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 6) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 7) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 8) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 9) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 10) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 11) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 12) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 13) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 14) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 15) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 16) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 17) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 18) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 19) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 20) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 21) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 22) 
GO
INSERT INTO B_ADV_CONTRACT_2_WEEKDAY (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 23) 
GO
