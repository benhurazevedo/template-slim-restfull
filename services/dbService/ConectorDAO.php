<?php 
namespace dbService;
use \PDO;
use \Slim\Container;

class ConectorDAO
{
	private $c;
	function __construct(Container $c)
	{
		$this->c = $c;		
	}
	public function getConn()
	{
		global $conn;
		if ($conn == null) 
		{
			try{
				$conn = new PDO($this->c['DSN'], $this->c['DATABASE_USER'], $this->c['DB_PASSWORD']);
				$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			} catch (\Exception $e)
			{
				throw new DbConnException();
			}
		}
		return $conn;
	}
}
?>