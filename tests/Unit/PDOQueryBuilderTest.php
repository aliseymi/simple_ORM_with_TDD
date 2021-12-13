<?php

namespace Tests\Unit;

use App\Helpers\Config;
use PHPUnit\Framework\TestCase;
use App\Database\PDOQueryBuilder;
use App\Database\PDODatabaseConnection;

class PDOQueryBuilderTest extends TestCase
{
    private $queryBuilder;

    public function setUp(): void
    {
        $pdoConnection = new PDODatabaseConnection($this->getConfig());

        $this->queryBuilder = new PDOQueryBuilder($pdoConnection->connect());

        $this->queryBuilder->beginTransaction();

        parent::setUp();
    }

    public function testItCanCreateData()
    {
        $result = $this->insertIntoDb();

        $this->assertIsInt($result);

        $this->assertGreaterThan(0, $result);
    }

    private function getConfig()
    {
        return Config::get('database', 'pdo_testing');
    }

    public function testItCanUpdateData()
    {
        $this->insertIntoDb();

        $result = $this->queryBuilder
            ->table('bugs')
            ->where('user', 'aliseymi')
            ->update([
                'email' => 'ali2@gmail.com',
                'name' => 'ali'
            ]);

        $this->assertEquals(1, $result);
    }

    public function testItCanDeleteData()
    {
        $this->insertIntoDb();
        $this->insertIntoDb();
        $this->insertIntoDb();
        $this->insertIntoDb();

        $result = $this->queryBuilder->where('user', 'aliseymi')->delete();

        $this->assertEquals(4, $result);
    }

    private function insertIntoDb()
    {
        $data = [
            'name' => 'First Bug Report',
            'link' => 'http://link.com',
            'user' => 'aliseymi',
            'email' => 'ali@gmail.com'
        ];

        return $this->queryBuilder->table('bugs')->create($data);
    }

    public function tearDown(): void
    {
        // $this->queryBuilder->truncateAllTable();

        $this->queryBuilder->rollback();

        parent::tearDown();
    }
}
