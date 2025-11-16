<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subir entrenamiento .FIT</title>
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
                <div class="app-bar__subtitle">Carga y gestión de actividades</div>
            </div>
        </div>
    </div>
</header>

<main>
    <header class="page-header">
        <h1>Subí tu entrenamiento .FIT</h1>
        <p>
            Podés arrastrar y soltar el archivo desde tu computadora
            o seleccionarlo manualmente.
        </p>
    </header>

    <form id="upload-form" class="section-card" action="procesar.php"
          method="post" enctype="multipart/form-data" novalidate>
        <div class="upload-area">
            <input id="archivo" class="upload-input" type="file" name="archivo"
                   accept=".fit,application/octet-stream"
                   required data-max-size="20971520" aria-hidden="true">
            <div class="upload-dropzone" role="button" tabindex="0"
                 aria-controls="archivo" aria-describedby="selected-file">
                <span class="dropzone-title">Seleccioná un archivo FIT</span>
                <span class="dropzone-subtitle">
                    Arrastrá y soltá o hacé clic para elegirlo desde tu dispositivo.
                </span>
                <span id="selected-file" class="dropzone-selected"
                      data-default="Todavía no seleccionaste ningún archivo.">
                    Todavía no seleccionaste ningún archivo.
                </span>
            </div>
        </div>
        <p class="form-hint">
            Aceptamos únicamente archivos con extensión <code>.fit</code>
            de hasta <strong>20&nbsp;MB</strong>.
        </p>
        <p id="client-error" class="form-hint alert alert--error" role="alert" hidden></p>
        <div class="actions">
            <button type="submit">Subir entrenamiento</button>
        </div>
        <small>
            Los archivos se almacenan de forma privada y se registran hashes MD5/SHA1
            para evitar duplicados.
        </small>
    </form>

    <section class="section-card" aria-labelledby="pasos-titulo">
        <h2 id="pasos-titulo">¿Qué sucede después?</h2>
        <ul class="card-list">
            <li>
                <strong>Validación inmediata</strong>
                Revisamos tamaño, extensión y procedencia del archivo.
            </li>
            <li>
                <strong>Verificación de duplicados</strong>
                Comparamos los hashes con la base de datos para no repetir actividades.
            </li>
            <li>
                <strong>Registro en la base</strong>
                Guardamos los metadatos para análisis posteriores.
            </li>
        </ul>
    </section>

    <footer>
        ¿Necesitás ayuda para configurar la base de datos?
        Revisá el README incluido en el proyecto.
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

    dropzone.addEventListener('click', (event) => {
        event.preventDefault();
        fileInput.click();
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('upload-dropzone--dragging');
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
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
