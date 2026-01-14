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
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },

            colors: {
                navy: {
                    50: "rgb(235 242 250)",
                    100: "rgb(214 228 242)",
                    200: "rgb(175 205 228)",
                    300: "rgb(140 178 205)",
                    400: "rgb(95 145 185)",
                    500: "rgb(70 130 175)", // lebih terang (biru institusional)
                    600: "rgb(35 90 135)", // primary button cocok
                    700: "rgb(25 70 110)",
                },
            },
        },
    },

    plugins: [forms],
};
