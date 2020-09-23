<?php
use dbService;
// DIC configuration
$container = $app->getContainer();


$container['dbConnService'] = function ($c)
{
    return new ConectorDAO ($c);
};

// db service
$container['TesteDAO'] = function ($c) 
{  
    $dbTesteService = new TesteDAO($c);
    
    return $dbTesteService;
};

// controller
$container['controllers\TesteController'] = function ($c) {
    return new controllers\TesteController( $c['view'], $c['router'], $c['dbTesteService']);
};