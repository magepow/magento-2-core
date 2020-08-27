/*
* @Author: Alex Dong
* @Date:   2020-07-29 13:21:07
* @Last Modified by:   Alex Dong
* @Last Modified time: 2020-08-27 22:40:56
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

			_uniqid: function (a = "", b = false) {
			    const c = Date.now()/1000;
			    let d = c.toString(16).split(".").join("");
			    while(d.length < 14) d += "0";
			    let e = "";
			    if(b){
			        e = ".";
			        e += Math.round(Math.random()*100000000);
			    }
			    return a + d + e;
			},

            _initSlider: function () {
                var options = this.options;
                var self = this;
                self.element.find(options.selector).each(function() {
                    var el = $(this);
                    var selector = 'grid-slider-' + self._uniqid();
                    el.addClass(selector);
                    selector = '.' + selector;
		            if($('body').hasClass('rtl')){
		                el.attr('dir', 'rtl');
		                el.data( 'rtl', true );
		            }
		            var options = el.data();
		            if(iClass === undefined){
		                el.children().addClass('alo-item');
		                var iClass = '.alo-item';
		            }
		            var classes	 = selector + ' '+ iClass;
		            var padding  = options.padding;
		            var float  	 = $('body').hasClass('rtl') ? 'right' : 'left';
		            var style 	 = padding ? classes + '{float: ' + float + '; padding: 0 '+padding+'px; box-sizing: border-box} ' + selector + '{margin: 0 -'+padding+'px}' : '';
		            if(options.slidesToShow){
		                var lazy  = el.find('img.lazyload');
		                if(lazy.length){
		                    lazy.each(function(index) {
		                        $(this).data('lazy', $(this).data('src'));
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
		                });
		                slider.on( "click", ".item", function() {
		                    el.slick('slickSetOption', "autoplay",false,false);
		                }); 
		            } else {
						var responsive 	= options.responsive;
						if(responsive == undefined) return;
						var length = Object.keys(responsive).length;

						$.each( responsive, function( key, value ) {
							var col = 0;
							var maxWith = 0;
							var minWith = 0;
							$.each( value , function(size, num) { minWith = parseInt(size) + 1; col = num;});
							if(key+2<length){
								$.each( responsive[key+1], function( size, num) { maxWith = size; col = num;});
								style += ' @media (min-width: '+minWith+'px) and (max-width: '+maxWith+'px)';
							} else { 
								if(key+2 == length) return; // don't use key = length - 1;
								$.each( responsive[key], function( size, num) { maxWith = size; col = num;});
								style += ' @media (min-width: '+maxWith+'px)';
							}
							style += ' {'+selector + '{margin: 0 -'+padding+'px}'+classes+'{padding: 0 '+padding+'px; box-sizing: border-box; width: '+(Math.floor((10/col) * 100000000000) / 10000000000)+'%} '+classes+':nth-child('+col+'n+1){clear: ' + float + ';}}';
						});		            	
		            }

		           	$('head').append('<style type="text/css">'+style+'</style>');

                });
            }
        });

    return $.magepow.gridSlider;

});
