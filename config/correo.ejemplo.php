<?php
/**
 * Configuración del correo que envía los códigos de recuperación de contraseña.
 * Copiar este archivo como config/correo.php y completar los datos.
 * config/correo.php NO se sube a git (tiene la contraseña de la cuenta).
 *
 * Gmail: activar la verificación en 2 pasos de la cuenta y crear una
 * "contraseña de aplicación" en https://myaccount.google.com/apppasswords
 * (16 letras). Esa va en 'password', no la contraseña normal de la cuenta.
 */
return [
    // true = no envía correos: el código se muestra en pantalla, SOLO si se abre
    // el sistema desde el mismo equipo (localhost). En producción debe ser false.
    'modo_prueba' => true,

    'host' => 'smtp.gmail.com',
    'puerto' => 587,
    'usuario' => 'cuenta.del.posgrado@gmail.com',
    'password' => 'contraseña-de-aplicacion',
    'remitente_nombre' => 'Posgrado Odontología - UTO',
];
