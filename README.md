<h1 align="center">REST Certain</h1>

<p align="center">
    <strong>PHP DSL for easy testing of REST services, with a nod to the Java DSL, REST Assured</strong>
</p>

<p align="center">
    <a href="https://github.com/rest-certain/rest-certain"><img src="https://img.shields.io/badge/source-rest--certain/rest--certain-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://packagist.org/packages/rest-certain/rest-certain"><img src="https://img.shields.io/packagist/v/rest-certain/rest-certain.svg?style=flat-square&label=release" alt="Download Package"></a>
    <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/rest-certain/rest-certain.svg?style=flat-square&colorB=%238892BF" alt="PHP Programming Language"></a>
    <a href="https://github.com/rest-certain/rest-certain/blob/main/NOTICE"><img src="https://img.shields.io/packagist/l/rest-certain/rest-certain.svg?style=flat-square&colorB=darkcyan" alt="Read License"></a>
    <a href="https://github.com/rest-certain/rest-certain/actions/workflows/continuous-integration.yml"><img src="https://img.shields.io/github/actions/workflow/status/rest-certain/rest-certain/continuous-integration.yml?branch=main&style=flat-square&logo=github" alt="Build Status"></a>
    <a href="https://codecov.io/gh/rest-certain/rest-certain"><img src="https://img.shields.io/codecov/c/gh/rest-certain/rest-certain?label=codecov&logo=codecov&style=flat-square" alt="Codecov Code Coverage"></a>
</p>

## About

REST Certain is a port of [REST Assured](https://github.com/rest-assured/rest-assured)
to the PHP programming language. It provides a DSL that aims to simplify and ease
the testing of REST services.

This project adheres to a [code of conduct](CODE_OF_CONDUCT.md).
By participating in this project and its community, you are expected to
uphold this code.

## Installation

Install this package as a development dependency using [Composer](https://getcomposer.org).

``` bash
composer require --dev rest-certain/rest-certain
```

## Usage

Borrowing from the REST Assured project's examples, here's an example of how to
use REST Certain to make a `GET` request and validate a JSON response.

Given the following JSON response body:

``` json
{
  "lotto":{
    "lottoId": 5,
    "winning-numbers": [2, 45, 34, 23, 7, 5, 3],
    "winners":[{
      "winnerId": 23,
      "numbers": [2, 45, 34, 23, 3, 5]
    },{
      "winnerId": 54,
      "numbers": [52, 3, 12, 11, 18, 22]
    }]
  }
}
```

We can use [JMESPath query language](https://jmespath.org) syntax to assert that
`lottoId` is equal to `5`:

``` php
get('/lotto')->then()->assertThat()->bodyPath('lotto.lottoId', is(equalTo(5)));
```

We can also verify all the winner IDs:

``` php
get('/lotto')->then()->assertThat()
    ->bodyPath('lotto.winners[*].winnerId', hasItems(54, 23));
```

> [!TIP]
> REST Certain supports both [JMESPath](https://jmespath.org) and
> [JSONPath](https://www.rfc-editor.org/rfc/rfc9535). If the path query begins
> with a dollar symbol (`$`), REST Certain assumes the query syntax is JSONPath.
> Otherwise, it assume the query syntax is JMESPath.

We can also get a lot more complex and expressive with the HTTP requests and
assertions we make. For example:

```php
given()
    ->accept('application/json')
    ->queryParam('foo', 'bar')
    ->and()->body(['name' => 'Something Cool'])
->when()
    ->put('/something/{id}', ['id' => 123])
->then()
    ->statusCode(200)
    ->and()->header('content-type', 'application/json')
    ->and()->cookie('baz', 'qux')
    ->and()->bodyPath('id', 123);
```

REST Certain supports any HTTP method but has explicit support for `POST`, `GET`,
`PUT`, `DELETE`, `OPTIONS`, `PATCH`, and `HEAD` and includes specifying and
validating parameters, headers, cookies, and body easily.

## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the
contribution of external security researchers. If you believe you've found a
security issue in software that is maintained in this repository, please read
[SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.

## Copyright and License

REST Certain is copyright © [REST Certain Contributors](https://rest-certain.dev)
and licensed for use under the terms of the GNU Lesser General Public License
(LGPL-3.0-or-later) as published by the Free Software Foundation. Please see
[COPYING.LESSER](COPYING.LESSER), [COPYING](COPYING), and [NOTICE](NOTICE) for
more information.


