/**
 * Backend.Filter
 *
 * Script for managing spec field form
 *
 * The following class manages spec field forms. I have used an separate js file (a class)
 * because there are a lot of thing happening when you are dealing with spec fields forms.
 *
 * To use this class you should simply pass specFIelds filters to it like so
 * @example
 * <code>
 *     new Backend.Filter({
 *        "ID":"new",
 *        "name":"a:2:{s:2:\"en\";s:11:\"Electronics\";s:2:\"lt\";s:11:\"Elektronika\";}",
 *        "description":[],
 *        "handle":"",
 *        "filters":[],
 *        "rootId": "filter_item_new",
 *        "type":5,
 *        "dataType":2
 *     });
 * </code>
 *
 * I hope whoever reads this will figure aut what each value means. Name, description and filters
 * can have multiple filters for each language
 *
 * Also you should know that some filters are not meant to be passed to constructor (it will also
 * work fine... meaby) Here is an example
 *
 * @example
 * <code>
 *     Backend.Filter.prototype.languages = {"en":"English","lt":"Lithuanian","de":"German"};
 *     Backend.Filter.prototype.types = createTypesOptions({"2":{"1":"Selector","2":"Numbers"},"1":{"3":"Text","4":"Word processer","5":"selector","6":"Date"}});
 *     Backend.Filter.prototype.messages = {"deleteGroup":"delete field"};
 *     Backend.Filter.prototype.selectorValueTypes = [1,5];
 *     Backend.Filter.prototype.doNotTranslateTheseValueTypes = [2];
 *     Backend.Filter.prototype.countNewFilters = 0;
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

    /**
     * Constructor
     *
     * @param filtersJson Spec Field filters
     * @param hash If true the passed filter is an object. If hash is not passed or false then filterJson will be parsed as json string
     *
     * @access public
     *
     */
    initialize: function(filterJson, hash)
    {
        try
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

            this.loadLanguagesAction();
            this.findUsedNodes();
            this.bindFields();
        } catch(e)
        {
            jsTrace.debug(e);
        }
    },

    /**
     * This function destroys the old spec field form, then clones the prototype and then calls constructor once again
     *
     * @param filters Spec Field filters
     * @param hash If true the passed filter is an object. If hash is not passed or false then filterJson will be parsed as json string
     *
     * @access public
     *
     */
    recreate: function(filterJson, hash)
    {
        var root = ($(this.filter.rootId).tagName.toLowerCase() == 'li') ?  window.activeFiltersList.getContainer($(this.filter.rootId), 'edit') : $(this.filter.rootId);
        root.innerHTML = '';
        $H(this).each(function(el)
        {
            el = false;
        });
        
        this.initialize(filterJson, hash);
        this.clearAllFeedBack();
    },


    /**
     * Instead of sending spec field form we store form prototype which is cloned every time new spec field data is being recieved.
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
        var root = ($(this.filter.rootId).tagName.toLowerCase() == 'li') ?  window.activeFiltersList.getContainer($(this.filter.rootId), 'edit') : $(this.filter.rootId);

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
     *
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

        var ul = this.nodes.filtersDefaultGroup.getElementsByTagName('ul')[0];
        ul.id = this.cssPrefix + "form_"+this.id+'_filters_'+this.languageCodes[0];

    },

    /**
     * Find all translations fields. This is done every time when new field is being added
     *
     * @access private
     *
     */
    bindTranslationFilters: function()
    {
        this.nodes.translatedFilters = document.getElementsByClassName(this.cssPrefix + "form_filters_translations", this.nodes.parent);
    },


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
    
    addGeneratedFilters: function(jsonString)
    {
        var self = this;
    
        try
        {
            var jsonResponse = eval("("+jsonString+")");
                        
            for(var i = 0; i < this.specFields.length; i++)
            {
                 if(this.specFields[i].ID == this.nodes.specFieldID.value)
                 {
                    var specField = this.specFields[i];
                    if(this.selectorValueTypes.indexOf(specField.type) !== -1)
                    {
                        jsTrace.send("----");
                        $A(this.nodes.filtersDefaultGroup.getElementsByTagName("li")).each(function(li)
                        {
                            if(!Element.hasClassName(li, 'dom_template'))
                            {
                                jsTrace.send(document.getElementsByClassName('filter_selector', li)[0].getElementsByTagName("select")[0].value);
                                delete jsonResponse.filters[document.getElementsByClassName('filter_selector', li)[0].getElementsByTagName("select")[0].value];
                            }
                        });
                          
                    }
            
                    $H(jsonResponse.filters).each(function(filter) {
                        self.addFilter(filter.value, "new" + self.countNewFilters, true);
                    });
                    
                    return;
                 }
            }
            
        }
        catch(e)
        {
            jsTrace.debug(e);
            alert("Json error");
        }
    },  


    /**
     * Binds fields to some events
     *
     * @access private
     *
     */
    bindFields: function()
    {
        var self = this;

    try
    {
        for(var i = 0; i < this.nodes.stateLinks.length; i++)
        {
            this.nodes.stateLinks[i].onclick = this.changeStateAction.bind(this);
        }

        this.nodes.name.onkeyup = this.generateTitleAction.bind(this);
        this.nodes.addFilterLink.onclick = this.addFilterFieldAction.bind(this);
        this.nodes.specFieldID.onchange = this.specFieldIDWasChangedAction.bind(this);

        this.nodes.cancel.onclick = this.cancelAction.bind(this);
        this.nodes.save.onclick = this.saveAction.bind(this);
        
        this.nodes.generateFiltersLink.onclick = this.generateFiltersAction.bind(this);

        // Also some actions must be executed on load. Be aware of the order in which those actions are called
        this.fillSpecFieldsSelect();

        this.createLanguagesLinks();
        this.loadFilterAction();

        this.specFieldIDWasChangedAction();
        this.loadValueFieldsAction();

        this.bindTranslationFilters();
        this.loadTypes();
    } catch(e)
    {
        jsTrace.debug(e);
    }

        new Form.EventObserver(this.nodes.form, function() { self.formChanged = true; } );
        Form.backup(this.nodes.form);
    },

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
     * Whem Mike changes input type from "numbers" to "text" programm should select
     * appropriate value from types list (like selector, text, date, number, etc)
     *
     * @access private
     *
     */
    loadTypes: function()
    {
        if(this.type)
        {
            for(var i = 0; i < this.nodes.type.options.length; i++)
            {
                if(this.nodes.type.options[i].value == this.type)
                {
                    this.nodes.type.selectedIndex = i;
                    break;
                }
            }
        }
    },

    /**
     * When the value type changes whe should decide whether show step "Filters" (for selectors) or not,
     * and whether to show translations or not (show for text, hide for numbers)
     *
     * @access private
     *
     */
    specFieldIDWasChangedAction: function()
    {
        var self = this;
        for(var i = 0; i < this.specFields.length; i++)
        {
             if(this.specFields[i].ID == this.nodes.specFieldID.value)
             {
                $A(this.nodes.filtersDefaultGroup.getElementsByTagName("li")).each(function(li)
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
                    

                        document.getElementsByClassName('filter_selector', li)[0].style.display = (self.selectorValueTypes.indexOf(self.specFields[i].type) === -1 || self.specFields[i].type == Backend.SpecField.prototype.TYPE_TEXT_DATE) ? 'none' : 'block';
                        document.getElementsByClassName('filter_date_range', li)[0].style.display = (self.specFields[i].type == Backend.SpecField.prototype.TYPE_TEXT_DATE) ? 'block' : 'none'; 
                });
                
                
                return;
             }
        }
    },

    /**
     * This method binds all default filters (those which are field in "Filters" step) and create new fields in "Translations"
     * step where user can fill translations for those filters
     *
     * @access private
     *
     */
    bindDefaultFields: function()
    {
        var self = this;
        $A(this.nodes.filtersDefaultGroup.getElementsByTagName("li")).each(function(li)
        {
            li.getElementsByTagName("input")[0].onkeyup = self.mainValueFieldChangedAction.bind(self);
            li.getElementsByTagName("input")[1].onkeydown = self.rangeChangedAction.bind(self);
            li.getElementsByTagName("input")[2].onkeydown = self.rangeChangedAction.bind(self);
        });



        this.fieldsList = new ActiveList(this.nodes.filtersDefaultGroup.getElementsByTagName("ul")[0], {
            beforeSort: function(li, order)
            {
                return self.links.sortFilter + '?target=' + this.ul.id + '&' + order;
            },
            afterSort: function(li, response){    },

            beforeDelete: function(li){
                if(confirm('Are you realy want to delete this item?'))
                {
                    if(this.getRecordId(li).match(/^new/))
                    {
                        self.deleteValueFieldAction(li, this);
                    }
                    else
                    {
                        return Backend.Filter.prototype.links.deleteFilter + this.getRecordId(li);
                    }
                }
            },
            afterDelete: function(li, response){ self.deleteValueFieldAction(li, this) }
        });
    },

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
     * Here we fill "Main" step field filters like name, handle, input type and value type
     *
     * @access private
     *
     */
    loadFilterAction: function()
    {
        var self = this;

        // Default language
        if(this.id) this.nodes.id.value = this.id;
        if(this.handle) this.nodes.handle.value = this.handle;

        if(this.name[this.languageCodes[0]]) this.nodes.name.value = this.name[this.languageCodes[0]];
        this.nodes.name.name = "name[" + this.languageCodes[0] + "]";

        this.changeMainTitleAction(this.nodes.name.value);


        // Translations
        var translations = document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepTranslations);
        // we should have a template to continue
        if(translations.length > 0 && translations[0].className.split(' ').indexOf('dom_template') !== -1)
        {
            this.nodes.translations = new Array();
            for(var i = 1; i < this.languageCodes.length; i++)
            {
                // copy template class
                var newTranslation = translations[0].cloneNode(true);
                Element.removeClassName(newTranslation, "dom_template");

                if(i == 1)
                {
                    newTranslation.style.display = 'block';
                }

                newTranslation.className += this.languageCodes[i];

                newTranslation.getElementsByTagName("legend")[0].appendChild(document.createTextNode(this.languages[this.languageCodes[i]]));

                var inputFields = $A(newTranslation.getElementsByTagName('input'));
                var textAreas = newTranslation.getElementsByTagName('textarea');
                for(var j = 0; j < textAreas.length; j++)
                {
                    inputFields[inputFields.length] = textAreas[j];
                }

                for(var j = 0; j < inputFields.length; j++)
                {
                    if(Element.hasClassName(inputFields[j].parentNode, this.cssPrefix + 'step_translations_language'))
                    {
                        eval("if(self."+inputFields[j].name+"['"+self.languageCodes[i]+"']) inputFields[j].value = self."+inputFields[j].name+"['"+self.languageCodes[i]+"'];");
                        inputFields[j].name = inputFields[j].name + "[" + self.languageCodes[i] + "]";
                    }
                }

                this.nodes.stepTranslations.appendChild(newTranslation);

                // add to nodes list
                this.nodes.translations[this.languageCodes[i]] = newTranslation;
            }
        }

        // Delete language template, so that included in that template variables would not be sent to server
        Element.remove(document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepTranslations)[0]);
    },


    /**
     * When we create form from JSON string we should create and fill in filters fields (from "Filters" step)
     * and their translations in "Translations" step if needed
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
     * In Filter form template we not yet know what languages we'll be using so
     * what we are doing here is looking at what languages we are using and creating separate
     * sections for each language in "Translations" section
     *
     * @access private
     *
     */
    createLanguagesLinks: function()
    {
        var languageTemplateLink = document.getElementsByClassName("dom_template", this.nodes.translationsLinks)[0];

        for(var i = 1; i < this.languageCodes.length; i++)
        {
            var languageLinkDiv = languageTemplateLink.cloneNode(true);
            Element.removeClassName(languageLinkDiv, "dom_template");

            var languageLink = languageLinkDiv.getElementsByTagName("a")[0];
            languageLink.hash += this.languageCodes[i];
            Element.addClassName(languageLink, this.cssPrefix + languageLink.hash.substring(1) + "_link"); 
            
            languageLink.firstChild.nodeValue = this.languages[this.languageCodes[i]];

            // First link is active
            if(i == 1)
            {
                Element.addClassName(languageLink, this.cssPrefix + "step_translations_language_active");
                Element.addClassName(languageLink.parentNode, "active");
            }

            this.nodes.translationsLinks.appendChild(languageLinkDiv);

            // bind it
            languageLinkDiv.onclick = this.changeTranslationLanguageAction.bind(this);
        }
    },





    /**
     * Programm should change language section if we have click on a link meaning different language. If we click current
     * language it will callapse (not the programme of course =)
     *
     * @param Event e Event
     *
     * @access private
     *
     */
    changeTranslationLanguageAction: function(e)
    {
        if(!e)
        {
            e = window.event;
            e.target = e.srcElement;
        }

        Event.stop(e);

        if(e.target.tagName.toLowerCase() != 'a') return;

        var    currentLanguageClass = this.cssPrefix + e.target.hash.replace(/^#+/, '');

        var translationsNodes = document.getElementsByClassName(this.cssPrefix + "step_translations_language", this.nodes.stepTranslations);
        var translationsLinks = document.getElementsByClassName(this.cssPrefix + "translations_links", this.nodes.stepTranslations);

        var same = e.target.hasClassName(this.cssPrefix + "step_translations_language_active");

        for(var i = 0; i < translationsNodes.length; i++)
        {
            translationsNodes[i].style.display = 'none';
        }

        document.getElementsByClassName(currentLanguageClass, this.nodes.stepTranslations)[0].style.display = 'block';

        for(var i = 0; i < translationsLinks.length; i++)
        {
            Element.removeClassName(translationsLinks[i], this.cssPrefix + "step_translations_language_active");
            Element.removeClassName(translationsLinks[i].parentNode, "active");
        }


        Element.addClassName(e.target, this.cssPrefix + "step_translations_language_active");
        Element.addClassName(e.target.parentNode, "active");


    },

    /**
     * When we add new value "Filters" step we are also adding it to "Translations" step. Field name
     * will have new3 (or any other number) in its name. We are not realy creating a field here. Instead
     * we are calling for addFilter method to do the job. The only usefull thing we are doing here is
     * generating an id for new field
     *
     * @param Event e Event
     *
     * @access private
     *
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
     * This one is easy. When we click on delete value from "Filters" step we delete the value and it's
     * translation in "Translations" step
     *
     * @param Event e Event
     *
     * @access private
     *
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
     *
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

        var splitedHref  = e.target.parentNode.parentNode.parentNode.id.match(/(new)*(\d+)$/); //    splitedHref[splitedHref.length - 2] == 'new' ? true : false;
        var id = splitedHref[0];

        for(var i = 1; i < this.languageCodes.length; i++)
        {
            $(this.cssPrefix + "form_filters_" +  this.languageCodes[i] + "_" + id).getElementsByTagName("label")[0].firstChild.nodeValue = e.target.value;
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

        if(filters.length > 0 && filters[0].className.split(' ').indexOf('dom_template') !== -1)
        {
            var newValue = filters[0].cloneNode(true);
            Element.removeClassName(newValue, "dom_template");

            if(!this.fieldsList) this.bindDefaultFields();

            var li = this.fieldsList.addRecord(id, newValue, true);

            // The field itself
            var input = li.getElementsByTagName("input")[0];
            input.name = "filters[" + id + "][name]["+this.languageCodes[0]+"]";
            input.value = (value && value.name && value.name[this.languageCodes[0]]) ? value.name[this.languageCodes[0]] : '' ;

            var rangeStartInput = li.getElementsByTagName("input")[1];
            rangeStartInput.name = "filters[" + id + "][rangeStart]";
            rangeStartInput.value = (value && value.rangeStart) ? value.rangeStart : '' ;
            
            var rangeEndInput = li.getElementsByTagName("input")[2];
            rangeEndInput.name = "filters[" + id + "][rangeEnd]";
            rangeEndInput.value = (value && value.rangeEnd) ? value.rangeEnd : '' ;
            
            var specFieldValueIDInput = li.getElementsByTagName("select")[0];
            specFieldValueIDInput.name = "filters[" + id + "][specFieldValueID]";
            specFieldValueIDInput.value = (value && value.specFieldValueID) ? value.specFieldValueID : '' ;
            
            var dateParagraph = document.getElementsByClassName("filter_date_range", li)[0];
            var rangeDateStart = dateParagraph.getElementsByTagName("input")[0];
            var rangeDateEnd = dateParagraph.getElementsByTagName("input")[1];
            
            var rangeDateStartButton = document.getElementsByClassName("calendar_button", dateParagraph)[0];
            var rangeDateEndButton = document.getElementsByClassName("calendar_button", dateParagraph)[1];
            
            rangeDateStart.name = "filters[" + id + "][rangeDateStart]";
            rangeDateEnd.name   = "filters[" + id + "][rangeDateEnd]";
            
            rangeDateStart.id   = this.cssPrefix + "rangeDateStart_" + id;
            rangeDateEnd.id     = this.cssPrefix + "rangeDateEnd_" + id;

            rangeDateStartButton.id = rangeDateStart.id + "_button";
            rangeDateEndButton.id = rangeDateEnd.id + "_button";
            
            Calendar.setup({
                inputField:     rangeDateStart.id,
                ifFormat:       "%d-%m-%Y", 
                button:         rangeDateStartButton.id,
                align:          "BR",
                singleClick:    true
            });
            
            Calendar.setup({
                inputField:     rangeDateEnd.id,
                ifFormat:       "%d-%m-%Y", 
                button:         rangeDateEndButton.id,
                align:          "BR",
                singleClick:    true
            });

            // now insert all translation fields
            for(var i = 1; i < this.languageCodes.length; i++)
            {
                var newValueTranslation = document.getElementsByClassName(this.cssPrefix + "form_filters_value", this.nodes.translations[this.languageCodes[i]])[0].cloneNode(true);
                Element.removeClassName(newValueTranslation, "dom_template");

                newValueTranslation.id = newValueTranslation.id + this.languageCodes[i] + "_" + id;

                var inputTranslation = newValueTranslation.getElementsByTagName("input")[0];
                inputTranslation.name = "filters[" + id + "][name][" + this.languageCodes[i] + "]";
                inputTranslation.value = (value && value.name && value.name[this.languageCodes[i]]) ? value.name[this.languageCodes[i]] : '' ;

                var label = newValueTranslation.getElementsByTagName("label")[0];
                label.appendChild(document.createTextNode(input.value));

                // add to node tree
                var translationsUl = document.getElementsByClassName(this.cssPrefix + "form_filters_translations", this.nodes.translations[this.languageCodes[i]])[0].getElementsByTagName('ul')[0];
                translationsUl.id = this.cssPrefix + "form_"+this.id+'_filters_'+this.languageCodes[i];
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
            window.activeFiltersList.toggleContainer(this.nodes.parent, 'edit');
        }
        else
        {
            this.hideNewFilterAction(this.categoryID);
        }
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
        var self = this;
        if(!e)
        {
            e = window.event;
            e.target = e.srcElement;
        }

        Event.stop(e);

        // Toggle progress won't work on new form
        try
        {
            window.activeFiltersList.toggleProgress(self.nodes.parent);
        }
        catch (e)
        {
            // New item has no pr06r3s5 indicator
        }

        this.clearAllFeedBack();
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
                window.activeFiltersList.toggleContainer(this.nodes.parent, 'edit');
            }
            else
            {

                var div = document.createElement('span');
                Element.addClassName(div, 'filter_title');
                div.appendChild(document.createTextNode(this.nodes.name.value));
                window.activeFiltersList.addRecord(jsonResponse.id, [document.createTextNode(' '), div]);
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
                    if(fieldName == 'filters')
                    {
                        $H(jsonResponse.errors[fieldName]).each(function(value)
                        {
                            self.setFeedback($(self.cssPrefix + "form_" + self.id + "_filters_" + self.languageCodes[0] + "_" + value.key).getElementsByTagName("input")[0], value.value);
                        });
                    }
                    else
                    {
                        this.setFeedback(this.nodes[fieldName], jsonResponse.errors[fieldName]);
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
            window.activeFiltersList.toggleProgress(this.nodes.parent);
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

        Effect.Fade(form.id, {duration: 0.2});
        Effect.BlindUp(form.id, {duration: 0.3});

        setTimeout(function() { link.style.display = 'block'; }, 0.3);
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
        if(!e)
        {
            e = window.event;
            e.target = e.srcElement;
        }

        Event.stop(e);

        var link = $(this.cssPrefix + "item_new_"+categoryID+"_show");
        var form = $(this.cssPrefix + "item_new_"+categoryID+"_form");

        Effect.BlindDown(form.id, {duration: 0.3});
        Effect.Appear(form.id, {duration: 0.66});

        link.style.display = 'none';
        setTimeout(function() {  form.style.height = 'auto'; }, 0.7);
    }
}