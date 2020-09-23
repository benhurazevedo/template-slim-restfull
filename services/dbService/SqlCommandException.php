<?php 
namespace dbService;

class SqlCommandException extends DbServiceException
{
    public function __construct($message, $code = 0, Exception $previous = null) 
    {
        // some code
    
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}
?>