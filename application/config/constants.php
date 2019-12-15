<?php
defined('APP_ENTRY_PASS') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/*
|--------------------------------------------------------------------------
| DBMS Constants
|--------------------------------------------------------------------------
|
*/
defined('PGSQL_BOOL_TRUE_FROM_SERVER')   OR define('PGSQL_BOOL_TRUE_FROM_SERVER', 't');
defined('PGSQL_BOOL_FALSE_FROM_SERVER')  OR define('PGSQL_BOOL_FALSE_FROM_SERVER', 'f');
defined('PGSQL_BOOL_TRUE_TO_SERVER')     OR define('PGSQL_BOOL_TRUE_TO_SERVER', 'TRUE');
defined('PGSQL_BOOL_FALSE_TO_SERVER')    OR define('PGSQL_BOOL_FALSE_TO_SERVER', 'FALSE');
defined('PGSQL_TIMESTAMPTZ_FORMAT')      OR define('PGSQL_TIMESTAMPTZ_FORMAT', 'Y-m-d H:i:s.uP');
defined('PGSQL_TIMESTAMP_FORMAT')        OR define('PGSQL_TIMESTAMP_FORMAT', 'Y-m-d H:i:s.u');
defined('PGSQL_TIMETZ_FORMAT')           OR define('PGSQL_TIMETZ_FORMAT', 'H:i:s.uP');
defined('PGSQL_TIME_FORMAT')             OR define('PGSQL_TIME_FORMAT', 'H:i:s.u');
defined('PGSQL_DATE_FORMAT')             OR define('PGSQL_DATE_FORMAT', 'Y-m-d');
defined('PGSQL_INTERVAL_FORMAT')         OR define('PGSQL_INTERVAL_FORMAT', '%y years %m months %d days %h hours %i minutes %s seconds');

defined('MYSQL_BOOL_TRUE_FROM_SERVER')   OR define('MYSQL_BOOL_TRUE_FROM_SERVER', '1');
defined('MYSQL_BOOL_FALSE_FROM_SERVER')  OR define('MYSQL_BOOL_FALSE_FROM_SERVER', '0');
defined('MYSQL_BOOL_TRUE_TO_SERVER')     OR define('MYSQL_BOOL_TRUE_TO_SERVER', '1');
defined('MYSQL_BOOL_FALSE_TO_SERVER')    OR define('MYSQL_BOOL_FALSE_TO_SERVER', '0');
defined('MYSQL_DATETIME_FORMAT')         OR define('MYSQL_DATETIME_FORMAT', 'Y-m-d H:i:s.u');
defined('MYSQL_TIMESTAMP_FORMAT')        OR define('MYSQL_TIMESTAMP_FORMAT', 'Y-m-d H:i:s.u');
defined('MYSQL_DATE_FORMAT')             OR define('MYSQL_DATE_FORMAT', 'Y-m-d');
defined('MYSQL_YEAR_FORMAT')             OR define('MYSQL_YEAR_FORMAT', 'Y');
defined('MYSQL_TIME_FORMAT')             OR define('MYSQL_TIME_FORMAT', 'H:i:s.u');
defined('MYSQL_INTERVAL_FORMAT')         OR define('MYSQL_INTERVAL_FORMAT', '%h:%i:%s');

/*
|--------------------------------------------------------------------------
| Artefact Constants
|--------------------------------------------------------------------------
|
*/
defined('ARTEFACT_START_TABLE_ALIAS_NUMBER')  OR define('ARTEFACT_START_TABLE_ALIAS_NUMBER', 1);
defined('ARTEFACT_START_MODEL_NUMBER')        OR define('ARTEFACT_START_MODEL_NUMBER', 1);
