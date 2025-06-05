.. _usage:

Usage
=====

.. _installation:

Installation
------------

To use REST Certain, first require it as a development dependency using Composer:

.. code-block:: console

   composer require --dev rest-certain/rest-certain

Introduction
------------

Borrowing from the REST Assured project's examples, here's an example of how to use REST Certain to make a ``GET``
request and validate a JSON response.

Given the following JSON response body:

.. code-block:: json

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

We can use `JMESPath query language <https://jmespath.org>`_ syntax to assert that ``lottoId`` is equal to ``5``:

.. code-block:: php

   get('/lotto')->then()->assertThat()->path('lotto.lottoId', is(equalTo(5)));

We can also verify all the winner IDs:

.. code-block:: php

   get('/lotto')->then()->assertThat()
       ->path('lotto.winners[*].winnerId', hasItems(54, 23));

.. tip::

   REST Certain supports both `JMESPath <https://jmespath.org>`_ and `JSONPath <https://www.rfc-editor.org/rfc/rfc9535>`_.
   If the path query begins with a dollar symbol (``$``), REST Certain assumes the query syntax is JSONPath. Otherwise,
   it assumes the query syntax is JMESPath.

We can also get a lot more complex and expressive with the HTTP requests and assertions we make. For example:

.. code-block:: php

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
       ->and()->path('id', 123);

REST Certain supports any HTTP method but has explicit support for ``POST``, ``GET``, ``PUT``, ``DELETE``, ``OPTIONS``,
``PATCH``, and ``HEAD`` and includes specifying and validating parameters, headers, cookies, and body easily.
