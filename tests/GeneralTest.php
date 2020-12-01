<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GeneralTest extends WebTestCase
{

    /**
     * @test
     *
     * @dataProvider urlProvider
     */
    public function smokeTest(string $url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlProvider()
    {
        yield ['/'];
        yield ['/conference/amsterdam-2019'];
        yield ['/login'];
    }
}
