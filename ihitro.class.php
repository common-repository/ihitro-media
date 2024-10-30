<?php

class ihitro {
	
	public $user_id;
	public $ihitro_config;
	public $ihitro_config_users;
	public $footer_text;
	
	public function __construct(){
		$this->footer_text = 'ihitro:media - &copy; ' .  date("Y");
		add_action( 'plugins_loaded'	, array( $this, 'check_if_user_logged_in' ) );
		add_action( 'init'				, array( $this, 'create_acceslog' ) );
		$this->ihitro_config			= get_option('ihitro_config');
		$this->ihitro_config_users	= get_option('ihitro_config_users');
		$this->favicon();
		$this->login_css();
		$this->admin_css();
		$this->footer_text();
		$this->footer_versie();
		$this->default_tweaks();
	}
	
	public function check_if_user_logged_in(){
		if ( is_user_logged_in() ){
			  $current_user = wp_get_current_user(); 
			
			  if ( !($current_user instanceof WP_User) ) 
				return; 
			
			  $this->user_id = $current_user->ID;
			  

			  
			  if(is_array($this->ihitro_config_users) == false || in_array($this->user_id, $this->ihitro_config_users)){
			 	$this->config();
			  }
			  //echo $current_user->ID; 
		}
	}
	
	/**************************************\
	*
	*
	*	Visuele opties
	*
	*
	\***************************************/
	
	public function favicon(){
		if($this->ihitro_config['favicon']){
			add_action('wp_head', 'ihitro_favicon');
			function ihitro_favicon(){
				$icon_url = plugins_url('images/favicon.ico', __FILE__);
?>	
					<link rel="shortcut icon" href="<?php echo $icon_url; ?>" />
<?php
			}
		}
	}
	
	public function login_css(){
		// Aangepaste Login css
		if($this->ihitro_config['login_css']){
			add_action('login_head', 'ihitro_login_css');
			function ihitro_login_css() {		wp_enqueue_style( 'login_css', plugin_dir_url( __FILE__ ). 'css/login.css' ); }
		}
	}
	
	public function admin_css(){
		// Custom admin css 
		if($this->ihitro_config['admin_css']){
			add_action('admin_print_styles', 'ihitro_admin_css' );
			function ihitro_admin_css() {		wp_enqueue_style( 'admin_css', plugin_dir_url( __FILE__ ) . 'css/dashboard.css' ); }
		}
	}
	
	public function footer_text(){
		// Wijzig footer informatie
		if($this->ihitro_config['footer_text']){
			add_filter( 'admin_footer_text', 'ihitro_footer_tekst' );
			function ihitro_footer_tekst(){ 
				global $ihitro_config;
				if(strlen($ihitro_config['footer_text_alt']) > 0){
					return $ihitro_config['footer_text_alt']; 
				} else {
					return $this->footer_text;
				}
			}
		}
	}
	
	public function footer_versie(){
		// Verwijder versie informatie uit footer
		if($this->ihitro_config['footer_versie']){
			if(!is_admin()){
				add_filter( 'update_footer', 'ihitro_footer_versie', 11 );
				function ihitro_footer_versie() 	{ return '';	}
			}
		}
	}
	
	/**************************************\
	*
	*
	*	Standaard verberg opties
	*
	*
	\***************************************/
	
	private function default_tweaks(){
		// Pas de knoppen aan in de top bar
		add_action( 'admin_bar_menu', 'ihitro_custom_adminbar_menu', 15 );					
		function ihitro_custom_adminbar_menu( $meta = TRUE ) {
			global $wp_admin_bar;
			
			$wp_admin_bar->add_menu( array(
				'id' => 'custom_menu_one',
				'title' => __( '<img src="' . plugin_dir_url( __FILE__ ) . '/images/ihitro-logo-small.png" style="margin-top:3px;">' ),
				'href' 	=> 'http://www.ihitro.nl' ) 			// set the menu name 
			);	
			if( !is_admin()){
				$wp_admin_bar->add_menu( array(
					'id' => 'custom_menu_two',
					'title' => 'Website',
					'href' 	=> get_home_url() ) 			//set the menu name 
				);	
			}else{
				$wp_admin_bar->add_menu( array(
					'id' => 'custom_menu_three',
					'title' => 'CMS',
					'href' 	=> admin_url() ) 			// set the menu name 
				);	
			}
		}
		
		// Verwijder onderdelen bovenin de toolbar
		add_action( 'wp_before_admin_bar_render', 'ihitro_admin_bar' );
		function ihitro_admin_bar() {
			global $wp_admin_bar;
			$wp_admin_bar->remove_menu('wp-logo');			// Verwijder het WordPress logo
			$wp_admin_bar->remove_menu('new-content');		// Verwijder de knop om nieuwe content te plaatsen als media, bericht ed.
			$wp_admin_bar->remove_menu('search');			// Verwijder de zoekfunctie
			$wp_admin_bar->remove_menu('comments');			// Verwijder de comments ballon
			//$wp_admin_bar->remove_menu('site-name');		// Verwijder de knop om websites te bekijken
		}
		
		// Verwijder standaard widgets van dashboad
		add_action('wp_dashboard_setup', 'ihitro_remove_dashboard_widgets');
		function ihitro_remove_dashboard_widgets() {
			global $wp_meta_boxes;
			unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
			unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
			unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
			unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
		
			unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
			unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
			unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
			unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
			unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_stats']);
		}
		
		// Verwijder welkom bericht op dashboard
		add_action( 'load-index.php', 'ihitro_remove_welcome_panel' );
		function ihitro_remove_welcome_panel()
		{
			remove_action('welcome_panel', 'wp_welcome_panel');
			$user_id = get_current_user_id();
			if (0 !== get_user_meta( $user_id, 'show_welcome_panel', true ) ) {
				update_user_meta( $user_id, 'show_welcome_panel', 0 );
			}
		}
			
		// Voeg Ihitro gegevens toe aan dashboard
		add_action('wp_dashboard_setup', 'ihitro_add_dashboard_widgets' );
		function ihitro_dashboard_widget_function() {
			echo "<ul>
			<li><strong>ihitro:media</strong></li>
			<li>Boterzwin 2309a</li>
			<li>1788WN Den Helder</li>
			<li><hr style=\"margin:10px 0px;\"></li>
			<li><strong>T:</strong> 0223-692935</li>
			<li><strong>E:</strong> <a href=\"mailto:info@ihitro.nl\">info@ihitro.nl</a></li>
			<li><hr style=\"margin:10px 0px;\"></li>
			<li>Datum: " .  date("d-m-Y")  . "</li>
			</ul>";
		}
		function ihitro_add_dashboard_widgets() {
			wp_add_dashboard_widget('ihitroDashboardWidget', 'ihitro:media', 'ihitro_dashboard_widget_function');
		}
	}
	
	/**************************************\
	*
	*
	*	Configuratie
	*
	*
	\***************************************/
	
	private function config(){
        /*********************************\
        * Configuratie pagina aanmaken
        \*********************************/
        // Maak het menu aan
        function ihitro_menu_admin_config(){
            // Haal de configuratie gegevens op
            $options = get_option('ihitro_config');
            // $current_user->user_login
                // Create TOP level menu  -> TER INFO: options menu = add_options_page || add_menu_page
                add_menu_page('Ihitro Media configuratie', 'Ihitro Media', 'manage_options', 'ihitro-media-configuratie', 'ihitro_admin_config_pagina', plugins_url('images/menu_icon.png', __FILE__));
            
        }
        // Bouw de configuratie pagina aan
        function ihitro_admin_config_pagina(){
            // Haal de gegevens op
            $options	= get_option('ihitro_config');
            $users		= get_option('ihitro_config_users');
            // Locatie favicon
            $icon_url = plugins_url('favicon.ico', __FILE__);
    ?>
            <div id="ihitro_opties_algmeen" class="wrap">
              <h2>  Algemene configuratie opties  <h2>
              <form method="post" action="admin-post.php">
                <input type="hidden" name="action" value="save_ihitro_config" />
                <?php wp_nonce_field('IhItrO'); ?>
                <table class="wp-list-table widefat fixed">
                  <thead>
                    <tr>
                      <th width="250">Omschrijving</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                   <tr>
                      <td>Ihitro username</td>
                      <td><?php	$gebruikers = get_users('role=administrator'); foreach ($gebruikers as $user) { ?> <input type="checkbox" name="users[]" value="<?php echo $user->ID; ?>" <?php if(in_array($user->ID, $users)){ echo 'checked="checked"'; } ?> /> <?php echo $user->user_login; ?> <em>( <?php echo $user->user_firstname . ' ' . $user->user_lastname; ?> )</em><?php }  ?> </td>
                    </tr>
                    <tr>
                      <td><img width="16" src="<?php echo $icon_url; ?>" alt="Ihitro Media Favicon" /> Ihitro favicon tonen</td>
                      <td><input type="checkbox" name="favicon" <?php if($options['favicon']){ echo 'checked="checked"'; } ?> /></td>
                    </tr>
                    <tr>
                      <td>Ihitro login css</td>
                      <td><input type="checkbox" name="login_css" <?php if($options['login_css']){ echo 'checked="checked"'; } ?> /></td>
                    </tr>
                     <tr>
                      <td>Ihitro admin css</td>
                      <td><input type="checkbox" name="admin_css" <?php if($options['admin_css']){ echo 'checked="checked"'; } ?> /></td>
                    </tr>
                    <tr>
                      <td>Ihitro footer tekst</td>
                      <td><input type="checkbox" name="footer_text" <?php if($options['footer_text']){ echo 'checked="checked"'; } ?> /></td>
                    </tr>
                    <tr>
                      <td>Ihitro footer alternatieve tekst</td>
                      <td><input type="text" name="footer_text_alt" size="40" value="<?php echo $options['footer_text_alt']; ?>" placeholder="Vul eventueel een andere tekst in" /> <em>Standaard: <?php echo $ihitro->footer_text; ?></em></td>
                    </tr>
                    <tr>
                      <td>Verberg wordpress versie </td>
                      <td><input type="checkbox" name="footer_versie" <?php if($options['footer_versie']){ echo 'checked="checked"'; } ?> /> <em>Geldt niet voor beheerders</em></td>
                    </tr>
                  </tbody>
                </table>
                <input type="submit" value="Opslaan" class="button-primary">
              </form>
            </div>
    <?php
        }
        // Roep menu aanmaken aan
        add_action('admin_menu', 'ihitro_menu_admin_config');
        /*********************************\
        * Configuratie pagina verwerken
        \*********************************/
        // Roep de functie aan die de vaiabelen op gaat slaan
        function ihitro_config_init(){
            add_action('admin_post_save_ihitro_config', 'process_ihitro_config_options');
        }
        // Verwerk de variabelen
        function process_ihitro_config_options(){
            // Kijk of de persoon het juiste inlog niveau heeft
            if(!current_user_can('manage_options'))
                wp_die('Not allowed');
            // Controleer het wp_nonce veld
            check_admin_referer('IhItrO');
            
            // Haal de opties op
            $options	= get_option('ihitro_config');
            // Loop alle TEXT velden van het formulier door
            foreach( array('footer_text_alt') as $option_naam){
                if( isset($_POST[$option_naam]) ){
                    $options[$option_naam] = sanitize_text_field($_POST[$option_naam]);
                }
            }
            // Loop alle CHECKBOX velden van het formulier door
            foreach( array('favicon','login_css','admin_css','footer_text','footer_versie') as $option_naam){
                if( isset($_POST[$option_naam]) ){
                    $options[$option_naam] = true;
                } else {
                    $options[$option_naam] = false;
                }
            }
            // Sla de gebruikers op
            foreach( $_POST['users'] as $option_naam => $value){
                if( isset($value) ){
                    $users[] = $value;
                }
            }
            // Sla de gegevens op in de database
            update_option('ihitro_config'		,$options);
            update_option('ihitro_config_users'	,$users);
            // Ga terug naar de optie pagina
            wp_redirect(add_query_arg('page', 'ihitro-media-configuratie', admin_url('admin.php')));
            exit;
        }
        // Start verwerking opslaan configuratie
        add_action('admin_init', 'ihitro_config_init');
	}
	
	/**************************************\
	*
	*
	*	Log lijst
	*
	*
	\***************************************/
	
	public function create_acceslog(){
		/*********************************\
       * Maak posttype aan
       \*********************************/
		$labels = array(
			'name'               => 'Access Log',
			'singular_name'      => 'Access',
			'add_new'            => 'Toevoegen',
			'add_new_item'       => 'Naam',
			'edit_item'          => 'Aanpassen',
			'new_item'           => 'Nieuw',
			'all_items'          => 'Alle Resultaten',
			'search_items'       => 'Zoek Resultaat',
			'not_found'          => 'Geen Resultaten Gevonden',
			'not_found_in_trash' => 'Geen Resultaten Gevonden In De Prullenbak',
			'parent_item_colon'  => '',
			'menu_name'          => 'Access Log'
		  );
		
		  $args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'			   => true,
			//'show_in_admin_bar'  => true,
			//'rewrite'            => array( 'slug' => 'acceslog' ),
			'rewrite'			   => false,
			'capability_type'    => 'post',
			'capabilities'		   => array(
												'create_posts' => false,
												'delete_post' => false,
												'read' => false 
											),
			'has_archive'        => true,
			'menu_position'      => 100,
			'supports'           => array( 'title', ),
			'menu_icon'		   => plugins_url('images/access-log.png', __FILE__)
		);
		register_post_type( 'ihitro_acceslog', $args );
		/*********************************\
       * Maak MetaBoxes aan
       \*********************************
		add_action('admin_init', 'acceslog_metabox_init');
		function acceslog_metabox_init(){
		   add_meta_box('acceslog_metabox_details',
		   					'Toegang details',
							'acceslog_metabox_init_details',
							'ihitro_acceslog', 'normal', 'high');
		}
		function acceslog_metabox_init_details($data){
		   	$datum	= esc_html(get_post_meta($data->ID, 'accesslog_ip', true));
?>
			<table>
            <tr><td style="width:100%;">Datum</td><td><input type="text" size="80" name="datum" value="<?php echo $datum; ?>"></td></tr>
            </table>
<?php
	   	}
		// Sla de data op
		add_action('save_post', 'acceslog_metabox_details_velden', 10, 2);
	 	function acceslog_metabox_details_velden($data_id, $data){
			/*
			// Controleer of het van de juiste posttype af komt
			if($data->post_type == 'ihitro_acceslog'){
				if(isset($_POST['datum']) && $_POST['datum'] != ''){
					update_post_meta($data_id, 'accesslog_ip', $_POST['datum']);
				}
			}
			/* *
		}
		/* */
		add_filter('manage_edit-ihitro_acceslog_columns', 'ihitro_acceslog_add_columns');
		function ihitro_acceslog_add_columns($columns){
			$columns['accesslog_ip']			= 'IP';
			$columns['accesslog_provider']	= 'IP informatie';
			$columns['accesslog_user_id']		= 'Id';
			return $columns;
		}
		add_action('manage_posts_custom_column', 'ihitro_acceslog_populate_columns');
		function ihitro_acceslog_populate_columns($column){
			if($column == 'accesslog_ip'){
				$accesslog_ip = esc_html(get_post_meta(get_the_ID(), 'accesslog_ip', true));
				echo $accesslog_ip;
			}
			if($column == 'accesslog_provider'){
				$accesslog_provider = esc_html(get_post_meta(get_the_ID(), 'accesslog_provider', true));
				echo $accesslog_provider;
			}
			if($column == 'accesslog_user_id'){
				$accesslog_user_id = esc_html(get_post_meta(get_the_ID(), 'accesslog_user_id', true));
				echo $accesslog_user_id;
			}
		}
		add_filter('manage_edit-ihitro_acceslog_sortable_columns', 'ihitro_acceslog_column_sortable');
		function ihitro_acceslog_column_sortable(){
			$columns['title']					= 'title';
			$columns['date']					= 'date';
			$columns['accesslog_ip']			= 'accesslog_ip';
			$columns['accesslog_provider']	= 'accesslog_provider';
			return $columns;
		}
		//
		// Registreer inloggen
		function ihitro_acceslog_register($login) {
			//global $user_ID;
   			$user		= get_userdatabylogin($login);
			$user_info	= get_userdata( $user->ID );
			// Create post object
			$my_post = array(
			  'post_title'    => $user_info->first_name.' '.$user_info->last_name,
			  'post_status'   => 'publish',
			  'post_author'   => 1,
			  'post_type'     => 'ihitro_acceslog'
			);
			// Insert the post into the database
			wp_insert_post( $my_post );
			$args = array(
					'numberposts' => 1,
					'offset' => 0,
					'category' => 0,
					'orderby' => 'post_date',
					'order' => 'DESC',
					'post_type' => 'ihitro_acceslog'
					);
			$last = wp_get_recent_posts($args);
			$last_id = $last['0']['ID'];
			update_post_meta($last_id, 'accesslog_ip'			, $_SERVER['REMOTE_ADDR']);
			update_post_meta($last_id, 'accesslog_provider'		, gethostbyaddr($_SERVER['REMOTE_ADDR']));
			update_post_meta($last_id, 'accesslog_user_id'		, $user->ID);
		}
		add_action('wp_login', 'ihitro_acceslog_register');
	}
			
}
?>