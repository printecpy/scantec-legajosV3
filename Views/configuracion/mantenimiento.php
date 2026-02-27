<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8">

    <div class="container mx-auto px-4">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-montserrat font-bold text-scantec-blue uppercase tracking-wide">
                    <i class="fa fa-database mr-2"></i> Mantenimiento de Base de Datos
                </h1>
                <p class="text-sm text-gray-500 mt-1">Herramientas de respaldo, restauración y seguridad de la
                    información.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">
                <div class="p-6">
                    <div
                        class="w-12 h-12 rounded-xl bg-blue-50 text-scantec-blue flex items-center justify-center mb-4 group-hover:bg-scantec-blue group-hover:text-white transition-colors">
                        <i class="fa fa-download text-xl"></i>
                    </div>

                    <h3 class="font-bold text-gray-800 text-lg mb-2">Respaldo de BD</h3>
                    <p class="text-sm text-gray-500 mb-6 h-10">
                        Genera y descarga un archivo SQL con toda la información actual.
                    </p>

                    <form method="post" action="<?php echo base_url() ?>configuracion/backup">
                        <button type="submit"
                            class="w-full bg-white border-2 border-scantec-blue text-scantec-blue hover:bg-scantec-blue hover:text-white font-bold py-3 px-4 rounded-xl transition-all flex items-center justify-center">
                            <i class="fa fa-cloud-download mr-2"></i> Generar Backup
                        </button>
                    </form>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">
                <div class="p-6">
                    <div
                        class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center mb-4 group-hover:bg-red-600 group-hover:text-white transition-colors">
                        <i class="fa fa-upload text-xl"></i>
                    </div>

                    <h3 class="font-bold text-gray-800 text-lg mb-2">Restaurar BD</h3>
                    <p class="text-sm text-gray-500 mb-6 h-10">
                        Restaura el sistema a un punto anterior. <span
                            class="text-red-500 font-bold text-xs ml-1">(Acción Crítica)</span>
                    </p>

                    <form method="post" action="<?php echo base_url() ?>configuracion/restore"
                        enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Archivo
                                SQL</label>
                            <input type="file" name="sqlFile" accept=".sql" required
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                        </div>

                        <button type="submit"
                            onclick="return confirm('ATENCIÓN: ¿Estás seguro? Esto borrará los datos actuales y los reemplazará por el respaldo.')"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl transition-all flex items-center justify-center shadow-lg shadow-red-600/30">
                            <i class="fa fa-refresh mr-2"></i> Restaurar
                        </button>
                    </form>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">
                <div class="p-6">
                    <div
                        class="w-12 h-12 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center mb-4 group-hover:bg-gray-600 group-hover:text-white transition-colors">
                        <i class="fa fa-file-archive-o text-xl"></i>
                    </div>

                    <h3 class="font-bold text-gray-800 text-lg mb-2">Archivos Físicos</h3>
                    <p class="text-sm text-gray-500 mb-4 h-10">
                        Comprime la carpeta de documentos del servidor.
                    </p>

                    <form action="<?php echo base_url(); ?>configuracion/respaldo_archivos" method="post">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Ruta de
                                Destino</label>
                            <div class="relative">
                                <i class="fa fa-folder-open absolute left-3 top-3 text-gray-400"></i>
                                <input type="text" name="ruta_destino" required value="C:\Respaldos\Scantec"
                                    class="pl-9 w-full px-3 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-gray-400 outline-none text-sm text-gray-700"
                                    placeholder="Ej: D:\Backups">
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1">La carpeta debe existir en el servidor.</p>
                        </div>

                        <button type="submit"
                            class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-xl transition-all flex items-center justify-center shadow-lg shadow-gray-600/30">
                            <i class="fa fa-cogs mr-2"></i> Ejecutar Tarea
                        </button>
                    </form>

                    <div class="mt-4 text-center">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                            <i class="fa fa-clock-o mr-1"></i> Segundo plano
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<?php pie() ?>

<?php
// Script de alertas
if (isset($_SESSION['alert'])) {
    $alertType = $_SESSION['alert']['type'];
    $alertMessage = $_SESSION['alert']['message'];

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '$alertType',
                title: '$alertMessage',
                showConfirmButton: true,
                confirmButtonColor: '#1d4ed8',
                timer: 5000,
                customClass: {
                    popup: 'rounded-2xl shadow-xl',
                    title: 'font-sans text-lg'
                }
            });
        });
    </script>";
    unset($_SESSION['alert']);
}
?>