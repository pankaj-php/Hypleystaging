<div id="listing-cancellation_policy" class="listing-section">
	<h3 class="listing-desc-headline margin-bottom-30"><?php esc_html_e('Cancellation policy','listeo_core') ?></h3>
	
	<?php
	      echo get_post_meta( $post->ID, '_cancellation_policy', true);
	?>

</div>
