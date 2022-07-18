/*
* @Author: Alex Dong
* @Date:   2020-07-29 13:21:07
* @Last Modified by:   Alex Dong
* @Last Modified time: 2022-07-18 18:29:19
*/

define([
    'jquery',
    'slick',
    'jquery-ui-modules/core'
    ], function ($, slick) {
		"use strict";
        $.widget('magepow.gridSlider', {
            options: {
                selector: '.grid-slider',
                useIntersectionObserver: true,
                unobserve: true,
            },

            _create: function () {
            	var options = this.options;
            	this._initSlider();
            },

			_uniqid: function (length=10) {
	            let result       	   = '';
	            const characters 	   = 'abcdefghijklmnopqrstuvwxyz0123456789';
	            const charactersLength = characters.length;
	            for ( let i = 0; i < length; i++ ) {
	            	result += characters.charAt(Math.floor(Math.random() * charactersLength));
	           	}
	           	return result;
			},

            _initSlider: function () {
                var options = this.options;
                var useIntersectionObserver = options.useIntersectionObserver;
                var unobserve = options.unobserve;
                var self = this;
                var $head = $('head');
                var elements = options.selector ? self.element.find(options.selector) : self.element;
                elements.each(function() {
                    var element = $(this);
                    var selector = 'grid-slider-' + self._uniqid();
                    var styleId  = selector;
                    element.addClass(selector);
                    selector = '.' + selector;
		            if($('body').hasClass('rtl')){
		                element.attr('dir', 'rtl');
		                element.data( 'rtl', true );
		            }
		            var options = element.data();
		            if(iClass === undefined){
		                element.children().addClass('alo-item');
		                var iClass = '.alo-item';
		            }
		            var rows 	= ((options || {}).rows === void 0) ? 1 : options.rows;
		            var classes	= rows ? selector + ' '+ iClass : selector + ' > '+ iClass;
		            var padding = options.padding;
		            var float  	= $('body').hasClass('rtl') ? 'right' : 'left';
		            var style 	= (typeof padding !== 'undefined') ? classes + '{float: ' + float + '; padding: 0 '+padding+'px; box-sizing: border-box} ' + selector + '{margin: 0 -'+padding+'px}' : '';
		            $head.append('<style type="text/css" >'+style+'</style>');
		            style 		= '';
		            if(options.slidesToShow){
						if ("IntersectionObserver" in window && useIntersectionObserver) {
							var nthChild = options.slidesToShow + 1;
							style += selector + ' .item:nth-child(n+ ' + nthChild + ')' + '{display: none;} ' + selector +  ' .item{float:left};';
							let gridSliderObserver = new IntersectionObserver(function(entries, observer) {
								entries.forEach(function(entry) {
									if (entry.isIntersecting) {
										let el  = entry.target;
										var $el = $(el);
										$el.on('init', function(){
											$head.find('#' + styleId).remove();
										});
										self.sliderRender($el);
										if(unobserve) gridSliderObserver.unobserve(el);
									}
								});
							});

							element.each(function(index, el){
						    	gridSliderObserver.observe(el);
						    });
						} else {
							self.sliderRender(element);
						}
		            }
		            var responsive 	= self.getPesponsive(options);
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
						style += ' {'+selector + '{margin: 0 -'+padding+'px}'+classes+'{padding: 0 '+padding+'px; box-sizing: border-box; width: calc(100% / ' + col + ')} '+classes+':nth-child('+col+'n+1){clear: ' + float + ';}}';
					});	
		           	$head.append('<style type="text/css" id="' + styleId + '" >'+style+'</style>');

		           	self.element.addClass('grid-init');
		           	
                });
            },

            getPesponsive : function (options) {
            	if(!options.slidesToShow || !options.responsive) return options.responsive;
				var responsive 	= options.responsive;
				var length = Object.keys(responsive).length;
				var gridResponsive = [];
				$.each( responsive, function( key, value ) { 
					var breakpoint = {};
					breakpoint[value.breakpoint] = parseInt(value.settings.slidesToShow);
					gridResponsive.push(breakpoint);
				 });
				return gridResponsive.reverse();
            },

            sliderRender: function (el) {
            	if(el.hasClass('slick-initialized')){
            		el.slick("refresh");
            		return;
            	}
            	var options = el.data();
                var lazy  = el.find('img.lazyload');
                if(lazy.length){
                    lazy.each(function(index) {
                        $(this).data('lazy', $(this).data('src'));
                    });
                }
                el.on('init', function(event, slick){
                	$('body').trigger('contentUpdated'); // support lazyload
                    var video = $(this).find('.external-video');
                    video.on('click', function(event) {
                        var $this = $(this);
                        if($this.hasClass('embed')) return;
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
            }

        });

    return $.magepow.gridSlider;

});
