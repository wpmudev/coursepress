<?php
/**
 * @var int $product_id
 * @var string $wc_button
 * @var string $wc_cart_url
 */
?>
<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>
<form class="cart" method="post" enctype='multipart/form-data' action="<?php echo esc_url( $wc_cart_url ); ?>">
    <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>" />
    <button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $wc_button ); ?></button>
    <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
</form>
<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
