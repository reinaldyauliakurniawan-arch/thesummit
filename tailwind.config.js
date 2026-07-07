/** @type {import('tailwindcss').Config} */
export default {
  content: ['./resources/**/*.blade.php','./resources/**/*.js','./components/**/*.blade.php'],
  theme: {
    extend: {
      colors: {
        mountain:{50:'#f7f6f4',100:'#edeae5',200:'#d9d4cb',300:'#bfbbb0',400:'#a39d91',500:'#8a8477',600:'#6e6a5f',700:'#575449',800:'#48453d',900:'#3e3c36',950:'#211f1b'},
        trust:{50:'#fffbeb',100:'#fef3c7',200:'#fde68a',300:'#fcd34d',400:'#fbbf24',500:'#f59e0b',600:'#d97706',700:'#b45309',800:'#92400e',900:'#78350f',950:'#451a03'},
        basecamp:{50:'#fdf8f0',100:'#f9edda',200:'#f2d8b4',300:'#e9bc87',400:'#df9a57',500:'#d78038',600:'#c96a2b',700:'#a85125',800:'#884225',900:'#6f3821',950:'#3c1a0e'},
        camp:{50:'#f0fdf4',100:'#dcfce7',200:'#bbf7d0',300:'#86efac',400:'#4ade80',500:'#22c55e',600:'#16a34a',700:'#15803d',800:'#166534',900:'#14532d',950:'#052e16'},
        summit:{50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a',950:'#020617'},
        crisis:{50:'#fef2f2',100:'#fee2e2',200:'#fecaca',300:'#fca5a5',400:'#f87171',500:'#ef4444',600:'#dc2626',700:'#b91c1c',800:'#991b1b',900:'#7f1d1d'},
      },
      fontFamily:{expedition:['Georgia','Cambria','serif']},
      animation:{'card-flip':'cardFlip .6s ease-in-out','fade-in':'fadeIn .3s ease-in','slide-up':'slideUp .4s ease-out','pulse-gold':'pulseGold 2s infinite'},
      keyframes:{
        cardFlip:{'0%':{transform:'rotateY(0deg)'},'50%':{transform:'rotateY(90deg)'},'100%':{transform:'rotateY(0deg)'}},
        fadeIn:{'0%':{opacity:'0'},'100%':{opacity:'1'}},
        slideUp:{'0%':{opacity:'0',transform:'translateY(20px)'},'100%':{opacity:'1',transform:'translateY(0)'}},
        pulseGold:{'0%,100%':{boxShadow:'0 0 0 0 rgba(245,158,11,0.4)'},'50%':{boxShadow:'0 0 0 8px rgba(245,158,11,0)'}},
      },
    },
  },
  plugins:[require('@tailwindcss/forms'),require('@tailwindcss/typography')],
};
