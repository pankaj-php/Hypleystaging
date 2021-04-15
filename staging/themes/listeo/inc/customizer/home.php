<?php 



listeo_Kirki::add_section( 'homepage', array(
    'title'          => esc_html__( 'Home Search Options', 'listeo'  ),
    'description'    => esc_html__( 'Options for Page with Search form', 'listeo'  ),
    'priority'       => 21,
    'capability'     => 'edit_theme_options',
    'theme_supports' => '', // Rarely needed.
) );
		
		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'radio',
		    'settings'     => 'listeo_home_transparent_header',
		    'label'       => esc_html__( 'Enable Transparent Header on Homepage', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => 'disable',
			'priority'    => 1,
			'choices'     => array(
				'enable'  => esc_attr__( 'Enable', 'listeo' ),
				'disable' => esc_attr__( 'Disable', 'listeo' ),
			),
		) );

		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'radio',
		    'settings'     => 'listeo_home_form_type',
		    'label'       => esc_html__( 'Choose Search Form Style:', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => 'wide',
			'priority'    => 1,
			'choices'     => array(
				'wide'  => esc_attr__( 'Wide', 'listeo' ),
				'boxed' => esc_attr__( 'Boxed', 'listeo' ),
			),
		) );	

		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'text',
		    'settings'     => 'listeo_home_title',
		    'label'       => esc_html__( 'Search Banner Title', 'listeo' ),
		    'description' => esc_html__( 'Title above search form ', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => esc_html__('Find Nearby Attractions','listeo') ,
		    'priority'    => 1,
		) );

		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'radio',
		    'settings'     => 'listeo_home_full_screen',
		    'label'       => esc_html__( 'Full Screen Search Container', 'listeo' ),
		    'description'       => esc_html__( 'Works above 1360px viewport', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => 'disable',
			'priority'    => 1,
			'choices'     => array(
				'enable'  => esc_attr__( 'Enable', 'listeo' ),
				'disable' => esc_attr__( 'Disable', 'listeo' ),
			),
		) );

		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'radio',
		    'settings'     => 'listeo_home_typed_status',
		    'label'       => esc_html__( 'Enable Typed words effect', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => 'disable',
			'priority'    => 1,
			'choices'     => array(
				'enable'  => esc_attr__( 'Enable', 'listeo' ),
				'disable' => esc_attr__( 'Disable', 'listeo' ),
			),
			'active_callback'  => array(
	            array(
	                'setting'  => 'listeo_home_form_type',
	                'operator' => '==',
	                'value'    => 'wide',
	            ),
	        )
		) );			

		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'text',
		    'settings'     => 'listeo_home_typed_text',
		    'label'       => esc_html__( 'Text to display in "typed" Banner Subtitle', 'listeo' ),
		    'description' => esc_html__( 'Separate with coma', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => esc_html__('Attractions, Restaurants, Hotels','listeo') ,
		    'priority'    => 1,
		    'active_callback'  => array(
	            array(
	                'setting'  => 'listeo_home_typed_status',
	                'operator' => '==',
	                'value'    => 'enable',
	            ),
	            array(
	                'setting'  => 'listeo_home_form_type',
	                'operator' => '==',
	                'value'    => 'wide',
	            ),
	        )

		) );	

		
		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'text',
		    'settings'     => 'listeo_home_subtitle',
		    'label'       => esc_html__( 'Search Banner Subtitle', 'listeo' ),
		    'description' => esc_html__( 'Subtitle above search form ', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => esc_html__('Expolore top-rated attractions, activities and more','listeo') ,
		    'priority'    => 1,
		) );	

		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'radio',
		    'settings'     => 'listeo_home_banner_text_align',
		    'label'       => esc_html__( 'Text alignment on Search form', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => 'left',
			'priority'    => 1,
			'choices'     => array(
				'center'  => esc_attr__( 'Center', 'listeo' ),
				'left' => esc_attr__( 'Left', 'listeo' ),
			),
			'active_callback'  => array(
	            array(
	                'setting'  => 'listeo_home_form_type',
	                'operator' => '==',
	                'value'    => 'wide',
	            ),
	        )
		) );	
		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'radio',
		    'settings'     => 'listeo_home_featured_categories_status',
		    'label'       => esc_html__( 'Enable "or browse by category" section', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => 'disable',
			'priority'    => 99,
			'choices'     => array(
				'enable'  => esc_attr__( 'Enable', 'listeo' ),
				'disable' => esc_attr__( 'Disable', 'listeo' ),
			),
			'active_callback'  => array(
	            array(
	                'setting'  => 'listeo_home_form_type',
	                'operator' => '==',
	                'value'    => 'wide',
	            ),
	        )
		) );




		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'radio',
		    'settings'     => 'listeo_home_background_type',
		    'label'       => esc_html__( 'Choose Background Type for Form:', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => 'image',
			'priority'    => 1,
			'choices'     => array(
				'image'  => esc_attr__( 'Image', 'listeo' ),
				'video' => esc_attr__( 'Video', 'listeo' ),
			),
		) );	

		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'image',
		    'settings'     => 'listeo_search_bg',
		    'label'       => esc_html__( 'Background for search banner on homepage', 'listeo' ),
		    'description' => esc_html__( 'Set image for search banner, should be 1920px wide', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => '',
		    'priority'    => 2,
		    'active_callback'  => array(
	            array(
	                'setting'  => 'listeo_home_background_type',
	                'operator' => '==',
	                'value'    => 'image',
	            ),
	        )
		) );

		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'radio',
		    'settings'     => 'listeo_home_solid_background',
		    'label'       => esc_html__( 'Enable Solid Background', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => 'disable',
			'priority'    => 1,
			'choices'     => array(
				'enable'  => esc_attr__( 'Enable', 'listeo' ),
				'disable' => esc_attr__( 'Disable', 'listeo' ),
			),
			'active_callback'  => array(
	            array(
	                'setting'  => 'listeo_home_transparent_header',
	                'operator' => '==',
	                'value'    => 'disable',
	            ),
	        )
		) );	
		listeo_Kirki::add_field( 'listeo', array(
			'type'        => 'slider',
			'settings'    => 'listeo_search_bg_opacity',
			'label'       => esc_html__( 'Banner opacity', 'listeo' ),
			'section'     => 'homepage',
			'default'     => '0.8',
			'choices'     => array(
				'min'  => '0',
				'max'  => '1',
				'step' => '0.01',
			),
			'priority'    => 3,
		
		) ); 

		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'color',
		    'settings'     => 'listeo_search_color',
		    'label'       => esc_html__( 'Color for the image overlay on homepage search banner', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => '#333333',
		    'priority'    => 4,
		     
		) );

		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'image',
		    'settings'    => 'listeo_search_video_poster',
		    'label'       => esc_html__( 'Video Poster', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => false,
		    'priority'    => 5,
		     'active_callback'  => array(
	            array(
	                'setting'  => 'listeo_home_background_type',
	                'operator' => '==',
	                'value'    => 'video',
	            ),
	        )
		) );

		listeo_Kirki::add_field( 'listeo', array(
	    'type'        => 'upload',
	    'settings'    => 'listeo_search_video_webm',
	    'label'       => esc_html__( 'Upload webm file', 'listeo' ),
	    'section'     => 'homepage',
	    'default'     => false,
	    'priority'    => 6,
	     'active_callback'  => array(
	            array(
	                'setting'  => 'listeo_home_background_type',
	                'operator' => '==',
	                'value'    => 'video',
	            ),
	        )
	    
		) );
		listeo_Kirki::add_field( 'listeo', array(
		    'type'        => 'upload',
		    'settings'    => 'listeo_search_video_mp4',
		    'label'       => esc_html__( 'Upload mp4 file', 'listeo' ),
		    'section'     => 'homepage',
		    'default'     => false,
		    'priority'    => 7,
		    'active_callback'  => array(
	            array(
	                'setting'  => 'listeo_home_background_type',
	                'operator' => '==',
	                'value'    => 'video',
	            ),
	        )
		    
		) );
	

		// listeo_Kirki::add_field( 'listeo', array(
		//     'type'        => 'color',
		//     'settings'     => 'listeo_video_search_color',
		//     'label'       => esc_html__( 'Video overlay color and opacity', 'listeo' ),
		//     'section'     => 'homepage',
		//     'default'     => 'rgba(22,22,22,0.4)',
		//     'priority'    => 9,
		//     'choices'     => array(
		// 		'alpha' => true,
		// 	),
		// 	'active_callback'  => array(
	 //            array(
	 //                'setting'  => 'listeo_home_background_type',
	 //                'operator' => '==',
	 //                'value'    => 'video',
	 //            ),
	 //        )
		// ) );




add_action( 'customize_register', 'jt_load_customize_controls', 0 );
function jt_load_customize_controls() {

	
class Listeo_Customize_Control_Checkbox_Multiple extends WP_Customize_Control {

	/**
	 * The type of customize control being rendered.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $type = 'checkbox-multiple';

	/**
	 * Enqueue scripts/styles.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_script( 'jt-customize-controls', trailingslashit( get_template_directory_uri() ) . 'js/customize-controls.js', array( 'jquery' ) );
	}

	/**
	 * Displays the control content.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function render_content() {

		?>

		<?php if ( !empty( $this->label ) ) : ?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>

		<?php if ( !empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php endif; ?>

		<?php $multi_values = !is_array( $this->value() ) ? explode( ',', $this->value() ) : $this->value(); ?>

		<ul>
			<?php 
	 		$terms = get_terms('listing_category');
	 		
			foreach (  $terms as $term) : 
			
				?>
				<li>
					<label>
						<input type="checkbox" value="<?php echo esc_attr( $term->term_id ); ?>" <?php checked( in_array( $term->term_id, $multi_values ) ); ?> />
						<?php echo esc_html( $term->name ); ?>
					</label>
				</li>

			<?php endforeach; ?>
		</ul>

		<input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( implode( ',', $multi_values ) ); ?>" />
	<?php }
}
}
add_action( 'customize_register', 'listeo_customizer_register' );

function listeo_customizer_register( $wp_customize ) {

	$wp_customize->add_setting(
		'listeo_home_featured_categories',
		array(
			'default'           => array(),
			'sanitize_callback' => 'listeo_sanitize_listeo_home_featured_categories'
		)
	);

	$wp_customize->add_control(
		new Listeo_Customize_Control_Checkbox_Multiple(
			$wp_customize,
			'listeo_home_featured_categories',
			array(
				'section' => 'homepage',
				'priority' => 100,
				'label'   => __( 'Featured Categories', 'listeo' ),
				'term' => 'listing_category'
			)
		)
	);
}
function listeo_sanitize_listeo_home_featured_categories( $values ) {

	$multi_values = !is_array( $values ) ? explode( ',', $values ) : $values;

	return !empty( $multi_values ) ? array_map( 'sanitize_text_field', $multi_values ) : array();
}
?>