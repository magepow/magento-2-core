var config = {

	map: {
		'*': {
			'easing': 'magepow/easing',
			'slick': 'magepow/slick',
			'gridSlider': 'magepow/grid-slider',
		},
	},

	paths: {
		'magepow/easing'		: 'Magepow_Core/js/plugin/jquery.easing.min',
		'magepow/slick'			: 'Magepow_Core/js/plugin/slick.min',
		'magepow/grid-slider'	: 'Magepow_Core/js/grid-slider',
	},

	shim: {
		'magepow/easing': {
			deps: ['jquery']
		},
		'magepow/slick': {
			deps: ['jquery']
		}
	}

};
