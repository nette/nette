DROP SCHEMA IF EXISTS public CASCADE;
CREATE SCHEMA public;


CREATE TABLE "nUsers" (
  "nUserId" serial NOT NULL,
  "users_id" integer NOT NULL,
  "name" character varying(100) NOT NULL,
  "version" integer NOT NULL,
  "status" character NOT NULL,
  "third" integer NOT NULL
);

INSERT INTO "nUsers" ("nUserId", "users_id", "name", "version", "status", "third") VALUES (1,1,	'John',1,'e',0);
INSERT INTO "nUsers" ("nUserId", "users_id", "name", "version", "status", "third") VALUES (2,2,	'Doe',1,'e',0);
INSERT INTO "nUsers" ("nUserId", "users_id", "name", "version", "status", "third") VALUES (3,1,	'Johnny',2,'u',0);
INSERT INTO "nUsers" ("nUserId", "users_id", "name", "version", "status", "third") VALUES (4,2,	'Smith',2,'u',0);
SELECT setval('"nUsers_nUserId_seq"', 4, TRUE);

