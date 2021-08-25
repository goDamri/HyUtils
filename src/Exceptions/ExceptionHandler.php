<?php

namespace Godamri\HyUtils\Exceptions;

use Godamri\HyUtils\Exceptions\InterruptException;
use Godamri\HyUtils\Exceptions\ValidationException;
use Throwable;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\ExceptionHandler\ExceptionHandler as Handler;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpMessage\Exception\MethodNotAllowedHttpException;
use Hyperf\HttpMessage\Exception\ServerErrorHttpException;

class ExceptionHandler extends Handler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->log($throwable);
        $error = $this->convertExceptionToArray($throwable);

        return $response
            ->withHeader('Server', 'Hyperf')
            ->withStatus(200)
            ->withAddedHeader('content-type', 'application/json')
            ->withBody(new SwooleStream(Json::encode($error)));
    }

    /**
     * Undocumented function
     *
     * @param Throwable $throwable
     * @return boolean
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Throwable  $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return [
                'error' => $e->getCode(),
                'data' => $e->getData(),
            ];
        }
        if ($e instanceof InterruptException) {
            return [
                'error' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
        return config('debug', false) ? [
            'error'     => $e instanceof InterruptException ? $e->getCode() : 3000,
            'message'   => $e->getMessage(),
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => explode("\n", $e->getTraceAsString())
        ] : [
            'error'     => $e instanceof InterruptException ? $e->getCode() : 3000,
            'message'   => $this->isHttpException($e) ? $e->getMessage() : 'Server Error'
        ];
    }

    protected function isHttpException(Throwable $e)
    {
        return $e instanceof NotFoundHttpException || $e instanceof MethodNotAllowedHttpException || $e instanceof ServerErrorHttpException;
    }

    protected function log(Throwable $e) : void
    {
        if ( ! $e instanceof InterruptException || ! $e instanceof ValidationException)
            return;

        $this->logger->error(
            sprintf('%s[%s] in %s',
                $e->getMessage(),
                $e->getLine(), $e->getFile()
            )
        );

        $this->logger->error($e->getTraceAsString());
    }
}
