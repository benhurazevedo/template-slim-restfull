<?php

require 'vendor/autoload.php';

require 'config.php';

#session 
#$app->add(new \Slim\Middleware\Session([
#  'autorefresh' => true
#]));

#dempendencies
require 'dependencies.php';

#routes
require 'routes/routes.php';

$app->run();
?>
