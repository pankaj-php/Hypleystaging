<?php
/**
 * listeo Theme Customizer
 *
 * @package listeo
 */

/**
 * Add the theme configuration
 */
listeo_Kirki::add_config( 'listeo', array(
    'option_type' => 'option',
    'capability'  => 'edit_theme_options',
) );
    
    
require get_template_directory() . '/inc/customizer/var.php';
require get_template_directory() . '/inc/customizer/home.php';
require get_template_directory() . '/inc/customizer/blog.php';
require get_template_directory() . '/inc/customizer/header.php';
require get_template_directory() . '/inc/customizer/typography.php';
require get_template_directory() . '/inc/customizer/footer.php';
require get_template_directory() . '/inc/customizer/general.php';
require get_template_directory() . '/inc/customizer/listings.php';


/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function listeo_customize_register( $wp_customize ) {
    $wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
    $wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
    $wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
}
add_action( 'customize_register', 'listeo_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function listeo_customize_preview_js() {
    wp_enqueue_script( 'listeo_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20151215', true );
}
add_action( 'customize_preview_init', 'listeo_customize_preview_js' );


/**
 * Add color styling from theme
 */
function listeo_custom_styles() {
    $maincolor = get_option('pp_main_color','#f30c0c' ); 
    $maincolor_rgb = implode(",",sscanf($maincolor, "#%02x%02x%02x"));
    
    $video_color = get_option('listeo_video_search_color','rgba(22,22,22,0.4)');
    $custom_css = "
input[type='checkbox'].switch_1:checked,
.time-slot input:checked ~ label:hover,
div.datedropper:before,
div.datedropper .pick-submit,
div.datedropper .pick-lg-b .pick-sl:before,
div.datedropper .pick-m,
body.no-map-marker-icon .face.front,
body.no-map-marker-icon .face.front:after,
div.datedropper .pick-lg-h {
  background-color: {$maincolor} !important;
}
#booking-date-range-enabler:after,
.nav-links div a:hover, #posts-nav li a:hover,
.hosted-by-title a:hover,

.claim-badge a i,
.search-input-icon:hover i,
.listing-features.checkboxes a:hover,
div.datedropper .pick-y.pick-jump,
div.datedropper .pick li span,
div.datedropper .pick-lg-b .pick-wke,
div.datedropper .pick-btn {
  color: {$maincolor} !important;
}

.comment-by-listing a:hover,
.browse-all-user-listings a i,
.hosted-by-title h4 a:hover,
.style-2 .trigger.active a,
.style-2 .ui-accordion .ui-accordion-header-active:hover,
.style-2 .ui-accordion .ui-accordion-header-active,
#posts-nav li a:hover,
.plan.featured .listing-badge,
.post-content h3 a:hover,
.add-review-photos i,
.show-more-button i,
.listing-details-sidebar li a,
.star-rating .rating-counter a:hover,
.more-search-options-trigger:after,
.header-widget .sign-in:hover,
#footer a,
#footer .footer-links li a:hover,
#navigation.style-1 .current,
#navigation.style-1 ul li:hover a,
.user-menu.active .user-name:after,
.user-menu:hover .user-name:after,
.user-menu.active .user-name,
.user-menu:hover .user-name,
.main-search-input-item.location a:hover,
.chosen-container .chosen-results li.highlighted,
.input-with-icon.location a i:hover,
.sort-by .chosen-container-single .chosen-single div:after,
.sort-by .chosen-container-single .chosen-default,
.panel-dropdown a:after,
.post-content a.read-more,
.post-meta li a:hover,
.widget-text h5 a:hover,
.about-author a,
button.button.border.white:hover,
a.button.border.white:hover,
.icon-box-2 i,
button.button.border,
a.button.border,
.style-2 .ui-accordion .ui-accordion-header:hover,
.style-2 .trigger a:hover ,
.plan.featured .listing-badges .featured,
.list-4 li:before,
.list-3 li:before,
.list-2 li:before,
.list-1 li:before,
.info-box h4,
.testimonial-carousel .slick-slide.slick-active .testimonial:before,
.sign-in-form .tabs-nav li a:hover,
.sign-in-form .tabs-nav li.active a,
.lost_password:hover a,
#top-bar .social-icons li a:hover i,
.listing-share .social-icons li a:hover i,
.agent .social-icons li a:hover i,
#footer .social-icons li a:hover i,
.headline span i,
vc_tta.vc_tta-style-tabs-style-1 .vc_tta-tab.vc_active a,.vc_tta.vc_tta-style-tabs-style-2 .vc_tta-tab.vc_active a,.tabs-nav li.active a,.wc-tabs li.active a.custom-caption,#backtotop a,.trigger.active a,.post-categories li a,.vc_tta.vc_tta-style-tabs-style-3.vc_general .vc_tta-tab a:hover,.vc_tta.vc_tta-style-tabs-style-3.vc_general .vc_tta-tab.vc_active a,.wc-tabs li a:hover,.tabs-nav li a:hover,.tabs-nav li.active a,.wc-tabs li a:hover,.wc-tabs li.active a,.testimonial-author h4,.widget-button:hover,.widget-text h5 a:hover,a,a.button.border,a.button.border.white:hover,button.button.border,button.button.border.white:hover,.wpb-js-composer .vc_tta.vc_general.vc_tta-style-tabs-style-1 .vc_tta-tab.vc_active>a,.wpb-js-composer .vc_tta.vc_general.vc_tta-style-tabs-style-2 .vc_tta-tab.vc_active>a,
#add_payment_method .cart-collaterals .cart_totals tr th,
.woocommerce-cart .cart-collaterals .cart_totals tr th, 
.woocommerce-checkout .cart-collaterals .cart_totals tr th,
#add_payment_method table.cart th, 
.woocommerce-cart table.cart th, 
.woocommerce-checkout table.cart th,
.woocommerce-checkout table.shop_table th,
.uploadButton .uploadButton-button:before,
.time-slot input ~ label:hover,
.time-slot label:hover span,
.booking-loading-icon {
    color: {$maincolor};
}

.qtyTotal,
.mm-menu em.mm-counter,
.category-small-box:hover,
.option-set li a.selected,
.pricing-list-container h4:after,
#backtotop a,
.chosen-container-multi .chosen-choices li.search-choice,
.select-options li:hover,
button.panel-apply,
.layout-switcher a:hover,
.listing-features.checkboxes li:before,
.comment-by a.comment-reply-link:hover,
.add-review-photos:hover,
.office-address h3:after,
.post-img:before,
button.button,
.booking-confirmation-page a.button.color,
input[type=\"button\"],
input[type=\"submit\"],
a.button,
a.button.border:hover,
button.button.border:hover,
table.basic-table th,
.plan.featured .plan-price,
mark.color,
.style-4 .tabs-nav li.active a,
.style-5 .tabs-nav li.active a,
.dashboard-list-box .button.gray:hover,
.change-photo-btn:hover,
.dashboard-list-box  a.rate-review:hover,
input:checked + .slider,
.add-pricing-submenu.button:hover,
.add-pricing-list-item.button:hover,
.custom-zoom-in:hover,
.custom-zoom-out:hover,
#geoLocation:hover,
#streetView:hover,
#scrollEnabling:hover,
#scrollEnabling.enabled,
#mapnav-buttons a:hover,
#sign-in-dialog .mfp-close:hover,
#small-dialog .mfp-close:hover,
.daterangepicker td.end-date.in-range.available,
.radio input[type='radio'] + label .radio-label:after,
.radio input[type='radio']:checked + label .radio-label,
.daterangepicker .ranges li.active, .day-slot-headline, .add-slot-btn button:hover, .daterangepicker td.available:hover, .daterangepicker th.available:hover, .time-slot input:checked ~ label, .daterangepicker td.active, .daterangepicker td.active:hover, .daterangepicker .drp-buttons button.applyBtn,.uploadButton .uploadButton-button:hover {
    background-color: {$maincolor};
}


.rangeslider__fill,
span.blog-item-tag ,
.testimonial-carousel .slick-slide.slick-active .testimonial-box,
.listing-item-container.list-layout span.tag,
.tip,
.search .panel-dropdown.active a,
#getDirection:hover,
.loader-ajax-container,
.mfp-arrow:hover {
    background: {$maincolor};
}

.radio input[type='radio']:checked + label .radio-label,
.rangeslider__handle { border-color: {$maincolor}; }

.layout-switcher a.active {
    color: {$maincolor};
    border-color: {$maincolor};
}

#titlebar.listing-titlebar span.listing-tag a,
#titlebar.listing-titlebar span.listing-tag {
    border-color: {$maincolor};
    color: {$maincolor};
}

.services-counter,
.listing-slider .slick-next:hover,
.listing-slider .slick-prev:hover {
    background-color: {$maincolor};
}


.listing-nav-container.cloned .listing-nav li:first-child a.active,
.listing-nav-container.cloned .listing-nav li:first-child a:hover,
.listing-nav li:first-child a,
.listing-nav li a.active,
.listing-nav li a:hover {
    border-color: {$maincolor};
    color: {$maincolor};
}

.pricing-list-container h4 {
    color: {$maincolor};
    border-color: {$maincolor};
}

.sidebar-textbox ul.contact-details li a { color: {$maincolor}; }

button.button.border,
a.button.border {
    color: {$maincolor};
    border-color: {$maincolor};
}

.trigger.active a,
.ui-accordion .ui-accordion-header-active:hover,
.ui-accordion .ui-accordion-header-active {
    background-color: {$maincolor};
    border-color: {$maincolor};
}

.numbered.color ol > li::before {
    border-color: {$maincolor};;
    color: {$maincolor};
}

.numbered.color.filled ol > li::before {
    border-color: {$maincolor};
    background-color: {$maincolor};
}

.info-box {
    border-top: 2px solid {$maincolor};
    background: linear-gradient(to bottom, rgba(255,255,255,0.98), rgba(255,255,255,0.95));
    background-color: {$maincolor};
    color: {$maincolor};
}

.info-box.no-border {
    background: linear-gradient(to bottom, rgba(255,255,255,0.96), rgba(255,255,255,0.93));
    background-color: {$maincolor};
}

.tabs-nav li a:hover { border-color: {$maincolor}; }
.tabs-nav li a:hover,
.tabs-nav li.active a {
    border-color: {$maincolor};
    color: {$maincolor};
}

.style-3 .tabs-nav li a:hover,
.style-3 .tabs-nav li.active a {
    border-color: {$maincolor};
    background-color: {$maincolor};
}
.woocommerce-cart .woocommerce table.shop_table th,
.vc_tta.vc_general.vc_tta-style-style-1 .vc_active .vc_tta-panel-heading,
.wpb-js-composer .vc_tta.vc_general.vc_tta-style-tabs-style-2 .vc_tta-tab.vc_active>a,
.wpb-js-composer .vc_tta.vc_general.vc_tta-style-tabs-style-2 .vc_tta-tab:hover>a,
.wpb-js-composer .vc_tta.vc_general.vc_tta-style-tabs-style-1 .vc_tta-tab.vc_active>a,
.wpb-js-composer .vc_tta.vc_general.vc_tta-style-tabs-style-1 .vc_tta-tab:hover>a{    
    border-bottom-color: {$maincolor}
}

.checkboxes input[type=checkbox]:checked + label:before {
    background-color: {$maincolor};
    border-color: {$maincolor};
}

.listing-item-container.compact .listing-item-content span.tag { background-color: {$maincolor}; }

.dashboard-nav ul li.active,
.dashboard-nav ul li:hover { border-color: {$maincolor}; }

.dashboard-list-box .comment-by-listing a:hover { color: {$maincolor}; }

.opening-day:hover h5 { color: {$maincolor} !important; }

.map-box h4 a:hover { color: {$maincolor}; }
.infoBox-close:hover {
    background-color: {$maincolor};
    -webkit-text-stroke: 1px {$maincolor};
}

body .select2-container--default .select2-results__option--highlighted[aria-selected], body .select2-container--default .select2-results__option--highlighted[data-selected],
body .woocommerce .cart .button, 
body .woocommerce .cart input.button,
body .woocommerce #respond input#submit, 
body .woocommerce a.button, 
body .woocommerce button.button, 
body .woocommerce input.button,
body .woocommerce #respond input#submit.alt:hover, 
body .woocommerce a.button.alt:hover, 
body .woocommerce button.button.alt:hover, 
body .woocommerce input.button.alt:hover,
.marker-cluster-small div, .marker-cluster-medium div, .marker-cluster-large div,
.cluster-visible {
    background-color: {$maincolor} !important;
}

.marker-cluster div:before {
    border: 7px solid {$maincolor};
    opacity: 0.2;
    box-shadow: inset 0 0 0 4px {$maincolor};
}

.cluster-visible:before {
    border: 7px solid {$maincolor};
    box-shadow: inset 0 0 0 4px {$maincolor};
}

.marker-arrow {
    border-color: {$maincolor} transparent transparent;
}

.face.front {
    border-color: {$maincolor};
    color: {$maincolor};
}

.face.back {
    background: {$maincolor};
    border-color: {$maincolor};
}

.custom-zoom-in:hover:before,
.custom-zoom-out:hover:before  { -webkit-text-stroke: 1px {$maincolor};  }

.category-box-btn:hover {
    background-color: {$maincolor};
    border-color: {$maincolor};
}

.message-bubble.me .message-text {
    color: {$maincolor};
    background-color: rgba({$maincolor_rgb},0.05);
}


.time-slot input ~ label:hover {
    background-color: rgba({$maincolor_rgb},0.08);   
}

.message-bubble.me .message-text:before {
    color: rgba({$maincolor_rgb},0.05);
}
.booking-widget i, .opening-hours i, .message-vendor i {
    color: {$maincolor};
}
.opening-hours.summary li:hover,
.opening-hours.summary li.total-costs span { color: {$maincolor}; }
.payment-tab-trigger > input:checked ~ label::before { border-color: {$maincolor}; }
.payment-tab-trigger > input:checked ~ label::after { background-color: {$maincolor}; }
#navigation.style-1 > ul > li.current-menu-ancestor > a,
#navigation.style-1 > ul > li.current-menu-item > a,
#navigation.style-1 > ul > li:hover > a { 
    background: rgba({$maincolor_rgb}, 0.06);
    color: {$maincolor};
}

.img-box:hover span {  background-color: {$maincolor}; }

body #navigation.style-1 ul ul li:hover a:after,
body #navigation.style-1 ul li:hover ul li:hover a,
body #navigation.style-1 ul li:hover ul li:hover li:hover a,
body #navigation.style-1 ul li:hover ul li:hover li:hover li:hover a,
body #navigation.style-1 ul ul li:hover ul li a:hover { color: {$maincolor}; }

.headline.headline-box span:before {
    background: {$maincolor};
}

.main-search-inner .highlighted-category {
    background-color:{$maincolor};
    box-shadow: 0 2px 8px rgba({$maincolor_rgb}, 0.2);
}

.category-box:hover .category-box-content span {
    background-color: {$maincolor};
}

.user-menu ul li a:hover {
    color: {$maincolor};
}

.icon-box-2 i {
    background-color: {$maincolor};
}

@keyframes iconBoxAnim {
    0%,100% {
        box-shadow: 0 0 0 9px rgba({$maincolor_rgb}, 0.08);
    }
    50% {
        box-shadow: 0 0 0 15px rgba({$maincolor_rgb}, 0.08);
    }
}
.listing-type:hover {
box-shadow: 0 3px 12px rgba(0,0,0,0.1);
background-color: {$maincolor};
}
.listing-type:hover .listing-type-icon {
color: {$maincolor};
}

.listing-type-icon {
background-color: {$maincolor};
box-shadow: 0 0 0 8px rgb({$maincolor_rgb}, 0.1);
}

#footer ul.menu li a:hover {
    color: {$maincolor};
}

#booking-date-range span::after, .time-slot label:hover span, .daterangepicker td.in-range, .time-slot input ~ label:hover, .booking-estimated-cost span, .time-slot label:hover span {
    color: {$maincolor};
}

.daterangepicker td.in-range {
    background-color: rgba({$maincolor_rgb}, 0.05);
    color: {$maincolor};
}

.transparent-header #header:not(.cloned) #navigation.style-1 > ul > li.current-menu-ancestor > a, 
.transparent-header #header:not(.cloned) #navigation.style-1 > ul > li.current-menu-item > a, 
.transparent-header #header:not(.cloned) #navigation.style-1 > ul > li:hover > a {
    background: {$maincolor};
}

.transparent-header #header:not(.cloned) .header-widget .button:hover,
.transparent-header #header:not(.cloned) .header-widget .button.border:hover {
    background: {$maincolor};
}

.transparent-header.user_not_logged_in #header:not(.cloned) .header-widget .sign-in:hover {
    background: {$maincolor};
}


.category-small-box i {
    color: {$maincolor};
}

.account-type input.account-type-radio:checked ~ label {
    background-color: {$maincolor};
}

.category-small-box:hover {
    box-shadow: 0 3px 12px rgba({$maincolor_rgb}, 0.22);
}


.transparent-header.user_not_logged_in #header.cloned .header-widget .sign-in:hover,
.user_not_logged_in .header-widget .sign-in:hover {
    background: {$maincolor};
}
.nav-links div.nav-next a:hover:before,
.nav-links div.nav-previous a:hover:before,
#posts-nav li.next-post a:hover:before,
#posts-nav li.prev-post a:hover:before { background: {$maincolor}; }

.slick-current .testimonial-author h4 span {
   background: rgba({$maincolor_rgb}, 0.06);
   color: {$maincolor};
}

body .icon-box-2 i {
   background-color: rgba({$maincolor_rgb}, 0.07);
   color: {$maincolor};
}

.headline.headline-box:after,
.headline.headline-box span:after {
background: {$maincolor};
}
.listing-item-content span.tag {
   background: {$maincolor};
}

.message-vendor div.wpcf7 .ajax-loader,
body .message-vendor input[type='submit'],
body .message-vendor input[type='submit']:focus,
body .message-vendor input[type='submit']:active {
  background-color: {$maincolor};
}   

.message-vendor .wpcf7-form .wpcf7-radio input[type=radio]:checked + span:before {
   border-color: {$maincolor};
}

.message-vendor .wpcf7-form .wpcf7-radio input[type=radio]:checked + span:after {
   background: {$maincolor};
}
#show-map-button,
.slider-selection {
background-color:{$maincolor};
}

.slider-handle {
border-color:{$maincolor};
}
.bookable-services .single-service:hover h5,
.bookable-services .single-service:hover .single-service-price {
    color: {$maincolor};
}
 
.bookable-services .single-service:hover .single-service-price {
    background-color: rgba({$maincolor_rgb}, 0.08);
    color: {$maincolor};
}
 
 
.bookable-services input[type='checkbox'] + label:hover {
    background-color: rgba({$maincolor_rgb}, 0.08);
    color: {$maincolor};
}
.services-counter,
.bookable-services input[type='checkbox']:checked + label {
    background-color: {$maincolor};
}
.bookable-services input[type='checkbox']:checked + label .single-service-price {
    color: {$maincolor};
}
";

if(get_option('listeo_home_banner_text_align')=='center'){
    $custom_css .= '.main-search-inner {
                    text-align: center;
                    }';
}
$opacity = get_option('listeo_search_bg_opacity',0.8);
$correct_opactity = str_replace(',', '.', $opacity);
$homecolor = get_option('listeo_search_color','#333333');
$homecolor_rgb = implode(",",sscanf($homecolor, "#%02x%02x%02x"));

$custom_css .= "
.solid-bg-home-banner .main-search-container:before,
body.transparent-header .main-search-container:before {
background: rgba({$homecolor_rgb},{$correct_opactity}) ;
}


.loader-ajax-container {
   box-shadow: 0 0 20px rgba( {$maincolor_rgb}, 0.4);
}
";


$header_menu_margin_top = get_option('header_menu_margin_top',0);
$header_menu_margin_bottom = get_option('header_menu_margin_bottom',0);
$custom_css.="
@media (min-width: 1240px) { #header:not(.sticky) ul.menu, #header:not(.sticky) .header-widget { margin-top: {$header_menu_margin_top}px; margin-bottom: {$header_menu_margin_bottom}px; } }
";

if(get_option('listeo_disable_reviews')){ 
    $custom_css .= ' .infoBox .listing-title { display: none; }';
}

$radius_scale = get_option('listeo_radius_unit');
$custom_css.="
.range-output:after {
    content: '$radius_scale';
}";

$ordering = get_option( 'pp_shop_ordering' ); 
if($ordering == 'hide') {
     $custom_css .= '.woocommerce-ordering { display: none; }
    .woocommerce-result-count { display: none; }';
}

wp_add_inline_style( 'listeo-style', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'listeo_custom_styles' );

function listeo_hex2RGB($hex) 
{
        preg_match("/^#{0,1}([0-9a-f]{1,6})$/i",$hex,$match);
        if(!isset($match[1]))
        {
            return false;
        }

        if(strlen($match[1]) == 6)
        {
            list($r, $g, $b) = array($hex[0].$hex[1],$hex[2].$hex[3],$hex[4].$hex[5]);
        }
        elseif(strlen($match[1]) == 3)
        {
            list($r, $g, $b) = array($hex[0].$hex[0],$hex[1].$hex[1],$hex[2].$hex[2]);
        }
        else if(strlen($match[1]) == 2)
        {
            list($r, $g, $b) = array($hex[0].$hex[1],$hex[0].$hex[1],$hex[0].$hex[1]);
        }
        else if(strlen($match[1]) == 1)
        {
            list($r, $g, $b) = array($hex.$hex,$hex.$hex,$hex.$hex);
        }
        else
        {
            return false;
        }

        $color = array();
        $color['r'] = hexdec($r);
        $color['g'] = hexdec($g);
        $color['b'] = hexdec($b);

        return $color;
}