/**
 *	@author Integry Systems
 */


/*****************************
    Product related JS
*****************************/
Product = {}

Product.ImageHandler = Class.create();
Product.ImageHandler.prototype =
{
	initialize: function(imageData, imageDescr)
	{
		imageData.each(function(pair)
		{
    		if ($('img_' + pair.key))
    		{
    			new Product.ImageSwitcher(pair.key, pair.value, imageDescr[pair.key]);
    		}
		});
	}
}

Product.ImageSwitcher = Class.create();
Product.ImageSwitcher.prototype =
{
	id: 0,

	imageData: null,
	imageDescr: null,

	initialize: function(id, imageData, imageDescr)
	{
        this.id = id;
		this.imageData = imageData;
		this.imageDescr = imageDescr;

		$('img_' + id).onclick = this.switchImage.bind(this);
	},

	switchImage: function()
	{
		$('mainImage').src = this.imageData[3];

        if ($('imageDescr'))
        {
            $('imageDescr').innerHTML = this.imageDescr;
        }

        var lightBox = $('largeImage').down('a');
        lightBox.href = this.imageData[4];
        lightBox.title = this.imageDescr;
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

        if (this.stateSelector.length > 0)
        {
            Element.show(this.stateSelector);
            Element.hide(this.stateTextInput);
        }

        Event.observe(countrySelector, 'change', this.updateStates.bind(this));
    },

    updateStates: function(e)
    {
        var url = this.url + '/?country=' + this.countrySelector.value;
        new Ajax.Request(url, {onComplete: this.updateStatesComplete.bind(this)});

        var indicator = document.getElementsByClassName('progressIndicator', this.countrySelector.parentNode);
        if (indicator.length > 0)
        {
            this.indicator = indicator[0];
            Element.show(this.indicator);
        }

        this.stateSelector.length = 0;
        this.stateTextInput.value = '';
    },

    updateStatesComplete: function(ajaxRequest)
    {
        eval('var states = ' + ajaxRequest.responseText);

        if (0 == states.length)
        {
            Element.hide(this.stateSelector);
            Element.show(this.stateTextInput);
            this.stateTextInput.focus();
        }
        else
        {
            this.stateSelector.options[this.stateSelector.length] = new Option('', '', true);

            Object.keys(states).each(function(key)
            {
                if (!isNaN(parseInt(key)))
                {
                    this.stateSelector.options[this.stateSelector.length] = new Option(states[key], key, false);
                }
            }.bind(this));
            Element.show(this.stateSelector);
            Element.hide(this.stateTextInput);

            this.stateSelector.focus();
        }

        if (this.indicator)
        {
            Element.hide(this.indicator);
        }
    }
}

User.ShippingFormToggler = Class.create();
User.ShippingFormToggler.prototype =
{
    checkbox: null,
    container: null,

    initialize: function(checkbox, container)
    {
        this.checkbox = checkbox;
        this.container = container;

        Event.observe(this.checkbox, 'change', this.handleChange.bindAsEventListener(this));
        Event.observe(this.checkbox, 'click', this.handleChange.bindAsEventListener(this));

        this.handleChange(null);
    },

    handleChange: function(e)
    {
        if (this.checkbox.checked)
        {
            Element.hide(this.container);
        }
        else
        {
            Element.show(this.container);
        }
    }
}