<?php

// db_var.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: gustavo $
// $Id: db_var.inc.php,v 1.19 2006/11/07 00:28:32 gustavo Exp $

// check whether setup.php calls this script - authentication!
if (!defined('setup_included')) die('Please use setup.php!');

// create auto_increment for oracle
function sequence($a, $e='ID') {
    $a = DB_PREFIX.$a;
    $b = $a."_id_seq";
    $result = db_query("
      CREATE SEQUENCE $b
        START WITH 1
        INCREMENT BY 1
        NOMINVALUE
        NOMAXVALUE
        NOCYCLE
        CACHE 20
        ORDER
    ");
    $c = $a."_auto_inc";
    $d = $b.".nextval";
    $result = db_query("CREATE OR REPLACE TRIGGER $c BEFORE INSERT ON $a REFERENCING OLD AS OLD NEW AS NEW FOR EACH ROW BEGIN SELECT $d INTO :NEW.$e FROM DUAL; END;");
}

// function which implements the autoinc function for interbase
function ib_autoinc($table) {
    // looks like some overhead but its required
    $table     = DB_PREFIX.$table;
    $trigger   = "set_id_".$table;
    $generator = "gen_id_".$table;

    $result = db_query("
      CREATE GENERATOR $generator
    "); //each table gets its own generator for its own trigger
    //echo $result;
    //echo "generator for $table generated<br />";
    $result = db_query("
      CREATE TRIGGER $trigger FOR $table BEFORE INSERT AS
      BEGIN
      new.id=gen_id($generator,1);
      END
    "); //generator which was incremented by 1 from its own trigger
    //echo $result;
    //echo "trigger for $table generated<br />";
}

// error codes
$db_error_code_table_exists["mysql"] = '1050';
$db_error_code_table_exists["sqlite"] = '';
$db_error_code_table_exists["oracle"] = '';
$db_error_code_table_exists["informix"] = '11053';
$db_error_code_table_exists["postgresql"] = '42P07';
$db_error_code_table_exists["ms_sql"] = '';
$db_error_code_table_exists["interbase"] = '';
$db_error_code_table_exists["db2"] = '';

// ID auto_increment
$db_int8_auto["mysql"] = "int(8) NOT NULL auto_increment";     // ID int(8) NOT NULL auto_increment,
$db_int8_auto["sqlite"] = "integer";
$db_int8_auto["oracle"] = "number(8) NOT NULL ";
$db_int8_auto["informix"] = "serial NOT NULL ";
$db_int8_auto["postgresql"] = "serial";
$db_int8_auto["ms_sql"] = "int identity";
$db_int8_auto["interbase"] = "integer not null";
$db_int8_auto["db2"] = "integer not null GENERATED ALWAYS AS IDENTITY (START WITH 0, INCREMENT BY 1) ";

// ID auto_increment
$db_int11_auto["mysql"] = "int(11) NOT NULL auto_increment";   // ID int(11) NOT NULL auto_increment,
$db_int11_auto["sqlite"] = "integer";
$db_int11_auto["oracle"] = "number(11) NOT NULL ";
$db_int11_auto["informix"] = "serial NOT NULL ";
$db_int11_auto["postgresql"] = "serial";
$db_int11_auto["ms_sql"] = "decimal identity";
$db_int11_auto["interbase"] = "decimal not null";
// ************************************************** DB2 knows float, long, double, no decimal.
$db_int11_auto["db2"] = "integer not null GENERATED ALWAYS AS IDENTITY (START WITH 0, INCREMENT BY 1) ";

// text
// since 'post' only allows 4 kB of transmitted signs, varchar2(4000) fits.
$db_text["mysql"] = "text";
$db_text["sqlite"] = "text";
$db_text["oracle"] = "varchar2(4000)";
$db_text["informix"] = "char(4000)";
$db_text["postgresql"] = "text";
$db_text["ms_sql"] = "text NULL";
$db_text["interbase"] = "varchar(4000)";
$db_text["db2"] = "clob";

// text
// since 'post' only allows 4 kB of transmitted signs, varchar2(4000) fits.
$db_text2["mysql"] = "text";
$db_text2["sqlite"] = "text";
$db_text2["oracle"] = "varchar2(4000)";
$db_text2["informix"] = "char(4000)";
$db_text2["postgresql"] = "text";
$db_text2["ms_sql"] = "text NULL";
$db_text2["interbase"] = "varchar(4000)";
$db_text2["db2"] = "clob";

// integer
$db_int1["mysql"] = "int(1)";                                      // int(1),
$db_int1["sqlite"] = "int(1)";
$db_int1["oracle"] = "number(1)";
$db_int1["informix"] = "decimal(1)";
$db_int1["postgresql"] = "integer";
$db_int1["ms_sql"] = "tinyint NULL";
$db_int1["interbase"] = "decimal(1)";
$db_int1["db2"] = "integer";

$db_int2["mysql"] = "int(2)";                                      // int(2),
$db_int2["sqlite"] = "int(2)";
$db_int2["oracle"] = "number(2)";
$db_int2["informix"] = "decimal(2)";
$db_int2["postgresql"] = "integer";
$db_int2["ms_sql"] = "smallint NULL";
$db_int2["interbase"] = "decimal(2)";
$db_int2["db2"] = "integer";

$db_int3["mysql"] = "int(3)";                                      // int(3),
$db_int3["sqlite"] = "int(3)";
$db_int3["oracle"] = "number(3)";
$db_int3["informix"] = "decimal(3)";
$db_int3["postgresql"] = "integer";
$db_int3["ms_sql"] = "int NULL";
$db_int3["interbase"] = "decimal(3)";
$db_int3["db2"] = "integer";

$db_int4["mysql"] = "int(4)";                                      // int(4),
$db_int4["sqlite"] = "int(4)";
$db_int4["oracle"] = "number(4)";
$db_int4["informix"] = "decimal(4)";
$db_int4["postgresql"] = "integer";
$db_int4["ms_sql"] = "int NULL";
$db_int4["interbase"] = "decimal(4)";
$db_int4["db2"] = "integer";

$db_int6["mysql"] = "int(6)";                                      // int(6),
$db_int6["sqlite"] = "int(6)";
$db_int6["oracle"] = "number(6)";
$db_int6["informix"] = "decimal(6)";
$db_int6["postgresql"] = "integer";
$db_int6["ms_sql"] = "int NULL";
$db_int6["interbase"] = "decimal(6)";
$db_int6["db2"] = "integer";

$db_int8["mysql"] = "int(8)";                                      // int(8),
$db_int8["sqlite"] = "int(8)";
$db_int8["oracle"] = "number(8)";
$db_int8["informix"] = "decimal(8)";
$db_int8["postgresql"] = "integer";
$db_int8["ms_sql"] = "int NULL";
$db_int8["interbase"] = "decimal(8)";
$db_int8["db2"] = "integer";

$db_int11["mysql"] = "int(11)";                                    // int(11),
$db_int11["sqlite"] = "int(11)";
$db_int11["oracle"] = "number(11)";
$db_int11["informix"] = "decimal(11)";
$db_int11["postgresql"] = "integer";
$db_int11["ms_sql"] = "decimal NULL";
$db_int11["interbase"] = "decimal(11)";
$db_int11["db2"] = "integer";

// char
$db_char1["mysql"] = "char(1)";                                    // char(1)
$db_char1["sqlite"] = "char(1)";
$db_char1["oracle"] = "char(1)";
$db_char1["informix"] = "char(1)";
$db_char1["postgresql"] = "char(1)";
$db_char1["ms_sql"] = "char(1) NULL";
$db_char1["interbase"] = "char(1)";
$db_char1["db2"] = "char(1)";

// char
$db_char2["mysql"] = "char(2)";                                    // char(2)
$db_char2["sqlite"] = "char(2)";
$db_char2["oracle"] = "char(2)";
$db_char2["informix"] = "char(2)";
$db_char2["postgresql"] = "char(2)";
$db_char2["ms_sql"] = "char(2) NULL";
$db_char2["interbase"] = "char(2)";
$db_char2["db2"] = "char(2)";

//varchar
$db_varchar2["mysql"] = "varchar(2)";                             // varchar(2),
$db_varchar2["sqlite"] = "varchar(2)";
$db_varchar2["oracle"] = "varchar2(2)";
$db_varchar2["informix"] = "varchar(2)";
$db_varchar2["postgresql"] = "varchar(2)";
$db_varchar2["ms_sql"] = "varchar(2) NULL";
$db_varchar2["interbase"] = "varchar(2)";
$db_varchar2["db2"] = "varchar(2)";

$db_varchar3["mysql"] = "varchar(3)";                             // varchar(3),
$db_varchar3["sqlite"] = "varchar(3)";
$db_varchar3["oracle"] = "varchar2(3)";
$db_varchar3["informix"] = "varchar(3)";
$db_varchar3["postgresql"] = "varchar(3)";
$db_varchar3["ms_sql"] = "varchar(3) NULL";
$db_varchar3["interbase"] = "varchar(3)";
$db_varchar3["db2"] = "varchar(3)";

$db_varchar4["mysql"] = "varchar(4)";                             // varchar(4),
$db_varchar4["sqlite"] = "varchar(4)";
$db_varchar4["oracle"] = "varchar2(4)";
$db_varchar4["informix"] = "varchar(4)";
$db_varchar4["postgresql"] = "varchar(4)";
$db_varchar4["ms_sql"] = "varchar(4) NULL";
$db_varchar4["interbase"] = "varchar(4)";
$db_varchar4["db2"] = "varchar(4)";

$db_varchar10["mysql"] = "varchar(10)";                           // varchar(10),
$db_varchar10["sqlite"] = "varchar(10)";
$db_varchar10["oracle"] = "varchar2(10)";
$db_varchar10["informix"] = "varchar(10)";
$db_varchar10["postgresql"] = "varchar(10)";
$db_varchar10["ms_sql"] = "varchar(10) NULL";
$db_varchar10["interbase"] = "varchar(10)";
$db_varchar10["db2"] = "varchar(10)";

$db_varchar20["mysql"] = "varchar(20)";                           // varchar(20),
$db_varchar20["sqlite"] = "varchar(20)";
$db_varchar20["oracle"] = "varchar2(20)";
$db_varchar20["informix"] = "varchar(20)";
$db_varchar20["postgresql"] = "varchar(20)";
$db_varchar20["ms_sql"] = "varchar(20) NULL";
$db_varchar20["interbase"] = "varchar(20)";
$db_varchar20["db2"] = "varchar(20)";

$db_varchar30["mysql"] = "varchar(30)";                           // varchar(30),
$db_varchar30["sqlite"] = "varchar(30)";
$db_varchar30["oracle"] = "varchar2(30)";
$db_varchar30["informix"] = "varchar(30)";
$db_varchar30["postgresql"] = "varchar(30)";
$db_varchar30["ms_sql"] = "varchar(30) NULL";
$db_varchar30["interbase"] = "varchar(30)";
$db_varchar30["db2"] = "varchar(30)";

$db_varchar40["mysql"] = "varchar(40)";                           // varchar(40)
$db_varchar40["sqlite"] = "varchar(40)";
$db_varchar40["oracle"] = "varchar2(40)";
$db_varchar40["informix"] = "varchar(40)";
$db_varchar40["postgresql"] = "varchar(40)";
$db_varchar40["ms_sql"] = "varchar(40) NULL";
$db_varchar40["interbase"] = "varchar(40)";
$db_varchar40["db2"] = "varchar(40)";

$db_varchar60["mysql"] = "varchar(60)";                           // varchar(50),
$db_varchar60["sqlite"] = "varchar(60)";
$db_varchar60["oracle"] = "varchar2(60)";
$db_varchar60["informix"] = "varchar(60)";
$db_varchar60["postgresql"] = "varchar(60)";
$db_varchar60["ms_sql"] = "varchar(60) NULL";
$db_varchar60["interbase"] = "varchar(60)";
$db_varchar60["db2"] = "varchar(60)";

$db_varchar80["mysql"] = "varchar(80)";                           // varchar(80),
$db_varchar80["sqlite"] = "varchar(80)";
$db_varchar80["oracle"] = "varchar2(80)";
$db_varchar80["informix"] = "varchar(80)";
$db_varchar80["postgresql"] = "varchar(80)";
$db_varchar80["ms_sql"] = "varchar(80) NULL";
$db_varchar80["interbase"] = "varchar(80)";
$db_varchar80["db2"] = "varchar(80)";

$db_varchar100["mysql"] = "varchar(100)";                           // varchar(100),
$db_varchar100["sqlite"] = "varchar(100)";
$db_varchar100["oracle"] = "varchar2(100)";
$db_varchar100["informix"] = "varchar(100)";
$db_varchar100["postgresql"] = "varchar(100)";
$db_varchar100["ms_sql"] = "varchar(100) NULL";
$db_varchar100["interbase"] = "varchar(100)";
$db_varchar100["db2"] = "varchar(100)";

$db_varchar128["mysql"] = "varchar(128)";                         // varchar(128),
$db_varchar128["sqlite"] = "varchar(128)";
$db_varchar128["oracle"] = "varchar2(128)";
$db_varchar128["informix"] = "varchar(128)";
$db_varchar128["postgresql"] = "varchar(128)";
$db_varchar128["ms_sql"] = "varchar(128) NULL";
$db_varchar128["interbase"] = "varchar(128)";
$db_varchar128["db2"] = "varchar(128)";

$db_varchar255["mysql"] = "varchar(255)";                         // varchar(255),
$db_varchar255["sqlite"] = "varchar(255)";
$db_varchar255["oracle"] = "varchar2(255)";
$db_varchar255["informix"] = "varchar(255)";
$db_varchar255["postgresql"] = "varchar(255)";
$db_varchar255["ms_sql"] = "varchar(255) NULL";
$db_varchar255["interbase"] = "varchar(255)";
$db_varchar255["db2"] = "varchar(255)";

$db_float["mysql"] = "float";                         // float,
$db_float["sqlite"] = "float";
$db_float["oracle"] = "float";
$db_float["informix"] = "float";
$db_float["postgresql"] = "float";
$db_float["ms_sql"] = "float";
$db_float["interbase"] = "float";
$db_float["db2"] = "float";
?>
