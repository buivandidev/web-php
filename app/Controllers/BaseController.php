<?php
namespace App\Controllers;

use App\Core\Container;
use App\Core\Session;
use App\Core\CsrfProtection;

abstract class BaseController
{
    public function __construct(
        protected readonly Container $container
    ) {}

    // ── Render View ──────────────────────────────────────────
    protected function render(string $view, array $data = []): void
    {
        extract($data);  // $data['title'] → $title trong view
        $viewPath = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: $viewPath");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $layout = str_starts_with($view, 'admin.') ? 'admin' : 'main';
        require VIEW_PATH . '/layouts/' . $layout . '.php';   // $content inject vào layout
    }

    // ── Redirect ─────────────────────────────────────────────
    protected function redirect(string $url): never
    {
        header("Location: $url");
        exit;
    }

    // ── JSON Response (cho AJAX) ─────────────────────────────
    protected function json(mixed $data, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── Lấy user đang đăng nhập ──────────────────────────────
    protected function getCurrentUserId(): ?int
    {
        return Session::get('user_id');
    }

    // ── Kiểm tra đăng nhập ───────────────────────────────────
    protected function requireLogin(): void
    {
        if (!$this->getCurrentUserId()) {
            $this->redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        }
    }

    // ── Kiểm tra role Admin ───────────────────────────────────
    protected function requireAdmin(): void
    {
        $this->requireLogin();
        if (Session::get('user_role') !== 'admin') {
            http_response_code(403);
            $this->render('errors.403');
            exit;
        }
    }

    // ── CSRF validation ───────────────────────────────────────
    protected function validateCsrf(): void
    {
        $token = $_POST['_csrf_token'] ?? $_GET['_csrf_token'] ?? '';
        if (!CsrfProtection::validate($token)) {
            http_response_code(403);
            $this->json(['error' => 'CSRF token không hợp lệ.'], 403);
        }
    }
}
