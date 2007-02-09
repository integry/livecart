if(!Backend) var Backend = {};
if(!Backend.SpecField) Backend.SpecField = {};



/**
 * Specification field controller
 * @b:controller
 */
Backend.SpecField.Controller = Class.create();
Backend.SpecField.Controller.prototype = {   
    /**
     * Create new controller instance
     * 
     * @note: When creating new controller pass parent node to limit searchable dom space
     * 
     * @param HTMLElement parentNode
     * @constructor
     */
    _initialize: function()
    {        
        this._model = new Backend.SpecField.Model();
        
        this._view.generateTranslations(this._model.getLanguages());
    },
    
    /**
     * Save specification field action
     * 
     * @note: Use 
     *         <code>
     *             var someLiElement = $$('li')[0];
     *             Event.observe(element, 'click', function(e) { Backend.SpecField.Controller.save(e, {'li': someLiElement}); });
     *             Event.observe(element, 'click', function(e) { Backend.SpecField.Controller.generateTitle(e, {'li': someLiElement}); }); // Second action on same event
     *         </code>
     *         This way you will always have real Event object in IE, Firefox and Opera.
     *         Also this way you can set multiple actions on the same event
     * 
     * @param Event e Event object
     * @param Object args
     */
    submit: function(e, args)
    {
        Event.stop(e); // You can choose prevent default action like submitting form by using Event.stop(e);
        this._model.sendSaveRequest(args)
    },
    
    changeTitle: function(e, args)
    {
        var newDescriptionText = this._model.changeTitle({'name': args.name.value});
        this._view.updateTitle(e, $H(args).merge({newDescriptionText: newDescriptionText}));
    },
}





/**
 * @b:view
 */
Backend.SpecField.View = Class.create();
Backend.SpecField.View.prototype = {
    _findNodes: function()
    {
        /**
         * @note: When possible search only inside parrent this.nodes.parent. This will improve efficiency
         * @note: Use tag name on the end of the variable
         */
        this.nodes.translationsTemplateFieldset = $$("fieldset.translation_template")[0];
        this.nodes.defaultNameInput             = this.nodes.parent.down(".default_name").down("input");
        this.nodes.defaultDescriptionTextarea   = this.nodes.parent.down(".default_description").down("textarea");
        this.nodes.translationsFieldset         = this.nodes.parent.down(".translations");
        this.nodes.specFieldForm                = this.nodes.parent.down("form");
    },
    
    _bindNodes: function()
    {
        var self = this; // You can't use this inside each loop but you can assign a reference to this object to other variable, like self
        Event.observe(this.nodes.defaultNameInput, 'keyup', function(e) { self._controller.changeTitle(e, {'name': this, 'description': self.nodes.defaultDescriptionTextarea}); });
        Event.observe(this.nodes.specFieldForm, 'submit', function(e) { self._controller.submit(e, {values: Form.serialize(self.nodes.specFieldForm) }); });
    },
    
    generateTranslations: function(args)
    {
        var self = this; 
        $H(args.languages).each(function(language) 
        {
            var clonedTranslationsFieldset = self.nodes.translationsTemplateFieldset.cloneNode(true); // Remember? Use tag name on the end of variable
            Element.removeClassName(clonedTranslationsFieldset, 'dom_template');
            Element.removeClassName(clonedTranslationsFieldset, 'translation_template');
            
            var languageLegend = clonedTranslationsFieldset.down('legend');
            var nameInput = clonedTranslationsFieldset.down('.name').down('input');
            var descriptionInput = clonedTranslationsFieldset.down('.description').down('textarea');
            
            languageLegend.update(language.value);
            nameInput.name += "[" + language.key + "]";  
            descriptionInput.name += "[" + language.key + "]";
            
            /**
             * Say we want to change description when we change title
             * 
             * @note: Note `this` here is element wich triggered the event
             * @note: We are reusing See we are reusing changeTitle action
             */
            Event.observe(nameInput, 'keyup', function(e) { self._controller.changeTitle(e, {'name': this, 'description': descriptionInput}); });
            
            self.nodes.translationsFieldset.appendChild(clonedTranslationsFieldset);
        });
    },
    
    updateTitle: function(e, args)
    {
        args.description.update(args.newDescriptionText);
    }
}




/**
 * @b:model
 */
Backend.SpecField.Model = Class.create();
Backend.SpecField.Model.prototype = {
    initialize: function()
    {
    },
    
    changeTitle: function(args)
    {
        return args.name;
    },
    
    sendSaveRequest: function(args)
    {
        new Ajax.Request("someurl?" + args.values);
    },
    
    getLanguages: function()
    {
        return {
            'languages': {
                'en': "English",
                'lt': "Lithuanian",
                'de': "Deutch"
            }
        };
    }
}

/**
 * Extend objects
 */
Object.extend(Backend.SpecField.View.prototype, View.prototype);
Object.extend(Backend.SpecField.Controller.prototype, Controller.prototype);