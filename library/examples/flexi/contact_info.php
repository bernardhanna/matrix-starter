<?php
$section_id = 'contact-info-' . uniqid();
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$background_color = get_sub_field('background_color');
$use_two_columns_for_two_items_raw = get_sub_field('use_two_columns_for_two_items');
$use_two_columns_for_two_items = ($use_two_columns_for_two_items_raw === null || $use_two_columns_for_two_items_raw === '' || $use_two_columns_for_two_items_raw === 1 || $use_two_columns_for_two_items_raw === '1' || $use_two_columns_for_two_items_raw === true);

$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

$contact_locations = [];
if (have_rows('contact_locations')) {
    while (have_rows('contact_locations')) {
        the_row();
        $contact_locations[] = [
            'office_name'     => get_sub_field('office_name'),
            'address'         => get_sub_field('address'),
            'phone'           => get_sub_field('phone'),
            'email'           => get_sub_field('email'),
            'directions_link' => get_sub_field('directions_link'),
        ];
    }
}

$desktop_grid_columns_class = 'lg:grid-cols-3';
if ($use_two_columns_for_two_items && count($contact_locations) === 2) {
    $desktop_grid_columns_class = 'lg:grid-cols-2';
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden bg-[linear-gradient(313deg,_#059DED_24.08%,_#28B2FA_63%)] <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center pt-10 pb-14 mx-auto w-full max-w-[1104px] max-xl:px-5">

        <?php if (!empty($heading)): ?>
            <<?php echo esc_attr($heading_tag); ?>
                id="<?php echo esc_attr($section_id); ?>-heading"
                class="self-center mb-8 text-3xl font-semibold leading-none text-white"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>
        <?php endif; ?>

        <?php if (!empty($contact_locations)): ?>
            <!-- Desktop / large screens -->
            <div class="hidden gap-8 items-stretch w-full lg:grid <?php echo esc_attr($desktop_grid_columns_class); ?> max-md:max-w-full" role="list">
                <?php foreach ($contact_locations as $location):
                    $office_name = $location['office_name'];
                    $address = $location['address'];
                    $phone = $location['phone'];
                    $email = $location['email'];
                    $directions_link = $location['directions_link'];
                ?>
                    <article class="flex flex-col p-8 min-w-0 h-full bg-white rounded-lg max-md:px-5" role="listitem">

                        <!-- Office Address Section -->
                        <div class="flex gap-2 items-start w-full">
                            <span class="inline-flex mt-0.5 shrink-0" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="block w-5 h-6" width="20" height="24" viewBox="0 0 20 24" fill="none">
                                    <path d="M19 10C19 17 10 23 10 23C10 23 1 17 1 10C1 7.61305 1.94821 5.32387 3.63604 3.63604C5.32387 1.94821 7.61305 1 10 1C12.3869 1 14.6761 1.94821 16.364 3.63604C18.0518 5.32387 19 7.61305 19 10Z" stroke="#00628F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 13C11.6569 13 13 11.6569 13 10C13 8.34315 11.6569 7 10 7C8.34315 7 7 8.34315 7 10C7 11.6569 8.34315 13 10 13Z" stroke="#00628F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>

                            <div class="flex flex-col flex-1 min-w-0">
                                <?php if (!empty($office_name) || !empty($address)): ?>
                                    <address class="not-italic">
                                        <?php if (!empty($office_name)): ?>
                                            <strong class="block font-sans text-[14px] font-bold not-italic leading-5 text-[var(--Blue-SR-500,#00628F)]">
                                                <?php echo esc_html($office_name); ?>
                                            </strong>
                                        <?php endif; ?>
                                        <?php if (!empty($address)) : ?>
                                            <div
                                                class="wp_editor block mt-1 font-sans !text-[14px] !font-normal !not-italic !leading-[20px] !text-[var(--Gray-800,#001929)] [&_p]:!mt-2 [&_p:first-child]:!mt-0 [&_p]:!font-sans [&_p]:!text-[14px] [&_p]:!not-italic [&_p]:!font-normal [&_p]:!leading-[20px] [&_p]:!text-[var(--Gray-800,#001929)]"
                                            >
                                                <?php echo wp_kses_post($address); ?>
                                            </div>
                                        <?php endif; ?>
                                    </address>
                                <?php endif; ?>

                                <?php if ($directions_link && is_array($directions_link) && isset($directions_link['url'], $directions_link['title'])): ?>
                                    <div class="flex flex-col justify-center self-start pt-0.5 mt-1 whitespace-nowrap">
                                        <div class="flex gap-1 items-center">
                                            <a
                                                href="<?php echo esc_url($directions_link['url']); ?>"
                                                class="btn self-stretch my-auto font-sans text-[14px] font-bold not-italic leading-5 text-[var(--Blue-SR-500,#00628F)] hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--Blue-SR-500,#00628F)] focus:ring-offset-2"
                                                target="<?php echo esc_attr($directions_link['target'] ?? '_self'); ?>"
                                                aria-label="Get directions to <?php echo esc_attr($office_name); ?>"
                                            >
                                                <?php echo esc_html($directions_link['title']); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Phone Section -->
                        <?php if (!empty($phone)): ?>
                            <div class="flex gap-2 items-center mt-4 w-full min-w-0">
                                <span class="inline-flex shrink-0" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="block h-[22px] w-[22px]" width="22" height="22" viewBox="0 0 22 22" fill="none">
                                        <path d="M20.8881 15.9201V18.9201C20.8892 19.1986 20.8322 19.4743 20.7206 19.7294C20.6091 19.9846 20.4454 20.2137 20.2402 20.402C20.035 20.5902 19.7927 20.7336 19.5289 20.8228C19.265 20.912 18.9855 20.9452 18.7081 20.9201C15.631 20.5857 12.6751 19.5342 10.0781 17.8501C7.66194 16.3148 5.61345 14.2663 4.07812 11.8501C2.38809 9.2413 1.33636 6.27109 1.00812 3.1801C0.983127 2.90356 1.01599 2.62486 1.10462 2.36172C1.19324 2.09859 1.33569 1.85679 1.52288 1.65172C1.71008 1.44665 1.93792 1.28281 2.19191 1.17062C2.44589 1.05843 2.72046 1.00036 2.99812 1.0001H5.99812C6.48342 0.995321 6.95391 1.16718 7.32188 1.48363C7.68985 1.80008 7.93019 2.23954 7.99812 2.7201C8.12474 3.68016 8.35957 4.62282 8.69812 5.5301C8.83266 5.88802 8.86178 6.27701 8.78202 6.65098C8.70227 7.02494 8.51698 7.36821 8.24812 7.6401L6.97812 8.9101C8.40167 11.4136 10.4746 13.4865 12.9781 14.9101L14.2481 13.6401C14.52 13.3712 14.8633 13.1859 15.2372 13.1062C15.6112 13.0264 16.0002 13.0556 16.3581 13.1901C17.2654 13.5286 18.2081 13.7635 19.1681 13.8901C19.6539 13.9586 20.0975 14.2033 20.4146 14.5776C20.7318 14.9519 20.9003 15.4297 20.8881 15.9201Z" stroke="#00628F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <div class="flex flex-col flex-1 items-start self-stretch my-auto min-w-0">
                                    <div class="flex flex-col justify-center pt-0.5 min-w-0">
                                        <div class="flex gap-1 items-center min-w-0">
                                            <a
                                                href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"
                                                class="btn self-stretch my-auto min-w-0 font-sans text-[14px] font-bold not-italic leading-5 text-[var(--Blue-SR-500,#00628F)] hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--Blue-SR-500,#00628F)] focus:ring-offset-2"
                                                aria-label="Call <?php echo esc_attr($office_name); ?> at <?php echo esc_attr($phone); ?>"
                                            >
                                                <?php echo esc_html($phone); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Email Section -->
                        <?php if (!empty($email)): ?>
                            <div class="flex gap-2 items-center mt-4 w-full min-w-0">
                                <span class="inline-flex shrink-0" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="block w-6 h-6" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M22 6C22 4.9 21.1 4 20 4H4C2.9 4 2 4.9 2 6M22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6M22 6L12 13L2 6" stroke="#00628F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>

                                <div class="flex flex-col flex-1 items-start self-stretch my-auto min-w-0">
                                    <div class="flex flex-col justify-center pt-0.5 min-w-0">
                                        <div class="flex gap-1 items-center min-w-0">
                                            <a
                                                href="mailto:<?php echo esc_attr($email); ?>"
                                                class="btn self-stretch my-auto min-w-0 break-all font-sans text-[14px] font-bold not-italic leading-5 text-[var(--Blue-SR-500,#00628F)] hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--Blue-SR-500,#00628F)] focus:ring-offset-2 sm:break-normal"
                                                aria-label="Email <?php echo esc_attr($office_name); ?> at <?php echo esc_attr($email); ?>"
                                            >
                                                <?php echo esc_html($email); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Mobile / tablet accordion -->
            <div class="flex flex-col gap-3 w-full lg:hidden" role="list">
                <?php foreach ($contact_locations as $index => $location):
                    $office_name = $location['office_name'];
                    $address = $location['address'];
                    $phone = $location['phone'];
                    $email = $location['email'];
                    $directions_link = $location['directions_link'];
                    $details_id = $section_id . '-location-' . $index;
                ?>
                    <details
                        class="overflow-hidden bg-white rounded-lg group"
                        role="listitem"
                        <?php echo $index === 0 ? 'open' : ''; ?>
                    >
                        <summary
                            class="flex gap-2 justify-between items-center p-5 w-full list-none cursor-pointer [&::-webkit-details-marker]:hidden"
                            aria-controls="<?php echo esc_attr($details_id); ?>"
                        >
                            <div class="flex gap-2 items-center min-w-0">
                                <span class="inline-flex mt-0.5 shrink-0" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="block w-5 h-6" width="20" height="24" viewBox="0 0 20 24" fill="none">
                                        <path d="M19 10C19 17 10 23 10 23C10 23 1 17 1 10C1 7.61305 1.94821 5.32387 3.63604 3.63604C5.32387 1.94821 7.61305 1 10 1C12.3869 1 14.6761 1.94821 16.364 3.63604C18.0518 5.32387 19 7.61305 19 10Z" stroke="#00628F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M10 13C11.6569 13 13 11.6569 13 10C13 8.34315 11.6569 7 10 7C8.34315 7 7 8.34315 7 10C7 11.6569 8.34315 13 10 13Z" stroke="#00628F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <strong class="block min-w-0 font-sans text-[14px] font-bold not-italic leading-5 text-[var(--Blue-SR-500,#00628F)]">
                                    <?php echo esc_html($office_name); ?>
                                </strong>
                            </div>
                            <span class="accordion-chevron inline-flex shrink-0 ml-3 text-[var(--Blue-SR-500,#00628F)] transition-transform duration-200 group-open:rotate-180" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="block w-5 h-5" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M5 8L10 13L15 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </summary>

                        <div id="<?php echo esc_attr($details_id); ?>" class="px-5 pb-5">
                            <?php if (!empty($address)): ?>
                                <address class="not-italic">
                                    <div class="wp_editor block mt-1 font-sans !text-[14px] !font-normal !not-italic !leading-[20px] !text-[var(--Gray-800,#001929)] [&_p]:!mt-2 [&_p:first-child]:!mt-0 [&_p]:!font-sans [&_p]:!text-[14px] [&_p]:!not-italic [&_p]:!font-normal [&_p]:!leading-[20px] [&_p]:!text-[var(--Gray-800,#001929)]">
                                        <?php echo wp_kses_post($address); ?>
                                    </div>
                                </address>
                            <?php endif; ?>

                            <?php if ($directions_link && is_array($directions_link) && isset($directions_link['url'], $directions_link['title'])): ?>
                                <div class="mt-1">
                                    <a
                                        href="<?php echo esc_url($directions_link['url']); ?>"
                                        class="btn font-sans text-[14px] font-bold not-italic leading-5 text-[var(--Blue-SR-500,#00628F)] hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--Blue-SR-500,#00628F)] focus:ring-offset-2"
                                        target="<?php echo esc_attr($directions_link['target'] ?? '_self'); ?>"
                                        aria-label="Get directions to <?php echo esc_attr($office_name); ?>"
                                    >
                                        <?php echo esc_html($directions_link['title']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($phone)): ?>
                                <div class="flex gap-2 items-center mt-4 w-full min-w-0">
                                    <span class="inline-flex shrink-0" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="block h-[22px] w-[22px]" width="22" height="22" viewBox="0 0 22 22" fill="none">
                                            <path d="M20.8881 15.9201V18.9201C20.8892 19.1986 20.8322 19.4743 20.7206 19.7294C20.6091 19.9846 20.4454 20.2137 20.2402 20.402C20.035 20.5902 19.7927 20.7336 19.5289 20.8228C19.265 20.912 18.9855 20.9452 18.7081 20.9201C15.631 20.5857 12.6751 19.5342 10.0781 17.8501C7.66194 16.3148 5.61345 14.2663 4.07812 11.8501C2.38809 9.2413 1.33636 6.27109 1.00812 3.1801C0.983127 2.90356 1.01599 2.62486 1.10462 2.36172C1.19324 2.09859 1.33569 1.85679 1.52288 1.65172C1.71008 1.44665 1.93792 1.28281 2.19191 1.17062C2.44589 1.05843 2.72046 1.00036 2.99812 1.0001H5.99812C6.48342 0.995321 6.95391 1.16718 7.32188 1.48363C7.68985 1.80008 7.93019 2.23954 7.99812 2.7201C8.12474 3.68016 8.35957 4.62282 8.69812 5.5301C8.83266 5.88802 8.86178 6.27701 8.78202 6.65098C8.70227 7.02494 8.51698 7.36821 8.24812 7.6401L6.97812 8.9101C8.40167 11.4136 10.4746 13.4865 12.9781 14.9101L14.2481 13.6401C14.52 13.3712 14.8633 13.1859 15.2372 13.1062C15.6112 13.0264 16.0002 13.0556 16.3581 13.1901C17.2654 13.5286 18.2081 13.7635 19.1681 13.8901C19.6539 13.9586 20.0975 14.2033 20.4146 14.5776C20.7318 14.9519 20.9003 15.4297 20.8881 15.9201Z" stroke="#00628F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <a
                                        href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"
                                        class="btn min-w-0 font-sans text-[14px] font-bold not-italic leading-5 text-[var(--Blue-SR-500,#00628F)] hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--Blue-SR-500,#00628F)] focus:ring-offset-2"
                                        aria-label="Call <?php echo esc_attr($office_name); ?> at <?php echo esc_attr($phone); ?>"
                                    >
                                        <?php echo esc_html($phone); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($email)): ?>
                                <div class="flex gap-2 items-center mt-4 w-full min-w-0">
                                    <span class="inline-flex shrink-0" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="block w-6 h-6" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M22 6C22 4.9 21.1 4 20 4H4C2.9 4 2 4.9 2 6M22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6M22 6L12 13L2 6" stroke="#00628F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <a
                                        href="mailto:<?php echo esc_attr($email); ?>"
                                        class="btn min-w-0 break-all font-sans text-[14px] font-bold not-italic leading-5 text-[var(--Blue-SR-500,#00628F)] hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--Blue-SR-500,#00628F)] focus:ring-offset-2 sm:break-normal"
                                        aria-label="Email <?php echo esc_attr($office_name); ?> at <?php echo esc_attr($email); ?>"
                                    >
                                        <?php echo esc_html($email); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endforeach; ?>
           </div>
        <?php endif; ?>

    </div>
</section>