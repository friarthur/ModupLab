<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
require_once __DIR__ . '/app/controllers/ProjectController.php';

$projectController = new ProjectController();
$message = '';

if ($_SERVER['REQUEST_METHOD'] ?? '' === 'POST') {
    $projectName = $_POST['projectName'] ?? '';
    $projectType = $_POST['projectType'] ?? '';
    $language = $_POST['language'] ?? '';

    if ($projectName && $projectType && $language) {
        $basePath = '/home/modup/compartilhada/VSCODE/ModupModelsWeb/';
        $result = $projectController->createProject($projectName, $projectType, $language, $basePath);

        $message = $result['message'];
        if ($result['success']) {
            $message .= "<br><small>" . $result['instructions'] . "</small>";
            $message .= "<br><small><strong>📍 Local:</strong> " . $result['path'] . "</small>";
        }
    } else {
        $message = "❌ Por favor, preencha todos os campos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerador de Projetos Mod-up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            padding: 30px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { 
            background: rgba(255, 255, 255, 0.95); 
            padding: 40px; 
            border-radius: 20px; 
            width: 500px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
        }
        h2 { 
            text-align: center; 
            color: #333;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .path-info {
            background: #e3f2fd;
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #1976d2;
            border-left: 4px solid #1976d2;
        }
        .path-info strong {
            color: #0d47a1;
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        label { 
            display: block; 
            margin-bottom: 8px; 
            color: #555;
            font-weight: 500;
        }
        input, select, button { 
            width: 100%; 
            padding: 12px 15px; 
            border-radius: 10px; 
            border: 2px solid #e0e0e0;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        input:focus, select:focus { 
            outline: none; 
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button { 
            background: linear-gradient(to right, #667eea, #764ba2);
            color: #fff; 
            border: none; 
            cursor: pointer; 
            font-weight: 600;
            letter-spacing: 1px;
            margin-top: 10px;
            padding: 15px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .message { 
            padding: 15px; 
            border-radius: 10px; 
            margin: 20px 0; 
            text-align: center;
            font-weight: 500;
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb;
        }
        .project-type-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 5px;
            font-size: 13px;
            color: #666;
            display: none;
        }
        .language-options {
            display: flex;
            gap: 10px;
            margin-top: 5px;
        }
        .language-option {
            flex: 1;
            text-align: center;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .language-option:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .language-option.selected {
            border-color: #667eea;
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
        }
        .language-option input {
            display: none;
        }
        .quick-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .quick-action-btn {
            flex: 1;
            padding: 10px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        .quick-action-btn:hover {
            border-color: #667eea;
            background: #eef2ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🚀 Gerador de Projetos Mod-up</h2>
        
        <div class="path-info">
            <strong>📍 Local de criação:</strong><br>
            /home/modup/compartilhada/VSCODE/ModupModelsWeb/<strong id="dynamicPath">[nome-do-projeto]</strong>
        </div>
        
        <form method="POST" id="projectForm">
            <div class="form-group">
                <label for="projectName">📁 Nome do Projeto</label>
                <input type="text" name="projectName" id="projectName" 
                       placeholder="Ex: meu-projeto-incrivel" 
                       oninput="updatePathPreview()"
                       required>
            </div>
            
            <div class="form-group">
                <label for="projectType">🎯 Tipo de Projeto</label>
                <select name="projectType" id="projectType" required onchange="showProjectInfo()">
                    <option value="">Selecione um tipo...</option>
                    <option value="php_mvc">PHP Puro com MVC</option>
                    <option value="laravel">Laravel</option>
                    <option value="react">React com Vite</option>
                    <option value="mvp">MVP (Model-View-Presenter)</option>
                    <option value="simple">Simple PHP</option>
                </select>
                <div class="project-type-info" id="projectTypeInfo"></div>
            </div>
            
            <div class="form-group">
                <label>💻 Linguagem/Framework</label>
                <div class="language-options">
                    <label class="language-option" onclick="selectLanguage(this, 'php')">
                        <input type="radio" name="language" value="php"> PHP
                    </label>
                    <label class="language-option" onclick="selectLanguage(this, 'javascript')">
                        <input type="radio" name="language" value="javascript"> JavaScript
                    </label>
                    <label class="language-option" onclick="selectLanguage(this, 'both')">
                        <input type="radio" name="language" value="both"> Ambos
                    </label>
                </div>
            </div>
            
            <button type="submit">✨ Gerar Projeto</button>
        </form>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function selectLanguage(element, value) {
            document.querySelectorAll('.language-option').forEach(opt => {
                opt.classList.remove('selected');
                opt.querySelector('input[type="radio"]').checked = false;
            });
            
            element.classList.add('selected');
            element.querySelector('input[type="radio"]').checked = true;
        }
        
        function showProjectInfo() {
            const select = document.getElementById('projectType');
            const infoDiv = document.getElementById('projectTypeInfo');
            const projectName = document.getElementById('projectName').value || '[nome-do-projeto]';
            
            const info = {
                'php_mvc': `📂 Estrutura PHP MVC (SEM banco):<br>
                            • app/controllers/<br>
                            • app/views/<br>
                            • core/config/<br>
                            • public/{css,js,img}<br>
                            • index.php (roteador)<br>
                            <em>Pasta: ModupModelsWeb/${projectName}</em>`,
                
                'laravel': `⚡ Laravel (SEM database/):<br>
                            • composer create-project laravel/laravel<br>
                            • Remove pasta database/<br>
                            • Configuração simplificada<br>
                            <em>Pasta: ModupModelsWeb/${projectName}</em>`,
                
                'react': `⚛️ React com Vite:<br>
                          • npm create vite@latest . -- --template react<br>
                          • src/components/<br>
                          • public/assets/<br>
                          <em>Pasta: ModupModelsWeb/${projectName}</em>`,
                
                'mvp': `🎯 MVP Pattern (SEM database):<br>
                        • public/ (acesso público)<br>
                        • core/config/<br>
                        • app/controllers/<br>
                        • app/presenters/<br>
                        • app/models/<br>
                        <em>Pasta: ModupModelsWeb/${projectName}</em>`,
                
                'simple': `✨ Simple PHP:<br>
                           • index.php<br>
                           • css/<br>
                           • js/<br>
                           • images/<br>
                           <em>Pasta: ModupModelsWeb/${projectName}</em>`
            };
            
            infoDiv.innerHTML = info[select.value] || '';
            infoDiv.style.display = info[select.value] ? 'block' : 'none';
            
            // Auto-select language
            if (['php_mvc', 'laravel', 'mvp', 'simple'].includes(select.value)) {
                selectLanguage(document.querySelector('.language-option[onclick*="php"]'), 'php');
            } else if (select.value === 'react') {
                selectLanguage(document.querySelector('.language-option[onclick*="javascript"]'), 'javascript');
            }
        }
        
        function updatePathPreview() {
            const projectName = document.getElementById('projectName').value || '[nome-do-projeto]';
            document.getElementById('dynamicPath').textContent = projectName;
            showProjectInfo();
        }
        
        updatePathPreview();
    </script>
</body>
</html>