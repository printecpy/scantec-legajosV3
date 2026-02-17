<?php encabezado(); // Assuming this includes necessary headers, CSS, and JS ?>

<style>
/* Add any specific styles you need here or keep them in your CSS file */
.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 35px; /* Adjust size as needed */
    height: 35px;
    padding: 0;
    margin: 2px;
    border: none;
    border-radius: 5px;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    text-decoration: none; /* Remove underline from links styled as buttons */
}
.btn-icon i {
    margin: 0; /* Center icon if needed */
}
.btn-icon-new { background-color: #28a745; }
.btn-icon-new:hover { background-color: #218838; }
.btn-icon-pdf { background-color: #dc3545; }
.btn-icon-pdf:hover { background-color: #c82333; }
.btn-icon-excel { background-color: #17a2b8; } /* Using info color for Excel */
.btn-icon-excel:hover { background-color: #117a8b; }
.btn-icon-edit { background-color: #ffc107; color: #212529; } /* Warning color */
.btn-icon-edit:hover { background-color: #e0a800; }
.btn-icon-delete { background-color: #dc3545; } /* Danger color */
.btn-icon-delete:hover { background-color: #c82333; }
.btn-icon-reingres { background-color: #6f42c1; } /* Custom purple */
.btn-icon-reingres:hover { background-color: #5b36a1; }

.thead {
    background-color: #e9ecef; /* Light gray background for headers */
}

/* Adjustments for the import form to align better */
.import-form {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem; /* Space between file input and button */
    margin-left: 0.5rem; /* Spacing from other buttons */
}
.import-form input[type="file"] {
    /* Optional: Style file input if needed */
    /* Example: width: 200px; */
}

</style>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid mt-4"> <h4 class="text-center mb-4">Gestor de Alertas Programadas</h4> 
            <ul class="nav nav-tabs" id="alertTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tareas-tab" data-toggle="tab" href="#tareas" role="tab"
                       aria-controls="tareas" aria-selected="true">Tareas Programadas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="historial-tab" data-toggle="tab" href="#historial" role="tab"
                       aria-controls="historial" aria-selected="false">Historial de Envíos</a>
                </li>
            </ul>

            <div class="tab-content pt-3" id="alertTabContent"> 
                
            <div class="tab-pane fade show active" id="tareas" role="tabpanel" aria-labelledby="tareas-tab">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <div class="mb-3"> <button title="Registrar Tarea" class="btn-icon btn-icon-new" type="button" data-toggle="modal" data-target="#nuevaTareaModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <a target="_blank" href="<?php echo base_url(); ?>tareas/pdf" title="Generar informe en PDF" class="btn-icon btn-icon-pdf">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a target="_blank" href="<?php echo base_url(); ?>tareas/excel" title="Generar informe en Excel" class="btn-icon btn-icon-excel">
                                    <i class="fas fa-file-excel"></i>
                                </a>
                                </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm" id="tableTareas"> <thead class="thead">
                                        <tr class="small">
                                            <th>ID</th>
                                            <th>Nombre Tarea</th>
                                            <th>Tipo Informe</th>
                                            <th>Frecuencia</th>
                                            <th>Próxima Ejecución</th>
                                            <th>Última Ejecución</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data['tareas'])): ?>
                                            <?php foreach ($data['tareas'] as $tarea): ?>
                                                <tr class="small">
                                                    <td><?php echo $tarea['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($tarea['nombre_tarea']); ?></td>
                                                    <td><?php echo htmlspecialchars($tarea['tipo_informe']); ?></td>
                                                    <td><?php echo htmlspecialchars($tarea['frecuencia']); ?></td>
                                                    <td><?php echo !empty($tarea['fecha_proxima_ejecucion']) ? date('d/m/Y H:i', strtotime($tarea['fecha_proxima_ejecucion'])) : 'N/A'; ?></td>
                                                    <td><?php echo !empty($tarea['fecha_ultima_ejecucion']) ? date('d/m/Y H:i', strtotime($tarea['fecha_ultima_ejecucion'])) : 'Nunca'; ?></td>
                                                    <td>
                                                        <?php if ($tarea['activa'] == 1): ?>
                                                            <span class="badge badge-success">Activa</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Inactiva</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button onclick="openRecipientsModal('<?php echo $tarea['id']; ?>', '<?php echo htmlspecialchars(addslashes($tarea['nombre_tarea'])); ?>')" class="btn-icon btn-primary" title="Ver/Editar Destinatarios">
                                                            <i class="fas fa-users"></i>
                                                        </button>
                                                        
                                                        <button onclick="openTaskModal(<?php echo htmlspecialchars(json_encode($tarea), ENT_QUOTES, 'UTF-8'); ?>)" class="btn-icon btn-icon-edit" title="Editar Tarea">
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <?php if ($tarea['activa'] == 1): ?>
                                                        <form action="<?php echo base_url(); ?>Alerta/desactivar" method="post" class="d-inline eliminar-form">
                                                            <input type="hidden" name="id" value="<?php echo $tarea['id']; ?>">
                                                            <button type="submit" class="btn-icon btn-icon-delete" title="Desactivar Tarea">
                                                                <i class="fas fa-toggle-off"></i> </button>
                                                        </form>
                                                        <?php else: ?>
                                                        <form action="<?php echo base_url(); ?>Alerta/activar" method="post" class="d-inline reingresar-form">
                                                            <input type="hidden" name="id" value="<?php echo $tarea['id']; ?>">
                                                            <button type="submit" class="btn-icon btn-icon-reingres" title="Activar Tarea">
                                                                <i class="fas fa-toggle-on"></i> </button>
                                                        </form>
                                                        <?php endif; ?>
                                                        </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No hay tareas programadas.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><div class="tab-pane fade" id="historial" role="tabpanel" aria-labelledby="historial-tab">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                             <div class="mb-3"> <a target="_blank" href="<?php echo base_url(); ?>AlertaHistorial/pdf" title="Generar informe en PDF" class="btn-icon btn-icon-pdf">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a target="_blank" href="<?php echo base_url(); ?>AlertaHistorial/excel" title="Generar informe en Excel" class="btn-icon btn-icon-excel">
                                    <i class="fas fa-file-excel"></i>
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-sm" id="tableHistorial">
                                    <thead class="thead">
                                        <tr class="small">
                                            <th>ID Tarea</th>
                                            <th>Nombre Tarea</th> <th>Destinatario</th>
                                            <th>Fecha Envío</th>
                                            <th>Estado</th>
                                            <th>Detalle</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data['historial'])): ?>
                                            <?php 
                                            // Create a map of task IDs to names for easy lookup
                                            $taskNames = array_column($data['tareas'], 'nombre_tarea', 'id');
                                            ?>
                                            <?php foreach ($data['historial'] as $envio): ?>
                                                <tr class="small">
                                                    <td><?php echo $envio['id_tarea_programada']; ?></td>
                                                    <td><?php echo htmlspecialchars($taskNames[$envio['id_tarea_programada']] ?? 'Tarea Desconocida'); ?></td>
                                                    <td><?php echo htmlspecialchars($envio['correo_destino']); ?></td>
                                                    <td><?php echo date('d/m/Y H:i:s', strtotime($envio['fecha_envio'])); ?></td> <td>
                                                        <?php if ($envio['estado'] === 'Exitoso'): ?>
                                                            <span class="badge badge-success">Exitoso</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Error</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($envio['detalle']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No hay historial de envíos disponible.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div></div> </div> </main>

    </div> <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Simple function to switch views (can be replaced with Bootstrap's built-in tab JS)
    function setView(viewId) {
        // This function might not be necessary if Bootstrap's data-toggle="tab" is working correctly.
        // If tabs aren't switching, ensure Bootstrap JS is loaded *after* jQuery.
        
        // Example: manually switching tabs if needed
        // $('.nav-tabs a').removeClass('active');
        // $('.tab-pane').removeClass('show active');
        // $(`#${viewId}-tab`).addClass('active');
        // $(`#${viewId}`).addClass('show active');
    }

    // Placeholder functions for modal interactions (you'll need to adapt these)
    function openTaskModal(task = null) {
        // Reset form
        $('#task-form')[0].reset(); 
        
        if (task) {
            // Populate form for editing
            $('#task-modal-title').text('Editar Tarea');
            $('#task-id').val(task.id);
            $('#task-nombre').val(task.nombre_tarea);
            $('#task-tipo').val(task.tipo_informe);
            $('#task-frecuencia').val(task.frecuencia);
            $('#task-activa').prop('checked', task.activa == 1); // Assuming 1 is active
             
            // Add logic here to potentially disable the 'tipo_informe' select
            // based on the 'isUserRoot()' function and if the task has 'solo_root' = true
            const isRoot = isUserRoot('<?php echo $_SESSION['id_usuario']; // Pass user ID ?>'); // Replace with your actual user ID variable
            const selectedReportInfo = catalogoInformes.find(report => report.clave_php === task.tipo_informe);
            const isRootOnlyReport = selectedReportInfo && selectedReportInfo.solo_root;

            if (isRootOnlyReport && !isRoot) {
                $('#task-tipo').prop('disabled', true);
                 // Optionally add a message indicating why it's disabled
            } else {
                 $('#task-tipo').prop('disabled', false);
            }

        } else {
            // Setup for creating a new task
            $('#task-modal-title').text('Crear Nueva Tarea');
            $('#task-id').val('');
            $('#task-activa').prop('checked', true); // Default to active
            $('#task-tipo').prop('disabled', false); // Ensure enabled for new tasks
        }
        $('#task-modal').modal('show'); // Use Bootstrap's modal function
    }

    function closeTaskModal() {
        $('#task-modal').modal('hide'); // Use Bootstrap's modal function
    }

    // --- Placeholder for openRecipientsModal and closeRecipientsModal ---
    // You'll need to adapt these based on how you implemented the Firestore version
    // or create new modals specific to your PHP/MySQL setup.

    function openRecipientsModal(taskId, taskName) {
        selectedTaskId = taskId; // Make sure selectedTaskId is declared globally if needed
        $('#recipient-task-name').text(taskName);
        $('#recipients-modal').modal('show'); // Use Bootstrap's modal function
        // You'll need an AJAX call here to load the recipients for this task ID
        loadRecipientsForTask(taskId); 
    }

    function closeRecipientsModal() {
        $('#recipients-modal').modal('hide'); // Use Bootstrap's modal function
        selectedTaskId = null;
        $('#recipients-list').html(''); // Clear the list
    }
    
    // Placeholder function to load recipients via AJAX
    async function loadRecipientsForTask(taskId) {
        const listContainer = document.getElementById('recipients-list');
        listContainer.innerHTML = '<p class="text-center text-gray-500">Cargando...</p>'; // Loading indicator
        
        try {
            // Replace with your actual endpoint to fetch recipients
            const response = await fetch(`<?php echo base_url(); ?>Alerta/getDestinatarios/${taskId}`); 
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const recipients = await response.json(); 

            // Clear previous list
            listContainer.innerHTML = ''; 

            if (recipients.length === 0) {
                listContainer.innerHTML = '<p class="text-gray-500 text-sm p-4 text-center border-dashed border-2 rounded-lg">No hay destinatarios. Añade el primer correo.</p>';
            } else {
                recipients.forEach(d => {
                    const item = document.createElement('div');
                    item.className = 'flex justify-between items-center bg-gray-50 p-3 rounded-lg border';
                    item.innerHTML = `
                        <span class="font-medium text-gray-700">${d.correo_destino}</span>
                        <button onclick="deleteRecipient('${d.id}')" class="text-red-500 hover:text-red-700 transition duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    `;
                    listContainer.appendChild(item);
                });
            }
        } catch (error) {
            console.error('Error fetching recipients:', error);
            listContainer.innerHTML = '<p class="text-red-500 text-sm p-4 text-center">Error al cargar destinatarios.</p>';
        }
    }


    // --- Placeholder functions for CRUD operations on recipients (AJAX) ---
    // You'll need PHP controller methods for these too
    
    document.getElementById('recipient-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('recipient-email').value;
        if (!selectedTaskId || !email) return;

        const formData = new FormData();
        formData.append('id_tarea_programada', selectedTaskId);
        formData.append('correo_destino', email);
        // Add CSRF token if needed: formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

        try {
            const response = await fetch('<?php echo base_url(); ?>Alerta/agregarDestinatario', { // Adjust URL
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                document.getElementById('recipient-email').value = ''; // Clear input
                alertMessage('Destinatario añadido.', 'success');
                // Reload recipients for the current task
                loadRecipientsForTask(selectedTaskId); 
            } else {
                alertMessage(result.message || 'Error al añadir destinatario.', 'error');
            }
        } catch (error) {
            console.error("Error al añadir destinatario:", error);
            alertMessage('Error de conexión al añadir destinatario.', 'error');
        }
    });

    window.deleteRecipient = async (recipientId) => {
         if (!window.confirm("¿Estás seguro de que deseas eliminar este destinatario?")) {
             return;
         }
        try {
            const formData = new FormData();
            formData.append('id', recipientId);
             // Add CSRF token if needed: formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            const response = await fetch('<?php echo base_url(); ?>Alerta/eliminarDestinatario', { // Adjust URL
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                alertMessage('Destinatario eliminado.', 'success');
                // Reload recipients for the current task
                loadRecipientsForTask(selectedTaskId); 
            } else {
                alertMessage(result.message || 'Error al eliminar destinatario.', 'error');
            }
        } catch (error) {
            console.error("Error al eliminar destinatario:", error);
            alertMessage('Error de conexión al eliminar destinatario.', 'error');
        }
    };
    
    // --- Initial setup ---
    // We don't need setupFirebase anymore since we're using PHP to load initial data.
    // Call renderUI to display the default tab ('tareas') when the page loads.
    document.addEventListener('DOMContentLoaded', () => {
         // Initialize tabs (make sure Bootstrap JS is loaded)
        $('#alertTabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
        
        // Set initial view (optional, Bootstrap handles this via 'active' class)
        setView('tareas'); 
        
        // Add event listeners for delete/reactivate forms (using event delegation for dynamically added rows)
        $(document).on('submit', '.eliminar-form', function(e) {
            e.preventDefault();
            if (confirm('¿Está seguro de desactivar esta tarea?')) {
                this.submit();
            }
        });
        
        $(document).on('submit', '.reingresar-form', function(e) {
            e.preventDefault();
             if (confirm('¿Está seguro de activar esta tarea?')) {
                this.submit();
            }
        });

        // Initialize DataTables (Optional but recommended for large tables)
        // You'll need to include DataTables library for this
        // $('#tableTareas').DataTable();
        // $('#tableHistorial').DataTable();
        
    });

    </script>
    
</div>
</main>
<?php pie() ?>