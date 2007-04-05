/*****************************
    Product related JS
*****************************/
Product = {}

Product.ImageHandler = Class.create();
Product.ImageHandler.prototype = 
{
	initialize: function(imageData)
	{
		imageData.each(function(pair)
		{
    		if ($('img_' + pair.key))
    		{
    			new Product.ImageSwitcher(pair.key, pair.value);
    		}
		});
	}
}

Product.ImageSwitcher = Class.create();
Product.ImageSwitcher.prototype = 
{
	id: 0,
	
	imageData: null,
	
	initialize: function(id, imageData)
	{        
        this.id = id;
		this.imageData = imageData;
			
		$('img_' + id).onclick = this.switchImage.bind(this);
	},
	
	switchImage: function()
	{
		$('mainImage').src = this.imageData[3];
	}
}

/*****************************
    User related JS
*****************************/
User = {}

User.StateSwitcher = Class.create();
User.StateSwitcher.prototype = 
{
    countrySelector: null, 
    stateSelector: null, 
    stateTextInput: null,
    url: '',
    
    initialize: function(countrySelector, stateSelector, stateTextInput, url)
    {
        this.countrySelector = countrySelector;
        this.stateSelector = stateSelector;
        this.stateTextInput = stateTextInput;        
        this.url = url;
        Event.observe(countrySelector, 'change', this.updateStates.bind(this)); 
    },
    
    updateStates: function(e)
    {
        var url = this.url + '/?country=' + this.countrySelector.value;
        new Ajax.Request(url, {onComplete: this.updateStatesComplete.bind(this)});    
    },
    
    updateStatesComplete: function(ajaxRequest)
    {
        console.log(ajaxRequest.responseText);    
    }   
    
}