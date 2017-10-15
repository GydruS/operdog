<?php

# MAIN BEHAVIOR CONFIGS
define('ALLOW_EXTERNAL_CONFIGS',    FALSE);

# HELPERS
define('DS',      DIRECTORY_SEPARATOR);

# TYPES
define('TP_STRING',                 0);
define('TP_INT',                    1);
define('TP_UINT',                   2);
define('TP_FLOAT',                  3);
define('TP_UFLOAT',                 4);
define('TP_BOOL',                   5);
define('TP_ARRAY',                  6);
define('TP_CHAR',                   7);
define('TP_ENUM',                   8);

define('DEFAULT_MODULES_PATH', 'modules/');
define('DEFAULT_TEMPLATES_PATH', 'modules/');

define('MODULE_TYPE_PP', 'PP');
define('MODULE_TYPE_OOP', 'OOP');

define('MODULE_OUTPUT_NORMAL', 'MODULE_OUTPUT_NORMAL');
define('MODULE_OUTPUT_NONE', 'MODULE_OUTPUT_NONE');
define('MODULE_OUTPUT_EXCLUSIVE', 'MODULE_OUTPUT_EXCLUSIVE');

# Error codes
define('ERR_UNABLE_TO_LOAD_MODULE',     10);
define('ERR_MODULE_IS_NULL',            11);

