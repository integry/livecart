/**
 * bootstrap-multiselect.js
 * https://github.com/davidstutz/bootstrap-multiselect
 *
 * Copyright 2012, 2013 David Stutz
 * 
 * Dual licensed under the BSD-3-Clause and the Apache License, Version 2.0.
 */
!function($) {
    
    "use strict";// jshint ;_;

    if (typeof ko != 'undefined' && ko.bindingHandlers && !ko.bindingHandlers.multiselect) {
        ko.bindingHandlers.multiselect = {
            init : function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {},
            update : function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
                var ms = $(element).data('multiselect');
                if (!ms) {
                    $(element).multiselect(ko.utils.unwrapObservable(valueAccessor()));
                }
                else if (allBindingsAccessor().options && allBindingsAccessor().options().length !== ms.originalOptions.length) {
                    ms.updateOriginalOptions();
                    $(element).multiselect('rebuild');
                }
            }
        };
    }

    function Multiselect(select, options) {

        this.options = this.mergeOptions(options);
        this.$select = $(select);
        this.$select.data('instance', this);
        
        // Initialization.
        // We have to clone to create a new reference.
        this.originalOptions = this.$select.clone()[0].options;
        this.query = '';
        this.searchTimeout = null;
        
        this.options.multiple = this.$select.attr('multiple') == "multiple";
        this.options.onChange = $.proxy(this.options.onChange, this);
        
        // Build select all if enabled.
        this.buildContainer();
        this.buildButton();
        this.buildSelectAll();
        this.buildDropdown();
        this.buildDropdownOptions();
        this.buildFilter();
        this.updateButtonText();

        this.$select.hide().after(this.$container);
    };

    Multiselect.prototype = {
        
        // Default options.
        defaults: {
            // Default text function will either print 'None selected' in case no
            // option is selected, or a list of the selected options up to a length of 3 selected options.
            // If more than 3 options are selected, the number of selected options is printed.
            buttonText: function(options, select) {
                if (options.length == 0) {
                    return this.nonSelectedText + ' <b class="caret"></b>';
                }
                else {
                    if (options.length > 3) {
                        return options.length + ' ' + this.nSelectedText + ' <b class="caret"></b>';
                    }
                    else {
                        var selected = '';
                        options.each(function() {
                            var label = ($(this).attr('label') !== undefined) ? $(this).attr('label') : $(this).html();

                            selected += label + ', ';
                        });
                        return selected.substr(0, selected.length - 2) + ' <b class="caret"></b>';
                    }
                }
            },
            // Like the buttonText option to update the title of the button.
            buttonTitle: function(options, select) {
                if (options.length == 0) {
                    return this.nonSelectedText;
                }
                else {
                    var selected = '';
                    options.each(function () {
                        selected += $(this).text() + ', ';
                    });
                    return selected.substr(0, selected.length - 2);
                }
            },
            // Is triggered on change of the selected options.
            onChange : function(option, checked) {

            },
            buttonClass: 'btn',
            dropRight: false,
            selectedClass: 'active',
            buttonWidth: 'auto',
            buttonContainer: '<div class="btn-group" />',
            // Maximum height of the dropdown menu.
            // If maximum height is exceeded a scrollbar will be displayed.
            maxHeight: false,
            includeSelectAllOption: false,
            selectAllText: ' Select all',
            selectAllValue: 'multiselect-all',
            enableFiltering: false,
            enableCaseInsensitiveFiltering: false,
            filterPlaceholder: 'Search',
            // possible options: 'text', 'value', 'both'
            filterBehavior: 'text',
            preventInputChangeEvent: false,
            nonSelectedText: 'None selected',
            nSelectedText: 'selected'
        },
        
        // Templates.
        templates: {
            button: '<button type="button" class="multiselect dropdown-toggle" data-toggle="dropdown"></button>',
            ul: '<ul class="multiselect-container dropdown-menu"></ul>',
            filter: '<div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span><input class="form-control multiselect-search" type="text"></div>',
            li: '<li><a href="javascript:void(0);"><label></label></a></li>',
            liGroup: '<li><label class="multiselect-group"></label></li>'
        },
        
        constructor: Multiselect,
        
        buildContainer: function() {
            this.$container = $(this.options.buttonContainer);
        },
        
        buildButton: function() {
            // Build button.
            this.$button = $(this.templates.button).addClass(this.options.buttonClass);
            
            // Adopt active state.
            if (this.$select.prop('disabled')) {
                this.disable();
            }
            else {
                this.enable();
            }
           
            // Manually add button width if set.
            if (this.options.buttonWidth) {
                this.$button.css({
                    'width' : this.options.buttonWidth
                });
            }

            // Keep the tab index from the select.
            var tabindex = this.$select.attr('tabindex');
            if (tabindex) {
                this.$button.attr('tabindex', tabindex);
            }
           
            this.$container.prepend(this.$button)
        },
        
        // Build dropdown container ul.
        buildDropdown: function() {
            
            // Build ul.
            this.$ul = $(this.templates.ul);
            
            if (this.options.dropRight) {
                this.$ul.addClass('pull-right');
            }
            
            // Set max height of dropdown menu to activate auto scrollbar.
            if (this.options.maxHeight) {
                // TODO: Add a class for this option to move the css declarations.
                this.$ul.css({
                    'max-height': this.options.maxHeight + 'px',
                    'overflow-y': 'auto',
                    'overflow-x': 'hidden'
                });
            }
            
            this.$container.append(this.$ul)
        },
        
        // Build the dropdown and bind event handling.
        buildDropdownOptions: function() {
            
            var self = this;
            this.$select.children().each($.proxy(function(index, element) {
                // Support optgroups and options without a group simultaneously.
                var tag = $(element).prop('tagName').toLowerCase();
                if (tag == 'optgroup') {
                    this.createOptgroup(element);
                }
                else if (tag == 'option') {
                    this.createOptionValue(element);
                }
                // Other illegal tags will be ignored.
            }, this));

            // Bind the change event on the dropdown elements.
            $('li input', this.$ul).on('change', $.proxy(function(event) {
                var checked = $(event.target).prop('checked') || false;
                var isSelectAllOption = $(event.target).val() == this.options.selectAllValue;

                // Apply or unapply the configured selected class.
                if (this.options.selectedClass) {
                    if (checked) {
                        $(event.target).parents('li').addClass(this.options.selectedClass);
                    }
                    else {
                        $(event.target).parents('li').removeClass(this.options.selectedClass);
                    }
                }
                
                // Get the corresponding option.
                var value = $(event.target).val();
                var $option = this.getOptionByValue(value);

                var $optionsNotThis = $('option', this.$select).not($option);
                var $checkboxesNotThis = $('input', this.$container).not($(event.target));

                // Toggle all options if the select all option was changed.
                if (isSelectAllOption) {
                    /*
                    $checkboxesNotThis.filter(function() {
                        return $(this).is(':checked') != checked;
                    }).trigger('click');
                    */

                    var values = [];
                    var lastCb = null;
                    _.each($checkboxesNotThis.filter(function() {
                        return $(this).is(':checked') != checked;
                    }), function(cb)
                    {
                    	//self.select($(cb).val());
                    	//$(cb).change();
                    	values.push($(cb).val());
                    	lastCb = cb;
					});
                    
					self[checked ? 'select' : 'deselect'](values);
					$(lastCb).change();
                }

                if (checked) {
                    $option.prop('selected', true);

                    if (this.options.multiple) {
                        // Simply select additional option.
                        $option.prop('selected', true);
                    }
                    else {
                        // Unselect all other options and corresponding checkboxes.
                        if (this.options.selectedClass) {
                            $($checkboxesNotThis).parents('li').removeClass(this.options.selectedClass);
                        }

                        $($checkboxesNotThis).prop('checked', false);
                        $optionsNotThis.prop('selected', false);

                        // It's a single selection, so close.
                        this.$button.click();
                    }

                    if (this.options.selectedClass == "active") {
                        $optionsNotThis.parents("a").css("outline", "");
                    }
                }
                else {
                    // Unselect option.
                    $option.prop('selected', false);
                }

                this.updateButtonText();
                this.$select.change();
                this.options.onChange($option, checked);
                
                if(this.options.preventInputChangeEvent) {
                    return false;
                }
            }, this));

            $('li a', this.$ul).on('touchstart click', function(event) {
                event.stopPropagation();
                $(event.target).blur();
            });

            // Keyboard support.
            this.$container.on('keydown', $.proxy(function(event) {
                if ($('input[type="text"]', this.$container).is(':focus')) {
                    return;
                }
                if ((event.keyCode == 9 || event.keyCode == 27) && this.$container.hasClass('open')) {
                    // Close on tab or escape.
                    this.$button.click();
                }
                else {
                    var $items = $(this.$container).find("li:not(.divider):visible a");

                    if (!$items.length) {
                        return;
                    }

                    var index = $items.index($items.filter(':focus'));

                    // Navigation up.
                    if (event.keyCode == 38 && index > 0) {
                        index--;
                    }
                    // Navigate down.
                    else if (event.keyCode == 40 && index < $items.length - 1) {
                        index++;
                    }
                    else if (!~index) {
                        index = 0;
                    }

                    var $current = $items.eq(index);
                    $current.focus();

                    if (event.keyCode == 32 || event.keyCode == 13) {
                        var $checkbox = $current.find('input');

                        $checkbox.prop("checked", !$checkbox.prop("checked"));
                        $checkbox.change();
                    }

                    event.stopPropagation();
                    event.preventDefault();
                }
            }, this));
        },
        
        // Will build an dropdown element for the given option.
        createOptionValue: function(element) {
            if ($(element).is(':selected')) {
                $(element).prop('selected', true);
            }

            // Support the label attribute on options.
            var label = $(element).attr('label') || $(element).html();
            var value = $(element).val();
            var inputType = this.options.multiple ? "checkbox" : "radio";

            var $li = $(this.templates.li);
            $('label', $li).addClass(inputType);
            $('label', $li).append('<input type="' + inputType + '" />');

            var selected = $(element).prop('selected') || false;
            var $checkbox = $('input', $li);
            $checkbox.val(value);

            if (value == this.options.selectAllValue) {
                $checkbox.parent().parent().addClass('multiselect-all');
            }

            $('label', $li).append(" " + label);

            this.$ul.append($li);

            if ($(element).is(':disabled')) {
                $checkbox.attr('disabled', 'disabled').prop('disabled', true).parents('li').addClass('disabled');
            }

            $checkbox.prop('checked', selected);

            if (selected && this.options.selectedClass) {
                $checkbox.parents('li').addClass(this.options.selectedClass);
            }
        },

        // Create optgroup.
        createOptgroup: function(group) {
            var groupName = $(group).prop('label');

            // Add a header for the group.
            var $li = $(this.templates.liGroup);
            $('label', $li).text(groupName);
            
            this.$ul.append($li);

            // Add the options of the group.
            $('option', group).each($.proxy(function(index, element) {
                this.createOptionValue(element);
            }, this));
        },
        
        // Add the select all option to the select.
        buildSelectAll: function() {
            var alreadyHasSelectAll = this.$select[0][0] ? this.$select[0][0].value == this.options.selectAllValue : false;
            // If options.includeSelectAllOption === true, add the include all checkbox.
            if (this.options.includeSelectAllOption && this.options.multiple && !alreadyHasSelectAll) {
                this.$select.prepend('<option value="' + this.options.selectAllValue + '">' + this.options.selectAllText + '</option>');
            }
        },
        
        // Build and bind filter.
        buildFilter: function() {
            
            // Build filter if filtering OR case insensitive filtering is enabled and the number of options exceeds (or equals) enableFilterLength.
            if (this.options.enableFiltering || this.options.enableCaseInsensitiveFiltering) {
                var enableFilterLength = Math.max(this.options.enableFiltering, this.options.enableCaseInsensitiveFiltering);
                if (this.$select.find('option').length >= enableFilterLength) {
                    
                    this.$filter = $(this.templates.filter);
                    $('input', this.$filter).attr('placeholder', this.options.filterPlaceholder);
                    this.$ul.prepend(this.$filter);

                    this.$filter.val(this.query).on('click', function(event) {
                        event.stopPropagation();
                    }).on('keydown', $.proxy(function(event) {
                        // This is useful to catch "keydown" events after the browser has updated the control.
                        clearTimeout(this.searchTimeout);

                        this.searchTimeout = this.asyncFunction($.proxy(function() {

                            if (this.query != event.target.value) {
                                this.query = event.target.value;

                                $.each($('li', this.$ul), $.proxy(function(index, element) {
                                    var value = $('input', element).val();
                                    if (value != this.options.selectAllValue) {
                                        var text = $('label', element).text();
                                        var value = $('input', element).val();
                                        if (value && text && value != this.options.selectAllValue) {
                                            // by default lets assume that element is not
                                            // interesting for this search
                                            var showElement = false;

                                            var filterCandidate = '';
                                            if ((this.options.filterBehavior == 'text' || this.options.filterBehavior == 'both')) {
                                                filterCandidate = text;
                                            }
                                            if ((this.options.filterBehavior == 'value' || this.options.filterBehavior == 'both')) {
                                                filterCandidate = value;
                                            }

                                            if (this.options.enableCaseInsensitiveFiltering && filterCandidate.toLowerCase().indexOf(this.query.toLowerCase()) > -1) {
                                                showElement = true;
                                            }
                                            else if (filterCandidate.indexOf(this.query) > -1) {
                                                showElement = true;
                                            }

                                            if (showElement) {
                                                $(element).show();
                                            }
                                            else {
                                                $(element).hide();
                                            }
                                        }
                                    }
                                }, this));
                            }
                        }, this), 300, this);
                    }, this));
                }
            }
        },

        // Destroy - unbind - the plugin.
        destroy: function() {
            this.$container.remove();
            this.$select.show();
        },

        // Refreshs the checked options based on the current state of the select.
        refresh: function() {
            $('option', this.$select).each($.proxy(function(index, element) {
                var $input = $('li input', this.$ul).filter(function() {
                    return $(this).val() == $(element).val();
                });

                if ($(element).is(':selected') || $(element).attr('selected')) {
                    $(element).prop('selected', true);
                    $input.prop('checked', true);

                    if (this.options.selectedClass) {
                        $input.parents('li').addClass(this.options.selectedClass);
                    }
                }
                else {
                    $(element).prop('selected', false);
                    $input.prop('checked', false);

                    if (this.options.selectedClass) {
                        $input.parents('li').removeClass(this.options.selectedClass);
                    }
                }

                if ($(element).is(":disabled")) {
                    $input.attr('disabled', 'disabled').prop('disabled', true).parents('li').addClass('disabled');
                }
                else {
                    $input.prop('disabled', false).parents('li').removeClass('disabled');
                }
            }, this));

            this.updateButtonText();
            this.$select.change();
        },

        // Select an option by its value or multiple options using an array of values.
        select: function(selectValues) {
            if(selectValues && !$.isArray(selectValues)) {
                selectValues = [selectValues];
            }
            
            for (var i = 0; i < selectValues.length; i++) {
                
                var value = selectValues[i];
                
                var $option = this.getOptionByValue(value);
                var $checkbox = this.getInputByValue(value);

                if (this.options.selectedClass) {
                    $checkbox.parents('li').addClass(this.options.selectedClass);
                }

                $checkbox.prop('checked', true);
                $option.prop('selected', true);                
                this.options.onChange($option, true);
            }

            this.updateButtonText();
        },

        // Deselect an option by its value or using an array of values.
        deselect: function(deselectValues) {
            if(deselectValues && !$.isArray(deselectValues)) {
                deselectValues = [deselectValues];
            }

            for (var i = 0; i < deselectValues.length; i++) {
                
                var value = deselectValues[i];
                
                var $option = this.getOptionByValue(value);
                var $checkbox = this.getInputByValue(value);

                if (this.options.selectedClass) {
                    $checkbox.parents('li').removeClass(this.options.selectedClass);
                }

                $checkbox.prop('checked', false);
                $option.prop('selected', false);               
                this.options.onChange($option, false);
            }

            this.updateButtonText();
        },

        // Rebuild the whole dropdown menu.
        rebuild: function() {
            this.$ul.html('');
            
            // Remove select all option in select.
            $('option[value="' + this.options.selectAllValue + '"]', this.$select).remove();
            
            // Important to distinguish between radios and checkboxes.
            this.options.multiple = this.$select.attr('multiple') == "multiple";
            
            this.buildSelectAll();
            this.buildDropdownOptions();
            this.updateButtonText();
            this.buildFilter();
        },
        
        // Build select using the given data as options.
        dataprovider: function(dataprovider) {
            var optionDOM = "";
            dataprovider.forEach(function (option) {
                optionDOM += '<option value="' + option.value + '">' + option.label + '</option>';
            });

            this.$select.html(optionDOM);
            this.rebuild();
        },

        // Enable button.
        enable: function() {
            this.$select.prop('disabled', false);
            this.$button.prop('disabled', false)
                .removeClass('disabled');
        },

        // Disable button.
        disable: function() {
            this.$select.prop('disabled', true);
            this.$button.prop('disabled', true)
                .addClass('disabled');
        },

        // Set options.
        setOptions: function(options) {
            this.options = this.mergeOptions(options);
        },

        // Get options by merging defaults and given options.
        mergeOptions: function(options) {
            return $.extend({}, this.defaults, options);
        },
        
        // Update button text and button title.
        updateButtonText: function() {
            var options = this.getSelected();

            this.inputCache = null;
            this.optionCache = null;
            
            // First update the displayed button text.
            $('button', this.$container).html(this.options.buttonText(options, this.$select));
            
            // Now update the title attribute of the button.
            $('button', this.$container).attr('title', this.options.buttonTitle(options, this.$select));
            
        },

        // Get all selected options.
        getSelected: function() {
            return $('option[value!="' + this.options.selectAllValue + '"]:selected', this.$select).filter(function() {
                return $(this).prop('selected');
            });
        },
        
        // Get the corresponding option by ts value.
        getOptionByValue: function(value) {
            return $('option', this.$select).filter(function() {
                return $(this).val() == value;
            });

            if (!this.optionCache)
            {
            	var optionCache = {};
            	$('option', this.$select).each(function(i, option)
            	{
            		optionCache[$(option).val()] = $(option);
				});
				this.optionCache = optionCache;
			}
			
			return this.optionCache[value];

/*
            return $('option', this.$select).filter(function() {
                return $(this).val() == value;
            });
*/
        },
        
        // Get an input in the dropdown by its value.
        getInputByValue: function(value) {
            return $('li input', this.$ul).filter(function() {
                return $(this).val() == value;
            });

            if (!this.inputCache)
            {
            	var inputCache = {};
            	$('li input', this.$ul).each(function(i, input)
            	{
            		inputCache[$(input).val()] = $(input);
				});
				
				this.inputCache = inputCache;
			}
			
			return this.inputCache[value];

            return $('li input', this.$ul).filter(function() {
                return $(this).val() == value;
            });
        },
        
        updateOriginalOptions: function() {
            this.originalOptions = this.$select.clone()[0].options;
        },

        asyncFunction: function(callback, timeout, self) {
            var args = Array.prototype.slice.call(arguments, 3);
            return setTimeout(function() {
                callback.apply(self || window, args);
            }, timeout);
        }
    };

    $.fn.multiselect = function(option, parameter) {
        return this.each(function() {
            var data = $(this).data('multiselect'), options = typeof option == 'object' && option;

            // Initialize the multiselect.
            if (!data) {
                $(this).data('multiselect', ( data = new Multiselect(this, options)));
            }

            // Call multiselect method.
            if ( typeof option == 'string') {
                data[option](parameter);
            }
        });
    };

    $.fn.multiselect.Constructor = Multiselect;
    
    // Automatically init selects by their data-role.
    $(function() {
        $("select[data-role=multiselect]").multiselect();
    });

}(window.jQuery);

/* Scrollable tbody */
;(function($){

	var scrollbarWidth = 0;

	// http://jdsharp.us/jQuery/minute/calculate-scrollbar-width.php
	function getScrollbarWidth()
	{
		if (scrollbarWidth) return scrollbarWidth;
		var div = $('<div style="width:50px;height:50px;overflow:hidden;position:absolute;top:-200px;left:-200px;"><div style="height:100px;"></div></div>');
		$('body').append(div);
		var w1 = $('div', div).innerWidth();
		div.css('overflow-y', 'auto');
		var w2 = $('div', div).innerWidth();
		$(div).remove();
		scrollbarWidth = (w1 - w2);
		return scrollbarWidth;
	}

	$.fn.tableScroll = function(options)
	{
		if (options == 'undo')
		{
			var container = $(this).parent().parent();
			if (container.hasClass('tablescroll_wrapper'))
			{
				container.find('.tablescroll_head thead').prependTo(this);
				container.find('.tablescroll_foot tfoot').appendTo(this);
				container.before(this);
				container.empty();
			}
			return;
		}

		var settings = $.extend({},$.fn.tableScroll.defaults,options);

		// Bail out if there's no vertical overflow
		//if ($(this).height() <= settings.height)
		//{
		//  return this;
		//}

		settings.scrollbarWidth = getScrollbarWidth();

		this.each(function()
		{
			var flush = settings.flush;

			var tb = $(this).addClass('tablescroll_body');

			var wrapper = $('<div class="tablescroll_wrapper"></div>').insertBefore(tb).append(tb);

			// check for a predefined container
			if (!wrapper.parent('div').hasClass(settings.containerClass))
			{
				$('<div></div>').addClass(settings.containerClass).insertBefore(wrapper).append(wrapper);
			}

			var width = settings.width ? settings.width : tb.outerWidth();

			wrapper.css
			({
				'width': width+'px',
				'height': settings.height+'px',
				'overflow': 'auto'
			});

			tb.css('width',width+'px');

			// with border difference
			var wrapper_width = wrapper.outerWidth();
			var diff = wrapper_width-width;

			// assume table will scroll
			wrapper.css({width:((width-diff)+settings.scrollbarWidth)+'px'});
			tb.css('width',(width-diff)+'px');

			if (tb.outerHeight() <= settings.height)
			{
				wrapper.css({height:'auto',width:(width-diff)+'px'});
				flush = false;
			}

			// using wrap does not put wrapper in the DOM right
			// away making it unavailable for use during runtime
			// tb.wrap(wrapper);

			// possible speed enhancements
			var has_thead = $('thead',tb).length ? true : false ;
			var has_tfoot = $('tfoot',tb).length ? true : false ;
			var thead_tr_first = $('thead tr:eq(1)',tb);
			var tbody_tr_first = $('tbody tr:first',tb);
			var tfoot_tr_first = $('tfoot tr:first',tb);

			// remember width of last cell
			var w = 0;

			$('th, td',tbody_tr_first).each(function(i)
			{
				w = $(this).width();

				$('th:eq('+i+'), td:eq('+i+')',thead_tr_first).css('width',w+'px');
				$('th:eq('+i+'), td:eq('+i+')',tbody_tr_first).css('width',w+'px');
				if (has_tfoot) $('th:eq('+i+'), td:eq('+i+')',tfoot_tr_first).css('width',w+'px');
			});

			if (has_thead)
			{
				var tbh = $('<table class="tablescroll_head" cellspacing="0"></table>').insertBefore(wrapper).prepend($('thead',tb));
			}

			if (has_tfoot)
			{
				var tbf = $('<table class="tablescroll_foot" cellspacing="0"></table>').insertAfter(wrapper).prepend($('tfoot',tb));
			}

			if (tbh != undefined)
			{
				tbh.css('width',width+'px');

				if (flush)
				{
					$('tr:first th:last, tr:first td:last',tbh).css('width',(w+settings.scrollbarWidth)+'px');
					tbh.css('width',wrapper.outerWidth() + 'px');
				}
			}

			if (tbf != undefined)
			{
				tbf.css('width',width+'px');

				if (flush)
				{
					$('tr:first th:last, tr:first td:last',tbf).css('width',(w+settings.scrollbarWidth)+'px');
					tbf.css('width',wrapper.outerWidth() + 'px');
				}
			}
		});

		return this;
	};

	// public
	$.fn.tableScroll.defaults =
	{
		flush: true, // makes the last thead and tbody column flush with the scrollbar
		width: null, // width of the table (head, body and foot), null defaults to the tables natural width
		height: 100, // height of the scrollable area
		containerClass: 'tablescroll' // the plugin wraps the table in a div with this css class
	};

})(jQuery);

/* Center popup container */
;(function ($)
{
	$.fn.center = function ()
	{
		var windowWidth = document.documentElement.clientWidth;
		var windowHeight = document.documentElement.clientHeight;
		var popupHeight = $(this).height();
		var popupWidth = $(this).width();

		//centering
		$(this).css({
		"position": "fixed",
		"top": windowHeight/2-popupHeight/2,
		"left": windowWidth/2-popupWidth/2
		});
	}
}
)(jQuery);

// Sticky Plugin
// =============
// Author: Anthony Garand
// Improvements by German M. Bravo (Kronuz) and Ruud Kamphuis (ruudk)
// Improvements by Leonardo C. Daronco (daronco)
// Created: 2/14/2011
// Date: 2/12/2012
// Website: http://labs.anthonygarand.com/sticky
// Description: Makes an element on the page stick on the screen as you scroll
//              It will only set the 'top' and 'position' of your element, you
//              might need to adjust the width in some cases.

;(function($) {
    var defaults = {
            topSpacing: 0,
            bottomSpacing: 0,
            className: 'is-sticky',
            wrapperClassName: 'sticky-wrapper'
        },
        $window = $(window),
        $document = $(document),
        sticked = [],
        windowHeight = $window.height(),
        scroller = function() {
            var scrollTop = $window.scrollTop(),
                documentHeight = $document.height(),
                dwh = documentHeight - windowHeight,
                extra = (scrollTop > dwh) ? dwh - scrollTop : 0;
            for (var i = 0; i < sticked.length; i++) {
                var s = sticked[i],
                    elementTop = s.stickyWrapper.offset().top,
                    etse = elementTop - s.topSpacing - extra;
                if (scrollTop <= etse) {
                    if (s.currentTop !== null) {
                        s.stickyElement
                            .css('position', '')
                            .css('top', '')
                            .removeClass(s.className);
                        s.stickyElement.parent().removeClass(s.className);
                        s.currentTop = null;
                    }
                }
                else {
                    var newTop = documentHeight - s.stickyElement.outerHeight()
                        - s.topSpacing - s.bottomSpacing - scrollTop - extra;
                    if (newTop < 0) {
                        newTop = newTop + s.topSpacing;
                    } else {
                        newTop = s.topSpacing;
                    }
                    if (s.currentTop != newTop) {
                        s.stickyElement
                            .css('position', 'fixed')
                            .css('top', newTop)
                            .addClass(s.className);
                        s.stickyElement.parent().addClass(s.className);
                        s.currentTop = newTop;
                    }
                }
            }
        },
        resizer = function() {
            windowHeight = $window.height();
        },
        methods = {
            init: function(options) {
                var o = $.extend(defaults, options);
                return this.each(function() {
                    var stickyElement = $(this);

                    stickyId = stickyElement.attr('id');
                    wrapper = $('<div></div>')
                        .attr('id', stickyId + '-sticky-wrapper')
                        .addClass(o.wrapperClassName);
                    stickyElement.wrapAll(wrapper);
                    var stickyWrapper = stickyElement.parent();
                    stickyWrapper.css('height', stickyElement.outerHeight());
                    sticked.push({
                        topSpacing: o.topSpacing,
                        bottomSpacing: o.bottomSpacing,
                        stickyElement: stickyElement,
                        currentTop: null,
                        stickyWrapper: stickyWrapper,
                        className: o.className
                    });
                });
            },
            update: scroller
        };

    // should be more efficient than using $window.scroll(scroller) and $window.resize(resizer):
    if (window.addEventListener) {
        window.addEventListener('scroll', scroller, false);
        window.addEventListener('resize', resizer, false);
    } else if (window.attachEvent) {
        window.attachEvent('onscroll', scroller);
        window.attachEvent('onresize', resizer);
    }

    $.fn.sticky = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.sticky');
        }
    };
    $(function() {
        setTimeout(scroller, 0);
    });
})(jQuery);

;

function scrollToElement(selector, time, verticalOffset) {
    time = typeof(time) != 'undefined' ? time : 1000;
    verticalOffset = typeof(verticalOffset) != 'undefined' ? verticalOffset : 0;
    element = $(selector);
    offset = element.offset();
    offsetTop = offset.top + verticalOffset;
    jQuery('html, body').animate({
        scrollTop: offsetTop
    }, time);
};

/*
 * jQuery UI Nested Sortable
 * v 1.3.5 / 21 jun 2012
 * http://mjsarfatti.com/code/nestedSortable
 *
 * Depends on:
 *	 jquery.ui.sortable.js 1.8+
 *
 * Copyright (c) 2010-2012 Manuele J Sarfatti
 * Licensed under the MIT License
 * http://www.opensource.org/licenses/mit-license.php
 */

(function($) {

	$.widget("mjs.nestedSortable", $.extend({}, $.ui.sortable.prototype, {

		options: {
			tabSize: 20,
			disableNesting: 'mjs-nestedSortable-no-nesting',
			errorClass: 'mjs-nestedSortable-error',
			doNotClear: false,
			listType: 'ol',
      items: 'li',
      listTypes: null,
      listTypesRepeatedTypes: null,
      listTypesRepeatThreshold: Infinity,
			maxLevels: 0,
			protectRoot: false,
			rootID: null,
			rtl: false,
			isAllowed: function(item, parent) { return true; }
		},

    _updateChildLists: function (element) {

      var o = this.options,
          listType = o.listType,
          listTypesRepeatThreshold = this._listTypesRepeatThreshold,
          listTypesRepeatedTypes = this._listTypesRepeatedTypes,
          root = this.element;

      $(element).find(listType).each(function () {

        var depth = $(this).parentsUntil(root, listType).length + 1,
            listTypes;

          if (depth < listTypesRepeatThreshold) {
            listTypes = o.listTypes;
          } else {
            depth -= listTypesRepeatThreshold;
            listTypes = listTypesRepeatedTypes;
          }

          this.setAttribute('type', listTypes[depth % listTypes.length])
      });
    },

    _handleDOMNodeInserted: function (event) {
      var placeholder = this.placeholder,
          target = event.target;
      if (target.nodeName !== this.options.items || placeholder && placeholder[0] && placeholder[0] === target) return;
      this._updateChildLists(target);
    },

		_create: function() {

      var o = this.options,
          element = this.element,
          listTypes = o.listTypes;

			element.data('sortable', element.data('nestedSortable'));

      o.items = o.items.toUpperCase();

			if (!element.is(o.listType)) {
				throw new Error('nestedSortable: Please check the listType option is set to your actual list type');
      }

      if (listTypes) {
        if ($.isArray(listTypes[listTypes.length - 1])) {
          this._listTypesRepeatedTypes = listTypes.pop();
          this._listTypesRepeatThreshold = listTypes.length;
        }
        element.attr('type', listTypes[0]);
        this._updateChildLists(element);
        element.on('DOMNodeInserted.' + this.widgetName,  $.proxy(this, '_handleDOMNodeInserted'));
      }

			return $.ui.sortable.prototype._create.apply(this, arguments);
		},

		destroy: function() {
			this.element
				.removeData("nestedSortable")
				.unbind(".nestedSortable");
			return $.ui.sortable.prototype.destroy.apply(this, arguments);
		},

		_mouseDrag: function(event) {

			//Compute the helpers position
			this.position = this._generatePosition(event);
			this.positionAbs = this._convertPositionTo("absolute");

			if (!this.lastPositionAbs) {
				this.lastPositionAbs = this.positionAbs;
			}

			var o = this.options;

			//Do scrolling
			if(this.options.scroll) {
				var scrolled = false;
				if(this.scrollParent[0] != document && this.scrollParent[0].tagName != 'HTML') {

					if((this.overflowOffset.top + this.scrollParent[0].offsetHeight) - event.pageY < o.scrollSensitivity)
						this.scrollParent[0].scrollTop = scrolled = this.scrollParent[0].scrollTop + o.scrollSpeed;
					else if(event.pageY - this.overflowOffset.top < o.scrollSensitivity)
						this.scrollParent[0].scrollTop = scrolled = this.scrollParent[0].scrollTop - o.scrollSpeed;

					if((this.overflowOffset.left + this.scrollParent[0].offsetWidth) - event.pageX < o.scrollSensitivity)
						this.scrollParent[0].scrollLeft = scrolled = this.scrollParent[0].scrollLeft + o.scrollSpeed;
					else if(event.pageX - this.overflowOffset.left < o.scrollSensitivity)
						this.scrollParent[0].scrollLeft = scrolled = this.scrollParent[0].scrollLeft - o.scrollSpeed;

				} else {

					if(event.pageY - $(document).scrollTop() < o.scrollSensitivity)
						scrolled = $(document).scrollTop($(document).scrollTop() - o.scrollSpeed);
					else if($(window).height() - (event.pageY - $(document).scrollTop()) < o.scrollSensitivity)
						scrolled = $(document).scrollTop($(document).scrollTop() + o.scrollSpeed);

					if(event.pageX - $(document).scrollLeft() < o.scrollSensitivity)
						scrolled = $(document).scrollLeft($(document).scrollLeft() - o.scrollSpeed);
					else if($(window).width() - (event.pageX - $(document).scrollLeft()) < o.scrollSensitivity)
						scrolled = $(document).scrollLeft($(document).scrollLeft() + o.scrollSpeed);

				}

				if(scrolled !== false && $.ui.ddmanager && !o.dropBehaviour)
					$.ui.ddmanager.prepareOffsets(this, event);
			}

			//Regenerate the absolute position used for position checks
			this.positionAbs = this._convertPositionTo("absolute");

      // Find the top offset before rearrangement,
      var previousTopOffset = this.placeholder.offset().top;

			//Set the helper position
			if(!this.options.axis || this.options.axis != "y") this.helper[0].style.left = this.position.left+'px';
			if(!this.options.axis || this.options.axis != "x") this.helper[0].style.top = this.position.top+'px';

			//Rearrange
			for (var i = this.items.length - 1; i >= 0; i--) {

				//Cache variables and intersection, continue if no intersection
				var item = this.items[i], itemElement = item.item[0], intersection = this._intersectsWithPointer(item);
				if (!intersection) continue;

				if(itemElement != this.currentItem[0] //cannot intersect with itself
					&&	this.placeholder[intersection == 1 ? "next" : "prev"]()[0] != itemElement //no useless actions that have been done before
					&&	!$.contains(this.placeholder[0], itemElement) //no action if the item moved is the parent of the item checked
					&& (this.options.type == 'semi-dynamic' ? !$.contains(this.element[0], itemElement) : true)
					//&& itemElement.parentNode == this.placeholder[0].parentNode
          // only rearrange items within the same container
				) {

					$(itemElement).mouseenter();

					this.direction = intersection == 1 ? "down" : "up";

					if (this.options.tolerance == "pointer" || this._intersectsWithSides(item)) {
						$(itemElement).mouseleave();
						this._rearrange(event, item);
					} else {
						break;
					}

					// Clear emtpy ul's/ol's
					this._clearEmpty(itemElement);

					this._trigger("change", event, this._uiHash());
					break;
				}
			}

			var parentItem = (this.placeholder[0].parentNode.parentNode &&
							 $(this.placeholder[0].parentNode.parentNode).closest('.ui-sortable').length)
				       			? $(this.placeholder[0].parentNode.parentNode)
				       			: null,
			    level = this._getLevel(this.placeholder),
			    childLevels = this._getChildLevels(this.helper);

      // To find the previous sibling in the list, keep backtracking until we hit a valid list item.
			var previousItem = this.placeholder[0].previousSibling ? $(this.placeholder[0].previousSibling) : null;
			if (previousItem != null) {
				while (previousItem[0].nodeName.toLowerCase() != 'li' || previousItem[0] == this.currentItem[0] || previousItem[0] == this.helper[0]) {
					if (previousItem[0].previousSibling) {
						previousItem = $(previousItem[0].previousSibling);
					} else {
						previousItem = null;
						break;
					}
				}
			}

      // To find the next sibling in the list, keep stepping forward until we hit a valid list item.
      var nextItem = this.placeholder[0].nextSibling ? $(this.placeholder[0].nextSibling) : null;
      if (nextItem != null) {
        while (nextItem[0].nodeName.toLowerCase() != 'li' || nextItem[0] == this.currentItem[0] || nextItem[0] == this.helper[0]) {
          if (nextItem[0].nextSibling) {
            nextItem = $(nextItem[0].nextSibling);
          } else {
            nextItem = null;
            break;
          }
        }
      }

			var newList = document.createElement(o.listType);
			this.beyondMaxLevels = 0;

			// If the item is moved to the left, send it to its parent's level unless there are siblings below it.
			if (parentItem != null && nextItem == null &&
					(o.rtl && (this.positionAbs.left + this.helper.outerWidth() > parentItem.offset().left + parentItem.outerWidth()) ||
					!o.rtl && (this.positionAbs.left < parentItem.offset().left))) {
				parentItem.after(this.placeholder[0]);
				this._clearEmpty(parentItem[0]);
				this._trigger("change", event, this._uiHash());
			}
			// If the item is below a sibling and is moved to the right, make it a child of that sibling.
			else if (previousItem != null &&
						(o.rtl && (this.positionAbs.left + this.helper.outerWidth() < previousItem.offset().left + previousItem.outerWidth() - o.tabSize) ||
						!o.rtl && (this.positionAbs.left > previousItem.offset().left + o.tabSize))) {
				this._isAllowed(previousItem, level, level+childLevels+1);
				if (!previousItem.children(o.listType).length) {
					previousItem[0].appendChild(newList);
				}
        // If this item is being moved from the top, add it to the top of the list.
        if (previousTopOffset && (previousTopOffset <= previousItem.offset().top)) {
          previousItem.children(o.listType).prepend(this.placeholder);
        }
        // Otherwise, add it to the bottom of the list.
        else {
				  previousItem.children(o.listType)[0].appendChild(this.placeholder[0]);
        }
				this._trigger("change", event, this._uiHash());
			}
			else {
				this._isAllowed(parentItem, level, level+childLevels);
			}

			//Post events to containers
			this._contactContainers(event);

			//Interconnect with droppables
			if($.ui.ddmanager) $.ui.ddmanager.drag(this, event);

			//Call callbacks
			this._trigger('sort', event, this._uiHash());

			this.lastPositionAbs = this.positionAbs;

			return false;

		},

		_mouseStop: function(event, noPropagation) {

      var helper = this.helper;

			// If the item is in a position not allowed, send it back
			if (this.beyondMaxLevels) {

				this.placeholder.removeClass(this.options.errorClass);

				if (this.domPosition.prev) {
					$(this.domPosition.prev).after(this.placeholder);
				} else {
					$(this.domPosition.parent).prepend(this.placeholder);
				}

				this._trigger("revert", event, this._uiHash());

			}

			// Clean last empty ul/ol
			for (var i = this.items.length - 1; i >= 0; i--) {
				var item = this.items[i].item[0];
				this._clearEmpty(item);
			}

			$.ui.sortable.prototype._mouseStop.apply(this, arguments);
		},

		serialize: function(options) {

			var o = $.extend({}, this.options, options),
				items = this._getItemsAsjQuery(o && o.connected),
			    str = [];

			$(items).each(function() {
				var res = ($(o.item || this).attr(o.attribute || 'id') || '')
						.match(o.expression || (/(.+)[-=_](.+)/)),
				    pid = ($(o.item || this).parent(o.listType)
						.parent(o.items)
						.attr(o.attribute || 'id') || '')
						.match(o.expression || (/(.+)[-=_](.+)/));

				if (res) {
					str.push(((o.key || res[1]) + '[' + (o.key && o.expression ? res[1] : res[2]) + ']')
						+ '='
						+ (pid ? (o.key && o.expression ? pid[1] : pid[2]) : o.rootID));
				}
			});

			if(!str.length && o.key) {
				str.push(o.key + '=');
			}

			return str.join('&');

		},

		toHierarchy: function(options) {

			var o = $.extend({}, this.options, options),
				sDepth = o.startDepthCount || 0,
			    ret = [];

			$(this.element).children(o.items).each(function () {
				var level = _recursiveItems(this);
				ret.push(level);
			});

			return ret;

			function _recursiveItems(item) {
				var id = ($(item).attr(o.attribute || 'id') || '').match(o.expression || (/(.+)[-=_](.+)/));
				if (id) {
					var currentItem = {"id" : id[2]};
					if ($(item).children(o.listType).children(o.items).length > 0) {
						currentItem.children = [];
						$(item).children(o.listType).children(o.items).each(function() {
							var level = _recursiveItems(this);
							currentItem.children.push(level);
						});
					}
					return currentItem;
				}
			}
		},

		toArray: function(options) {

			var o = $.extend({}, this.options, options),
				sDepth = o.startDepthCount || 0,
			    ret = [],
			    left = 2;

			ret.push({
				"item_id": o.rootID,
				"parent_id": 'none',
				"depth": sDepth,
				"left": '1',
				"right": ($(o.items, this.element).length + 1) * 2
			});

			$(this.element).children(o.items).each(function () {
				left = _recursiveArray(this, sDepth + 1, left);
			});

			ret = ret.sort(function(a,b){ return (a.left - b.left); });

			return ret;

			function _recursiveArray(item, depth, left) {

				var right = left + 1,
				    id,
				    pid;

				if ($(item).children(o.listType).children(o.items).length > 0) {
					depth ++;
					$(item).children(o.listType).children(o.items).each(function () {
						right = _recursiveArray($(this), depth, right);
					});
					depth --;
				}

				id = ($(item).attr(o.attribute || 'id')).match(o.expression || (/(.+)[-=_](.+)/));

				if (depth === sDepth + 1) {
					pid = o.rootID;
				} else {
					var parentItem = ($(item).parent(o.listType)
											 .parent(o.items)
											 .attr(o.attribute || 'id'))
											 .match(o.expression || (/(.+)[-=_](.+)/));
					pid = parentItem[2];
				}

				if (id) {
						ret.push({"item_id": id[2], "parent_id": pid, "depth": depth, "left": left, "right": right});
				}

				left = right + 1;
				return left;
			}

		},

		_clearEmpty: function(item) {

			var emptyList = $(item).children(this.options.listType);
			if (emptyList.length && !emptyList.children().length && !this.options.doNotClear) {
				emptyList.remove();
			}

		},

		_getLevel: function(item) {

			var level = 1;

			if (this.options.listType) {
				var list = item.closest(this.options.listType);
				while (list && list.length > 0 &&
                    	!list.is('.ui-sortable')) {
					level++;
					list = list.parent().closest(this.options.listType);
				}
			}

			return level;
		},

		_getChildLevels: function(parent, depth) {
			var self = this,
			    o = this.options,
			    result = 0;
			depth = depth || 0;

			$(parent).children(o.listType).children(o.items).each(function (index, child) {
					result = Math.max(self._getChildLevels(child, depth + 1), result);
			});

			return depth ? result + 1 : result;
		},

		_isAllowed: function(parentItem, level, levels) {
			var o = this.options,
				isRoot = $(this.domPosition.parent).hasClass('ui-sortable') ? true : false,
				maxLevels = this.placeholder.closest('.ui-sortable').nestedSortable('option', 'maxLevels'); // this takes into account the maxLevels set to the recipient list

			// Is the root protected?
			// Are we trying to nest under a no-nest?
			// Are we nesting too deep?
			if (!o.isAllowed(this.currentItem, parentItem) ||
				parentItem && parentItem.hasClass(o.disableNesting) ||
				o.protectRoot && (parentItem == null && !isRoot || isRoot && level > 1)) {
					this.placeholder.addClass(o.errorClass);
					if (maxLevels < levels && maxLevels != 0) {
						this.beyondMaxLevels = levels - maxLevels;
					} else {
						this.beyondMaxLevels = 1;
					}
			} else {
				if (maxLevels < levels && maxLevels != 0) {
					this.placeholder.addClass(o.errorClass);
					this.beyondMaxLevels = levels - maxLevels;
				} else {
					this.placeholder.removeClass(o.errorClass);
					this.beyondMaxLevels = 0;
				}
			}
		}

	}));

	$.mjs.nestedSortable.prototype.options = $.extend({}, $.ui.sortable.prototype.options, $.mjs.nestedSortable.prototype.options);
})(jQuery);
