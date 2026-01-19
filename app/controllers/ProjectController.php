<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
class ProjectController {
    public function createProject($projectName, $projectType, $language, $basePath = null) {
        if ($basePath === null) {
            $basePath = __DIR__ . '/../../projects/';
        }
        
        $basePath = rtrim($basePath, '/') . '/';
        $projectPath = $basePath . $projectName;
        
        // Verifica se basePath existe
        if (!file_exists($basePath)) {
            if (!mkdir($basePath, 0777, true)) {
                return [
                    'success' => false,
                    'message' => '❌ Erro: Não foi possível criar o diretório base!'
                ];
            }
        }
        
        // Verifica se projeto já existe
        if (file_exists($projectPath)) {
            return [
                'success' => false,
                'message' => '❌ Erro: Diretório já existe! Escolha outro nome.'
            ];
        }
        
        // Cria o diretório
        if (!mkdir($projectPath, 0777, true)) {
            return [
                'success' => false,
                'message' => '❌ Erro: Não foi possível criar o projeto!'
            ];
        }
        
        $instructions = '';
        
        switch($projectType) {
            case 'php_mvc':
                $this->createPHPMVC($projectPath);
                $instructions = "📌 Para testar: cd '{$projectPath}' && php -S localhost:8000 -t public";
                break;
                
            case 'laravel':
                chdir($projectPath);
                $output = shell_exec('composer create-project laravel/laravel . 2>&1');
                
                // REMOVE pasta database do Laravel
                $databasePath = $projectPath . '/database';
                if (file_exists($databasePath)) {
                    $this->deleteDirectory($databasePath);
                }
                
                // Remove referências ao banco no .env.example
                $envExample = $projectPath . '/.env.example';
                if (file_exists($envExample)) {
                    $envContent = file_get_contents($envExample);
                    // Remove linhas relacionadas a database
                    $envContent = preg_replace('/DB_.*\n/', '', $envContent);
                    file_put_contents($envExample, $envContent);
                }
                
                if (file_exists($projectPath . '/vendor')) {
                    $instructions = "📌 Para iniciar: cd '{$projectPath}' && php artisan serve";
                } else {
                    return [
                        'success' => false,
                        'message' => '❌ Erro na instalação do Laravel'
                    ];
                }
                break;
                
            case 'react':
                chdir($projectPath);
                $output = shell_exec('npm create vite@latest . -- --template react 2>&1');
                if (strpos($output, 'Done') !== false || file_exists($projectPath . '/package.json')) {
                    $instructions = "📌 Para iniciar: cd '{$projectPath}' && npm install && npm run dev";
                } else {
                    return [
                        'success' => false,
                        'message' => '❌ Erro na instalação do React'
                    ];
                }
                break;
                
            case 'mvp':
                $this->createMVP($projectPath);
                $instructions = "📌 Para testar: cd '{$projectPath}' && php -S localhost:8000 -t public";
                break;
                
            case 'simple':
                $this->createSimplePHP($projectPath);
                $instructions = "📌 Para testar: cd '{$projectPath}' && php -S localhost:8000";
                break;
        }
        
        return [
            'success' => true,
            'message' => "✅ Projeto '{$projectName}' criado com sucesso!",
            'instructions' => $instructions,
            'path' => $projectPath
        ];
    }
    
    private function deleteDirectory($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        
        return rmdir($dir);
    }
    
    private function createPHPMVC($path) {
        // ESTRUTURA PHP MVC SEM DATABASE
        $structure = [
            'app/controllers' => ['HomeController.php' => '<?php
class HomeController {
    public function index() {
        $data = ["title" => "Bem-vindo", "message" => "Sistema MVC sem banco"];
        require __DIR__ . "/../views/home.php";
    }
    
    public function about() {
        $data = ["title" => "Sobre", "message" => "Esta é uma aplicação PHP MVC"];
        require __DIR__ . "/../views/about.php";
    }
}'],
            
            'app/views' => [
                'home.php' => '<!DOCTYPE html>
<html>
<head>
    <title><?= $data["title"] ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= $data["message"] ?></h1>
        <nav>
            <a href="/">Home</a> | 
            <a href="/about">Sobre</a>
        </nav>
    </div>
</body>
</html>',
                
                'about.php' => '<!DOCTYPE html>
<html>
<head>
    <title><?= $data["title"] ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= $data["message"] ?></h1>
        <nav>
            <a href="/">Home</a> | 
            <a href="/about">Sobre</a>
        </nav>
    </div>
</body>
</html>'
            ],
            
            'core/config' => [
                'Config.php' => '<?php
class Config {
    const APP_NAME = "Mod-up MVC";
    const DEBUG_MODE = true;
    
    public static function get($key) {
        return [
            "site_name" => self::APP_NAME,
            "debug" => self::DEBUG_MODE
        ][$key] ?? null;
    }
}'
            ],
            
            'public/css' => [
                'style.css' => 'body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}
.container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
h1 {
    color: #333;
}
nav {
    margin-top: 20px;
    padding: 10px;
    background: #eee;
    border-radius: 5px;
}
nav a {
    margin-right: 15px;
    text-decoration: none;
    color: #0066cc;
}'
            ],
            
            'public/js' => [
                'app.js' => 'console.log("Mod-up MVC iniciado!");
document.addEventListener("DOMContentLoaded", function() {
    console.log("Página carregada");
});'
            ],
            
            'public/img' => []
        ];
        
        // Cria toda a estrutura
        foreach ($structure as $folder => $files) {
            $fullPath = $path . '/' . $folder;
            mkdir($fullPath, 0777, true);
            
            if (is_array($files)) {
                foreach ($files as $filename => $content) {
                    file_put_contents($fullPath . '/' . $filename, $content);
                }
            }
        }
        
        // index.php (Front Controller)
        $router = '<?php
// Front Controller - Mod-up MVC
session_start();
require_once __DIR__ . "/core/config/Config.php";

$request = $_SERVER["REQUEST_URI"];
$request = str_replace("/index.php", "", $request);

// Rotas simples
$routes = [
    "/" => ["controller" => "HomeController", "method" => "index"],
    "/about" => ["controller" => "HomeController", "method" => "about"],
];

if (isset($routes[$request])) {
    $route = $routes[$request];
    $controllerFile = __DIR__ . "/app/controllers/" . $route["controller"] . ".php";
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controllerClass = $route["controller"];
        $method = $route["method"];
        
        $controller = new $controllerClass();
        $controller->$method();
    } else {
        http_response_code(404);
        echo "Controller não encontrado";
    }
} else {
    http_response_code(404);
    echo "404 - Página não encontrada";
}
?>';
        
        file_put_contents($path . '/index.php', $router);
        
        // public/index.php
        $publicIndex = '<?php
// Ponto de entrada público
require_once __DIR__ . "/../index.php";
?>';
        file_put_contents($path . '/public/index.php', $publicIndex);
        
        // Criar .htaccess para URLs amigáveis
        $htaccess = 'RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]';
        file_put_contents($path . '/.htaccess', $htaccess);
    }
    
    private function createMVP($path) {
        $structure = [
            'public' => [
                'index.php' => '<?php
// Public entry point - MVP Pattern
require_once __DIR__ . "/../app/bootstrap.php";'
            ],
            
            'core/config' => [
                'App.php' => '<?php
class App {
    public static function run() {
        $controller = $_GET["c"] ?? "home";
        $action = $_GET["a"] ?? "index";
        
        $controllerClass = ucfirst($controller) . "Controller";
        $controllerFile = __DIR__ . "/../app/controllers/" . $controllerClass . ".php";
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerInstance = new $controllerClass();
            
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            } else {
                echo "Ação não encontrada";
            }
        } else {
            echo "Controller não encontrado";
        }
    }
}'
            ],
            
            'app/controllers' => [
                'HomeController.php' => '<?php
class HomeController {
    public function index() {
        $model = new HomeModel();
        $data = $model->getData();
        
        $presenter = new HomePresenter();
        $presenter->render($data);
    }
}'
            ],
            
            'app/presenters' => [
                'HomePresenter.php' => '<?php
class HomePresenter {
    public function render($data) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?= $data["title"] ?></title>
            <style>
                body { font-family: Arial; padding: 20px; }
                h1 { color: #333; }
            </style>
        </head>
        <body>
            <h1><?= $data["message"] ?></h1>
            <p>MVP Pattern funcionando!</p>
        </body>
        </html>
        <?php
    }
}'
            ],
            
            'app/models' => [
                'HomeModel.php' => '<?php
class HomeModel {
    public function getData() {
        return [
            "title" => "MVP Application",
            "message" => "Bem-vindo ao padrão MVP"
        ];
    }
}'
            ]
        ];
        
        // Cria bootstrap.php
        $bootstrap = '<?php
// Bootstrap MVP
require_once __DIR__ . "/../core/config/App.php";
require_once __DIR__ . "/models/HomeModel.php";
require_once __DIR__ . "/presenters/HomePresenter.php";

App::run();
?>';
        
        foreach ($structure as $folder => $files) {
            $fullPath = $path . '/' . $folder;
            mkdir($fullPath, 0777, true);
            
            foreach ($files as $filename => $content) {
                file_put_contents($fullPath . '/' . $filename, $content);
            }
        }
        
        file_put_contents($path . '/app/bootstrap.php', $bootstrap);
    }
    
    private function createSimplePHP($path) {
        $structure = [
            'index.php' => '<?php
$title = "Mod-up Simple PHP";
$message = "Projeto criado automaticamente!";
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= $message ?></h1>
        <p>Projeto Simple PHP sem banco de dados</p>
        
        <div class="features">
            <h2>Recursos:</h2>
            <ul>
                <li>✅ Estrutura organizada</li>
                <li>✅ CSS e JS separados</li>
                <li>✅ Pronto para usar</li>
                <li>✅ Sem banco de dados</li>
            </ul>
        </div>
    </div>
    
    <script src="js/app.js"></script>
</body>
</html>',
            
            'css' => [
                'style.css' => '* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.container {
    background: rgba(255, 255, 255, 0.95);
    padding: 40px;
    border-radius: 20px;
    max-width: 600px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
h1 {
    color: #333;
    margin-bottom: 20px;
    text-align: center;
}
p {
    color: #666;
    text-align: center;
    margin-bottom: 30px;
}
.features {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
}
.features h2 {
    color: #333;
    margin-bottom: 15px;
}
.features ul {
    list-style: none;
}
.features li {
    padding: 8px 0;
    color: #555;
}
.features li:before {
    content: "✓ ";
    color: #28a745;
    font-weight: bold;
}'
            ],
            
            'js' => [
                'app.js' => 'console.log("Mod-up Simple PHP iniciado!");
    
document.addEventListener("DOMContentLoaded", function() {
    console.log("Documento pronto");
    
    // Exemplo simples de interação
    const features = document.querySelectorAll(".features li");
    features.forEach((feature, index) => {
        feature.addEventListener("click", function() {
            this.style.transform = "scale(1.05)";
            setTimeout(() => {
                this.style.transform = "scale(1)";
            }, 300);
        });
    });
    
    console.log("Features interativas carregadas: " + features.length);
});'
            ],
            
            'images' => []
        ];
        
        foreach ($structure as $folder => $content) {
            if (is_array($content)) {
                $fullPath = $path . '/' . $folder;
                mkdir($fullPath, 0777, true);
                
                foreach ($content as $filename => $fileContent) {
                    file_put_contents($fullPath . '/' . $filename, $fileContent);
                }
            } else {
                // É um arquivo na raiz
                file_put_contents($path . '/' . $folder, $content);
            }
        }
    }
}
?>