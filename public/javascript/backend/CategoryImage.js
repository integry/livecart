Backend.CategoryImage = Class.create();

Backend.CategoryImage.prototype = 
{
	sortUrl: false,

	deleteUrl: false,

	editUrl: false,
	
	saveUrl: false,
				
	delConfirmMsg: '',

	editCaption: '',
	
	saveCaption: '',
	
	initialize: function()
	{	  

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
		var id = 'catImageList_' + categoryId;
		
		// display message if no images are uploaded
		this.showNoImagesMessage(categoryId);

		ActiveList.prototype.getInstance(id, {
	         
			 beforeEdit:     function(li) 
			 {
				 var recordId = this.getRecordId(li).split('_')[1];	
				 var categoryId = this.getRecordId(li).split('_')[0];	
				 

				 var form = $('catImgAdd_' + categoryId).getElementsByTagName('form')[0].cloneNode(true);
				 
				 form.action = Backend.Category.image.saveUrl;
				 
				 form.elements.namedItem('imageId').value = recordId;
				 
				 form.getElementsByTagName('legend')[0].innerHTML = Backend.Category.image.editCaption;
				 
				 form.elements.namedItem('upload').value = Backend.Category.image.saveCaption;
				 
				 legends = form.getElementsByTagName('legend');
				 for (k = 0; k < legends.length; k++)
				 {
				 	expanderIcon = document.getElementsByClassName('expandIcon', legends[k]);
					if (expanderIcon.length > 0)
					{
  				    	expanderIcon[0].parentNode.removeChild(expanderIcon[0]);
					} 
				 }
				 
				 imageData = document.getElementsByClassName('catImage', li)[0].imageData;
				 for (k in imageData)
				 {
					if (k.substr(0, 5) == 'title')
					{
					  	if (form.elements.namedItem(k))
					  	{
							form.elements.namedItem(k).value = imageData[k];    
						}
					}   
				 }
				 
				 form.getElementsByTagName('a')[0].onclick = 
					function()
					{
						var formNode = this.parentNode;
						while (formNode.tagName != 'FORM')
						{
							formNode = formNode.parentNode;							  
						}
						
						formNode.reset();
						Effect.SlideUp(formNode, {duration: 0.1});

						return false;
					}
				 
				 var editCont = document.getElementsByClassName('activeList_editContainer', li)[0];
				 
				 while (editCont.firstChild)
				 {
				 	editCont.removeChild(editCont.firstChild);
				 }
				 			 
				 editCont.style.display = 'none';
				 editCont.appendChild(form);
				 
				 var expander = new SectionExpander();				 
				 
				// Effect.Appear(editCont, {duration: 0.2});
                 
                 this.toggleContainerOn(editCont)
			 },
	         
			 beforeSort:     function(li, order) 
			 { 
				 var recordId = this.getRecordId(li).split('_')[1];	
				 var categoryId = this.getRecordId(li).split('_')[0];	
				 return Backend.Category.image.sortUrl + '?categoryId=' + categoryId + '&draggedId=' + recordId + '&' + order 
			 },
	         
			 beforeDelete:   function(li)
	         {				 	
				 var recordId = this.getRecordId(li).split('_')[1];	
				 
				 if(confirm(Backend.Category.image.delConfirmMsg)) 
				 {
					 return Backend.Category.image.deleteUrl + '/' + recordId;
				 }
	         },
	         afterEdit:      function(li, response) {  },
	         afterSort:      function(li, response) {  },
	         afterDelete:    function(li, response)  
			 { 
    	 	 	var categoryId = this.getRecordId(li).split('_')[0];
    	 	 	
				Element.remove(li); 
				CategoryTabControl.prototype.resetTabItemsCount(categoryId);
                
				Backend.Category.image.showNoImagesMessage(categoryId);			   	
			 }
	     },
         
         this.activeListMessages
         );
	},
	
	showNoImagesMessage: function(categoryId)
	{
		// display message if no images are uploaded
		$('catNoImages_' + categoryId).style.display = ($('catImageList_' + categoryId).childNodes.length > 0) ? 'none' : 'block';	 	 
	},
	
	createEntry: function(categoryId, imageData)
	{
		var templ = document.getElementsByClassName('catImageTemplate', $('tabImagesContent_' + categoryId))[0].cloneNode(true);
	  		  	
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

	  	templ.id = 'catImageListItem' + categoryId + '_' + imageData['ID'];

		if (imageData['title'])
		{
			document.getElementsByClassName('catImageTitle', templ)[0].innerHTML = imageData['title'];		  
		}
		
		return templ;	  
	},
	
	addToList: function(categoryId, imageData, highLight)
	{
		var templ = this.createEntry(categoryId, imageData);
		$('catImageList_' + categoryId).appendChild(templ);
	  	
	  	if (highLight)
	  	{
			new Effect.Highlight(templ, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});		    
		}
	},
	
	updateEntry: function(categoryId, imageData, highLight)
	{
	  	// force image reload
	  	var timeStamp = new Date().getTime();
		for(k = 0; k < imageData['paths'].length; k++)
	  	{
			imageData['paths'][k] += '?' + timeStamp;
		}

		var templ = this.createEntry(categoryId, imageData);
		var entry = $('catImageListItem' + categoryId + '_' + imageData['ID']);
	  	  	
	  	entry.parentNode.replaceChild(templ, entry);
	  	
	  	if (highLight)
	  	{
			new Effect.Highlight(templ, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});		    
		}
	},

	upload: function(form)
	{
		categoryId = form.elements.namedItem('catId').value;
		errorElement = document.getElementsByClassName('errorText', $('catImgAdd_' + categoryId))[0];
		errorElement.style.display = 'none';		  

		return false;
	},
	
	postUpload: function(categoryId, result)
	{
		errorElement = document.getElementsByClassName('errorText', $('catImgAdd_' + categoryId))[0];
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
            
            CategoryTabControl.prototype.resetTabItemsCount(categoryId);
		}
	},

	postSave: function(categoryId, imageId, result)
	{
		var entry = $('catImageListItem' + categoryId + '_' + imageData['ID']);
		errorElement = document.getElementsByClassName('errorText', entry)[0];
		if (result['error'])  	
		{
			errorElement.innerHTML = result['error'];
			Effect.Appear(errorElement, {duration: 0.4});
		}
		else
		{
			errorElement.style.display = 'none';
			this.updateEntry(categoryId, result, true);		  
			entry.getElementsByTagName('form')[0].style.display = 'none';
			this.initActiveList(categoryId);
		}
	},
	
	
	setSortUrl: function(url)
	{
	  	this.sortUrl = url;
	},
	
	setDeleteUrl: function(url)
	{
	  	this.deleteUrl = url;
	},

	setEditUrl: function(url)
	{
	  	this.editUrl = url;
	},

	setSaveUrl: function(url)
	{
	  	this.saveUrl = url;
	},

	setEditCaption: function(message)
	{
	  	this.editCaption = message;
	},

	setSaveCaption: function(message)
	{
	  	this.saveCaption = message;
	},

	setDeleteMessage: function(message)
	{
	  	this.delConfirmMsg = message;
	}
}