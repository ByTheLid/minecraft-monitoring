<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Controllers\Api\VoteApiController;
use App\Core\Request;
use App\Models\Server;
use App\Models\Vote;
use App\Core\Database;

class VoteApiTest extends TestCase
{
    private $controller;
    private $serverId;

    protected function setUp(): void
    {
        \App\Core\Env::load(__DIR__ . '/../../.env');
        
        $this->controller = new VoteApiController();
        
        // Setup database (create a test server)
        $db = Database::getInstance();
        $db->exec("DELETE FROM server_rankings");
        $db->exec("DELETE FROM votes");
        $db->exec("DELETE FROM servers");
        
        // Create user for server
        $db->exec("DELETE FROM users WHERE username = 'test_owner'");
        $db->exec("INSERT INTO users (username, email, password_hash, role) VALUES ('test_owner', 'owner@example.com', 'pass', 'user')");
        $userId = $db->lastInsertId();
        
        // Create server
        $stmt = $db->prepare("INSERT INTO servers (user_id, name, ip, port, is_active, is_approved) VALUES (?, 'Test Server', '127.0.0.1', 25565, 1, 1)");
        $stmt->execute([$userId]);
        $this->serverId = $db->lastInsertId();
        
        // Create ranking entry
        $db->prepare("INSERT INTO server_rankings (server_id, vote_count) VALUES (?, 0)")->execute([$this->serverId]);
    }

    public function test_vote_success()
    {
        // Mock request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'PlayerOne';
        
        // Mock server param (Router usually handles this, but here we invoke controller directly)
        // Controller uses $request->param('id'), which reads from private property params.
        // We can't easily populate params without Router. 
        // But let's check Request::param implementation.
        // Request uses route params array set by Router.
        // We can mock Request or subclass it, or reflection.
        
        // Easier: Request->param($key) returns $this->params[$key] ?? null.
        // Let's create a MockRequest
        $request = new class extends Request {
             public function ip(): string {
                return '192.168.1.100'; // Unique IP
            }
        };
        $request->setParams(['id' => $this->serverId]);
        
        // Call controller
        $response = $this->controller->vote($request);
        
        // Assertions
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertEquals(1, $body['data']['vote_count']);
        
        // Check DB
        $db = Database::getInstance();
        $count = $db->query("SELECT COUNT(*) FROM votes")->fetchColumn();
        $this->assertEquals(1, $count);
        $vote = $db->query("SELECT * FROM votes LIMIT 1")->fetch();
        $this->assertEquals('PlayerOne', $vote['username']);
    }

    public function test_vote_missing_username()
    {
        // Mock request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = ''; 
        $request = new class extends Request {};
        $request->setParams(['id' => $this->serverId]);

        $response = $this->controller->vote($request);

        $this->assertEquals(422, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertArrayHasKey('username', $body['error']['details']);
    }

    public function test_vote_duplicate_ip()
    {
         // First vote
         $db = Database::getInstance();
         
         $_SERVER['REQUEST_METHOD'] = 'POST';
         $_POST['username'] = 'PlayerTwo';
         
         $request1 = new class extends Request {
             public function ip(): string { return '10.0.0.5'; }
         };
         $request1->setParams(['id' => $this->serverId]);
         
         $this->controller->vote($request1);
         
         // Second vote from same IP
         $_POST['username'] = 'PlayerThree';
         
         $request2 = new class extends Request {
             public function ip(): string { return '10.0.0.5'; }
         };
         $request2->setParams(['id' => $this->serverId]);
         
         $response = $this->controller->vote($request2);
         
         $code = $response->getStatusCode();
         if ($code !== 400) {
             echo "\nDEBUG: Status $code. Body: " . $response->getBody() . "\n";
         }
         $this->assertEquals(400, $code); // ALREADY_VOTED
         $body = json_decode($response->getBody(), true);
         $this->assertEquals('ALREADY_VOTED', $body['error']['code']);
    }
}
