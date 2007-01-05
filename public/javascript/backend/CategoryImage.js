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
			this.addToList(categoryId, result, true);		  
			$('catImgAdd_' + categoryId).style.display = 'none';
			$('catImgMenu_' + categoryId).style.display = 'block';
			this.initActiveList(categoryId);
		}
	},
	
	initList: function(categoryId, imageList)
	{
		for (k = 0; k < imageList.length; k++)
		{
		  	this.addToList(categoryId, imageList[k]);
		}  
		this.initActiveList(categoryId);
	},
	
	initActiveList: function(categoryId)
	{
		new ActiveList('catImageList_' + categoryId, {
	         beforeEdit:     function(li) {window.location.href = lng.editUrl + '/' + this.getRecordId(li); },
	         beforeSort:     function(li, order) 
			 { 
				 return lng.sortUrl + '?draggedId=' + this.getRecordId(li) + '&' + order 
			   },
	         beforeDelete:   function(li)
	         {
	             if(confirm(lng.delConfirmMsg)) return lng.deleteUrl + '/' + this.getRecordId(li)
	         },
	         afterEdit:      function(li, response) {  },
	         afterSort:      function(li, response) {  },
	         afterDelete:    function(li, response)  { Element.remove(li); }
	     });
	},
	
	addToList: function(categoryId, imageData, highLight)
	{
		var templ = $('tabImagesContent_' + categoryId).getElementsByClassName('catImageTemplate')[0].cloneNode(true);
	  		  	
	  	image = templ.getElementsByTagName('img')[0];
		image.src = imageData['paths'][0];
	  	image.imageData = imageData;
	  	image.onclick = 
			function() 
			{ 
				for (k = 0; k < this.imageData['paths'].length; k++) 
				{ 
					if (this.src.substr(this.src.length - this.imageData['paths'][k].length, this.imageData['paths'][k].length) == this.imageData['paths'][k])
					{
						var currentImg = k;
					}  
				}

				var nextImg = currentImg + 1;

				if (nextImg >= this.imageData['paths'].length)
				{
					nextImg = 0;  	
				} 


				this.src = this.imageData['paths'][nextImg];
			}

	  	$('catImageList_' + categoryId).appendChild(templ);
	  	
	  	if (highLight)
	  	{
			new Effect.Highlight(templ, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});		    
		}
	}  
}