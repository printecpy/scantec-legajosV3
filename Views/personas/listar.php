<?php encabezado(); ?>
<?php
$personas = $data['personas'] ?? [];
$termino = $data['termino'] ?? '';
$estado = $data['estado'] ?? '';
$token = $_SESSION['csrf_token'] ?? '';
?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-address-book mr-3"></i>Personas
                </h1>
                <p class="text-sm text-gray-500 mt-1">Clientes y empleados vinculables a legajos.</p>
            </div>
            <button type="button" id="btnNuevaPersona"
                class="px-5 py-2.5 bg-scantec-blue text-white rounded-lg font-bold shadow-sm hover:bg-blue-800 transition-all">
                <i class="fas fa-plus mr-2"></i>Nueva persona
            </button>
        </div>

        <form action="<?php echo base_url(); ?>personas/listar" method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 md:items-end">
                <div class="md:col-span-5">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Buscar</label>
                    <input type="text" name="termino" value="<?php echo htmlspecialchars($termino); ?>"
                        placeholder="Apellido, nombre, CI, correo o celular"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estado</label>
                    <select name="estado" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-scantec-blue outline-none">
                        <option value="">Todos</option>
                        <option value="activo" <?php echo $estado === 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $estado === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-gray-800 text-white rounded-lg font-bold hover:bg-black text-center">Filtrar</button>
                    <a href="<?php echo base_url(); ?>personas/listar" class="flex-1 px-4 py-2 bg-white border border-gray-300 rounded-lg font-bold text-gray-700 hover:bg-gray-50 text-center">Limpiar</a>
                </div>
            </div>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Persona</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">CI</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Contacto</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <?php if (empty($personas)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 font-semibold">No hay personas para mostrar.</td>
                        </tr>
                        <?php endif; ?>
                        <?php foreach ($personas as $persona): ?>
                        <?php
                        $estadoPersona = strtolower((string)($persona['estado'] ?? 'activo'));
                        $tipoPersona = strtolower((string)($persona['tipo_persona'] ?? 'cliente'));
                        ?>
                        <tr class="hover:bg-gray-50"
                            data-persona='<?php echo htmlspecialchars(json_encode($persona, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>'>
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-800"><?php echo htmlspecialchars(($persona['apellido'] ?? '') . ', ' . ($persona['nombre'] ?? '')); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($persona['direccion'] ?? ''); ?></div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-semibold"><?php echo htmlspecialchars($persona['ci'] ?? ''); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <div><?php echo htmlspecialchars($persona['celular'] ?? ''); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($persona['correo'] ?? ''); ?></div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <span class="font-bold uppercase"><?php echo htmlspecialchars($tipoPersona); ?></span>
                                <?php if ($tipoPersona === 'empleado' && trim((string)($persona['cargo'] ?? '')) !== ''): ?>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($persona['cargo']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo $estadoPersona === 'activo' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700'; ?>">
                                    <?php echo htmlspecialchars($estadoPersona); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo base_url(); ?>legajos/buscar_legajos?termino=<?php echo urlencode($persona['ci'] ?? ''); ?>" 
                                       class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-800 shadow-sm transition-colors flex items-center"
                                       title="Ver legajos de esta persona">
                                        <i class="fas fa-layer-group mr-1.5"></i> Legajos
                                    </a>
                                    <button type="button" class="btnEditarPersona px-3 py-2 bg-scantec-blue text-white rounded-lg text-xs font-bold hover:bg-blue-800 shadow-sm flex items-center">
                                        <i class="fas fa-edit mr-1.5"></i> Editar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div id="modalPersona" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl overflow-hidden">
        <div class="px-5 py-4 bg-scantec-blue text-white flex justify-between items-center">
            <h2 id="modalPersonaTitulo" class="font-bold uppercase text-sm">Nueva persona</h2>
            <button type="button" id="btnCerrarPersona" class="w-8 h-8 rounded-lg hover:bg-white/10">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="<?php echo base_url(); ?>personas/guardar" method="POST" class="p-5">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="hidden" name="id_persona" id="id_persona" value="0">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Apellido *</label>
                    <input type="text" name="apellido" id="apellido" required maxlength="100" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                </div>
                <div class="md:col-span-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre *</label>
                    <input type="text" name="nombre" id="nombre" required maxlength="100" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CI *</label>
                    <input type="text" name="ci" id="ci" required maxlength="30" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Celular</label>
                    <input type="text" name="celular" id="celular" maxlength="40" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cumpleanos</label>
                    <input type="date" name="fecha_cumpleanos" id="fecha_cumpleanos" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                </div>
                <div class="md:col-span-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Correo</label>
                    <input type="email" name="correo" id="correo" maxlength="150" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                </div>
                <div class="md:col-span-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Direccion</label>
                    <input type="text" name="direccion" id="direccion" maxlength="255" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo *</label>
                    <select name="tipo_persona" id="tipo_persona" required class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-scantec-blue outline-none">
                        <option value="cliente">Cliente</option>
                        <option value="empleado">Empleado</option>
                    </select>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cargo</label>
                    <input type="text" name="cargo" id="cargo" maxlength="120" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estado</label>
                    <select name="estado" id="estado" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-scantec-blue outline-none">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-5">
                <button type="button" id="btnCancelarPersona" class="px-5 py-2 bg-white border border-gray-300 rounded-lg font-bold text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-scantec-blue text-white rounded-lg font-bold hover:bg-blue-800">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalPersona');
    const titulo = document.getElementById('modalPersonaTitulo');
    const campos = ['id_persona', 'apellido', 'nombre', 'ci', 'celular', 'correo', 'direccion', 'tipo_persona', 'cargo', 'fecha_cumpleanos', 'estado'];
    
    const tipoPersonaSelect = document.getElementById('tipo_persona');
    const inputCargo = document.getElementById('cargo');
    const containerCargo = inputCargo ? inputCargo.closest('div') : null;

    const toggleCargo = () => {
        if (!containerCargo) return;
        if (tipoPersonaSelect.value === 'empleado') {
            containerCargo.style.display = 'block';
        } else {
            containerCargo.style.display = 'none';
        }
    };

    if (tipoPersonaSelect) {
        tipoPersonaSelect.addEventListener('change', toggleCargo);
    }

    const inputCi = document.getElementById('ci');
    if (inputCi) {
        inputCi.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value) {
                e.target.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            } else {
                e.target.value = "";
            }
        });
    }

    const abrir = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        toggleCargo();
    };
    
    const cerrar = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };
    
    const limpiar = () => {
        campos.forEach((campo) => {
            const input = document.getElementById(campo);
            if (input) {
                input.value = campo === 'id_persona' ? '0' : '';
            }
        });
        document.getElementById('tipo_persona').value = 'cliente';
        document.getElementById('estado').value = 'activo';
        titulo.textContent = 'Nueva persona';
        toggleCargo();
    };

    const btnNuevaPersona = document.getElementById('btnNuevaPersona');
    if (btnNuevaPersona) {
        btnNuevaPersona.addEventListener('click', function () {
            limpiar();
            abrir();
        });
    }

    const btnCerrarPersona = document.getElementById('btnCerrarPersona');
    if (btnCerrarPersona) {
        btnCerrarPersona.addEventListener('click', cerrar);
    }
    
    const btnCancelarPersona = document.getElementById('btnCancelarPersona');
    if (btnCancelarPersona) {
        btnCancelarPersona.addEventListener('click', cerrar);
    }

    document.querySelectorAll('.btnEditarPersona').forEach((boton) => {
        boton.addEventListener('click', function () {
            const fila = boton.closest('tr');
            const persona = JSON.parse(fila.dataset.persona || '{}');
            campos.forEach((campo) => {
                const input = document.getElementById(campo);
                if (input) {
                    if (campo === 'ci') {
                        let ciStr = String(persona[campo] || '').replace(/\D/g, '');
                        input.value = ciStr ? ciStr.replace(/\B(?=(\d{3})+(?!\d))/g, ".") : '';
                    } else {
                        input.value = persona[campo] || '';
                    }
                }
            });
            titulo.textContent = 'Editar persona';
            abrir();
        });
    });
    
    // Initialize initial state immediately
    toggleCargo();
});
</script>

<?php pie(); ?>
