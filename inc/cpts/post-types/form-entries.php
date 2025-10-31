<?php
/**
 * “Form Entries” CPT
 * – Top-level menu item with e-mail icon
 * – Hidden on front-end (`public => false`)
 * – No Gutenberg/editor UI (supports => false)
 * – Custom columns + read-only metabox further below
 */

add_action( 'init', function () {

    /* ——— Register using Extended CPTs (same style as FAQs) ——— */
    register_extended_post_type(
        'form_entry',
        [
            'menu_icon'      => 'dashicons-email-alt', // small mail icon
            'supports'       => false,                 // no title/body editor
            'public'         => false,
            'show_ui'        => true,
            'show_in_menu'   => true,                  // top-level
            'menu_position'  => 26,                    // under "Comments"
            'capability_type'=> 'post',
            'map_meta_cap'   => true,
        ],
        [
            'singular' => 'Form Entry',
            'plural'   => 'Form Entries',
            'slug'     => 'form-entries',
        ]
    );

} );



/* ────────────────────────────────────────────────────────────────
 *  Admin-side UX helpers
 * ──────────────────────────────────────────────────────────────── */

/* 1. Columns in wp-admin list table */
add_filter( 'manage_edit-form_entry_columns', function ( $cols ) {
    return [
        'cb'    => '<input type="checkbox" />',
        'title' => 'Entry',   // keeps default sortable "title" column (date/time)
        'name'  => 'Name',
        'email' => 'Email',
        'date'  => 'Date',
    ];
} );

add_action( 'manage_form_entry_posts_custom_column', function ( $col, $post_id ) {
    switch ( $col ) {
        case 'name':
            echo esc_html( get_post_meta( $post_id, 'name', true ) );
            break;
        case 'email':
            echo esc_html( get_post_meta( $post_id, 'email', true ) );
            break;
    }
}, 10, 2 );


/* 2. Read-only “Submission Details” metabox on single screen */
add_action( 'add_meta_boxes_form_entry', function () {
    add_meta_box(
        'form_entry_details',
        'Submission Details',
        function ( $post ) {

            $meta = get_post_meta( $post->ID );
            echo '<table class="widefat striped">';

            foreach ( $meta as $key => $vals ) {
                if ( str_starts_with( $key, '_' ) ) { continue; } // skip core/internal keys
                $label = ucwords( str_replace( '_', ' ', $key ) );
                $value = is_array( $vals ) ? implode( ', ', $vals ) : $vals[0];
                printf(
                    '<tr><th style="width:25%%;">%s</th><td>%s</td></tr>',
                    esc_html( $label ),
                    esc_html( $value )
                );
            }

            echo '</table>';
        },
        'form_entry',
        'normal',
        'high'
    );
} );