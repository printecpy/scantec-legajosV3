/* Configuracion de Tailwind para SCANTEC (generada por Compilar-Tailwind.ps1).
   Se puede editar a mano: el script NO la pisa salvo que se use -RegenerarConfig. */
module.exports = {
  content: [
    './Views/**/*.php',
    './Controller/**/*.php',
    './Helpers/**/*.php',
    './Assets/js/Funciones.js',
    './Assets/js/grafico_productividad.js',
    './Assets/js/main.js',
    './Assets/js/scripts.js',
    './Assets/js/sweetalert2@9.js',
    './Assets/js/tables.js',
  ],
  // Clases que el compilador no puede detectar porque se arman por concatenacion en el codigo.
  // Ejemplo:  { pattern: /^(bg|text)-(red|green|blue)-(100|600|800)$/ },
  safelist: [
  ],
  theme: {
    extend: {
      colors: {
        'scantec-blue': 'var(--scantec-blue)',
        'scantec-red': 'var(--scantec-red)',
        'scantec-white': 'var(--scantec-white)',
        'scantec-gray': 'var(--scantec-gray)',
        'scantec-black': 'var(--scantec-black)',
      },
      fontFamily: {
        sans: ['Roboto', 'sans-serif'],
        montserrat: ['Montserrat', 'sans-serif'],
      },
    },
  },
}
