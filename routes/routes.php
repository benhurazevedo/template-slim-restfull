<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

#$app->get('/index/{id}',function(Request $req, Response $res, array $args ){
#	#$res->getBody()->write('Oi ' . $_SESSION['usuario']['nome'].' sua id é '. $req->getAttribute('id'));
#	$res->getBody()->write('Oi ' . $_SESSION['usuario']['nome'].' sua id é '. $args['id']);
#	return $res;
#})->add(new filters\AdminFilter)->add(new filters\AppFilter);

$app->get('/index/{id}','controllers\TesteController:index')
	->add(new filters\AdminFilter)
	->add(new filters\AppFilter);
	
$app->get('/exibirnome',function(Request $req, Response $res, array $args ){
	$res->getBody()->write('nome');
	return $res;
});
?>