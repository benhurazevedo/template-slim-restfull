<?php

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
		,'DSN' => 'sqlsrv:SERVER=DESKTOP-9K0552C\SQLEXPRESS;DATABASE=DB_PRODUCAO_TEMATICA'
		,'DATABASE_USER' => 'sa'
		,'DB_PASSWORD' => 'sa'
    ]
]);
?>