/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Filament/**/*.php',
        './app/Http/**/*.php',
    ],
    safelist: [
        // Layout
        'ml-64', 'pt-20', 'pt-16',
        // Glassmorphism backgrounds
        'bg-white/5', 'bg-white/10', 'bg-white/[0.02]', 'bg-white/[0.03]',
        'border-white/5', 'border-white/10', 'border-white/20',
        // Dark backgrounds
        'bg-[#060913]', 'bg-[#0a0f1d]',
        // Primary colors
        'bg-primary-600', 'bg-primary-500', 'bg-primary-400',
        'text-primary-400', 'text-primary-300', 'text-primary-200',
        'border-primary-500', 'border-primary-400',
        'bg-primary-600/10', 'bg-primary-600/20',
        'shadow-primary-900',
        // Gradients
        'from-primary-400', 'to-primary-600', 'from-primary-600', 'to-primary-500',
        'via-primary-300',
        // Text colors used dynamically
        'text-emerald-400', 'text-emerald-300', 'text-emerald-200',
        'text-amber-400', 'text-amber-300', 'text-amber-200',
        'text-red-400', 'text-red-300',
        'text-yellow-400', 'text-purple-400',
        'text-dark-200', 'text-dark-300', 'text-dark-400', 'text-dark-500',
        // Badges & dynamic badge colors
        'bg-emerald-500/10', 'bg-emerald-500/20', 'border-emerald-500/20', 'border-emerald-500/30',
        'bg-amber-500/10', 'bg-amber-500/20', 'border-amber-500/20', 'border-amber-500/30',
        'bg-red-500/10', 'bg-red-500/20', 'border-red-500/20', 'border-red-500/30',
        'bg-yellow-500/10', 'border-yellow-500/20',
        'bg-purple-500/10', 'border-purple-500/20',
        // Blur
        'backdrop-blur-xl', 'backdrop-blur-2xl', 'backdrop-blur-3xl',
        // Animations
        'animate-fade-in-up', 'animate-float', 'animate-pulse-glow',
        // Hover effects
        'hover:border-primary-500/30', 'hover:border-amber-500/30',
        'hover:-translate-y-1',
        // Grid dynamic
        'lg:col-span-1', 'lg:col-span-2', 'lg:col-span-3', 'lg:col-span-4', 'lg:col-span-6',
        'col-span-1', 'col-span-2', 'col-span-3',
        // Aspect ratios
        'aspect-square', 'aspect-[16/9]', 'aspect-[4/5]',
        // Sizing
        'w-[50%]', 'h-[50%]',
        'top-[-20%]', 'left-[-10%]', 'bottom-[-20%]', 'right-[-10%]',
        // Active star state (used in loops)
        'text-amber-400', 'text-dark-700',
        // Blur backgrounds
        'blur-[120px]',
        'bg-blue-900/20', 'bg-primary-600/10',
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
