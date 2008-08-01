/**
 *	@author Integry Systems
 */

if (!Backend.Review)
{
	Backend.Review = {}
}

Backend.Review.GridFormatter =
{
	url: '',

	productIDs: {},

	formatValue: function(field, value, id)
	{
		if ('Product.ID' == field)
		{
			this.productIDs[id] = value;
		}

		if ('ProductReview.title' == field || 'ProductReview.nickname' == field)
		{
			return '<span><span class="progressIndicator manufacturerIndicator" id="reviewIndicator_' + id + '" style="display: none;"></span></span>' +
				'<a href="' + this.url + '#review_' + id + '" id="review_' + id + '" onclick="Backend.Review.Editor.prototype.open(' + id + ', event); return false;">' +
					 value +
				'</a>';
		}

		if ('Product.name' == field)
		{
			return '<span class="progressIndicator" style="display: none;"></span><a href="' + Backend.Product.GridFormatter.productUrl + this.productIDs[id] + '" onclick="Backend.Product.openProduct(' + this.productIDs[id] + ', event); return false;">' + value + '</a>';
		}

		return value;
	}
}