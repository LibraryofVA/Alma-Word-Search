USE [Automation]
GO

/****** Object:  Table [dbo].[alma_arm]    Script Date: 9/11/2024 10:36:29 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[alma_arm](
	[arm_id] [varchar](233) NOT NULL,
	[arm_section] [varchar](100) NULL,
	[arm_mms_id] [varchar](233) NOT NULL,
	[arm_accession] [varchar](255) NULL,
	[arm_series] [varchar](max) NULL,
	[arm_title] [varchar](max) NULL,
	[arm_text] [varchar](max) NULL,
	[arm_terms] [varchar](max) NULL,
	[arm_category] [varchar](400) NULL,
	[arm_date_stamp] [datetime] NOT NULL,
 CONSTRAINT [pk_alma_arm] PRIMARY KEY CLUSTERED 
(
	[arm_mms_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [dbo].[alma_arm] ADD  DEFAULT (newid()) FOR [arm_id]
GO

ALTER TABLE [dbo].[alma_arm] ADD  DEFAULT (getdate()) FOR [arm_date_stamp]
GO


