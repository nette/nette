DROP SCHEMA IF EXISTS public CASCADE;
CREATE SCHEMA public;

DROP TABLE IF EXISTS "types";
CREATE TABLE "types" (
  "smallint" smallint NULL,
  "integer" integer NULL,
  "bigint" bigint NULL,
  "numeric" numeric NULL,
  "real" real NULL,
  "double" double precision NULL,
  "money" money NULL,
  "bool" boolean NULL,
  "date" date NULL,
  "time" time NULL,
  "timestamp" timestamp NULL,
  "interval" interval NULL,
  "character" character(30) NULL,
  "character_varying" character varying(30) NULL,
  "text" text NULL,
  "tsquery" tsquery NULL,
  "tsvector" tsvector NULL,
  "uuid" uuid NULL,
  "xml" xml NULL,
  "cidr" cidr NULL,
  "inet" inet NULL,
  "macaddr" macaddr NULL,
  "bit" bit NULL,
  "bit_varying" bit varying NULL,
  "bytea" bytea NULL,
  "box" box NULL,
  "circle" circle NULL,
  "lseg" lseg NULL,
  "path" path NULL,
  "point" point NULL,
  "polygon" polygon NULL
);

INSERT INTO "types" ("smallint", "integer", "bigint", "numeric", "real", "double", "money", "bool", "date", "time", "timestamp", "interval", "character", "character_varying", "text", "tsquery", "tsvector", "uuid", "xml", "cidr", "inet", "macaddr", "bit", "bit_varying", "box", "circle", "lseg", "path", "point", "polygon")
VALUES ('1', '1', '1', '1.00', '1.10', '1.11', '0', 'T', '2012-10-13', '10:10:10', '2012-10-13 10:10:10', '1 year', 'a', 'a', 'a', 'a', 'a', 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11', 'a', '192.168.1', '192.168.1.1', '08002b:010203', '1', '1', '10,20,30,40', '10,20,30', '10,20,30,40', '10,20,30,40', '10,20', '10,20,30,40');

INSERT INTO "types" ("smallint", "integer", "bigint", "numeric", "real", "double", "money", "bool", "date", "time", "timestamp", "interval", "character", "character_varying", "text", "tsquery", "tsvector", "uuid", "xml", "cidr", "inet", "macaddr", "bit", "bit_varying", "box", "circle", "lseg", "path", "point", "polygon")
VALUES ('0', '0', '0', '0.00', '0.0', '0.0', NULL, 'F', NULL, NULL, NULL, '0 year', '', '', '', '', '', 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11', 'a', '192.168.1', '192.168.1.1', '08002b:010203', '0', '0', '10,20,30,40', '10,20,30', '10,20,30,40', '10,20,30,40', '10,20', '10,20,30,40');

INSERT INTO "types" ("smallint", "integer", "bigint", "numeric", "real", "double", "money", "bool", "date", "time", "timestamp", "interval", "character", "character_varying", "text", "tsquery", "tsvector", "uuid", "xml", "cidr", "inet", "macaddr", "bit", "bit_varying", "box", "circle", "lseg", "path", "point", "polygon")
VALUES (NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
