USE [Automation]
GO

/****** Object:  Table [dbo].[alma_api_data]    Script Date: 9/11/2024 10:35:15 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[alma_api_data](
	[api_id] [varchar](233) NOT NULL,
	[api_mms_id] [varchar](233) NOT NULL,
	[api_text] [varchar](max) NULL,
	[api_title] [varchar](max) NULL,
	[api_section] [varchar](100) NULL,
	[api_series] [varchar](max) NULL,
	[api_date_stamp] [datetime] NOT NULL,
	[api_accession] [varchar](255) NULL,
	[api_complete] [bit] NOT NULL,
 CONSTRAINT [pk_alma_api_data] PRIMARY KEY CLUSTERED 
(
	[api_mms_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [dbo].[alma_api_data] ADD  DEFAULT (newid()) FOR [api_id]
GO

ALTER TABLE [dbo].[alma_api_data] ADD  DEFAULT (getdate()) FOR [api_date_stamp]
GO

ALTER TABLE [dbo].[alma_api_data] ADD  DEFAULT ((0)) FOR [api_status]
GO


