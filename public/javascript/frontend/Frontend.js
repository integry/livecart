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
