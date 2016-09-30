--
-- Vista information_schema.key_column_usage con diritti corretti
--

CREATE OR REPLACE VIEW "information_schema"."key_column_usage" (
    constraint_catalog,
    constraint_schema,
    constraint_name,
    table_catalog,
    table_schema,
    table_name,
    column_name,
    ordinal_position)
AS
SELECT (current_database())::information_schema.sql_identifier AS
    constraint_catalog, (ss.nc_nspname)::information_schema.sql_identifier AS
    constraint_schema, (ss.conname)::information_schema.sql_identifier AS
    constraint_name, (current_database())::information_schema.sql_identifier AS
    table_catalog, (ss.nr_nspname)::information_schema.sql_identifier AS
    table_schema, (ss.relname)::information_schema.sql_identifier AS
    table_name, (a.attname)::information_schema.sql_identifier AS column_name,
    ((ss.x).n)::information_schema.cardinal_number AS ordinal_position
FROM pg_attribute a, (
    SELECT r.oid, nc.nspname AS nc_nspname, c.conname, nr.nspname AS
        nr_nspname, r.relname, information_schema._pg_expandarray(c.conkey) AS x
    FROM pg_namespace nr, pg_class r, pg_namespace nc, pg_constraint c
    WHERE ((((((nr.oid = r.relnamespace) AND (r.oid = c.conrelid)) AND (nc.oid
        = c.connamespace)) AND (((c.contype = 'p'::"char") OR (c.contype =
        'u'::"char")) OR (c.contype = 'f'::"char"))) AND (r.relkind =
        'r'::"char")) 

	AND (pg_has_role(r.relowner, 'USAGE')
                     OR has_table_privilege(r.oid, 'SELECT')
                     OR has_table_privilege(r.oid, 'INSERT')
                     OR has_table_privilege(r.oid, 'UPDATE')
                     OR has_table_privilege(r.oid, 'REFERENCES')))
    ) ss
WHERE (((ss.oid = a.attrelid) AND (a.attnum = (ss.x).x)) AND (NOT a.attisdropped));




--
-- Vista information_schema.table_constraints con diritti corretti
--

CREATE OR REPLACE VIEW "information_schema"."table_constraints" (
    constraint_catalog,
    constraint_schema,
    constraint_name,
    table_catalog,
    table_schema,
    table_name,
    constraint_type,
    is_deferrable,
    initially_deferred)
AS
SELECT (current_database())::information_schema.sql_identifier AS
    constraint_catalog, (nc.nspname)::information_schema.sql_identifier AS
    constraint_schema, (c.conname)::information_schema.sql_identifier AS
    constraint_name, (current_database())::information_schema.sql_identifier AS
    table_catalog, (nr.nspname)::information_schema.sql_identifier AS
    table_schema, (r.relname)::information_schema.sql_identifier AS table_name,
    (CASE c.contype WHEN 'c'::"char" THEN 'CHECK'::text WHEN 'f'::"char" THEN
    'FOREIGN KEY'::text WHEN 'p'::"char" THEN 'PRIMARY KEY'::text WHEN
    'u'::"char" THEN 'UNIQUE'::text ELSE NULL::text
    END)::information_schema.character_data AS constraint_type, (CASE WHEN
    c.condeferrable THEN 'YES'::text ELSE 'NO'::text
    END)::information_schema.character_data AS is_deferrable, (CASE WHEN
    c.condeferred THEN 'YES'::text ELSE 'NO'::text
    END)::information_schema.character_data AS initially_deferred
FROM pg_namespace nc, pg_namespace nr, pg_constraint c, pg_class r
WHERE (((((nc.oid = c.connamespace) AND (nr.oid = r.relnamespace)) AND
    (c.conrelid = r.oid)) AND (r.relkind = 'r'::"char")) 
	AND (pg_has_role(r.relowner, 'USAGE')
                     OR has_table_privilege(r.oid, 'SELECT')
                     OR has_table_privilege(r.oid, 'INSERT')
                     OR has_table_privilege(r.oid, 'UPDATE')
                     OR has_table_privilege(r.oid, 'REFERENCES')));