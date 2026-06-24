<?php
/**
 * Lost password reset form.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-reset-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_reset_password_form' );
?>
<style>
    .woocommerce-ResetPassword {
        padding: 4rem;
    }

    @media (max-width: 768px) {
        .woocommerce-ResetPassword {
            padding: 2rem;
        }
    }

    @media (max-width: 575px) {
        .woocommerce-ResetPassword {
            padding: 1rem;
        }
    }

    label {
        font-weight: bold;
        margin-left: 4px;
    }
</style>
<form method="post" class="woocommerce-ResetPassword lost_reset_password w-full max-w-max-704 mx-auto border-black-full border-2 border-solid rounded-[10px] laptop:rounded-md-32">
	<p class="py-4 woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label class="text-black" for="password_1"><?php esc_html_e( 'New password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true"><span class="text-red-critical">*</span></span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
		<input type="password" class="flex w-full mt-2 font-light rounded-lg-x h-input text-black-secondary text-mob-xs-font font-laca pl-11 max-w-max-704 woocommerce-Input woocommerce-Input--text input-text" placeholder="<?php esc_html_e('Enter New Password', 'woocommerce'); ?>"  name="password_1" id="password_1" autocomplete="new-password" required aria-required="true" />
	</p>
	<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
		<label class="text-black" for="text-white password_2"><?php esc_html_e( 'Re-enter new password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true"><span class="text-red-critical">*</span></span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
		<input type="password" class="flex w-full mt-2 font-light woocommerce-Input rounded-lg-x h-input text-black-secondary text-mob-xs-font font-laca pl-11 max-w-max-704 woocommerce-Input--text input-text" placeholder="<?php esc_html_e('Reenter New Password', 'woocommerce'); ?>" name="password_2" id="password_2" autocomplete="new-password" required aria-required="true" />
	</p>

	<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
	<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />

	<div class="clear"></div>

	<?php do_action( 'woocommerce_resetpassword_form' ); ?>

	<p class="woocommerce-form-row form-row">
		<input type="hidden" name="wc_reset_password" value="true" />
		<button type="submit" class="tn text-black-full hover:text-yellow-primary text-mob-lg-font lg:text-sm-md-font font-medium h-[52px] bg-yellow-primary rounded-lg-x w-full rd-border hover:bg-black-primary ml-auto mr-auto max-w-max-704 mt-6 woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" value="<?php esc_attr_e( 'Save', 'woocommerce' ); ?>"><?php esc_html_e( 'Save', 'woocommerce' ); ?></button>
	</p>

	<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>

</form>
<?php
do_action( 'woocommerce_after_reset_password_form' );
