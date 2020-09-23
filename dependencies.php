<?php
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Slim\Views\Twig_Extension_Debug;
use dbService;
// DIC configuration
$container = $app->getContainer();

// View
$container['view'] = function ($c) 
{
    $view = new Twig("views", array('cache' => 'cache/twig', 'debug' => true));
    // Add extensions
    $view->addExtension(new TwigExtension($c['router'], $c['request']->getUri()));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};

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