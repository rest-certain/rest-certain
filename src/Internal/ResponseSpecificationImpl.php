<?php

/**
 * This file is part of REST Certain
 *
 * REST Certain is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * REST Certain is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with REST Certain. If not, see <https://www.gnu.org/licenses/>.
 *
 * @copyright Copyright (c) REST Certain Contributors <https://rest-certain.dev>
 * @license https://opensource.org/license/lgpl-3-0/ GNU Lesser General Public License version 3 or later
 */

declare(strict_types=1);

namespace RestCertain\Internal;

use LogicException;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use RestCertain\Http\Header;
use RestCertain\Response\Response;
use RestCertain\Specification\RequestSender;
use RestCertain\Specification\RequestSpecification;
use RestCertain\Specification\ResponseSpecification;
use Stringable;

use function is_array;

/**
 * @internal
 */
final class ResponseSpecificationImpl implements ResponseSpecification
{
    private RequestSpecification $requestSpecification;

    public function __construct(
        private readonly Response $response,
        ?RequestSpecification $requestSpecification = null,
    ) {
        if ($requestSpecification !== null) {
            $this->setRequestSpecification($requestSpecification);
        }
    }

    #[Override] public function and(): static
    {
        return $this;
    }

    #[Override] public function body(
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        $this->evaluateExpectations(
            $this->response->getBody()->asString(),
            [$expectation, ...$additionalExpectations],
        );

        return $this;
    }

    #[Override] public function bodyPath(
        string $path,
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function contentType(
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        $this->evaluateExpectations(
            $this->response->getHeaderLine(Header::CONTENT_TYPE),
            [$expectation, ...$additionalExpectations],
        );

        return $this;
    }

    #[Override] public function cookie(
        string $name,
        Constraint | Stringable | string | null $expectation = null,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        if ($expectation === null) {
            if ($this->response->getCookie($name) === null) {
                throw new ExpectationFailedException('Failed asserting that cookie "' . $name . '" is set.');
            }

            return $this;
        }

        $this->evaluateExpectations(
            $this->response->getCookie($name),
            [$expectation, ...$additionalExpectations],
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function cookies(array $expectations): static
    {
        foreach ($expectations as $name => $expectation) {
            if (!is_array($expectation)) {
                $expectation = [$expectation];
            }
            $this->cookie($name, ...$expectation);
        }

        return $this;
    }

    #[Override] public function expect(): static
    {
        return $this;
    }

    #[Override] public function given(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    #[Override] public function header(
        string $name,
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        $this->evaluateExpectations(
            $this->response->getHeaderLine($name),
            [$expectation, ...$additionalExpectations],
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function headers(array $expectations): static
    {
        foreach ($expectations as $name => $expectation) {
            if (!is_array($expectation)) {
                $expectation = [$expectation];
            }
            $this->header($name, ...$expectation);
        }

        return $this;
    }

    #[Override] public function request(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    #[Override] public function response(): static
    {
        return $this;
    }

    #[Override] public function setRequestSpecification(RequestSpecification $requestSpecification): static
    {
        $this->requestSpecification = $requestSpecification;

        return $this;
    }

    #[Override] public function statusCode(
        Constraint | int $expectation,
        Constraint | int ...$additionalExpectations,
    ): static {
        $this->evaluateExpectations(
            $this->response->getStatusCode(),
            [$expectation, ...$additionalExpectations],
        );

        return $this;
    }

    #[Override] public function statusLine(
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        $this->evaluateExpectations(
            $this->response->getStatusLine(),
            [$expectation, ...$additionalExpectations],
        );

        return $this;
    }

    #[Override] public function that(): static
    {
        return $this;
    }

    #[Override] public function then(): static
    {
        return $this;
    }

    #[Override] public function time(Constraint $expectation, Constraint ...$additionalExpectations): static
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function when(): RequestSender
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function with(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    /**
     * @param array<Constraint | Stringable | int | string> $expectations
     */
    private function evaluateExpectations(mixed $value, array $expectations): void
    {
        foreach ($expectations as $expectation) {
            if (!$expectation instanceof Constraint) {
                if ($expectation instanceof Stringable) {
                    $expectation = (string) $expectation;
                }

                $expectation = new IsEqual($expectation);
            }

            $expectation->evaluate($value);
        }
    }
}
