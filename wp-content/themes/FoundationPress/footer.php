<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "off-canvas-wrap" div and all content after.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */
?>

<footer class="footer">
  <?php if ( current_user_can('administrator') ) : ?>
    <script>
      function updateWOD() {
        const year = document.querySelector('#year').value;
        const wodindex = document.querySelector('#wodindex').value;
        const path = window.location.origin + '/wp-json/tcf-athletes/v1/update-wod/' + year + '/' + wodindex;
        const request = new XMLHttpRequest();
        request.addEventListener('load', function() {
          document.querySelector('#result').innerText = 'WOD updated!';
        })
        request.open('GET', path);
        request.send();
      }
    </script>
    <div class="formlike">
      Enter information for WOD to pull from games site:
      <div>
        <label for="year">year:</label>
        <input type="text" name="year" id="year" required>
      </div>
      <div>
        <label for="wodindex">wodindex:</label>
        <input type="text" name="wodindex" id="wodindex" required>
      </div>
      <div>
        <button onclick="updateWOD()">Update WOD</button>
        <span id="result"></span>
      </div>
    </div>
  <?php endif; ?>
    <div class="footer-container">
        <div class="footer-grid">
            <?php dynamic_sidebar( 'footer-widgets' ); ?>
        </div>
    </div>
</footer>

<?php if ( get_theme_mod( 'wpt_mobile_menu_layout' ) === 'offcanvas' ) : ?>
	</div><!-- Close off-canvas content -->
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
