$(document).ready(function () {
    
    // --- 1. LÓGICA DE CONFIRMACIONES (Vital para seguridad) ---
    // Usamos colores Scantec: #1e293b (Slate-800) para confirmar, #ef4444 (Red-500) para cancelar

    // Confirmar Eliminación / Anulación
    $(".eliminar").submit(function (e) {
        e.preventDefault(); // Detiene el envío
        Swal.fire({
            title: '¿Confirmar eliminación?',
            text: "Esta acción cambiará el estado del registro.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1e293b', // Color Scantec
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit(); // Envía el formulario si dice que sí
            }
        });
    });

    // Confirmar Reingreso / Reactivación
    $(".reingresar").submit(function (e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Reactivar registro?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1e293b', // Color Scantec
            cancelButtonColor: '#64748b',  // Gris para cancelar
            confirmButtonText: 'Sí, reactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Confirmar Devolución (Si aún usas este módulo)
    $(".devolver").submit(function (e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Confirmar devolución?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#1e293b',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Sí, devolver'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // --- 2. INICIALIZACIÓN DE COMPONENTES UI ---
    
    // Inicializar Select2 solo si existen los elementos (Evita errores en consola)
    if ($('#buscar_lote').length) {
        $('#buscar_lote').select2({ dropdownParent: $("#prestar") });
    }
    if ($('#buscar').length) {
        $('#buscar').select2({ dropdownParent: $("#prestar") });
    }
    if ($('#estudiante').length) {
        $('#estudiante').select2({ dropdownParent: $("#prestar") });
    }

    // --- 3. VALIDACIÓN DE LOGIN (Cliente) ---
    // Esto da feedback inmediato antes de recargar la página
    var formLogin = document.getElementById('loginForm');

    if (formLogin) {
        formLogin.addEventListener('submit', function(event) {
            var password = document.getElementById('clave').value;
            var passwordError = document.getElementById('passwordError');
            
            // Regex: Al menos 7 caracteres, 1 mayúscula, 1 caracter especial
            var regex = /^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.{7,})/;

            if (!regex.test(password)) {
                event.preventDefault();
                if (passwordError) {
                    passwordError.textContent = 'La contraseña debe tener al menos 7 caracteres, incluir una mayúscula y un carácter especial.';
                    // Opcional: mostrar borde rojo en el input
                    document.getElementById('clave').classList.add('border-red-500');
                }
            } else {
                if (passwordError) {
                    passwordError.textContent = '';
                    document.getElementById('clave').classList.remove('border-red-500');
                }
            }
        });
    }

    // --- 4. CÓDIGO OBSOLETO ELIMINADO ---
    // Borré las llamadas a .toast('show') porque ahora usas SweetAlert desde PHP.
});