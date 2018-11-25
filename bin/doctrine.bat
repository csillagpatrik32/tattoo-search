@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../../vendor/tattoo-search/vendor/doctrine/orm/bin/doctrine
php "%BIN_TARGET%" %*
