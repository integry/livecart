/**
 *	@author Integry Systems
 */

if (Backend == undefined)
{
	var Backend = {}
}

Backend.CsvImport =
{
	showCategorySelector: function(current)
	{
		new Backend.Category.PopupSelector(
			function(categoryID, pathAsText, path)
			{
				$('categoryID').value = categoryID;

				var out = '';
				var path = $H(path);
				var count = path.values().length - 1;

				path.each(
					function(s, index)
					{
						out = '<a href="#" onclick="Backend.CsvImport.showCategorySelector(' + s[0] + '); return false;">' + s[1] + '</a>' + out;
						if (index < count)
						{
							out = ' &gt; ' + out;
						}
					}
				);

				$('targetCategory').innerHTML = out;

				return true;
			},
			null,
			current
		);
	},

	updatePreview: function()
	{
		new LiveCart.AjaxUpdater($('delimitersForm'), $('previewContainer'), $('previewIndicator'));
	},

	toggleHeaderRow: function(state, row)
	{
		if (state)
		{
			row.addClassName('headerRow');
		}
		else
		{
			row.removeClassName('headerRow');
		}
	},

	cont: function(e)
	{
		// set delimiter
		if ($('delimiters').visible())
		{
			$('delimitersForm').action = $('fieldsUrl').innerHTML;
			new LiveCart.AjaxUpdater($('delimitersForm'), $('fieldsContainer'), $('previewIndicator'), null, Backend.CsvImport.loadFields);
		}

		// proceed with import
		else
		{
			$('delimitersForm').action = $('importUrl').innerHTML;
			this.request = new LiveCart.AjaxRequest($('delimitersForm'), $('previewIndicator'), this.dataResponse.bind(this),  {onInteractive: this.dataResponse.bind(this) });
			$('importControls').hide();
			$('columns').hide();
			$('preview').hide();
			$('progress').show();

			$('wizardProgress').removeClassName('stepArrange');
			$('wizardProgress').addClassName('stepImport');
		}
	},

	loadFields: function()
	{
		$('delimiters').hide();
		$('columns').show();
		$('preview').addClassName('delimiterSelected');

		$('wizardProgress').removeClassName('stepDelimiters');
		$('wizardProgress').addClassName('stepArrange');

		var selectChange = function(e)
		{
			Backend.CsvImport.toggleSelectValues(this, true);
			this.previousIndex = this.selectedIndex;
			Backend.CsvImport.toggleSelectValues(this, false);

			var index = this.name.match(/\[([0-9]*)\]/)[1];
			$A($('preview').getElementsByClassName('column_' + index)).each
			(
				function(cell)
				{
					if (this.value)
					{
						cell.addClassName('selectedColumn');
					}
					else
					{
						cell.removeClassName('selectedColumn');
					}
				}.bind(this)
			)
		}

		var allSelects = $A($('columns').getElementsByTagName('select'));
		allSelects.each
		(
			function(select)
			{
				Event.observe(select, 'change', selectChange);
				select.allSelects = allSelects;
			}
		);
	},

	toggleSelectValues: function(element, state)
	{
		var index = element.previousIndex;

		if (!index)
		{
			return false;
		}

		element.allSelects.each
		(
			function(select)
			{
				if (select != element)
				{
					if (state)
					{
						select.options[index].show();
					}
					else
					{
						select.options[index].hide();
					}
				}
			}
		);
	},

	showColumn: function(index)
	{
		var cells = $('preview').getElementsByClassName('column_' + index);
		if (!cells.length)
		{
			return false;
		}

		cells[0].scrollIntoView();
		for (k = 0; k < cells.length; k++)
		{
			new Effect.Highlight(cells[k], {endcolor: '#F8F8F8'});
		}
	},

	showSelect: function(index)
	{
		var element = $('column_select_' + index);
		if (element)
		{
			element.scrollIntoView();
			new Effect.Highlight(element, {endcolor: '#F8F8F8'});
		}
	},

	dataResponse: function(originalRequest)
    {
		if (originalRequest.responseData && originalRequest.responseData.errors)
		{
			this.restoreLayoutAfterCancel();
			ActiveForm.prototype.setErrorMessages($('delimitersForm'), originalRequest.responseData.errors);
			return false;
		}

        var response = originalRequest.responseText.substr(this.formerLength + 1);
        this.formerLength = originalRequest.responseText.length;

        var portions = response.split('|');

        for (var k = 0; k < portions.length; k++)
        {
			response = eval('(' + decode64(portions[k]) + ')');

            // progress
            if (response.progress != undefined)
            {
                this.setProgress(response);
            }

            // cancel
            else if (response.cancelled)
            {
                this.setProgress(response);
            }
        }
    },

    setProgress: function(response)
    {
        var li = $('progress');
        li.down('.progressBar').show();
        li.down('.progressTotal').update(response.total);

        if (response.progress > 0)
        {
            this.updateProgress(response.progress, response.total, li);
        }
        else
        {
            li.removeClassName('inProgress');
            li.addClassName('completed');
			li.down('.progressCount').update(response.total);
			li.down('.progressBarIndicator').hide();
			li.down('.cancel').hide();

			new Backend.SaveConfirmationMessage($('completeMessage'));
        }
    },

    cancel: function()
    {
		this.request.request.transport.abort();
    	new LiveCart.AjaxRequest($('cancelUrl').innerHTML, null, this.completeCancel.bind(this));
	},

	completeCancel: function(originalRequest)
	{
		var resp = originalRequest.responseData;

		if (resp.cancelled)
		{
			var container = $('progress');
			var progress = container.down('.progressCount').innerHTML;
			var total = container.down('.progressTotal').innerHTML;

			var step = Math.round(progress/50);
			if (step < 1)
			{
				step = 1;
			}

			Backend.CsvImport.rewindProgressBar(progress, total, container, step);
		}
		else
		{
			new Backend.SaveConfirmationMessage($('cancelFailureMessage'));
		}
	},

	rewindProgressBar: function(progress, total, container, step)
	{
		if (progress > 0)
		{
			progress -= step;
			Backend.CsvImport.updateProgress(progress, total, container);
			setTimeout(function() { Backend.CsvImport.rewindProgressBar(progress, total, container, step) }, 70);
		}
		else
		{
			this.restoreLayoutAfterCancel();
			new Backend.SaveConfirmationMessage($('cancelCompleteMessage'));
		}
	},

	restoreLayoutAfterCancel: function()
	{
		$('importControls').show();
		$('columns').show();
		$('preview').show();
		$('progress').hide();
		$('wizardProgress').removeClassName('stepImport');
		$('wizardProgress').addClassName('stepArrange');
	},

	updateProgress: function(progress, total, container)
	{
		container.down('.progressCount').update(progress);
		var progressWidth = (parseFloat(progress) / parseFloat(total)) * container.down('.progressBar').clientWidth;
		container.down('.progressBarIndicator').style.width = progressWidth + 'px';
	}
}