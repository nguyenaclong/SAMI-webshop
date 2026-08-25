<?php
/**
 * Custom Shop Page main layout template.
 *
 * Restructured to follow the exact workflow sequence and premium visuals
 * matching the user specifications.
 */

// Load categories at the top and sort them hierarchically
$all_categories = get_terms([ 'taxonomy' => 'product_cat', 'hide_empty' => true ]);
$categories = [];

if ( ! is_wp_error( $all_categories ) && ! empty( $all_categories ) ) {
    $categories_by_id = [];
    foreach ( $all_categories as $cat ) {
        $categories_by_id[$cat->term_id] = $cat;
    }

    $root_categories = [];
    $children_map = [];

    foreach ( $all_categories as $cat ) {
        if ( $cat->parent == 0 || ! isset( $categories_by_id[$cat->parent] ) ) {
            $root_categories[] = $cat;
        } else {
            $children_map[$cat->parent][] = $cat;
        }
    }

    foreach ( $root_categories as $parent_cat ) {
        $parent_cat->is_child = false;
        $categories[] = $parent_cat;

        if ( isset( $children_map[$parent_cat->term_id] ) ) {
            foreach ( $children_map[$parent_cat->term_id] as $child_cat ) {
                $child_cat->is_child = true;
                $categories[] = $child_cat;
            }
        }
    }
}

// Retrieve selected values from WC session if available
// Bug #1 fix: keys must match what save_pickup_time_session() stores
$saved_time = '';
$saved_date = '';
$chosen_method = 'pickup';
$saved_address = '';
$saved_distance = '';
if ( function_exists('WC') && WC()->session ) {
    $saved_time = WC()->session->get( 'pickup_time' );
    $saved_date = WC()->session->get( 'pickup_date' );
    $saved_address = WC()->session->get( 'delivery_address' );
    $saved_distance = WC()->session->get( 'delivery_distance' );
    $chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );
    if ( is_array( $chosen_shipping ) ) {
        foreach ( $chosen_shipping as $method ) {
            if ( strpos( $method, 'flat_rate' ) !== false ) {
                $chosen_method = 'delivery';
                break;
            }
        }
    }
}

$is_pickup_enabled = get_option( 'pickup_enable_pickup', 'yes' ) === 'yes';
$is_delivery_enabled = get_option( 'pickup_enable_delivery', 'yes' ) === 'yes';
$price_display_mode = get_option( 'custom_shop_variable_price_display', 'min' );
$show_cat_image     = get_option( 'custom_shop_show_cat_image', 'no' ) === 'yes';
$show_cat_desc      = get_option( 'custom_shop_show_cat_desc', 'yes' ) === 'yes';
$cat_text_align     = get_option( 'custom_shop_cat_text_align', 'left' );

if ( ! $is_pickup_enabled ) {
    $chosen_method = 'delivery';
} elseif ( ! $is_delivery_enabled ) {
    $chosen_method = 'pickup';
}

$restaurant_name = tsw_get_restaurant_name();
$pickup_address  = tsw_get_store_address();
$store_logo_url  = tsw_get_store_logo_url();
?>
<div class="<?php echo esc_attr( $container_class_str ); ?>" id="csp-webshop-container" data-pickup-enabled="<?php echo esc_attr( $is_pickup_enabled ? 'yes' : 'no' ); ?>" data-delivery-enabled="<?php echo esc_attr( $is_delivery_enabled ? 'yes' : 'no' ); ?>">
    
    <!-- Sticky Header Wrapper (Brand Header + Subheader Selector Tabs) -->
    <div class="csp-sticky-header-wrapper">
        <!-- 1. N14 Sticky Brand Header -->
        <div class="csp-n14-brand-header">
            <div class="csp-n14-brand-left">
                <?php
                if ( ! empty( $store_logo_url ) ) {
                    echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="csp-brand-logo-link"><img src="' . esc_url( $store_logo_url ) . '" alt="' . esc_attr( $restaurant_name ) . '" class="csp-brand-logo"></a>';
                }
                ?>
                <div class="csp-brand-info">
                    <h1 class="csp-brand-title"><?php echo esc_html( $restaurant_name ); ?></h1>
                    <div class="csp-brand-meta">
                        <span class="csp-brand-address"><?php echo esc_html( $pickup_address ); ?></span>
                        <span class="csp-brand-separator">•</span>
                        <span class="csp-brand-hours"><?php echo esc_html( $n14_opening_hours ); ?></span>
                        <span class="csp-brand-separator">•</span>
                        <?php 
                        $is_currently_open = TSW_Pickup_Scheduler::is_store_currently_open();
                        if ( $is_currently_open ) {
                            echo '<span class="csp-brand-status open">' . esc_html__( 'Open', '2-step-webshop' ) . '</span>';
                        } else {
                            $opening_time = get_option( 'pickup_opening_time', '11:30' );
                            echo '<span class="csp-brand-status closed">' . sprintf( esc_html__( 'Opens at %s', '2-step-webshop' ), esc_html( $opening_time ) ) . '</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="csp-n14-brand-right">
    
            </div>
        </div>
    
        <!-- 2. Sub-Header Horizontal Selector Tabs -->
        <div class="csp-subheader-tabs-bar">
            <ul class="csp-subheader-tabs">
                <li class="active" data-tab="menu"><a href="#"><?php esc_html_e( 'Menu', '2-step-webshop' ); ?></a></li>
                <li class="csp-info-trigger" data-tab="info"><a href="#"><?php esc_html_e( 'Store Info', '2-step-webshop' ); ?></a></li>
            </ul>
        </div>

        <!-- 2b. Sticky Category Bar (Hamburger + Search + Scrollable Categories) -->
        <div class="csp-category-bar" id="csp-category-bar">
            <button type="button" class="csp-catbar-btn" id="csp-hamburger-btn" aria-label="<?php esc_attr_e( 'Open categories', '2-step-webshop' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                </svg>
            </button>
            <button type="button" class="csp-catbar-btn" id="csp-search-toggle" aria-label="<?php esc_attr_e( 'Search', '2-step-webshop' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                </svg>
            </button>
            <div class="csp-catbar-scroll" id="csp-catbar-scroll">
                <?php
                if ( ! empty( $root_categories ) ) {
                    $is_first_cat = true;
                    foreach ( $root_categories as $parent_cat ) {
                        $active_cls = $is_first_cat ? ' active' : '';
                        $is_first_cat = false;
                        ?>
                        <button type="button" class="csp-catbar-pill<?php echo esc_attr( $active_cls ); ?>" data-slug="<?php echo esc_attr( $parent_cat->slug ); ?>"><?php echo esc_html( $parent_cat->name ); ?></button>
                        <?php
                    }
                }
                ?>
            </div>
            
            <!-- Search Overlay (replaces category bar when active) -->
            <div class="csp-search-overlay" id="csp-search-overlay" style="display: none;">
                <input type="search" id="csp-search-overlay-input" class="csp-search-overlay-field" placeholder="<?php esc_attr_e( 'Search', '2-step-webshop' ); ?>" autocomplete="off">
                <button type="button" id="csp-search-cancel" class="csp-search-cancel-btn"><?php esc_html_e( 'Cancel', '2-step-webshop' ); ?></button>
            </div>
        </div>
    </div>
    <!-- 3. Rewards Banner removed (no points system) -->

    <!-- Category Modal Drawer (slide-in from left) -->
    <div id="csp-category-modal" class="csp-category-modal-overlay" style="display: none;">
        <div class="csp-category-modal-drawer">
            <div class="csp-category-modal-header">
                <h3 class="csp-category-modal-title"><?php esc_html_e( 'Categories', '2-step-webshop' ); ?></h3>
                <button type="button" class="csp-category-modal-close" aria-label="<?php esc_attr_e( 'Close', '2-step-webshop' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                </button>
            </div>
            <nav class="csp-category-modal-nav">
                <?php
                if ( ! empty( $root_categories ) ) {
                    foreach ( $root_categories as $parent_cat ) {
                        ?>
                        <button type="button" class="csp-category-modal-item" data-slug="<?php echo esc_attr( $parent_cat->slug ); ?>">
                            <?php echo esc_html( $parent_cat->name ); ?>
                        </button>
                        <?php
                        if ( ! empty( $children_map[$parent_cat->term_id] ) ) {
                            foreach ( $children_map[$parent_cat->term_id] as $child_cat ) {
                                ?>
                                <button type="button" class="csp-category-modal-item is-child" data-slug="<?php echo esc_attr( $child_cat->slug ); ?>">
                                    <?php echo esc_html( $child_cat->name ); ?>
                                </button>
                                <?php
                            }
                        }
                    }
                }
                ?>
            </nav>
        </div>
    </div>

    <?php
    $hero_banner_image = get_option( 'custom_shop_hero_image', '' );
    if ( empty( $hero_banner_image ) ) {
        $hero_banner_image = 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?q=80&w=1200&auto=format&fit=crop';
    }
    ?>
    <!-- 4. Hero Banner (optional fallback) -->
    <div class="shop-hero-banner" style="background-image: url('<?php echo esc_url( $hero_banner_image ); ?>'); display: none;">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title"><?php echo esc_html( $restaurant_name ); ?></h1>
        </div>
    </div>

    <!-- New Top Banner and Controls Section -->
    <div class="csp-banner-controls-section">
        <div class="csp-banner-wrapper">
            <img src="<?php echo esc_url( $hero_banner_image ); ?>" alt="<?php echo esc_attr( $restaurant_name ); ?>" class="csp-banner-img">
        </div>
        <div class="csp-banner-controls-row">
            <!-- Left: Method Switcher Capsule -->
            <div class="csp-method-switcher-capsule" style="<?php echo (! $is_pickup_enabled || ! $is_delivery_enabled) ? 'display: none !important;' : ''; ?>">
                <button type="button" class="csp-method-btn <?php echo ($chosen_method === 'pickup') ? 'active' : ''; ?>" data-method="pickup" <?php echo (! $is_pickup_enabled) ? 'disabled data-disabled="yes"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-bag" viewBox="0 0 16 16">
                        <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z"/>
                    </svg>
                    <span><?php esc_html_e( 'Pickup', '2-step-webshop' ); ?></span>
                </button>
                <button type="button" class="csp-method-btn <?php echo ($chosen_method === 'delivery') ? 'active' : ''; ?>" data-method="delivery" <?php echo (! $is_delivery_enabled) ? 'disabled data-disabled="yes"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-bicycle" viewBox="0 0 16 16">
                        <path d="M4 4.5a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1v.5h4.14l.386-1.158A.5.5 0 0 1 11 4h1a.5.5 0 0 1 0 1h-.64l-.311.935A1.5 1.5 0 1 1 9.5 7.5H6v2.5h2a.5.5 0 0 1 0 1H6v1.5a.5.5 0 0 1-1 0V11H4a1 1 0 0 1-1-1v-.5H2a.5.5 0 0 1 0-1h1v-4H1.5a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                    <span><?php esc_html_e( 'Delivery', '2-step-webshop' ); ?></span>
                </button>
            </div>

            <!-- Right: Combined settings button -->
            <button type="button" class="csp-combined-settings-btn" id="csp-combined-settings-trigger">
                <span class="csp-combined-btn-left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
                        <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                    </svg>
                    <span class="csp-combined-method-address-text">
                        <?php 
                        if ($chosen_method === 'pickup') {
                            printf( esc_html__( 'Pickup at %s', '2-step-webshop' ), esc_html( $pickup_address ) );
                        } else {
                            if ( ! empty( $saved_address ) ) {
                                $dist_str = $saved_distance ? ' (' . number_format( (float) $saved_distance, 2 ) . ' km)' : '';
                                $addr_parts = explode( ',', $saved_address );
                                $short_addr = $addr_parts[0] . ( isset( $addr_parts[1] ) ? ', ' . trim( $addr_parts[1] ) : '' );
                                printf( esc_html__( 'Delivery to %s', '2-step-webshop' ), esc_html( $short_addr ) );
                                echo esc_html( $dist_str );
                            } else {
                                echo esc_html__( 'Delivery to Stuttgart', '2-step-webshop' );
                            }
                        }
                        ?>
                    </span>
                </span>
                
                <span class="csp-combined-btn-divider">|</span>
                
                <span class="csp-combined-btn-right">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-clock-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                    </svg>
                    <span class="csp-combined-time-text">
                        <?php 
                        if ( ! empty( $saved_date ) && ! empty( $saved_time ) ) {
                            echo esc_html( date_i18n( 'D d. M', strtotime( $saved_date ) ) . ', ' . $saved_time );
                        } else {
                            echo esc_html__( 'Schedule for later', '2-step-webshop' );
                        }
                        ?>
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </span>
            </button>
        </div>
    </div>

    <!-- 5. Main Catalog Layout -->
    <div class="shop-body-layout">
        
        <!-- Sidebar: Search & Categories List -->
        <aside class="shop-sidebar">
            <div class="sidebar-inner">
                <!-- Search bar input -->
                <div class="csp-sidebar-search-wrapper">
                    <input type="text" id="custom-live-search" class="shop-search-input" placeholder="<?php esc_attr_e( 'Search', '2-step-webshop' ); ?>">
                    <span class="search-icon-right">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>
                    </span>
                </div>

                <!-- Categories list with sub-infos -->
                <ul class="shop-categories-list">
                    <?php
                    if ( ! empty( $root_categories ) ) {
                        $is_first = true;
                        foreach ( $root_categories as $parent_cat ) {
                            $li_class = 'is-parent' . ( $is_first ? ' active' : '' );
                            $is_first = false;
                            
                            // Load category description or subtitle if set
                            $category_subtitle = ! empty( $parent_cat->description ) ? $parent_cat->description : '';
                            ?>
                            <li class="<?php echo esc_attr( $li_class ); ?>" data-slug="<?php echo esc_attr( $parent_cat->slug ); ?>">
                                <div class="category-item-row">
                                    <a href="#cat-<?php echo esc_attr( $parent_cat->slug ); ?>" class="cat-link"><?php echo esc_html( $parent_cat->name ); ?></a>
                                </div>

                                <?php if ( ! empty( $children_map[$parent_cat->term_id] ) ) : ?>
                                    <ul class="shop-subcategories-list">
                                        <?php foreach ( $children_map[$parent_cat->term_id] as $child_cat ) : ?>
                                            <li class="is-child" data-slug="<?php echo esc_attr( $child_cat->slug ); ?>">
                                                <a href="#cat-<?php echo esc_attr( $child_cat->slug ); ?>" class="cat-link"><?php echo esc_html( $child_cat->name ); ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </div>
        </aside>

        <!-- Right Main Column: Products list -->
        <main class="shop-main-content">
            <?php
            foreach($root_categories as $category) : 
                
                $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
                $image_url = wp_get_attachment_url( $thumbnail_id );
            ?>
                <section id="cat-<?php echo esc_attr($category->slug); ?>" class="category-section is-parent-category csp-cat-align-<?php echo esc_attr( $cat_text_align ); ?>">
                    <!-- Category Header Column: Image -> Title -> Description -->
                    <div class="category-header csp-cat-header-col">
                        <?php if ( $show_cat_image && ! empty( $image_url ) ) : ?>
                            <div class="category-header-image-wrapper">
                                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $category->name ); ?>" class="category-header-image">
                            </div>
                        <?php endif; ?>

                        <div class="cat-info">
                            <h2><?php echo esc_html($category->name); ?></h2>
                            <?php if ( $show_cat_desc && ! empty( $category->description ) ) : ?>
                                <div class="category-description-content">
                                    <?php echo wp_kses_post( $category->description ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php
                    $products_query = new WP_Query([
                        'post_type' => 'product',
                        'posts_per_page' => -1,
                        'orderby' => 'ID',
                        'order' => 'ASC',
                        'tax_query' => [[
                            'taxonomy'         => 'product_cat',
                            'field'            => 'term_id',
                            'terms' => $category->term_id,
                            'include_children' => false,
                        ]]
                    ]);
                    ?>

                    <!-- Products Loop inside Category -->
                    <?php if($products_query->have_posts()) : ?>
                    <div class="category-products">
                        <?php
                            while($products_query->have_posts()) : $products_query->the_post(); 
                                $product = wc_get_product( get_the_ID() );
                                if ( ! $product ) continue;
                                $is_variable = $product->is_type('variable');
                                $product_image_id = $product->get_image_id();
                                $product_image_url = $product_image_id ? wp_get_attachment_image_src( $product_image_id, 'large' ) : false;
                                $product_image_full = $product_image_id ? wp_get_attachment_image_src( $product_image_id, 'full' ) : false;
                                
                                $sales_count = (int) $product->get_total_sales();
                                $is_popular = $sales_count > 10;
                                $pop_index = rand(2, 5);
                        ?>
                                <div class="custom-product-item <?php echo $is_variable ? 'is-variable' : 'is-simple'; ?> <?php echo $product_image_url ? 'has-product-image' : 'no-product-image'; ?> csp-n14-card"
                                    data-id="<?php echo absint( $product->get_id() ); ?>"
                                    data-full-image="<?php echo esc_url( $product_image_full ? $product_image_full[0] : '' ); ?>"
                                    <?php if ($is_variable): ?>
                                        data-product_id="<?php echo absint( $product->get_id() ); ?>"
                                        data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $product->get_available_variations() ) ); ?>"
                                    <?php endif; ?>
                                >
                                    <?php if (!$is_variable): ?>
                                    <form class="cart simple-product-form" action="<?php echo esc_url( $product->add_to_cart_url() ); ?>" method="post" enctype="multipart/form-data" style="margin:0;">
                                    <?php else: ?>
                                    <form class="variations_form cart" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post" enctype='multipart/form-data' style="margin:0;">
                                    <?php endif; ?>

                                        <!-- Card Layout Grid -->
                                        <div class="product-top-row">
                                            <!-- Left Info Section -->
                                            <div class="product-info-left">
                                                <h3 class="product-item-title"><?php the_title(); ?></h3>
                                                
                                                <div class="product-price-likes-row">
                                                    <span class="csp-card-price">
                                                        <?php 
                                                        if ( $product->is_type( 'variable' ) ) {
                                                            if ( 'range' === $price_display_mode ) {
                                                                echo $product->get_price_html();
                                                            } else {
                                                                echo wc_price( $product->get_variation_price( 'min', true ) );
                                                            }
                                                        } else {
                                                            echo $product->get_price_html();
                                                        }
                                                        ?>
                                                    </span>
                                                </div>

                                                <div class="product-excerpt">
                                                    <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                                </div>
                                            </div>

                                            <!-- Right Column (Image/No image check) -->
                                            <?php if ( $product_image_url ) : ?>
                                                <div class="product-image-action-right">
                                                    <div class="product-image-square-wrapper">
                                                        <img src="<?php echo esc_url( $product_image_url[0] ); ?>" alt="<?php the_title_attribute(); ?>" class="product-item-image">
                                                        <!-- Plus button floating at bottom-right corner of image -->
                                                        <button type="button" class="csp-n14-plus-btn" aria-label="<?php esc_attr_e( 'Open details', '2-step-webshop' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg></button>
                                                    </div>
                                                </div>
                                            <?php else : ?>
                                                <!-- Action column for No-Image product cards -->
                                                <div class="product-noimage-action-right">
                                                    <button type="button" class="csp-n14-plus-btn no-img-plus" aria-label="<?php esc_attr_e( 'Open details', '2-step-webshop' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg></button>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Hidden inputs for WC state machine compatibility -->
                                        <div class="csp-hidden-form-elements" style="display: none !important;">
                                            <input type="number" name="quantity" class="qty" value="1" min="1" step="1">
                                            <?php if(!$is_variable): ?>
                                                <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>">
                                                <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>">
                                            <?php endif; ?>
                                            <?php if ($is_variable): ?>
                                                <div class="variations-container">
                                                    <?php 
                                                    $variations = $product->get_variation_attributes();
                                                    foreach ( $variations as $attribute_name => $options ) : 
                                                    ?>
                                                        <div class="variation-select-wrapper">
                                                            <label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label>
                                                            <?php
                                                            wc_dropdown_variation_attribute_options(
                                                                array(
                                                                    'options'   => $options,
                                                                    'attribute' => $attribute_name,
                                                                    'product'   => $product,
                                                                    'class'     => 'variation-dropdown',
                                                                )
                                                            );
                                                            ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="single_variation_wrap" style="display:none;"></div>
                                            <?php endif; ?>
                                            <button type="submit" class="single_add_to_cart_button button alt">+</button>
                                        </div>

                                    </form>
                                </div>
                        <?php 
                            endwhile; 
                            wp_reset_postdata();
                        ?>
                    </div>
                    <?php endif; ?>

                    <!-- Child Categories -->
                    <?php
                    if ( ! empty( $children_map[$category->term_id] ) ) {
                        foreach ( $children_map[$category->term_id] as $child_cat ) {
                            $child_products_query = new WP_Query([
                                'post_type' => 'product',
                                'posts_per_page' => -1,
                                'orderby' => 'ID',
                                'order' => 'ASC',
                                'tax_query' => [[
                                    'taxonomy'         => 'product_cat',
                                    'field'            => 'term_id',
                                    'terms'            => $child_cat->term_id,
                                    'include_children' => false,
                                ]]
                            ]);
                            
                            if ( $child_products_query->have_posts() ) :
                                $child_thumb_id = get_term_meta( $child_cat->term_id, 'thumbnail_id', true );
                                $child_img_url  = wp_get_attachment_url( $child_thumb_id );
                            ?>
                                <div id="cat-<?php echo esc_attr($child_cat->slug); ?>" class="subcategory-section csp-cat-align-<?php echo esc_attr( $cat_text_align ); ?>" style="margin-top: 30px; width: 100%;">
                                    <div class="category-header subcategory-header csp-cat-header-col">
                                        <?php if ( $show_cat_image && ! empty( $child_img_url ) ) : ?>
                                            <div class="category-header-image-wrapper">
                                                <img src="<?php echo esc_url( $child_img_url ); ?>" alt="<?php echo esc_attr( $child_cat->name ); ?>" class="category-header-image">
                                            </div>
                                        <?php endif; ?>

                                        <div class="cat-info">
                                            <h3 class="subcategory-title">
                                                <?php echo esc_html($child_cat->name); ?>
                                            </h3>
                                            <?php if ( $show_cat_desc && ! empty( $child_cat->description ) ) : ?>
                                                <div class="category-description-content">
                                                    <?php echo wp_kses_post( $child_cat->description ); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="category-products">
                                        <?php
                                            while($child_products_query->have_posts()) : $child_products_query->the_post(); 
                                                $product = wc_get_product( get_the_ID() );
                                                if ( ! $product ) continue;
                                                $is_variable = $product->is_type('variable');
                                                $product_image_id = $product->get_image_id();
                                                $product_image_url = $product_image_id ? wp_get_attachment_image_src( $product_image_id, 'large' ) : false;
                                                $product_image_full = $product_image_id ? wp_get_attachment_image_src( $product_image_id, 'full' ) : false;
                                        ?>
                                                <div class="custom-product-item <?php echo $is_variable ? 'is-variable' : 'is-simple'; ?> <?php echo $product_image_url ? 'has-product-image' : 'no-product-image'; ?> csp-n14-card"
                                                    data-id="<?php echo absint( $product->get_id() ); ?>"
                                                    data-full-image="<?php echo esc_url( $product_image_full ? $product_image_full[0] : '' ); ?>"
                                                    <?php if ($is_variable): ?>
                                                        data-product_id="<?php echo absint( $product->get_id() ); ?>"
                                                        data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $product->get_available_variations() ) ); ?>"
                                                    <?php endif; ?>
                                                >
                                                    <?php if (!$is_variable): ?>
                                                    <form class="cart simple-product-form" action="<?php echo esc_url( $product->add_to_cart_url() ); ?>" method="post" enctype="multipart/form-data" style="margin:0;">
                                                    <?php else: ?>
                                                    <form class="variations_form cart" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post" enctype='multipart/form-data' style="margin:0;">
                                                    <?php endif; ?>

                                                        <!-- Card Layout Grid -->
                                                        <div class="product-top-row">
                                                            <!-- Left Info Section -->
                                                            <div class="product-info-left">
                                                                <h3 class="product-item-title"><?php the_title(); ?></h3>
                                                                
                                                                <div class="product-price-likes-row">
                                                                    <span class="csp-card-price">
                                                                        <?php 
                                                                        if ( $product->is_type( 'variable' ) ) {
                                                                            if ( 'range' === $price_display_mode ) {
                                                                                echo $product->get_price_html();
                                                                            } else {
                                                                                echo wc_price( $product->get_variation_price( 'min', true ) );
                                                                            }
                                                                        } else {
                                                                            echo $product->get_price_html();
                                                                        }
                                                                        ?>
                                                                    </span>
                                                                </div>

                                                                <div class="product-excerpt">
                                                                    <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                                                </div>
                                                            </div>

                                                            <!-- Right Column (Image/No image check) -->
                                                            <?php if ( $product_image_url ) : ?>
                                                                <div class="product-image-action-right">
                                                                    <div class="product-image-square-wrapper">
                                                                        <img src="<?php echo esc_url( $product_image_url[0] ); ?>" alt="<?php the_title_attribute(); ?>" class="product-item-image">
                                                                        <!-- Plus button floating at bottom-right corner of image -->
                                                                        <button type="button" class="csp-n14-plus-btn" aria-label="<?php esc_attr_e( 'Open details', '2-step-webshop' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg></button>
                                                                    </div>
                                                                </div>
                                                            <?php else : ?>
                                                                <!-- Action column for No-Image product cards -->
                                                                <div class="product-noimage-action-right">
                                                                    <button type="button" class="csp-n14-plus-btn no-img-plus" aria-label="<?php esc_attr_e( 'Open details', '2-step-webshop' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg></button>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Hidden inputs for WC state machine compatibility -->
                                                        <div class="csp-hidden-form-elements" style="display: none !important;">
                                                            <input type="number" name="quantity" class="qty" value="1" min="1" step="1">
                                                            <?php if(!$is_variable): ?>
                                                                <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>">
                                                                <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>">
                                                            <?php endif; ?>
                                                            <?php if ($is_variable): ?>
                                                                <div class="variations-container">
                                                                    <?php 
                                                                    $variations = $product->get_variation_attributes();
                                                                    foreach ( $variations as $attribute_name => $options ) : 
                                                                    ?>
                                                                        <div class="variation-select-wrapper">
                                                                            <label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label>
                                                                            <?php
                                                                            wc_dropdown_variation_attribute_options(
                                                                                array(
                                                                                    'options'   => $options,
                                                                                    'attribute' => $attribute_name,
                                                                                    'product'   => $product,
                                                                                    'class'     => 'variation-dropdown',
                                                                                )
                                                                            );
                                                                            ?>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                <div class="single_variation_wrap" style="display:none;"></div>
                                                            <?php endif; ?>
                                                            <button type="submit" class="single_add_to_cart_button button alt">+</button>
                                                        </div>

                                                    </form>
                                                </div>
                                        <?php 
                                            endwhile; 
                                            wp_reset_postdata();
                                        ?>
                                    </div>
                                </div>
                            <?php
                            endif;
                        }
                    }
                    ?>
                </section>
            <?php endforeach; ?>
        </main>
    </div>
</div>

<!-- ========================================== -->
<!-- 6. Startup Location & Time Picker Overlay Modal -->
<!-- ========================================== -->
<div class="csp-startup-modal-backdrop" id="csp-startup-modal" style="display: none;">
    
    <!-- Screen 1: Select Location -->
    <div class="csp-startup-modal-box" id="csp-screen-select-location">
        <h3 class="csp-startup-modal-title"><?php esc_html_e( 'Select location', '2-step-webshop' ); ?></h3>
        
        <!-- Toggle capsule -->
        <div class="csp-startup-method-switcher" style="<?php echo (! $is_pickup_enabled || ! $is_delivery_enabled) ? 'display: none !important;' : ''; ?>">
            <button type="button" class="csp-startup-method-btn <?php echo ($chosen_method === 'pickup') ? 'active' : ''; ?>" data-type="pickup" <?php echo (! $is_pickup_enabled) ? 'disabled data-disabled="yes"' : ''; ?>>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
                    <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                </svg>
                <span><?php esc_html_e( 'Pickup', '2-step-webshop' ); ?></span>
            </button>
            <button type="button" class="csp-startup-method-btn <?php echo ($chosen_method === 'delivery') ? 'active' : ''; ?>" data-type="delivery" <?php echo (! $is_delivery_enabled) ? 'disabled data-disabled="yes"' : ''; ?>>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                    <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732-1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                </svg>
                <span><?php esc_html_e( 'Delivery', '2-step-webshop' ); ?></span>
            </button>
        </div>

        <!-- Store detail card info -->
        <div class="csp-location-store-card" style="<?php echo ($chosen_method === 'delivery') ? 'display: none !important;' : ''; ?>">
            <div class="csp-store-radio-col">
                <input type="radio" checked disabled class="csp-store-radio">
                <span class="csp-custom-radio-indicator"></span>
            </div>
            <div class="csp-store-info-col">
                <span class="csp-store-name"><?php echo esc_html( $restaurant_name ); ?></span>
                <span class="csp-store-status open-today">
                    <?php 
                    if ( $is_currently_open ) {
                        echo esc_html__( 'Open Today', '2-step-webshop' );
                    } else {
                        $opening_time = get_option( 'pickup_opening_time', '11:30' );
                        printf( esc_html__( 'Opens Today %s', '2-step-webshop' ), esc_html( $opening_time ) );
                    }
                    ?>
                </span>
                <span class="csp-store-address-text"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16" style="vertical-align: text-bottom; margin-right: 4px;"><path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"/><path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4"/></svg> <?php echo esc_html( $pickup_address ); ?></span>
                <a href="#" class="csp-see-hours-link csp-info-trigger"><?php esc_html_e( 'See hours', '2-step-webshop' ); ?></a>
            </div>
        </div>

        <!-- Delivery location search container -->
        <div class="csp-delivery-location-container" style="<?php echo ($chosen_method === 'pickup') ? 'display: none !important;' : ''; ?>">
            <div class="csp-delivery-search-wrapper">
                <input type="text" id="csp-delivery-address-input" class="csp-delivery-address-input" placeholder="<?php esc_attr_e( 'Enter your delivery address', '2-step-webshop' ); ?>">
                <button type="button" class="csp-delivery-search-btn" id="csp-delivery-search-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </button>
            </div>
            
            <a href="#" class="csp-use-device-location-link" id="csp-use-device-location-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-compass-fill" viewBox="0 0 16 16" style="vertical-align: text-bottom; margin-right: 4px;">
                    <path d="M15.5 8.516a7.5 7.5 0 1 1-9.462-7.24A1 1 0 0 1 7 2v2a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5V2a1 1 0 0 1 .962-.724 7.5 7.5 0 0 1 5.538 7.24zM8 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8zm0 1a3 3 0 1 1 0 6 3 3 0 0 1 0-6zm0 1a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                </svg>
                <span><?php esc_html_e( 'Use my current position', '2-step-webshop' ); ?></span>
            </a>

            <div id="csp-delivery-location-status" class="csp-delivery-location-status" style="display: none;"></div>
        </div>

        <!-- Schedule box block -->
        <div class="csp-location-schedule-box">
            <div class="csp-schedule-info-left">
                <!-- Checked circle icon appears when time chosen -->
                <span class="csp-schedule-check-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg></span>
                <span class="csp-schedule-text-label"><?php esc_html_e( 'Schedule for later', '2-step-webshop' ); ?></span>
            </div>
            <a href="#" class="csp-schedule-change-link" id="csp-schedule-trigger-btn"><?php esc_html_e( 'Change', '2-step-webshop' ); ?></a>
        </div>

        <!-- View Menu primary action button -->
        <button type="button" class="csp-view-menu-btn" id="csp-startup-confirm-btn" disabled>
            <?php esc_html_e( 'View Menu', '2-step-webshop' ); ?>
        </button>
    </div>

    <!-- Screen 2: Scheduled Order Screen -->
    <div class="csp-startup-modal-box" id="csp-screen-scheduled-order" style="display: none;">
        <div class="csp-scheduled-header">
            <button type="button" class="csp-scheduled-back-btn" id="csp-scheduled-back-btn" aria-label="<?php esc_attr_e( 'Back', '2-step-webshop' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                </svg>
            </button>
            <h3 class="csp-startup-modal-title"><?php esc_html_e( 'Scheduled Order', '2-step-webshop' ); ?></h3>
        </div>

        <!-- Date selector accordion button elements -->
        <div class="csp-startup-date-picker-wrapper">
            <input type="hidden" id="csp-startup-date-input" value="">
            
            <?php 
            $dates = TSW_Pickup_Scheduler::get_available_order_dates_structured();
            $keys = array_keys( $dates );
            
            $first_keys = array_slice( $keys, 0, 2 );
            $remaining_keys = array_slice( $keys, 2 );
            ?>

            <!-- First 2 choices visible -->
            <div class="csp-date-buttons-row">
                <?php foreach ( $first_keys as $date_key ) : ?>
                    <button type="button" class="csp-date-btn" data-date="<?php echo esc_attr( $date_key ); ?>">
                        <span class="csp-date-btn-day"><?php echo esc_html( $dates[$date_key]['day_label'] ); ?></span>
                        <span class="csp-date-btn-date"><?php echo esc_html( $dates[$date_key]['date_label'] ); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Show more expandable grid -->
            <?php if ( ! empty( $remaining_keys ) ) : ?>
                <div class="csp-date-accordion-panel" id="csp-date-accordion-panel" style="display: none;">
                    <div class="csp-date-buttons-grid">
                        <?php foreach ( $remaining_keys as $date_key ) : ?>
                            <button type="button" class="csp-date-btn" data-date="<?php echo esc_attr( $date_key ); ?>">
                                <span class="csp-date-btn-day"><?php echo esc_html( $dates[$date_key]['day_label'] ); ?></span>
                                <span class="csp-date-btn-date"><?php echo esc_html( $dates[$date_key]['date_label'] ); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="button" class="csp-date-accordion-toggle" id="csp-date-accordion-toggle">
                    <span><?php esc_html_e( 'Show more', '2-step-webshop' ); ?></span>
                    <span class="csp-arrow-icon" style="display: inline-block; transition: transform 0.2s ease;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16" style="vertical-align: middle;">
                            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/>
                        </svg>
                    </span>
                </button>
            <?php endif; ?>
        </div>

        <!-- Timeslots list radio selector rows -->
        <div class="csp-startup-time-picker-wrapper">
            <ul class="csp-scheduled-timeslots-list">
                <?php 
                $slots = TSW_Pickup_Scheduler::get_pickup_time_choices();
                foreach ( $slots as $val => $label ) {
                    if ( $val === '' ) continue;
                    $disabled = TSW_Pickup_Scheduler::is_pickup_time_passed( $val );
                    ?>
                    <li class="csp-timeslot-row-item <?php echo $disabled ? 'disabled' : ''; ?>" data-time="<?php echo esc_attr($val); ?>">
                        <span class="csp-timeslot-time-val"><?php echo esc_html($label); ?></span>
                        <input type="radio" name="csp_timeslot_radio" value="<?php echo esc_attr($val); ?>" <?php echo $disabled ? 'disabled' : ''; ?>>
                        <span class="csp-custom-radio-indicator"></span>
                    </li>
                <?php
                }
                ?>
            </ul>
        </div>

        <!-- Schedule Order confirm action button -->
        <button type="button" class="csp-schedule-order-submit-btn" id="csp-schedule-order-submit-btn" disabled>
            <?php esc_html_e( 'Schedule Order', '2-step-webshop' ); ?> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16" style="vertical-align: middle; margin-left: 4px;"><path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/></svg>
        </button>
    </div>
</div>

<!-- ========================================== -->
<!-- 7. Store Info opening hours overlay modal -->
<!-- ========================================== -->
<div class="csp-modal-overlay" id="csp-info-modal" style="display: none;">
    <div class="csp-modal-container info-modal-container">
        <button type="button" class="csp-modal-close csp-info-close-btn" aria-label="<?php esc_attr_e( 'Close', '2-step-webshop' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
        <div class="csp-modal-body">
            <h2 class="csp-info-modal-title"><?php esc_html_e( 'Info', '2-step-webshop' ); ?></h2>
            
            <!-- Map iframe -->
            <div class="csp-info-map-wrapper">
                <?php
                $custom_map_url = get_option( 'tsw_google_maps_url', '' );
                if ( empty( $custom_map_url ) ) {
                    $custom_map_url = 'https://maps.google.com/maps?q=' . rawurlencode( $pickup_address ) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
                }
                ?>
                <iframe src="<?php echo esc_url( $custom_map_url ); ?>" width="100%" height="240" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy"></iframe>
            </div>

            <!-- Opening hours online ordering -->
            <div class="csp-info-hours-section">
                <h3><?php esc_html_e( 'Opening hours: Online ordering', '2-step-webshop' ); ?></h3>
                <ul class="csp-info-hours-list">
                    <?php
                    $day_keys_map = array(
                        'Monday'    => 'monday',
                        'Tuesday'   => 'tuesday',
                        'Wednesday' => 'wednesday',
                        'Thursday'  => 'thursday',
                        'Friday'    => 'friday',
                        'Saturday'  => 'saturday',
                        'Sunday'    => 'sunday',
                    );
                    $weekdays = array();
                    foreach ( $day_keys_map as $label => $key ) {
                        $is_open = get_option( 'pickup_open_' . $key, 'yes' ) === 'yes';
                        if ( $is_open ) {
                            $day_hours = tsw_get_day_opening_hours( $key );
                            $weekdays[ $label ] = $day_hours['open'] . ' - ' . $day_hours['close'];
                        } else {
                            $weekdays[ $label ] = __( 'Closed', '2-step-webshop' );
                        }
                    }
                    foreach ( $weekdays as $day => $hours ) :
                    ?>
                        <li>
                            <span class="day-label"><?php echo esc_html( __( $day, '2-step-webshop' ) ); ?></span>
                            <span class="hours-val"><?php echo esc_html( $hours ); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Delivery cost details -->
            <?php if ( $is_delivery_enabled ) : ?>
            <div class="csp-info-delivery-section">
                <h3><?php esc_html_e( 'Delivery costs', '2-step-webshop' ); ?></h3>
                <?php
                $delivery_zone_label = get_option( 'tsw_delivery_zone_label', '' );
                if ( empty( $delivery_zone_label ) ) {
                    $delivery_zone_label = __( 'Delivery zone 1', '2-step-webshop' );
                }
                $delivery_zone_desc = get_option( 'tsw_delivery_zone_desc', '' );
                if ( empty( $delivery_zone_desc ) ) {
                    $delivery_zone_desc = __( 'Min. amount - 300,00 €, Fee - 0,00 €', '2-step-webshop' );
                }
                ?>
                <div class="csp-delivery-zone-row">
                    <span class="zone-label"><?php echo esc_html( $delivery_zone_label ); ?> <span class="zone-dot-indicator"></span></span>
                    <span class="zone-desc"><?php echo esc_html( $delivery_zone_desc ); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 8. Product detailed Info Popup Modal -->
<!-- ========================================== -->
<div id="csp-product-modal" class="csp-modal-overlay">
    <div class="csp-modal-container">
        <button type="button" class="csp-modal-close" aria-label="<?php esc_attr_e( 'Close', '2-step-webshop' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
        <div class="csp-modal-body">
            <div class="csp-modal-img-wrapper">
                <img id="csp-modal-img" src="" alt="">
            </div>
            <div class="csp-modal-info">
                <h2 id="csp-modal-title"></h2>
                
                <div class="csp-price-likes-modal-row">
                    <div id="csp-modal-price" class="csp-modal-price-val"></div>
                </div>

                <div id="csp-modal-desc" class="csp-modal-desc-val"></div>
                
                <!-- Product Info accordion -->
                <div class="csp-product-info-accordion">
                    <div class="csp-accordion-header" id="csp-info-accordion-trigger" style="display: flex; align-items: center;">
                        <span style="display: inline-flex; align-items: center; vertical-align: middle; margin-right: 6px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg></span>
                        <span><?php esc_html_e( 'Product Info', '2-step-webshop' ); ?></span>
                        <span class="arrow-icon" style="display: inline-flex; align-items: center; transition: transform 0.2s ease; margin-left: auto;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/>
                            </svg>
                        </span>
                    </div>
                    <div class="csp-accordion-content" id="csp-info-accordion-content" style="display: none;">
                        <p><?php esc_html_e( 'Ingredients, additives and allergens can be queried by requesting our service staff.', '2-step-webshop' ); ?></p>
                    </div>
                </div>

                <!-- Dynamic attributes will be loaded here -->
                <div id="csp-modal-options-container"></div>

                <!-- Special notes section -->
                <div class="csp-modal-special-notes">
                    <label for="csp-modal-notes-input"><strong><?php esc_html_e( 'Special notes:', '2-step-webshop' ); ?></strong></label>
                    <p class="csp-modal-notes-help"><?php esc_html_e( "We'll try our best to accommodate requests, but can't make changes that affect pricing.", '2-step-webshop' ); ?></p>
                    <textarea id="csp-modal-notes-input" placeholder="<?php esc_attr_e( 'Add special request', '2-step-webshop' ); ?>" name="special_request_note"></textarea>
                </div>
            </div>
        </div>
        
        <!-- Modal inline error message -->
        <div id="csp-modal-error-msg" class="csp-modal-error-msg" style="display:none;"></div>

        <!-- Footer actions -->
        <div class="csp-modal-footer">
            <div class="csp-modal-qty-container">
                <button type="button" class="csp-modal-qty-btn minus"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16" style="vertical-align: middle;"><path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"/></svg></button>
                <input type="number" id="csp-modal-qty-input" class="qty" value="1" min="1">
                <button type="button" class="csp-modal-qty-btn plus"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16" style="vertical-align: middle;"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg></button>
            </div>
            <button type="button" id="csp-modal-add-btn" class="csp-modal-add-btn-action">
                <span class="btn-text"><?php esc_html_e( 'Add To Cart', '2-step-webshop' ); ?></span>
                <span id="csp-modal-add-price"></span>
            </button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 9. Slide-Out Cart Drawer -->
<!-- ========================================== -->
<div id="csp-cart-drawer" class="csp-cart-drawer-overlay">
    <div class="csp-cart-drawer-container">
        <!-- Header -->
        <div class="csp-drawer-header">
            <h2><?php esc_html_e( 'Cart', '2-step-webshop' ); ?></h2>
            <button type="button" class="csp-drawer-close" aria-label="<?php esc_attr_e( 'Close', '2-step-webshop' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg></button>
        </div>
        
        <!-- Body -->
        <div class="csp-drawer-body">
            




            <!-- Rewards progress card removed (no points system) -->

            <!-- Cart Items List (State-specific dynamic container) -->
            <div class="csp-drawer-items-list-container">
                <?php if ( function_exists('WC') && WC()->cart && ! WC()->cart->is_empty() ) : ?>
                    
                    <!-- State B: Cart NOT Empty -->
                    <div class="csp-drawer-items-list">
                        <?php 
                        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
                            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                            
                            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) :
                                $product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                                $product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                                $options_output = tsw_get_formatted_variation_options( $cart_item );
                                ?>
                                <div class="csp-drawer-item-card" data-cart_item_key="<?php echo esc_attr( $cart_item_key ); ?>">
                                    <div class="csp-item-qty-adjuster">
                                        <button type="button" class="csp-item-qty-btn minus" data-key="<?php echo esc_attr( $cart_item_key ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-dash-lg" viewBox="0 0 16 16" style="vertical-align: middle;"><path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"/></svg></button>
                                        <span class="csp-item-qty-val"><?php echo intval( $cart_item['quantity'] ); ?></span>
                                        <button type="button" class="csp-item-qty-btn plus" data-key="<?php echo esc_attr( $cart_item_key ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16" style="vertical-align: middle;"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg></button>
                                    </div>
                                    <div class="csp-item-details">
                                        <h4 class="csp-item-name"><?php echo wp_kses_post( $product_name ); ?></h4>
                                        <?php if ( ! empty($options_output) ) : ?>
                                            <span class="csp-item-options"><?php echo esc_html( $options_output ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="csp-item-price-col">
                                        <span class="csp-item-price"><?php echo wp_kses_post( $product_price ); ?></span>
                                    </div>
                                </div>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </div>



                <?php else : ?>
                    
                    <!-- State A: Cart EMPTY -->
                    <div class="csp-empty-cart-message">
                        <div class="csp-empty-cart-graphic">
                            <span style="display: inline-flex; align-items: center; justify-content: center;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-basket" viewBox="0 0 16 16"><path d="M5.071 1.243a.5.5 0 0 1 .858.514L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15.5a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 6h1.717L5.07 1.243zM3.5 10.5a.5.5 0 1 0-1 0v3a.5.5 0 1 0 1 0zm2.5 0a.5.5 0 1 0-1 0v3a.5.5 0 1 0 1 0zm2.5 0a.5.5 0 1 0-1 0v3a.5.5 0 1 0 1 0zm2.5 0a.5.5 0 1 0-1 0v3a.5.5 0 1 0 1 0zm2.5 0a.5.5 0 1 0-1 0v3a.5.5 0 1 0 1 0z"/></svg></span>
                        </div>
                        <h3><?php esc_html_e( 'Your basket is empty', '2-step-webshop' ); ?></h3>
                        <p><?php esc_html_e( 'Add items to get started', '2-step-webshop' ); ?></p>
                    </div>

                <?php endif; ?>
            </div>
            
        </div>
        
        <!-- Drawer Footer Checkout -->
        <div class="csp-drawer-footer">
            <?php if ( function_exists('WC') && WC()->cart && ! WC()->cart->is_empty() ) : ?>
                <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="csp-drawer-checkout-btn">
                    <span><?php printf( esc_html__( 'Go to checkout · %s', '2-step-webshop' ), WC()->cart->get_cart_total() ); ?></span>
                </a>

            <?php else : ?>
                <button type="button" class="csp-drawer-checkout-btn disabled" disabled>
                    <span><?php esc_html_e( 'Go to checkout', '2-step-webshop' ); ?></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Floating Cart Button -->
<?php
$floating_cart_pos = get_option( 'pickup_floating_cart_position', 'bottom-right' );
$cart_count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>
<button type="button" class="csp-floating-cart-btn csp-pos-<?php echo esc_attr( $floating_cart_pos ); ?>" id="csp-floating-cart-trigger" aria-label="<?php esc_attr_e( 'View Cart', '2-step-webshop' ); ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-cart" viewBox="0 0 16 16">
        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
    </svg>
    <span class="csp-floating-cart-count csp-n14-header-cart-count"><?php echo esc_html( $cart_count ); ?></span>
</button>
