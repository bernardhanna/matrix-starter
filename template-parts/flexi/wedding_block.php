<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2025-02-21 11:07:45
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2025-03-07 16:29:04
 */
?>
<?php
$weddingTitle = get_sub_field('wedding_title') ?: 'Your dream party, Perfect wedding';
    $weddingText =
        get_sub_field('wedding_text') ?:
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    $weddingPaddingTop = get_sub_field('wedding_padding_top') ?: '0';
    $weddingPaddingBottom = get_sub_field('wedding_padding_bottom') ?: '0';
?>
<div class="flex flex-col items-center justify-center w-full px-4" style="padding-top: <?php echo $weddingPaddingTop; ?>rem;">
    <span class="leading-10 text-center text-black text-md-font lg:text-lg-font font-reg420"><?php echo $weddingTitle; ?></span>
    <p
        class="w-full py-8 mx-auto mb-12 font-light leading-none text-center text-reg-font lg:text-mob-md-font text-black-font lg:w-3/4">
        <?php echo $weddingText; ?>
    </p>
</div>


<div class="mb-12 wedding-grid-container pb-<?php echo $weddingPaddingBottom; ?>rem">
    <div class="max-w-6xl px-4 m-auto">
        <div class="grid-container">
            <?php if (have_rows('wedding_products') : ?>
)
                <?php while (have_rows('wedding_products') : ?>
)
                    <?php
the_row(); ?>
<?php
$productImage = get_sub_field('wedding_image');
                        $productHoverImage = get_sub_field('wedding_image_hover');
                        $productLink = get_sub_field('wedding_link');
                        $productTitle = get_sub_field('product_title');
                        $productDescription = get_sub_field('product_desc');
                    ?>
<div class="grid-item">
                        <div class="cursor-pointer group ">
                            <div class="image-container">
                                <img class="cursor-pointer main-image" src="<?php echo $productImage['url']; ?>"
                                    alt="<?php echo $productImage['alt']; ?>">
                                <?php if ($productHoverImage) : ?>
<img class="hover-image" src="<?php echo $productHoverImage['url']; ?>"
                                        alt="<?php echo $productHoverImage['alt']; ?>">
                                <?php endif; ?>
</div>
                            <div class="product-details">
                                <h3 class="leading-10 text-black text-mob-md-font font-reg420"><?php echo $productTitle; ?></h3>
                                <p class="leading-7 text-black text-mob-md-font font-lighter font-laca">
                                    <?php echo $productDescription; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
<?php endif; ?>
</div>
    </div>
</div>

<style>
    /* Grid layout */
    .grid-container {
        display: grid;
        gap: 1.5rem;
        grid-template-columns: repeat(3, 1fr);
        /* 2 per row by default */
        max-width: 1364px;
        margin: auto;
    }

    /* Responsive grid */
    @media (max-width: 1124px) {
        .grid-container {
            grid-template-columns: repeat(2, 1fr);
            /* 1 per row on smaller devices */
        }
    }

    @media (max-width: 768px) {
        .grid-container {
            grid-template-columns: repeat(1, 1fr);
            /* 1 per row on smaller devices */
        }
    }

    /* Grid item styling */
    .grid-item {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 4px solid black;
        border-radius: 12px;
        box-shadow: 2px 4px 10px rgba(0, 0, 0, 0.1);
    }

    /* Image container */
    .image-container {
        position: relative;
        overflow: hidden;
        width: 100%;
        height: 600px;
    }

    /* Main and hover images */
    .main-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease-in-out;
    }

    .hover-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    }

    .image-container:hover .hover-image {
        opacity: 1;
    }

    /* Product details */
    .product-details {
        padding: 1rem;
        background: white;
    }

    .product-details h3 {
        font-size: 1.25rem;
        font-weight: bold;
        color: black;
        margin-bottom: 0.5rem;
    }

    .product-details p {
        font-size: 1rem;
        color: #4A4A4A;
    }
</style>
