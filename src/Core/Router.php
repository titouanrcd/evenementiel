<?php
/**
 * ============================================================
 * ROUTEUR - NOVA Événements
 * ============================================================
 * Système de routage simple et sécurisé
 * ============================================================
 */

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    
    /**
     * Ajouter une route GET
     */
    public function get(string $path, string $handler, array $middlewares = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middlewares);
    }
    
    /**
     * Ajouter une route POST
     */
    public function post(string $path, string $handler, array $middlewares = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middlewares);
    }
    
    /**
     * Ajouter une route
     */
    private function addRoute(string $method, string $path, string $handler, array $middlewares = []): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares,
            'pattern' => $this->convertToPattern($path)
        ];
        return $this;
    }
    
    /**
     * Convertir un chemin en pattern regex
     */
    private function convertToPattern(string $path): string
    {
        // Échapper les caractères spéciaux
        $pattern = preg_quote($path, '/');
        
        // Remplacer les paramètres {param} par des groupes de capture
        $pattern = preg_replace('/\\\{([a-zA-Z_]+)\\\}/', '(?P<$1>[^\/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Dispatcher la requête
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();
        
        // Rechercher la route correspondante
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extraire les paramètres
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Exécuter les middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $this->executeMiddleware($middleware);
                }
                
                // Exécuter le handler
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }
        
        // Route non trouvée
        $this->notFound();
    }
    
    /**
     * Obtenir l'URI nettoyée
     */
    private function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Retirer la query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Retirer le BASE_PATH (sous-dossier) si défini
        if (defined('BASE_PATH') && BASE_PATH !== '' && BASE_PATH !== '/') {
            $basePath = BASE_PATH;
            if (strpos($uri, $basePath) === 0) {
                $uri = substr($uri, strlen($basePath));
            }
        }
        
        // Nettoyer l'URI
        $uri = '/' . trim($uri, '/');
        
        // Décoder l'URL de manière sécurisée
        $uri = rawurldecode($uri);
        
        // Prévenir les attaques de path traversal
        $uri = str_replace(['../', '..\\'], '', $uri);
        
        return $uri === '' ? '/' : $uri;
    }
    
    /**
     * Exécuter un middleware
     */
    private function executeMiddleware(string $middleware): void
    {
        $middlewareClass = "\\App\\Middleware\\{$middleware}";
        if (class_exists($middlewareClass)) {
            $instance = new $middlewareClass();
            $instance->handle();
        }
    }
    
    /**
     * Exécuter un handler
     */
    private function executeHandler(string $handler, array $params): void
    {
        list($controllerName, $method) = explode('@', $handler);
        
        $controllerClass = "\\App\\Controllers\\{$controllerName}";
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller not found: {$controllerClass}");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new \Exception("Method not found: {$method}");
        }
        
        // Appeler la méthode avec les paramètres
        call_user_func_array([$controller, $method], $params);
    }
    
    /**
     * Page 404
     */
    private function notFound(): void
    {
        http_response_code(404);
        include ROOT_PATH . '/src/Views/errors/404.php';
        exit;
    }
}
