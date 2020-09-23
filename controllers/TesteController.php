<?php
namespace controllers;

use Slim\Views\Twig;
use Slim\Router;

final class TesteController
{
	private $router;
	
	public function __construct(Twig $view, Router $router, $dbService)
    {
        $this->view = $view;
        $this->router = $router;
		$this->dbService = $dbService;
    }
	public function index($req, $res, array $args)
	{
		#$res->getBody()->write('Oi ' . $_SESSION['usuario']['nome'].' sua id é '. $req->getAttribute('id'));
		#$res->getBody()->write('Oi ' . $_SESSION['usuario']['nome'].' sua id é '. $args['id']);
		#return $res;
		return $this->view->render($res, 'teste/teste.twig', [
            'usuario' => $_SESSION['usuario']['nome']
			,'id' => $args['id']
			,'linguagens' => $this->dbService->list()
        ]);
	}
}