/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./Views/**/*.php"],
  theme: {
    extend: {
      colors: {
        'primario': '#182541',
        'secundario': '#dc153d',
        'fondo-claro': '#e3e3e3',
        'texto-principal': '#1d1d1b',
        'texto-secundario': '#878787',
      },
      fontFamily: {
        'sans': ['Roboto', 'sans-serif'],
        'display': ['Montserrat', 'sans-serif'],
      },
      borderColor: theme => ({
        ...theme('colors'),
        'default': theme('colors.texto-secundario', '#878787'),
      }),
      ringColor: {
        'default': '#182541',
      }
    },
  },
  plugins: [],
}
