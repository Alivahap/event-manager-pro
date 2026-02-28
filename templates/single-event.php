<?php
if ( ! defined('ABSPATH') ) { exit; }
get_header();

while ( have_posts() ) : the_post();
    $event_date = get_post_meta(get_the_ID(), '_emp_event_date', true);
    $location   = get_post_meta(get_the_ID(), '_emp_location', true);
    $types      = get_the_terms(get_the_ID(), 'event_type');
    ?>
    <main class="emp-wrap emp-single">
        <article class="emp-card">
            <header class="emp-header">
                <h1 class="emp-title"><?php the_title(); ?></h1>

                <div class="emp-meta">
                    <?php if ($event_date) : ?>
                        <span class="emp-pill"><?php echo esc_html($event_date); ?></span>
                    <?php endif; ?>

                    <?php if ($location) : ?>
                        <span class="emp-pill"><?php echo esc_html($location); ?></span>
                    <?php endif; ?>

                    <?php if ( ! empty($types) && ! is_wp_error($types) ) : ?>
                        <span class="emp-pill">
                            <?php echo esc_html(implode(', ', wp_list_pluck($types, 'name'))); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </header>

            <div class="emp-content">
                <?php the_content(); ?>
				<?php
// RSVP nonce for CSRF protection
$rsvp_nonce = wp_create_nonce('emp_rsvp');
?>
<section class="emp-rsvp">
    <h2 class="emp-rsvp-title"><?php echo esc_html__('Register for this event', 'event-manager-pro'); ?></h2>

    <form class="emp-rsvp-form" method="post" action="#">
        <input type="hidden" name="event_id" value="<?php echo esc_attr(get_the_ID()); ?>">
        <input type="hidden" name="nonce" value="<?php echo esc_attr($rsvp_nonce); ?>">

        <p>
            <label>
                <?php echo esc_html__('Name', 'event-manager-pro'); ?><br>
                <input type="text" name="name" required>
            </label>
        </p>

        <p>
            <label>
                <?php echo esc_html__('Email', 'event-manager-pro'); ?><br>
                <input type="email" name="email" required>
            </label>
        </p>

        <button class="emp-btn" type="submit"><?php echo esc_html__('Register', 'event-manager-pro'); ?></button>

        <div class="emp-rsvp-msg" aria-live="polite"></div>
    </form>
</section>
            </div>
        </article>
    </main>
    <?php
endwhile;

get_footer();