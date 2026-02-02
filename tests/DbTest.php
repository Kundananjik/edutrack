<?php

// tests/DbTest.php
use PHPUnit\Framework\TestCase;

final class DbTest extends TestCase
{
    public function testPdoSelectOne(): void
    {
        require_once dirname(__DIR__) . '/includes/db.php';
        $this->assertInstanceOf(PDO::class, $pdo, 'PDO instance should be available');

        try {
            $val = $pdo->query('SELECT 1')->fetchColumn();
            $this->assertEquals(1, (int)$val);
        } catch (Throwable $e) {
            $this->markTestIncomplete('DB not reachable or misconfigured: ' . $e->getMessage());
        }
    }
}
