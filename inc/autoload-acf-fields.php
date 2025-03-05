<?php
// Autoload ACF Fields
$acf_fields_dir = get_template_directory() . '/acf-fields';

foreach (glob($acf_fields_dir . '/**/*.php') as $file) {
    require_once $file;
}