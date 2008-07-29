/**
 *	@author Integry Systems
 */

Backend.ProductListCommon = {}
Backend.ProductListCommon.Product = {}

Backend.ProductListCommon.Product.activeListCallbacks = function(ownerID)
{
	this.ownerID = ownerID;
}

Backend.ProductListCommon.Product.activeListCallbacks.prototype =
{
	beforeSort: function(li, order)
	{
		return Backend.Router.createUrl(this.callbacks.controller, 'sort', {target: this.ul.id, id: this.callbacks.ownerID}) + "&" + order;
	}
}