<?php
	class Woocommerce_Setting_Price{

		private static $initiated = false;

		public static function init() {
			if ( ! defined( 'ABSPATH' ) ) { 
			    exit; // Exit if accessed directly
			}
			if ( ! self::$initiated ){
				self::init_hooks();
			}
		}
	// Initializes WordPress hooks.
		private static function init_hooks() {
			self::$initiated = true;
		// filter
			add_filter( 'woocommerce_get_price', array( 'Woocommerce_Setting_Price', 'woocommerce_set_price' ),  10, 2 );
			add_filter( 'woocommerce_get_sale_price', array( 'Woocommerce_Setting_Price', 'woocommerce_set_sale_price' ), 10, 2 );
			add_filter( 'plugin_action_links_' . SP_PLUGIN_BASENAME, array( 'Woocommerce_Setting_Price', 'plugin_settings_link' ) );
		// action
			add_action( 'woocommerce_product_write_panel_tabs', array( 'Woocommerce_Setting_Price', 'woocommerce_product_write_panel_tabs' ) );
			add_action( 'woocommerce_product_write_panels', array( 'Woocommerce_Setting_Price', 'woocommerce_product_write_panel' ) );
			add_action( 'admin_enqueue_scripts', array( 'Woocommerce_Setting_Price', 'load_resources_backend' ) );
			add_action( 'wp_ajax_woocommerce_saving_price', array( 'Woocommerce_Setting_Price','woocommerce_saving_price' ) );
			add_action( 'wp_ajax_woocommerce_geo_info', array( 'Woocommerce_Setting_Price','woocommerce_geo_info' ) );
			add_action( 'wp_ajax_woocommerce_delete_price', array( 'Woocommerce_Setting_Price','woocommerce_delete_price' ) );
			add_action( 'wp_ajax_woocommerce_update_price', array( 'Woocommerce_Setting_Price','woocommerce_update_price' ) );
			add_action( 'wp_ajax_woocommerce_pagination_option', array( 'Woocommerce_Setting_Price','woocommerce_pagination_option' ) );
		}

	// Link settings
		public static function plugin_settings_link( $links ) {	
			$plugin_links = '<a href="' . admin_url( 'edit.php?post_type=product', '//' ) . '">Настройки</a>';
			array_unshift( $links, $plugin_links );
			return $links; 
		}

	// Add tab to product panel
		public static function woocommerce_product_write_panel_tabs() {
			$product_data_tabs = apply_filters( 'woocommerce_product_data_tabs', array(
	            'setting_price' => array(
	                'label'  => __( 'Установить цены', 'woocommerce' ),
	                'target' => 'setting_price_product_data',
	                'class'  => array(),
	            	)
	           	)
			);
			foreach ( $product_data_tabs as $key => $tab ) : ?>
			 	<li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab <?php echo implode( ' ', $tab['class'] ); ?>">
	                <a href=" <?php echo $tab['target']; ?>"><?php echo esc_html( $tab['label'] ); ?></a>
	            </li>
	        <?php 
	        endforeach;
		}

	// Add setting to product panel
		public static function woocommerce_product_write_panel() { ?>
			<div id="setting_price_product_data" class="panel woocommerce_options_panel hidden">
				<div class="options_group">
					<?php 
						woocommerce_wp_text_input( array(
							'id' => 'sp_google_autocomplete',
							'name' => 'choose_country',
						    'label' => __( 'Где:', 'woocommerce' ),
						    'placeholder' => _x( 'Выберите страну/город', 'placeholder', 'woocommerce' ),
						    'type' => 'text' )
						);
					?>
					<span id="valid_field_autocomplete"></span>	
				</div>
				<div class="options_group">
					<?php 
						woocommerce_wp_text_input( array( 
							'id' => 'add_price',
					    	'label' => __( 'Цена:', 'woocommerce' ),
					   		'placeholder' => _x( 'Введите цену', 'placeholder', 'woocommerce' ),
					    	'type' => 'number', 'custom_attributes' => array(
		                        'step'  => '1',
		                        'min'   => '0'
		                    ) )
						);
					?>
					<span id="valid_field_price"></span>
				</div>
				<div class="options_group">
					<?php 
						woocommerce_wp_text_input( array( 
							'id' => 'add_sale_price',
					    	'label' => __( 'Цена распродажи:', 'woocommerce' ),
					   		'placeholder' => _x( 'Введите цену', 'placeholder', 'woocommerce' ),
					    	'type' => 'number', 'custom_attributes' => array(
		                        'step'  => '1',
		                        'min'   => '0'
		                    ) )
						);
					?>
					<span id="valid_field_sale_price"></span>
				</div>
				<button type="button" id="save_choose" name="save_price_city" class="button button-primary"><?php _e( 'Добавить', 'woocommerce' ); ?></button>
				<?php
					global $post;
					$option_price = get_post_meta( $post->ID, 'new_price', true );				
					if ( ! empty ( $option_price ) ) : 
				    	$options = array_chunk( array_reverse( $option_price ), 5 );
				   		$pag = ( ! empty( $_GET['page'] ) ) ? (int)$_GET['page'] : 0;
				   		ob_start();
				?>
						<div id="show_option_add_price">
							<button class="btn" id="show_price" type="button" data-toggle="dropdown">
								Установленные цены <span class="caret"></span>
							</button>
							<div id="set_price">
								<ul class="dropdown-menu">
									<?php foreach ( $options[$pag]  as $option ) : ?>
										<li>
											<p class="form-field show_set_price_field ">
												<label for="show_set_price"><b>Цена для:</b> <i> <?php echo $option['city']; ?> </i></label>
												<input type="number" class="short show_set_price" name="show_set_price" value="<?php echo $option['price']; ?>" placeholder="" step="1" min="0">
												<button class="button button-primary update_city_price" data-id="<?php echo $option['id']; ?>"><?php _e( 'Сохранить', 'woocommerce' ); ?></button>
												<button class="button button-primary delete_price_city" data-id="<?php echo $option['id']; ?>"><?php _e( 'Удалить', 'woocommerce' ); ?></button>
												<span class="button_save"></span>
											</p>
											<p class="form-field show_set_price_field ">
												<label for="show_sale_price"><b>Цена распродажи: </b></label>
												<input type="number" class="short show_sale_price" name="show_sale_price" value="<?php echo $option['sale_price']; ?>" placeholder="" step="1" min="0">
												<button class="button button-primary update_city_price" data-id="<?php echo $option['id']; ?>"><?php _e( 'Сохранить', 'woocommerce' ); ?></button>
												<button class="button button-primary delete_price_city" data-id="<?php echo $option['id']; ?>"><?php _e( 'Очистить', 'woocommerce' ); ?></button>
												<span class="button_save_sale"></span>
											</p>
											<hr>
										</li>
									<?php endforeach; ?>
								</ul>
								<?php if( count( $option_price ) > 5 ) : ?>
									<div class="pagination">
							        <?php 
						        		for ( $i = 0; $i < count( $options ); $i++ ) { 
											$_GET['page'] = $i;
									        if ( $i != $pag ) {
									            echo "<a id='pagination_option' rel='$i'>" . ( $i + 1 ) . "</a>&nbsp;";
									        } else {
									            echo "<span class='pag'>" . ( $i + 1 ) . "</span>&nbsp;";
									        }
						        		}
							        ?>
							        </div> 
							    <?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
			</div>				
		<?php 
			wp_reset_query();
			$output_string = ob_get_contents();
			ob_end_clean();
			echo $output_string;
	}

	// Save price for city in array
		public static function woocommerce_saving_price() {
			$id = '';
			$sale_price = '';
			$new_city_price = get_post_meta( $_POST['post_id'], 'new_price', true );
			if ( ! empty( $_POST['sale_price'] ) ) {
				$sale_price = $_POST['sale_price'];
			}
			if ( ! empty( $new_city_price ) ) {
				$option_id = array_pop( get_post_meta( $_POST['post_id'], 'new_price', true ) );
				$id = $option_id['id']+1;
				$show_set_price = "";
			} else {
				$new_city_price = array();
				$id = 1;
				$show_set_price = "<button class='btn' id='show_price' data-toggle='dropdown'>";
				$show_set_price .= "Установленные цены <span class='caret'></span>";				
				$show_set_price .= "</button>";			
				$show_set_price .= "<div id='set_price'>";	
				$show_set_price .= "<ul class='dropdown-menu'>";	
				$html = "";	
			}
			for ( $i = 0; $i < count( $new_city_price ); $i++ ) { 
				if ( $_POST['city'] == $new_city_price[$i]['city'] ) {
					exit('false');
				}
			}
			$new_price = array( 'id' => $id, 'city' => $_POST['city'], 'short_name_country' => $_POST['new_point']['short_name_country'], 'price' => $_POST['price'], 'sale_price' => $sale_price );
			array_push( $new_city_price, $new_price );
			$up = update_post_meta( $_POST['post_id'], 'new_price', $new_city_price );
			if ( $up ) {
				$html = "<p class='form-field show_set_price_field'>";
				$html .= "<label for='show_set_price'><b>Цена для:</b> <i> {$_POST['city']} </i></label>";
				$html .= "<input type='number' class='short show_set_price' name='show_set_price' value='{$_POST['price']}' placeholder='' step='1' min='0'>";
				$html .= "<button class='button button-primary save_price update_city_price' data-id='{$id}'> Сохранить </button>";
				$html .= "<button class='button button-primary delete_price_city' data-id='{$id}'> Удалить </button>";
				$html .= "<span class='button_save'></span>";
				$html .= "</p>";
				$html .= "<p class='form-field show_set_price_field'>";
				$html .= "<label for='show_sale_price'><b>Цена распродажи: </b></label>";
				$html .= "<input type='number' class='short show_sale_price' name='show_sale_price' value='{$_POST['sale_price']}' placeholder='' step='1' min='0'>";
				$html .= "<button class='button button-primary save_price update_city_price' data-id='{$id}'> Сохранить </button>";
				$html .= "<button class='button button-primary delete_price_city' data-id='{$id}'> Очистить </button>";
				$html .= "<span class='button_save_sale'></span>";
				$html .= "</p>";
				$html .= "<hr>";
				echo json_encode( array( 'res' => 'true', 'html' => $html, 'show_set_price' => $show_set_price ) );
			}
			die;
		}

	// Pagination
		public static function woocommerce_pagination_option() {
			$option_price = get_post_meta( $_POST['post_id'], 'new_price', true );
			if ( ! empty( $option_price ) ) : 
		    	$pag = $_POST['id'];
		    	$options = array_chunk( array_reverse( $option_price ), 5 );
				foreach( $options[$pag] as $option ): 
					$html = "<li>";
					$html .= "<p class='form-field show_set_price_field'>";
					$html .= "<label for='show_set_price'><b>Цена для:</b> <i> " . $option['city'] . " </i></label>";
					$html .= "<input type='number' class='short show_set_price' name='show_set_price' value=" . $option['price'] . " placeholder='' step='1' min='0'>";
					$html .= "<button class='button button-primary save_price update_city_price' data-id= " . $option['id'] ."> Сохранить </button>";
					$html .= "<button class='button button-primary delete_price_city' data-id=" . $option['id'] . "> Удалить </button>";
					$html .= "<span class='button_save'></span>";
					$html .= "</p>";
					$html .= "<p class='form-field show_set_price_field'>";
					$html .= "<label for='show_sale_price'><b>Цена распродажи:</b></label>";
					$html .= "<input type='number' class='short show_sale_price' name='show_sale_price' value=" . $option['sale_price'] . " placeholder=' step='1' min='0'>";
					$html .= "<button class='button button-primary save_price update_city_price' data-id=" . $option['id'] . "> Сохранить </button>";
					$html .= "<button class='button button-primary delete_price_city' data-id=" . $option['id'] . "> Очистить </button>";
					$html .= "<span class='button_save_sale'></span>";
					$html .= "</p>";
					$html .= "<hr>";
					$html .= "</li>";
				endforeach; 
				$html = "<div class='pagination'>";
	        		for ( $i = 0; $i < count( $options ); $i++ ) { 
						$_GET['page'] = $i;
				        if ( $i != $pag ) {
				            $html .= "<a id='pagination_option' rel='$i'>" . ( $i + 1 ) . "</a>&nbsp;";
				        } else {
				            $html .= "<span class='pag'>" . ( $i + 1 ) . "</span>&nbsp;";
				        }
	        		}
				$html .= "</div>";
			endif; 
			echo json_encode( array ( 'html' => $html, 'res' => $options[$pag] ) );
			die;
		}

	// Delete price from array
		public static function woocommerce_delete_price() {
			$city_price = get_post_meta( $_POST['post_id'], 'new_price', true );
			if( $_POST['name'] == 'show_set_price' ){
				$res = '';
				if ( count( $city_price ) > 1 ) {
					for ( $i = 0; $i < count( $city_price ); $i++ ) { 
						if ( $city_price[$i]['id'] == $_POST['id'] ) {
							unset( $city_price[$i] );
						}
					}
					sort( $city_price );
					$up = update_post_meta( $_POST['post_id'], 'new_price', $city_price );
					if ( $up ) {
						$res = 'true';
					} else {
						$res = 'false';
					}	
				} else {
					$res = 'del';	
					delete_post_meta( $_POST['post_id'], 'new_price' );
				}
			} else {
				for ( $i = 0; $i < count( $city_price ); $i++ ) { 
					if ( $city_price[$i]['id'] == $_POST['id'] ) {
						$city_price[$i]['sale_price'] = '';
					}
				} 
				$up = update_post_meta( $_POST['post_id'], 'new_price', $city_price );
				if ( $up ){
					$res = 'clean';	
				} 			
			}
			echo json_encode( $res );
			die;
		}

	// Update price in array
		public static function woocommerce_update_price() {
			$city_price = get_post_meta( $_POST['post_id'], 'new_price', true );
			for ( $i = 0; $i < count( $city_price ); $i++ ) { 
				if ( $city_price[$i]['id'] == $_POST['id'] ) {
					if( $_POST['name'] == 'show_set_price' ){
						$city_price[$i]['price'] = $_POST['price'];
					} else {
						$city_price[$i]['sale_price'] = $_POST['price'];
					}
				}
			}
			$up = update_post_meta( $_POST['post_id'], 'new_price', $city_price );
			if ( $up ){
				exit('true');	
			} else {
				exit('false');
			}
			die;
		}

	// Save geoinfo visitor
		public static function woocommerce_geo_info() {
			update_option( 'place', array( 'city' => $_POST['city'], 'country' => $_POST['country'] ) );
			die;
		}

	// Return saving price 
		public static function woocommerce_set_price( $price, $product ) {
			if( get_post_meta( $product->id, 'new_price', true ) ) {
				$place = get_option('place');
				$city_price = get_post_meta( $product->id, 'new_price', true );
				foreach ( $city_price as $city ) {
					if ( $place['country'] == $city['short_name_country'] && $place['city'] == $city['city'] ) {
						if ( ! empty( $city['sale_price'] ) ) {
							$sale_price = $product->get_sale_price = $city['sale_price'];
							return $sale_price;
						} 
						$price = $city['price'];
					}
				}
			}
			return $price;
		}

	// Return saving sale price 
		public static function woocommerce_set_sale_price( $price, $product ) {
			if( get_post_meta( $product->id, 'new_price', true ) ) {
				$place = get_option('place');
				$city_price = get_post_meta( $product->id, 'new_price', true );
				foreach ( $city_price as $city ) {
					if ( $place['country'] == $city['short_name_country'] && $place['city'] == $city['city'] ) {
						$price = $product->regular_price = $city['price'];
						if ( ! empty( $city['sale_price'] ) ) {
							$sale_price = $product->get_sale_price = $city['sale_price'];
							return $sale_price;
						} 					
					}
				}
			}
			return $price;
		}

	// Style and Scripts
		public static function load_resources_backend() {
		    wp_register_script( 'scp-google-autocomplete', '//maps.googleapis.com/maps/api/js?key=' . SP_API_KEY . '&libraries=places&language=en', array( 'jquery' ), SP_VERSION );
		    wp_enqueue_script( 'scp-google-autocomplete' );

		    wp_register_style( 'plugin-style', SP_PLUGIN_URL . 'assets/css/backend-style.css', array(), SP_VERSION ); 
		    wp_enqueue_style( 'plugin-style' );

		    wp_register_script( 'scp-backend', SP_PLUGIN_URL . 'assets/js/backend-main.js', array( 'jquery' ), SP_VERSION );
		    wp_enqueue_script( 'scp-backend' );
		}
	}
?>