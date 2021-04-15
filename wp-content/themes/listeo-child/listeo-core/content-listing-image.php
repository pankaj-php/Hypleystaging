<?php 	

if(has_post_thumbnail()){ 
	the_post_thumbnail('listeo-listing-grid'); 
} 
else { 	
	$gallery = (array) get_post_meta( $id, '_gallery', true );
	$count_gallery = listeo_count_gallery_items($id);

	if($count_gallery < 2) {
		$ids = array_keys($gallery);
		if(!empty($ids[0]) && $ids[0] !== 0){ 
			$image_url = wp_get_attachment_image_url($ids[0],'listeo-listing-grid'); 
		} else {
			$image_url = get_listeo_core_placeholder_image();
		}
		?>
		<a target="_blank" href="<?php echo esc_url(get_post_permalink($id)); ?>">
			<img class="listeo_liting_single_galary_image listeo_liting_single_image" src="<?php echo esc_attr($image_url); ?>" alt="">
		</a>
	<?php
	}
	else {
		$gallery_img_counter = 0;
		echo '<div class="listeo_cat_page_silder listeo_liting_single_tttt" data-tttt="test">';
		foreach ( (array) $gallery as $attachment_id => $attachment_url ) {
			$gallery_img_counter++;	
			$image = wp_get_attachment_image_src( $attachment_id, 'listeo-gallery' );
			//echo '<a href="'.esc_url($image[0]).'" data-background-image="'.esc_attr($image[0]).'" class="item mfp-gallery"></a>';
			?>
			<a target="_blank" href="<?php echo esc_url(get_post_permalink($id)); ?>">
				<?php echo '<img data-url="'.esc_url(get_post_permalink($id)).'" class="listeo_liting_single_galary_image" src="'.esc_attr($image[0]).'" alt="">'; ?>
			</a>
			<?php
			if($gallery_img_counter == 3)
			{
				break;
			}
		}
		echo '</div>';
	}
}