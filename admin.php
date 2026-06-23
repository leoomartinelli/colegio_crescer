<?php
session_start();

// Configuração
$password = 'crescer123'; // Senha padrão para acessar o painel administrativo
$musicDir = __DIR__ . '/musicas';
$imgDir = __DIR__ . '/assets/img';

// Mensagens de feedback
$error = '';
$success = '';

// Processamento de Login
if (isset($_POST['login'])) {
    $pass = $_POST['password'] ?? '';
    if ($pass === $password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Senha incorreta! Tente novamente.';
    }
}

// Processamento de Logout
if (isset($_GET['logout'])) {
    $_SESSION['admin_logged_in'] = false;
    session_destroy();
    header('Location: admin.php');
    exit;
}

$authorized = $_SESSION['admin_logged_in'] ?? false;

if ($authorized) {
    // 1. Upload de Músicas
    if (isset($_POST['upload_music']) && isset($_FILES['music_file'])) {
        $file = $_FILES['music_file'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext === 'mp3') {
                // Sanitização do nome do arquivo
                $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
                if (!str_ends_with(strtolower($cleanName), '.mp3')) {
                    $cleanName .= '.mp3';
                }
                
                $dest = $musicDir . '/' . $cleanName;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $success = "Música '" . htmlspecialchars($file['name']) . "' adicionada com sucesso!";
                } else {
                    $error = "Erro ao salvar o arquivo de música no servidor. Verifique as permissões de gravação da pasta 'musicas/'.";
                }
            } else {
                $error = "Apenas arquivos de áudio com extensão .mp3 são permitidos.";
            }
        } else {
            $error = "Erro no upload da música (Código: " . $file['error'] . ").";
        }
    }

    // 2. Exclusão de Músicas
    if (isset($_POST['delete_music'])) {
        $filename = $_POST['filename'] ?? '';
        $filename = basename($filename); // Evitar Directory Traversal
        $filepath = $musicDir . '/' . $filename;
        if (file_exists($filepath) && is_file($filepath) && str_ends_with(strtolower($filename), '.mp3')) {
            if (unlink($filepath)) {
                $success = "Música '" . htmlspecialchars($filename) . "' removida do sistema com sucesso!";
            } else {
                $error = "Não foi possível remover o arquivo de música do servidor.";
            }
        } else {
            $error = "Arquivo de música inválido ou inexistente.";
        }
    }

    // 3. Substituição de Fotos
    if (isset($_POST['replace_photo']) && isset($_POST['photo_slot']) && isset($_FILES['photo_file'])) {
        $slot = $_POST['photo_slot'];
        $file = $_FILES['photo_file'];
        
        $validSlots = [
            'nova-img-1.jpg'  => 'Galeria 1 - Sala de Aula (JPG)',
            'nova-img-2.jpg'  => 'Galeria 2 - Atividades Didáticas (JPG)',
            'nova-img-3.jpg'  => 'Galeria 3 - Aulas Práticas (JPG)',
            'nova-img-4.jpg'  => 'Galeria 4 - Área de Convivência (JPG)',
            'nova-img-5.jpg'  => 'Galeria 5 - Atividades Criativas (JPG)',
            'nova-img-6.jpg'  => 'Galeria 6 - Educação Física (JPG)',
            'nova-img-7.jpg'  => 'Galeria 7 - Biblioteca Escolar (JPG)',
            'nova-img-8.jpg'  => 'Galeria 8 - Apresentações e Cultura (JPG)',
            'nova-img-9.jpg'  => 'Banner Principal (JPG)',
            'nova-img-10.jpg' => 'Banner Secundário (JPG)',
            'nova-img-11.jpg' => 'Fachada Principal (JPG)',
            'nova_logo.png'   => 'Logotipo Principal (PNG)'
        ];

        if (array_key_exists($slot, $validSlots)) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $targetExt = strtolower(pathinfo($slot, PATHINFO_EXTENSION));
                
                // Valida se a extensão do arquivo enviado corresponde à do slot original (.jpg/.jpeg para slots JPG e .png para slots PNG)
                $isValidExt = false;
                if ($targetExt === 'jpg' && in_array($ext, ['jpg', 'jpeg'])) {
                    $isValidExt = true;
                } elseif ($targetExt === 'png' && $ext === 'png') {
                    $isValidExt = true;
                }

                if ($isValidExt) {
                    $dest = $imgDir . '/' . $slot;
                    
                    // Exclui a imagem antiga antes de salvar para evitar problemas de cache de upload
                    if (file_exists($dest)) {
                        @unlink($dest);
                    }
                    
                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $success = "Imagem '" . $validSlots[$slot] . "' atualizada com sucesso!";
                    } else {
                        $error = "Erro ao mover a imagem para a pasta 'assets/img/'. Verifique as permissões de gravação da pasta.";
                    }
                } else {
                    $error = "Extensão incorreta. O slot selecionado requer um arquivo do tipo ." . strtoupper($targetExt) . ".";
                }
            } else {
                $error = "Erro no upload da imagem (Código: " . $file['error'] . ").";
            }
        } else {
            $error = "Slot de imagem inválido selecionado.";
        }
    }
}

// Coleta de músicas atualmente cadastradas no sistema
$songs = [];
if (is_dir($musicDir)) {
    $files = scandir($musicDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && str_ends_with(strtolower($file), '.mp3')) {
            $songs[] = $file;
        }
    }
}

// Configuração dos slots de fotos para exibição no painel administrativo
$photoSlots = [
    ['file' => 'nova_logo.png',   'name' => 'Logotipo Principal', 'desc' => 'Logo oficial exibido no cabeçalho e rodapé', 'ext' => 'PNG'],
    ['file' => 'nova-img-9.jpg',  'name' => 'Banner Principal', 'desc' => 'Fundo do banner principal com título escolar', 'ext' => 'JPG'],
    ['file' => 'nova-img-10.jpg', 'name' => 'Banner Secundário', 'desc' => 'Fundo do segundo bloco ou CTA escolar', 'ext' => 'JPG'],
    ['file' => 'nova-img-11.jpg', 'name' => 'Fachada Principal', 'desc' => 'Imagem da fachada ao lado do texto "Nossa Escola"', 'ext' => 'JPG'],
    ['file' => 'nova-img-1.jpg',  'name' => 'Galeria 1', 'desc' => 'Foto: Sala de Aula', 'ext' => 'JPG'],
    ['file' => 'nova-img-2.jpg',  'name' => 'Galeria 2', 'desc' => 'Foto: Atividades Didáticas', 'ext' => 'JPG'],
    ['file' => 'nova-img-3.jpg',  'name' => 'Galeria 3', 'desc' => 'Foto: Aulas Práticas', 'ext' => 'JPG'],
    ['file' => 'nova-img-4.jpg',  'name' => 'Galeria 4', 'desc' => 'Foto: Área de Convivência', 'ext' => 'JPG'],
    ['file' => 'nova-img-5.jpg',  'name' => 'Galeria 5', 'desc' => 'Foto: Atividades Criativas', 'ext' => 'JPG'],
    ['file' => 'nova-img-6.jpg',  'name' => 'Galeria 6', 'desc' => 'Foto: Educação Física', 'ext' => 'JPG'],
    ['file' => 'nova-img-7.jpg',  'name' => 'Galeria 7', 'desc' => 'Foto: Biblioteca Escolar', 'ext' => 'JPG'],
    ['file' => 'nova-img-8.jpg',  'name' => 'Galeria 8', 'desc' => 'Foto: Apresentações e Cultura', 'ext' => 'JPG']
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Colégio Crescer</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/favicon-96x96-1.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2b3273;
            --primary-hover: #1e2454;
            --accent-color: #e31c24;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --success-color: #10b981;
            --error-color: #ef4444;
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* login layout */
        .login-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #171c43 100%);
        }

        .login-card {
            background-color: var(--bg-white);
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
        }

        .login-logo {
            max-width: 180px;
            margin-bottom: 24px;
        }

        .login-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.4rem;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-dark);
        }

        .input-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            outline: none;
            transition: border-color var(--transition-speed);
        }

        .input-control:focus {
            border-color: var(--primary-color);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color var(--transition-speed);
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        /* Admin layout */
        .admin-header {
            background-color: var(--primary-color);
            color: white;
            padding: 16px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-brand-logo {
            max-height: 40px;
            background-color: white;
            padding: 4px;
            border-radius: 4px;
        }

        .admin-brand h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .btn-logout {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color var(--transition-speed);
        }

        .btn-logout:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffcccc;
        }

        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Alert styling */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            animation: fadeIn 0.3s ease-out;
        }

        .alert-success {
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: var(--success-color);
        }

        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: var(--error-color);
        }

        /* Tab Layout */
        .tab-navigation {
            display: flex;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 30px;
            gap: 20px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 12px 6px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            position: relative;
            transition: color var(--transition-speed);
        }

        .tab-btn.active {
            color: var(--primary-color);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 3px 3px 0 0;
        }

        .tab-panel {
            display: none;
            animation: fadeIn 0.4s ease-out;
        }

        .tab-panel.active {
            display: block;
        }

        /* Cards and Grids */
        .panel-header {
            margin-bottom: 24px;
        }

        .panel-header h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 4px;
        }

        .panel-header p {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .card {
            background-color: var(--bg-white);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            border: 1px solid var(--border-color);
            padding: 24px;
            margin-bottom: 30px;
        }

        /* Music specific styles */
        .upload-row {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            flex-wrap: wrap;
        }

        .upload-row .form-group {
            margin-bottom: 0;
            flex: 1;
            min-width: 250px;
        }

        .upload-row button {
            width: auto;
            padding: 12px 24px;
            height: 48px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .music-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .music-item {
            background-color: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
        }

        .music-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        }

        .music-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 200px;
        }

        .music-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(43, 50, 115, 0.05);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 1.1rem;
        }

        .music-title {
            font-weight: 600;
            font-size: 0.95rem;
            word-break: break-all;
        }

        .music-player-preview {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .audio-preview-element {
            height: 36px;
            max-width: 200px;
            border-radius: 8px;
        }

        .btn-delete {
            background: none;
            border: 1px solid #fee2e2;
            color: var(--error-color);
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all var(--transition-speed);
        }

        .btn-delete:hover {
            background-color: var(--error-color);
            color: white;
            border-color: var(--error-color);
        }

        .no-content {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
            font-size: 0.95rem;
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            background-color: #fafbfc;
        }

        /* Image slots styling */
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .photo-card {
            background-color: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
        }

        .photo-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
        }

        .photo-thumbnail-container {
            position: relative;
            height: 160px;
            background-color: #f1f5f9;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border-color);
        }

        .photo-thumbnail-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(30, 41, 59, 0.8);
            color: white;
            padding: 4px 8px;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 4px;
            backdrop-filter: blur(4px);
        }

        .photo-info-body {
            padding: 16px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 15px;
        }

        .photo-title-block h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            color: var(--primary-color);
            margin-bottom: 4px;
        }

        .photo-title-block p {
            font-size: 0.8rem;
            color: var(--text-muted);
            min-height: 38px;
        }

        .replace-photo-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-btn {
            border: 1px dashed var(--primary-color);
            color: var(--primary-color);
            background-color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            transition: all var(--transition-speed);
        }

        .file-input-wrapper:hover .file-input-btn {
            background-color: rgba(43, 50, 115, 0.04);
            border-style: solid;
        }

        .file-input-wrapper input[type="file"] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }

        .btn-replace-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: background-color var(--transition-speed);
        }

        .btn-replace-submit:hover {
            background-color: var(--primary-hover);
        }

        .selected-file-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            margin-top: 4px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 600px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 20px;
            }
            .admin-container {
                margin: 20px auto;
            }
            .music-item {
                flex-direction: column;
                align-items: stretch;
            }
            .audio-preview-element {
                max-width: 100%;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <?php if (!$authorized): ?>
        <!-- Tela de Login -->
        <div class="login-wrapper">
            <div class="login-card">
                <img src="assets/img/nova_logo.png" alt="Colégio Crescer" class="login-logo" onerror="this.src='https://colegiocrescer.com.br/wp-content/uploads/2021/04/crescer-logo.png'">
                <h2 class="login-title">Acesso Restrito</h2>
                <p class="login-subtitle">Entre com a senha administrativa para continuar</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: 20px; padding: 10px; font-size: 0.85rem;">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form action="admin.php" method="POST">
                    <div class="form-group">
                        <label for="password">Senha de Acesso</label>
                        <input type="password" id="password" name="password" class="input-control" placeholder="••••••••" required autofocus>
                    </div>
                    <button type="submit" name="login" class="btn-submit">
                        Entrar <i class="fa-solid fa-right-to-bracket" style="margin-left: 6px;"></i>
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Cabeçalho Administrativo -->
        <header class="admin-header">
            <div class="admin-brand">
                <img src="assets/img/nova_logo.png" alt="Logo" class="admin-brand-logo" onerror="this.src='https://colegiocrescer.com.br/wp-content/uploads/2021/04/crescer-logo.png'">
                <h1>Painel do Administrador</h1>
            </div>
            <a href="admin.php?logout=true" class="btn-logout">
                Sair <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </header>

        <!-- Container do Painel -->
        <main class="admin-container">
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error && !isset($_POST['login'])): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Abas de Navegação -->
            <nav class="tab-navigation">
                <button class="tab-btn active" onclick="switchTab(event, 'tab-songs')">
                    <i class="fa-solid fa-music" style="margin-right: 8px;"></i> Músicas da Rádio
                </button>
                <button class="tab-btn" onclick="switchTab(event, 'tab-photos')">
                    <i class="fa-solid fa-images" style="margin-right: 8px;"></i> Fotos e Banners
                </button>
            </nav>

            <!-- ABA 1: GERENCIAMENTO DE MÚSICAS -->
            <section id="tab-songs" class="tab-panel active">
                <div class="panel-header">
                    <h2>Gerenciar Músicas do Sistema</h2>
                    <p>Faça upload de novas músicas em formato MP3 e gerencie a fila de reprodução contínua da rádio.</p>
                </div>

                <!-- Formulário de Upload -->
                <div class="card">
                    <h3 style="margin-bottom: 15px; font-family: 'Montserrat', sans-serif; font-size: 1.1rem; color: var(--primary-color);">Enviar Nova Música (.mp3)</h3>
                    <form action="admin.php" method="POST" enctype="multipart/form-data">
                        <div class="upload-row">
                            <div class="form-group">
                                <label for="music_file">Selecione o arquivo MP3 no seu computador</label>
                                <input type="file" id="music_file" name="music_file" class="input-control" accept=".mp3" required>
                            </div>
                            <button type="submit" name="upload_music" class="btn-submit">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Enviar Música
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de Músicas -->
                <div class="card">
                    <h3 style="margin-bottom: 20px; font-family: 'Montserrat', sans-serif; font-size: 1.1rem; color: var(--primary-color); display: flex; justify-content: space-between; align-items: center;">
                        <span>Músicas Ativas (<?php echo count($songs); ?>)</span>
                        <small style="font-size: 0.8rem; font-weight: normal; color: var(--text-muted);">As músicas tocam em sequência infinita na rádio.</small>
                    </h3>

                    <?php if (empty($songs)): ?>
                        <div class="no-content">
                            <i class="fa-solid fa-music" style="font-size: 2.5rem; margin-bottom: 12px; display: block; color: var(--border-color);"></i>
                            Nenhuma música encontrada na pasta 'musicas/'. Envie um arquivo .mp3 acima para iniciar.
                        </div>
                    <?php else: ?>
                        <div class="music-list">
                            <?php foreach ($songs as $song): ?>
                                <div class="music-item">
                                    <div class="music-info">
                                        <div class="music-icon">
                                            <i class="fa-solid fa-volume-high"></i>
                                        </div>
                                        <span class="music-title" title="<?php echo htmlspecialchars($song); ?>"><?php echo htmlspecialchars($song); ?></span>
                                    </div>
                                    <div class="music-player-preview">
                                        <!-- Player de preview para o admin ouvir -->
                                        <audio class="audio-preview-element" src="musicas/<?php echo rawurlencode($song); ?>" controls></audio>
                                        
                                        <!-- Form de Exclusão -->
                                        <form action="admin.php" method="POST" onsubmit="return confirm('Deseja realmente remover esta música permanentemente?');">
                                            <input type="hidden" name="filename" value="<?php echo htmlspecialchars($song); ?>">
                                            <button type="submit" name="delete_music" class="btn-delete">
                                                <i class="fa-solid fa-trash-can"></i> Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- ABA 2: GERENCIAMENTO DE FOTOS -->
            <section id="tab-photos" class="tab-panel">
                <div class="panel-header">
                    <h2>Substituir Fotos e Banners</h2>
                    <p>Substitua as imagens oficiais do site mantendo os mesmos nomes de arquivos e dimensões recomendadas.</p>
                </div>

                <div class="photo-grid">
                    <?php foreach ($photoSlots as $slot): ?>
                        <div class="photo-card">
                            <div class="photo-thumbnail-container">
                                <?php 
                                $cacheBuster = time(); 
                                $imagePath = 'assets/img/' . $slot['file'];
                                ?>
                                <img src="<?php echo $imagePath . '?v=' . $cacheBuster; ?>" alt="<?php echo $slot['name']; ?>" onerror="this.src='https://placehold.co/400x300/f1f5f9/64748b?text=Sem+Imagem'">
                                <span class="photo-badge"><?php echo $slot['ext']; ?></span>
                            </div>
                            
                            <div class="photo-info-body">
                                <div class="photo-title-block">
                                    <h3><?php echo $slot['name']; ?></h3>
                                    <p><?php echo $slot['desc']; ?></p>
                                </div>

                                <form action="admin.php" method="POST" enctype="multipart/form-data" class="replace-photo-form" id="form-<?php echo str_replace('.', '-', $slot['file']); ?>">
                                    <input type="hidden" name="photo_slot" value="<?php echo $slot['file']; ?>">
                                    
                                    <div class="file-input-wrapper">
                                        <span class="file-input-btn">
                                            <i class="fa-solid fa-image"></i> Escolher Imagem
                                        </span>
                                        <input type="file" name="photo_file" accept="<?php echo $slot['ext'] === 'PNG' ? '.png' : '.jpg,.jpeg'; ?>" required onchange="showFileName(this, '<?php echo str_replace('.', '-', $slot['file']); ?>')">
                                    </div>
                                    
                                    <span class="selected-file-label" id="label-<?php echo str_replace('.', '-', $slot['file']); ?>">Nenhum arquivo selecionado</span>
                                    
                                    <button type="submit" name="replace_photo" class="btn-replace-submit">
                                        <i class="fa-solid fa-rotate"></i> Substituir
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </main>
    <?php endif; ?>

    <script>
        // Função de troca de abas
        function switchTab(e, tabId) {
            // Desativa botões das abas
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            
            // Oculta painéis
            const panels = document.querySelectorAll('.tab-panel');
            panels.forEach(panel => panel.classList.remove('active'));
            
            // Ativa o botão clicado
            e.currentTarget.classList.add('active');
            
            // Exibe o painel correspondente
            document.getElementById(tabId).classList.add('active');
        }

        // Mostra o nome do arquivo selecionado antes de enviar
        function showFileName(input, slotId) {
            const label = document.getElementById('label-' + slotId);
            if (input.files && input.files.length > 0) {
                label.textContent = input.files[0].name;
                label.style.color = '#10b981'; // Green color for success select
            } else {
                label.textContent = 'Nenhum arquivo selecionado';
                label.style.color = '#64748b';
            }
        }
    </script>
</body>
</html>
