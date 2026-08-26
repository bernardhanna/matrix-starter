<?php

/**
 * Login Form
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}

$rd_login_redirect = '';
if (! empty($_GET['redirect'])) {
    $rd_login_redirect = wp_validate_redirect(esc_url_raw(wp_unslash((string) $_GET['redirect'])), '');
}

do_action('woocommerce_before_customer_login_form');

$rd_label     = 'ml-2 block text-mob-xs-font font-reg420';
$rd_input     = 'flex w-full font-light woocommerce-Input woocommerce-Input--text rounded-lg-x h-input text-black-secondary text-mob-xs-font font-laca pl-11 border-grey-input';
$rd_input_pw  = $rd_input . ' pr-12';
$rd_icon_wrap = 'absolute inset-y-0 left-0 z-10 flex items-center pl-3.5 pointer-events-none';
$rd_btn       = 'no-form-btn-style btn text-black-full text-mob-lg-font font-medium h-[52px] bg-yellow-primary rounded-btn-72 w-full border-3 border-black-full rd-border';
$rd_link      = 'underline hover:no-underline font-reg420';
$rd_checkbox  = 'woocommerce-form__input woocommerce-form__input-checkbox bg-white mr-2 h-[20px] w-[20px] flex flex-col rounded-sm border-2 border-solid border-grey-input';
$rd_tab       = 'block w-full text-center text-md-font font-reg420 py-2.5 no-underline cursor-pointer';
$rd_icon_mail = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 32 32" aria-hidden="true"><path fill="#484848" d="M28 6H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h24a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2Zm-2.2 2L16 14.78L6.2 8ZM4 24V8.91l11.43 7.91a1 1 0 0 0 1.14 0L28 8.91V24Z"></path></svg>';
$rd_icon_lock = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path fill="#484848" d="M12 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm6-7h-1V8a5 5 0 0 0-10 0v2H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2Zm-7-5a3 3 0 0 1 3 3v2H8V8a3 3 0 0 1 3-3Z"></path></svg>';
$rd_icon_user = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" aria-hidden="true"><path fill="#484848" d="M9.993 10.573a4.5 4.5 0 1 0 0-9a4.5 4.5 0 0 0 0 9ZM10 0a6 6 0 0 1 3.04 11.174c3.688 1.11 6.458 4.218 6.955 8.078c.047.367-.226.7-.61.745c-.383.045-.733-.215-.78-.582c-.54-4.19-4.169-7.345-8.57-7.345c-4.425 0-8.101 3.161-8.64 7.345c-.047.367-.397.627-.78.582c-.384-.045-.657-.378-.61-.745c.496-3.844 3.281-6.948 6.975-8.068A6 6 0 0 1 10 0Z"></path></svg>';
?>
<style>
    .woocommerce-password-strength.short, .woocommerce-password-strength.bad {
        color: red !important;
    }
    .woocommerce-password-strength.strong {
        color: green;
        font-weight: bold;
    }
    body.tw-myaccount.tw-auth .woocommerce-notices-wrapper {
        background: black;
        height: 50px;
        display: flex;
        align-items: center;
        color: white;
        justify-content: center;
        padding-left: 2rem;
        padding-right: 2rem;
        transition: opacity .4s ease, transform .4s ease;
    }
    body.tw-myaccount.tw-auth .woocommerce-notices-wrapper strong {
        font-weight: bolder;
        color: red;
    }
    body.tw-myaccount.tw-auth .notice-hide {
        opacity: 0;
        transform: translateY(-100%);
        pointer-events: none;
    }
</style>
<div class="px-4 pb-24 mx-auto laptop:px-0 lg:max-w-max-848">
    <?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>
        <div class="w-full max-w-max-848 mx-auto border-2 border-black-full border-solid rounded-[10px] laptop:rounded-md-32 pt-10 pb-10 px-4 laptop:px-0 bg-white"
            data-testid="rd-auth-card"
            x-data="{
                activeTab: 'sign-in',
                showLostPassword: false,
                init() {
                    window.addEventListener('update-active-tab', (event) => {
                        this.activeTab = event.detail.tab;
                    });
                    window.addEventListener('update-show-lost-password', (event) => {
                        this.showLostPassword = event.detail.show;
                    });
                }
            }">
            <div class="w-full max-w-max-704 mx-auto px-4 laptop:px-0">
                <ul class="flex flex-wrap -mb-px text-sm-md-font font-reg420 signin-tabs pb-2 items-end w-full list-none m-0 p-0">
                    <li class="relative w-1/2 m-0">
                        <a id="signInTab" data-testid="rd-tab-sign-in" class="<?php echo esc_attr($rd_tab); ?>" href="#"
                            @click.prevent="
                                window.dispatchEvent(new CustomEvent('update-active-tab', { detail: { tab: 'sign-in' } }));
                                window.dispatchEvent(new CustomEvent('update-show-lost-password', { detail: { show: false } }));
                            "
                            :class="{ 'active-tab': activeTab === 'sign-in' && !showLostPassword, 'inactive-tab': activeTab !== 'sign-in' || showLostPassword }"
                            x-text="showLostPassword ? '<?php echo esc_js(__('Forgot Password', 'woocommerce')); ?>' : '<?php echo esc_js(__('Sign In', 'woocommerce')); ?>'">
                        </a>
                    </li>
                    <li class="relative w-1/2 m-0">
                        <a id="registerTab" data-testid="rd-tab-register" class="<?php echo esc_attr($rd_tab); ?>" href="#"
                            @click.prevent="
                                window.dispatchEvent(new CustomEvent('update-active-tab', { detail: { tab: 'register' } }));
                                window.dispatchEvent(new CustomEvent('update-show-lost-password', { detail: { show: false } }));
                            "
                            :class="{ 'active-tab': activeTab === 'register', 'inactive-tab': activeTab !== 'register' }">
                            <?php esc_html_e('Register', 'woocommerce'); ?>
                        </a>
                    </li>
                </ul>
            </div>

            <?php wc_print_notices(); ?>

            <div class="w-full max-w-max-704 mx-auto px-4 laptop:px-0">
                <template x-if="activeTab === 'sign-in' && !showLostPassword">
                <div class="w-full">
                <form data-testid="rd-form-sign-in" class="woocommerce-form woocommerce-form-login login w-full max-w-max-704 mx-auto pt-9" method="post" action="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">

                    <?php
                    $inline_errors = wc_get_notices('error');
                    $u_error = $p_error = '';

                    foreach ($inline_errors as $notice) {
                        if (stripos($notice['notice'], 'username') !== false) {
                            $u_error = $notice['notice'];
                        }
                        if (stripos($notice['notice'], 'password') !== false) {
                            $p_error = $notice['notice'];
                        }
                    }

                    wc_clear_notices();
                    ?>

                    <div class="w-full">
                        <label class="<?php echo esc_attr($rd_label); ?>" for="username"><?php esc_html_e('Email*', 'woocommerce'); ?></label>
                        <div class="relative w-full mt-1">
                            <div class="<?php echo esc_attr($rd_icon_wrap); ?>"><?php echo $rd_icon_mail; ?></div>
                            <input required type="email" data-testid="rd-field-username" class="<?php echo esc_attr($rd_input); ?>" placeholder="<?php esc_attr_e('Email*', 'woocommerce'); ?>" name="username" id="username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" />
                            <?php if ($u_error) : ?>
                                <p class="text-red-600 text-sm mt-1"><?php echo wp_kses_post($u_error); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="w-full pt-4 max-w-max-704">
                        <label class="<?php echo esc_attr($rd_label); ?>" for="password"><?php esc_html_e('Password', 'woocommerce'); ?></label>
                        <div class="relative w-full mt-1">
                            <div class="<?php echo esc_attr($rd_icon_wrap); ?>"><?php echo $rd_icon_lock; ?></div>
                            <input required class="<?php echo esc_attr($rd_input_pw); ?>" data-testid="rd-field-password" type="password" name="password" id="password" autocomplete="current-password" placeholder="<?php esc_attr_e('Enter Password', 'woocommerce'); ?>" />
                            <?php if ($p_error) : ?>
                                <p class="text-red-600 text-sm mt-1"><?php echo wp_kses_post($p_error); ?></p>
                            <?php endif; ?>
                        </div>
                        <p class="woocommerce-LostPassword lost_password mt-2 ml-2 text-mob-xs-font font-reg420">
                            <a data-testid="rd-link-forgot-password" class="<?php echo esc_attr($rd_link); ?>" href="#"
                                @click.prevent="
                                    window.dispatchEvent(new CustomEvent('update-show-lost-password', { detail: { show: true } }));
                                    window.dispatchEvent(new CustomEvent('update-active-tab', { detail: { tab: 'sign-in' } }));
                                ">
                                <?php esc_html_e('Forgot your password?', 'woocommerce'); ?>
                            </a>
                        </p>
                    </div>

                    <div class="w-full mt-8 ml-2">
                        <label class="flex items-center gap-2 woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme cursor-pointer">
                            <input class="<?php echo esc_attr($rd_checkbox); ?>" name="rememberme" type="checkbox" id="rememberme" value="forever" />
                            <span class="text-sm-font font-regular"><?php esc_html_e('Keep me signed in', 'woocommerce'); ?></span>
                        </label>
                    </div>

                    <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                    <?php if ($rd_login_redirect !== '') : ?>
                        <input type="hidden" name="redirect" value="<?php echo esc_url($rd_login_redirect); ?>" />
                    <?php endif; ?>
                    <?php echo function_exists('matrix_rd_account_captcha_markup') ? matrix_rd_account_captcha_markup() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                    <div class="flex flex-col gap-4 w-full mt-8">
                        <button x-ref="submitBtn" data-testid="rd-submit-sign-in" class="<?php echo esc_attr($rd_btn); ?> woocommerce-button button woocommerce-form-login__submit" type="submit" name="login" value="<?php esc_attr_e('Sign in', 'woocommerce'); ?>"><?php esc_html_e('Sign in', 'woocommerce'); ?></button>
                        <p class="m-0 text-center font-laca font-light text-sm-font text-black-full">
                            <?php esc_html_e("Don't have an account?", 'woocommerce'); ?>
                            <a class="<?php echo esc_attr($rd_link); ?> font-laca font-light" href="#"
                                @click.prevent="
                                    window.dispatchEvent(new CustomEvent('update-active-tab', { detail: { tab: 'register' } }));
                                    window.dispatchEvent(new CustomEvent('update-show-lost-password', { detail: { show: false } }));
                                ">
                                <?php esc_html_e('Register', 'woocommerce'); ?>
                            </a>
                        </p>
                    </div>

                </form>
                </div>
                </template>

                <template x-if="showLostPassword">
                <div class="w-full">
                <form data-testid="rd-form-lost-password" method="post" class="woocommerce-ResetPassword lost_reset_password w-full max-w-max-704 mx-auto pt-6">
                    <p class="my-4 font-laca text-sm-font"><?php echo apply_filters('woocommerce_lost_password_message', esc_html__('Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce')); ?></p>
                    <label class="<?php echo esc_attr($rd_label); ?>" for="user_login"><?php esc_html_e('Username or email', 'woocommerce'); ?></label>
                    <div class="relative w-full mt-1">
                        <div class="<?php echo esc_attr($rd_icon_wrap); ?>"><?php echo $rd_icon_user; ?></div>
                        <input required class="<?php echo esc_attr($rd_input); ?>" data-testid="rd-field-user-login" type="text" name="user_login" id="user_login" autocomplete="username" placeholder="<?php esc_attr_e('Enter Username or Password', 'woocommerce'); ?>" />
                    </div>

                    <?php do_action('woocommerce_lostpassword_form'); ?>
                    <?php echo function_exists('matrix_rd_account_captcha_markup') ? matrix_rd_account_captcha_markup() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                    <div class="flex flex-col gap-4 w-full mt-8">
                        <input type="hidden" name="wc_reset_password" value="true" />
                        <button type="submit" class="<?php echo esc_attr($rd_btn); ?> woocommerce-Button button" value="<?php esc_attr_e('Reset password', 'woocommerce'); ?>"><?php esc_html_e('Reset password', 'woocommerce'); ?></button>
                        <a class="<?php echo esc_attr($rd_link); ?> text-center text-mob-xs-font" href="#"
                            @click.prevent="
                                window.dispatchEvent(new CustomEvent('update-active-tab', { detail: { tab: 'sign-in' } }));
                                window.dispatchEvent(new CustomEvent('update-show-lost-password', { detail: { show: false } }));
                            ">
                            <?php esc_html_e('Back to Sign in', 'woocommerce'); ?>
                        </a>
                        <?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>
                    </div>
                </form>
                </div>
                </template>

                <template x-if="activeTab === 'register' && !showLostPassword">
                <div class="w-full">
                <form data-testid="rd-form-register" method="post" class="woocommerce-form woocommerce-form-register register w-full max-w-max-564 mx-auto pt-9" <?php do_action('woocommerce_register_form_tag'); ?>>

                    <?php do_action('woocommerce_register_form_start'); ?>

                    <div class="flex flex-wrap macbook:justify-between">
                        <div class="w-full macbook:w-[256px]">
                            <label class="<?php echo esc_attr($rd_label); ?>" for="reg_first_name"><?php esc_html_e('First Name', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
                            <input type="text" required data-testid="rd-field-first-name" class="<?php echo esc_attr($rd_input); ?> mt-2" placeholder="<?php esc_attr_e('First name*', 'woocommerce'); ?>" name="first_name" id="reg_first_name" autocomplete="given-name" />
                        </div>
                        <div class="mt-4 macbook:mt-0 w-full macbook:w-[256px]">
                            <label class="<?php echo esc_attr($rd_label); ?>" for="reg_last_name"><?php esc_html_e('Last Name', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
                            <input type="text" required data-testid="rd-field-last-name" class="<?php echo esc_attr($rd_input); ?> mt-2" placeholder="<?php esc_attr_e('Surname*', 'woocommerce'); ?>" name="last_name" id="reg_last_name" autocomplete="family-name" />
                        </div>
                    </div>

                    <div class="w-full pt-4">
                        <label class="<?php echo esc_attr($rd_label); ?>" for="reg_email"><?php esc_html_e('Email Address', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
                        <input type="email" required data-testid="rd-field-email" class="<?php echo esc_attr($rd_input); ?> mt-1" placeholder="<?php esc_attr_e('Email*', 'woocommerce'); ?>" name="email" id="reg_email" autocomplete="email" />
                    </div>

                    <div class="w-full pt-4">
                        <label class="<?php echo esc_attr($rd_label); ?>" for="reg_password"><?php esc_html_e('Set Password', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
                        <input type="password" required data-testid="rd-field-reg-password" class="<?php echo esc_attr($rd_input_pw); ?> mt-1" placeholder="<?php esc_attr_e('Password*', 'woocommerce'); ?>" name="password" id="reg_password" autocomplete="new-password" />
                    </div>

                    <div class="w-full pt-4">
                        <label class="<?php echo esc_attr($rd_label); ?>" for="confirm_password"><?php esc_html_e('Confirm Password', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
                        <input type="password" required data-testid="rd-field-confirm-password" class="<?php echo esc_attr($rd_input_pw); ?> mt-1" placeholder="<?php esc_attr_e('Confirm Password*', 'woocommerce'); ?>" name="confirm_password" id="confirm_password" />
                    </div>

                    <div class="w-full pt-4 ml-2">
                        <label class="flex woocommerce-form__label woocommerce-form__label-for-checkbox cursor-pointer">
                            <input required data-testid="rd-field-privacy" class="<?php echo esc_attr($rd_checkbox); ?>" type="checkbox" name="privacy_agreement" id="privacy_agreement" />
                            <span class="text-mob-xs-font font-regular"><?php esc_html_e('I agree with the handling of my data in accordance with the company privacy policy.', 'woocommerce'); ?></span>
                        </label>
                    </div>

                    <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                    <?php echo function_exists('matrix_rd_account_captcha_markup') ? matrix_rd_account_captcha_markup() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                    <div class="flex flex-col gap-4 w-full mt-8">
                        <button type="submit" data-testid="rd-submit-register" class="<?php echo esc_attr($rd_btn); ?> woocommerce-button button woocommerce-form-register__submit disabled:bg-yellow-disabled disabled:cursor-not-allowed disabled:border-grey-disabled" name="register" value="<?php esc_attr_e('Register', 'woocommerce'); ?>">
                            <?php esc_html_e('Register', 'woocommerce'); ?>
                        </button>
                        <p class="m-0 text-center font-laca font-light text-sm-font text-black-full">
                            <?php esc_html_e('Already have an account?', 'woocommerce'); ?>
                            <a class="<?php echo esc_attr($rd_link); ?> font-laca font-light" href="#"
                                @click.prevent="
                                    window.dispatchEvent(new CustomEvent('update-active-tab', { detail: { tab: 'sign-in' } }));
                                    window.dispatchEvent(new CustomEvent('update-show-lost-password', { detail: { show: false } }));
                                ">
                                <?php esc_html_e('Login', 'woocommerce'); ?>
                            </a>
                        </p>
                    </div>

                    <?php do_action('woocommerce_register_form_end'); ?>

                </form>
                </div>
                </template>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php do_action('woocommerce_after_customer_login_form'); ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const notice = document.querySelector('.woocommerce-notices-wrapper');
    if (notice) {
      setTimeout(() => notice.classList.add('notice-hide'), 5000);
    }
  });
</script>
