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
		console.log(resp);
	}
}