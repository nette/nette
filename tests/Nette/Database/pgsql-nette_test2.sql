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

CREATE TABLE "nTopics" (
  "nTopicId" serial NOT NULL,
  "title" varchar(100) NOT NULL,
  PRIMARY KEY ("nTopicId")
);

INSERT INTO "nTopics" ("nTopicId", "title") VALUES (10,	'Topic #1');
INSERT INTO "nTopics" ("nTopicId", "title") VALUES (11,	'Topic #2');
INSERT INTO "nTopics" ("nTopicId", "title") VALUES (12,	'Topic #3');
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
