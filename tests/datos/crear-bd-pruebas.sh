#!/usr/bin/env bash
# Crea (o refresca) la base proyecto_pruebas como copia de proyecto.
# La usan las pruebas PHPUnit que escriben datos (p. ej. PlanGrupalDescuentosTest),
# para no tocar nunca la base real. Ejecutar desde Git Bash:
#   bash tests/datos/crear-bd-pruebas.sh
set -e
MYSQL_BIN="${MYSQL_BIN:-/c/xampp/mysql/bin}"
"$MYSQL_BIN/mysql.exe" -u root -e "DROP DATABASE IF EXISTS proyecto_pruebas; CREATE DATABASE proyecto_pruebas CHARACTER SET utf8mb4;"
"$MYSQL_BIN/mysqldump.exe" -u root --routines --triggers proyecto | "$MYSQL_BIN/mysql.exe" -u root proyecto_pruebas
echo "Base proyecto_pruebas lista."
