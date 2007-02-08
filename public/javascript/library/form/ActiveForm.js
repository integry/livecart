/**
 * ActiveForm will most likely work in pair with ActiveList. While ActiveList handles ActiveRecords ActiveForm handles new instances, which are not yet saved in database. 
 * 
 * It's main feature is to show/hide the new form and the link to this form. It allso show/hide 
 * the progress indicator for new forms and generates valid handle from title
 * 
 * @author Sergej Andrejev <sandrejev@gmail.com>
 */
ActiveForm = Class.create();
ActiveForm.prototype = {
    /**
     * Show form and hide "Show this form" link
     * @param HTMLElement link
     * @param HTMLElement form Form should have display block set to use animation. In other case you should pass div instead of form.
     * @param boolean animate If true or not passed then try to animate this action, else just hide link and show form
     */
    showNewItemForm: function(link, form, animate) 
    {
        animate = (undefined == animate ? false : animate);
        
        if(link) link.style.display = 'none';
        
        if(animate && BrowserDetect.browser != 'Explorer')
        {             
            if(form) 
            {
                Effect.BlindDown(form, {duration: 0.3});
                Effect.Appear(form, {duration: 0.66});
                
                setTimeout(function() { 
                    form.style.display = 'block'; 
                    form.style.height = 'auto';
                }, 700);
            }
            
            if(link) 
            {
                setTimeout(function()
                { 
                    link.style.display = 'none';  
                }, 700);
            }
        }
        else
        {
            if(form) form.style.display = 'block'; 
        }
    },
    
    /**
     * Show "Show this form" link and hide form
     * 
     * @param HTMLElement link
     * @param HTMLElement form Form should have display block set to use animation. In other case you should pass div instead of form.
     * @param boolean animate If true or not passed then try to animate this action, else just hide link and show form
     */
    hideNewItemForm: function(link, form, animate)
    {
        animate = (undefined == animate ? false : animate);
        
        if(animate && BrowserDetect.browser != 'Explorer')
        {
            if(form) 
            {
                Effect.Fade(form, {duration: 0.2});
                Effect.BlindUp(form, {duration: 0.3});
                setTimeout(function() { form.style.display = 'none'; }, 300);   
            }
            
            if(link) 
            {
                setTimeout(function() { link.style.display = 'inline'; }, 300);   
            }
        }
        else
        {
            if(link) link.style.display = 'inline';
            if(form) form.style.display = 'none';
        }
    },
    
    /**
     * Generate valid handle from item title
     * 
     * @param string title Input title
     * @return string valid handle
     */
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
    
    /**
     * Turn on progress indicator in the form.
     * 
     * Note: to use this method you should place empty div/span tag inside your form with class "activeForm_progress"
     * 
     * @param HTMLElement form
     */
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
    
    /**
     * Turn off progress indicator in the form.
     * 
     * Note: to use this method you should place empty div/span tag inside your form with class "activeForm_progress"
     * 
     * @param HTMLElement form
     */
    offProgress: function(form) 
    {
        var progress = document.getElementsByClassName('activeForm_progress', $(form))[0];
        
        var img = progress.getElementsByTagName("img")[0];      
        if(img) img.style.visibility = "hidden";
    }
}