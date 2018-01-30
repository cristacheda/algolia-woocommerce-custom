<?php
/**
 * Plugin Name: Algolia WooCommerce Extension
 * Author: Dragoș Cristache
 * Author URI: https://www.webventures.ro/
 * Version: 1.0.1
 * Description: Add WooCommerce product info (display price, price, regular price, average_rating, rating_count, review_count, sku, total_sales) to the Algolia index.
 * GitHub Plugin URI: https://github.com/cristacheda/algolia-woocommerce-custom
 */

function aw_product_shared_attributes( array $shared_attributes, WP_Post $post ) {
  $product = wc_get_product( $post );

  $shared_attributes['display_price'] = (float) $product->get_display_price();
  $shared_attributes['price'] = (float) $product->get_price();
  $shared_attributes['regular_price'] = (float) $product->get_regular_price();
  $shared_attributes['average_rating'] = (float) $product->get_average_rating();
  $shared_attributes['rating_count'] = (int) $product->get_rating_count();
  $shared_attributes['review_count'] = (int) $product->get_review_count();
  $shared_attributes['sku'] = $product->get_sku();
  $shared_attributes['total_sales'] = (int) get_post_meta( $post->ID, 'total_sales', true );
  $attributes['is_featured'] = $product->is_featured() ? 1 : 0;

  return $shared_attributes;
}

add_filter( 'algolia_post_product_shared_attributes', 'aw_product_shared_attributes', 10, 2 );
add_filter( 'algolia_searchable_post_product_shared_attributes', 'aw_product_shared_attributes', 10, 2 );

function aw_should_index_post( $should_index, WP_Post $post ) {
  // Only alter decision making if we are dealing with a product.
  if ( 'product' !== $post->post_type ) {
    return $should_index;
  }

  // This is required as is_visible method also checks for user_cap.
  if( 'publish' !== $post->post_status ) {
    return false;
  }

  $product = wc_get_product( $post );
  // We extracted this check because is_visible will not detect searchable products if not in a loop.
  if ( 'search' === $product->visibility ) {
    return true;
  }

  return $product->is_visible();
}

add_filter( 'algolia_should_index_post', 'aw_should_index_post', 10, 2 );
add_filter( 'algolia_should_index_searchable_post', 'aw_should_index_post', 10, 2 );
