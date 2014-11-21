--Use to sync local table with remote table on source server, uses commitment control and dynamic sql
CREATE OR REPLACE PROCEDURE YOURLIB.SYNCTABLE (
        IN PAR_SOURCE_SERVER VARCHAR(128),
        IN PAR_LIBRARY VARCHAR(128),
        IN PAR_TABLE VARCHAR(128)
    )

    LANGUAGE SQL
    SPECIFIC YOURLIB.SYNCTABLE
    NOT DETERMINISTIC
    COMMIT ON RETURN YES

    SET OPTION  ALWBLK = *ALLREAD ,
    ALWCPYDTA = *OPTIMIZE ,
    COMMIT = *UR ,--  read uncommitted (UR), cursor stability (CS), repeatable read (RR) and read stability (RS).
    DECRESULT = (31, 31, 00) ,
    DFTRDBCOL = *NONE ,
    DYNDFTCOL = *NO ,
    -- DYNUSRPRF - The *OWNER of this stored procedure will execute the commands below.  Take that Permissions issues
    -- Be careful to who you GRANT access to this Stored Procedure
    DYNUSRPRF = *OWNER ,
    SRTSEQ = *HEX

    P1: BEGIN
        --Variables
        DECLARE DYNAMIC_STMT VARCHAR(1000);
        DECLARE LIBRARY_AND_TABLE VARCHAR(200);
        DECLARE BACKUP_LIBRARY_AND_TABLE VARCHAR(200);
        DECLARE DOES_TABLE_EXIST_ON_CURRENT_SYSTEM CHAR(1);

        --Initialize Variables
        SET LIBRARY_AND_TABLE = PAR_LIBRARY || '.' || PAR_TABLE;
        SET BACKUP_LIBRARY_AND_TABLE = 'BACKUP.' || PAR_TABLE;

        --Does passed in table exist on current system?
        IF (EXISTS(SELECT * FROM QSYS2.SYSTABLES WHERE TABLE_SCHEMA = PAR_LIBRARY AND TABLE_NAME=PAR_TABLE)) THEN
            SET DOES_TABLE_EXIST_ON_CURRENT_SYSTEM = 'Y';
        ELSE
            SET DOES_TABLE_EXIST_ON_CURRENT_SYSTEM = 'N';
        END IF;

        --Logic Begins:
        --If no BACKUP Library create one to store old table data
        IF (NOT EXISTS(SELECT * FROM TABLE(SYSIBM.SCHEMAS()) AS SCHEMAS WHERE ODOBNM='BACKUP')) THEN
            CREATE SCHEMA BACKUP;
        END IF;

        IF (DOES_TABLE_EXIST_ON_CURRENT_SYSTEM='Y') THEN
            --Create and insert into BACKUP.Table  if it doesn't exist
            IF (NOT EXISTS(SELECT * FROM QSYS2.SYSTABLES WHERE TABLE_SCHEMA = 'BACKUP' AND TABLE_NAME=PAR_TABLE)) THEN
                SET DYNAMIC_STMT = 'CREATE TABLE  ' || BACKUP_LIBRARY_AND_TABLE || ' AS (SELECT * FROM ' || LIBRARY_AND_TABLE || ')   WITH DATA';
                EXECUTE IMMEDIATE DYNAMIC_STMT;
            ELSE
            --Insert into the table if BACKUP.Table exists
                SET DYNAMIC_STMT = ' INSERT INTO  ' || BACKUP_LIBRARY_AND_TABLE || ' SELECT * FROM ' || LIBRARY_AND_TABLE;
                EXECUTE IMMEDIATE DYNAMIC_STMT;
            END IF;
        END IF;

        --Create temp table from source data
        SET DYNAMIC_STMT = 'DECLARE GLOBAL TEMPORARY TABLE ' || PAR_TABLE || '  AS (SELECT * FROM '||PAR_SOURCE_SERVER||'.' || LIBRARY_AND_TABLE || ') WITH DATA WITH REPLACE ';
        EXECUTE IMMEDIATE DYNAMIC_STMT;

        IF (DOES_TABLE_EXIST_ON_CURRENT_SYSTEM='N') THEN
            --Create Table from Temp Table 
            SET DYNAMIC_STMT = 'CREATE TABLE ' || LIBRARY_AND_TABLE || ' AS (SELECT * FROM SESSION.' || PAR_TABLE || ') WITH DATA';
            EXECUTE IMMEDIATE DYNAMIC_STMT;
        ELSE
            --Wipe local table
            SET DYNAMIC_STMT = 'DELETE FROM ' || LIBRARY_AND_TABLE;
            EXECUTE IMMEDIATE DYNAMIC_STMT;

            --Replace with Source data
            SET DYNAMIC_STMT = 'INSERT INTO '|| LIBRARY_AND_TABLE ||' (SELECT * FROM SESSION.' || PAR_TABLE || ')' ;
            EXECUTE IMMEDIATE DYNAMIC_STMT;
        END IF;
    END P1;

COMMENT ON SPECIFIC PROCEDURE YOURLIB.SYNCTABLE IS 'Sync Remote Table to local (Parameters: source server, library, and table)' ;
GRANT EXECUTE ON PROCEDURE YOURLIB.SYNCTABLE TO USERPROFILEALLOWEDTOUSETHIS;

----------------------
----- Test Script ----
----------------------

--Execute the command
CALL YOURLIB.SYNCTABLE('SOURCEIBMISERVERNAME','LIBRARY','TABLE');

--Did it sync?
SELECT * FROM LIBRARY.TABLE;
--Did it back it up?
SELECT * FROM BACKUP.TABLE;