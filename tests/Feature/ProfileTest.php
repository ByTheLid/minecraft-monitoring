<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Controllers\ProfileController;
use App\Controllers\DashboardController;
use App\Core\Request;
use App\Core\Database;

class ProfileTest extends TestCase
{
    protected function setUp(): void
    {
        \App\Core\Env::load(__DIR__ . '/../../.env');
        
        // Setup database
        $db = Database::getInstance();
        $db->exec("DELETE FROM servers");
        $db->exec("DELETE FROM users WHERE username IN ('profile_test', 'profile_viewer')");
        
        // Create user
        $db->exec("INSERT INTO users (username, email, password_hash, role) VALUES ('profile_test', 'test@profile.com', '" . password_hash('password', PASSWORD_BCRYPT) . "', 'user')");
        $this->userId = $db->lastInsertId();
    }
    
    public function test_public_profile_view()
    {
        $controller = new ProfileController();
        
        // Mock Request
        $request = new class extends Request {
             public function param(string $key, mixed $default = null): mixed {
                 return 'profile_test';
             }
        };
        
        $response = $controller->show($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('profile_test', $response->getBody());
    }
    
    public function test_settings_page_access()
    {
        $controller = new DashboardController();
        
        // Mock Auth
        $_SESSION['user'] = ['id' => $this->userId, 'username' => 'profile_test', 'email' => 'test@profile.com', 'role' => 'user'];
        
        $request = new Request();
        $response = $controller->settings($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Account Settings', $response->getBody());
    }
    
    public function test_update_settings()
    {
        $controller = new DashboardController();
        
        // Mock Auth
        $_SESSION['user'] = ['id' => $this->userId, 'username' => 'profile_test', 'email' => 'test@profile.com', 'role' => 'user', 'password_hash' => password_hash('password', PASSWORD_BCRYPT)];
        
        // Mock Request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['email'] = 'newemail@profile.com';
        $_POST['password'] = 'password'; // Current
        $_POST['new_password'] = 'newpass123';
        
        $request = new Request();
        
        // We need to suppress headers from redirect
        ob_start(); 
        try {
            $response = $controller->updateSettings($request);
        } catch (\Exception $e) {
            // redirect might exit? No, Controller::redirect just returns Response
        }
        ob_end_clean();

        $this->assertEquals(302, $response->getStatusCode());
        
        // Verify DB
        $db = Database::getInstance();
        $user = $db->query("SELECT * FROM users WHERE id = {$this->userId}")->fetch();
        $this->assertEquals('newemail@profile.com', $user['email']);
        $this->assertTrue(password_verify('newpass123', $user['password_hash']));
    }
}
