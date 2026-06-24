<?php
/**
 * Flexi block renderer: Form.
 *
 * Runs inside a flexible-content row (the_row() context). Renders an optional
 * heading/intro followed by either the built-in themed contact form or an
 * embedded Gravity Form.
 */

if (! defined('ABSPATH')) {
    exit;
}

$heading     = (string) get_sub_field('heading');
$intro       = (string) get_sub_field('intro');
$form_source = (string) get_sub_field('form_source') ?: 'contact';
$gf_id       = (int) get_sub_field('gravity_form_id');
$gf_title    = (bool) get_sub_field('gravity_show_title');

if ($heading !== '' || $intro !== '') :
    ?>
    <div class="rd-form-block-intro w-full max-w-max-1038 mx-auto px-4">
        <?php if ($heading !== '') : ?>
            <h2 class="text-lg-font font-reg420 pb-4"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>
        <?php if ($intro !== '') : ?>
            <div class="entry-content"><?php echo wp_kses_post($intro); ?></div>
        <?php endif; ?>
    </div>
    <?php
endif;

if ($form_source === 'gravity') {
    if ($gf_id > 0) {
        echo do_shortcode(
            sprintf(
                '[gravityform id="%d" title="%s" ajax="true"]',
                $gf_id,
                $gf_title ? 'true' : 'false'
            )
        );
    }
    return;
}

get_template_part('template-parts/forms/contact-us');
