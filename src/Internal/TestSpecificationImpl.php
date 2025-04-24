<?php

declare(strict_types=1);

namespace RestCertain\Internal;

use Override;
use Psr\Http\Message\UriInterface;
use RestCertain\Response\Response;
use RestCertain\Specification\RequestSender;
use RestCertain\Specification\RequestSpecification;
use RestCertain\Specification\ResponseSpecification;
use Stringable;

/**
 * @internal
 */
final readonly class TestSpecificationImpl implements RequestSender
{
    public function __construct(
        public RequestSpecification $requestSpecification,
        public ResponseSpecification $responseSpecification,
    ) {
        $this->requestSpecification->setResponseSpecification($this->responseSpecification);
        $this->responseSpecification->setRequestSpecification($this->requestSpecification);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function delete(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return $this->requestSpecification->delete($path, $pathParams);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function get(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return $this->requestSpecification->get($path, $pathParams);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function head(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return $this->requestSpecification->head($path, $pathParams);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function options(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return $this->requestSpecification->options($path, $pathParams);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function patch(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return $this->requestSpecification->patch($path, $pathParams);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function post(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return $this->requestSpecification->post($path, $pathParams);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function put(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return $this->requestSpecification->put($path, $pathParams);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function request(
        Stringable | string $method,
        Stringable | UriInterface | string $path,
        array $pathParams = [],
    ): Response {
        return $this->requestSpecification->request($method, $path, $pathParams);
    }
}
