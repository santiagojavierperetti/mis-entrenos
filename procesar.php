<?php
// procesar.php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

/* ============================================================
   1) CONEXIÓN PDO
   - Si existe config.php (que define $pdo), lo usa.
   - Si no existe o no define $pdo, conecta con valores por defecto
     de XAMPP: root sin contraseña, DB 'mis_entrenos'.
   ============================================================ */
$pdo = null;
$configPath = __DIR__ . '/config.php';
if (file_exists($configPath)) {
    require $configPath; // se espera que defina $pdo
}

if (!$pdo instanceof PDO) {
    $dbHost = '127.0.0.1';
    $dbName = 'mis_entrenos';   // <-- cambialo si usás otro nombre
    $dbUser = 'root';
    $dbPass = '';

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
        exit('No puedo conectar a la base de datos: ' . $e->getMessage());
    }
}

/* ============================================================
   2) VALIDAR SUBIDA
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

if (!isset($_FILES['archivo'])) {
    exit('No se recibió ningún archivo.');
}

$err = $_FILES['archivo']['error'];
if ($err !== UPLOAD_ERR_OK) {
    $map = [
        UPLOAD_ERR_INI_SIZE   => 'El archivo excede upload_max_filesize en php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'El archivo excede MAX_FILE_SIZE del formulario.',
        UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente.',
        UPLOAD_ERR_NO_FILE    => 'No se subió ningún archivo.',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal.',
        UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir en disco.',
        UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida.',
    ];
    exit('Error al subir el archivo: ' . ($map[$err] ?? ('código ' . $err)));
}

$original = $_FILES['archivo']['name'] ?? 'sin_nombre.fit';
$tmp      = $_FILES['archivo']['tmp_name'] ?? '';
$size     = (int)($_FILES['archivo']['size'] ?? 0);

/* ============================================================
   3) VALIDACIONES BÁSICAS DEL ARCHIVO
   ============================================================ */
if (!is_uploaded_file($tmp)) {
    exit('El archivo temporal no es válido.');
}

$ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
if ($ext !== 'fit') {
    exit('Sólo se permiten archivos .fit');
}

// El tipo MIME de los .fit suele ser "application/octet-stream".
// Igual lo tomamos informativo (no confiable), pero lo guardamos.
$mime = @mime_content_type($tmp) ?: 'application/octet-stream';

// (Opcional) límite de tamaño, por ejemplo 20 MB:
$MAX_MB = 20;
if ($size <= 0 || $size > $MAX_MB * 1024 * 1024) {
    exit('Tamaño inválido. Máximo permitido: ' . $MAX_MB . ' MB');
}

/* ============================================================
   4) EVITAR DUPLICADOS
   ============================================================ */
$md5  = md5_file($tmp);
$sha1 = sha1_file($tmp);

try {
    $stmt = $pdo->prepare("SELECT id FROM fit_files WHERE md5_hash = ?");
    $stmt->execute([$md5]);
    $existente = $stmt->fetchColumn();
    if ($existente) {
        echo "Este archivo ya estaba subido (ID {$existente}).";
        echo '<br><a href="index.php">Volver</a>';
        exit;
    }
} catch (Throwable $e) {
    exit('Error al verificar duplicados: ' . $e->getMessage());
}

/* ============================================================
   5) MOVER A /uploads (crear si no existe)
   ============================================================ */
$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) {
    if (!mkdir($uploadsDir, 0777, true) && !is_dir($uploadsDir)) {
        exit('No pude crear la carpeta /uploads');
    }
}

// Nombre único para guardar el archivo
$stored  = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.fit';
$destAbs = $uploadsDir . '/' . $stored;         // ruta absoluta
$destRel = 'uploads/' . $stored;                 // ruta relativa para guardar en BD

if (!move_uploaded_file($tmp, $destAbs)) {
    exit('No se pudo mover el archivo a /uploads.');
}

/* ============================================================
   6) GUARDAR EN BD
   ============================================================ */
try {
    $stmt = $pdo->prepare("
        INSERT INTO fit_files (
            original_name, stored_name, path, size_bytes,
            mime_type, md5_hash, sha1_hash
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $original,
        $stored,
        $destRel,
        $size,
        $mime,
        $md5,
        $sha1
    ]);

    $fitId = $pdo->lastInsertId();
} catch (Throwable $e) {
    // Si falla el insert, borramos el archivo físico para no dejar basura
    @unlink($destAbs);
    exit('Error al guardar en la BD: ' . $e->getMessage());
}

/* ============================================================
   7) RESPUESTA SIMPLE
   ============================================================ */
echo "Archivo recibido y guardado correctamente.<br>";
echo "ID en la base: <strong>{$fitId}</strong><br>";
echo "Nombre original: {$original}<br>";
echo "Guardado como: {$destRel}<br>";
echo "Tamaño: {$size} bytes<br>";
echo "MD5: {$md5}<br>";
echo "SHA1: {$sha1}<br><br>";
echo '<a href="index.php">Volver y subir otro</a>';