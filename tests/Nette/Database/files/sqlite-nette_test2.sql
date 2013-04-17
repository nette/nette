DROP TABLE IF EXISTS nUsers_nTopics_alt;
DROP TABLE IF EXISTS nUsers_nTopics;
DROP TABLE IF EXISTS nTopics;
DROP TABLE IF EXISTS nPriorities;
DROP TABLE IF EXISTS nUsers;



CREATE TABLE [nUsers] (
	[nUserId] INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	[name] TEXT NOT NULL
);

INSERT INTO [nUsers] ([nUserId], [name]) VALUES (1, 'John');
INSERT INTO [nUsers] ([name]) VALUES ('Doe');



CREATE TABLE [nPriorities] (
	[nPriorityId] INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	[name] TEXT NOT NULL
);

INSERT INTO [nPriorities] ([nPriorityId], [name]) VALUES (20, 'High');
INSERT INTO [nPriorities] ([name]) VALUES ('Medium');
INSERT INTO [nPriorities] ([name]) VALUES ('Low');



CREATE TABLE [nTopics] (
	[nTopicId] INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	[title] TEXT NOT NULL,
	[nPriorityId] INTEGER NOT NULL,
	CONSTRAINT priority_id FOREIGN KEY (nPriorityId) REFERENCES nPriorities (nPriorityId)
);

INSERT INTO [nTopics] ([nTopicId], [title], [nPriorityId]) VALUES (10, 'Topic #1', 20);
INSERT INTO [nTopics] ([title], [nPriorityId]) VALUES ('Topic #2', 20);
INSERT INTO [nTopics] ([title], [nPriorityId]) VALUES ('Topic #3', 22);



CREATE TABLE [nUsers_nTopics] (
	[nUserId] INTEGER NOT NULL,
	[nTopicId] INTEGER NOT NULL,
	PRIMARY KEY ([nUserId], [nTopicId]),
	CONSTRAINT user_id FOREIGN KEY (nUserId) REFERENCES nUsers (nUserId),
	CONSTRAINT topic_id FOREIGN KEY (nTopicId) REFERENCES nTopics (nTopicId)
);

INSERT INTO [nUsers_nTopics] ([nUserId], [nTopicId]) VALUES (1, 10);
INSERT INTO [nUsers_nTopics] ([nUserId], [nTopicId]) VALUES (1, 12);
INSERT INTO [nUsers_nTopics] ([nUserId], [nTopicId]) VALUES (2, 11);



CREATE TABLE [nUsers_nTopics_alt] (
	[nUserId] INTEGER NOT NULL,
	[nTopicId] INTEGER NOT NULL,
	PRIMARY KEY ([nUserId], [nTopicId]),
	CONSTRAINT user_id_alt FOREIGN KEY (nUserId) REFERENCES nUsers (nUserId),
	CONSTRAINT topic_id_alt FOREIGN KEY (nTopicId) REFERENCES nTopics (nTopicId)
);

INSERT INTO [nUsers_nTopics_alt] ([nUserId], [nTopicId]) VALUES (2, 10);
