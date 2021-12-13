<?php

namespace Tests\Unit;

use App\Helpers\Config;
use PHPUnit\Framework\TestCase;
use App\Database\PDODatabaseConnection;
use App\Contracts\DatabaseConnectionInterface;
use App\Exceptions\ConfigNotValidException;
use App\Exceptions\DatabaseConnectionException;
use PDO;

class PDODatabaseConnectionTest extends TestCase
{
    public function testPDODatabaseConnectionImplementsDatabaseConnectionInterface()
    {   
        $config = $this->getConfig();

        $pdoConnection = new PDODatabaseConnection($config);

        $this->assertInstanceOf(DatabaseConnectionInterface::class, $pdoConnection);
    }

    private function getConfig()
    {
        return Config::get('database', 'pdo_testing');
    }

    public function testConnectMethodShouldReturnValidInstance()
    {
        $config = $this->getConfig();

        $pdoConnection = new PDODatabaseConnection($config);

        $pdoHandler = $pdoConnection->connect();

        $this->assertInstanceOf(PDODatabaseConnection::class, $pdoHandler);

        return $pdoHandler;
    }


    /**
     * @depends testConnectMethodShouldReturnValidInstance
     */
    public function testConnectMethodShouldBeConnectToDatabase($pdoHandler)
    {
        $this->assertInstanceOf(PDO::class, $pdoHandler->getConnection());
    }

    public function testItThrowsExceptionIfConfigIsInvalid()
    {
        $this->expectException(DatabaseConnectionException::class);

        $config = $this->getConfig();

        $config['database'] = 'dummy';

        $pdoConnection = new PDODatabaseConnection($config);

        $pdoConnection->connect();
    }

    public function testReceivedConfigHasRequiredKey()
    {
        $this->expectException(ConfigNotValidException::class);
        
        $config = $this->getConfig();

        unset($config['db_user']);

        $pdoConnection = new PDODatabaseConnection($config);

        $pdoConnection->connect();
    }
}