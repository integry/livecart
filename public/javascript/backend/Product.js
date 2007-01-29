Backend.Product = 
{
	generateHandle: function(titleElement)
	{
	  	handleElement = titleElement.form.elements.namedItem('handle');
	  	handleElement.value = ActiveForm.prototype.generateHandle(titleElement.value);
	}  
}