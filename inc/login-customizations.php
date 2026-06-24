<?php
/**
 * wp-login.php branding.
 *
 * The legacy site styled the login screen via the `modern-login` mu-plugin
 * (Rolling Donut logo, white logo plate, yellow primary button). That plugin is
 * not present here, so we reproduce the same look with core login hooks instead.
 */

add_filter('login_color_palette', function () {
    return [
        'brand'    => '#ffed56',
        'trim'     => '#000000',
        'trim-alt' => '#181818',
    ];
});

add_filter('login_headertext', function () {
    return get_bloginfo('name');
});

add_filter('login_headerurl', function () {
    return home_url('/');
});

/**
 * Resolve the Rolling Donut login logo URL (the animated brand mark used on the
 * legacy login screen), falling back to the theme's custom logo if present.
 */
function matrix_rd_login_logo_url(): string {
    $uploads = wp_get_upload_dir();
    $base    = isset($uploads['basedir']) ? (string) $uploads['basedir'] : '';
    $url     = isset($uploads['baseurl']) ? (string) $uploads['baseurl'] : '';
    $gif     = '/2023/07/logo-gif.gif';

    if ($base !== '' && is_readable($base . $gif)) {
        return $url . $gif;
    }

    $custom_logo_id = (int) get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $src = wp_get_attachment_image_src($custom_logo_id, 'full');
        if (is_array($src) && ! empty($src[0])) {
            return (string) $src[0];
        }
    }

    return '';
}

add_action('login_enqueue_scripts', function () {
    $logo = matrix_rd_login_logo_url();
    ?>
    <style id="matrix-rd-login">
        /* Ported 1:1 from the legacy "modern login" styling
         * (--login-brand:#fff, --login-trim:#181818, yellow #ffed56 submit). */
        body.login {
            background: #ffffff;
            color: #111111;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .login #login {
            margin: auto;
            padding: 0;
            max-width: 100%;
        }

        /* Brand logo on a white plate (320 x 150), like the legacy login. */
        .login #login > h1 {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 320px;
            height: 150px;
            margin: 0 auto;
            padding: 0;
            background-color: #ffffff;
            border-radius: 0;
        }

        <?php if ($logo !== '') : ?>
        .login #login h1 a {
            background-image: url(<?php echo esc_url($logo); ?>) !important;
            width: 320px !important;
            height: 150px !important;
            background-size: contain !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            text-indent: -9999px;
        }
        <?php endif; ?>

        /* Dark card for every login form variant. */
        .login #loginform,
        .login #lostpasswordform,
        .login #registerform,
        .login #resetpassform {
            padding: 2rem;
            border: 0;
            border-radius: 0.25rem;
            background-color: #181818;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .login #loginform label,
        .login #lostpasswordform label,
        .login #registerform label,
        .login #resetpassform label {
            color: #ffffff;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 14px;
        }

        .login #loginform input[type="text"],
        .login #loginform input[type="email"],
        .login #loginform input[type="password"],
        .login #lostpasswordform input[type="text"],
        .login #lostpasswordform input[type="email"],
        .login #resetpassform input[type="password"],
        .login #registerform input[type="text"],
        .login #registerform input[type="email"] {
            margin-top: 0.25rem;
            padding: 0.25rem 0.5rem;
            background-color: #ffffff;
            color: #111111;
            border: 1px solid #181818;
            border-radius: 0.25rem;
        }

        /* The "show password" toggle sits on the white input — keep it readable. */
        .login .wp-hide-pw {
            color: #181818;
            border: 0;
            margin-top: 0.25rem;
        }

        /* "Remember Me" row. */
        .login .forgetmenot {
            display: flex;
            align-items: center;
            margin-top: 0.25rem;
        }

        /* Needs the #form id to out-specify the uppercase label rule above. */
        .login #loginform .forgetmenot label,
        .login #lostpasswordform .forgetmenot label,
        .login #registerform .forgetmenot label,
        .login #resetpassform .forgetmenot label {
            margin: 0 0 0 0.5rem;
            opacity: 0.5;
            font-weight: 400;
            text-transform: capitalize;
        }

        /* Yellow primary submit with black text. */
        .login #wp-submit,
        .login .button-primary {
            padding: 0 0.5rem;
            background: #ffed56 !important;
            color: #111111 !important;
            border: 0 !important;
            border-radius: 3px !important;
            font-weight: 700 !important;
            text-shadow: none !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .login #wp-submit:hover,
        .login #wp-submit:focus,
        .login .button-primary:hover,
        .login .button-primary:focus {
            background: #ffed56 !important;
            color: #111111 !important;
        }

        /* Nav links (Lost your password? etc.) in dark text; hide "Go to site". */
        .login #nav {
            margin: 0.75rem 0 0;
            text-align: center;
        }

        .login #nav a {
            color: #111111;
        }

        .login #nav a:hover,
        .login #nav a:focus {
            color: #111111;
            text-decoration: underline;
        }

        .login #backtoblog {
            display: none;
        }
    </style>
    <?php
});
