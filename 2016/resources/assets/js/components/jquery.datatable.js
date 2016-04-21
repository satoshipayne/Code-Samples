/**
 * datatable.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'datatable',
		defaults = {
			// Expecting: dataset, datasetKey, row, modifyRow
		};

	// The actual plugin constructor
	// Expected element: <div class="component-datatable">
	function Plugin(element, options) {
		this.element = element;

		// merge options
		this.options = $.extend({}, defaults, options);

		this._defaults = defaults;
		this._name = pluginName;

		this.init();
	}

	Plugin.prototype = {

		// Place initialization logic in here
		init: function() {
			var plugin = this;
			var $element = $(this.element);

			// orderable/searchable
			$(this.element).find('thead th').each(function() {
				var $header = $(this);
				var $cell = $header.find('.cell');
				var $orderable  = $header.find('input[name^=orderable]');
				var $searchable = $header.find(':input[name^=searchable]');

				// insert order triggers
				if($orderable.length) {
					var name = $orderable.attr('name');
					$('<a class="order icon-caret-up" href="" data-direction="asc" data-variable="' + name + '"></a>').appendTo($cell);
					$('<a class="order icon-caret-down" href="" data-direction="desc" data-variable="' + name + '"></a>').appendTo($cell);
				}

				// insert search triggers
				if($searchable.length) {
					$('<a class="search button toggle"><span class="icon icon-search"></span></a>').appendTo($cell);
				}
			});

			// table header row: toggle searchable field
			$(this.element).on('click', 'thead th .button.search', function(e) {
				e.preventDefault();
				$(this).parents('th').toggleClass('searchable-visible');
				$(this).parents('th').find(':input:visible').focus();
			});

			// table header row: hide searchable field
			$(this.element).on('blur', 'thead th :input', function(e) {
				e.preventDefault();
				$(this).parents('th').removeClass('searchable-visible');
			});

			// table header row: order
			$(this.element).on('click', 'thead th .order', function(e) {
				e.preventDefault();

				// reset other orderables
				$element.find('input[name^=orderable]').val('');

				var direction = $(this).data('direction');
				$(this).parents('th').find('input[name^=orderable]').val(direction);
				plugin.search(); // execute the actual search
			});

			// table header row for searchable columns
			// any <th> that has a "searchable" class, will then locate input field that will be used to search in data table
			$(this.element).on('input', 'thead th :input', function() {

				// Throttle search to only search after user stops typing
				var waitTime = 200;
				if($element.data('autocomplete') !== null) {
					clearTimeout($element.data('autocomplete'));
				}
				// not fetching data
				if(!$element.hasClass('fetching-data')) {
					var t = setTimeout(function() {
						plugin.search(); // execute the actual search
						$element.data('autocomplete', null);
					}, waitTime);
					$element.data('autocomplete', t);
				}
				// instant search
				else if(instant != undefined && instant == true) {
					plugin.search(); // execute the actual search
					$element.data('autocomplete', null);
				}
			});

			// pagination
			$(this.element).on('click', '.pagination a', function(e) {
				e.preventDefault();
				var page = $(this).attr('href').replace('?', '').replace('page=', '');
				plugin.search({ page: page }); // execute the actual search
			});

			// populate table
			if(this.options.dataset != undefined) {
				plugin.populate(this.options.dataset.data);
			}

			// generate pagination
			if(this.options.dataset != undefined) {
				plugin.paginate(this.options.dataset);
			}
		},

		populate: function(data) {
			var plugin = this;
			var $element = $(this.element);

			// empty first
			plugin.empty();

			// get all rows
			var rows = [];
			for(var i in data) {
				var item = data[i];
				rows.push(this.options.row(item));
			}

			// build elements
			var $body = $element.find('tbody');
			for(var i in rows) {
				var row = rows[i];
				var item = data[i];
				var $row = $('<tr></tr>').appendTo($body);
				for(var j in row) {
					var cell = row[j];
					var $cell = $('<td></td>').appendTo($row);
					$cell.html(cell);
				}

				// modify row if callback is defined
				if(this.options.modifyRow != undefined) {
					var modifiers = this.options.modifyRow(item);
					if(modifiers) {
						for(var m in modifiers) {
							var attr  = m;
							var value = modifiers[m];
							$row.attr(attr, value);
						}
					}
				}
			}
		},

		empty: function() {
			var plugin = this;
			var $element = $(this.element);

			// get tbody
			var $body = $element.find('tbody');
			$body.empty();
		},

		search: function(params) {
			var plugin = this;
			var $element = $(this.element);

			if(params == undefined) {
				params = {};
			}

			// get fields for table header
			params = $.extend(params, $element.find('thead').parse(function(input) {
				return $(input).val() !== '';
			}));

			// get url, this will do for now
			var url = window.location.href;

			$.ajax(url, {
				data: params,
				success: function(json) {
					var dataset = json[plugin.options.datasetKey];
					plugin.populate(dataset.data);
					if(dataset != undefined) {
						plugin.populate(dataset.data);
						plugin.paginate(dataset);
					} else {
						plugin.empty();
					}
				}
			});
		},

		paginate: function(paginated) {
			var plugin = this;
			var $element = $(this.element);

			var range = 4;
			var step = 3;
			var first = Math.max(paginated.current_page - range, 1);
			var curr  = paginated.current_page;
			var last  = Math.min(paginated.current_page + range, paginated.last_page);

			var i;

			$element.find('.pagination').remove();
			var $pagination = $('<ul class="pagination">').appendTo($element);

			// prev
			if(curr != first) {
				i = Math.max(curr - step, 1);
				$('<a href="?page=' + i + '">' + '‹' + '</a>').appendTo($pagination);
			}

			// numbers
			for(i = first; i <= last; i++) {
				var $page = $('<a href="?page=' + i + '">' + i + '</a>').appendTo($pagination);
				$page.toggleClass('current', i == curr);
			}

			// next
			if(curr != last) {
				i = Math.min(curr + step, paginated.last_page);
				$('<a href="?page=' + i + '">' + '›' + '</a>').appendTo($pagination);
			}
		}
	};

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function(options) {
		return this.each(function() {
			if(!$.data(this, 'plugin_' + pluginName)) {
				$.data(this, 'plugin_' + pluginName, new Plugin(this, options));
			}
		});
	};

})(jQuery, window, document);