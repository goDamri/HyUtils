<?php

namespace App\Response;

use Growinc\Support\ResponseCode;
use Hyperf\HttpServer\Contract\ResponseInterface;

class Success {
    
    protected $data;
    protected $meta;
    
    public function __construct($data, $meta = null)
    {
        $this->data = $data;
        $this->meta = $meta;
    }
    
    public function __invoke()
    {
        $build = [
            'error' => 0,
            'data' => $this->data,
        ];
        $this->meta ? $build['meta'] = $this->meta: false;
        
        return self::getResponseInterface()
            ->withHeader('Content-Type', 'application/json')
            ->json($build);
    }
    
    public static function ok($data)
    {
        return (new self($data))();
    }
    public static function message(string $message)
    {
        return self::getResponseInterface()
            ->withHeader('Content-Type', 'application/json')
            ->json([
                'error' => ResponseCode::SUCCESS,
                'message' => $message,
            ]);
    }
    
    static function getResponseInterface(): ResponseInterface
    {
        return container()->get(ResponseInterface::class);
    }
    
    public static function paginate($data, $resourceMapper = null)
    {
        return (new self( $resourceMapper ? (new $resourceMapper($data->items()))() : $data->items(), [
            'page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
            'link' => rtrim(request()->url(), '?'),
        ]))();
    }
}
