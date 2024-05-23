import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter Tight", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                kado: {
                    50: "#faf6f6",
                    100: "#f6eaea",
                    200: "#efd9d9",
                    300: "#e2c0bf",
                    400: "#d6a6a6",
                    500: "#bd7776",
                    600: "#a75d5b",
                    700: "#8b4b4a",
                    800: "#744140",
                    900: "#623b3a",
                    950: "#341c1b",
                },
                backgroundImage: {
                    "hero-pattern": "url('/images/hero-pattern.svg')",
                    "footer-texture": "url('/img/footer-texture.png')",
                },
            },
        },
    },

    plugins: [forms],
};
