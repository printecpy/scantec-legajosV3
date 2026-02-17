<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container mt-5">
            <!-- Sección de Autenticación -->
            <div class="card mb-4">
                <h2>Interfaz para Firmar Documentos</h2>
                <div class="card-header">Autenticación</div>
                <div class="card-body">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de usuario</label>
                            <input type="text" class="form-control" id="username"
                                placeholder="Ingrese su nombre de usuario">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password"
                                placeholder="Ingrese su contraseña">
                        </div>
                        <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                    </form>
                </div>
            </div>

            <!-- Sección para Firmar Documentos -->
            <div class="card">
                <div class="card-header">Firmar Documentos</div>
                <div class="card-body">
                    <!-- Selector de nivel de firma -->
                    <div class="mb-3">
                        <label for="firmaLevel" class="form-label">Nivel de Firma</label>
                        <select class="form-select" id="firmaLevel">
                            <option value="T">Sello de Tiempo (T)</option>
                            <option value="BES">Firma Básica (BES)</option>
                        </select>
                    </div>

                    <!-- Subida de archivos -->
                    <div class="mb-3">
                        <label for="fileUpload" class="form-label">Seleccione archivos para firmar</label>
                        <input type="file" class="form-control" id="fileUpload" multiple>
                    </div>

                    <!-- Botón para iniciar la firma -->
                    <button class="btn btn-success" id="firmarBtn">Firmar Documentos</button>
                </div>
            </div>

            <!-- Tabla de estado de documentos -->
            <div class="card mt-4">
                <div class="card-header">Estado de los Documentos</div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nombre del Archivo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="estadoDocumentos">
                            <!-- Aquí se irán añadiendo los documentos -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        // Lógica para autenticarse y obtener token
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            // Llamada AJAX para obtener el token de autenticación
            // ...
        });

        // Lógica para firmar documentos
        document.getElementById('firmarBtn').addEventListener('click', function() {
            const nivelFirma = document.getElementById('firmaLevel').value;
            const archivos = document.getElementById('fileUpload').files;

            for (let archivo of archivos) {
                // Añadir el archivo a la tabla con el estado "En proceso"
                const fila = `<tr>
                <td>${archivo.name}</td>
                <td id="estado-${archivo.name}">En proceso...</td>
            </tr>`;
                document.getElementById('estadoDocumentos').innerHTML += fila;

                // Lógica para firmar el archivo mediante la API
                // Llamada AJAX para firmar el documento y actualizar el estado
                // ...

                // Cuando se firme el documento, cambiar el estado
                document.getElementById(`estado-${archivo.name}`).innerText = "Firmado";
            }
        });
        </script>
    </main>
    <?php pie() ?>