<?php encabezado($data); ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-network-wired mr-3"></i> Dependencias
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Gestión de grupos y tipos de documentos del sistema.
                </p>
            </div>

            <div class="flex gap-3">
                <button onclick="toggleModal('modalNuevoGrupo')"
                    class="bg-scantec-blue hover:bg-gray-800 text-white font-bold py-2 px-6 rounded-xl shadow-lg transition-all transform hover:scale-105 flex items-center">
                    <i class="fas fa-users mr-2"></i> Nuevo Grupo
                </button>
                <button onclick="toggleModal('modalNuevoTipoDoc')"
                    class="bg-white hover:bg-gray-50 text-slate-900 border border-gray-200 font-bold py-2 px-6 rounded-xl shadow-lg transition-all transform hover:scale-105 flex items-center">
                    <i class="fas fa-folder-plus mr-2 text-slate-600"></i> Nuevo Doc
                </button>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'errorPermiso') { ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r shadow-sm flex items-center animate-pulse" role="alert">
                <div class="flex-shrink-0 text-red-500">
                    <i class="fas fa-exclamation-circle fa-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-bold">Acceso Denegado</p>
                    <p class="text-sm text-red-600">No tienes permiso para realizar esta acción.</p>
                </div>
                <button type="button" class="ml-auto text-red-400 hover:text-red-600" onclick="this.parentElement.remove();">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php } ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                Asignar Permisos y Relaciones
            </h2>

            <div class="flex flex-col lg:flex-row justify-between items-end gap-6">
                <form action="<?php echo base_url(); ?>usuarios/asignar_permisos" method="POST" class="w-full lg:w-3/4">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Grupo</label>
                            <select name="id_grupo" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue bg-white">
                                <?php foreach ($data['grupos'] as $g) { ?>
                                    <option value="<?php echo $g['id_grupo']; ?>">
                                        <?php echo $g['descripcion']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Documento</label>
                            <select name="id_tipoDoc" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue bg-white">
                                <?php foreach ($data['tipos_documentos'] as $t) { ?>
                                    <option value="<?php echo $t['id_tipoDoc']; ?>">
                                        <?php echo $t['nombre_tipoDoc']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div>
                            <button type="submit"
                                class="w-full bg-scantec-blue hover:bg-gray-800 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center justify-center">
                                <i class="fas fa-link mr-2"></i> Vincular
                            </button>
                        </div>
                    </div>
                </form>

                <div class="flex items-center gap-2">
                    <a target="_blank" href="<?php echo base_url(); ?>usuarios/grupo_pdf"
                        class="px-3 py-1.5 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 text-sm font-bold">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                    <a target="_blank" href="<?php echo base_url(); ?>usuarios/grupo_excel"
                        class="px-3 py-1.5 border border-green-200 text-green-600 rounded-lg hover:bg-green-50 text-sm font-bold">
                        <i class="fas fa-file-excel"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h5 class="font-bold text-slate-800">Listado de Permisos</h5>
                <span class="px-2 py-1 bg-slate-200 text-slate-700 rounded text-xs font-bold">
                    Total: <?php echo count($data['permisos']); ?>
                </span>
            </div>
            
            <div class="table-container">
                <table class="scantec-table" id="table">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Documento Asociado</th>
                            <th class="text-center">Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['permisos'] as $p) {
                            // Detección de estado (Numérico o Texto)
                            $estado = isset($p['estado_permiso']) ? $p['estado_permiso'] : 0;
                            // Normalizamos: es activo si es '1', 1, o 'ACTIVO'
                            $activo = ($estado == 1 || strtoupper((string)$estado) === 'ACTIVO');
                            
                            $rowClass = $activo ? '' : 'bg-gray-50 opacity-75';
                        ?>
                            <tr class="<?php echo $rowClass; ?>">
                                <td class="font-bold text-gray-800">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-blue-50 text-scantec-blue flex items-center justify-center mr-3 border border-blue-100">
                                            <i class="fas fa-users text-xs"></i>
                                        </div>
                                        <?php echo $p['descripcion']; ?>
                                    </div>
                                </td>

                                <td class="text-gray-600">
                                    <div class="flex items-center">
                                        <i class="far fa-file-alt mr-2 text-gray-400"></i>
                                        <?php echo $p['nombre_tipoDoc']; ?>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <?php if ($activo): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold border border-green-200">
                                            Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold border border-red-200">
                                            Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-right">
                                    <div class="flex justify-end space-x-2">
                                        <?php if ($activo): ?>
                                            <form action="<?php echo base_url(); ?>usuarios/eliminar_permiso" method="POST" class="inline eliminar">
                                                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="id_permiso" value="<?php echo $p['id']; ?>">
                                                <button type="submit" class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center border border-red-100 hover:bg-white hover:shadow-md transition-all" title="Desactivar">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?php echo base_url(); ?>usuarios/reactivar_permiso" method="POST" class="inline reingresar">
                                                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="id_permiso" value="<?php echo $p['id']; ?>">
                                                <button type="submit" class="w-8 h-8 rounded-full bg-green-50 text-green-500 flex items-center justify-center border border-green-100 hover:bg-white hover:shadow-md transition-all" title="Reactivar">
                                                    <i class="fas fa-undo text-xs"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div id="modalNuevoGrupo" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-lg border border-gray-100">
            <div class="bg-scantec-blue px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold leading-6 text-white tracking-wide">
                    <i class="fas fa-users mr-2"></i> Nuevo Grupo
                </h3>
                <button type="button" onclick="toggleModal('modalNuevoGrupo')" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="<?php echo base_url() ?>usuarios/registrar_grupo" method="post" id="frmGrupo" autocomplete="off">
                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="px-6 py-6 bg-gray-50">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <label class="block text-xs font-bold text-scantec-blue uppercase mb-2">Nombre del Grupo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-tag text-gray-400"></i></div>
                            <input type="text" id="descripcion" name="descripcion" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all" placeholder="Ej: Contabilidad" required>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent bg-scantec-blue px-6 py-2 text-base font-bold text-white shadow-sm hover:bg-gray-800 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">Registrar</button>
                    <button type="button" onclick="toggleModal('modalNuevoGrupo')" class="mt-3 inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-6 py-2 text-base font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modalNuevoTipoDoc" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-2xl border border-gray-100">
            <div class="bg-scantec-blue px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold leading-6 text-white tracking-wide">
                    <i class="fas fa-folder-plus mr-2"></i> Nuevo Tipo de Documento
                </h3>
                <button type="button" onclick="toggleModal('modalNuevoTipoDoc')" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="<?php echo base_url() ?>usuarios/registrar_tipoDoc" method="post" id="frmTipoDoc" autocomplete="off">
                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="px-6 py-6 bg-gray-50">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-scantec-blue uppercase mb-2">Nombre del Documento</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="far fa-file-alt text-gray-400"></i></div>
                                <input type="text" id="nombre_tipoDoc" name="nombre_tipoDoc" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" placeholder="Ej: Factura Proveedor" required>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 my-4 pt-4">
                            <h6 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Definición de Metadatos (Índices)</h6>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Índice <?php echo $i; ?></label>
                                        <input type="text" name="indice_<?php echo $i; ?>" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm" placeholder="Etiqueta...">
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent bg-scantec-blue px-6 py-2 text-base font-bold text-white shadow-sm hover:bg-gray-800 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">Registrar Doc</button>
                    <button type="button" onclick="toggleModal('modalNuevoTipoDoc')" class="mt-3 inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-6 py-2 text-base font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php pie() ?>

<script>
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        if (modal) {
            modal.classList.toggle("hidden");
        }
    }
</script>

<?php if (isset($_SESSION['alert'])) { ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: '<?php echo $_SESSION['alert']['type']; ?>',
                title: '<?php echo $_SESSION['alert']['message']; ?>',
                showConfirmButton: true,
                confirmButtonColor: '#182541',
                timer: 5000
            });
        });
    </script>
    <?php unset($_SESSION['alert']);
} ?>