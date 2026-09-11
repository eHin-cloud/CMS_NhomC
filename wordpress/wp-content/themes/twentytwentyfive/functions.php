<?php
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

if ( ! function_exists( 'twentytwentyfive_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_post_format_setup' );

if ( ! function_exists( 'twentytwentyfive_editor_style' ) ) :
	/**
	 * Enqueues editor-style.css in the editors.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_editor_style() {
		add_editor_style( 'assets/css/editor-style.css' );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_editor_style' );

if ( ! function_exists( 'twentytwentyfive_enqueue_styles' ) ) :
	/**
	 * Enqueues the theme stylesheet on the front.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_styles() {
		$suffix = SCRIPT_DEBUG ? '' : '.min';
		$src    = 'style' . $suffix . '.css';

		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( $src ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
		wp_style_add_data(
			'twentytwentyfive-style',
			'path',
			get_parent_theme_file_path( $src )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

if ( ! function_exists( 'twentytwentyfive_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfive' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_block_styles' );

if ( ! function_exists( 'twentytwentyfive_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_pattern_categories() {

		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'twentytwentyfive' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfive' ),
			)
		);

		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'twentytwentyfive' ),
				'description' => __( 'A collection of post format patterns.', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_pattern_categories' );

if ( ! function_exists( 'twentytwentyfive_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_register_block_bindings() {
		register_block_bindings_source(
			'twentytwentyfive/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'twentytwentyfive' ),
				'get_value_callback' => 'twentytwentyfive_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_register_block_bindings' );

if ( ! function_exists( 'twentytwentyfive_enqueue_custom_styles' ) ) :
	/**
	 * Enqueues custom CSS for the horizontal post list layout.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_custom_styles() {
		wp_enqueue_style(
			'twentytwentyfive-custom-post-list',
			get_template_directory_uri() . '/assets/css/custom-post-list.css',
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_custom_styles' );

if ( ! function_exists( 'twentytwentyfive_tin_tuc_ngang_shortcode' ) ) :
	/**
	 * Shortcode [tin_tuc_ngang] - Renders posts in horizontal list layout.
	 * Usage: [tin_tuc_ngang so_bai="4" danh_muc="tin-tuc"]
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	function twentytwentyfive_tin_tuc_ngang_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'so_bai'    => 4,
				'danh_muc'  => '',
				'offset'    => 0,
			),
			$atts,
			'tin_tuc_ngang'
		);

		$args = array(
			'posts_per_page' => intval( $atts['so_bai'] ),
			'offset'         => intval( $atts['offset'] ),
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( ! empty( $atts['danh_muc'] ) ) {
			$args['category_name'] = sanitize_text_field( $atts['danh_muc'] );
		}

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p class="ttn-no-posts">' . esc_html__( 'Không có bài viết nào.', 'twentytwentyfive' ) . '</p>';
		}

		$months_vi = array(
			1  => 'THÁNG 1',
			2  => 'THÁNG 2',
			3  => 'THÁNG 3',
			4  => 'THÁNG 4',
			5  => 'THÁNG 5',
			6  => 'THÁNG 6',
			7  => 'THÁNG 7',
			8  => 'THÁNG 8',
			9  => 'THÁNG 9',
			10 => 'THÁNG 10',
			11 => 'THÁNG 11',
			12 => 'THÁNG 12',
		);

		ob_start();
		?>
		<div class="ttn-post-list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();

				$post_day   = get_the_date( 'd' );
				$post_month = intval( get_the_date( 'n' ) );
				$post_year  = get_the_date( 'Y' );
				$month_label = isset( $months_vi[ $post_month ] ) ? $months_vi[ $post_month ] : 'THÁNG ' . $post_month;

				$categories = get_the_category();
				$cat_names  = array();
				foreach ( $categories as $cat ) {
					$cat_names[] = '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a>';
				}
				$cat_output = ! empty( $cat_names ) ? implode( ', ', $cat_names ) : '';

				$excerpt = get_the_excerpt();
				if ( empty( $excerpt ) ) {
					$excerpt = wp_trim_words( get_the_content(), 30, '...' );
				}
				?>
				<article class="ttn-post-item">
					<a class="ttn-thumbnail-link" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
						<div class="ttn-thumbnail">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
							<?php else : ?>
								<div class="ttn-thumbnail-placeholder"></div>
							<?php endif; ?>
						</div>
					</a>

					<div class="ttn-date-box">
						<span class="ttn-day"><?php echo esc_html( $post_day ); ?></span>
						<span class="ttn-month"><?php echo esc_html( $month_label ); ?></span>
						<span class="ttn-year"><?php echo esc_html( $post_year ); ?></span>
					</div>

					<div class="ttn-content">
						<h2 class="ttn-title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>
						<?php if ( ! empty( $excerpt ) ) : ?>
							<p class="ttn-excerpt"><?php echo esc_html( $excerpt ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $cat_output ) ) : ?>
							<div class="ttn-categories">
								<span class="ttn-cat-label">Categories</span>
								<span class="ttn-cat-links"><?php echo wp_kses_post( $cat_output ); ?></span>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
		<?php
		return ob_get_clean();
	}
endif;
add_shortcode( 'tin_tuc_ngang', 'twentytwentyfive_tin_tuc_ngang_shortcode' );

if ( ! function_exists( 'twentytwentyfive_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function twentytwentyfive_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;
