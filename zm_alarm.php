<?php
include("zm.php");

if ( count($argv) != 2 ) {
    echo("Wrong Usage\n");
    return;
}

$zm_token = zm_getToken();
switch($argv[1]) {
    case 'activar':
        zm_changeMonitorStatus($zm_token, 'Modect');
        echo("Alarma Activada\n");
        break;
    case 'desactivar':
        zm_changeMonitorStatus($zm_token, 'Monitor');
        echo("Alarma Desactivada\n");
        break;
    default:
        echo("Comando Invalido\n");
        break;
}