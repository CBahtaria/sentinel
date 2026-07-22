<?php
declare(strict_types=1);
namespace UEDF\Controllers;

use UEDF\Session;

class LCEController {
    private Session $session;

    public function __construct() {
        $this->session = new Session();
        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public function index(): void {
        // Metrics loaded via AJAX GET /api/v1/lce
        require __DIR__ . '/../../views/lce/index.php';
    }
}
