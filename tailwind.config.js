/** Tailwind config for ITTI LMS — compiled to a static CSS file (no runtime CDN). */
module.exports = {
  darkMode: 'class',
  content: [
    './resources/views/**/*.php',
    './app/**/*.php',
    './public/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        // Brand palette derived from the ITTI crest: deep navy + steel blue.
        brand: {
          50:'#eef3f9',100:'#d6e3f0',200:'#aec7e1',300:'#7ba3cd',400:'#4f7cae',
          500:'#345f8c',600:'#274a70',800:'#152a45',900:'#0f2038',950:'#091627',
          700:'#1d3a59'
        },
        // Prestige gold accent (awards, highlights, CTAs).
        gold: {
          50:'#fbf7ec',100:'#f6edcf',200:'#ecd99a',300:'#e1c45f',400:'#d6b13a',
          500:'#c69b2d',600:'#a87d24',700:'#865f20',800:'#714e21',900:'#604121'
        },
        ink: '#070f1c',
      },
      fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
      keyframes: {
        float:  { '0%,100%':{transform:'translateY(0)'}, '50%':{transform:'translateY(-12px)'} },
        fadeup: { '0%':{opacity:0,transform:'translateY(16px)'}, '100%':{opacity:1,transform:'translateY(0)'} },
      },
      animation: { float:'float 6s ease-in-out infinite', fadeup:'fadeup .7s ease both' },
    },
  },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
