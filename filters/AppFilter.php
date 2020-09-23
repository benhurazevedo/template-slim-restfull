<?php
namespace filters;
class Appfilter
{
    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        #$response->getBody()->write('BEFORE');
        #$response = $next($request, $response);
        #$response->getBody()->write('AFTER');
		
		
		if(!isset($_SESSION['usuario'])){
			#session_start();
			$_SESSION['usuario'] = array('nome' => 'Benhur', 'perfil' => 'ADMIN');
		}
		
		#$session = new \SlimSession\Helper;
		#
		#if(!$session.exists('usuario'))
		#{
		#	$session['usuario'] = array('nome' => 'Benhur', 'perfil' => 'USER');
		#}
		
		$response = $next($request, $response);
        return $response;
    }
}
