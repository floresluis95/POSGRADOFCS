// @ts-check
import { test as setup } from '@playwright/test';
import { execSync } from 'node:child_process';

// Antes de las pruebas del proceso: copia fresca de "proyecto" en "proyecto_pruebas".
// Así cada corrida parte del mismo estado y los datos que crean las pruebas no se acumulan.
const MYSQL = 'C:/xampp/mysql/bin';

setup('refrescar la base proyecto_pruebas', async () => {
  setup.setTimeout(120_000);
  execSync(`"${MYSQL}/mysql.exe" -u root -e "DROP DATABASE IF EXISTS proyecto_pruebas; CREATE DATABASE proyecto_pruebas CHARACTER SET utf8mb4;"`);
  execSync(`"${MYSQL}/mysqldump.exe" -u root --routines --triggers proyecto | "${MYSQL}/mysql.exe" -u root proyecto_pruebas`, { shell: 'cmd.exe' });
});
