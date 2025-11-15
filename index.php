<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir entrenamiento .FIT</title>
</head>
<body>

<h1>Subir archivo .fit</h1>

<form action="procesar.php" method="post" enctype="multipart/form-data">
    <label>Seleccionar archivo FIT:</label><br>
    <input type="file" name="archivo" accept=".fit" required>
    <br><br>
    <button type="submit">Subir entrenamiento</button>
</form>

</body>
</html>
