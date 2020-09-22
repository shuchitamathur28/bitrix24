DROP TABLE b_search_user_right
GO
DROP TABLE b_search_custom_rank
GO
DROP TABLE b_search_content_freq
GO
DROP TABLE b_search_content_stem
GO
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'b_search_stem') DROP TABLE b_search_stem
GO
DROP TABLE b_search_tags
GO
DROP TABLE b_search_content_right
GO
DROP TABLE b_search_content_param
GO
DROP TABLE b_search_content_text
GO
DROP TABLE b_search_content
GO
DROP TABLE b_search_content_site
GO
DROP TABLE b_search_suggest
GO
DROP TABLE b_search_content_title
GO
