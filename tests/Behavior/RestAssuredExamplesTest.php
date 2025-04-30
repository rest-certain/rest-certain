<?php

declare(strict_types=1);

namespace Behavior;

use RestCertain\Test\Behavior\BehaviorTestCase;

use function RestCertain\get;
use function RestCertain\when;

class RestAssuredExamplesTest extends BehaviorTestCase
{
    public function testLottoExample(): void
    {
        $lottoResponse = <<<'JSON'
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
            JSON;

        $this->bypass->addRoute(method: 'GET', uri: '/lotto', body: $lottoResponse);

        get('/lotto')
            ->then()->bodyPath('lotto.lottoId', 5)
            ->and()->bodyPath('lotto.winners[*].winnerId', [54, 23]);
    }

    public function testPriceExample(): void
    {
        $priceResponse = '{"price":12.12}';
        $this->bypass->addRoute(method: 'GET', uri: '/price', body: $priceResponse);

        when()->get('/price')
            ->then()->bodyPath('price', 12.12);
    }

    public function testAnonymousJsonRootValidation(): void
    {
        $this->bypass->addRoute(method: 'GET', uri: '/json', body: '[1, 2, 3]');

        when()->get('/json')
            // Using a JSONPath expression.
            ->then()->bodyPath('$', [[1, 2, 3]])
            // Using a JMESPath expression.
            ->and()->bodyPath('[]', [1, 2, 3]);
    }
}
