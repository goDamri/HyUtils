<?php
namespace Godamri\HyUtils\Exception;

use Growinc\Support\ResponseCode;
use Throwable;

class InterruptException extends \Exception {
    
    public function __construct($message='', int $code = ResponseCode::SERVER_ERROR, Throwable $previous=null)
    {
        parent::__construct($message, $code, $previous);
    }
}
