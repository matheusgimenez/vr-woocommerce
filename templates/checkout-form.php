<?php
/**
 * Formulário de checkout
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<fieldset id="vr-payment-form" class="<?php echo 'storefront' === basename( get_template_directory() ) ? 'woocommerce-pagseguro-form-storefront' : ''; ?>" data-cart_total="<?php echo esc_attr( number_format( $cart_total, 2, '.', '' ) ); ?>" style="width:100%;">
	<div style="width:100;">
		<label for="vr-card-name">
			<strong><?php _e( "VR Card owner's name", 'vr-woocommerce' );?></strong>
		</label>
		<input type="text" id="vr-card-name" name="vr-card-name" style="width:100%;" />
		<label for="vr-card-name">
			<strong><?php _e( "VR Card owner's Document", 'vr-woocommerce' );?></strong>
		</label>
		<input type="text" id="vr-card-cpf" name="vr-card-cpf" style="width:100%;" />

		<label for="vr-card-num">
			<strong><?php _e( "Number", 'vr-woocommerce' );?></strong>
		</label>
		<input type="text" id="vr-card-num" name="vr-card-num" style="width:100%;" pattern="[0-9]+$" />
		<label for="vr-card-exp-date" style="width: 100%;clear:both;display:block;">
			<strong><?php _e( "Expiration date", 'vr-woocommerce' );?></strong>
		</label>
		<input type="text" id="vr-card-exp-date" name="vr-card-exp-date" style="width:20%;" />
		<label for="vr-card-security-code" style="width: 100%;clear:both;display:block;">
			<strong><?php _e( "Security Code", 'vr-woocommerce' );?></strong>
			<br>
			<small>
				<?php _e( 'The three numbers behind the card', 'vr-woocommerce' );?>
			</small>
		</label>
		<input type="text" id="vr-card-security-code" name="vr-card-security-code" style="width:20%;" />

	</div>
</fieldset>
