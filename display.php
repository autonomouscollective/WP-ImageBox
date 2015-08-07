<?php
/**
 * Constructs TP image box on single post pages.
 * @return script
 * @author Seth
 */
function wpib_image_box_js() {
	if (is_singular()) {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
	    $('img.wp-photo').each(function() {
			if ($(this).data('credit') != undefined) {
				credit = '<span class="photo-credit">'+$(this).data('credit')+'</span>';
			} else {
				credit = '';
			}
			if ($(this).data('caption') != undefined) {
				caption = '<span class="photo-caption">'+$(this).data('caption')+'</span>';
			} else {
				caption = '';
			}
			alignment = $(this).data('alignment');
			width = $(this).attr('width');
		
			$(this).wrap('<figure class="image-box '+alignment+'"/>');
			$(this).after('<div class="image-meta" style="max-width:'+width+'px;">'+credit+caption+'</div>');
	    });
	});
	</script>
	<?php }
}
add_action('wp_footer','wpib_image_box_js');
