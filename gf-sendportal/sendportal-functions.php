<?php

GFForms::include_addon_framework();

class GFSendportalAddOn extends GFAddOn {
	protected $_version = GF_Sendportal_ADDON_VERSION;
	protected $_min_gravityforms_version = '2.5';
	protected $_slug = 'gf-sendportal';
	protected $_path = 'sendportal-add-on-gf/sendportal-add-on-gf.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Add on for SendPortal on Gravity Forms';
	protected $_short_title = 'SendPortal Add On';
	private static $_instance = null;

	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFSendportalAddOn();
		}
		return self::$_instance;
	}

	public function init() {
		parent::init();
		add_action( 'gform_after_submission', [$this, 'after_submission'], 10, 2 );
	}
	
	public function form_settings_fields( $form ) {
		$email_fields = [];
		$name_fields  = [['label'=>'select field','value'=>'']];
		$list_fields  = [['label'=>'select list','value'=>'']];

		foreach ( $form['fields'] as &$field ) {
            if ($field->type == 'name') {
                $name = $field['inputs'];
                foreach ($name as $key => $input) {
                    if ($input['label'] == 'Prefix' || $input['label'] == 'Suffix') continue;
                    $name_fields[] = ['label' => $field->label . ' (' . $input['label'] . ')', 'value' => $input['id']];
                }
            } elseif ($field->type == 'email') {
                $email_fields[] = ['label' => $field->label, 'value' => $field->id];
            } elseif ($field->type == 'text') {
                $name_fields[] = ['label' => $field->label, 'value' => $field->id];
            }
        }

       if( !empty($form['gf-sendportal']['key'])){
		$str = $form['gf-sendportal']['url'];
		$delimiter = "/";

		if (substr($str, -1) !== $delimiter) {
			$str .= $delimiter;
		}

		$url = $str.'tags';
			$headers = array(
				'Authorization' =>'Bearer '.$form['gf-sendportal']['key'],
				'Accept' => 'application/json'
			);
		
			$args = array(
				'headers' => $headers
			);
			$response = wp_remote_get($url, $args);
if (is_wp_error($response)) {
 
    $error_message = $response->get_error_message();
	GFCommon::add_error_message("Error connecting: " . $error_message);
    
} else {
    $response_code = wp_remote_retrieve_response_code($response); 
    $response_body = wp_remote_retrieve_body($response); 


    if ($response_code == 200) {
      
		$resbody = json_decode($response['body'],true);

		$list_field = [];
		if( !empty($resbody) ){
			foreach( $resbody['data'] as $d ){ 
				$list_fields[] = ['label'=>$d['name'],'value'=>$d['id']];
			}
		}
		GFCommon::add_message ("Api successfully connected.");
    } else {
       
		GFCommon::add_error_message("Error in request. Error code: " . $response_code);
    }
}

        

}
        $list_field = [
            'label'   => esc_html__( 'Contact List', 'gf-sendportal' ),
            'type'    => 'select',
            'name'    => 'list',
            'choices' => $list_fields,
            'tooltip' => esc_html__( 'Contact List Only Visible After Add Correct SendPortal API Key' )
        ];

		return array(
			array(
				'title'  => esc_html__('Send Portal Setting', 'gf-sendportal' ),
				'fields' => array(
					[
                        'label'   => esc_html__( 'Key', 'gf-sendportal' ),
                        'type'    => 'text',
                        'placeholder' => 'Enter API Key',
                        'name'    => 'key',
                        'required'=>'required',
                        'tooltip' => esc_html__( 'SendPortal API Key', 'gf-sendportal' ),
                    ],
					[
                        'label'   => esc_html__( 'url', 'gf-sendportal' ),
                        'type'    => 'text',
                        'placeholder' => 'Enter API Url',
                        'name'    => 'url',
                        'required'=>'required',
                        'tooltip' => esc_html__( 'Enter Api Url', 'gf-sendportal' ),
                    ],
					[
                        'label'   => esc_html__( 'Email', 'gf-sendportal' ),
                        'type'    => 'select',
                        'placeholder' => 'Enter Your Email',
                        'name'    => 'email',
                        'required'=>'required',
                        'choices' => $email_fields,
                        'tooltip' => esc_html__( 'Filed Type Email Only' )
                    ],
					[
                        'label'   => esc_html__( 'First Name', 'gf-sendportal' ),
                        'type'    => 'select',
                        'name'    => 'fnm',
                        'choices' => $name_fields
                    ],
					[
                        'label'   => esc_html__( 'Last Name', 'gf-sendportal' ),
                        'type'    => 'select',
                        'name'    => 'lnm',
                        'choices' => $name_fields
                    ],
					$list_field
				),
			),
		);
	}

	public function after_submission( $entry, $form ) {
		$first_name='';
		$last_name='';

		if( !empty($form['gf-sendportal']['fnm']) ){
			$first_name=$data['first_name'] = $entry[$form['gf-sendportal']['fnm']];
		}
		if( !empty($form['gf-sendportal']['lnm']) ){
			$last_name=$data['last_name'] = $entry[$form['gf-sendportal']['lnm']];
		}
	
		if( !empty($form['gf-sendportal']['list']) ){
			$sendportal_list_id = [intval($form['gf-sendportal']['list'])];
		}
		if( !empty($form['gf-sendportal']['key'])){
			$str = $form['gf-sendportal']['url'];
			$delimiter = "/";
			if (substr($str, -1) !== $delimiter) {
				$str .= $delimiter;
				$url = $str.'subscribers';
			}
		}
        /** Create Subscribers **/
		$headers = array(
			'Authorization' =>'Bearer '.$form['gf-sendportal']['key'],
			'Accept' => 'application/json',
			'Content-Type' => 'application/json'
		);

	    $data = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'email' => $entry[$form['gf-sendportal']['email']],
			'tags'=> $sendportal_list_id 
		);
		$args = array(
			'headers' => $headers,
			'body'    => json_encode($data)
		);

	
		$response = wp_remote_post( $url,$args);

	}
}

