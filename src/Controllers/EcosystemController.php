<?php
declare(strict_types=1);
namespace UEDF\Controllers;

use UEDF\Session;

class EcosystemController {
    private Session $session;

    public function __construct() {
        $this->session = new Session();
        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public function index(): void {
        require __DIR__ . '/../../views/ecosystem/index.php';
    }
}
