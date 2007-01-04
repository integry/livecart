Backend.CategoryImage = Class.create();

Backend.CategoryImage.prototype = 
{
	initialize: function()
	{	  
	},
	
	upload: function(form)
	{
		categoryId = form.elements.namedItem('catId').value;

		errorElement = $('catImgAdd_' + categoryId).getElementsByClassName('errorText')[0];
		errorElement.style.display = 'none';		  

		return false;
	},
	
	postUpload: function(categoryId, result)
	{
		errorElement = $('catImgAdd_' + categoryId).getElementsByClassName('errorText')[0];
		if (result['error'])  	
		{
			errorElement.innerHTML = result['error'];
			Effect.Appear(errorElement, {duration: 0.4});
		}
		else
		{
			errorElement.style.display = 'none';		  
		}
	}  
}