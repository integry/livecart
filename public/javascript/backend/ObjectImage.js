Backend.ObjectImage = Class.create();

Backend.ObjectImage.prototype = 
{
	container: null,
	addForm: null,	
	addMenu: null,	
		
	ownerID: null,
	
	sortUrl: false,
	deleteUrl: false,
	editUrl: false,
	saveUrl: false,
				
	delConfirmMsg: '',
	editCaption: '',
	saveCaption: '',
	
	prefix: '',
	
	initialize: function(container, prefix)
	{	  
		this.container = container;
		this.container.handler = this;
		
		this.ownerID = ActiveList.prototype.getRecordId(container);
		this.prefix = prefix;
		
		this.addForm = $(this.prefix + 'ImgAdd_' + this.ownerID);
		this.addMenu = $(this.prefix + 'ImgMenu_' + this.ownerID);
	},
	
	initList: function(imageList)
	{
		for (k = 0; k < imageList.length; k++)
		{
		  	this.addToList(imageList[k]);
		}  
		                
        this.arrangeImages();                
        this.initActiveList();
	},
    
    arrangeImages: function()
    {
        var images = this.container.getElementsByTagName('li');
        var mainP = this.container.getElementsByTagName('p')[0];
		var supplementalP = this.container.getElementsByTagName('p')[1];
                
    	var firstli = images[0];
    		
        if (firstli)
        {
            // move first image under "Main Image"
            if (mainP.nextSibling == supplementalP)
            {
                firstli.parentNode.insertBefore(firstli, mainP.nextSibling);            
            }    
            
            while ('LI' == firstli.nextSibling.tagName)
            {
                firstli.parentNode.insertBefore(firstli.nextSibling, supplementalP.nextSibling);   
            }            
        }
        
        supplementalP.style.display = images.length < 2 ? 'none' : '';
        mainP.style.display = images.length == 0 ? 'none' : '';
    },    
        
	initActiveList: function()
	{
		// display message if no images are uploaded
		this.showNoImagesMessage();

		new ActiveList(this.container, {
	         
			 beforeEdit:     function(li) 
			 {
				 var recordId = this.getRecordId(li);	
				 var ownerId = this.getRecordId(li.parentNode);	
					
				 var handler = li.parentNode.handler;	
				
    			 var uploadForm = $(handler.prefix + 'ImgAdd_' + handler.ownerID).getElementsByTagName('form')[0];
    			 uploadForm.reset();
				 var form = uploadForm.cloneNode(true);            
				 
				 form.action = handler.saveUrl;
				 onsubm = function(e) {var form = Event.element(e); this.showProgressIndicator(form); }
				 form.onsubmit = onsubm.bindAsEventListener(handler);
				 
				 form.elements.namedItem('imageId').value = recordId;
				 
				 Element.addClassName(form.getElementsByTagName('fieldset')[0], 'container');
				 
				 form.getElementsByTagName('legend')[0].innerHTML = handler.editCaption;
				 
				 form.elements.namedItem('upload').value = handler.saveCaption;
				 
				 legends = form.getElementsByTagName('legend');
				 for (k = 0; k < legends.length; k++)
				 {
				 	expanderIcon = document.getElementsByClassName('expandIcon', legends[k]);
					if (expanderIcon.length > 0)
					{
  				    	expanderIcon[0].parentNode.removeChild(expanderIcon[0]);
					} 
				 }
				 
				 imageData = document.getElementsByClassName('image', li)[0].imageData;
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
				 
				// Effect.Appear(editCont, {duration: 0.2});
                 
                 this.toggleContainerOn(editCont);
                 
                 new Backend.LanguageForm();
			 },
	         
			 beforeSort:     function(li, order) 
			 { 
				 var recordId = this.getRecordId(li);	
				 var ownerId = this.getRecordId(li.parentNode);	
				 return li.parentNode.handler.sortUrl + '?ownerId=' + ownerId + '&draggedId=' + recordId + '&' + order 
			 },
	         
			 beforeDelete:   function(li)
	         {				 	
				 var recordId = this.getRecordId(li);	
				 if(confirm(li.parentNode.handler.delConfirmMsg)) 
				 {
					 return li.parentNode.handler.deleteUrl + '/' + recordId;
				 }
	         },
	         afterEdit:      function(li, response) {  },
	         afterSort:      function(li, response) { li.parentNode.handler.arrangeImages(); },
	         afterDelete:    function(li, response)  
			 { 
    	 	 	
				Element.remove(li); 
				
				//CategoryTabControl.prototype.resetTabItemsCount(this.getRecordId(li.parentNode));
                
				li.parentNode.handler.showNoImagesMessage();			   	
				
				li.parentNode.handler.arrangeImages();		
			 }
	     },
         
         this.activeListMessages
         );
	},
	
	showProgressIndicator: function(form)
	{
		var inst = document.getElementsByClassName('progressIndicator', form)[0];
		Element.show(inst);	
	},
	
	hideProgressIndicator: function(form)
	{
		var inst = document.getElementsByClassName('progressIndicator', form)[0];
		Element.hide(inst);	
	},

	showNoImagesMessage: function()
	{
		// display message if no images are uploaded
		document.getElementsByClassName('noRecords', this.container.parentNode)[0].style.display = (this.container.childNodes.length > 0) ? 'none' : 'block';	 	 
	},
	
	createEntry: function(imageData)
	{
		var templ = document.getElementsByClassName('imageTemplate', this.container.parentNode)[0].cloneNode(true);
	  		  	
	  	image = templ.getElementsByTagName('img')[0];
		image.src = imageData['paths'][1];
	  	image.imageData = imageData;
	  	image.onclick = 
			function() 
			{ 
                for (k in this.imageData['paths']) 
				{ 
                    if (this.src.substr(this.src.length - this.imageData['paths'][k].length, this.imageData['paths'][k].length) == this.imageData['paths'][k])
					{
						var currentImg = k;
					}  
       			}

				var nextImg = parseInt(currentImg) + 1;

				if (!this.imageData['paths'][nextImg])
				{
					nextImg = 1;  	
				} 

				this.src = this.imageData['paths'][nextImg];
			}

	  	templ.id = this.__createElementID(imageData['ID']);

		if (imageData['title'])
		{
			document.getElementsByClassName('imageTitle', templ)[0].innerHTML = imageData['title'];		  
		}
		
		return templ;	  
	},
	
	addToList: function(imageData, highLight)
	{
		var templ = this.createEntry(imageData);
		this.container.appendChild(templ);
	  	
	  	if (highLight)
	  	{
			new Effect.Highlight(templ, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});		    
		}
	},
	
	updateEntry: function(imageData, highLight)
	{
	  	// force image reload
	  	var timeStamp = new Date().getTime();
		for(k = 0; k < imageData['paths'].length; k++)
	  	{
			imageData['paths'][k] += '?' + timeStamp;
		}

		var templ = this.createEntry(imageData);
		var entry = $(this.__createElementID(imageData['ID']));
	  	  	
	  	entry.parentNode.replaceChild(templ, entry);
	  	
	  	if (highLight)
	  	{
			new Effect.Highlight(templ, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});		    
		}
	},

	upload: function(form)
	{
		errorElement = document.getElementsByClassName('errorText', this.addForm)[0];
		errorElement.style.display = 'none';
		this.showProgressIndicator(this.addForm);
        
        if(form.action.match(/random=/))
        {
            form.action.replace(/random=/, 'random=' + "random=" + Math.random() * 100000)
        }
        else
        {
            if(form.action.match(/\?/))
            {
                form.action += '&';    
            }
            else
            {
                form.action += '?';
            }
            
            form.action += "random=" + Math.random() * 100000
        }
        
		return false;
	},
	
	postUpload: function(result)
	{
		var errorElement = document.getElementsByClassName('errorText', this.addForm)[0];
		if (result['error'])  	
		{
			errorElement.innerHTML = result['error'];
			Element.removeClassName(errorElement, 'hidden');
			Effect.Appear(errorElement, {duration: 0.4});
		}
		else
		{
			errorElement.style.display = 'none';
			this.addToList(result, true);		  
			this.addForm.style.display = 'none';
			this.addMenu.style.display = 'block';
			this.initActiveList();
            
            CategoryTabControl.prototype.resetTabItemsCount(this.ownerID);
            
            this.arrangeImages();
		}
	},

	postSave: function(imageId, result)
	{
		var entry = $(this.__createElementID(imageData['ID']));
		this.hideProgressIndicator(entry);
		errorElement = document.getElementsByClassName('errorText', entry)[0];
		if (result['error'])  	
		{
			errorElement.innerHTML = result['error'];
			Effect.Appear(errorElement, {duration: 0.4});
		}
		else
		{
			errorElement.style.display = 'none';
			this.updateEntry(result, true);		  
			entry.getElementsByTagName('form')[0].style.display = 'none';
			this.initActiveList();
		}
	},
	
	__createElementID: function(id)
	{
		return this.prefix + 'image_' + id;		
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