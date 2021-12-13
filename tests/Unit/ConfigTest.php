<?php

namespace Tests\Unit;

use App\Exceptions\ConfigFileNotFoundException;
use App\Helpers\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testGetFileContentsReturnsArray()
    {
        $config = Config::getFileContents('database');

        $this->assertIsArray($config);
    }

    public function testItThrowExceptionIfFileNotExists()
    {
        $this->expectException(ConfigFileNotFoundException::class);
        $config = Config::getFileContents('dummy');
    }

    public function testGetMethodReturnsValidData()
    {
        $config = Config::get('database', 'pdo');

        $expectedData = [
            'driver' => 'mysql',
            'database' => 'bug_tracker',
            'host' => 'localhost',
            'db_user' => 'root',
            'db_password' => ''
        ];

        $this->assertEquals($expectedData, $config);
    }
}
