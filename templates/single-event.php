<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) : the_post();

	$event_date = get_post_meta( get_the_ID(), '_emp_event_date', true );
	$location   = get_post_meta( get_the_ID(), '_emp_location', true );
	$types      = get_the_terms( get_the_ID(), 'event_type' );
	$rsvp_nonce = wp_create_nonce( 'emp_rsvp' );

	$badge_date = $nice_date = '';
	if ( $event_date && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $event_date ) ) {
		$ts = strtotime( $event_date );
		if ( $ts ) {
			$badge_date = date_i18n( 'd M', $ts );
			$nice_date  = date_i18n( get_option( 'date_format' ), $ts );
		}
	}

	$type_names  = ( $types && ! is_wp_error( $types ) ) ? wp_list_pluck( $types, 'name' ) : [];
	$archive_url = get_post_type_archive_link( 'event' );

?>
<main class="emp-wrap emp-single" id="main">

	<div class="emp-single-layout">

		<div class="emp-single-main">

			<nav class="emp-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'event-manager-pro' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'event-manager-pro' ); ?></a>
				<span aria-hidden="true">/</span>
				<a href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'Events', 'event-manager-pro' ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page"><?php the_title(); ?></span>
			</nav>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'emp-single-article' ); ?>>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="emp-single-hero-img">
						<?php the_post_thumbnail( 'full', [ 'loading' => 'eager', 'decoding' => 'async' ] ); ?>
						<?php if ( $badge_date ) : ?>
							<span class="emp-date-badge" aria-hidden="true"><?php echo esc_html( $badge_date ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<header class="emp-single-header">

					<?php if ( $type_names ) : ?>
						<div class="emp-meta emp-meta--top">
							<?php foreach ( $type_names as $name ) : ?>
								<span class="emp-pill emp-pill--type"><?php echo esc_html( $name ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<h1 class="emp-title-single"><?php the_title(); ?></h1>

					<div class="emp-single-facts">
						<?php if ( $nice_date ) : ?>
							<div class="emp-fact">
								<span class="emp-fact-icon" aria-hidden="true">
									<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="1.5" y="3" width="15" height="13.5" rx="2" stroke="currentColor" stroke-width="1.4" fill="none"/><path d="M1.5 7.5h15M6 1.5v3M12 1.5v3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
								</span>
								<div class="emp-fact-body">
									<span class="emp-fact-label"><?php esc_html_e( 'Date', 'event-manager-pro' ); ?></span>
									<span class="emp-fact-value"><?php echo esc_html( $nice_date ); ?></span>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $location ) : ?>
							<div class="emp-fact">
								<span class="emp-fact-icon" aria-hidden="true">
									<svg width="16" height="19" viewBox="0 0 16 19" fill="none"><path d="M8 0C4.686 0 2 2.686 2 6c0 5.25 6 13 6 13s6-7.75 6-13c0-3.314-2.686-6-6-6Zm0 8.5A2.5 2.5 0 1 1 8 3.5a2.5 2.5 0 0 1 0 5Z" fill="currentColor"/></svg>
								</span>
								<div class="emp-fact-body">
									<span class="emp-fact-label"><?php esc_html_e( 'Location', 'event-manager-pro' ); ?></span>
									<span class="emp-fact-value"><?php echo esc_html( $location ); ?></span>
								</div>
							</div>
						<?php endif; ?>
					</div>

				</header>

				<div class="emp-content emp-prose">
					<?php the_content(); ?>
				</div>

				<footer class="emp-single-footer">
					<a class="emp-btn emp-btn--ghost" href="<?php echo esc_url( $archive_url ); ?>">
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M9 2L4 7l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<?php esc_html_e( 'All Events', 'event-manager-pro' ); ?>
					</a>
				</footer>

			</article>

		</div>

		<aside class="emp-single-sidebar" aria-label="<?php esc_attr_e( 'Event registration', 'event-manager-pro' ); ?>">
			<div class="emp-rsvp">
				<div class="emp-rsvp-eyebrow"><?php esc_html_e( 'Join this event', 'event-manager-pro' ); ?></div>
				<h2 class="emp-rsvp-title"><?php esc_html_e( 'Register for this event', 'event-manager-pro' ); ?></h2>

				<?php if ( $nice_date || $location ) : ?>
					<div class="emp-rsvp-summary">
						<?php if ( $nice_date ) : ?>
							<span><?php echo esc_html( $nice_date ); ?></span>
						<?php endif; ?>
						<?php if ( $nice_date && $location ) : ?>
							<span aria-hidden="true">·</span>
						<?php endif; ?>
						<?php if ( $location ) : ?>
							<span><?php echo esc_html( $location ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<form class="emp-rsvp-form" method="post" action="#" novalidate>
					<input type="hidden" name="event_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
					<input type="hidden" name="nonce"    value="<?php echo esc_attr( $rsvp_nonce ); ?>">

					<div class="emp-rsvp-field">
						<label class="emp-rsvp-label" for="emp-rsvp-name">
							<?php esc_html_e( 'Name', 'event-manager-pro' ); ?>
						</label>
						<input class="emp-control" type="text" id="emp-rsvp-name" name="name" required
						       placeholder="<?php esc_attr_e( 'Your full name', 'event-manager-pro' ); ?>">
					</div>

					<div class="emp-rsvp-field">
						<label class="emp-rsvp-label" for="emp-rsvp-email">
							<?php esc_html_e( 'Email', 'event-manager-pro' ); ?>
						</label>
						<input class="emp-control" type="email" id="emp-rsvp-email" name="email" required
						       placeholder="<?php esc_attr_e( 'your@email.com', 'event-manager-pro' ); ?>">
					</div>

					<button class="emp-btn emp-btn--primary emp-btn--full" type="submit">
						<?php esc_html_e( 'Register', 'event-manager-pro' ); ?>
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M2 7h10M7.5 3l4.5 4-4.5 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>

					<div class="emp-rsvp-msg" aria-live="polite"></div>
				</form>
			</div>
		</aside>

	</div>

</main>
<?php endwhile;
get_footer();