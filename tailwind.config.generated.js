module.exports = {
    "mode": "jit",
    "content": [
        "./*.php",
        "./templates/**/*.php",
        "./assets/js/**/*.js",
        "./acf-fields/**/*.php"
    ],
    "theme": {
        "extend": {
            "fontFamily": {
                "primary": [
                    "Poppins",
                    "sans-serif"
                ],
                "secondary": [
                    "Poppins",
                    "sans-serif"
                ],
                "tertiary": [
                    "Poppins",
                    "sans-serif"
                ]
            },
            "colors": {
                "primary": {
                    "DEFAULT": "#ff0044",
                    "light": "#50B847",
                    "dark": "#00672F"
                },
                "secondary": {
                    "DEFAULT": "#F16623",
                    "light": "#ECCEBF",
                    "dark": "#C4D831"
                },
                "background": {
                    "DEFAULT": "#ffffff",
                    "light": "#EFF5EC",
                    "dark": "#D0E2C8"
                }
            },
            "fontSize": {
                "base": "12px"
            }
        },
        "screens": {
            "mob": "575px",
            "md": "768px",
            "lg": "1024px"
        }
    },
    "darkMode": "class",
    "safelist": [
        "text-[#123456]",
        "bg-primary",
        "text-green-700",
        "hover:text-green-700",
        "focus:text-green-700",
        "lg:text-green-700",
        "text-xs",
        "text-sm",
        "text-base",
        "text-lg",
        "text-xl",
        "text-2xl",
        "text-3xl"
    ],
    "future": {
        "hoverOnlyWhenSupported": true
    }
};