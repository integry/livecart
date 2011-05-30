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
					console.log('changing');
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

		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.module', 'update', {id: node.id, repo: node.repo.repo, handshake: node.repo.handshake, from: from, to: to}), node.down('input.submit'),
		function (oR)
		{
			console.log(oR.responseData);
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
		var resp = oR.responseData;

		// recreate node
		var d = document.createElement('div');
		d.innerHTML = resp.node;

		var newNode = d.firstChild;
		node.parentNode.replaceChild(newNode, node);
		this.initNode(newNode);

		// status message
		Backend.SaveConfirmationMessage.prototype.showMessage(resp.status.status);
	}
}