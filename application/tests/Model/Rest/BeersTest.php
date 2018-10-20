<?php

namespace Model\Rest;

use \PHPUnit\Framework\TestCase;

defined('SYSPATH') or die('No direct access allowed.');

/**
 * test Model_Rest_Beers class
 *
 * @author   dariusz daniec
 */
class BeersTest extends TestCase
{
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new \Model_Rest_Beers();
    }

    public function dpVerifyFilters()
    {
        return [
            'only bwr is given' => [
                'params' => [
                    'bwr' => '1',
                ],
                'expected' => [
                    'id' => NULL,
                    'bwr' => 1,
                    'name__li' => NULL,
                    'price__from' => NULL,
                    'price__to' => NULL,
                    'country_code' => NULL,
                    'type' => NULL,
                    'limit' => 10,
                    'offset' => 0
                ]
            ],
            'all params are given' => [
                'params' => [
                    'bwr' => '1',
                    'id' => '2',
                    'bwr' => '1',
                    'name__li' => 'test',
                    'price__from' => '10.20',
                    'price__to' => '30.3',
                    'country_code' => 'pl',
                    'type' => 'ale',
                    'limit' => '20',
                    'offset' => '10'
                ],
                'expected' => [
                    'id' => 2,
                    'bwr' => 1,
                    'name__li' => 'test',
                    'price__from' => 10.20,
                    'price__to' => 30.3,
                    'country_code' => 'PL',
                    'type' => 'ale',
                    'limit' => 20,
                    'offset' => 10
                ]
            ],
            'wrong country_code is given' => [
                'params' => [
                    'country_code' => 'some code',
                ],
                'expected' => [
                    'id' => NULL,
                    'bwr' => NULL,
                    'name__li' => NULL,
                    'price__from' => NULL,
                    'country_code' => NULL,
                    'price__to' => NULL,
                    'type' => NULL,
                    'limit' => 10,
                    'offset' => 0
                ]
            ],
            'price__from  is given as int' => [
                'params' => [
                    'price__from' => '20',
                ],
                'expected' => [
                    'id' => NULL,
                    'bwr' => NULL,
                    'name__li' => NULL,
                    'price__from' => '20.00',
                    'price__to' => NULL,
                    'country_code' => NULL,
                    'type' => NULL,
                    'limit' => 10,
                    'offset' => 0
                ]
            ],
            'name__li special hars given' => [
                'params' => [
                    'name__li' => '"!@#$%^&*(){}[];<>,./~:;',
                ],
                'expected' => [
                    'id' => NULL,
                    'bwr' => NULL,
                    'name__li' => '&quot;!@#$%^&amp;*(){}[];&lt;&gt;,./~:;',
                    'price__from' => NULL,
                    'price__to' => NULL,
                    'country_code' => NULL,
                    'type' => NULL,
                    'limit' => 10,
                    'offset' => 0
                ]
            ],
            'type special hars given' => [
                'params' => [
                    'type' => '"!@#$%^&*(){}[];<>,./~:;',
                ],
                'expected' => [
                    'id' => NULL,
                    'bwr' => NULL,
                    'name__li' => NULL,
                    'price__from' => NULL,
                    'price__to' => NULL,
                    'country_code' => NULL,
                    'type' => '&quot;!@#$%^&amp;*(){}[];&lt;&gt;,./~:;',
                    'limit' => 10,
                    'offset' => 0
                ]
            ],
        ];
    }

    /**
     * test verifyFilters
     *
     * @dataProvider dpVerifyFilters
     * @covers Model_Rest_Beers::verifyFilters
     */
    public function testVerifyFilters($params, $expected)
    {
        $Reclection = new \ReflectionMethod(\Model_Rest_Beers::class, 'verifyFilters');
        $Reclection->setAccessible(TRUE);

        $result = $Reclection->invoke($this->object, $params);

        $this->assertEquals($expected, $result);
    }

    public function dpBuildQueryList()
    {
        return [
            'only type and limits given' => [
                'verified' => [
                    'type' => 'ale',
                    'limit' => 10,
                    'limit' => 0,
                ],
                'expected where' => [
                    [
                        'AND' => [
                            'bwr.id_bwr', '=', new \Database_Expression('beer.id_bwr')
                        ],
                    ],
                    [
                        'AND' => [
                            'beer.beer_removed', '=', 0
                        ],
                    ],
                    [
                        'AND' => [
                            'beer.beer_type',
                            '=',
                            'ale',
                        ],
                    ],
                ]
            ],
            'all filters given' => [
                'verified' => [
                    'id' => 1,
                    'bwr' => 2,
                    'name__li' => 'beer name',
                    'price__from' => 30.30,
                    'price__to' => 40.40,
                    'country_code' => 'PL',
                    'type' => 'ale',
                    'limit' => 10,
                    'limit' => 0,
                ],
                'expected where' => [
                    [
                        'AND' => [
                            'bwr.id_bwr', '=', new \Database_Expression('beer.id_bwr')
                        ],
                    ],
                    [
                        'AND' => [
                            'beer.beer_removed', '=', 0
                        ],
                    ],
                    [
                        'AND' => [
                            'beer.beer_id', '=', 1
                        ],
                    ],
                    [
                        'AND' => [
                            'beer.id_bwr', '=', 2
                        ],
                    ],
                    [
                        'AND' => [
                            'beer.beer_name', 'LIKE','%beer name%'
                        ],
                    ],
                    [
                        'AND' => [
                            'beer.beer_price', '>',30.30
                        ],
                    ],
                    [
                        'AND' => [
                            'beer.beer_price', '<',40.40
                        ],
                    ],
                    [
                        'AND' => [
                            'beer.beer_country_code', '=','PL'
                        ],
                    ],
                    [
                        'AND' => [
                            'beer.beer_type','=','ale',
                        ],
                    ],
                ]
            ]
        ];
    }

    /**
     * test buildQueryList
     *
     * @dataProvider dpBuildQueryList
     * @covers Model_Rest_Beers::buildQueryList
     */
    public function testBuildQueryList($params, $expectedWhere)
    {
        $Reclection = new \ReflectionMethod(\Model_Rest_Beers::class, 'buildQueryList');
        $Reclection->setAccessible(TRUE);

        /* @var $DBBeers \Database_Query_Builder_Select */
        $DBBeers = $Reclection->invoke($this->object, $params);

        $Reflection = new \ReflectionClass($DBBeers);
        $ReflectionWhere = $Reflection->getProperty('_where');
        $ReflectionWhere->setAccessible(TRUE);
        $where = $ReflectionWhere->getValue($DBBeers);

        $this->assertEquals($expectedWhere, $where);
    }

    public function dpBuildQueryCount()
    {
        $DBBeers = \DB::select_array([
                    'beer.id_beer',
                    'beer.beer_id',
                    'bwr.id_bwr',
                    'beer.beer_name',
                    'bwr.bwr_name',
                    'beer.beer_type',
                    'beer.beer_country',
                    'beer.beer_country_code',
                    'beer.beer_liter_price',
                ])
                ->from(['beer', 'beer'])
                ->from(['bewer', 'bwr'])
                ->where('bwr.id_bwr', '=', \DB::expr('beer.id_bwr'))
                ->where('beer.beer_removed', '=', 0)
                ->where('beer_name', 'LIKE', '%beer name%')
                ->where('beer_price', '>', 30.30);
        return [
            'default test' => [
                'query' => $DBBeers,
                'expected where' => [
                    [
                        'AND' => [
                            'bwr.id_bwr', '=', new \Database_Expression('beer.id_bwr')
                        ],
                    ],
                    [
                        'AND' => [
                            'beer.beer_removed', '=', 0
                        ],
                    ],
                    [
                        'AND' => [
                            'beer_name', 'LIKE','%beer name%'
                        ],
                    ],
                    [
                        'AND' => [
                            'beer_price', '>',30.30
                        ],
                    ],
                ],
                'expected select' => [
                    new \Database_Expression('COUNT(id_beer) AS liczba')
                ]
            ]
        ];
    }

    /**
     * test buildQueryList
     *
     * @dataProvider dpBuildQueryCount
     * @covers Model_Rest_Beers::buildQueryCount
     */
    public function testBuildQueryCount($params, $expectedWhere, $expectedSelect)
    {
        $Reclection = new \ReflectionMethod(\Model_Rest_Beers::class, 'buildQueryCount');
        $Reclection->setAccessible(TRUE);
        

        /* @var $DBBeers \Database_Query_Builder_Select */
        $DBBeers = $Reclection->invoke($this->object, $params);

        $Reflection = new \ReflectionClass($DBBeers);
        $ReflectionWhere = $Reflection->getProperty('_where');
        $ReflectionWhere->setAccessible(TRUE);
        $where = $ReflectionWhere->getValue($DBBeers);
        
        $ReflectionSelect = $Reflection->getProperty('_select');
        $ReflectionSelect->setAccessible(TRUE);
        $select = $ReflectionSelect->getValue($DBBeers);

        $this->assertEquals($expectedWhere, $where);
        $this->assertEquals($expectedSelect, $select);
    }

}
