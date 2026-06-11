<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function __construct(private readonly Container $container) {}

    public function get(string $path, string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, string $handler): void
    {
        // Convert route like '/movies/{id}' to regex pattern
        // '/movies/{id}' -> '#^/movies/(?P<id>[^/]+)$#'
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'  => $method,
            'path'    => $path,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$controllerName, $action] = explode('@', $route['handler']);
                $fullControllerName = 'App\\Controllers\\' . $controllerName;

                if (!class_exists($fullControllerName)) {
                    throw new \RuntimeException("Controller class $fullControllerName not found");
                }

                $controller = new $fullControllerName($this->container);

                if (!method_exists($controller, $action)) {
                    throw new \RuntimeException("Action $action not found in controller $fullControllerName");
                }

                $args = [];
                foreach ($params as $key => $val) {
                    $args[$key] = is_numeric($val) ? (int)$val : $val;
                }

                call_user_func_array([$controller, $action], $args);
                return;
            }
        }

        // 404
        http_response_code(404);
        $viewPath = VIEW_PATH . '/errors/404.php';
        if (file_exists($viewPath)) {
            $pageTitle = 'Không tìm thấy trang';
            ob_start();
            require $viewPath;
            $content = ob_get_clean();
            require VIEW_PATH . '/layouts/main.php';
        } else {
            echo "404 Not Found";
        }
        exit;
    }
}
