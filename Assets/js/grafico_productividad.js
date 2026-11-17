/*
 * Grafico de columnas "Productividad" del dashboard y de la lista.
 * Reemplaza a Google Charts: usa Chart.js local (Assets/vendor/chartjs), sin internet.
 *
 * Uso:  scantecGraficoProductividad('columnchart', [{ fecha: '2026-10-01', total: 12 }, ...]);
 * El elemento 'columnchart' debe ser un <canvas> dentro de un contenedor con altura fija.
 */
function scantecGraficoProductividad(idCanvas, filas) {
    var canvas = document.getElementById(idCanvas);
    if (!canvas || typeof Chart === 'undefined') {
        return null;
    }
    filas = Array.isArray(filas) ? filas : [];

    var etiquetas = filas.map(function (f) { return String(f.fecha); });
    var totales = filas.map(function (f) { return Number(f.total) || 0; });
    // Mismo criterio de colores que antes: gris para "Sin Fecha", azul Scantec para el resto.
    var colores = filas.map(function (f) { return f.fecha === 'Sin Fecha' ? '#878787' : '#182541'; });

    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Total Archivos',
                data: totales,
                backgroundColor: colores,
                barPercentage: 0.6,          // equivale al groupWidth: '60%' de Google Charts
                categoryPercentage: 1.0
            }]
        },
        options: {
            responsive: true,                // se redimensiona sola (ya no hace falta el listener de resize)
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: function (items) { return 'Fecha: ' + items[0].label; },
                        label: function (item) { return 'Total: ' + item.parsed.y; }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#878787', font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { color: '#878787', precision: 0 } }
            }
        }
    });
}