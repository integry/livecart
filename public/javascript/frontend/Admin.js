(function()
{
	var $ = jQuery;

	var getEditLink = function(h1)
	{
		$(h1).addClass('edit');

		var span = document.createElement('span');
		span.innerHTML = h1.innerHTML;
		$(h1).empty();
		h1.appendChild(span);

		var edit = document.createElement('a');
		edit.className = 'edit';
		edit.target = '_blank';
		h1.appendChild(edit);

		return edit;
	}

	var getRecordId = function(container, prefix)
	{
		return (new RegExp(prefix + '([0-9]+)')).exec(container.attr('class')).pop();
	}

	$(window).load(function(e)
	{
		$('.product-index h1').each(function(index, h1)
		{
			getEditLink(h1).href = Router.createUrl('backend.category', 'index') + '#product_' + getRecordId($('.productIndex'), 'product_');
		});

		$('.category-index h1').each(function(index, h1)
		{
			getEditLink(h1).href = Router.createUrl('backend.category', 'index') + '#cat_' + getRecordId($('.categoryIndex'), 'category_') + '#tabMainDetails__';
		});

		$('.staticPage-view h1').each(function(index, h1)
		{
			getEditLink(h1).href = Router.createUrl('backend.staticPage', 'index') + '#page_' + getRecordId($('.staticPageView'), 'staticPage_');
		});
	});
})();