<?php

namespace StackNerds\MtnOpenAPI\Tests;

use StackNerds\MtnOpenAPI\Config;
use StackNerds\MtnOpenAPI\OpenAPI;

/**
 * Class OpenAPITest
 *
 * @category Test
 * @package  StackNerds\MtnOpenAPI\Tests
 * @author   Fenn-CS@StackNerds <normad@stacknerds.com>
 */
class OpenAPITest extends TestCase
{

    public function testSayHello()
    {
        $config = new Config();
        $OpenAPI = new OpenAPI($config);

        $name = 'Fenn-CS@StackNerds';

        $result = $OpenAPI->sayHello($name);

        $expected = $config->get('greeting') . ' ' . $name;

        $this->assertEquals($result, $expected);

    }

}
