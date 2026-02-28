<?php
if ( ! defined('ABSPATH') ) { exit; }
get_header();




$selected_type = isset($_GET['event_type']) ? sanitize_text_field(wp_unslash($_GET['event_type'])) : '';
$from          = isset($_GET['from']) ? sanitize_text_field(wp_unslash($_GET['from'])) : '';
$to            = isset($_GET['to']) ? sanitize_text_field(wp_unslash($_GET['to'])) : '';
$search        = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

// Basic date validate (YYYY-MM-DD)
if ($from !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = '';
if ($to   !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = '';

$paged = isset($_GET['paged'])
    ? max(1, intval($_GET['paged']))
    : max(1, get_query_var('paged'));



    $cache_key = EMP_Cache::key('archive', [
      'locale' => determine_locale(), // or get_locale()
      'type'   => $selected_type,
      'from'   => $from,
      'to'     => $to,
      's'      => $search,
      'paged'  => $paged,
  ]);

$cached_html = EMP_Cache::get($cache_key);
// cache control 
if ($cached_html !== false) {
    echo '<!-- CACHE HIT -->';
    echo $cached_html;
    get_footer();
	
    return;
}

echo '<!-- CACHE MISS -->';

ob_start();


$meta_query = [];
if ($from !== '' || $to !== '') {
    $range = ['key' => '_emp_event_date', 'compare' => 'BETWEEN', 'type' => 'CHAR', 'value' => ['', '']];
    if ($from === '') $from = '0000-00-00';
    if ($to   === '') $to   = '9999-12-31';
    $range['value'] = [$from, $to];
    $meta_query[] = $range;
}

$tax_query = [];
if ($selected_type !== '') {
    $tax_query[] = [
        'taxonomy' => 'event_type',
        'field'    => 'slug',
        'terms'    => [$selected_type],
    ];
}

$args = [
    'post_type'      => 'event',
    'post_status'    => 'publish',
    'posts_per_page' => 10,
    'paged'          => $paged,
    'meta_key'       => '_emp_event_date',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
];

if (!empty($meta_query)) $args['meta_query'] = $meta_query;
if (!empty($tax_query))  $args['tax_query']  = $tax_query;


if ($search !== '') {
    $args['s'] = $search;
}

$query = new WP_Query($args);

?>
<main class="emp-wrap emp-archive">
   <header class="emp-archive-header emp-hero">
  <div class="emp-hero-inner">
    <h1 class="emp-title">
      <?php
 
      $title = post_type_archive_title('', false);

 
      if (!$title) {
          $title = wp_strip_all_tags(get_the_archive_title());
      }

      echo esc_html($title);
      ?>
    </h1>

    <p class="emp-subtitle"><?php echo esc_html__('Upcoming and published events', 'event-manager-pro'); ?></p>
  </div>
</header>
<form class="emp-filters emp-filters--pro"
      method="get"
      action="<?php echo esc_url(get_post_type_archive_link('event')); ?>">

  <div class="emp-filter-grid">

    <!-- TYPE -->
    <label class="emp-field">
      <span class="emp-label"><?php _e('Type','event-manager-pro'); ?></span>
      <select name="event_type" class="emp-control">
        <option value=""><?php _e('All Types','event-manager-pro'); ?></option>

        <?php
        $terms = get_terms([
          'taxonomy'=>'event_type',
          'hide_empty'=>false
        ]);

        foreach($terms as $t){
          printf(
            '<option value="%s" %s>%s</option>',
            esc_attr($t->slug),
            selected($selected_type,$t->slug,false),
            esc_html($t->name)
          );
        }
        ?>
      </select>
    </label>

    <!-- FROM -->
    <label class="emp-field">
      <span class="emp-label"><?php _e('From','event-manager-pro'); ?></span>
      <input class="emp-control" type="date"
             name="from"
             value="<?php echo esc_attr($from); ?>">
    </label>

    <!-- TO -->
    <label class="emp-field">
      <span class="emp-label"><?php _e('To','event-manager-pro'); ?></span>
      <input class="emp-control" type="date"
             name="to"
             value="<?php echo esc_attr($to); ?>">
    </label>

    <!-- SEARCH -->
    <label class="emp-field">
      <span class="emp-label"><?php _e('Search','event-manager-pro'); ?></span>
      <input class="emp-control"
             type="search"
             name="s"
             value="<?php echo esc_attr($search); ?>"
            >
    </label>

    <!-- BUTTON AREA -->
    <div class="emp-field emp-field-actions">

      <button class="emp-btn emp-btn--primary emp-btn--filter"
              type="submit">
              <?php echo esc_html__('Search', 'event-manager-pro'); ?>
      </button>

      <?php if ($selected_type || $from || $to || $search) : ?>
        <a class="emp-btn emp-btn--ghost"
           href="<?php echo esc_url(get_post_type_archive_link('event')); ?>">
           <?php echo esc_html__('Reset', 'event-manager-pro'); ?>
        </a>
      <?php endif; ?>

    </div>

  </div>
</form>

    <?php if ( $query->have_posts() ) : ?>
        <div class="emp-grid">
            <?php while ( $query->have_posts() ) : $query->the_post();
  $event_date = get_post_meta(get_the_ID(), '_emp_event_date', true);
  $location   = get_post_meta(get_the_ID(), '_emp_location', true);

  // Nice date format (if valid)
  $nice_date = $event_date;
  if ($event_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
      $ts = strtotime($event_date);
      if ($ts) {
          $nice_date = date_i18n(get_option('date_format'), $ts);
      }
  }

  // Event type term (first one)
  $type_label = '';
  $type_terms = get_the_terms(get_the_ID(), 'event_type');
  if (!empty($type_terms) && !is_wp_error($type_terms)) {
      $type_label = $type_terms[0]->name;
  }
?>
 <article class="emp-card">
  <a class="emp-card-media" href="<?php the_permalink(); ?>">
    <?php if (has_post_thumbnail()) : ?>
      <?php the_post_thumbnail('large'); ?>
    <?php else : ?>
      <span class="emp-card-media--placeholder"></span>
    <?php endif; ?>

    <?php
      $event_date = get_post_meta(get_the_ID(), '_emp_event_date', true);
      $nice_date  = $event_date;

      if ($event_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
          $ts = strtotime($event_date);
          if ($ts) $nice_date = date_i18n('d M', $ts); // ör: 12 Mar
      }
    ?>
    <?php if (!empty($nice_date)) : ?>
      <span class="emp-date-badge"><?php echo esc_html($nice_date); ?></span>
    <?php endif; ?>
  </a>

  <div class="emp-card-body">
    <h2 class="emp-card-title">
      <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h2>

    <div class="emp-meta">
      <?php
      $location = get_post_meta(get_the_ID(), '_emp_location', true);
      if ($location) : ?>
        <span class="emp-pill"><?php echo esc_html($location); ?></span>
      <?php endif; ?>
    </div>

    <div class="emp-excerpt">
      <?php
      $excerpt = get_the_excerpt();
      echo esc_html( wp_trim_words($excerpt, 150, '…') );
      ?>
    </div>

    <a class="emp-btn emp-btn--primary" href="<?php the_permalink(); ?>">
      <?php echo esc_html__('View Event', 'event-manager-pro'); ?>
    </a>
  </div>
</article>
<?php endwhile; ?>
        </div>

        <div class="emp-pagination">
            <?php
            echo paginate_links([
                'total'   => $query->max_num_pages,
                'current' => $paged,
            ]);
            ?>
        </div>
    <?php else : ?>
        <p><?php echo esc_html__('No events found.', 'event-manager-pro'); ?></p>
    <?php endif; ?>
</main>
<?php

$html = ob_get_clean();
echo $html;

// 5 minutes cache 
EMP_Cache::set($cache_key, $html, 300);
get_footer();