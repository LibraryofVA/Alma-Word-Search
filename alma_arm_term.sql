USE [Automation]
GO

/****** Object:  Table [dbo].[alma_arm_term]    Script Date: 9/11/2024 10:37:24 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[alma_arm_term](
	[term_id] [varchar](233) NOT NULL,
	[category_id] [varchar](233) NOT NULL,
	[term_name] [varchar](233) NOT NULL,
	[date_stamp] [datetime] NOT NULL,
 CONSTRAINT [pk_alma_arm_term] PRIMARY KEY CLUSTERED 
(
	[term_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO

ALTER TABLE [dbo].[alma_arm_term] ADD  DEFAULT (newid()) FOR [term_id]
GO

ALTER TABLE [dbo].[alma_arm_term] ADD  DEFAULT (getdate()) FOR [date_stamp]
GO

ALTER TABLE [dbo].[alma_arm_term]  WITH CHECK ADD FOREIGN KEY([category_id])
REFERENCES [dbo].[alma_arm_category] ([category_id])
GO


