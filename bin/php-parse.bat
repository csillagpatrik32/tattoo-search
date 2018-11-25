@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../../vendor/tattoo-search/vendor/nikic/php-parser/bin/php-parse
php "%BIN_TARGET%" %*
