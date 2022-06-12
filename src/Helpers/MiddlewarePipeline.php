<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class MiddlewarePipeline
{
    /**
     * @var Pipeline
     */
    protected Pipeline $requestPipeline;

    /**
     * @var Pipeline
     */
    protected Pipeline $responsePipeline;

    /**
     * Instantiate the pipelines
     */
    public function __construct()
    {
        $this->requestPipeline = new Pipeline();
        $this->responsePipeline = new Pipeline();
    }

    /**
     * @param callable $closure
     * @param bool $highPriority
     * @return $this
     */
    public function addRequestPipe(callable $closure, bool $highPriority = false): self
    {
        $this->requestPipeline = $this->requestPipeline->pipe(function (PendingSaloonRequest $request) use ($closure) {
            $result = $closure($request);

            return $result instanceof PendingSaloonRequest ? $result : $request;
        }, $highPriority);

        return $this;
    }

    /**
     * @param callable $closure
     * @param bool $highPriority
     * @return $this
     */
    public function addResponsePipe(callable $closure, bool $highPriority = false): self
    {
        $this->responsePipeline = $this->responsePipeline->pipe(function (SaloonResponse $response) use ($closure) {
            $result = $closure($response);

            return $result instanceof SaloonResponse ? $result : $response;
        }, $highPriority);

        return $this;
    }

    /**
     * Process the request pipeline.
     *
     * @param PendingSaloonRequest $request
     * @return PendingSaloonRequest
     */
    public function executeRequestPipeline(PendingSaloonRequest $request): PendingSaloonRequest
    {
        $this->requestPipeline->process($request);

        return $request;
    }

    /**
     * Process the response pipeline.
     *
     * @param SaloonResponse $response
     * @return SaloonResponse
     */
    public function executeResponsePipeline(SaloonResponse $response): SaloonResponse
    {
        $this->responsePipeline->process($response);

        return $response;
    }

    /**
     * Merge in another middleware pipeline.
     *
     * @param MiddlewarePipeline $middlewarePipeline
     * @return $this
     */
    public function merge(MiddlewarePipeline $middlewarePipeline): self
    {
        $requestPipes = array_merge(
            $this->getRequestPipeline()->getPipes(),
            $middlewarePipeline->getRequestPipeline()->getPipes()
        );

        $responsePipes = array_merge(
            $this->getResponsePipeline()->getPipes(),
            $middlewarePipeline->getResponsePipeline()->getPipes()
        );

        $this->requestPipeline->setPipes($requestPipes);
        $this->responsePipeline->setPipes($responsePipes);

        return $this;
    }

    /**
     * @return Pipeline
     */
    public function getRequestPipeline(): Pipeline
    {
        return $this->requestPipeline;
    }

    /**
     * @return Pipeline
     */
    public function getResponsePipeline(): Pipeline
    {
        return $this->responsePipeline;
    }
}