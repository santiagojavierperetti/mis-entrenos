<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subir entrenamiento .FIT</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<main>
    <header>
        <h1>Subí tu entrenamiento .FIT</h1>
        <p>
            Importá tus actividades en formato <code>.fit</code> para analizarlas y guardarlas en la base de datos.
            El archivo no debe superar los <strong>20&nbsp;MB</strong> y debe provenir de un dispositivo confiable.
        </p>
    </header>

<h1>Subir archivo .fit</h1>
    <form id="upload-form" class="section-card" action="procesar.php" method="post" enctype="multipart/form-data" novalidate>
        <div class="upload-area">
            <input id="archivo" class="visually-hidden" type="file" name="archivo" accept=".fit,application/octet-stream" required data-max-size="20971520">
            <label class="upload-dropzone" for="archivo" tabindex="0">
                <span class="dropzone-title">Seleccioná un archivo FIT</span>
                <span class="dropzone-subtitle">Arrastrá y soltá o hacé clic para elegirlo desde tu dispositivo.</span>
                <span id="selected-file" class="dropzone-selected" data-default="Todavía no seleccionaste ningún archivo.">Todavía no seleccionaste ningún archivo.</span>
            </label>
        </div>
        <p class="form-hint">
            Aceptamos únicamente archivos con extensión <code>.fit</code>. Si necesitás ayuda para preparar tu base de datos, consultá el README del proyecto.
        </p>
        <p id="client-error" class="form-hint alert alert--error" role="alert" hidden></p>
        <div class="actions">
            <button type="submit">Subir entrenamiento</button>
        </div>
        <small>Los archivos se almacenan de forma privada en el servidor y se registran con hashes MD5/SHA1 para evitar duplicados.</small>
    </form>

<form action="procesar.php" method="post" enctype="multipart/form-data">
    <label>Seleccionar archivo FIT:</label><br>
    <input type="file" name="archivo" accept=".fit" required>
    <br><br>
    <button type="submit">Subir entrenamiento</button>
</form>
    <section class="section-card" aria-labelledby="pasos-titulo">
        <h2 id="pasos-titulo">¿Qué sucede después?</h2>
        <ul class="card-list">
            <li>
                <strong>Validación inmediata</strong>
                Revisamos el tamaño, la extensión y que el archivo provenga de una subida auténtica.
            </li>
            <li>
                <strong>Verificación de duplicados</strong>
                Comparamos los hashes con la base de datos para evitar subir la misma actividad dos veces.
            </li>
            <li>
                <strong>Registro en la base</strong>
                Guardamos los metadatos y dejamos el archivo disponible para análisis posteriores.
            </li>
        </ul>
    </section>

    <footer>
        ¿Necesitás ayuda para configurar la base de datos? Revisá el README incluido en el proyecto.
    </footer>
</main>

<script>
(() => {
    const form = document.getElementById('upload-form');
    const fileInput = document.getElementById('archivo');
    const errorBox = document.getElementById('client-error');
    const selectedFile = document.getElementById('selected-file');
    const dropzone = document.querySelector('.upload-dropzone');
    const maxBytes = Number(fileInput.dataset.maxSize || 0);
    const defaultSelectedText = selectedFile.dataset.default || '';

    function showError(message) {
        if (!message) {
            errorBox.hidden = true;
            errorBox.textContent = '';
            return;
        }
        errorBox.textContent = message;
        errorBox.hidden = false;
    }

    function updateSelectedFile() {
        const file = fileInput.files && fileInput.files.length ? fileInput.files[0] : null;
        if (!file) {
            selectedFile.textContent = defaultSelectedText;
            dropzone.classList.remove('upload-dropzone--has-file');
            return null;
        }
        selectedFile.textContent = `Archivo seleccionado: ${file.name}`;
        dropzone.classList.add('upload-dropzone--has-file');
        return file;
    }

    form.addEventListener('submit', (event) => {
        showError('');
        const file = updateSelectedFile();

        if (!file) {
            event.preventDefault();
            showError('Seleccioná un archivo antes de continuar.');
            fileInput.focus();
            return;
        }

        const lowerName = file.name.toLowerCase();
        if (!lowerName.endsWith('.fit')) {
            event.preventDefault();
            showError('El archivo debe tener extensión .fit.');
            fileInput.focus();
            return;
        }

        if (maxBytes && file.size > maxBytes) {
            event.preventDefault();
            const maxMB = (maxBytes / (1024 * 1024)).toFixed(0);
            showError(`El archivo supera el máximo permitido de ${maxMB} MB.`);
            fileInput.focus();
        }
    });

    fileInput.addEventListener('change', () => {
        showError('');
        updateSelectedFile();
    });

    ;['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('upload-dropzone--dragging');
        });
    });

    ;['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove('upload-dropzone--dragging');
        });
    });

    dropzone.addEventListener('drop', (event) => {
        const files = event.dataTransfer?.files;
        if (files && files.length) {
            fileInput.files = files;
            updateSelectedFile();
        }
    });

    dropzone.addEventListener('keydown', (event) => {
        if (event.key === ' ' || event.key === 'Enter') {
            event.preventDefault();
            fileInput.click();
        }
    });
})();
</script>
</body>
</html>