/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./Controller/**/*.php",
    "./Views/**/*.php",
    // Agrega aquí cualquier otra ruta donde uses clases de Tailwind
  ],
  theme: {
    extend: {
      colors: {
        'scantec-primary': '#182541',
        'scantec-secondary': '#dc153d',
        'scantec-white': '#e3e3e3',
        'scantec-gray': '#878787',
        'scantec-black': '#1d1d1b',
      }
    },
    fontFamily: {
      sans: ['Roboto', 'Montserrat', 'Arial', 'sans-serif'],
    },
  },
  plugins: [
    require('@tailwindcss/forms'), // Plugin oficial para estilos de formulario base
  ],
}