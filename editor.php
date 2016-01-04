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

/**
 * Not ideal but I like the way this works for a UX perspective, I would rather cludginly turn "Alternative Text" into credit than not.
 * @return {script}
 * @author Seth
 */
function wpib_change_media_library_labels() {
    ?>
    <script type="text/javascript">
    /*! jquery.livequery - v1.3.6 - 2013-08-26
     * Copyright (c)
     *  (c) 2010, Brandon Aaron (http://brandonaaron.net)
     *  (c) 2012 - 2013, Alexander Zaytsev (http://hazzik.ru/en)
     * Dual licensed under the MIT (MIT_LICENSE.txt)
     * and GPL Version 2 (GPL_LICENSE.txt) licenses.
     */
    !function(a){"function"==typeof define&&define.amd?define(["jquery"],a):"object"==typeof exports?a(require("jquery")):a(jQuery)}(function(a,b){function c(a,b,c,d){return!(a.selector!=b.selector||a.context!=b.context||c&&c.$lqguid!=b.fn.$lqguid||d&&d.$lqguid!=b.fn2.$lqguid)}a.extend(a.fn,{livequery:function(b,e){var f,g=this;return a.each(d.queries,function(a,d){return c(g,d,b,e)?(f=d)&&!1:void 0}),f=f||new d(g.selector,g.context,b,e),f.stopped=!1,f.run(),g},expire:function(b,e){var f=this;return a.each(d.queries,function(a,g){c(f,g,b,e)&&!f.stopped&&d.stop(g.id)}),f}});var d=a.livequery=function(b,c,e,f){var g=this;return g.selector=b,g.context=c,g.fn=e,g.fn2=f,g.elements=a([]),g.stopped=!1,g.id=d.queries.push(g)-1,e.$lqguid=e.$lqguid||d.guid++,f&&(f.$lqguid=f.$lqguid||d.guid++),g};d.prototype={stop:function(){var b=this;b.stopped||(b.fn2&&b.elements.each(b.fn2),b.elements=a([]),b.stopped=!0)},run:function(){var b=this;if(!b.stopped){var c=b.elements,d=a(b.selector,b.context),e=d.not(c),f=c.not(d);b.elements=d,e.each(b.fn),b.fn2&&f.each(b.fn2)}}},a.extend(d,{guid:0,queries:[],queue:[],running:!1,timeout:null,registered:[],checkQueue:function(){if(d.running&&d.queue.length)for(var a=d.queue.length;a--;)d.queries[d.queue.shift()].run()},pause:function(){d.running=!1},play:function(){d.running=!0,d.run()},registerPlugin:function(){a.each(arguments,function(b,c){if(a.fn[c]&&!(a.inArray(c,d.registered)>0)){var e=a.fn[c];a.fn[c]=function(){var a=e.apply(this,arguments);return d.run(),a},d.registered.push(c)}})},run:function(c){c!==b?a.inArray(c,d.queue)<0&&d.queue.push(c):a.each(d.queries,function(b){a.inArray(b,d.queue)<0&&d.queue.push(b)}),d.timeout&&clearTimeout(d.timeout),d.timeout=setTimeout(d.checkQueue,20)},stop:function(c){c!==b?d.queries[c].stop():a.each(d.queries,d.prototype.stop)}}),d.registerPlugin("append","prepend","after","before","wrap","attr","removeAttr","addClass","removeClass","toggleClass","empty","remove","html","prop","removeProp"),a(function(){d.play()})});

    jQuery(document).ready(function($){
        $('label.setting.alt-text span').livequery(function() {
            jQuery('label.setting.alt-text span').text('Credit');
        });
        $('label[data-setting="alt"] span').livequery(function() {
            jQuery('label[data-setting="alt"] span').text('Credit');
        });

    });
    </script>
    <?php
}
add_action('admin_footer','wpib_change_media_library_labels');
