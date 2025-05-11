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

use Override;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\IsEqualCanonicalizing;
use PHPUnit\Framework\Constraint\IsFalse;
use PHPUnit\Framework\Constraint\IsNull;
use PHPUnit\Framework\Constraint\IsTrue;
use RestCertain\Exception\PathResolutionFailure;
use RestCertain\Hamcrest\Constraint\AdditionallyDescribedConstraint;
use RestCertain\Http\Header;
use RestCertain\Http\HttpExchangeFormatter;
use RestCertain\Request\Sender;
use RestCertain\Response\Response;
use RestCertain\Specification\RequestSpecification;
use RestCertain\Specification\ResponseSpecification;
use Stringable;
use stdClass;

use function is_array;
use function is_float;
use function is_int;
use function is_string;

/**
 * @internal This class is not intended for direct use outside of Rest Certain.
 */
final class ResponseExpectations implements ResponseSpecification
{
    private HttpExchangeFormatter $httpExchangeFormatter;
    private RequestSpecification $requestSpecification;

    public function __construct(
        private readonly Response $response,
        ?RequestSpecification $requestSpecification = null,
    ) {
        if ($requestSpecification !== null) {
            $this->setRequestSpecification($requestSpecification);
        }

        $this->httpExchangeFormatter = new HttpExchangeFormatter();
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
            'Response body does not match expectations.',
        );

        return $this;
    }

    #[Override] public function bodyPath(
        string $path,
        Constraint | Stringable | stdClass | array | bool | float | int | string | null $expectation,
        Constraint | Stringable | stdClass | array | bool | float | int | string | null ...$additionalExpectations,
    ): static {
        try {
            $value = $this->response->path($path);
        } catch (PathResolutionFailure $exception) {
            Assert::fail(
                'Failed asserting that response body path "' . $path . '" exists: ' . $exception->getMessage(),
            );
        }

        $this->evaluateExpectations(
            $value,
            [$expectation, ...$additionalExpectations],
            'Response body path "' . $path . '" does not match expectations.',
        );

        return $this;
    }

    #[Override] public function contentType(
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        $this->evaluateExpectations(
            $this->response->getHeaderLine(Header::CONTENT_TYPE),
            [$expectation, ...$additionalExpectations],
            'Response content type does not match expectations.',
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
                Assert::fail('Failed asserting that cookie "' . $name . '" is set.');
            }

            return $this;
        }

        $this->evaluateExpectations(
            $this->response->getCookie($name),
            [$expectation, ...$additionalExpectations],
            'Response cookie "' . $name . '" does not match expectations.',
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
            'Response header "' . $name . '" does not match expectations.',
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
            'Response status code does not match expectations.',
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
            'Response status line does not match expectations.',
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
        $this->evaluateExpectations(
            $this->response->getTime(),
            [$expectation, ...$additionalExpectations],
            'Response time does not match expectations.',
        );

        return $this;
    }

    #[Override] public function when(): Sender
    {
        return $this->requestSpecification;
    }

    #[Override] public function with(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    /**
     * @param array<Constraint | Stringable | stdClass | array<mixed> | bool | float | int | string | null> $expectations
     */
    private function evaluateExpectations(mixed $value, array $expectations, string $message): void
    {
        foreach ($expectations as $expectation) {
            $expectation = match (true) {
                $expectation === false => new IsFalse(),
                $expectation === null => new IsNull(),
                $expectation === true => new IsTrue(),
                $expectation instanceof Constraint => $expectation,
                $expectation instanceof Stringable, is_string($expectation) => new IsEqual((string) $expectation),
                $expectation instanceof stdClass, is_float($expectation),
                    is_int($expectation) => new IsEqual($expectation),
                is_array($expectation) => new IsEqualCanonicalizing($expectation),
            };

            Assert::assertThat($value, $this->describeHttpExchange($expectation), $message);
        }
    }

    private function describeHttpExchange(Constraint $constraint): Constraint
    {
        if (!$this->response instanceof HttpResponse) {
            return $constraint;
        }

        $formatted = $this->httpExchangeFormatter->format(
            $this->response->getPsrRequest(),
            $this->response->getPsrResponse(),
        );

        return new AdditionallyDescribedConstraint("\n" . $formatted, $constraint);
    }
}
