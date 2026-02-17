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

/**
 * Obtención y renderizado de metadatos PDF (Estilo Scantec)
 */
function cargarMetadatos(ruta) {
  const contenido = document.getElementById("contenidoMetadatos");
  const modalID = "metadatosModal"; // ID del modal en tu HTML

  // 1. Spinner de carga
  contenido.innerHTML = `
        <div class="flex flex-col items-center justify-center p-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-slate-800"></div>
            <p class="mt-4 text-xs font-bold text-slate-600 uppercase tracking-widest">Analizando Documento...</p>
        </div>`;

  // Abrir el modal inmediatamente para mostrar el spinner
  const modal = document.getElementById(modalID);
  if (modal && modal.classList.contains("hidden")) {
    toggleModal(modalID);
  }

  // 2. Petición Fetch
  fetch(`${baseURL}expedientes/obtener_metadatos_pdf?ruta=${encodeURIComponent(ruta)}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const m = data.data;

        // 3. Construcción del HTML
        let html = `
                    <div class="space-y-6 animate-fade-in-up">
                        <section>
                            <h5 class="text-xs font-bold text-slate-800 uppercase tracking-widest border-b border-gray-200 pb-2 mb-3">
                                <i class="fas fa-info-circle mr-1"></i> Información General
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-4 text-[11px] text-gray-600">
                                <p><strong class="text-slate-700">Archivo:</strong> ${m.FileName || "---"}</p>
                                <p><strong class="text-slate-700">Tamaño:</strong> ${m.FileSize || "---"}</p>
                                <p><strong class="text-slate-700">Creador:</strong> ${m.Creator || "---"}</p>
                                <p><strong class="text-slate-700">Herramienta:</strong> ${m.CreatorTool || "---"}</p>
                                <p><strong class="text-slate-700">Creación:</strong> ${m.CreateDate || "---"}</p>
                                <p><strong class="text-slate-700">Modificación:</strong> ${m.ModifyDate || "---"}</p>
                                <p class="col-span-2 truncate"><strong class="text-slate-700">Palabras Clave:</strong> ${m.Keywords || "---"}</p>
                            </div>
                        </section>

                        <section>
                            <h5 class="text-xs font-bold text-blue-800 uppercase tracking-widest border-b border-blue-100 pb-2 mb-3">
                                <i class="fas fa-signature mr-1"></i> Datos de Firma y Captura
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-4 text-[11px] text-gray-600">
                                <p class="col-span-2 break-all bg-blue-50 p-2 rounded border border-blue-100">
                                    <strong class="text-blue-700">URI Firmante:</strong> ${m.URI_Firmante || "No detectada"}
                                </p>
                                <p><strong class="text-slate-700">Firmante ID:</strong> ${m.ID_Firmante || "---"}</p>
                                <p><strong class="text-slate-700">Cargo:</strong> ${m.CARGO_FIRMANTE || "---"}</p>
                                <p><strong class="text-slate-700">Fecha Firma:</strong> ${m.Fecha_Firma || "---"}</p>
                                <p><strong class="text-slate-700">Resolución:</strong> ${m.RESOLUCION_PDF || "---"}</p>
                                <p><strong class="text-slate-700">Software:</strong> ${m.SOFTWARE_DE_CAPTURA || "---"}</p>
                            </div>
                        </section>
                    </div>`;

        contenido.innerHTML = html;

      } else {
        throw new Error(data.error || "Error desconocido al procesar PDF");
      }
    })
    .catch(error => {
      contenido.innerHTML = `
                <div class="p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Error de Lectura</h3>
                    <p class="text-sm text-gray-500 mt-2">${error.message}</p>
                </div>`;

      // Opcional: Cerrar modal automáticamente si falla muy rápido, o dejarlo abierto con el error
      Swal.fire({
        icon: "error",
        title: "Error",
        text: error.message,
        confirmButtonColor: "#1e293b"
      });
    });
}