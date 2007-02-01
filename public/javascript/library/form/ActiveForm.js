ActiveForm = Class.create();
ActiveForm.prototype = {

    showNewItemForm: function(link, form) 
    {
        link.style.display = 'none';
        
        if(BrowserDetect.browser != 'Explorer')
        {
            Effect.BlindDown(form.id, {duration: 0.3});
            Effect.Appear(form.id, {duration: 0.66});
    
                setTimeout(function() {  
                form.style.height = 'auto'; 
            }, 0.7);
        }
        else
        {
            form.style.display = 'block'; 
        }
    },
    
    
    hideNewItemForm: function(link, form)
    {
        if(BrowserDetect.browser != 'Explorer')
        {
            Effect.Fade(form.id, {duration: 0.2});
            Effect.BlindUp(form.id, {duration: 0.3});
    
            setTimeout(function() { link.style.display = 'block'; }, 0.3);
        }
        else
        {
            link.style.display = 'block';
            form.style.display = 'none';
        }
    },
    
    generateHandle: function(title)
    {
		handle = title.toLowerCase();  
		
		handle = handle.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,""); // trim
		handle = handle.replace(/[^a-z_\d \.]/g, ""); // remove all illegal simbols
		// handle = handle.replace(/^[\d\_]+/g, "."); // replace first digits with "."
		handle = handle.replace(/ /g, "."); // replace spaces with "."

		// replace repeating dots with one
		var oldHandle = '';
		while (oldHandle != handle) 
		{
		  	oldHandle = handle;
		  	handle = handle.replace(/\.\./g, ".");
		}		 
		
		// replace leading and ending dots
		handle = handle.replace(/^\./g, "");
		handle = handle.replace(/\.$/g, "");
				       
        return handle;
    },
    
    /**
     * Set feedback message near the field
     *
     * @param HTMLInputElement|HTMLSelectElement|HTMLTextareaElement field
     * @param string value Feedback message
     *
     */
    setFeedback: function(field, value)
    {
         var feedback = document.getElementsByClassName('feedback', field.parentNode)[0];

        try
        {
            feedback.firstChild.nodeValue = value;
        }
        catch(e)
        {
            feedback.appendChild(document.createTextNode(value))
        }

        feedback.style.visibility = 'visible';
    },
    
    onProgress: function(form) 
    {
        var progress = document.getElementsByClassName('activeForm_progress', $(form))[0];
        
        var img = progress.getElementsByTagName("img")[0];
        if(!img) 
        {
            img = document.createElement("img");
            img.src = 'image/indicator.gif';
            progress.style.paddingRight = "inherit";
            progress.appendChild(img);
        }
        
        img.style.visibility = "visible";
    },
    
    offProgress: function(form) 
    {
        var progress = document.getElementsByClassName('activeForm_progress', $(form))[0];
        
        var img = progress.getElementsByTagName("img")[0];      
        if(img) img.style.visibility = "hidden";
    }
}