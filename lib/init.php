<?php
/**
 * CMB2 Related Links
 *
 * Custom field for CMB2 which adds a releated links repeatable group field.
 *
 * @category WordPressLibrary
 * @package  CMB2_Related_Links
 * @author   Justin Sternberg <justin@dsgnwrks.pro>
 * @license  GPL-2.0+
 * @version  0.1.0
 * @link     https://github.com/jtsternberg/CMB2-Related-Links
 * @since    0.1.0
 */
class CMB2_Related_Links {

	protected static $single_instance = null;
	protected static $script_add = false;
	protected $l10n_strings = array(
		'description' => 'Add links, or select from related content by clicking the search icon.',
		'group_title' => 'Link {#}',
		'link_title'  => 'Title',
		'link_url'    => 'URL',
		'find_text'   => 'Find/Select related content',
	);

	/**
	 * Creates or returns an instance of this class.
	 * @since  0.1.0
	 * @return CMB2_Related_Links A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Constructor (setup our hooks)
	 */
	protected function __construct() {
		add_action( is_admin() ? 'admin_footer' : 'wp_footer', array( __CLASS__, 'footer_js' ) );
		add_action( 'wp_ajax_cmb2_related_links_get_post_data', array( __CLASS__, 'get_post_data' ) );
	}

	public function field( $args = array(), $l10n_strings = array() ) {
		if ( ! isset( $args['id'] ) || ! $args['id'] ) {
			wp_die( "CMB2_Related_Links field requires an 'id' parameter." );
		}

		$l10n_strings = wp_parse_args( $l10n_strings, $this->l10n_strings );

		$options = array(
			'group_title'   => $l10n_strings['group_title'],
			'add_button'    => '<span class="dashicons dashicons-plus"></span>',
			'remove_button' => '<span class="dashicons dashicons-no-alt"></span>',
			'sortable'      => true,
		);

		if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
			$options = wp_parse_args( $args['options'], $options );
		}

		$fields = array(
			'title' => array(
				'name' => $l10n_strings['link_title'],
				'id'   => 'title',
				'type' => 'text',
				'attributes' => array(
					'placeholder' => $l10n_strings['link_title'],
				),
			),
			'url' => array(
				'name'        => $l10n_strings['link_url'],
				'id'          => 'url',
				'type'        => 'post_search_text',
				'post_type'   => $this->post_types(),
				'select_type' => 'radio',
				'options'     => array(
					'find_text' => $l10n_strings['find_text'],
				),
				'attributes' => array(
					'class'=> 'regular-text post-search-data',
					'placeholder' => $l10n_strings['link_url'],
				),
			),
		);

		if ( isset( $args['fields'] ) && is_array( $args['fields'] ) ) {
			if ( isset( $args['fields']['title'] ) && is_array( $args['fields']['title'] ) ) {
				$fields['title'] = wp_parse_args( $args['fields']['title'], $fields['title'] );
				unset( $args['fields']['title'] );
			}

			if ( isset( $args['fields']['url'] ) && is_array( $args['fields']['url'] ) ) {
				$fields['url'] = wp_parse_args( $args['fields']['url'], $fields['url'] );
				unset( $args['fields']['url'] );
			}

			$fields = array_merge( array_values( $args['fields'] ), array_values( $fields ) );
		} else {
			$fields = array_values( $fields );
		}

		$args = wp_parse_args( $args, array(
			'id'           => '',
			'desc'         => $l10n_strings['description'],
			'before_group' => array( $this, 'before_group' ),
			'after_group'  => '</div><!-- .cmb2-related-links-wrap -->',
			'show_names'   => false,
		) );

		$args['type'] = 'group';
		$args['options'] = $options;
		$args['fields'] = $fields;

		// wp_die( '<xmp>$args: '. print_r( $args, true ) .'</xmp>' );
		return $args;
	}

	public function before_group() {
		self::$script_add = true;
		$css = $this->get_css();
		return $css . '<div class="cmb2-related-links-wrap">';
	}

	public function post_types() {
		$post_types = get_post_types( array( 'public' => true ) );
		unset( $post_types['attachment'] );

		return $post_types;
	}

	/**
	 * Some nasty css to override CMB2 styling for this metabox and make things more compact
	 */
	protected function get_css() {
		static $added = false;

		// Only add this CSS once per page-load.
		if ( $added ) {
			return '';
		}

		$added = true;
		ob_start();
		?>
		<style type="text/css" media="screen">
		.cmb2-related-links-wrap input {
			width: 100%;
		}
		.cmb2-related-links-wrap .cmb-th+.cmb-td {
			width: 100%;
		}
		.cmb2-related-links-wrap .cmb-add-row {
			margin: 1em 0 0;
		}
		.cmb2-related-links-wrap .cmb-add-row .dashicons-plus {
			line-height: 1.5em;
		}
		.cmb2-related-links-wrap:after {
			content: '';
			display: block;
			clear: both;
			width: 100%;
		}
		#side-sortables .cmb2-related-links-wrap .cmb-remove-row {
			padding-top: 0;
		}
		#side-sortables .cmb2-related-links-wrap .cmb-th,
		.cmb2-related-links-wrap .cmb-repeat-group-field,
		.cmb2-related-links-wrap .cmb-td .cmb-td
		{
			padding: 0;
		}
		#side-sortables .cmb2-related-links-wrap .cmb-th label {
			padding: 0;
		}
		#side-sortables .cmb2-related-links-wrap .cmb-th label:after {
			border: 0;
		}

		.cmb2-related-links-wrap .cmb-row.cmb-type-post-search-text {
			border-bottom: 0;
		}
		.cmb2-related-links-wrap .cmb2-post-search-button {
			margin-top: -26px;
			float: right;
			position: relative;
			background-color: white;
			right: 3px;
		}
		.cmb2-related-links-wrap .cmb-row .cmb-row .cmb-row {
			border-bottom: 0;
			margin-bottom: 0;
		}
		.cmb2-related-links-wrap .cmb-row .cmb2-metabox-description {
			padding-bottom: 0;
			padding-bottom: 0;
		}
		.cmb2-related-links-wrap .cmb-remove-field-row {
			padding-top: 0;
			padding-bottom: 2px;
		}
		.cmb2-related-links-wrap .cmb-remove-group-row.alignright {
			line-height: 1em;
			color: #a00;
		}
		.cmb2-related-links-wrap .cmb-remove-group-row.alignright:hover {
			color: red;
		}
		.cmb2-related-links-wrap .cmb-remove-row {
			position: relative;
		}
		</style>
		<?php
		return ob_get_clean();
	}

	/**
	 * Adds JS to footer which enables the autocomplete
	 */
	public static function footer_js() {
		if ( ! self::$script_add ) {
			return '';
		}
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			var debug = <?php echo defined( 'WP_DEBUG' ) && WP_DEBUG ? '1' : '0'; ?>;

			window.relatedlinkdebug = function() {
				window.relatedlinkdebug.history = window.relatedlinkdebug.history || [];
				window.relatedlinkdebug.history.push( arguments );
				if ( window.console && debug ) {
					window.console.log( Array.prototype.slice.call(arguments) );
				}
			};

			function get_post_data( post_id, $object ) {
				window.relatedlinkdebug('post_id',post_id);
				$.post( ajaxurl, {
					action : 'cmb2_related_links_get_post_data',
					ajaxurl : ajaxurl,
					post_id : post_id
				}, function( response ) {
					window.relatedlinkdebug('response',response);
					if ( response.success && response.data.url ) {
						// update the url w/ the post permalink
						$object.val( response.data.url );
						// update the title w/ the post title
						var id = $object.attr( 'id' ).replace( '_url', '_title' );
						$( document.getElementById( id ) ).val( response.data.title );
					}
				})
			}

			// Make sure window.cmb2_post_search is around
			setTimeout( function() {
				if ( window.cmb2_post_search ) {
					// once a post is selected...
					window.cmb2_post_search.handleSelected = function( checked ) {
						window.relatedlinkdebug( 'cmb2_post_search.handleSelected OVERRIDE', '<?php echo __CLASS__; ?>' );

						if ( this.$idInput.hasClass( 'post-search-data' ) ) {

							// ajax-grab the data we need
							get_post_data( checked[0], this.$idInput );

						} else {
							// Make sure things work as normal for other fields.
							var existing = this.$idInput.val();
							existing = existing ? existing + ', ' : '';
							this.$idInput.val( existing + checked.join( ', ' ) );
						}

						this.close();
					};
				}
			}, 500 );
		});
		</script>
		<?php
	}

	/**
	 * Ajax handler for fetching title/url for a post
	 */
	public static function get_post_data() {
		if ( isset( $_POST['post_id'] ) ) {
			$post = get_post( absint( $_POST['post_id'] ) );

			if ( $post ) {
				wp_send_json_success( array( 'url' => get_permalink( $post->ID ), 'title' => get_the_title( $post->ID ) ) );
			}
		}

		wp_send_json_error( 'Missing required data.' );
	}

}
CMB2_Related_Links::get_instance();

function cmb2_related_links_field( $args = array(), $l10n_strings = array() ) {
	return CMB2_Related_Links::get_instance()->field( $args, $l10n_strings );
}
