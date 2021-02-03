<?php
namespace Godamri\HyUtils\Exception;

use Growinc\Support\ResponseCode;

class ValidationException extends \Exception {
    
    public $data;
    public function __construct($data=[], int $code = ResponseCode::FORM_ERROR)
    {
        parent::__construct('Validation failed', $code, null);
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
}
