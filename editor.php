<?php
/**
 * Remove Insert from URL from media uploader
 *
 * per http://wordpress.org/support/topic/removing-insert-from-url-in-35
 */
/**
 * Callback to remove Insert from URL from media uploader
 * @param array $strings
 * @return array
*/
function wpib_remove_insert_from_url($strings) {
    unset($strings["insertFromUrlTitle"]);
    return $strings;
}
add_filter("media_view_strings", "wpib_remove_insert_from_url");

/**
 * [wpib_image_data_attr description]
 * @param {[type]} $html [description]
 * This function looks at the src url and retrieves some data by attachment id from that.
 * The matches are:
 * 0 -- the whole match string
 * 1 -- the anchor link tag, if any
 * 2 -- the url of the anchor link, if any
 * 3 -- the src url
 * 4 -- the alt text
 * 5 -- the width
 * 6 -- the height
 * 7 -- the classes wordpress applies
 * 8 -- the id of the image
 * @author Seth
*/
function wpib_image_data_attr( $html ) {
    // We pull out the pieces we need to process image
	$pattern = '/(<a href="([^"]+)">)?<img src="([^"]+)" alt="([^"]*)" width=\"(\d+)" height="(\d+)" class="([^"]+)wp-image-(\d+)"/';

	$match_count = preg_match($pattern, $html, $matches);
	if($match_count != 1){
		// something went wrong, give up and output default
		return $html;
	}

	$attachment_id = (int)$matches[8];
    $image_post = get_post($attachment_id);
    $classes_match = $matches[7];
    $src = $matches[3];
    $width = $matches[5];
    $height = $matches[6];
	$credit = $matches[4];
    $data_attr_credit = null;
    if ($credit) {
        $data_attr_credit = 'data-credit="'.esc_attr($credit).'"';
    }
    $caption = $image_post->post_excerpt;

    $classes_array = explode(" ", $classes_match);
    $classes = array();
    $alignment = '';
    foreach ($classes_array as $index => $class) {
        if ($index == 0) {
            $alignment = $class;
        } else {
            $classes[] = $class;
        }
    }
    $classes[] = $alignment;
    $classes = implode(" ", $classes);

    $html ='<img class="wp-photo '.esc_attr($classes).' wp-image-'.$attachment_id.'" src="'.$src.'" width="'.$width.'" height="'.$height.'" '.$data_attr_credit.' data-attachid="'.esc_attr($attachment_id).'" data-alignment="'.esc_attr($alignment).'" alt="'.esc_attr($caption).'"/>';
	return $html;
}
add_filter( 'image_send_to_editor', 'wpib_image_data_attr', 10 );

/**
 * Hijack the WP Core 'caption' shortcode and rebuild it adding our photo credit fields.
 * We're also going to go ahead and add data-attr for all these things as well eventually use this in our react app.
 * @param   string $ns An emptry string, not used
 * @param   array $atts The shortcode args
 * @param   string|null $content The content passed to the caption shortcode.
 * @return  string
 * @author  Seth
*/
function wpib_hijack_caption_shortcode($na, $atts, $content) {
    extract(shortcode_atts(array(
        'id'    => '',
        'align' => 'alignnone',
        'width' => '',
        'caption' => ''
    ), $atts));

    // We want to add the caption as a data-attr to the image.
    $caption = str_replace('<img', '<img data-caption="'.$caption.'" alt="'.$caption.'"', $content);

    return $caption;
}
add_filter('img_caption_shortcode', 'wpib_hijack_caption_shortcode', 10, 3);

/**
 * We do not want images without captions to have P tags so we're going to be stripping the P tags.
 * @param  $content = post content well search for img tags.
 * @return filtered content with <p><img changed to <figure><img
 * @author Seth
 */
function wpib_filter_ptags($content){
   return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}

add_filter('the_content', 'wpib_filter_ptags');
