<?php 	
// die;
if(has_post_thumbnail()){ 
	// the_post_thumbnail('listeo-listing-grid'); 
	global $post;
	$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
	// echo $image[0];
	/* echo '<div class="listeo_cat_page_silder listeo_liting_single_tttt" data-tttt="test">';
		echo '<a target="_blank" href="'.get_permalink( $id ).'"><img data-url="'.esc_url(get_post_permalink($id)).'" class="listeo_liting_single_galary_image" src="'.$image[0].'" alt=""></a>';
		echo '<a target="_blank" href="'.get_permalink( $id ).'"><img data-url="'.esc_url(get_post_permalink($id)).'" class="listeo_liting_single_galary_image" src="'.$image[0].'" alt=""></a>';
		echo '<a target="_blank" href="'.get_permalink( $id ).'"><img data-url="'.esc_url(get_post_permalink($id)).'" class="listeo_liting_single_galary_image" src="'.$image[0].'" alt=""></a>';
	echo '</div>'; */
	
	$gallery = (array) get_post_meta( $id, '_gallery', true );
	$count_gallery = listeo_count_gallery_items($id);
// echo $count_gallery;
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
		// For Featured image Start
		echo '<a target="_blank" href="'.get_permalink( $id ).'"><img data-url="'.esc_url(get_post_permalink($id)).'" class="listeo_liting_single_galary_image" src="'.$image[0].'" alt=""></a>';
		// For Featured image End
		foreach ( (array) $gallery as $attachment_id => $attachment_url ) {
			$gallery_img_counter++;	
			$image = wp_get_attachment_image_src( $attachment_id, 'listeo-gallery' );
			// echo '<a href="'.esc_url($image[0]).'" data-background-image="'.esc_attr($image[0]).'" class="item mfp-gallery"></a>';
			?>
			<a target="_blank" href="<?php echo esc_url(get_post_permalink($id)); ?>">
				<?php echo '<img data-url="'.esc_url(get_post_permalink($id)).'" class="listeo_liting_single_galary_image" src="'.esc_attr($image[0]).'" alt="">'; ?>
			</a>
			<?php
			if($gallery_img_counter == 2)
			{
				break;
			}
		}
		echo '</div>';
	}
}  
else { 	
	$gallery = (array) get_post_meta( $id, '_gallery', true );
	$count_gallery = listeo_count_gallery_items($id);
	if($count_gallery<=1){
		// echo "1";
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
			<a target="_blank" href="<?php echo esc_url(get_post_permalink($id)); ?>">
				<?php echo '<img data-url="'.esc_url(get_post_permalink($id)).'" class="listeo_liting_single_galary_image" src="'.esc_attr($image[0]).'" alt="">'; ?>
			</a>
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
	elseif($count_gallery<2){
		// echo "2";
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
	else{
		// echo "3";
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