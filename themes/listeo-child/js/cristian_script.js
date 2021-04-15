/*
Theme Name: Listeo-child
Theme URI: http://hypley.com
Author: Cristian
Author URI: http://hypley.com
Description: Directory WordPress Theme by Purethemes
Version: 6.0.1
License: ThemeForest
License URI: http://themeforest.net/licenses
Text Domain: listeo_child
Domain Path: /languages/
Tags:  theme-options, translation-ready, two-columns
*/


(function($){
    // var temp_add_string='<li>'+
    //                         '<a href="https://hypley.com/add-listing/">'+
    //                             '<i class="sl sl-icon-plus"></i>Add Listing'+
    //                         '</a>'+
    //                     '</li>';
    var temp_add_string_1='';

    if ($(window).width() <1399) {
        temp_add_string_1='<li style="border-top: 1px solid #ddd;margin-top: 10px;padding-top: 10px;">'+
                            '<a href="https://hypley.com">Categories</a>'+
                        '</li>'+
                        '<li>'+
                            '<a href="https://hypley.com/messages/">Inbox</a>'+
                        '</li>'+
                        '<li>'+
                            '<a href="https://hypley.com">App coming soon!</a>'+
                        '</li>'+
                        '<li>'+
                            '<a href="https://hypley.com.au/join-today/">Partner with Hypley</a>'+
                        '</li>'+
                        '<li>'+
                            '<a href="https://hypley.zendesk.com/hc/en-us">Help Centre</a>'+
                        '</li>';
    }
    if ($(window).width() <600) {
        $("#listeo_core-search-form div.row:nth-child(2) div.panel-wrapper").append("<button type='button' class='cristian_more_filter'>Filters</button>");
        $("body").prepend("<div class='cristian_more_filter_modal'>"+
                                "<div class='modal_container'>"+
                                    "<div class='modal_head'>More Filters<button class='modal_close'></button></div>"+
                                    "<div class='modal_info'></div>"+
                                    "<div class='modal_body'><div class='modal_footer'><button class='modal_apply'>Apply</button></div></div>"+
                            "</div>");
        
        
    }
    
        //move sort by filter to the top
        $(".sort-by").appendTo("#listeo_core-search-form div.row:nth-child(2) div.panel-wrapper");
    	$(".sort-by a.chosen-single span").text("Sort By");

    // $(document).ready(function(){
        // $(".right-side .user-menu ul").prepend(temp_add_string);
        $(".right-side .user-menu ul").append(temp_add_string_1);
        $('input[name="cristian_date_picker"]').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            // minYear: parseInt(moment().format('YYYY'),10),
            minDate: moment().format('MM/DD/YYYY'),
            maxYear: parseInt(moment().format('YYYY'),10)+1
          }, function(start, end, label) {
            // var years = moment().diff(start, 'years');
            // alert("You are " + years + " years old!");
        });
        $('input[name="cristian_date_picker"]').val('');
 	$('input[name="cristian_date_picker"]').attr("placeholder","When?");
    
    // });
    
    $(".cristian_more_filter_modal .modal_close").click(function(){
        close_cristian_modal();
    });
    $(".cristian_more_filter_modal .modal_apply").click(function(){
        close_cristian_modal();
    });
    $(".cristian_more_filter").click(function(){
    	$('#listeo_core-search-form').prependTo('.cristian_more_filter_modal .modal_body');
        $('body').css("overflow","hidden");
        $(".cristian_more_filter_modal").css({'display':'block','top':window.pageYOffset});
        $('#tax-listing_category-panel').addClass("active");
        $('#tax-listing_feature-panel').addClass("active");
        $('#_price-panel').addClass("active");
        $('.chosen-container').trigger("click");
    });

    $(document).ready(function(){
    	var url_string = location.href;
        var url = new URL(url_string);
        //console.log(url_string);
        var c=url.searchParams.get("open");
        //console.log(c);
        if(c=="signInDialog"){
        	$("a.sign-in.popup-with-zoom-anim").trigger("click");
        }

        $("#_price_min, .pricing-price input").attr({
            "oninput" : "this.value = Math.abs(this.value)",
            "min" : 1          // values (or variables) here
        });

        //in add listing from if have to filled required field, then go to them
        var pos=$(".listing-manager-error p").text().indexOf("is a required field");
        // console.log($(".listing-manager-error p").text().substring(0,pos-1));
        if(pos>0){
            if($(".listing-manager-error p").text().indexOf("Pricing")>=0)
                setTimeout(function() { $("div.add-listing-section.menu").attr("tabindex",-1).focus();}, 2000);
            else if($(".listing-manager-error p").text().indexOf("Gallery")>=0)
                setTimeout(function() { $("div.add-listing-section.gallery").attr("tabindex",-1).focus();}, 2000);
        }
        
        $("body").on("click", function (event) 
        {
            if($(event.target).is('.not_found_modal')) {
                event.preventDefault();
                $(".not_found_modal").css("display","none");
            }     
        });

        if (window.history && history.pushState && $("form#listing_preview").length) {
            addEventListener('load', function() {
                history.pushState(null, null, null); // creates new history entry with same URL
                addEventListener('popstate', function() {
                    var leavePage = confirm("If you go back, your previous settings would be unsaved. Do you still want to leave this page?");
                    if (leavePage) {
                        history.back() 
                    } else {
                        history.pushState(null, null, null);
                    }
                });    
            });
        }


    });

    function close_cristian_modal(){
        $(".cristian_more_filter_modal").css('display','none');
        $('body').css("overflow","unset");
        $('#listeo_core-search-form').appendTo('section.search .row .col-md-12');
    }
           
	
	
})(jQuery);
 