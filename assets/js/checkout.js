/**
 *
 * Codigos JS para a página de checkout do WooCommerce
 *
*/
jQuery(document).ready(function($) {
	/**
	 *
	 * Bloquea letras nos campos de numeros no formulário
	 *
	*/
	$( document ).on( 'keyup', '.vr-wc-num', function( e ){
		$( this ).val( $( this ).val().replace(/[^0-9\.]/g,'') );
	})
});
