/**
 *	@author Integry Systems
 */

if (Backend == undefined)
{
	var Backend = {}
}

Backend.CsvImport =
{
	isCompleted: false,

	uid: null,

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
			new LiveCart.AjaxUpdater($('delimitersForm'), $('fieldsContainer'), $('previewIndicator'), null, Backend.CsvImport.loadFields.bind(this));
		}

		// proceed with import
		else
		{
			this.isCompleted = false;
			$('delimitersForm').action = $('importUrl').innerHTML;
			this.request = this.getImportRequest();
			$('importControls').hide();
			$('columns').hide();
			$('preview').hide();
			$('progress').show();

			this.progressBar = new Backend.ProgressBar($('progress'));

			$('wizardProgress').removeClassName('stepArrange');
			$('wizardProgress').addClassName('stepImport');
		}
	},

	getImportRequest: function()
	{
		return new LiveCart.AjaxRequest($('delimitersForm'), $('previewIndicator'), this.onComplete.bind(this),  {onInteractive: this.dataResponse.bind(this), onSuccess: this.onComplete.bind(this)});
	},

	loadFields: function()
	{
		$('delimiters').hide();
		$('columns').show();
		$('preview').addClassName('delimiterSelected');
		$('importControls').addClassName('delimiterSelected');

		$('wizardProgress').removeClassName('stepDelimiters');
		$('wizardProgress').addClassName('stepArrange');

		$('profile').onchange = this.loadProfile.bind(this);
		$('saveProfile').onchange = function(e)
		{
			$('profileName').disabled = !this.checked;
			$('profileName').focus();
		}

		$('deleteProfile').onclick = this.deleteProfile.bind(this);

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

		var showConfigFields = function(select, inst)
		{
			if (select)
			{
				return function()
				{
					if (!select.configContainer)
					{
						select.configContainer = select.parentNode.down('.fieldConfigContainer');
					}

					select.configContainer.update('');
					var columnIndex = select.name.match(/([0-9]+)/).pop();

					var field = $F(select);
					if (this.fieldConfig[field])
					{
						var clonedConfig = this.fieldConfig[field].cloneNode(true);
						clonedConfig.className = 'config ' + field.replace('.', '-', 'g');
						select.configContainer.appendChild(clonedConfig);
						$A(clonedConfig.getElementsByTagName('select')).each(function(sel)
						{
							sel.className = 'param-' + sel.name;
							sel.name = 'params[' + columnIndex + '][' + sel.name + ']';
						});
					}
				}.bind(inst)
			}
		}

		/* get field configurator containers */
		this.fieldConfig = {};
		$A($('fieldConfigTemplates').childNodes).each(function(container)
		{
			if (!container.tagName)
			{
				return;
			}

			container.className.split(/ /).each(function(className)
			{
				this.fieldConfig[className] = container;
			}.bind(this));
		}.bind(this));

		var allSelects = $A($('columns').down('#fieldsContainer').getElementsByTagName('select'));
		allSelects.each
		(
			function(select)
			{
				select.allSelects = allSelects;

				var colName = select.parentNode.down('label').down('a').innerHTML.toLowerCase();
				$A(select.options).each(function(opt)
				{
					if (colName == opt.innerHTML.toLowerCase())
					{
						select.value = opt.value;
						selectChange.bind(select)();
						showConfigFields(select, this)();
						throw $break;
					}
				}.bind(this));

				Event.observe(select, 'change', selectChange);
				Event.observe(select, 'change', showConfigFields(select, this));
			}.bind(this)
		);
	},

	deleteProfile: function()
	{
		var el = $('profile');
		var profile = el.value;

		if (profile && confirm(Backend.getTranslation('_confirm_profile_delete')))
		{
			new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.csvImport', 'deleteProfile', { profile: profile, type: $('delimitersForm').elements.namedItem('type').value }), el.parentNode.down('span'), this.completeProfileDelete.bind(this));
		}
	},

	completeProfileDelete: function(oReq)
	{
		$A($('profile').options).each(function(opt)
		{
			if (opt.value == oReq.responseData.profile)
			{
				opt.parentNode.removeChild(opt);
				return;
			}
		});
	},

	loadProfile: function()
	{
		var el = $('profile');
		var profile = el.value;
		$('profileName').value = profile;
		if (!profile)
		{
			this.resetFields();
			return;
		}

		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.csvImport', 'loadProfile', { profile: profile, type: $('delimitersForm').elements.namedItem('type').value }), el.parentNode.down('span'), this.applyProfile.bind(this));
	},

	resetFields: function()
	{
		var k = 0;
		do
		{
			this.setField(k, '');
		} while ($('column_select_' + ++k));
	},

	applyProfile: function(oReq)
	{
		var profileData = oReq.responseData;
		var params = profileData.params;
		delete profileData.params;

		$H(profileData).each(function(entry)
		{
			var key = entry[0];
			var entry = entry[1];
			var name = entry.name;
			delete entry.name;
			this.setField(key, name, entry);
		}.bind(this));

		$('firstHeader').checked = params.isHead;
	},

	setField: function(index, value, params)
	{
		var parent = $('column_select_' + index);
		var select = parent.down('select');
		select.value = value;
		fireEvent(select, 'change');

		$H(params).each(function(param)
		{
			var el = parent.down('.param-' + param[0]);
			if (el)
			{
				el.value = param[1];
			}
		}.bind(this));
	},

	toggleSelectValues: function(element, state)
	{
		var index = element.previousIndex;

		if (this.fieldConfig[$F(element)])
		{
			return false;
		}

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
		this.isCompleted = false;

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
			if (0 == portions[k].length)
			{
				continue;
			}

			response = eval('(' + decode64(portions[k]) + ')');

			// progress
			if (response.progress != undefined)
			{
				this.setProgress(response);
			}

			if (response.uid != undefined)
			{
				this.uid = response.uid;
				console.log(this.uid);
			}
		}
	},

	onComplete: function(originalRequest)
	{
		// sometimes the dataResponse() function is not called, so we have to double check if we're not done yet
		var response = eval('(' + decode64(originalRequest.responseText.split('|').pop()) + ')');
		if (0 == response.progress)
		{
			this.isCompleted = true;
			this.setProgress(response);
		}

		if (this.isCancelled)
		{
			this.completeCancel(originalRequest);
			return;
		}

		if (!this.isCompleted)
		{
			if (!this.nonTransactional)
			{
				new Backend.SaveConfirmationMessage($('nonTransactionalMessage'));
			}

			$('delimitersForm').elements.namedItem('continue').value = true;
			$('delimitersForm').elements.namedItem('uid').value = this.uid;
			this.isCancelled = false;
			this.request = this.getImportRequest();
			this.nonTransactional = true;
		}
	},

	setProgress: function(response)
	{
		var li = $('progress');
		li.down('.progressBar').show();

		if (response.progress > 0)
		{
			this.progressBar.update(response.progress, response.total);

			if (response.lastName)
			{
				li.down('.lastName').innerHTML = response.lastName;
			}
		}
		else
		{
			li.removeClassName('inProgress');
			li.addClassName('completed');
			this.progressBar.update(response.total, response.total);
			li.down('.progressBarIndicator').hide();
			li.down('.cancel').hide();

			new Backend.SaveConfirmationMessage($('completeMessage'));
			this.isCompleted = true;
		}
	},

	cancel: function()
	{
		this.isCancelled = true;
		this.request.request.transport.abort();
		new LiveCart.AjaxRequest($('cancelUrl').innerHTML, null, this.completeCancel.bind(this));
	},

	completeCancel: function(originalRequest)
	{
		var resp = originalRequest.responseData;

		if (resp.cancelled)
		{
			var container = $('progress');
			var progress = this.progressBar.getProgress();
			var total = this.progressBar.getTotal();

			var step = Math.round(progress/50);
			if (step < 1)
			{
				step = 1;
			}

			this.progressBar.rewind(progress, total, step,
				function()
				{
					this.restoreLayoutAfterCancel();
					new Backend.SaveConfirmationMessage($('cancelCompleteMessage'));
				}.bind(this));
		}
		else
		{
			if (!this.nonTransactional)
			{
				new Backend.SaveConfirmationMessage($('cancelFailureMessage'));
			}
		}

		this.isCancelled = true;
	},

	restoreLayoutAfterCancel: function()
	{
		$('importControls').show();
		$('columns').show();
		$('preview').show();
		$('progress').hide();
		$('wizardProgress').removeClassName('stepImport');
		$('wizardProgress').addClassName('stepArrange');
	}
}