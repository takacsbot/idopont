<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);


use PHPUnit\Framework\TestCase;

require_once './php_backend/db_config.php'; // vagy használj egy mock PDO-t
require_once './loginphp.php'; // Az eredeti kódod



class LoginTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $_SERVER = array_merge($_SERVER, [
            'REQUEST_METHOD' => 'POST'
        ]);

        $_POST = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $_SESSION = []; // Ürítsük ki az esetleges régi session adatokat

        if (!session_id()) {
            session_start(); // Indítsuk el a sessiont, ha még nincs aktív
        }
    }


    public function testGetUserByEmail()
    {     
        $email = 'test@example.com';

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetch')->willReturn(['id' => 1, 'email' => $email, 'password' => password_hash('password123', PASSWORD_DEFAULT)]);

        $this->pdo->method('prepare')->willReturn($stmtMock);

        $user = getUserByEmail($this->pdo, $email);

        $this->assertIsArray($user);
        $this->assertEquals($email, $user['email']);
    }

    public function testCreateAuthToken()
    {        
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())->method('execute');

        $this->pdo->method('prepare')->willReturn($stmtMock);

        $tokenData = createAuthToken($this->pdo, 1);

        $this->assertArrayHasKey('token', $tokenData);
        $this->assertArrayHasKey('expires', $tokenData);
        $this->assertNotEmpty($tokenData['token']);
        $this->assertGreaterThan(time(), $tokenData['expires']);
    }

    public function testSetAuthSessionAndCookie()
    {       
        session_start(); // Győződjünk meg róla, hogy fut-e

        $_SESSION = [];
        $_COOKIE = [];

        $user = ['id' => 1, 'username' => 'testuser'];
        $tokenData = ['token' => 'test_token', 'expires' => time() + 3600];

        setAuthSessionAndCookie($user, $tokenData);

        // var_dump(session_id()); // Nézd meg, hogy van-e session ID
        // var_dump($_SESSION); // Ellenőrizd, hogy valóban beállt-e az érték

        $this->assertEquals($_SESSION['auth_token'], 'test_token');
    }
}
