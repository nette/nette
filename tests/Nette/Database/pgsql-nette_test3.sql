DROP SCHEMA IF EXISTS public CASCADE;
CREATE SCHEMA public;

DROP TABLE IF EXISTS "types";
CREATE TABLE "types" (
  "int1" smallint NULL,
  "int2" integer NULL,
  "int3" bigint NULL,
  "float1" numeric NULL,
  "float2" real NULL,
  "float3" double precision NULL,
  "float4" money NULL,
  "bool" boolean NULL,
  "date1" date NULL,
  "date2" time NULL,
  "date3" timestamp NULL,
  "date4" interval NULL,
  "str1" character(30) NULL,
  "str2" character varying(30) NULL,
  "str3" text NULL,
  "str4" tsquery NULL,
  "str5" tsvector NULL,
  "str6" uuid NULL,
  "str7" xml NULL,
  "str8" cidr NULL,
  "str9" inet NULL,
  "str10" macaddr NULL,
  "bin1" bit NULL,
  "bin2" bit varying NULL,
  "bin3" bytea NULL,
  "geo1" box NULL,
  "geo2" circle NULL,
  "geo3" lseg NULL,
  "geo4" path NULL,
  "geo5" point NULL,
  "geo6" polygon NULL
);

INSERT INTO "types" ("int1", "int2", "int3", "float1", "float2", "float3", "float4", "bool", "date1", "date2", "date3", "date4", "str1", "str2", "str3", "str4", "str5", "str6", "str7", "str8", "str9", "str10", "bin1", "bin2", "geo1", "geo2", "geo3", "geo4", "geo5", "geo6")
VALUES ('1', '1', '1', '1.00', '1.10', '1.11', '0', 'T', '2012-10-13', '10:10:10', '2012-10-13 10:10:10', '1 year', 'a', 'a', 'a', 'a', 'a', 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11', 'a', '192.168.1', '192.168.1.1', '08002b:010203', '1', '1', '10,20,30,40', '10,20,30', '10,20,30,40', '10,20,30,40', '10,20', '10,20,30,40');

INSERT INTO "types" ("int1", "int2", "int3", "float1", "float2", "float3", "float4", "bool", "date1", "date2", "date3", "date4", "str1", "str2", "str3", "str4", "str5", "str6", "str7", "str8", "str9", "str10", "bin1", "bin2", "geo1", "geo2", "geo3", "geo4", "geo5", "geo6")
VALUES ('0', '0', '0', '0.00', '0.0', '0.0', NULL, 'F', NULL, NULL, NULL, '0 year', '', '', '', '', '', 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11', 'a', '192.168.1', '192.168.1.1', '08002b:010203', '0', '0', '10,20,30,40', '10,20,30', '10,20,30,40', '10,20,30,40', '10,20', '10,20,30,40');

INSERT INTO "types" ("int1", "int2", "int3", "float1", "float2", "float3", "float4", "bool", "date1", "date2", "date3", "date4", "str1", "str2", "str3", "str4", "str5", "str6", "str7", "str8", "str9", "str10", "bin1", "bin2", "geo1", "geo2", "geo3", "geo4", "geo5", "geo6")
VALUES (NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
