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
    }
}