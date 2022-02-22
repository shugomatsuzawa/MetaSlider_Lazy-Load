<?php
/*
Plugin Name: Meta Slider（FlexSlider）遅延読み込みカスタムファンクション
Description: Meta Slider（FlexSlider）に遅延読み込み機能を追加します。
Version: 1.0
Author: Shugo Matsuzawa
Author URI: https://shugomatsuzawa.com
License: GPL2
*/

/*
Copyright 2022 Shugo Matsuzawa (https://shugomatsuzawa.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// JavaScriptを読み込むコード
function mls_lazy_load_scripts() {
	wp_enqueue_script(
		'lazyload',
		plugin_dir_url( __FILE__ ) . 'node_modules/lazyload/lazyload.min.js',
		array( 'jquery' ),
		'2.0.0-rc.2',
		true
    );
}
add_action( 'wp_enqueue_scripts', 'mls_lazy_load_scripts' );


// MetaSlider デフォルトのスライドショー設定（最初のスライドショーのみ適応）
function metaslider_default_slideshow_properties($params) {
    $params['type'] = 'flex';
    return $params;
}
/*
$params 内容の例
array(37) {
    ["type"]=> string(4) "flex"
    ["random"]=> bool(false)
    ["cssClass"]=> string(0) ""
    ["printCss"]=> bool(true)
    ["printJs"]=> bool(true)
    ["width"]=> int(700)
    ["height"]=> int(300)
    ["spw"]=> int(7)
    ["sph"]=> int(5)
    ["delay"]=> int(3000)
    ["sDelay"]=> int(30)
    ["opacity"]=> float(0.7)
    ["titleSpeed"]=> int(500)
    ["effect"]=> string(6) "random"
    ["navigation"]=> bool(true)
    ["links"]=> bool(true)
    ["hoverPause"]=> bool(true)
    ["theme"]=> string(7) "default"
    ["direction"]=> string(10) "horizontal"
    ["reverse"]=> bool(false)
    ["animationSpeed"]=> int(600)
    ["prevText"]=> string(6) "前へ"
    ["nextText"]=> string(4) "Next"
    ["slices"]=> int(15)
    ["center"]=> bool(false)
    ["smartCrop"]=> bool(true)
    ["carouselMode"]=> bool(false)
    ["carouselMargin"]=> int(5)
    ["firstSlideFadeIn"]=> bool(false)
    ["easing"]=> string(6) "linear"
    ["autoPlay"]=> bool(true)
    ["thumb_width"]=> int(150)
    ["thumb_height"]=> int(100)
    ["responsive_thumbs"]=> bool(true)
    ["thumb_min_width"]=> int(100)
    ["fullWidth"]=> bool(true)
    ["noConflict"]=> bool(true)
}
*/
add_filter('metaslider_default_parameters', 'metaslider_default_slideshow_properties', 10, 1);


// スライドimgタグの属性を編集
function mls_add_class_lazy($attributes, $slide, $slider_id) {
	$attributes['class'] = 'lazy';
    $attributes['loading'] = 'eager';
	$attributes['data-src'] = $slide['src'];
	$attributes['src'] = plugin_dir_url( __FILE__ ) . 'loading.gif';
    $attributes['style'] = 'aspect-ratio: ' . $slide['width'] . ' / ' . $slide['height'] . ';'; // Webkitの挙動を修正 要Experimental Features - CSS Aspect Ratio
	return $attributes;
}
/*
$slide 内容の例
Array (
    [id] => 123
    [url] => 
    [title] => image001
    [target] => _self
    [src] => https://www.example.com/wp-content/uploads/2020/09/image001-600x300.jpg
    [thumb] => https://www.example.com/wp-content/uploads/2020/09/image001-600x300.jpg
    [width] => 600
    [height] => 300
    [alt] => 
    [caption] => &copy; Shugo Matsuzawa
    [caption_raw] => ©︎ Shugo Matsuzawa
    [class] => slider-141 slide-143
    [rel] => 
    [data-thumb] => 
)
*/
add_filter('metaslider_flex_slider_image_attributes', 'mls_add_class_lazy', 10, 3);


function metaslider_flex_params($options, $slider_id, $settings) {
    $options['start'][] = <<<EOF
        jQuery("ul.slides li:first-of-type img.lazy, ul.slides li:first-of-type + li img.lazy").each(function () {
            var src = jQuery(this).attr("data-src");
            jQuery(this).attr("src", src).removeAttr("data-src");
        });
    EOF;
    $options['after'][] = <<<EOF
        jQuery("ul.slides li.flex-active-slide img.lazy, ul.slides li.flex-active-slide + li img.lazy").each(function () {
            var src = jQuery(this).attr("data-src");
            jQuery(this).attr("src", src).removeAttr("data-src");
        });
    EOF;
    return $options;
}
add_filter('metaslider_flex_slider_parameters', 'metaslider_flex_params', 10, 3);


function custom_style() {
echo <<< EOF
/*
<style>
.metaslider-flex img.lazy[src$="loading.gif"]{
    object-fit: scale-down;
}
</style>
*/
EOF;
}
add_action( 'wp_footer', 'custom_style' );

?>