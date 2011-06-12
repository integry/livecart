/**
 *	@author Integry Systems
 */

Backend.Module = function(container)
{
	this.container = container;
	$A(container.getElementsBySelector('li.module')).each(function(node)
	{
		this.initNode(node);
	}.bind(this));
}

Backend.Module.prototype =
{
	initNode: function(node)
	{
		node.cb = node.down('input.checkbox');
		node.cb.onchange = function(e) { this.setStatus(node); }.bind(this);

		node.installAction = node.down('a.installAction');
		node.installAction.onclick = function(e) { Event.stop(e); this.setInstall(node); }.bind(this);

		node.updateAction = node.down('a.updateAction');

		if (node.updateAction)
		{
			node.updateAction.onclick = function(e) { Event.stop(e); this.loadUpdateMenu(node); }.bind(this);
		}
	},

	loadUpdateMenu: function(node)
	{
		node.updateMenu = node.down('.updateMenuContainer');
		node.actionMenu = node.down('.moduleUpdate');
		new LiveCart.AjaxUpdater(Backend.Router.createUrl('backend.module', 'updateMenu', {id: node.id, repo: node.repo.repo, handshake: node.repo.handshake}), node.updateMenu, node.updateAction.down('.progressIndicator'), null,
			function (oR)
			{
				node.actionMenu.hide();
				node.addClassName('updateMenuLoaded');
				Event.observe(node.updateMenu.down('a.cancel'), 'click', function(e) { this.hideUpdateMenu(e, node); }.bind(this));

				var form = node.updateMenu.down('form');
				var channel = form.elements.namedItem('channel');
				Event.observe(channel, 'change', function(e)
				{
					this.updateVersionList(node, channel, form.elements.namedItem('version'));
				}.bind(this));

				Event.observe(node, 'submit', function(e)
				{
					Event.stop(e);
					this.update(node);
				}.bind(this));
			}.bind(this));
	},

	hideUpdateMenu: function(e, node)
	{
		Event.stop(e);
		node.actionMenu.show();
		node.updateMenu.update('');
		node.removeClassName('updateMenuLoaded');
	},

	updateVersionList: function(node, channelSelect, versionSelect)
	{
		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.module', 'listVersions', {id: node.id, repo: node.repo.repo, handshake: node.repo.handshake, channel: channelSelect.value}), versionSelect,
		function (oR)
		{
			versionSelect.options.length = 0;

			for (var v in oR.responseData)
			{
				versionSelect.options[versionSelect.options.length++] = new Option(oR.responseData[v], v, false, false);
			}
		});
	},

	update: function(node)
	{
		var form = node.down('form');
		var from = node.version;
		var to = form.elements.namedItem('version').value;

		ActiveForm.prototype.resetErrorMessages(form);

		var req = new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.module', 'update', {id: node.id, repo: node.repo.repo, handshake: node.repo.handshake, from: from, to: to}), node.down('input.submit'),
			function (oR)
			{ },
		{onInteractive:
			function(oR)
			{
				req.getResponseChunks(oR).each(function(chunk)
				{
					if (chunk.final)
					{
						Backend.SaveConfirmationMessage.prototype.showMessage(chunk.final);
						this.reloadNode(node);
					}
					else if ('err' == chunk.status)
					{
						Backend.SaveConfirmationMessage.prototype.showMessage(chunk.msg, 'red');
						this.reloadNode(node);
					}
					else if (chunk.path && !chunk.path.length)
					{
						ActiveForm.prototype.setErrorMessage(form.version, chunk.status);
					}
				}.bind(this));
			}.bind(this)
		});
	},

	setStatus: function(node)
	{
		this.setState(node, 'setStatus', node.cb.checked, node.cb);
	},

	setInstall: function(node)
	{
		var confirmMsg = Backend.getTranslation(node.hasClassName('installed') ? '_confirm_deinstall' : '_confirm_install');

		if (confirm(confirmMsg))
		{
			this.setState(node, node.hasClassName('installed') ? 'deinstall' : 'install', true, node.installAction);
		}
	},

	setState: function(node, action, state, progressIndicator)
	{
		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.module', action, {id: node.id, state: state}), progressIndicator, function (oR) { this.saveComplete(node, oR); }.bind(this));
	},

	saveComplete: function(node, oR)
	{
		var resp = oR.responseData || {node: oR.responseText};

		// recreate node
		var d = document.createElement('div');
		d.update(resp.node);

		var newNode = d.firstChild;
		node.parentNode.replaceChild(newNode, node);

		newNode.repo = node.repo;
		newNode.version = node.version;
		this.initNode(newNode);

		// status message
		if (resp.status)
		{
			Backend.SaveConfirmationMessage.prototype.showMessage(resp.status.status);
		}

		new Effect.Highlight(newNode);
	},

	reloadNode: function(node)
	{
		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.module', 'node', {id: node.id, repo: node.repo.repo, handshake: node.repo.handshake}), null, function (oR) { this.saveComplete(node, oR); }.bind(this));
	}
}

Backend.Module.downloadManager = function(repos)
{
	var a = $('download-modules').down('a');
	Event.observe(a, 'click', function(e)
	{
		Event.stop(e);
		new LiveCart.AjaxUpdater(Router.createUrl('backend.module', 'packageList'), $('download-modules-container'), ('download-modules'), null, function(oR)
		{
			var menu = new ActiveForm.Slide($('module-menu'));
			menu.show("download-modules", $('download-modules-container'));

			var form = $('download-modules-container').down('form');
			if (form)
			{
				Event.observe(form, 'submit', function(e)
				{
					Event.stop(e);
					new LiveCart.AjaxRequest(form, form.down('.submit'), function(oR)
					{
						menu.hide("download-modules", $('download-modules-container'));
						if (oR.responseData)
						{
							Backend.SaveConfirmationMessage.prototype.showMessage(oR.responseData.error, 'red');
						}
						else
						{
							var cont = $('just-installed');
							var nodeCont = document.createElement('div');
							cont.appendChild(nodeCont);
							nodeCont.update(oR.responseText);
							var node = nodeCont.down('li');
							window.moduleManager.initNode(node);
							cont.show();
							new Effect.Highlight(node);
						}
					});
				});
			}
		}, {parameters: 'repos=' + repos});
	});
}