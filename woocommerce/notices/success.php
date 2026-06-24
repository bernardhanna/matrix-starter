<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!$notices) {
    return;
}

?>
<?php foreach ($notices as $notice) : ?>
    <div id="custom-woocommerce-notice"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 text-center bg-transparent success max-ablet:max-h-max woocommerce-noticeslgrapper margin-auto" style="z-index: 10000">
        <div class="w-auto lg:w-[50%] max-w-md border-2 flex items-center justify-between woocommerce-message bg-black-full rounded-sm-12 border-yellow-primary min-h-[100px] " style="min-height: 100px;" role="alert">
            <?php
            $notice_bg_url = get_field('notice_bg', 'option');

            if ($notice_bg_url) :
            ?>
                <img src="<?php echo esc_url($notice_bg_url); ?>"
                    class="relative hidden object-cover w-full h-auto m-auto tablet:block rounded-t-sm-12 bg-black-full">
            <?php endif; ?>

            <div class="relative flex items-start justify-between px-4 m-auto mt-2 text-white">
                <div class="font-light text-center text-white w-1/8 font-laca text-sm-md-font">
                    <?php echo wc_kses_notice($notice['notice']); ?>
                </div>
                <button class="flex items-center justify-center w-auto h-auto px-4 py-2 ml-4 border-2 border-solid rounded-full close-notice-button close-shadow bg-red-critical border-black-full hover:opacity-50 text-black-full text-base-font font-bolder">
                    x
                </button>
            </div>
        </div>
    </div>
<?php endforeach; ?>