/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
    "./src/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        'emerald': {
          500: '#10B981',
        },
        'amber': {
          500: '#F59E0B',
        },
        'cyan': {
          500: '#06B6D4',
        },
        'orange': {
          500: '#F97316',
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
