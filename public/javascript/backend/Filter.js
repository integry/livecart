/**
 * Backend.Filter
 *
 * This class manages filters forms
 *
 * Create object by passing json to constructor
 * @example
 * <code>
 *     new Backend.Filter({
 *         'ID': 15
 *         'ID': 15
 *         'name': {'lt': 'Pagal dydi'}
 *         'rootId': 'filter_item_new_41_form'
 *         'categoryID': 41
 *         'specFields: { // SpecFieldArray in json // } 
 *     });
 * </code>
 *
 * You should also modify prototype by passing settins to it
 * 
 * @example
 * <code>
 *   Backend.Filter.prototype.links = {};
 *   Backend.Filter.prototype.links.deleteGroup = '/en/backend.filter/delete/';
 *   Backend.Filter.prototype.links.editGroup = '/en/backend.filter/item/';
 *   Backend.Filter.prototype.links.sortGroup = '/en/backend.filter/sort/';
 *   Backend.Filter.prototype.links.deleteFilter = '/en/backend.filter/deleteFilter/';
 *   Backend.Filter.prototype.links.sortFilter = '/en/backend.filter/sortFilter/';
 *   Backend.Filter.prototype.links.generateFilters = '/en/backend.filter/generateFilters/';
 *   
 *   Backend.Filter.prototype.languages = {"en":"English","lt":"Lithuanian","lv":"Latvian"};
 *   Backend.Filter.prototype.messages = {"deleteField":"delete field"};
 *   Backend.Filter.prototype.selectorValueTypes = [1,5];
 *   Backend.Filter.prototype.countNewFilters = 0;
 *   Backend.Filter.prototype.typesWithNoFiltering = [];
 *   Backend.Filter.prototype.dateFormat = "%d-%b-%Y";
 * </code>
 *
 * @version 1.0
 * @author Sergej Andrejev
 */
if (Backend == undefined)
{
    var Backend = {}
}

Backend.Filter = Class.create();
Backend.Filter.prototype = {
    cssPrefix: "filter_",
    countNewFilters: 0,
    
    /**
     * Constructor
     *
     * @param filtersJson Spec Field filters
     * @param hash If true the passed filter is an object. If hash is not passed or false then filterJson will be assumed as a string
     *
     * @access public
     */
    initialize: function(filterJson, hash)
    {
        this.filter = !hash ? eval("(" + filterJson + ")" ) : filterJson;
        this.cloneForm('filter_item_blank', this.filter.rootId);

        this.id = this.filter.ID;
        
        this.categoryID = this.filter.categoryID;
        this.rootId = this.filter.rootId;
        this.specFields = this.filter.specFields;
        this.name = this.filter.name;
        this.filters = this.filter.filters;
        this.backupName = this.name;
        
        this.filterCalendars = {};

        this.loadLanguagesAction();
        this.findUsedNodes();
        this.bindFields();
    },

    /**
     * This function destroys the old filter group form, then clones the prototype and then calls constructor once again
     *
     * @param object filterJson Filter group form values
     *
     * @access public
     */
    recreate: function(filterJson)
    {
        var root = ($(this.filter.rootId).tagName.toLowerCase() == 'li') ?  window.activeFiltersList[this.categoryID].getContainer($(this.filter.rootId), 'edit') : $(this.filter.rootId);
        root.innerHTML = '';
        $H(this).each(function(el)
        {
            el = false;
        });
        
        this.initialize(filterJson, hash);
        this.clearAllFeedBack();
    },


    /**
     * Create a clone of form from prototype form
     *
     * @param prototypeId Id of root prototype element
     * @param rootId Id of root element where the copy of prototype will be copied
     *
     * @access private
     */
    cloneForm: function(prototypeId, rootId)
    {
        var blankForm = $(prototypeId);
        var blankFormFilters = blankForm.getElementsByTagName("*");
        var root = ($(this.filter.rootId).tagName.toLowerCase() == 'li') ?  window.activeFiltersList[this.filter.categoryID].getContainer($(this.filter.rootId), 'edit') : $(this.filter.rootId);

        for(var i = 0; i < blankFormFilters.length; i++)
        {
            if(blankFormFilters[i] && blankFormFilters[i].parentNode == blankForm)
            {
                root.appendChild(blankFormFilters[i].cloneNode(true));
            }
        }
    },


    /**
     * Find ussed nodes
     *
     * @access private
     */
    findUsedNodes: function()
    {
        if(!this.nodes) this.nodes = [];

        this.nodes.parent = document.getElementById(this.rootId);

        this.nodes.form                   = this.nodes.parent.getElementsByTagName("form")[0];

        this.nodes.id                     = document.getElementsByClassName(this.cssPrefix + "form_id", this.nodes.parent)[0];
        this.nodes.name                   = document.getElementsByClassName(this.cssPrefix + "form_name", this.nodes.parent)[0];
        this.nodes.specFieldID            = document.getElementsByClassName(this.cssPrefix + "form_specFieldID", this.nodes.parent)[0];

        this.nodes.stepTranslations       = document.getElementsByClassName(this.cssPrefix + "step_translations", this.nodes.parent)[0];
        this.nodes.stepMain               = document.getElementsByClassName(this.cssPrefix + "step_main", this.nodes.parent)[0];
        this.nodes.stepValues             = document.getElementsByClassName(this.cssPrefix + "step_filters", this.nodes.parent)[0];
        this.nodes.stepLevOne             = document.getElementsByClassName(this.cssPrefix + "step_lev1", this.nodes.parent);
        this.nodes.generateFiltersLink    = document.getElementsByClassName(this.cssPrefix + "generate_filters", this.nodes.parent)[0];

        for(var i = 0; i < this.nodes.stepLevOne.length; i++)
        {
            if(!this.nodes.stepLevOne[i].id) this.nodes.stepLevOne[i].id = this.nodes.stepLevOne[i].className.replace(/ /, "_") + "_" + this.id;
        }

        this.nodes.mainTitle              = document.getElementsByClassName(this.cssPrefix + "title", this.nodes.parent)[0];
        this.nodes.stateLinks             = document.getElementsByClassName(this.cssPrefix + "change_state", this.nodes.parent);
        this.nodes.cancel                 = document.getElementsByClassName(this.cssPrefix + "cancel", this.nodes.parent)[0];
        this.nodes.save                   = document.getElementsByClassName(this.cssPrefix + "save", this.nodes.parent)[0];

        this.nodes.translationsLinks      = document.getElementsByClassName(this.cssPrefix + "form_filters_translations_language_links", this.nodes.parent)[0];
        this.nodes.filtersDefaultGroup    = document.getElementsByClassName(this.cssPrefix + "form_filters_group", this.nodes.parent)[0];
        this.nodes.addFilterLink          = this.nodes.filtersDefaultGroup.getElementsByClassName(this.cssPrefix + "add_filter", this.nodes.parent)[0];

        this.nodes.valuesTranslations = {};
        
        var ul = this.nodes.filtersDefaultGroup.getElementsByTagName('ul')[0];
        ul.id = this.cssPrefix + "form_"+this.id+'_filters_'+this.languageCodes[0];

    },

    /**
     * Find all translations fields. This is done every time when new filter is being added
     *
     * @access private
     */
    bindTranslationFilters: function()
    {
        this.nodes.translatedFilters = document.getElementsByClassName(this.cssPrefix + "form_filters_translations", this.nodes.parent);
    },


    /**
     * Generate filters
     * 
     * @param Object e
     */
    generateFiltersAction: function(e)
    {
        var self = this;
    
        if(!e)
        {
            e = window.event;
            e.target = e.srcElement;
        }

        Event.stop(e);
    
        // execute the action
        new Ajax.Request(
                this.links.generateFilters + "?specFieldID="+this.nodes.specFieldID.value,
                {
                    method: 'get',
                    onComplete: function(param)
                    {
                        self.addGeneratedFilters(param.responseText)
                    }
                });                
    },
    
    /**
     * Add generated filters to filters list
     * 
     * @param Object jsonString
     */
    addGeneratedFilters: function(jsonString)
    {
        var self = this;
    
        var jsonResponse = eval("("+jsonString+")");
                                
        for(var i = 0; i < this.specFields.length; i++)
        {
             if(this.specFields[i].ID == this.nodes.specFieldID.value)
             {
                var specField = this.specFields[i];
                if(this.selectorValueTypes.indexOf(specField.type) !== -1)
                {
                    $A(this.nodes.filtersDefaultGroup.getElementsByTagName('ul')[0].getElementsByTagName("li")).each(function(li)
                    {
                        if(!Element.hasClassName(li, 'dom_template'))
                        {
                            delete jsonResponse.filters[document.getElementsByClassName('filter_selector', li)[0].getElementsByTagName("select")[0].value];
                        }
                    });
                }
        
                $H(jsonResponse.filters).each(function(filter) {
                    self.addFilter(filter.value, "new" + self.countNewFilters, true);
                    self.countNewFilters++;
                });
                
                return;
             }
        }
    },  


    /**
     * Binds fields to some events
     */
    bindFields: function()
    {
        var self = this;

        for(var i = 0; i < this.nodes.stateLinks.length; i++)
        {
            Event.observe(this.nodes.stateLinks[i], "click", function(e) { self.changeStateAction(e) });
        }

        Event.observe(this.nodes.name, "keyup", function(e) { self.generateTitleAction(e) });
        Event.observe(this.nodes.addFilterLink, "click", function(e) { self.addFilterFieldAction(e) });
        Event.observe(this.nodes.specFieldID, "change", function(e) { self.specFieldIDWasChangedAction(e) });
        Event.observe(this.nodes.cancel, "click", function(e) { self.cancelAction(e) });
        Event.observe(this.nodes.save, "click", function(e) { self.saveAction(e) });
        Event.observe(this.nodes.generateFiltersLink, "click", function(e) { self.generateFiltersAction(e) });
             
        
        // Also some actions must be executed on load. Be aware of the order in which those actions are called
        this.fillSpecFieldsSelect();
        if(this.filter.SpecField) this.nodes.specFieldID.value = this.filter.SpecField.ID;
        
        this.loadFilterAction();

        this.specFieldIDWasChangedAction();
        this.loadValueFieldsAction();

        this.bindTranslationFilters();

        new Form.EventObserver(this.nodes.form, function() { self.formChanged = true; } );
        Form.backup(this.nodes.form);
       
        var self = this;
        $A(this.nodes.form.getElementsByTagName("input")).each(function(input) {
           Event.observe(input, 'keydown', function(e) { self.submitOnEnter(e) }); 
        });
    },

    /**
     * Fill spec field select with options
     */
    fillSpecFieldsSelect: function()
    {
        var self = this;

        this.nodes.specFieldID.options.length = 0;
        this.specFields.each(function(value)
        {
            self.nodes.specFieldID.options[self.nodes.specFieldID.options.length] = new Option(value.name, value.ID);
        });

    },


    /**
     * When specField is changed show dates, ranges or select in filters tab
     */
    specFieldIDWasChangedAction: function()
    {
        var self = this;
        for(var i = 0; i < this.specFields.length; i++)
        {
             if(this.specFields[i].ID == this.nodes.specFieldID.value)
             {
                $A(this.nodes.filtersDefaultGroup.getElementsByTagName('ul')[0].getElementsByTagName("li")).each(function(li)
                {                    
                    document.getElementsByClassName('filter_range', li)[0].style.display = (self.selectorValueTypes.indexOf(self.specFields[i].type) === -1 && self.specFields[i].type != Backend.SpecField.prototype.TYPE_TEXT_DATE) ? 'block' : 'none';
                                                
                    if(self.selectorValueTypes.indexOf(self.specFields[i].type) !== -1)
                    {
                        var select = document.getElementsByClassName('filter_selector', li)[0].getElementsByTagName("select")[0];
                        select.options.length = 0;
                        for(var j = 0; j < self.specFields[i].values.length; j++)
                        {
                           select.options[select.options.length] = new Option(self.specFields[i].values[j].value[self.languageCodes[0]], self.specFields[i].values[j].ID);
                        } 
                    }                          
                    
                    if((self.selectorValueTypes.indexOf(self.specFields[i].type) === -1 || self.specFields[i].type == Backend.SpecField.prototype.TYPE_TEXT_DATE))
                    {
                        self.nodes.generateFiltersLink.style.visibility = 'hidden';
                        document.getElementsByClassName('filter_selector', li)[0].style.display = 'none';                            
                    }
                    else
                    {
                        self.nodes.generateFiltersLink.style.visibility = 'visible';
                        document.getElementsByClassName('filter_selector', li)[0].style.display = 'block';  
                    }
 
                    document.getElementsByClassName('filter_date_range', li)[0].style.display = (self.specFields[i].type == Backend.SpecField.prototype.TYPE_TEXT_DATE) ? 'block' : 'none'; 
                });
                
                document.getElementsByClassName(this.cssPrefix + "step_filters_translations", this.nodes.filtersDefaultGroup)[0].style.display = (Backend.SpecField.prototype.TYPE_TEXT_SELECTOR != self.specFields[i].type) ? 'none' : 'block';
                
                return;
             }
        }
    },

    bindOneFilter: function(li)
    {
        var self = this;
        
        var rangeParagraph = document.getElementsByClassName('filter_range', li)[0];
        var nameParagraph = document.getElementsByClassName('filter_name', li)[0];
        
        var name       = nameParagraph.getElementsByTagName("input")[0];
        var rangeStart = rangeParagraph.getElementsByTagName("input")[0];
        var rangeEnd   = rangeParagraph.getElementsByTagName("input")[1];
        
        Event.observe(nameParagraph.getElementsByTagName("input")[0], "keyup", function(e) { self.mainValueFieldChangedAction(e) }, false);
        
        Event.observe(rangeParagraph.getElementsByTagName("input")[0], "keydown", function(e) { self.rangeChangedAction(e) });
        Event.observe(rangeParagraph.getElementsByTagName("input")[1], "keydown", function(e) { self.rangeChangedAction(e) });        
        
        $A(li.getElementsByTagName("input")).each(function(input) {
           Event.observe(input, 'keydown', function(e) { self.submitOnEnter(e) }); 
        });
    },


    /**
     * Bind default language filter fields to actions
     */
    bindDefaultFields: function()
    {
        var self = this;
        var liList = this.nodes.filtersDefaultGroup.getElementsByTagName('ul')[0].getElementsByTagName('li');

        this.fieldsList = ActiveList.prototype.getInstance(this.nodes.filtersDefaultGroup.getElementsByTagName("ul")[0], {
            beforeSort: function(li, order)
            {
                return self.links.sortFilter + '?target=' + this.ul.id + '&' + order;
            },
            afterSort: function(li, response){    },

            beforeDelete: function(li){
                if(this.getRecordId(li).match(/^new/))
                {
	                var emptyFilters = true;
                    var inputValues = li.getElementsByTagName("input");
                    for(var i = 0; i < inputValues.length; i++) 
                    {
                        if(!Element.hasClassName('dom_template', inputValues[i]) && inputValues[i].parentNode.style.display != 'none' && inputValues[i].type != 'hidden' && inputValues[i].value != '')
                        {
                            emptyFilters =  false;
                        }
                    }
                    
                    if(emptyFilters || confirm('Are you realy want to delete this item?'))
                    {
                        self.deleteValueFieldAction(li, this);
                    }
                    
                }
                else if(confirm('Are you realy want to delete this item?'))
                {
                    return Backend.Filter.prototype.links.deleteFilter + this.getRecordId(li);
                }
            },
            afterDelete: function(li, response){ self.deleteValueFieldAction(li, this) }
        }, this.activeListMessages);
    },
   
    

    /**
     * Check if range values are valid floats
     * @param Event e
     */
    rangeChangedAction: function(e)
    {
        if(!e)
        {
            e = window.event;
            e.target = e.srcElement;
        }

        keyboard = new KeyboardEvent(e);

        if(
            !(
                // you can use +/- as the first character
                (keyboard.getCursorPosition() == 0 && !e.target.value.match('[\-\+]') && (keyboard.getKey() == 109 || keyboard.getKey() == 107 || (keyboard.isShift() && keyboard.getKey() == 61))) ||
                // You even can use dots or commas, but only once and not as the first symbol
                (e.target.value != '' && !e.target.value.match('[\.\,]') && [110, 188, 190].indexOf(keyboard.getKey()) >= 0) ||
                // at last but not the least i have implemanted such a great feature, that you can use digits to create numbers. [applause]
                ([48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105].indexOf(keyboard.getKey()) > 0) ||
                // special chars
                ([46, 8, 17, 16, 37, 38, 39, 40].indexOf(keyboard.getKey()) >= 0)
            )
        ){
            Event.stop(e);
        }
    },

    /**
     * @param string newTitle Modify AR title
     */
    changeMainTitleAction: function(newTitle)
    {
        if(this.nodes.mainTitle)
        {
            if(this.nodes.mainTitle.firstChild)
            {
                this.nodes.mainTitle.firstChild.nodeValue = newTitle;
            }
            else
            {
                this.nodes.mainTitle.appendChild(document.createTextNode(newTitle));
            }
        }
    },

    /**
     * Fill main filter group values (name and spec field) and create translations for those values
     */
    loadFilterAction: function()
    {
        var self = this;

        // Default language
        if(this.id) this.nodes.id.value = this.id;

        if(this.name[this.languageCodes[0]]) this.nodes.name.value = this.name[this.languageCodes[0]];
        this.nodes.name.name = "name[" + this.languageCodes[0] + "]";

        this.changeMainTitleAction(this.nodes.name.value);

        // Translations
        var translations = document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepTranslations);
		var valuesTranslations = document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepValues);
        // we should have a template to continue
        if(translations.length > 0 && translations[0].className.split(' ').indexOf('dom_template') !== -1)
        {
            this.nodes.translations = new Array();
            for(var i = 1; i < this.languageCodes.length; i++)
            {
                // copy template class
                var newTranslation = translations[0].cloneNode(true);
                Element.removeClassName(newTranslation, "dom_template");
                
    			// bind it
                Event.observe(newTranslation.getElementsByTagName("legend")[0], "click", function(e) { self.changeTranslationLanguageAction(e) });

                newTranslation.className += this.languageCodes[i];
                document.getElementsByClassName(this.cssPrefix + "legend_text", newTranslation.getElementsByTagName("legend")[0])[0].appendChild(document.createTextNode(this.languages[this.languageCodes[i]]));

                var inputFields = $A(newTranslation.getElementsByTagName('input'));
                var textAreas = newTranslation.getElementsByTagName('textarea');
                for(var j = 0; j < textAreas.length; j++)
                {
                    inputFields[inputFields.length] = textAreas[j];
                }

                for(var j = 0; j < inputFields.length; j++)
                {
                    var test1 = inputFields[j];
                    var test2 = inputFields[j].parentNode.parentNode;
                    if(Element.hasClassName(inputFields[j].parentNode.parentNode, this.cssPrefix + 'language_translation'))
                    {
                        eval("if(self."+inputFields[j].name+"['"+self.languageCodes[i]+"']) inputFields[j].value = self."+inputFields[j].name+"['"+self.languageCodes[i]+"'];");
                        inputFields[j].name = inputFields[j].name + "[" + self.languageCodes[i] + "]";
                    }
                }

                this.nodes.stepTranslations.appendChild(newTranslation);

                // add to nodes list
                this.nodes.translations[this.languageCodes[i]] = newTranslation;
                  
                // Create place for values translations
				var newValueTranslation = valuesTranslations[0].cloneNode(true);
				Element.removeClassName(newValueTranslation, "dom_template");
				newValueTranslation.className += "_" + this.languageCodes[i];
                
                var valueTranslationLegend = document.getElementsByClassName(this.cssPrefix + "legend_text", newValueTranslation.getElementsByTagName("legend")[0])[0];
				valueTranslationLegend.appendChild(document.createTextNode(this.languages[this.languageCodes[i]]));
                
                Event.observe(valueTranslationLegend.parentNode, "click", function(e) { self.toggleValueLanguage(e) });
                
				valuesTranslations[0].parentNode.appendChild(newValueTranslation);
                this.nodes.valuesTranslations[this.languageCodes[i]] = newValueTranslation;
            }
        }

        // Delete language template, so that included in that template variables would not be sent to server
        Element.remove(document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepTranslations)[0]);
    },


    toggleValueLanguage: function(e)
    {
        if(!e)
		{
			e = window.event;
			e.target = e.srcElement;
		}
        
        var values = document.getElementsByClassName(this.cssPrefix + "form_language_translation", e.target.parentNode.parentNode)[0];
        values.style.display = (values.style.display == 'block') ? 'none' : 'block';
        document.getElementsByClassName("expandIcon", e.target.parentNode)[0].firstChild.nodeValue = (values.style.display == 'block') ? '[-] ' : '[+] ' ;
    },
    
    

    /**
     * Create filters from json Object
     *
     * @access private
     *
     */
    loadValueFieldsAction: function()
    {
        var self = this;

        if(this.filters)
        {
            $H(this.filters).each(function(value) {
                self.addFilter(value.value, value.key);
            });

            this.bindDefaultFields();
        }
    },


    /**
     * This method separates language codes from language titles
     *
     * @example (lt: Lithuanian, ru: Russian) will create [lt, ru] array
     *
     * @access private
     *
     */
    loadLanguagesAction: function()
    {
        var self = this;
        this.languageCodes = [];

        $H(this.languages).each(function(language) {
            self.languageCodes[self.languageCodes.length] = language.key;
        });
    },


    /**
     * Change translation language tab
     *
     * @param Event e Event
     *
     * @access private
     */
	changeTranslationLanguageAction: function(e)
	{
	    if(!e)
		{
			e = window.event;
			e.target = e.srcElement;
		}

        Event.stop(e);
        var currentTranslationNode = document.getElementsByClassName(this.cssPrefix + "language_translation", e.target.parentNode.parentNode)[0];               
        currentTranslationNode.style.display = (currentTranslationNode.style.display == 'block') ? 'none' : 'block';
        
        document.getElementsByClassName("expandIcon", e.target.parentNode)[0].firstChild.nodeValue = (currentTranslationNode.style.display == 'block') ? '[-] ' : '[+] ';
	},

    /**
     * Create appropriate fields in translation tab when creating new filter
     *
     * @param Event e Event
     *
     * @access private
     */
    addFilterFieldAction: function(e)
    {
        if(!e)
        {
            e = window.event;
            e.target = e.srcElement;
        }

        Event.stop(e);

        this.addFilter(null, "new" + this.countNewFilters, true);
        this.bindDefaultFields();
        this.countNewFilters++;
    },


    /**
     * Delete filter
     *
     * @param Event e Event
     *
     * @access private
     */
    deleteValueFieldAction: function(li, activeList)
    {
        var splitedHref = li.id.split("_");
        var isNew = splitedHref[splitedHref.length - 2] == 'new' ? true : false;
        var id = (isNew ? 'new' : '') + splitedHref[splitedHref.length - 1];

        activeList.remove(li);

        for(var i = 1; i < this.languageCodes.length; i++)
        {
            var translatedValue = document.getElementById(this.cssPrefix + "form_filters_" + this.languageCodes[i] + "_" + id);

            // if new or not main language
            if(isNew || i > 0)
            {
                Element.remove(translatedValue);
            }
            else
            {
                translatedValue.id += '_deleted';
                var input = translatedValue.getElementsByTagName('input')[0];
                input.name = input.name.replace(/\[\w+\]\[([\d]+)\]/, "[deleted][$1]");
                translatedValue.style.display = 'none';
            }
        }
    },


    /**
     * This callback is executed when user changes the state. When user change the state all other
     * states are hidden and only current state shown or if the user was so stupid to click on current
     * state whe whole thing will crash (or the current step will collapse. I don't realy remember)
     *
     * @param Event e Event
     *
     * @access private
     */
    changeStateAction: function(e)
    {
        if(!e)
        {
            e = window.event;
            e.target = e.srcElement;
        }

        Event.stop(e);

        var currentStep = this.cssPrefix + e.target.hash.substring(1);
        for(var i = 0; i < this.nodes.stepLevOne.length; i++)
        {
            this.nodes.stateLinks[i].id = this.cssPrefix + 'change_state' + this.id;

            if(this.nodes.stepLevOne[i].className.split(' ').indexOf(currentStep) === -1)
            {
                this.nodes.stepLevOne[i].style.display = 'none';
                Element.removeClassName(this.nodes.stateLinks[i], this.cssPrefix + "change_state_active");
                Element.removeClassName(this.nodes.stateLinks[i].parentNode, 'active');
            }
            else
            {
                this.nodes.stepLevOne[i].style.display = 'block';
                Element.addClassName(this.nodes.stateLinks[i], this.cssPrefix + "change_state_active");
                Element.addClassName(this.nodes.stateLinks[i].parentNode, 'active');
            }
        }
    },


    /**
     * When some dumbass creates/modifies value in "Filters" step, we are automatically creating
     * a label for similar field in every language section in "Translations" step.
     *
     * @example If we tipe one in "Filters" step like so
     * ___________
     * |One       |
     * ------------
     *
     * the programm will change label of similar fields in every translation language like so
     *
     * Lithuanian:
     *        ___________
     * One:   |Vienas    |
     *        ------------
     *
     * German:
     *        ___________
     * One:   |Einz      |   * I don't realy know how to write one in germat and also tooday i am to lazy to google for it :(
     *        ------------
     *
     * @param Event e Event
     *
     * @access private
     */
    mainValueFieldChangedAction: function(e)
    {
        if(!e)
        {
            e = window.event;
            e.target = e.srcElement;
        }

        Event.stop(e);
        
        var li = e.target.parentNode.parentNode.parentNode;
        var splitedHref  = li.id.match(/(new)*(\d+)$/); //    splitedHref[splitedHref.length - 2] == 'new' ? true : false;
        var id = splitedHref[0];
        
        if(id.match(/^new/))
        {
    		// generate handle
            var handleParagraph = document.getElementsByClassName('filter_handle', li)[0];
    		handleParagraph.getElementsByTagName('input')[0].value = ActiveForm.prototype.generateHandle(e.target.value);
        }

        for(var i = 1; i < this.languageCodes.length; i++)
        {
            $(this.cssPrefix + "form_filters_" +  this.languageCodes[i] + "_" + id).getElementsByTagName("label")[0].innerHTML = e.target.value;
        }
    },


    /**
     * When we are filling spec field name in "Main" step we are changing it's handle and a title
     * on the top of the form. Handle is actuali a stripped version of spec field name with all spec
     * symbols changed to "_" (underscope)
     *
     * @param Event e Event 
     *
     * @access private
     *
     */
    generateTitleAction: function(e)
    {
        if(this.nodes.mainTitle)
        {
            if(this.nodes.mainTitle.firstChild)
            {
                this.nodes.mainTitle.firstChild.nodeValue = this.nodes.name.value;
            }
            else
            {
                this.nodes.mainTitle.appendChild(document.createTextNode(this.nodes.name.value));
            }
        }
    },


    /**
     * Here we are adding new field to filters list in "Filters" step and "Translations" step.
     *
     * @param hash value Value of newly created field. The value is a hash array with value for every language {'en': "One", 'lt': "Vienas", 'de': "Einz"}
     * @param int id Id of a newly created field
     *
     * @access private
     *
     */
    addFilter: function(value, id, isDefault)
    {
        var filters = document.getElementsByClassName(this.cssPrefix + "form_filters_value", this.nodes.filtersDefaultGroup);

        var ul = this.nodes.filtersDefaultGroup.getElementsByTagName('ul')[0];

        if(filters.length > 0 && Element.hasClassName(filters[0], 'dom_template'))
        {
            var newValue = filters[0].cloneNode(true);
            Element.removeClassName(newValue, "dom_template");

            if(!this.fieldsList) this.bindDefaultFields();

            var li = this.fieldsList.addRecord(id, newValue, true);

            // Filter name
            var nameParagraph = document.getElementsByClassName('filter_name', li)[0];
            var input = nameParagraph.getElementsByTagName("input")[0];
            input.name = "filters[" + id + "][name]["+this.languageCodes[0]+"]";
            input.value = (value && value.name && value.name[this.languageCodes[0]]) ? value.name[this.languageCodes[0]] : '' ;


            // Handle name
            var hanldeParagraph = document.getElementsByClassName('filter_handle', li)[0];
            var handleInput = hanldeParagraph.getElementsByTagName("input")[0];
            handleInput.name = "filters[" + id + "][handle]";
            handleInput.value = (value && value.handle) ? value.handle : '' ;
            

            // Numeric range
            var rangeParagraph = document.getElementsByClassName('filter_range', li)[0];
            
            var rangeStartInput = rangeParagraph.getElementsByTagName("input")[0];
            rangeStartInput.name = "filters[" + id + "][rangeStart]";
            rangeStartInput.value = (value && value.rangeStart) ? value.rangeStart : '' ;
            
            var rangeEndInput = rangeParagraph.getElementsByTagName("input")[1];
            rangeEndInput.name = "filters[" + id + "][rangeEnd]";
            rangeEndInput.value = (value && value.rangeEnd) ? value.rangeEnd : '' ;
            
            
            // Select
            var specFieldValueParagraph = document.getElementsByClassName('filter_selector', li)[0];
            
            var specFieldValueIDInput = specFieldValueParagraph.getElementsByTagName("select")[0];
            specFieldValueIDInput.name = "filters[" + id + "][specFieldValueID]";
            specFieldValueIDInput.value = (value && value.specFieldValueID) ? value.specFieldValueID : '' ;
            
            
            // Date range
            var dateParagraph = document.getElementsByClassName("filter_date_range", li)[0];
            
            var rangeDateStart = dateParagraph.getElementsByTagName("input")[0];
            var rangeDateEnd = dateParagraph.getElementsByTagName("input")[1];
            
            var rangeDateStartButton = document.getElementsByClassName("calendar_button", dateParagraph)[0];
            var rangeDateEndButton   = document.getElementsByClassName("calendar_button", dateParagraph)[1];
            
            var rangeDateStartReal   = document.getElementsByClassName(this.cssPrefix + "date_start_real", dateParagraph)[0];
            var rangeDateEndReal     = document.getElementsByClassName(this.cssPrefix + "date_end_real", dateParagraph)[0];
            
            rangeDateStart.id         = this.cssPrefix + "rangeDateStart_" + id;
            rangeDateEnd.id           = this.cssPrefix + "rangeDateEnd_" + id;
            rangeDateStartReal.id     = rangeDateStart.id + "_real";
            rangeDateEndReal.id       = rangeDateEnd.id + "_real";
            rangeDateStartButton.id   = rangeDateStart.id + "_button";
            rangeDateEndButton.id     = rangeDateEnd.id + "_button";      
            
            
            rangeDateStart.name       = "filters[" + id + "][rangeDateStart_show]";
            rangeDateEnd.name         = "filters[" + id + "][rangeDateEnd_show]";
            rangeDateStartReal.name   = "filters[" + id + "][rangeDateStart]";
            rangeDateEndReal.name     = "filters[" + id + "][rangeDateEnd]";
                  
                  
            rangeDateStartReal.value  = (value && value.rangeDateStart) ? value.rangeDateStart : '' ;
            rangeDateEndReal.value    = (value && value.rangeDateEnd) ? value.rangeDateEnd : '' ;
            
            rangeDateStart.value  = rangeDateStartReal.value;
            rangeDateEnd.value    = rangeDateEndReal.value ;
                       
            var calDateStart = Calendar.setup({
                inputField:       rangeDateStart.id,
                inputFieldReal:   rangeDateStartReal.id,
                ifFormat:         this.dateFormat, 
                button:           rangeDateStartButton.id
            });
            
            
            var calDateEnd = Calendar.setup({
                inputField:       rangeDateEnd.id,
                inputFieldReal:   rangeDateEndReal.id,
                ifFormat:         this.dateFormat, 
                button:           rangeDateEndButton.id
            });
            
            this.bindOneFilter(li);
            
			// now insert all translation fields
			for(var i = 1; i < this.languageCodes.length; i++)
			{
				var newValueTranslation = document.getElementsByClassName(this.cssPrefix + "form_filters_value", this.nodes.valuesTranslations[this.languageCodes[i]])[0].cloneNode(true);
				Element.removeClassName(newValueTranslation, "dom_template");

				newValueTranslation.id = newValueTranslation.id + this.languageCodes[i] + "_" + id;

				var inputTranslation = newValueTranslation.getElementsByTagName("input")[0];
				inputTranslation.name = "filters[" + id + "][name][" + this.languageCodes[i] + "]";
				inputTranslation.value = (value && value.name && value.name[this.languageCodes[i]]) ? value.name[this.languageCodes[i]] : '' ;
                

                newValueTranslation.getElementsByTagName("label")[0].innerHTML = input.value;
                
				// add to node tree
				var translationsUl = document.getElementsByClassName(this.cssPrefix + "form_language_translation", this.nodes.valuesTranslations[this.languageCodes[i]])[0].getElementsByTagName('ul')[0];
				translationsUl.id = this.cssPrefix + "form_"+this.id+'_values_'+this.languageCodes[i];
				translationsUl.appendChild(newValueTranslation);
			}

        }
        else
        {
            return false;
        }
    },


    /**
     * This method is called when user click on cancel link. It resets all fields to its defaults and closes form
     *
     * @param Event e Event
     *
     * @access public
     *
     */
    cancelAction: function(e)
    {
        if(!e)
        {
            e = window.event;
            e.target = e.srcElement;
        }

        Event.stop(e);

        // first cancel all modifications if they took place
        if(this.id == 'new')
        {
            this.recreate(this.filter, true);
        }
        else if(Form.hasBackup(this.nodes.form) && this.formChanged)
        {
            Form.restore(this.nodes.form);

            this.changeMainTitleAction(this.nodes.name.value);
            this.specFieldIDWasChangedAction();
        }

        // Use Active list toggleContainer() method if this filter is inside Active list
        // Note that if it is inside a list we are showing and hidding form with the same action,
        // butt =] when dealing with new form showing form action is handled by Backend.Filter::createNewAction()
        if(this.nodes.parent.tagName.toLowerCase() == 'li')
        {
            window.activeFiltersList[this.categoryID].toggleContainer(this.nodes.parent, 'edit');
        }
        else
        {
            this.hideNewFilterAction(this.categoryID);
        }
    },
    

    /**
     * Clears all feedback messages in current spec field
     *
     */
    clearAllFeedBack: function()
    {
        var feedback = document.getElementsByClassName('feedback', this.nodes.parent);

        $A(feedback).each(function(field)
        {
            field.style.visibility = 'hidden';
        });
    },


    /**
     * This method is called when user clicks on save button. It saves form filters, and does i don't know what (i guess it should close the form)
     *
     * @param Event e Event
     *
     * @access public
     *
     */
    saveAction: function(e)
    {
        if(!e)
        {
            e = window.event;
            e.target = e.srcElement;
        }

        Event.stop(e);
        
        this.saveFilterGroup();
    },

    saveFilterGroup: function()
    {
        // Toggle progress won't work on new form
        try
        {
            window.activeFiltersList[this.categoryID].toggleProgress(this.nodes.parent);
        }
        catch (e)
        {
            // New item has no pr06r3s5 indicator
        }

        this.clearAllFeedBack();
        
        var self = this;
        new Ajax.Request(
            this.nodes.form.action,
            {
                method: this.nodes.form.method,
                postBody: Form.serialize(this.nodes.form),
                onComplete: function(param) {
                    self.afterSaveAction(param.responseText)
                }
            }
        );  
    },
    

    /**
     * This action is executed after server response with possible errors in entered
     * spec field fields
     *
     */
    afterSaveAction: function(jsonResponseString)
    {
        var self = this;

        try
        {
            var jsonResponse = eval("("+jsonResponseString+")");
        }
        catch(e)
        {
            alert("json error");
        }

        if(jsonResponse.status == 'success')
        {
            Form.backup(this.nodes.form);
            this.backupName = this.nodes.name.value;

            if(this.nodes.parent.tagName.toLowerCase() == 'li')
            {
                window.activeFiltersList[this.categoryID].toggleContainer(this.nodes.parent, 'edit');
            }
            else
            {

                var div = document.createElement('span');
                Element.addClassName(div, 'filter_title');
                div.appendChild(document.createTextNode(this.nodes.name.value));
                window.activeFiltersList[this.categoryID].addRecord(jsonResponse.id, [document.createTextNode(' '), div]);
                CategoryTabControl.prototype.resetTabItemsCount(this.categoryID);
                
                this.hideNewFilterAction(this.categoryID);
                this.recreate(this.filter, true);
            }
        }
        else
        {
            try
            {
                if(jsonResponse.errors)
                {
                    for(var fieldName in jsonResponse.errors)
                    {
                        
                        if(fieldName == 'toJSONString') continue;
                        
                        if(fieldName == 'filters')
                        {
                            $H(jsonResponse.errors[fieldName]).each(function(value)
                            {
                                var filterLi = $(self.cssPrefix + "form_" + self.id + "_filters_" + self.languageCodes[0] + "_" + value.key);
                                $H(value.value).each(function(filterField)
                                {
                                    var inputParagraph = document.getElementsByClassName('filter_' + filterField.key, filterLi)[0];
                                    try
                                    {
                                        ActiveForm.prototype.setFeedback(inputParagraph.getElementsByTagName('input')[0], filterField.value);
                                    } catch(e) {
                                        ActiveForm.prototype.setFeedback(inputParagraph.getElementsByTagName('select')[0], filterField.value);
                                    }
                                });
                            });
                        }
                        else
                        {
                            ActiveForm.prototype.setFeedback(this.nodes[fieldName], jsonResponse.errors[fieldName]);
                        }
                    }
                }
            }
            catch(e)
            {
                alert(e.fileName + ':' + e.lineNumber + '\n' + e.message);
            }
        }


        // Toggle progress won't work on new form
        try
        {
            window.activeFiltersList[this.categoryID].toggleProgress(this.nodes.parent);
        }
        catch (e)
        {
            // new item has no progress indicator
        }
    },


    /**
     * All Your Base Are Belong To Us! A mystery function.
     * Hides new spec field form
     *
     * @static
     */
    hideNewFilterAction: function(categoryID)
    {
        var link = $(this.cssPrefix + "item_new_"+categoryID+"_show");
        var form = $(this.cssPrefix + "item_new_"+categoryID+"_form");

        ActiveForm.prototype.hideNewItemForm(link, form);
    },


    /**
     * When the form is created it gets all it's parameters from JSON. However when getting options
     * list we should create an array of Option objects from JSON.
     *
     * @example
     * var json = {
     *              pc:  'Personal Computer',
     *              mac: 'PowerPC',
     *              sun: 'Sun Server'
     *           }
     *
     * is converted to
     *
     * var options = (
     *                new Option('Personal Computer', pc),
     *                new Option('e', mac),
     *                new Option('Sun Server', sun)
     *             )
     *
     * @static
     */
    createTypesOptions: function(types)
    {
       var typesOptions = {};
       $H(types).each(function(value) {
           var options = [];

           $H(value.value).each(function(option) {
               options[options.length] = [option.key, option.value];
           });

           typesOptions[value.key] = options;
        });

        return typesOptions;
    },

    /**
     * This method unfolds "Create new Spec Field entry" form. Items from existing spec fields list are unfolded using
     * ActiveList methods
     *
     * @see ActiveList
     *
     * @param HTMLElement parent form node (it should have "create new entry" and an empty spec field form inside it)
     *
     * @static
     *
     */
    createNewAction: function(e, categoryID)
    {
        if(!e) e = window.event;
        Event.stop(e);

        var link = $(this.cssPrefix + "item_new_"+categoryID+"_show");
        var form = $(this.cssPrefix + "item_new_"+categoryID+"_form");     
        
        window.activeFiltersList[categoryID].collapseAll();
        ActiveForm.prototype.showNewItemForm(link, form);
    },
    
    submitOnEnter: function(e)
    {
        keybordEvent = new KeyboardEvent(e);
        
        if(keybordEvent.getKey() == KeyboardEvent.prototype.KEY_ENTER) {
            this.saveFilterGroup();
        }
    }
}