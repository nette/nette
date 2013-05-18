DROP SCHEMA IF EXISTS public CASCADE;
CREATE SCHEMA public;


CREATE TABLE "nUsers" (
  "nUserId" serial NOT NULL,
  "name" varchar(100) NOT NULL,
  PRIMARY KEY ("nUserId")
);

INSERT INTO "nUsers" ("nUserId", "name") VALUES (1,	'John');
INSERT INTO "nUsers" ("nUserId", "name") VALUES (2,	'Doe');
SELECT setval('"nUsers_nUserId_seq"', 2, TRUE);

CREATE TABLE "nPriorities" (
		"nPriorityId" serial NOT NULL,
		"name" varchar(100) NOT NULL,
		PRIMARY KEY ("nPriorityId")
);

INSERT INTO "nPriorities" ("nPriorityId", "name") VALUES (20,	'High');
INSERT INTO "nPriorities" ("nPriorityId", "name") VALUES (21,	'Medium');
INSERT INTO "nPriorities" ("nPriorityId", "name") VALUES (22, 	'Low');
SELECT setval('"nPriorities_nPriorityId_seq"', 22, TRUE);

CREATE TABLE "nTopics" (
  "nTopicId" serial NOT NULL,
  "title" varchar(100) NOT NULL,
  "nPriorityId" int NOT NULL,
  PRIMARY KEY ("nTopicId"),
  CONSTRAINT priority_id FOREIGN KEY ("nPriorityId") REFERENCES "nPriorities" ("nPriorityId")
);

INSERT INTO "nTopics" ("nTopicId", "title", "nPriorityId") VALUES (10,	'Topic #1', 20);
INSERT INTO "nTopics" ("nTopicId", "title", "nPriorityId") VALUES (11,	'Topic #2', 20);
INSERT INTO "nTopics" ("nTopicId", "title", "nPriorityId") VALUES (12,	'Topic #3', 22);
SELECT setval('"nTopics_nTopicId_seq"', 12, TRUE);

CREATE TABLE "nUsers_nTopics" (
	"nUserId" int NOT NULL,
	"nTopicId" int NOT NULL,
	PRIMARY KEY ("nUserId", "nTopicId"),
	CONSTRAINT user_id FOREIGN KEY ("nUserId") REFERENCES "nUsers" ("nUserId"),
	CONSTRAINT topic_id FOREIGN KEY ("nTopicId") REFERENCES "nTopics" ("nTopicId")
);

INSERT INTO "nUsers_nTopics" ("nUserId", "nTopicId") VALUES (1, 10);
INSERT INTO "nUsers_nTopics" ("nUserId", "nTopicId") VALUES (1, 12);
INSERT INTO "nUsers_nTopics" ("nUserId", "nTopicId") VALUES (2, 11);

CREATE TABLE "nUsers_nTopics_alt" (
	"nUserId" int NOT NULL,
	"nTopicId" int NOT NULL,
	PRIMARY KEY ("nUserId", "nTopicId"),
	CONSTRAINT user_id_alt FOREIGN KEY ("nUserId") REFERENCES "nUsers" ("nUserId"),
	CONSTRAINT topic_id_alt FOREIGN KEY ("nTopicId") REFERENCES "nTopics" ("nTopicId")
);

INSERT INTO "nUsers_nTopics_alt" ("nUserId", "nTopicId") VALUES (2, 10);
