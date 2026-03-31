<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scantec - Registro</title>

    <?php include "Views/template/config_tailwind.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-login-custom font-sans min-h-screen flex items-center justify-center p-4">

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: <?php echo json_encode((string) ($_SESSION['alert']['type'] ?? 'info')); ?>,
                    title: 'Atención',
                    text: <?php echo json_encode((string) ($_SESSION['alert']['message'] ?? '')); ?>,
                    confirmButtonColor: '#182541',
                    confirmButtonText: 'Entendido'
                });
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <div class="max-w-3xl w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-7 md:p-9">
            <div class="flex justify-center mb-8">
                <img src="<?php echo base_url(); ?>Assets/img/icoScantec2.png" alt="Scantec Logo"
                    class="h-16 w-auto object-contain">
            </div>

            <h2 class="text-2xl font-montserrat font-bold text-scantec-blue text-center mb-2 uppercase tracking-wide">
                Registro de Usuario
            </h2>
            <p class="text-scantec-gray text-center text-sm mb-8">
                Complete sus datos. La cuenta quedará inactiva hasta que un administrador la apruebe.
            </p>

            <form id="registroPublicoForm" action="<?php echo base_url(); ?>home/guardar_registro" method="POST" class="space-y-6" autocomplete="off">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars((string) ($data['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2 px-1">Nombre completo</label>
                        <input type="text" name="nombre" value="" autocomplete="off" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50"
                            placeholder="Nombre y apellido">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2 px-1">Departamento</label>
                        <input type="text" name="departamento" value="" autocomplete="off" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50"
                            placeholder="Ej: Recursos Humanos">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2 px-1">Usuario</label>
                        <input type="text" name="usuario" value="" autocomplete="off" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50"
                            placeholder="Ej: juan.perez">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2 px-1">Correo</label>
                        <input type="email" name="email" value="" autocomplete="off" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50"
                            placeholder="correo@empresa.com">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2 px-1">Rol solicitado</label>
                        <select name="id_rol" autocomplete="off" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50">
                            <option value="" selected disabled>Seleccione un rol</option>
                            <?php foreach (($data['roles'] ?? []) as $rol): ?>
                                <option value="<?php echo intval($rol['id_rol']); ?>">
                                    <?php echo htmlspecialchars((string) $rol['descripcion'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-[11px] text-scantec-gray mt-2 px-1">
                            El administrador puede aprobar este rol o cambiarlo antes de activar su usuario.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2 px-1">Contraseña</label>
                        <input type="password" id="clave" name="clave" value="" autocomplete="new-password" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50"
                            placeholder="********">
                        <p id="passwordError" class="text-red-500 text-xs mt-1 px-1 font-bold"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2 px-1">Repetir contraseña</label>
                        <input type="password" id="clave_confirm" name="clave_confirm" value="" autocomplete="new-password" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50"
                            placeholder="********">
                        <p id="passwordConfirmError" class="text-red-500 text-xs mt-1 px-1 font-bold"></p>
                    </div>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-blue-50/60 px-4 py-3">
                    <p class="text-xs text-scantec-blue font-bold uppercase tracking-widest mb-1">Importante</p>
                    <p class="text-sm text-gray-600">
                        El registro se guarda como inactivo. Un administrador revisará sus datos, podrá ajustar el rol y luego habilitar el acceso.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <a href="<?php echo base_url(); ?>"
                        class="text-scantec-blue hover:underline font-bold text-xs uppercase tracking-widest text-center sm:text-left">
                        Volver al inicio de sesión
                    </a>

                    <button type="submit"
                        class="w-full sm:w-auto bg-scantec-blue hover:bg-scantec-red text-white font-montserrat font-bold py-4 px-8 rounded-xl transition-all shadow-lg tracking-widest">
                        ENVIAR REGISTRO
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-scantec-blue/5 py-3 text-center border-t border-gray-100">
            <p class="text-[9px] text-scantec-gray font-bold uppercase tracking-[0.2em]">
                © 2023 - 2026 PRINTEC SA | SOFTWARE DE GESTION DOCUMENTAL
            </p>
        </div>
    </div>

    <script>
        window.addEventListener('pageshow', function () {
            const form = document.getElementById('registroPublicoForm');
            if (!form) {
                return;
            }

            form.reset();
            const selectRol = form.querySelector('select[name="id_rol"]');
            if (selectRol) {
                selectRol.selectedIndex = 0;
            }
        });

        document.getElementById('registroPublicoForm').addEventListener('submit', function (event) {
            const password = document.getElementById('clave').value;
            const passwordConfirm = document.getElementById('clave_confirm').value;
            const regex = /^(?=.*[A-Z])(?=.*[!@#$%^&*\-_.#])(?=.*[a-z\d])(?=.{7,})/;
            let hasError = false;

            if (!regex.test(password)) {
                document.getElementById('passwordError').textContent = 'Mínimo 7 caracteres, 1 mayúscula y 1 símbolo';
                hasError = true;
            } else {
                document.getElementById('passwordError').textContent = '';
            }

            if (password !== passwordConfirm) {
                document.getElementById('passwordConfirmError').textContent = 'Las contraseñas no coinciden.';
                hasError = true;
            } else {
                document.getElementById('passwordConfirmError').textContent = '';
            }

            if (hasError) {
                event.preventDefault();
            }
        });
    </script>
</body>

</html>


