<?php
  /**
  * The template for the homepage (when set to static page)
  *
  */

  get_header(); ?>

<div class="main-container">
  <div class="main-grid">
    <main class="main-content">

      <div class="standings">
        <?php // This will hold the code for grabbing & displaying results from games.crossfit.com! ?>
      </div>

      <div class="analysis">
        <?php
          $args = array('numberposts' => 1);
          $post = get_posts($args)[0];
          echo '<div class="analysis_title">' . $post->post_title . '</div>';
          echo '<div class="analysis__excerpt">' . wp_trim_words($post->post_content, 55, '<a class="analysis__more-link" href="' . $post->guid . '"> &hellip;(read more)</a>') . '</div>';
        ?>
      </div>

      <div class="images">
        <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Front Page Bottom') ) : ?>
        <?php endif;?>
      </div>

    </main>
  </div>
</div>
<?php get_footer();
