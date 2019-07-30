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
	});

	// adiciona mascaras nos campos
	$('.vr-wc-cpf').mask('000.000.000-00', {reverse: true});
	$('.vr-wc-exp').mask('00/00', {reverse: true});
	$('.vr-wc-cc-num').mask('0000 0000 0000 0000', {reverse: true});

});
