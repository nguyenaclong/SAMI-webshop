<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TSW_Woo_Fragments {

    public function __construct() {
        add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'floating_cart_fragments' ) );
    }

    public function floating_cart_fragments( $fragments ) {
        ob_start();
        echo '<span class="cart-contents-count">' . WC()->cart->get_cart_contents_count() . '</span>';
        $fragments['span.cart-contents-count'] = ob_get_clean();

        ob_start();
        echo '<span class="cart-contents-total">' . WC()->cart->get_cart_total() . '</span>';
        $fragments['span.cart-contents-total'] = ob_get_clean();

        // Slide-out Cart Drawer Items List Fragment
        ob_start();
        ?>
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
        <?php
        $fragments['div.csp-drawer-items-list-container'] = ob_get_clean();

        // Slide-out Cart Drawer Footer Fragment
        ob_start();
        ?>
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
        <?php
        $fragments['div.csp-drawer-footer'] = ob_get_clean();

        return $fragments;
    }
}
