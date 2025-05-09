<?php

declare(strict_types=1);

namespace RestCertain\Test\Behavior;

use PHPUnit\Framework\TestCase;
use RestCertain\Test\MockWebServer;

use function RestCertain\Hamcrest\equalTo;
use function RestCertain\Hamcrest\greaterThan;
use function RestCertain\Hamcrest\hasItems;
use function RestCertain\Hamcrest\is;
use function RestCertain\get;
use function RestCertain\when;

class RestAssuredExamplesTest extends TestCase
{
    use MockWebServer;

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

        $this->server()->addRoute(method: 'GET', uri: '/lotto', body: $lottoResponse);

        get('/lotto')->then()
            ->assertThat()->bodyPath('lotto.lottoId', is(equalTo(5)))
            ->and()->bodyPath('lotto.winners[*].winnerId', hasItems(54, 23));
    }

    public function testPriceExample(): void
    {
        $priceResponse = '{"price":12.12}';
        $this->server()->addRoute(method: 'GET', uri: '/price', body: $priceResponse);

        when()->get('/price')
            ->then()->bodyPath('price', is(12.12));
    }

    public function testAnonymousJsonRootValidation(): void
    {
        $this->server()->addRoute(method: 'GET', uri: '/json', body: '[1, 2, 3]');

        when()->get('/json')
            // Using a JSONPath expression.
            ->then()->bodyPath('$', [[1, 2, 3]])
            // Using a JMESPath expression.
            ->and()->bodyPath('[]', [1, 2, 3]);
    }

    public function testStoreExample(): void
    {
        $storeResponse = <<<'JSON'
            {
               "store":{
                  "book":[
                     {
                        "author":"Nigel Rees",
                        "category":"reference",
                        "price":8.95,
                        "title":"Sayings of the Century"
                     },
                     {
                        "author":"Evelyn Waugh",
                        "category":"fiction",
                        "price":12.99,
                        "title":"Sword of Honour"
                     },
                     {
                        "author":"Herman Melville",
                        "category":"fiction",
                        "isbn":"0-5.5.11311-3",
                        "price":8.99,
                        "title":"Moby Dick"
                     },
                     {
                        "author":"J. R. R. Tolkien",
                        "category":"fiction",
                        "isbn":"0-395-19395-8",
                        "price":22.99,
                        "title":"The Lord of the Rings"
                     }
                  ]
               }
            }
            JSON;

        $this->server()->addRoute(method: 'GET', uri: '/store', body: $storeResponse);

        when()->get('/store')
            ->then()->bodyPath('store.book[?price < `10`].title', hasItems('Sayings of the Century', 'Moby Dick'))
            ->and()->bodyPath('sum(map(&length(@), store.book[].author))', is(greaterThan(50)));
    }
}
