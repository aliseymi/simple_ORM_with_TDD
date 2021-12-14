<?php

namespace Tests\Functional;

use GuzzleHttp\Client;
use App\Helpers\Config;
use PHPUnit\Framework\TestCase;
use App\Database\PDOQueryBuilder;
use App\Database\PDODatabaseConnection;
use App\Helpers\HttpClient;
use phpDocumentor\Reflection\Types\This;

class CrudTest extends TestCase
{
    private $httpClient;
    private $queryBuilder;

    public function setUp(): void
    {
        $pdoConnection = new PDODatabaseConnection($this->getConfig());

        $this->queryBuilder = new PDOQueryBuilder($pdoConnection->connect());

        $this->httpClient = new HttpClient();

        parent::setUp();
    }

    public function testItCanCreateDataWithApi()
    {
        $data = [
            'json' => [
                'name' => 'API',
                'user' => 'Ali2',
                'email' => 'api@gmail.com',
                'link' => 'api.com',
            ]
        ];

        $response = $this->httpClient->post('index.php', $data);

        $this->assertEquals(200, $response->getStatusCode());

        $bug = $this->queryBuilder
            ->table('bugs')
            ->where('name', 'API')
            ->where('user', 'Ali2')
            ->first();

        $this->assertNotNull($bug);

        return $bug;
    }

    /**
     * @depends testItCanCreateDataWithApi
     */
    public function testItCanUpdateDataWithApi($bug)
    {
        $data = [
            'json' => [
                'id' => $bug->id,
                'name' => 'API For Update'
            ]
        ];

        $response = $this->httpClient->put('index.php', $data);

        $this->assertEquals(200, $response->getStatusCode());

        $bug = $this->queryBuilder->table('bugs')
            ->find($bug->id);

        $this->assertNotNull($bug);
        $this->assertEquals('API For Update', $bug->name);
    }

    /**
     * @depends testItCanCreateDataWithApi
     */
    public function testItCanFetchDataWithApi($bug)
    {
        $response = $this->httpClient->get('index.php', [
            'json' => [
                'id' => $bug->id
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey('id', json_decode($response->getBody(), true));
    }


    /**
     * @depends testItCanCreateDataWithApi
     */
    public function testItCanDeleteWithApi($bug)
    {
        $response = $this->httpClient->delete('index.php', [
            'json' => [
                'id' => $bug->id
            ]
        ]);

        $this->assertEquals(204, $response->getStatusCode());

        $bug = $this->queryBuilder->table('bugs')->find($bug->id);

        $this->assertNull($bug);
    }

    public function tearDown(): void
    {
        $this->httpClient = null;

        parent::tearDown();
    }

    private function getConfig()
    {
        return Config::get('database', 'pdo_testing');
    }
}