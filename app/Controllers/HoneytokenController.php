<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Security;

class HoneytokenController extends Controller
{
    /**
     * Decoy endpoint. Legitimate users never reach this - any hit is a
     * scraper or someone poking at internal URLs, so it is logged and
     * the admins are alerted. Responds with a bland 404 to avoid tipping
     * off the visitor.
     */
    public function hit(array $params): void
    {
        $token = Database::first("SELECT * FROM honeytokens WHERE token = ?", [(string) ($params['token'] ?? '')]);
        if ($token) {
            Security::recordHoneytokenHit(
                $token,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                Auth::id()
            );
        }
        http_response_code(404);
        echo 'Not found.';
    }
}
