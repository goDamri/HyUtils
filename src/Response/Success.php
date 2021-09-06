<?php

namespace Godamri\HyUtils\Response;

use Growinc\Support\ResponseCode;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;

class Success {

    protected $data;
    protected $meta;
    protected $resourceMapper;

    public function __construct($data, $resourceMapper = null)
    {
        $this->data = $data;
        $this->resourceMapper = $resourceMapper;
    }

    public function __invoke()
    {
        $build = [
            'error' => 0,
            'data' => is_object($this->data)?(method_exists($this->data , 'items')?$this->data->items():(method_exists($this->data, 'toArray')?$this->data->toArray():(array)$this->data)):$this->data,
        ];
        $this->meta ? $build['meta'] = $this->meta: false;
        if( $this->resourceMapper ) {
            if (isset($build['data']) && is_array($build['data']) && count($build['data']) > 0 && isset($build['data'][0]) && is_object($build['data'][0])) {
                $build['data'] = (new $this->resourceMapper($build['data']))();
            }
            else {
                $build['data'] = \current((new $this->resourceMapper([(object)$build['data']]))()) ?? null;
            }
        }
        if(is_object($this->data) && method_exists($this->data, 'total')) {
            $build['meta'] = [
                'page' => $this->data->currentPage(),
                'last_page' => $this->data->lastPage(),
                'per_page' => $this->data->perPage(),
                'total' => $this->data->total(),
                'link' => rtrim(self::getRequestInterface()->url(), '?'),
                'prev' => $this->data->previousPageUrl(),
                'next' => $this->data->nextPageUrl(),
            ];
        }

        return self::getResponseInterface()
            ->withHeader('Content-Type', 'application/json')
            ->json($build);
    }

    public static function ok($data, $resourceMapper = null)
    {
        return (new self($data, $resourceMapper))();
    }
    public static function message(?string $message = NULL)
    {
        return self::getResponseInterface()
            ->withHeader('Content-Type', 'application/json')
            ->json($message ? [ 'error' => ResponseCode::SUCCESS, 'message' => $message ] : [ 'error' => ResponseCode::SUCCESS ] );
    }

    static function getResponseInterface(): ResponseInterface
    {
        return ApplicationContext::getContainer()->get(ResponseInterface::class);
    }
    static function getRequestInterface(): RequestInterface
    {
        return ApplicationContext::getContainer()->get(RequestInterface::class);
    }

    public static function paginate($data, $resourceMapper = null)
    {
        return (new self( $resourceMapper ? (new $resourceMapper($data->items()))() : $data->items(), [
            'page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
            'link' => rtrim(self::getRequestInterface()->url(), '?'),
        ]))();
    }
}
