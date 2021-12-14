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

    public function testItCanUpdateDataWithMultipleWhere()
    {
        $this->insertIntoDb();
        $this->insertIntoDb(['user' => 'ali2']);

        $result = $this->queryBuilder->table('bugs')
            ->where('user', 'aliseymi')
            ->where('link', 'http://link.com')
            ->update([
                'user' => 'ali'
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

    public function testItCanFetchData()
    {
        $this->multipleInsertIntoDb(10);
        $this->multipleInsertIntoDb(10, ['user' => 'ali']);

        $result = $this->queryBuilder->table('bugs')
            ->where('user', 'ali')
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(10, $result);
    }

    public function testItCanFetchSpecificColumn()
    {
        $this->MultipleInsertIntoDb(10);
        $this->MultipleInsertIntoDb(10, ['user' => 'ali']);

        $result = $this->queryBuilder->table('bugs')
            ->where('user', 'ali')
            ->get(['user', 'name']);

        $this->assertIsArray($result);
        $this->assertObjectHasAttribute('user', $result[0]);
        $this->assertObjectHasAttribute('name', $result[0]);
        
        $result = json_decode(json_encode($result[0]), true);
        $this->assertEquals(['user', 'name'], array_keys($result));
    }

    public function testItCanFetchFirstRow()
    {
        $this->MultipleInsertIntoDb(10, ['user' => 'ali']);

        $result = $this->queryBuilder
            ->table('bugs')
            ->where('user', 'ali')
            ->first();

        $this->assertIsObject($result);
        $this->assertObjectHasAttribute('id', $result);
        $this->assertObjectHasAttribute('name', $result);
        $this->assertObjectHasAttribute('email', $result);
        $this->assertObjectHasAttribute('link', $result);
        $this->assertObjectHasAttribute('user', $result);
    }

    public function testItCanFindWithId()
    {
        $this->insertIntoDb();
        $id = $this->insertIntoDb(['name' => 'For Find']);

        $result = $this->queryBuilder->table('bugs')->find($id);

        $this->assertIsObject($result);
        $this->assertEquals('For Find', $result->name);
    }

    public function testItCanFindBy()
    {
        $this->insertIntoDb();
        $id = $this->insertIntoDb(['name' => 'For FindBy']);

        $result = $this->queryBuilder
            ->table('bugs')
            ->findBy('name', 'For FindBy');
        
        $this->assertIsObject($result);
        $this->assertEquals($id, $result->id);
    }

    public function testItReturnsEmptyArrayWhenRecordNotFound()
    {
        $this->multipleInsertIntoDb(4);
        $result = $this->queryBuilder
            ->table('bugs')
            ->where('user', 'dummy')
            ->get();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testItReturnsNullWhenFirstRecordNotFound()
    {
        $this->multipleInsertIntoDb(4);
        $result = $this->queryBuilder
        ->table('bugs')
        ->where('user', 'dummy')
        ->first();

        $this->assertNull($result);
    }

    public function testItReturnsZeroWhenRecordNotFoundForUpdate()
    {
        $this->multipleInsertIntoDb(4);
        
        $result = $this->queryBuilder->table('bugs')
            ->where('user', 'Dummy')
            ->update(['name' => 'Test']);

        $this->assertEquals(0, $result);
    }

    private function insertIntoDb($options = [])
    {
        $data = array_merge([
            'name' => 'First Bug Report',
            'link' => 'http://link.com',
            'user' => 'aliseymi',
            'email' => 'ali@gmail.com'
        ], $options);

        return $this->queryBuilder->table('bugs')->create($data);
    }

    private function multipleInsertIntoDb($count, $options = [])
    {
        for($i = 1; $i <= $count; $i++){
            $this->insertIntoDb($options);
        }
    }

    public function tearDown(): void
    {
        // $this->queryBuilder->truncateAllTable();

        $this->queryBuilder->rollback();

        parent::tearDown();
    }
}
