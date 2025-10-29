/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './includes/**/*.php',
    './modules/**/*.php',
    './assets/js/**/*.js'
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        coresuite: '#ffbf00'
      },
      fontFamily: {
        sans: ['Inter', 'Nunito Sans', 'system-ui']
      },
      boxShadow: {
        card: '0 20px 45px -20px rgba(15, 23, 42, 0.25)'
      },
      borderRadius: {
        xl: '0.75rem'
      }
    }
  },
  plugins: []
};
