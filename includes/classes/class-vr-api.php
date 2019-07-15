<?
/**
 * Classe para conectar via HTTP na REST API da VR
 */
class VR_WP_API_HTTP{

	/**
	 * controla o tipo do ambiente a ser acessado
	 * @var string
	 * @link https://dev.vr.com.br/api-portal/content/apis/api-adquirencia/captura
	 */
	private $api_type = 'producao';

	/**
	 * @var string
	 */
	private $producao_url = 'https://api.vr.com.br/captura/v1';

	/**
	 * @var string
	 */
	private $homologacao_url = 'https://api-hmp.vr.com.br/captura/v1';

	/**
	 * @var string
	 */
	private $dev_url = 'https://api-devportal.vr.com.br/captura/v1';

	/**
	 * Essa string receberá o URL de acordo com o ambiente setado
	 * @var string
	 */
	private $api_url = '';

	/**
	 * @link https://dev.vr.com.br/api-portal/node/4
	 * @var string
	 */
	private $client_id = '';

	/**
	 * @link https://dev.vr.com.br/api-portal/node/4
	 * @var string
	 */
	private $secret = '';

	private $access_token = '';
	/**
	 * URL da api de autenticação. É o mesmo para todos ambientes.
	 * @link https://dev.vr.com.br/api-portal/node/4
	 * @var string
	 */
	private $authenticate_url = 'https://api.vr.com.br';

	/**
	 * Endpoint de autenticação
	 * @var string
	 * @link https://dev.vr.com.br/api-portal/node/4
	 */
	private $authenticate_endpoint = '/oauth/grant-code/';

	/**
	 * Endpoint parar gerar o access token
	 * @var string
	 * @link https://dev.vr.com.br/api-portal/node/4
	 */
	private $authenticate_endpoint_access_token = '/oauth/access-token';

	/**
	 * @var string
	 */
	private $grant_code = '';

	/**
	 * Variavel para receber objeto WP_Error, caso haja.
	 * @var boolean|object
	 */
	private $error = false;

	/**
	 * Valor da transação
	 * @var int
	 */
	private $transaction_value = 0;

	/**
	 * @var array
	 */
	private $transaction_data = array();
	/**
	 * Função obrigatória em classes no PHP 5.7
	 */
	public function __construct() {

	}

	/**
	 * Seta o tipo de ambiente
	 * @param string $type
	 */
	public function set_api_type( $type = 'producao' ) {
		if ( 'producao' === $type ) {
			$this->api_type = 'producao';
			$this->api_url = $this->producao_url;
		} elseif ( 'homologacao' === $type ) {
			$this->api_type = 'homologacao';
			$this->api_url = $this->homologacao_url;
		} elseif ( 'dev' === $type ) {
			$this->api_type = 'dev';
			$this->api_url = $this->dev_url;
		} else {
			$error = new WP_Error( 'vr_wp_api_http_no_api_type', __( 'No API Type is set in class-vr-api.php', 'vr_woocommerce' ) );
			wp_die( $error );
		}
	}
	/**
	 * Faz a autenticação na API
	 * @return boolean
	 */
	public function http_authenticate() {
		$body = array(
			'client_id' => $this->client_id,
			'redirect_uri' => home_url()
		);
		$args = array(
			'body' => json_encode( $body ),
			'headers' => array(
				'Content-Type' => 'application/json'
			)
		);
		//var_dump( $args );
		$request_grant_code = wp_remote_post( $this->authenticate_url . $this->authenticate_endpoint, $args );
		if ( ! $request_grant_code || is_wp_error( $request_grant_code ) ) {
			return false;
		}
		$grant_code = json_decode( $request_grant_code[ 'body'] );
		$grant_code = parse_url( $grant_code->redirect_uri );
		$grant_code = parse_str( $grant_code[ 'query'], $url_queries );
		$grant_code = $url_queries[ 'code' ];

		$body = array(
			'grant_type'	=> 'authorization_code',
			'code'			=> $grant_code
		);
		$args = array(
			'body' => json_encode( $body ),
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' .$this->secret )
			)
		);
		//var_dump( $args );
		$request_access_token = wp_remote_post( $this->authenticate_url . $this->authenticate_endpoint_access_token, $args );
		if ( ! $request_access_token || is_wp_error( $request_access_token ) ) {
			$this->error = $request_access_token;
			return false;
		}
		var_dump( $request_access_token[ 'body'] );
		$response_json = json_decode( $request_access_token[ 'body'], true );
		$this->access_token = $response_json[ 'access_token' ];
		//var_dump( $response_json );
		return true;
	}
	public function set_transaction_data( $data = array() ) {
		if ( ! isset( $data[ 'value' ] ) || ! is_numeric( $data[ 'value' ] ) ) {
			//colocar wp error aqui
			return false;
		}
		if ( ! isset( $data[ 'id_filiacao'] ) && ! is_numeric( $data[ 'id_filiacao'] ) ) {
			//colocar wp error aqui
			return false;
		}
		if ( ! isset( $data[ 'cartao_voucher' ] ) || ! is_array( $data[ 'cartao_voucher' ] ) ) {
			//colocar wp error aqui
			return false;
		}

		if ( ! isset( $data[ 'name'] ) && ! is_empty( $data['name'] ) ) {
			//colocar wp error aqui
			return false;
		}

		$this->transaction_data = array(
			'valor' 			=> absint( $data[ 'value'] );
			'id_filiacao' 		=> $data[ 'id_filiacao' ];
			'cartao_voucher'	=> array(
				'nome'				=> $data['name'],
				'numero_cartao'		=> $data[ 'card_num' ]
			)
		);

	}
	public function make_transaction() {
	}
}
