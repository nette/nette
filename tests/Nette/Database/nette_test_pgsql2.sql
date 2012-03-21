DROP SCHEMA IF EXISTS public CASCADE;
CREATE SCHEMA public;



CREATE TABLE "users" (
  "id" serial NOT NULL,
  "name" varchar(100) NOT NULL,
  PRIMARY KEY ("id")
);

INSERT INTO "users" ("id", "name") VALUES (1, 'John'), (2, 'Doe');
SELECT setval('users_id_seq', 2, TRUE);



CREATE TABLE "topics" (
  "id" serial NOT NULL,
  "userId" int NOT NULL,
  "title" varchar(100) NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "topics_ibfk_2" FOREIGN KEY ("userId") REFERENCES "users" ("id") ON DELETE CASCADE
);

INSERT INTO "topics" ("id", "userId", "title") VALUES (1, 2, 'Topic #1'), (2, 2, 'Topic #2'), (10, 1, 'Topic #3');
SELECT setval('topics_id_seq', 10, TRUE);
