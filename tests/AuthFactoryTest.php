<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\Test\CIDatabaseTestCase;
use Fluent\Auth\Adapters\SessionAdapter;
use Fluent\Auth\Adapters\TokenAdapter;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;
use Fluent\Auth\Models\UserModel;
use Fluent\Auth\UserDatabase;

class AuthFactoryTest extends CIDatabaseTestCase
{
    /** @var AuthFactoryInterface|AuthenticationInterface */
    protected $auth;

    /** @var string */
    protected $namespace = '\Fluent\Auth';

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth = Services::auth();
    }

    public function testCreateUserProvider()
    {
        $provider1 = $this->auth->createUserProvider('users');
        $provider2 = $this->auth->createUserProvider('database');

        $this->assertInstanceOf(UserModel::class, $provider1);
        $this->assertInstanceOf(UserDatabase::class, $provider2);
    }

    public function testGetDefaultUserProvider()
    {
        $this->assertNotInstanceOf(UserModel::class, $this->auth->getDefaultUserProvider());
    }

    public function testGuard()
    {
        $this->assertInstanceOf(SessionAdapter::class, $this->auth->guard('web'));
        $this->assertInstanceOf(TokenAdapter::class, $this->auth->guard('token'));

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Auth guard [notdefined] is not defined');

        $this->auth->guard('notdefined');
    }

    public function testGetDefaultDriver()
    {
        $this->assertEquals('web', $this->auth->getDefaultDriver());
    }

    public function testSetDefaultDriver()
    {
        $this->isInstanceOf(AuthFactoryInterface::class, $this->auth->setDefaultDriver('token'));
        $this->assertEquals('token', $this->auth->getDefaultDriver());
    }

    public function testResolveUserUsing()
    {
        $resolve = $this->auth->resolveUsersUsing(function () {
            return new UserModel();
        });

        $this->assertInstanceOf(AuthFactoryInterface::class, $resolve);
    }

    public function testExtendDriver()
    {
        $config = config('Auth');

        $extend = $this->auth->extend('token', function () use ($config) {
            return new TokenAdapter($config, new UserModel());
        });

        $this->assertInstanceOf(AuthFactoryInterface::class, $extend);
    }

    public function testHasResolvedGuards()
    {
        $this->assertTrue($this->auth->hasResolvedGuards());
    }
}