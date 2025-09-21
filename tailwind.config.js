const plugin = require('tailwindcss/plugin');

module.exports = {
  mode: 'jit',
  purge: ['./code/**/*.{html,js,php}'],
  darkMode: false,
  theme: {
    extend: {
      colors: {
        lightgrey: {
          100: '#E6E6E6',
        },
        grey: {
          100: '#909090',
        },
        offblack: {
          100: '#44403B',
        },
        offwhite: {
          100: '#FAFAF9',
        },
      },
    },
  },
  variants: {
    extend: {},
  },
  plugins: [
    plugin(function ({ addUtilities }) {
      const newFilters = {
        '.filter-blue': {
          filter: 'invert(41%) sepia(20%) saturate(6845%) hue-rotate(204deg) brightness(101%) contrast(101%)',
        },
        '.filter-red': {
          filter: 'invert(73%) sepia(48%) saturate(6386%) hue-rotate(318deg) brightness(96%) contrast(109%)',
        },
        '.filter-green': {
          filter: 'invert(65%) sepia(15%) saturate(1586%) hue-rotate(67deg) brightness(98%) contrast(88%)',
        },
        '.filter-yellow': {
          filter: 'invert(78%) sepia(87%) saturate(482%) hue-rotate(341deg) brightness(102%) contrast(100%)',
        },
        '.filter-grey': {
          filter: 'invert(59%) sepia(0%) saturate(251%) hue-rotate(205deg) brightness(98%) contrast(84%)',
        },
        '.filter-white': {
          filter: 'invert(100%) sepia(91%) saturate(38%) hue-rotate(254deg) brightness(110%) contrast(110%);',
        },
      };
      addUtilities(newFilters, ['responsive']);
    }),
  ],
}