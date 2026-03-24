<?php
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$projectDir = rtrim(str_replace('/index.php', '', $scriptName), '/') . '/';
define('IMAGE_PATH', $_SERVER['DOCUMENT_ROOT'] . $projectDir . 'Assets/img/');
