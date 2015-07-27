<?php

namespace Dwsla\Integration\Service\Mpx;

use Dwsla\Service\Mpx\AuthenticationService;

/**
 * A live test of the MPX Authentication service
 */
class AuthenticationServiceTest extends \PHPUnit_Framework_TestCase
{

    protected static $service;
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        static::$service = static::isEnabled()
                ? new AuthenticationService()
                : null;
    }
    
    protected static function isEnabled()
    {
        return defined('DWSLA_SERVICE_MPX_AUTH_USER') 
            && DWSLA_SERVICE_MPX_AUTH_USER
            && defined('DWSLA_SERVICE_MPX_AUTH_PASS')
            && DWSLA_SERVICE_MPX_AUTH_PASS;
    }

    public function testSignInWithValidCredentials()
    {
        if (!static::$service) {
            return;
        }
        $this->assertTrue(static::$service->signIn(DWSLA_SERVICE_MPX_AUTH_USER, DWSLA_SERVICE_MPX_AUTH_PASS));
        $this->assertNotEmpty(static::$service->getToken());
    }
    
    public function testSignInWithInvalidCredentials()
    {
        if (!static::$service) {
            return;
        }
        $this->assertFalse(static::$service->signIn('invalid-user', 'invalid-pass'));
    }    
}
