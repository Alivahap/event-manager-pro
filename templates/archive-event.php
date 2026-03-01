<?php
defined( 'ABSPATH' ) || exit;

get_header();

$selected_type = isset( $_GET['event_type'] ) ? sanitize_text_field( wp_unslash( $_GET['event_type'] ) ) : '';
$from          = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
$to            = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
$search = isset( $_GET['emp_s'] ) ? sanitize_text_field( wp_unslash( $_GET['emp_s'] ) ) : '';

if ( $from && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) ) $from = '';
if ( $to   && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) )   $to   = '';

$paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : max( 1, get_query_var( 'paged' ) );

$cache_key   = EMP_Cache::key( 'archive', [ 'locale' => determine_locale(), 'type' => $selected_type, 'from' => $from, 'to' => $to, 's' => $search, 'paged' => $paged ] );
$cached_html = EMP_Cache::get( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	get_footer();
	return;
}

ob_start();

$query_args = [
	'post_type'      => 'event',
	'post_status'    => 'publish',
	'posts_per_page' => 10,
	'paged'          => $paged,
	'meta_key'       => '_emp_event_date',
	'orderby'        => 'meta_value',
	'order'          => 'ASC',
];

if ( $from || $to ) {
	$query_args['meta_query'] = [ [
		'key'     => '_emp_event_date',
		'compare' => 'BETWEEN',
		'type'    => 'CHAR',
		'value'   => [ $from ?: '0000-00-00', $to ?: '9999-12-31' ],
	] ];
}

if ( $selected_type ) {
	$query_args['tax_query'] = [ [
		'taxonomy' => 'event_type',
		'field'    => 'slug',
		'terms'    => [ $selected_type ],
	] ];
}

if ( $search ) $query_args['s'] = $search;

$query        = new WP_Query( $query_args );
$active_count = (int) (bool) $selected_type + (int) (bool) $from + (int) (bool) $to + (int) (bool) $search;
$archive_url  = get_post_type_archive_link( 'event' );
$type_terms   = get_terms( [ 'taxonomy' => 'event_type', 'hide_empty' => false ] );
$title        = post_type_archive_title( '', false ) ?: wp_strip_all_tags( get_the_archive_title() );
?>
<main class="emp-wrap emp-archive" id="main">

	<header class="emp-archive-header emp-hero">
		<div class="emp-hero-inner">
			<h1 class="emp-title"><?php echo esc_html( $title ); ?></h1>
			<p class="emp-subtitle"><?php esc_html_e( 'Upcoming and published events', 'event-manager-pro' ); ?></p>
		</div>
	</header>

	<form class="emp-filters emp-filters--pro" method="get" action="<?php echo esc_url( $archive_url ); ?>">
		<div class="emp-filter-grid">

			<label class="emp-field">
				<span class="emp-label"><?php esc_html_e( 'Type', 'event-manager-pro' ); ?></span>
				<select name="event_type" class="emp-control">
					<option value=""><?php esc_html_e( 'All Types', 'event-manager-pro' ); ?></option>
					<?php foreach ( (array) $type_terms as $term ) :
						if ( is_wp_error( $term ) ) continue; ?>
						<option value="<?php echo esc_attr( $term->slug ); ?>"<?php selected( $selected_type, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>

			<label class="emp-field">
				<span class="emp-label"><?php esc_html_e( 'From', 'event-manager-pro' ); ?></span>
				<input class="emp-control" type="date" name="from" value="<?php echo esc_attr( $from ); ?>">
			</label>

			<label class="emp-field">
				<span class="emp-label"><?php esc_html_e( 'To', 'event-manager-pro' ); ?></span>
				<input class="emp-control" type="date" name="to" value="<?php echo esc_attr( $to ); ?>">
			</label>

			<label class="emp-field">
				<span class="emp-label"><?php esc_html_e( 'Search', 'event-manager-pro' ); ?></span>
				<input class="emp-control" type="search" name="emp_s"
				      
				       value="<?php echo esc_attr( $search ); ?>">
			</label>

			<div class="emp-field emp-field-actions">
				<button class="emp-btn emp-btn--primary emp-btn--filter" type="submit">
					<?php esc_html_e( 'Search', 'event-manager-pro' ); ?>
					<?php if ( $active_count ) : ?><span class="emp-badge"><?php echo absint( $active_count ); ?></span><?php endif; ?>
				</button>
				<?php if ( $active_count ) : ?>
					<a class="emp-btn emp-btn--ghost emp-btn--filter" href="<?php echo esc_url( $archive_url ); ?>">
						<?php esc_html_e( 'Reset', 'event-manager-pro' ); ?>
					</a>
				<?php endif; ?>
			</div>

		</div>
	</form>

	<?php if ( $query->have_posts() ) : ?>

		<div class="emp-grid">
		<?php while ( $query->have_posts() ) : $query->the_post();

			$event_date  = get_post_meta( get_the_ID(), '_emp_event_date', true );
			$location    = get_post_meta( get_the_ID(), '_emp_location', true );
			$card_terms  = get_the_terms( get_the_ID(), 'event_type' );
			$type_label  = ( $card_terms && ! is_wp_error( $card_terms ) ) ? $card_terms[0]->name : '';
			$badge_date  = '';
			$nice_date   = '';

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
						the_post_thumbnail( 'large', [ 'loading' => 'lazy', 'decoding' => 'async' ] );
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

					<h2 class="emp-card-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>

					<div class="emp-excerpt">
						<?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?>
					</div>

					<a class="emp-btn emp-btn--primary emp-btn--sm" href="<?php the_permalink(); ?>">
						<?php esc_html_e( 'View Event', 'event-manager-pro' ); ?>
						<svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M2 6h8M6.5 3l3.5 3-3.5 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>

				</div>
			</article>
		<?php endwhile;
		wp_reset_postdata(); ?>
		</div>

		<nav class="emp-pagination" aria-label="<?php esc_attr_e( 'Events pagination', 'event-manager-pro' ); ?>">
			<?php echo paginate_links( [
				'total'     => $query->max_num_pages,
				'current'   => $paged,
				'prev_text' => '&lsaquo;',
				'next_text' => '&rsaquo;',
			] ); ?>
		</nav>

	<?php else : ?>

		<div class="emp-empty">
			<svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true"><circle cx="20" cy="20" r="18" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 3"/><path d="M14 20h12M20 14v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
			<p><?php esc_html_e( 'No events found.', 'event-manager-pro' ); ?></p>
			<?php if ( $active_count ) : ?>
				<a class="emp-btn emp-btn--ghost" href="<?php echo esc_url( $archive_url ); ?>">
					<?php esc_html_e( 'Clear filters', 'event-manager-pro' ); ?>
				</a>
			<?php endif; ?>
		</div>

	<?php endif; ?>

</main>
<?php
EMP_Cache::set( $cache_key, ob_get_flush(), 300 );
get_footer();