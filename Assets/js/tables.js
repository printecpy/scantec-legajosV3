const languageConfig = {
    decimal: "",
    emptyTable: "No hay datos disponibles",
    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
    infoEmpty: "Mostrando 0 a 0 de 0 registros",
    infoFiltered: "(Filtrado de _MAX_ totales)",
    thousands: ",",
    lengthMenu: "Mostrar _MENU_ registros",
    search: "Buscar:",
    zeroRecords: "No se encontraron coincidencias",
    paginate: {
        first: '<i class="fas fa-angle-double-left"></i>',
        last: '<i class="fas fa-angle-double-right"></i>',
        next: '<i class="fas fa-chevron-right"></i>',
        previous: '<i class="fas fa-chevron-left"></i>'
    }
};

$(document).ready(function () {

    // 1. TABLA PRINCIPAL (Buscador + Paginación + Tailwind)
    if ($("#table").length) {
        $("#table").DataTable({
            // Forzamos paginación a 10 (esto arregla que veas 12)
            pageLength: 10,
            responsive: true,
            autoWidth: false,
            language: languageConfig,
            order: [[0, "asc"]],

            // === MAGIA DE TAILWIND ===
            // Definimos la estructura (DOM) usando clases Flexbox de Tailwind
            // l=length (select cantidad), f=filter (buscador), t=table, i=info, p=pagination
            dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4 gap-4"ip>',

            // Aplicamos estilos a los elementos generados
            drawCallback: function () {
                // Estilizar botones de paginación
                $('.dataTables_paginate > .paginate_button').addClass('px-3 py-1 mx-1 border border-gray-200 rounded-md bg-white text-gray-600 hover:bg-slate-100 cursor-pointer transition-colors');
                $('.dataTables_paginate > .paginate_button.current').addClass('bg-slate-900 text-white border-slate-900 hover:bg-slate-800 font-bold');

                // Estilizar input de búsqueda
                $('.dataTables_filter input').addClass('border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-slate-900 focus:outline-none ml-2 text-sm shadow-sm');

                // Estilizar selector de cantidad
                $('.dataTables_length select').addClass('border border-gray-300 rounded-lg px-2 py-1 focus:ring-2 focus:ring-slate-900 focus:outline-none mx-2 text-sm shadow-sm');
            }
        });
    }

    // 2. TABLAS DE DASHBOARD (Compactas)
    const dashboardConfig = {
        paging: true,
        searching: false,
        info: true,
        responsive: true,
        autoWidth: false,
        language: languageConfig,
        pageLength: 5,
        lengthChange: false,
        // Usamos una estructura DOM más simple para el dashboard
        dom: 'rt<"flex justify-between items-center mt-2"ip>',
        drawCallback: function () {
            $('.dataTables_paginate > .paginate_button').addClass('px-2 py-1 mx-1 text-xs border rounded hover:bg-gray-100 cursor-pointer');
            $('.dataTables_paginate > .paginate_button.current').addClass('bg-slate-800 text-white border-slate-800');
        }
    };

    if ($("#table1").length) $("#table1").DataTable(dashboardConfig);
    if ($("#table2").length) $("#table2").DataTable(dashboardConfig);
});

/**
 * Validación de rango de fechas para reportes
 */
function validarFormulario() {
  const anio_desde = parseInt(document.getElementById("anio_desde").value);
  const anio_hasta = parseInt(document.getElementById("anio_hasta").value);

  if (anio_desde > anio_hasta) {
    Swal.fire({
      icon: "warning",
      title: "Rango Inválido",
      text: "El año de inicio no puede ser mayor al año final.",
      confirmButtonColor: "#1e293b" // Slate-800
    });
    return false;
  }
  return true;
}

/**
 * Función global para abrir/cerrar modales Tailwind
 * (Asegúrate de que esta función exista aquí o en tu layout principal)
 */
function toggleModal(modalID) {
  const modal = document.getElementById(modalID);
  if (modal) {
    modal.classList.toggle("hidden");
  }
}

