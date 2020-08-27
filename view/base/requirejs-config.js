var config = {

	map: {
		'*': {
			'slick': 'magepow/slick',
			'gridSlider': 'magepow/grid-slider',
		},
	},

	paths: {
		'magepow/slick'			: 'Magepow_Core/js/plugin/slick.min',
		'magepow/grid-slider'	: 'Magepow_Core/js/grid-slider',
	},

	shim: {
		'magepow/slick': {
			deps: ['jquery']
		}
	}

};
