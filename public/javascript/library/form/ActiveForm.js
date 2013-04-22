/**
 * ActiveForm will most likely work in pair with ActiveList. While ActiveList handles ActiveRecords ActiveForm handles new instances, which are not yet saved in database.
 *
 * It's main feature is to show/hide the new form and the link to this form. It allso show/hide
 * the progress indicator for new forms and generates valid handle from title
 *
 * @author   Integry Systems
 */
ActiveForm = Class.create();
ActiveForm.prototype = {


	/**
	 * Generate valid handle from item title
	 *
	 * @param string title Input title
	 * @return string valid handle
	 */
	generateHandle: function(title)
	{
		var handle = title.toLowerCase();
		var sep = '_';

		handle = handle.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,""); // trim
		handle = handle.replace(/[^a-z_\d \.]/g, ""); // remove all illegal simbols
		// handle = handle.replace(/^[\d\_]+/g, "."); // replace first digits with "."
		handle = handle.replace(/ /g, sep); // replace spaces with "."

		// replace repeating dots with one
		var oldHandle = '';
		while (oldHandle != handle)
		{
		  	oldHandle = handle;
		  	handle = handle.replace(/\_+/g, "_");
		}

		// replace leading and ending dots
		handle = handle.replace(/^\_/g, "");
		handle = handle.replace(/\_$/g, "");

		return handle;
	},

	resetErrorMessages: function(form)
	{
		if (!form)
		{
			return;
		}

		if ('form' != form.tagName.toLowerCase())
		{
			form = form.down('form');
		}

		if (!form.elements)
		{
			return;
		}

		for (var k = 0; k < form.elements.length; k++)
		{
			this.resetErrorMessage(form.elements.item(k));
		}
	},

	resetErrorMessage: function(formElement)
	{
		jQuery(formElement).closest('.control-group').removeClass('has-error');
		this.getErrorContainer(formElement).html('').hide().addClass('hidden');
	},

	getErrorContainer: function(formElement)
	{
		var parent = jQuery(formElement).closest('div.control-group');
		if (!parent.length)
		{
			//parent = jQuery(formElement).parent();
		}

		var el = parent.find('.errorText, .text-danger');
		if (!el.parent().hasClass('controls'))
		{
			parent.find('.controls').append(el);
		}

		return el;
	},

	setErrorMessages: function(form, errorMessages)
	{
		if ('form' != form.tagName.toLowerCase()) form = form.down('form');

		var focusField = true;
		$H(errorMessages).each(function(error)
		{
			if (form.elements.namedItem(error.key))
		  	{
				var formElement = form.elements.namedItem(error.key);
				var errorMessage = error.value;

				ActiveForm.prototype.setErrorMessage(formElement, errorMessage, focusField);
				focusField = false;
			}
		});
	},

	setErrorMessage: function(formElement, errorMessage, focusField)
	{
		if (focusField)
		{
			Element.focus(formElement);
		}

		jQuery(formElement).closest('.control-group').addClass('has-error');
		var errorContainer = this.getErrorContainer(formElement);

		if (errorContainer.length > 0)
		{
			errorContainer.html(errorMessage).removeClass('hidden').show('fast');
		}
		else
		{
			console.info("Please add \n...\n <div class=\"errorText hidden\"></div> \n...\n after " + formElement.name);
		}
	},

	updateNewFields: function(className, ids, parent)
	{
		ids = $H(ids);
		ids.each(function(transformation) { transformation.value = new RegExp(transformation.value);   });
		var attributes = ['class', 'name', 'id', 'for'];
		var attributesLength = attributes.length;
		var fields = $A(document.getElementsByClassName(className));

		fields.each(function(element)
		{
			for(var a = 0; a < attributesLength; a++)
			{
			   var attr = attributes[a];
			   ids.each(function(transformation) {
				   if (element[attr]) element[attr] = element[attr].replace(transformation.value, transformation.key);
			   });
			};
		});
	},

	lastTinyMceId: 0,

	disabledTextareas: {},
	lastDisabledTextareaId: 1,
	idleTinyMCEFields: {},

	initTinyMceFields: function(container)
	{
		if (!window.tinyMCE)
		{
			return false;
		}

		if ($(container).down('.mceEditor'))
		{
			return;
		}

		var textareas = $(container).getElementsBySelector('textarea.tinyMCE');
		for (k = 0; k < textareas.length; k++)
		{
			if (textareas[k].readOnly)
			{
				textareas[k].style.display = 'none';
				new Insertion.After(textareas[k], '<div class="disabledTextarea" id="disabledTextareas_' + (ActiveForm.prototype.lastDisabledTextareaId++) + '">' + textareas[k].value + '</div>');
				var disabledTextarea = textareas[k].up().down('.disabledTextarea');
				ActiveForm.prototype.disabledTextareas[disabledTextarea.id] = disabledTextarea;

				var hoverFunction = function()
				{
					$H(ActiveForm.prototype.disabledTextareas).each(function(iter)
					{
						iter.value.style.backgroundColor = '';
						iter.value.style.borderStyle = '';
						iter.value.style.borderWidth = '';
					});
				}

				Event.observe(document.body, 'mousedown', hoverFunction, true);
				Event.observe(disabledTextarea, 'click', function()
				{
					this.style.backgroundColor = '#ffc';
					this.style.borderStyle = 'inset';
					this.style.borderWidth = '2px';
				}, true);

			}
			else
			{
				textareas[k].tinyMCEId = (this.lastTinyMceId++);
				if (!textareas[k].id)
				{
					textareas[k].id = 'tinyMceControll_' + textareas[k].tinyMCEId;
				}

				jQuery(textareas[k]).onShow(function()
				{
					tinyMCE.execCommand('mceAddControl', true, this.id);
				});
			}
		}
	},

	destroyTinyMceFields: function(container)
	{
		if (!window.tinyMCE)
		{
			return false;
		}

		var textareas = container.getElementsBySelector('textarea.tinyMCE');
		for (k = 0; k < textareas.length; k++)
		{
			if (textareas[k].readOnly)
			{
				textareas[k].style.display = 'block';
				var disabledTextarea = textareas[k].up().down('.disabledTextarea');
				if (disabledTextarea)
				{
					Element.remove(disabledTextarea);
					delete ActiveForm.prototype.disabledTextareas[disabledTextarea.id];
				}

			}
			else
			{
				if (tinyMCE.getInstanceById(textareas[k].id))
				{
					tinyMCE.execCommand('mceRemoveControl', false, textareas[k].id);
					window.clearInterval(ActiveForm.prototype.idleTinyMCEFields[textareas[k].id]);
				}
			}
		}
	},

	resetTinyMceFields: function(container)
	{
		if (!window.tinyMCE)
		{
			return false;
		}

		var textareas = container.getElementsBySelector('textarea.tinyMCE');
		for(k = 0; k < textareas.length; k++)
		{
			if (textareas[k].readonly)
			{
				continue;
			}
			tinyMCE.execInstanceCommand(textareas[k].id, 'mceSetContent', true, '', true);
		}
	}
}


ActiveForm.Slide = Class.create();
ActiveForm.Slide.prototype = {
	initialize: function(ul)
	{
		this.ul = $(ul);
	},

	show: function(className, form, ignoreFields, onCompleteCallback)
	{
		// Show progress indicator
		this.ul.getElementsBySelector(".progressIndicator").invoke("show");

		setTimeout(function(className, form, ignoreFields, onCompleteCallback)
		{
			if(typeof(ignoreFields) == 'function')
			{
				onCompleteCallback = ignoreFields;
				ignoreFields = [];
			}

			if(form)
			{
				slideForm(form);
			}

			ignoreFields = ignoreFields || [];
			var form = $(form);

			$A(form.getElementsByTagName("input")).each(function(input)
			{
				if(input.type == 'text')
				{
					input.focus();
					throw $break;
				}
			});

			if(window.Form.State && !Form.State.hasBackup(form)) Form.State.backup(form, ignoreFields);
			if(window.ActiveList) ActiveList.prototype.collapseAll();
			ActiveForm.prototype.initTinyMceFields(form);

			if(onCompleteCallback)
			{
				onCompleteCallback();
			}
		}.bind(this, className, form, ignoreFields, onCompleteCallback), 10);
	},

	hide: function(className, form, ignoreFields, onCompleteCallback)
	{
		// Hide progress indicator
		this.ul.getElementsBySelector(".progressIndicator").invoke("hide");

		setTimeout(function(className, form, ignoreFields, onCompleteCallback)
		{
			if(typeof(ignoreFields) == 'function')
			{
				onCompleteCallback = ignoreFields;
				ignoreFields = [];
			}

			ignoreFields = ignoreFields || [];
			var form = $(form);

			if(window.Form.State) Form.State.restore(form, ignoreFields);
			ActiveForm.prototype.destroyTinyMceFields(form);

			if(form)
			{
				hideForm(form);
				setTimeout(
					function()
					{
						form.style.display = 'none';

						if (onCompleteCallback)
						{
							onCompleteCallback();
						}

					}, 300);
			}
		}.bind(this, className, form, ignoreFields, onCompleteCallback), 10);
	}
}


/**
 * Extend focus to use it with TinyMce fields.
 *
 * @example
 *	 <code> Element.focus(element) </code>
 *
 *   This won't work
 *	 <code>
 *		 $(element).focus();
 *		 element.focus();
 *	 </code>
 *
 * @param HTMLElement element
 */
Element.focus = function(element)
{
	var styleDisplay = element.style.display;
	var styleHeight = element.style.height;
	var styleVisibility = element.style.visibility;
	var elementType = element.type;

	if ('none' == element.style.display || "hidden" == element.type)
	{
		if (Element.isTinyMce(element)) element.style.height = '80px';

		element.style.visibility = 'hidden';
		element.style.display = 'block';
		try { element.type = elementType; } catch(e) {}
		element.focus();
		element.style.display = styleDisplay;
		element.style.height = styleHeight;
		element.style.visibility = styleVisibility;
		try { element.type = elementType; } catch(e) {}

		if (Element.isTinyMce(element)) element.style.height = '1px';
	}
	else
	{
		element.focus();
	}

	if (Element.isTinyMce(element))
	{
		var inst = tinyMCE.getInstanceById(element.nextSibling.down(".mceEditorIframe").id);
		tinyMCE.execCommand("mceStartTyping");
		inst.contentWindow.focus();
	}
}

/**
 * Check if field is tinyMce field
 *
 * @example
 *	 <code> Element.isTinyMce(element) </code>
 *
 * @param HTMLElement element
 */
Element.isTinyMce = function(element)
{
	if (!window.tinyMCE)
	{
		return false;
	}

	return element.nextSibling && element.nextSibling.nodeType != 3 && Element.hasClassName(element.nextSibling, "mceEditorContainer");
}

/**
 * Copies data from TinyMce to textarea.
 *
 * Normally it would be copied automatically on form submit, but since validator overrides
 * form.submit() we should submit all fields ourself. Note that I'm calling this funciton
 * from validation, so most of the time there is no need to worry.
 *
 * @example
 *	 <code> Element.saveTinyMceFields(element) </code>
 *
 * @param HTMLElement element
 */
Element.saveTinyMceFields = function(element)
{
	if (!window.tinyMCE)
	{
		return false;
	}

	tinyMCE.triggerSave();

	document.getElementsByClassName("mceEditor", element).each(function(mceControl)
	{
		 var id = mceControl.id.substr(0, mceControl.id.length - 7);
		 var mce = tinyMCE.get(id);
		 if (mce)
		 {
		 	mce.save();
		 }
	});
}

Form.focus = function(form)
{
	form = $(form);

	if(form.tagName != 'FORM')
	{
		form = form.down('form');
	}

	if(form)
	{
		var firstElement = form.down('input[type=text]');

		if(firstElement)
		{
			firstElement.focus();
		}
	}
}

/******** jQuery onShow ********/

;(function($){
  $.fn.extend({
    onShow: function(callback, unbind){
      return this.each(function(){
        var obj = this;
        var bindopt = (unbind==undefined)?true:unbind;
        if($.isFunction(callback)){
          if($(this).is(':hidden')){
            var checkVis = function(){
              if($(obj).is(':visible')){
                callback.call(obj);
                if(bindopt){
                  $('body').unbind('click keyup keydown', checkVis);
                }
              }
            }
            $('body').bind('click keyup keydown', checkVis);
          }
          else{
            callback.call(obj);
          }
        }
      });
    }
  });
})(jQuery);
