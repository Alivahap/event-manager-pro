<?php
defined( 'ABSPATH' ) || exit;

class EMP_Shortcodes {

	public static function init() {
		add_shortcode( 'events', [ __CLASS__, 'events_shortcode' ] );
	}

	public static function events_shortcode( $atts ) {
		$atts = shortcode_atts( [
			'type'    => '',
			'limit'   => 10,
			'order'   => 'ASC',
			'columns' => 3,
		], $atts, 'events' );

		$limit   = max( 1, absint( $atts['limit'] ) );
		$columns = max( 1, min( 4, absint( $atts['columns'] ) ) );
		$order   = in_array( strtoupper( $atts['order'] ), [ 'ASC', 'DESC' ], true ) ? strtoupper( $atts['order'] ) : 'ASC';
		$type    = sanitize_text_field( $atts['type'] );

		$args = [
			'post_type'      => 'event',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'meta_key'       => '_emp_event_date',
			'orderby'        => 'meta_value',
			'order'          => $order,
			'no_found_rows'  => true,
		];

		if ( $type !== '' ) {
			$args['tax_query'] = [ [
				'taxonomy' => 'event_type',
				'field'    => 'slug',
				'terms'    => [ $type ],
			] ];
		}

		$q = new WP_Query( $args );

		ob_start();

		if ( ! $q->have_posts() ) {
			echo '<p class="emp-shortcode-empty">' . esc_html__( 'No events found.', 'event-manager-pro' ) . '</p>';
			return ob_get_clean();
		}

		printf( '<div class="emp-grid emp-grid--cols-%d">', $columns );

		while ( $q->have_posts() ) {
			$q->the_post();

			$event_date = get_post_meta( get_the_ID(), '_emp_event_date', true );
			$location   = get_post_meta( get_the_ID(), '_emp_location', true );
			$type_terms = get_the_terms( get_the_ID(), 'event_type' );
			$type_label = ( $type_terms && ! is_wp_error( $type_terms ) ) ? $type_terms[0]->name : '';

			$badge_date = $nice_date = '';
			if ( $event_date && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $event_date ) ) {
				$ts = strtotime( $event_date );
				if ( $ts ) {
					$badge_date = date_i18n( 'd M', $ts );
					$nice_date  = date_i18n( get_option( 'date_format' ), $ts );
				}
			}

			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'emp-card' ); ?>>

				<a class="emp-card-media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
					<?php if ( has_post_thumbnail() ) :
						the_post_thumbnail( 'medium_large', [ 'loading' => 'lazy', 'decoding' => 'async' ] );
					else : ?>
						<span class="emp-card-media--placeholder"></span>
					<?php endif; ?>
					<?php if ( $badge_date ) : ?>
						<span class="emp-date-badge" aria-hidden="true"><?php echo esc_html( $badge_date ); ?></span>
					<?php endif; ?>
				</a>

				<div class="emp-card-body">

					<?php if ( $type_label || $location || $nice_date ) : ?>
						<div class="emp-meta">
							<?php if ( $type_label ) : ?>
								<span class="emp-pill emp-pill--type"><?php echo esc_html( $type_label ); ?></span>
							<?php endif; ?>
							<?php if ( $location ) : ?>
								<span class="emp-pill">
									<svg width="11" height="13" viewBox="0 0 11 13" fill="none" aria-hidden="true"><path d="M5.5 0C3.015 0 1 2.015 1 4.5c0 3.375 4.5 8.5 4.5 8.5S10 7.875 10 4.5C10 2.015 7.985 0 5.5 0Zm0 6.125A1.625 1.625 0 1 1 5.5 2.875a1.625 1.625 0 0 1 0 3.25Z" fill="currentColor"/></svg>
									<?php echo esc_html( $location ); ?>
								</span>
							<?php endif; ?>
							<?php if ( $nice_date ) : ?>
								<span class="emp-pill">
									<svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><rect x="1" y="2" width="10" height="9" rx="1.5" stroke="currentColor" stroke-width="1.2" fill="none"/><path d="M1 5h10M4 1v2M8 1v2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
									<?php echo esc_html( $nice_date ); ?>
								</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<h3 class="emp-card-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>

					<div class="emp-excerpt">
						<?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?>
					</div>

					<a class="emp-btn emp-btn--primary emp-btn--sm" href="<?php the_permalink(); ?>">
						<?php esc_html_e( 'View Event', 'event-manager-pro' ); ?>
						<svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M2 6h8M6.5 3l3.5 3-3.5 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>

				</div>
			</article>
			<?php
		}

		wp_reset_postdata();

		echo '</div>';

		return ob_get_clean();
	}
}