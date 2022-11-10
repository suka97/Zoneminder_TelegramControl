<?php
// sshpass -p "mellamoandres" rsync -v -t -r telegram/*.{php,py} pilar_rasp:~/telegram/

include_once("telegram.php");
include_once("zm.php");
include_once("dbSQLs.php");

tel_sendVideo('/mnt/hhd1/Record-2/2022-11-10/72305/72305-video.mp4', 1507660693, 'hola');