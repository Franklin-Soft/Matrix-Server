<?php
/**
 * Matrix Server - Página de Bienvenida
 * Servidor Web Local para Android
 */

// ============================================================
// FUNCIONES
// ============================================================

function getServerInfo(): array {
    return [
        'phpVersion'     => phpversion(),
        'serverSoftware' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido',
        'httpsEnabled'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'documentRoot'   => $_SERVER['DOCUMENT_ROOT'] ?? 'Desconocido',
        'scriptPath'     => __FILE__,
        'localhostIp'    => getLocalhostIp(),
        'wifiIp'         => getWifiIp(),
        'memoryLimit'    => ini_get('memory_limit'),
        'maxUploadSize'  => ini_get('upload_max_filesize'),
        'timezone'       => date_default_timezone_get(),
    ];
}

function getLocalhostIp(): string {
    $ip = $_SERVER['SERVER_ADDR'] ?? '';
    if ($ip === '::1' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $hostname = gethostname();
        if ($hostname) {
            $ipv4 = gethostbyname($hostname);
            if (filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ip = $ipv4;
            }
        }
    }
    return $ip;
}

function getWifiIp(): string {
    $ip = '';
    if (function_exists('socket_create')) {
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock) {
            if (@socket_connect($sock, '8.8.8.8', 53)) {
                @socket_getsockname($sock, $ip, $port);
            }
            @socket_close($sock);
        }
    }
    if ($ip && filter_var($ip, FILTER_VALIDATE_IP) && $ip !== '127.0.0.1') {
        return $ip;
    }
    return '';
}

function getProjects(string $basePath): array {
    $projects = [];
    if (!is_dir($basePath)) return $projects;

    $items = @scandir($basePath);
    if ($items === false) return $projects;

    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item[0] === '.') continue;
        $fullPath = $basePath . DIRECTORY_SEPARATOR . $item;
        if (is_dir($fullPath)) {
            $projects[] = [
                'name' => $item,
                'url'  => '/' . rawurlencode($item) . '/',
            ];
        }
    }
    usort($projects, fn($a, $b) => strcasecmp($a['name'], $b['name']));
    return $projects;
}

// ============================================================
// ACCIONES
// ============================================================

if (isset($_GET['viewInfo'])) {
    phpinfo();
    exit;
}

// Crear nuevo proyecto
$mensajeCrear = '';
$htdocs = $_SERVER['DOCUMENT_ROOT'] ?? '/sdcard/htdocs';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nuevo_proyecto'])) {
    $nombre = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['nuevo_proyecto']);
    if ($nombre === '') {
        $mensajeCrear = '❌ Nombre inválido. Solo letras, números, guiones y guiones bajos.';
    } elseif (is_dir($htdocs . '/' . $nombre)) {
        $mensajeCrear = '⚠️ El proyecto "' . htmlspecialchars($nombre) . '" ya existe.';
    } else {
        if (@mkdir($htdocs . '/' . $nombre, 0777, true)) {
            $contenido = "<?php\necho '<h1>Proyecto: " . addslashes($nombre) . "</h1>';\necho '<p>Creado con Matrix Server</p>';\n";
            @file_put_contents($htdocs . '/' . $nombre . '/index.php', $contenido);
            $mensajeCrear = '✅ Proyecto "' . htmlspecialchars($nombre) . '" creado correctamente.';
        } else {
            $mensajeCrear = '❌ No se pudo crear la carpeta. Verifica los permisos.';
        }
    }
}

$info     = getServerInfo();
$projects = getProjects($htdocs);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrix Server</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚀</text></svg>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f4f6f9;
            color: #2c3e50;
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }

        .container { max-width: 720px; margin: 0 auto; }

        .header {
            text-align: center;
            padding: 30px 20px;
            margin-bottom: 20px;
        }

        .header .logo { font-size: 48px; margin-bottom: 8px; }
        .header h1 { font-size: 1.8rem; color: #2c3e50; margin-bottom: 4px; }
        .header p { color: #7f8c8d; font-size: 0.95rem; }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e8ecf1;
        }

        .card h2 {
            font-size: 1.1rem;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e8ecf1;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card h2 .badge {
            margin-left: auto;
            background: #3498db;
            color: #fff;
            font-size: 0.75rem;
            padding: 2px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .info-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 14px;
            background: #f8fafc;
            border-radius: 6px;
            font-size: 0.88rem;
        }

        .info-item .label { color: #7f8c8d; }

        .info-item .value {
            font-weight: 600;
            text-align: right;
            word-break: break-all;
        }

        .info-item .value.mono {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 0.82rem;
            font-weight: 500;
        }

        .info-item .value a {
            color: #3498db;
            text-decoration: none;
            border-bottom: 1px dashed #3498db;
        }

        .info-item .value a:hover { border-bottom-style: solid; }

        .projects {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .project {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            background: #f8fafc;
            border-radius: 6px;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .project:hover {
            background: #fff;
            border-color: #3498db;
            color: #3498db;
            transform: translateX(2px);
        }

        .project .icon { font-size: 1.2rem; }
        .project .name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .empty {
            text-align: center;
            padding: 30px 20px;
            color: #95a5a6;
            font-size: 0.9rem;
        }

        .empty .icon { font-size: 2.5rem; margin-bottom: 8px; opacity: 0.4; }

        .form-crear {
            display: flex;
            gap: 8px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px dashed #e8ecf1;
        }

        .form-crear input {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid #e8ecf1;
            border-radius: 6px;
            font-size: 0.88rem;
            font-family: inherit;
            color: #2c3e50;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-crear input:focus { border-color: #3498db; }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.88rem;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
            font-family: inherit;
        }

        .btn:hover { background: #2980b9; }

        .btn.gray { background: #ecf0f1; color: #2c3e50; }
        .btn.gray:hover { background: #d5dbdb; }

        .mensaje {
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 0.88rem;
            margin-bottom: 14px;
        }

        .mensaje.ok {
            background: #e8f8ef;
            color: #1e7e34;
            border-left: 4px solid #27ae60;
        }

        .mensaje.err {
            background: #fdecea;
            color: #a93226;
            border-left: 4px solid #e74c3c;
        }

        .mensaje.warn {
            background: #fff8e1;
            color: #7d5a00;
            border-left: 4px solid #f39c12;
        }

        .alert {
            background: #fff8e1;
            border-left: 4px solid #f39c12;
            padding: 14px 18px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #7d5a00;
            margin-bottom: 18px;
        }

        .alert code {
            background: rgba(0, 0, 0, 0.08);
            padding: 1px 6px;
            border-radius: 3px;
            font-family: monospace;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 0.82rem;
            color: #95a5a6;
        }

        .footer a { color: #3498db; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }

        @media (max-width: 520px) {
            body { padding: 12px; }
            .header h1 { font-size: 1.5rem; }
            .info-list { grid-template-columns: 1fr; }
            .projects { grid-template-columns: 1fr; }
            .info-item { flex-direction: column; align-items: flex-start; gap: 2px; }
            .info-item .value { text-align: left; }
            .form-crear { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <div class="logo">🚀</div>
        <h1>Matrix Server</h1>
        <p>Servidor Web Local para Android</p>
    </div>

    <?php if ($mensajeCrear): ?>
        <div class="mensaje <?= str_starts_with($mensajeCrear, '✅') ? 'ok' : (str_starts_with($mensajeCrear, '⚠️') ? 'warn' : 'err') ?>">
            <?= $mensajeCrear ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>💻 Información del Servidor</h2>
        <div class="info-list">
            <div class="info-item">
                <span class="label">PHP</span>
                <span class="value"><?= htmlspecialchars($info['phpVersion']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Servidor</span>
                <span class="value"><?= htmlspecialchars($info['serverSoftware']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">SSL</span>
                <span class="value"><?= $info['httpsEnabled'] ? '✅ Activado' : '❌ Desactivado' ?></span>
            </div>
            <div class="info-item">
                <span class="label">Memoria</span>
                <span class="value"><?= htmlspecialchars($info['memoryLimit']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Subida máx.</span>
                <span class="value"><?= htmlspecialchars($info['maxUploadSize']) ?></span>
            </div>
<div class="info-item">
                <span class="label">📁 Ruta raíz</span>
                <span class="value mono"><?= htmlspecialchars($info['documentRoot']) ?></span>
            </div>
            <?php if ($info['localhostIp']): ?>
            <div class="info-item">
                <span class="label">IP Local</span>
                <span class="value">
                    <a href="http://<?= htmlspecialchars($info['localhostIp']) ?>/" target="_blank">
                        <?= htmlspecialchars($info['localhostIp']) ?>
                    </a>
                </span>
            </div>
            <?php endif; ?>
            <?php if ($info['wifiIp']): ?>
            <div class="info-item">
                <span class="label">🌐 IP Wi-Fi</span>
                <span class="value">
                    <a href="http://<?= htmlspecialchars($info['wifiIp']) ?>/" target="_blank">
                        <?= htmlspecialchars($info['wifiIp']) ?>
                    </a>
                </span>
            </div>
            <?php endif; ?>
            

        </div>
    </div>

    <div class="card">
        <h2>
            📂 Proyectos
            <span class="badge"><?= count($projects) ?></span>
        </h2>

        <?php if (empty($projects)): ?>
            <div class="empty">
                <div class="icon">📭</div>
                <p>No hay proyectos todavía.</p>
                <p>Crea uno con el formulario de abajo 👇</p>
            </div>
        <?php else: ?>
            <div class="projects">
                <?php foreach ($projects as $p): ?>
                    <a href="<?= htmlspecialchars($p['url']) ?>" class="project">
                        <span class="icon">📁</span>
                        <span class="name"><?= htmlspecialchars($p['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-crear" autocomplete="off">
            <input type="text" name="nuevo_proyecto" placeholder="Nombre del nuevo proyecto..."
                   pattern="[a-zA-Z0-9_-]+" title="Solo letras, números, guiones y guiones bajos" required>
            <button type="submit" class="btn">➕ Crear</button>
        </form>
    </div>

    <div class="alert">
        ⚠️ <strong>MySQL:</strong> usuario <code>root</code> con contraseña vacía.
        Úsalo para entrar a phpMyAdmin. Cambia la contraseña por seguridad.
    </div>

    <div class="actions">
        <a href="/phpmyadmin/" class="btn">🗄️ phpMyAdmin</a>
        <a href="?viewInfo=1" class="btn gray">🐘 PHP Info</a>
        <a href="/" class="btn gray">🔄 Refrescar</a>
    </div>

</div>

<div class="footer">
    Hecho con ❤️ para la comunidad de Termux ·
    <a href="https://github.com/Franklin-Soft/Matrix-Server" target="_blank">GitHub</a>
</div>

</body>
</html>
