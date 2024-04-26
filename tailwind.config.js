const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'selector',

  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Filament/**/*.php",
    "./app/Liwewire/**/*.php",
    ],

  theme: {
      extend: {
          fontFamily: {
              sans: ['Figtree', ...defaultTheme.fontFamily.sans],
          },
      },
  },

  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
}

