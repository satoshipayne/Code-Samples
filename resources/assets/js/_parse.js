/**
 * parse.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Function for parsing form objects into JSON
 */
;(function($, window, document, undefined) {

	// Extend
	$.fn.extend({
		parse: function() {

			// helper
			var buildProp = function(obj, props, value) {
				var plugin = this;
				var prop = props.shift();
				if(props.length) {
					if(prop == '') {
						var i = -1;
						for(var prop in obj) {
							if(/^[0-9]+$/g.test(prop) === true) {
								i = Math.max(i, prop);
							}
						}
						obj[++i] = {};
						buildProp(obj[i], props, value)
					} else if(obj[prop]) {
						buildProp(obj[prop], props, value)
					} else {
						obj[prop] = {};
						buildProp(obj[prop], props, value)
					}
				} else {
					obj[prop] = value;
				}
				return obj;
			};

			// method
			return (function(form) {
				var config = {};
				$(form).serializeArray().map(function(item) {
					var props = item.name.replace(/\]\[/g, '~').replace(/\[/g, '~').replace(/\]/g, '').split('~');
					config = buildProp(config, props, item.value);
				});
				return config;
			})(this);
		}
	});

})(jQuery, window, document);