/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Filament/**/*.php',
        './app/Http/**/*.php',
    ],
    safelist: [
        // ── Layout helpers ────────────────────────────────
        'ml-64', 'pt-20', 'pt-16',

        // ── Glassmorphism / Alpha backgrounds ────────────
        'bg-white/5', 'bg-white/10', 'bg-white/[0.02]', 'bg-white/[0.03]',
        'border-white/5', 'border-white/10', 'border-white/20',

        // ── Hardcoded hex backgrounds ─────────────────────
        'bg-[#060913]', 'bg-[#0a0f1d]',

        // ── All dark-* utility variants (used extensively in admin/seller/mfg dashboards) ──
        {
            pattern: /bg-dark-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /text-dark-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern: /border-dark-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /from-dark-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /via-dark-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /to-dark-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /ring-dark-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /shadow-dark-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /divide-dark-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /hover:bg-dark-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /hover:border-dark-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /hover:text-dark-(50|100|200|300|400|500|600|700|800|900|950)/,
        },

        // ── All primary-* utility variants ────────────────
        {
            pattern: /bg-primary-(50|100|200|300|400|500|600|700|800|900)(\/\d+)?/,
        },
        {
            pattern: /text-primary-(50|100|200|300|400|500|600|700|800|900)/,
        },
        {
            pattern: /border-primary-(50|100|200|300|400|500|600|700|800|900)(\/\d+)?/,
        },
        {
            pattern: /from-primary-(50|100|200|300|400|500|600|700|800|900)/,
        },
        {
            pattern: /to-primary-(50|100|200|300|400|500|600|700|800|900)/,
        },
        {
            pattern: /via-primary-(50|100|200|300|400|500|600|700|800|900)/,
        },
        {
            pattern: /shadow-primary-(50|100|200|300|400|500|600|700|800|900)(\/\d+)?/,
        },
        {
            pattern: /hover:bg-primary-(50|100|200|300|400|500|600|700|800|900)(\/\d+)?/,
        },
        {
            pattern: /hover:border-primary-(50|100|200|300|400|500|600|700|800|900)(\/\d+)?/,
        },
        {
            pattern: /hover:text-primary-(50|100|200|300|400|500|600|700|800|900)/,
        },

        // ── Color utility variants (emerald, amber, purple, cyan, rose, blue, indigo) ──
        {
            pattern: /bg-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /text-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern: /border-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /from-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern: /to-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern: /via-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern: /shadow-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /ring-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /hover:bg-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /hover:border-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
        },
        {
            pattern: /hover:text-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern: /group-hover:text-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },

        // ── Opacity variants for backgrounds ─────────────
        {
            pattern: /bg-(emerald|amber|purple|cyan|rose|blue|indigo|teal|violet|slate|green|red|yellow|orange|pink|fuchsia|lime|sky)-(50|100|200|300|400|500|600|700|800|900|950)\/(10|15|20|25|30|40|50|60|70|80|90)/,
        },
        {
            pattern: /bg-dark-(50|100|200|300|400|500|600|700|800|900|950)\/(10|15|20|25|30|40|50|60|70|80|90)/,
        },
        {
            pattern: /bg-primary-(50|100|200|300|400|500|600|700|800|900)\/(10|15|20|25|30|40|50|60|70|80|90)/,
        },

        // ── Blur ─────────────────────────────────────────
        'backdrop-blur-xl', 'backdrop-blur-2xl', 'backdrop-blur-3xl',

        // ── Animations ───────────────────────────────────
        'animate-fade-in-up', 'animate-float', 'animate-pulse-glow', 'animate-pulse',

        // ── Grid col spans ──────────────────────────────
        'lg:col-span-1', 'lg:col-span-2', 'lg:col-span-3', 'lg:col-span-4', 'lg:col-span-6',
        'xl:col-span-2', 'xl:col-span-3',
        'col-span-1', 'col-span-2', 'col-span-3',

        // ── Sizing ───────────────────────────────────────
        'w-[50%]', 'h-[50%]',
        'top-[-20%]', 'left-[-10%]', 'bottom-[-20%]', 'right-[-10%]',

        // ── Misc needed utilities ─────────────────────────
        'blur-[120px]',
        'aspect-square', 'aspect-[16/9]', 'aspect-[4/5]',
        'hover:-translate-y-1',
        'font-mono',
        'truncate', 'line-clamp-1', 'line-clamp-2',
        'space-y-4', 'space-y-6',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50:  '#f0f9ff',
                    100: '#e0f2fe',
                    200: '#bae6fd',
                    300: '#7dd3fc',
                    400: '#38bdf8',
                    500: '#0ea5e9',
                    600: '#0284c7',
                    700: '#0369a1',
                    800: '#075985',
                    900: '#0c4a6e',
                },
                dark: {
                    50:  '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                    950: '#020617',
                },
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
