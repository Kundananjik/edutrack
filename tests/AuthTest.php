<?php

// tests/AuthTest.php
use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase
{
    public function testPasswordHashAndVerify(): void
    {
        $password = 'Secret123!';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('wrong', $hash));
    }

    public function testCsrfToken(): void
    {
        require_once dirname(__DIR__) . '/includes/csrf.php';
        $token = get_csrf_token();
        $this->assertNotEmpty($token);
        $this->assertTrue(verify_csrf_token($token));
        $this->assertFalse(verify_csrf_token('invalid'));
    }
}
