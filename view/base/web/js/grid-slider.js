/*
* @Author: Alex Dong
* @Date:   2020-07-29 13:21:07
* @Last Modified by:   nguyen
* @Last Modified time: 2020-08-18 20:28:33
*/

define([
    'jquery',
    'slick',
    'jquery-ui-modules/widget'
    ], function ($, slick) {
		"use strict";
        $.widget('magepow.gridSlider', {
            options: {
                selector: '.grid-slider',
            },

            _create: function () {
                this._initSlider();
            },

            _initSlider: function () {
                var options = this.options;
                var self = this;
                self.element.find(options.selector).each(function() {
                    var el = $(this);
		            if($('body').hasClass('rtl')){
		                el.attr('dir', 'rtl');
		                el.data( 'rtl', true );
		                // el.data( 'infinite', false );
		                // el.data( 'slides-to-scroll', -1 );
		            }
		            var options = el.data();
		            if(iClass === undefined){
		                el.children().addClass('alo-item');
		                var iClass = '.alo-item';
		            }
		            var selector = el.selector;
		            var classes = selector + ' '+ iClass;
		            var padding = options.padding;
		            var style = padding ? classes + '{float: left; padding-left: '+padding+'px; padding-right:'+padding+'px} ' + selector + '{margin-left: -'+padding+'px; margin-right: -'+padding+'px}' : '';
		            $('head').append('<style type="text/css">'+style+'</style>');
		            if(options.slidesToShow){
		                var lazy  = el.find('img.lazyload');
		                if(lazy.length){
		                    lazy.each(function(index) {
		                        $(this).data('lazy', $(this).data('src'));
		                        // $(this).attr('src', $(this).data('src')); // fixed data-adaptive-height="true"
		                    });
		                }
		                el.on('init', function(event, slick){
		                    var video = $(this).find('.external-video');
		                    video.click(function(event) {
		                        var $this = $(this);
		                        var img = $this.find('img');
		                        event.preventDefault();
		                        var url = $(this).data('video');
		                        url = url.replace("://vimeo.com/", "://player.vimeo.com/video/");
		                        url = url.replace("://www.youtube.com/watch?v=", "://youtube.com/embed/");
		                        url = url + '?autoplay=1&badge=0';
		                        var iframe = '<iframe class="iframe-video" src="' + url + '" width="' + img.width() + '" height="' + img.height()  + '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'; 
		                        $this.append(iframe).addClass('embed');
		                        img.hide();
		                    });
		                });

		                var slider = el.slick(options);
		                el.on('beforeChange', function(event, slick, currentSlide, nextSlide){
		                    var video = $(this).find('.external-video');
		                    video.removeClass('embed').find('img').show();
		                    video.find('.iframe-video').remove();
		                    // var slides = $(slick.$slides);
		                    // slides.removeClass('abc-slick-active');
		                    // var nxt_slider = $(slick.$slides.get(nextSlide));
		                    // if(nxt_slider.length) nxt_slider.addClass('abc-slick-active');
		                });
		                slider.on( "click", ".item", function() {
		                    el.slick('slickSetOption', "autoplay",false,false);
		                }); 
		            }
                });
            }
        });

    return $.magepow.gridSlider;

});
