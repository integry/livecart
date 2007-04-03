if(!Backend) Backend = {};
if(!Backend.Product) Backend.Product = {};
Backend.RelatedProduct = {
    activeListCallbacks: 
    {
        beforeDelete: function(li){ 
            if(confirm(Backend.RelatedProduct.messages.areYouSureYouWantToDelete)) 
            {
                return Backend.RelatedProduct.links.deleteRelated + "/?relatedProductID=" + this.getRecordId(li);
            }
        },
        afterDelete: function(li, response){
            if(!response.error) {
                this.remove(li);
                var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
                tabControl.setCounter('tabProductRelationship', tabControl.getCounter('tabProductRelationship') - 1);
            }
        },
        beforeSort: function(li, order){ 
            return Backend.RelatedProduct.links.sort + "?target=" + this.ul.id + "&" + order
        },
        afterSort: function(li, response){ console.info('afterSort') }
    },
    addProductToList: function(productID, relatedProductID)
    {
        var self = this;
        new Ajax.Request(this.links.related + "/?relatedProductID=" + relatedProductID , {
           method: 'get',
           onSuccess: function(response) {
                console.info(response.responseText);
                var evaluatedResponse;
                try
                {
                    evaluatedResponse = eval("(" + response.responseText + ")");
                }
                catch(e) {}
                
                if(evaluatedResponse && evaluatedResponse.error && evaluatedResponse.error.length > 0)
                {
                    // error
                    new Backend.SaveConfirmationMessage($('productRelationshipMsg_' + productID), { message: evaluatedResponse.error, type: 'red' });
                }
                else
                {
                    try
                    {
                        var relatedList = ActiveList.prototype.getInstance($("productRelationship_list_" + productID + "_"));
                        relatedList.addRecord(relatedProductID, response.responseText, true);

                        var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
                        tabControl.setCounter('tabProductRelationship', tabControl.getCounter('tabProductRelationship') + 1);
                    } 
                    catch(e)
                    {
                        console.info(e);
                    }
                }
               
           }
        });
    }
};


Backend.RelatedProduct.SelectProductPopup = Class.create();
Backend.RelatedProduct.SelectProductPopup.prototype = {
    height: 520,
    width:  800,
    onProductSelect: function() {},
    
    initialize: function(link, title, options)
    {
        try
        {
            this.link = link;
            this.title = title;
            
            if(options.onProductSelect) this.onProductSelect = options.onProductSelect;
            
            this.createPopup();
        }
        catch(e)
        {
            console.info(e);
        }
    },
    
    createPopup: function()
    {
        Backend.RelatedProduct.SelectProductPopup.prototype.popup = window.open(this.link, this.title, 'resizable=1,width=' + this.width + ',height=' + this.height);
        Backend.RelatedProduct.SelectProductPopup.prototype.popup.focus();
                        
        Event.observe(window, 'unload', function() { Backend.RelatedProduct.SelectProductPopup.prototype.popup.close(); });
        
        window.selectProductPopup = this;
    },
    
    getSelectedProduct: function(productID)
    {
        this.productID = productID;
        // Backend.RelatedProduct.SelectProductPopup.prototype.popup.opener.focus();
        // Backend.RelatedProduct.SelectProductPopup.prototype.popup.close();
        
        var self = this;
        setTimeout(function() { self.onProductSelect.call(self); }, 100)
        
    }
}

Backend.RelatedProduct.Group = {};

Backend.RelatedProduct.Group.Callbacks =
{
    beforeDelete: function(li) { 
        if(confirm(Backend.RelatedProduct.Group.Messages.areYouSureYouWantToDelete)) 
        {
            return Backend.RelatedProduct.Group.Links.remove + "/" + this.getRecordId(li);
        }
    },
    afterDelete: function(li, response) {
        if(!response.error) {
            this.remove(li);
            var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
            tabControl.setCounter('tabProductRelationship', tabControl.getCounter('tabProductRelationship') - li.getElementsByTagName('li').length);
        }
    },
    beforeSort: function(li, order) { 
        return Backend.RelatedProduct.Group.Links.sort + '&' + order;
    },
    afterSort: function(li, response) { 
        console.info('afterSort') 
    },
    
    beforeEdit:     function(li) 
    {
        if(!Backend.RelatedProduct.Group.Controller.prototype.getInstance(li.down('.productRelationshipGroup_form')))
        {
            return Backend.RelatedProduct.Group.Links.edit + "/" + this.getRecordId(li);
        }
        else
        {
            console.info('asd');
            with(Backend.RelatedProduct.Group.Controller.prototype.getInstance(li.down('.productRelationshipGroup_form')))
            {
                if('block' != view.nodes.root.style.display) showForm();
                else hideForm();
            }
        }
    },
    afterEdit:      function(li, response) 
    { 
        try
        {
            response = eval("(" + response + ")");
        }
        catch(e)
        {
            console.info(e);
        }
        
        var model = new Backend.RelatedProduct.Group.Model(response, Backend.availableLanguages);
        var group = new Backend.RelatedProduct.Group.Controller(li.down('.productRelationshipGroup_form'), model);
        group.showForm();
    }
}


Backend.RelatedProduct.Group.Model = Class.create();
Backend.RelatedProduct.Group.Model.prototype = {
    defaultLanguage: false,
    
    initialize: function(data, languages)
    {
        this.store(data || {});
        
        if(!this.get('ID', false)) this.isNew = true;
        
        this.languages = $H(languages);
    },
    
    getDefaultLanguage: function()
    {
        if(this.defaultLanguage === false) 
        {
            var self = this;
            this.languages.each(function(language)
            {
                if(parseInt(language.value.isDefault))
                {
                    self.defaultLanguage = language.value;
                    throw $break;
                }   
            });
        }
        
        return this.defaultLanguage;
    }, 
    
    save: function(serializedData, onSaveResponse)
    {
        if(true == this.saving) return;
        this.saving = true;
        this.serverError = false;
        
        var self = this;
        new Ajax.Request(Backend.RelatedProduct.Group.Links.save,
        {
            method: 'post',
            postBody: serializedData,
            onSuccess: function(response) 
            {
                var responseHash = {};
                try 
                { 
                    responseHash = eval("(" + response.responseText + ")");
                }
                catch(e)
                {
                    responseHash['status'] = 'serverError';
                    responseHash['responseText'] = response.responseText;
                }
                
                self.afterSave(responseHash, onSaveResponse);
            }
        });
    },
    
    afterSave: function(response, onSaveResponse)
    {
        switch(response.status)
        {
            case 'success':
                this.store('ID', response.ID);
                break;
            case 'failure':
                this.errors = response.errors;
                break;
            case 'serverError':
                this.serverError = response.responseText;
            	break;
        }
        
        onSaveResponse.call(this, response.status, 'aasdasd', 'asdasd', 'asdads');
        this.saving = false;
    }
}

Backend.RelatedProduct.Group.Controller = Class.create();
Backend.RelatedProduct.Group.Controller.prototype = {
    instances: {},
    
    initialize: function(root, model)
    {        
        this.model = model;
        this.view = new Backend.RelatedProduct.Group.View(root, this.model.get('Product.ID'));
        
        if(!this.view.nodes.root.id) this.view.nodes.root.id = this.view.prefix + 'list_' + this.model.get('Product.ID') + '_' + this.model.get('ID') + '_form';
        
        this.setDefaultValues();
        this.setTranslationValues();
        
        this.bindActions();
        
        Form.State.backup(this.view.nodes.root);
        
        Backend.RelatedProduct.Group.Controller.prototype.instances[this.view.nodes.root.id] = this;
    },
    
    getInstance: function(rootNode)
    {
        return Backend.RelatedProduct.Group.Controller.prototype.instances[$(rootNode).id];
    },
    
    setDefaultValues: function()
    {
        var defaultLanguageID = this.model.getDefaultLanguage()['ID'];
        
        this.view.assign('defaultLanguageID', defaultLanguageID);
        this.view.assign('name', this.model.get('name_' + defaultLanguageID));
        this.view.assign('ID', this.model.get('ID', ''));
        this.view.assign('productID', this.model.get('Product.ID', ''));
        
        this.view.setDefaultLanguageValues();
    },
    
    setTranslationValues: function()
    {
        var self = this;
        
        this.view.assign('defaultLanguageID', this.model.getDefaultLanguage()['ID']);
        var name = {};
        this.model.languages.each(function(lang)
        {
           name[lang.key] = self.model.get('name_' + lang.key)
        });
        
        this.view.assign('name', name);
        this.view.assign('languages', this.model.languages);
        this.view.setOtherLanguagesValues(this.model);  
    },
    
    bindActions: function()
    {
        var self = this;
        
        Event.observe(this.view.nodes.save, 'click', function(e) { Event.stop(e); self.onSave(); });
        Event.observe(this.view.nodes.cancel, 'click', function(e) { Event.stop(e); self.onCancel(); });
        Event.observe(this.view.nodes.newGroupCancelLink, 'click', function(e) { Event.stop(e); self.onCancel(); });
        
    },
    
    onSave: function()
    {        
        var self = this;
        ActiveForm.prototype.resetErrorMessages(this.view.nodes.root);
        this.model.save(Form.serialize(this.view.nodes.root), function(status) { 
            self.onSaveResponse(status) ;
        });
    },
    
    
    onCancel: function()
    {
        Form.State.restore(this.view.nodes.root);
        
        if(this.model.isNew)
        {
            this.hideNewForm();
        }
        else
        {
            this.hideForm();
        }
    },
    
    onSaveResponse: function(status)
    {
        if('success' == status)
        {
            if(this.model.isNew)
            {
                this.view.assign('ID', this.model.get('ID'));
                this.view.assign('productID', this.model.get('Product.ID'));
                this.view.createNewGroup();
                this.model.store('ID', false);
                
                this.hideNewForm();
            }
            else
            {
                this.view.nodes.title.update(this.view.nodes.name.value);
                this.hideForm();
            }
            Form.State.restore(this.view.nodes.root);
        }
        else
        {
            ActiveForm.prototype.setErrorMessages(this.view.nodes.root, this.model.errors);
        }
    },
    
    hideNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems($("productRelationship_menu_" + this.model.get('Product.ID')), [$(this.view.prefix + "new_" +this.model.get('Product.ID') + "_show"), $("selectProduct_" + this.model.get('Product.ID'))]);
        ActiveForm.prototype.hideNewItemForm($(this.view.prefix + "new_" + this.model.get('Product.ID') + "_show"), this.view.nodes.root); 
    },
    
    showNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems($("productRelationship_menu_" + this.model.get('Product.ID')), [$(this.view.prefix + "new_" + this.model.get('Product.ID') + "_cancel"), $("selectProduct_" + this.model.get('Product.ID')) + "_cancel"]);
        ActiveForm.prototype.showNewItemForm($(this.view.prefix + "new_" + this.model.get('Product.ID') + "_show"), this.view.nodes.root); 
    }, 
    
    showForm: function()
    {
        this.view.showForm();
    },
    
    hideForm: function()
    {
        this.view.hideForm();
    }
    
}


Backend.RelatedProduct.Group.View = Class.create();
Backend.RelatedProduct.Group.View.prototype = {
    prefix: 'productRelationshipGroup_',
    
    initialize: function(root, productID)
    {
        this.findNodes(root, productID);
        this.clear();
    },
    
    findNodes: function(root, productID)
    {
        this.nodes = {};
        this.nodes.root = root;
        this.nodes.form = ('FORM' == this.nodes.root.tagName) ? this.nodes.root : this.nodes.root.down('form');
        
        // controls
        this.nodes.controls = this.nodes.root.down('.' + this.prefix + 'controls');
        this.nodes.save = this.nodes.controls.down('.' + this.prefix + 'save');
        this.nodes.cancel = this.nodes.controls.down('.' + this.prefix + 'cancel');
        
        this.nodes.id = this.nodes.root.down('.' + this.prefix + 'ID');
        this.nodes.productID = this.nodes.root.down('.' + this.prefix + 'productID');
        this.nodes.name = this.nodes.root.down('.' + this.prefix + 'name');
        
        this.nodes.title = this.nodes.root.previous('.' + this.prefix + 'title');
        
        this.nodes.newGroupCancelLink = $(this.prefix + 'new_' + productID + '_cancel');
        
        this.nodes.translations = this.nodes.root.down('.' + this.prefix + 'translations');
        this.nodes.translationTemplate = this.nodes.translations.down('.' + this.prefix + 'translations_language');
        Element.remove(this.nodes.translationTemplate);
        Element.removeClassName(this.nodes.translationTemplate, 'dom_template');
        
        this.nodes.translationsFieldsets = {};
    },
    
    setDefaultLanguageValues: function()
    {
        this.nodes.id.value = this.get('ID', '');
        this.nodes.productID.value = this.get('productID', '');
        
        this.nodes.name.name += '_' + this.get('defaultLanguageID');
        this.nodes.name.value = this.get('name', '');
        
        this.clear();
    },
    
    setOtherLanguagesValues: function()
    {
        var defaultLanguageID = this.get('defaultLanguageID');
        
        var self = this;
        var languages = this.get('languages', {});
        altLanguagesCount = 0;
        languages.each(function(language)
        {
            if(language.value.ID == defaultLanguageID) return;
            
            var translationFieldset = self.nodes.translationTemplate.cloneNode(true);
            
            translationFieldset.down('legend').update(language.value.name);
            
            var name = translationFieldset.down('.' + self.prefix + 'name');
            name.name += '_' + language.key;
            name.value = self.get('name_' + language.key , '')
            
            self.nodes.translationsFieldsets[language.value.ID] = translationFieldset;
            self.nodes.translations.appendChild(translationFieldset);
            
            altLanguagesCount++;
        });
        
        if(altLanguagesCount < 1) this.nodes.translations.hide();
        
        this.clear();
    }, 
    
    createNewGroup: function()
    {
        var activeList = ActiveList.prototype.getInstance($(this.prefix + "list_" + this.get('productID'))); 
        
        var containerDiv = document.createElement('div');
        containerDiv.update(
            '<span class="' + this.prefix + 'title">' + this.nodes.name.value + '</span>'
            + $('productRelationshipGroup_item_blank').innerHTML
            + '<ul id="productRelationship_list_' + this.get('productID') + '_' + this.get('ID') + '" class="productRelationship_list activeList_add_sort activeList_add_edit activeList_add_delete activeList_accept_productRelationship_list">'
            + '</ul>'
        );
        
        li = activeList.addRecord(this.get('ID'), containerDiv);
        ActiveList.prototype.getInstance($('productRelationship_list_' + this.get('productID') + '_' + this.get('ID')), Backend.RelatedProduct.activeListCallbacks);
        Element.addClassName(li, 'productRelationshipGroup_item');
        
        activeList.highlight(li);
        activeList.touch();
        this.clear();
    },
    
    showForm: function()
    {
        var li = this.nodes.root.up("li");
        var activeList = ActiveList.prototype.getInstance(li.up('ul'));
        
        ActiveList.prototype.collapseAll();
        this.nodes.title.hide();
        activeList.toggleContainerOn(li.down('.' + this.prefix + 'form'));
        
        this.clear();
    },
    
    hideForm: function()
    {
        var li = this.nodes.root.up("li");
        var activeList = ActiveList.prototype.getInstance(li.up('ul'));
        
        this.nodes.title.show();
        activeList.toggleContainerOff(li.down('.' + this.prefix + 'form'));
        
        this.clear();
    }
    
}

Backend.RegisterMVC(Backend.RelatedProduct.Group);