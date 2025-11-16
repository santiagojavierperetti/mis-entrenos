<?php
// listado.php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

function formatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    $value = max($bytes, 0);
    while ($value >= 1024 && $i < count($units) - 1) {
        $value /= 1024;
        $i++;
    }
    return sprintf('%s %s', $i === 0 ? $value : number_format($value, 2), $units[$i]);
}

/* ============================================================
   CONEXIÓN PDO (igual que en procesar.php)
   ============================================================ */

$pdo = null;
$configPath = __DIR__ . '/config.php';

if (file_exists($configPath)) {
    require $configPath;
}

if (!$pdo instanceof PDO) {
    $dbHost = '127.0.0.1';
    $dbName = 'mis_entrenos';
    $dbUser = 'root';
    $dbPass = 'Falcon-1984'; // tu pass

    try {
        $pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (Throwable $e) {
        http_response_code(500);
        exit('Error de base de datos: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
}

/* ============================================================
   OBTENER ENTRENAMIENTOS
   ============================================================ */

try {
    // Más reciente primero; el número correlativo lo hacemos en PHP
    $stmt = $pdo->query('
        SELECT id, original_name, stored_name, path,
               size_bytes, mime_type, md5_hash, sha1_hash
        FROM fit_files
        ORDER BY id DESC
    ');
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    exit('Error al obtener entrenamientos: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Listado de entrenamientos .FIT</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="icon" type="image/png" href="assets/favicon.png">
</head>
<body>

<header class="app-bar">
    <div class="app-bar__inner">
        <div class="app-bar__brand">
            <span class="app-bar__logo">🚴‍♂️</span>
            <div>
                <div class="app-bar__title">Mis entrenos .FIT</div>
                <div class="app-bar__subtitle">Listado de actividades cargadas</div>
            </div>
        </div>
    </div>
</header>

<main>
    <header class="page-header">
        <h1>Entrenamientos cargados</h1>
        <p>
            Este listado muestra todas las actividades registradas en la base.
            El número de entrenamiento es correlativo dentro de esta vista y
            es independiente del ID interno de la base de datos.
        </p>
    </header>

    <section class="section-card">
        <?php if (empty($rows)): ?>
            <p>No hay entrenamientos cargados todavía.</p>
            <div class="actions">
                <a class="button-link" href="index.php">Subir un entrenamiento</a>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.95rem;">
                    <thead>
                    <tr>
                        <th style="text-align:left; padding:0.5rem 0.5rem; border-bottom:1px solid rgba(148,163,184,0.4);">Entrenamiento</th>
                        <th style="text-align:left; padding:0.5rem 0.5rem; border-bottom:1px solid rgba(148,163,184,0.4);">Nombre original</th>
                        <th style="text-align:left; padding:0.5rem 0.5rem; border-bottom:1px solid rgba(148,163,184,0.4);">Tamaño</th>
                        <th style="text-align:left; padding:0.5rem 0.5rem; border-bottom:1px solid rgba(148,163,184,0.4);">Tipo</th>
                        <th style="text-align:left; padding:0.5rem 0.5rem; border-bottom:1px solid rgba(148,163,184,0.4);">ID BD</th>
                        <th style="text-align:left; padding:0.5rem 0.5rem; border-bottom:1px solid rgba(148,163,184,0.4);">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $n = 1; // contador correlativo para mostrar, independiente del ID
                    foreach ($rows as $row):
                        $id       = (int)$row['id'];
                        $origName = htmlspecialchars($row['original_name'], ENT_QUOTES, 'UTF-8');
                        $sizeText = formatBytes((int)$row['size_bytes']);
                        $mime     = htmlspecialchars($row['mime_type'], ENT_QUOTES, 'UTF-8');
                        $path     = htmlspecialchars($row['path'], ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr>
                            <td style="padding:0.45rem 0.5rem; border-bottom:1px solid rgba(15,23,42,0.08);">
                                #<?php echo $n; ?>
                            </td>
                            <td style="padding:0.45rem 0.5rem; border-bottom:1px solid rgba(15,23,42,0.08);">
                                <?php echo $origName; ?>
                            </td>
                            <td style="padding:0.45rem 0.5rem; border-bottom:1px solid rgba(15,23,42,0.08);">
                                <?php echo $sizeText; ?>
                            </td>
                            <td style="padding:0.45rem 0.5rem; border-bottom:1px solid rgba(15,23,42,0.08);">
                                <?php echo $mime; ?>
                            </td>
                            <td style="padding:0.45rem 0.5rem; border-bottom:1px solid rgba(15,23,42,0.08);">
                                <?php echo $id; ?>
                            </td>
                            <td style="padding:0.45rem 0.5rem; border-bottom:1px solid rgba(15,23,42,0.08);">
                                <a href="<?php echo $path; ?>" download>Descargar</a>
                            </td>
                        </tr>
                    <?php
                        $n++;
                    endforeach;
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="actions" style="margin-top:1.5rem;">
                <a class="button-link" href="index.php">Subir nuevo entrenamiento</a>
            </div>
        <?php endif; ?>
    </section>
</main>

</body>
</html>
