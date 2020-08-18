define([
    'jquery',
    'magepow/slick',
    'domReady!'
    ], function($, slick){
        "use strict";

        var ref         = '';
        var username    = 'alotheme';
        var ref_url     = 'https://1.envato.market/c/1314680/275988/4415';
        var api_url     = '//alothemes.com/feed.php';
        var featureditem = $('.magepow_feed');
        if(!featureditem.length) return;
        var style = document.createElement("style");
        style.innerHTML  = ".slick-slider {position: relative; display: block; box-sizing: border-box; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; -webkit-touch-callout: none; -khtml-user-select: none; -ms-touch-action: pan-y; touch-action: pan-y; -webkit-tap-highlight-color: transparent; } .slick-list {position: relative; display: block; overflow: hidden; margin: 0; padding: 0; } .slick-list:focus {outline: none; } .slick-list.dragging {cursor: pointer; cursor: hand; } .slick-slider .slick-track, .slick-slider .slick-list {-webkit-transform: translate3d(0, 0, 0); -moz-transform: translate3d(0, 0, 0); -ms-transform: translate3d(0, 0, 0); -o-transform: translate3d(0, 0, 0); transform: translate3d(0, 0, 0); } .slick-track {position: relative; top: 0; left: 0; display: block; } .slick-track:before, .slick-track:after {display: table; content: ''; } .slick-track:after {clear: both; } .slick-loading .slick-track {visibility: hidden; } .slick-slide {display: none; float: left; height: 100%; min-height: 1px; } [dir='rtl'] .slick-slide {float: right; } .slick-slide img {display: block; } .slick-slide.slick-loading img {display: none; } .slick-slide.dragging img {pointer-events: none; } .slick-initialized .slick-slide {display: block; } .slick-loading .slick-slide {visibility: hidden; } .slick-vertical .slick-slide {display: block; height: auto; border: 1px solid transparent; } .slick-arrow.slick-hidden {display: none; }";
        style.innerHTML += '.slick-dots button {padding:0} .featureditem h2.title{color: #333;font-size:30px;font-family: Arial,sans-serif; font-weight: 700;text-transform: uppercase;} .featureditem p{color: #101010;margin-top: 10px;font-weight: normal; font-size: 18px;} .featureditem{background-color: #f6f6f6; padding: 50px; text-align: center;} .magicslider .item{padding-left: 15px;padding-right: 15px;} .magicslider .item img{width: 100%;} .item-box{position:relative;} .glare{position: absolute; top: 0px;height: 30px;line-height: 28px;} .hot-sale,.trending{background-color: #0084B4;left: 0px;} .new-sale{background-color: #709c36;right: 0px;} .glare span{padding: 0 10px;color: #ffffff;font-size: 17px;} .trending:after {background-color: transparent;border: 15px solid #0084B4;border-right-color: transparent;bottom: 0;content: "";position: absolute;right: -20px;z-index: 9;} .item-box a{margin: 30px 0 0;display: block;outline: none;text-decoration: none !important ;} .item-box a span{font-size: 18px;text-transform: capitalize;font-family: Arial,sans-serif;color: #000;} .item-box a span.name{display:block;} .item-box a span.price{font-size: 25px;font-weight: normal;color: #f36;} .item-box a span.sales{color: #999;} .slick-dots{margin-top: 20px;} .slick-dots li{display: inline-block;margin:0; padding: 0 3px;} .slick-dots li button {border-radius: 90%; background-color: transparent;border: 1px solid #999;color: #666;font-size: 12px;height: 25px;width: 25px;} .slick-dots li.slick-active button,.slick-dots li button:hover {background-color: #ff3366;border-color: #ff3366;color: #ffffff;} .slick-arrow{-moz-transition: all .45s ease;-webkit-transition: all .45s ease;-o-transition: all .45s ease;-ms-transition: all .45s ease;transition: all .45s ease;-webkit-transform: translate(0,-50%);-ms-transform: translate(0,-50%);transform: translate(0,-50%);opacity: 0;visibility: hidden;} .slick-arrow:hover{background-color: #ff3366;border-color: #ff3366;color: #ffffff;} .slick-next{right:-50px;} .slick-prev {left: -50px;} .slick-prev:before, .slick-next:before {font-family: FontAwesome;font-size: 14px;line-height: 22px;} .slick-prev:before {content: "\f104";} .slick-next:before {content: "\f105";} .slick-arrow {border-radius: 0;-moz-border-radius: 0;-webkit-border-radius: 0;-ms-border-radius: 0;-o-border-radius: 0;border-style: solid;border-width: 1px;border-color: #ddd;font-size: 0;line-height: 24px;position: absolute;top: 40%;display: block;width: 24px;height: 24px;padding: 0;margin-top: -10px\9;cursor: pointer;color: #727272;outline: 0;background: #eaeaea;z-index: 1;} .magicslider:hover .slick-next{right: 0;opacity: 1;visibility: inherit;} .magicslider:hover .slick-prev{left: 0;opacity: 1;visibility: inherit;}';
        document.getElementsByTagName('head')[0].appendChild(style);
        var xmlhttp = new XMLHttpRequest();
        //var url = "http://marketplace.envato.com/api/v1/new-files-from-user:alotheme,themeforest.json";
        // var url = "http://marketplace.envato.com/api/edge/new-files-from-user:alotheme,themeforest.json";
        var url = api_url + '?list=' + username;
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var obj = JSON.parse(xmlhttp.responseText);
                var item = obj['new-files-from-user'];
                var html = '<div id="featureditem" class="section featureditem">';
                html    += '<h2 class="section-title title">You may also like!</h2>';
                html    += '<p class="section-des">More powerful &amp; premium themes from us. Please take a look from our items below!</p>';
                html    += '<ul class="magicslider">';
                var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
                var today = new Date();
                var i = item.length - 1;
                var placeholder = 'data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22' + '590' + '%22%20height%3D%22' + '300' + '%22%20viewBox%3D%220%200%20225%20265%22%3E%3C%2Fsvg%3E';
                for(i; i > 0; i--) {
                    var upload = new Date(item[i].uploaded_on);
                    var ago = Math.round(Math.abs((today.getTime() - upload.getTime())/(oneDay)));
                    var sales = item[i].sales;
                    if(sales/ago < 0.15 ) continue;
                    var label = '';
                    if(ago < 90) label += '<span class="glare new-sale"><span>New</span></span>';
                    // if(sales/ago >= 0.5) label += '<span class="glare hot-sale"><span>Hot</span></span>';
                    if(sales/ago >= 0.5) label += '<span class="glare trending"><span>Trending</span></span>';
                    html += '<li class="item">';
                    html += '<div class="item-box">';
                    html += label;
                    html += '<div class="img">';
                    var linkRef = ref_url + '?u=' + item[i].url;
                    html += '<a target="_blank" href="' + linkRef + '"><img alt="' + item[i].item + '" src="' + item[i].live_preview_url + '" title="' + item[i].item + '" /></a>';
                    html += '</div>';
                    html += '<a target="_blank" href="' + linkRef + '"><span class="name">' + item[i].item + '</span><span class="price">$' + parseInt(item[i].cost) + ' </span><span class="sales">Sales ' + item[i].sales + ' </span></a>';
                    html += '</div>';
                    html += '</li>';
                }
                html += '</ul>';
                html += '</div>';
                featureditem.before(html);
                var slidecfg = {"autoplay":true,"arrows":false,"autoplayspeed":1000,"dots":true,"infinite":true,"vertical":false,"verticalSwiping":false,"responsive":[{'breakpoint': 1200, 'settings': {'slidesToShow': 4, 'slidesToScroll': 4}},{'breakpoint': 992, 'settings': {'slidesToShow': 3, 'slidesToScroll': 3}},{'breakpoint': 768, 'settings': {'slidesToShow': 2, 'slidesToScroll': 2}},{'breakpoint': 361, 'settings': {'slidesToShow': 1, 'slidesToScroll': 1}}],"rows":1,"slidesToShow":4, "slidesToScroll": 4, "swipeToSlide":true,"speed":300};
                $('#featureditem .magicslider').slick(slidecfg);
            }
        };
        xmlhttp.open("GET", url, true);
        xmlhttp.send();

});
