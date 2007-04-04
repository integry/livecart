if(!Backend) Backend = {};
if(!Backend.ProductFile) Backend.ProductFile = {};


/******************************************************************************
 * Product files
 * label:files
 *****************************************************************************/
Backend.ProductFile.Callbacks = 
{
    beforeDelete: function(li){ 
        if(confirm(Backend.RelatedProduct.messages.areYouSureYouWantToDelete)) 
        {
            return Backend.ProductFile.Links.deleteFile + "/" + this.getRecordId(li);
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
        return Backend.ProductFile.Links.sort + "?target=" + this.ul.id + "&" + order
    },
    afterSort: function(li, response){ console.info('afterSort') }
}

Backend.ProductFile.Model = Class.create();
Backend.ProductFile.Model.prototype = {
    initialize: function(data, languages)
    {
        this.store(data || {});
        
        if(!this.get('ID', false)) this.isNew = true;
        
        this.languages = $H(languages);
    },
    
    save: function(serializedData, onSaveResponse)
    {
        if(true == this.saving) return;
        this.saving = true;
        this.serverError = false;
        
        var self = this;
        new Ajax.Request(Backend.ProductFile.Links.save,
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
    }
};

Backend.ProductFile.Controller = Class.create();
Backend.ProductFile.Controller.prototype = {
    instances: {},
    
    initialize: function(root, model)
    {        
        this.model = model;
        this.view = new Backend.ProductFile.View(root, this.model.get('Product.ID'));
        
        if(!this.view.nodes.root.id) this.view.nodes.root.id = this.view.prefix + 'list_' + this.model.get('Product.ID') + '_' + this.model.get('ID') + '_form';
        
        var self = this;
        this.createUploadIFrame();
        this.setDefaultValues();
        this.setTranslationValues();
        
        this.bindActions();
        
        Form.State.backup(this.view.nodes.root);
        
        Backend.ProductFile.Controller.prototype.instances[this.view.nodes.root.id] = this;
    },
    
    getInstance: function(rootNode)
    {
        return Backend.ProductFile.Controller.prototype.instances[$(rootNode).id];
    },
    
    createUploadIFrame: function()
    {
        var defaultLanguageID = this.model.getDefaultLanguage()['ID'];
        
        this.view.assign('defaultLanguageID', defaultLanguageID);
        this.view.assign('productID', this.model.get('Product.ID', ''));
        this.view.assign('ID', this.model.get('ID', ''));
        this.view.assign('controller', this);

        this.view.createUploadIFrame();
          
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
        
        Event.observe(this.view.nodes.save, 'click', function(e) { self.onSave(); });
        Event.observe(this.view.nodes.cancel, 'click', function(e) { Event.stop(e); self.onCancel(); });
        Event.observe(this.view.nodes.newFileCancelLink, 'click', function(e) { Event.stop(e); self.onCancel(); });
    },
    
    onSave: function()
    {   
        this.view.nodes.form.action += "/" + this.model.get('ID', '');
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
        console.info(status);
        if('success' == status)
        {
            if(this.model.isNew)
            {
                this.view.assign('ID', this.model.get('ID'));
                this.view.assign('productID', this.model.get('Product.ID'));
                this.view.createNewFile();
                this.model.store('ID', false);
                
                this.hideNewForm();
            }
            else
            {
                this.view.nodes.title.update('FILE' + this.model.get('ID'));
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

Backend.ProductFile.View = Class.create();
Backend.ProductFile.View.prototype = {
    prefix: 'productFile_',
    
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
        this.nodes.description = this.nodes.root.down('.' + this.prefix + 'description');
        this.nodes.allowDownloadDays = this.nodes.root.down('.' + this.prefix + 'allowDownloadDays');
        this.nodes.uploadFile = this.nodes.root.down('.' + this.prefix + 'uploadFile');
        
        this.nodes.title = this.nodes.root.previous('.' + this.prefix + 'title');
        
        this.nodes.newFileCancelLink = $(this.prefix + 'new_' + productID + '_cancel');
        
        this.nodes.translations = this.nodes.root.down('.' + this.prefix + 'translations');
        this.nodes.translationTemplate = this.nodes.translations.down('.' + this.prefix + 'translations_language');
        Element.remove(this.nodes.translationTemplate);
        Element.removeClassName(this.nodes.translationTemplate, 'dom_template');
        
        this.nodes.translationsFieldsets = {};
    },
    
    createUploadIFrame: function()
    {
        var iframe = document.createElement('iframe');
        iframe.name = iframe.id = "productFileUploadIFrame_" + this.get("productID", '') + "_" + this.get("ID", '');
        this.nodes.root.appendChild(iframe);
        this.nodes.form.target = iframe.name;
        
        this.nodes.iframe = iframe;
        
        var controller = this.get('controller', null);
        this.nodes.iframe.controller = controller;
        this.nodes.iframe.action = function(status) { controller.onSaveResponse(status) };
    },
    
    setDefaultLanguageValues: function()
    {
        this.nodes.id.value = this.get('ID', '');
        this.nodes.productID.value = this.get('productID', '');
        
        this.nodes.description.name += '_' + this.get('defaultLanguageID');
        this.nodes.description.value = this.get('name', '');
        
        this.nodes.allowDownloadDays.value = this.get('allowDownloadDays', 0);
        
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
    
    createNewFilep: function()
    {
        var activeList = ActiveList.prototype.getInstance($(this.prefix + "list_" + this.get('productID'))); 
        
        var containerDiv = document.createElement('div');
        containerDiv.update(
            '<span class="' + this.prefix + 'title">' + this.nodes.name.value + '</span>'
            + $('productFile_item_blank').innerHTML
            + '<ul id="productFIle_list_' + this.get('productID') + '_' + this.get('ID') + '" class="productFIle_list activeList_add_sort activeList_add_edit activeList_add_delete activeList_accept_productFIle_list">'
            + '</ul>'
        );
        
        li = activeList.addRecord(this.get('ID'), containerDiv);
        ActiveList.prototype.getInstance($('productFIle_list_' + this.get('productID') + '_' + this.get('ID')), Backend.ProductFile.Links.Callbacks);
        Element.addClassName(li, 'productFile_item');
        
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



/******************************************************************************
 * Product files group
 * label:group
 *****************************************************************************/
Backend.ProductFile.Group = {};
Backend.ProductFile.Group.Callbacks =
{
    beforeDelete: function(li) { 
        if(confirm(Backend.ProductFile.Group.Messages.areYouSureYouWantToDelete)) 
        {
            return Backend.ProductFile.Group.Links.remove + "/" + this.getRecordId(li);
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
        return Backend.ProductFile.Group.Links.sort + '&' + order;
    },
    afterSort: function(li, response) { 
        console.info('afterSort') 
    },
    
    beforeEdit:     function(li) 
    {
        if(!Backend.ProductFile.Group.Controller.prototype.getInstance(li.down('.productFileGroup_form')))
        {
            return Backend.ProductFile.Group.Links.edit + "/" + this.getRecordId(li);
        }
        else
        {
            console.info('asd');
            with(Backend.ProductFile.Group.Controller.prototype.getInstance(li.down('.productFileGroup_form')))
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
        
        var model = new Backend.ProductFile.Group.Model(response, Backend.availableLanguages);
        var group = new Backend.ProductFile.Group.Controller(li.down('.productFileGroup_form'), model);
        group.showForm();
    }
}


Backend.ProductFile.Group.Model = Class.create();
Backend.ProductFile.Group.Model.prototype = {
    defaultLanguage: false,
    
    initialize: function(data, languages)
    {
        this.store(data || {});
        
        if(!this.get('ID', false)) this.isNew = true;
        
        this.languages = $H(languages);
    },
    
    save: function(serializedData, onSaveResponse)
    {
        if(true == this.saving) return;
        this.saving = true;
        this.serverError = false;
        
        var self = this;
        new Ajax.Request(Backend.ProductFile.Group.Links.save,
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
        
        onSaveResponse.call(this, response.status);
        this.saving = false;
    }
}

Backend.ProductFile.Group.Controller = Class.create();
Backend.ProductFile.Group.Controller.prototype = {
    instances: {},
    
    initialize: function(root, model)
    {        
        this.model = model;
        this.view = new Backend.ProductFile.Group.View(root, this.model.get('Product.ID'));
        
        if(!this.view.nodes.root.id) this.view.nodes.root.id = this.view.prefix + 'list_' + this.model.get('Product.ID') + '_' + this.model.get('ID', '') + '_form';
        
        this.setDefaultValues();
        this.setTranslationValues();
        
        this.bindActions();
        
        Form.State.backup(this.view.nodes.root);
        
        Backend.ProductFile.Group.Controller.prototype.instances[this.view.nodes.root.id] = this;
    },
    
    getInstance: function(rootNode)
    {
        return Backend.ProductFile.Group.Controller.prototype.instances[$(rootNode).id];
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
        ActiveForm.prototype.hideMenuItems($("productFile_menu_" + this.model.get('Product.ID')), [$(this.view.prefix + "new_" +this.model.get('Product.ID') + "_show"), $("productFile_new_" + this.model.get('Product.ID') + "_show")]);
        ActiveForm.prototype.hideNewItemForm($(this.view.prefix + "new_" + this.model.get('Product.ID') + "_show"), this.view.nodes.root); 
    },
    
    showNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems($("productFile_menu_" + this.model.get('Product.ID')), [$(this.view.prefix + "new_" + this.model.get('Product.ID') + "_cancel"), $("productFileGroup_new_" + this.model.get('Product.ID')) + "_cancel"]);
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


Backend.ProductFile.Group.View = Class.create();
Backend.ProductFile.Group.View.prototype = {
    prefix: 'productFileGroup_',
    
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
            + $('productFileGroup_item_blank').innerHTML
            + '<ul id="productRelationship_list_' + this.get('productID') + '_' + this.get('ID') + '" class="productRelationship_list activeList_add_sort activeList_add_edit activeList_add_delete activeList_accept_productRelationship_list">'
            + '</ul>'
        );
        
        li = activeList.addRecord(this.get('ID'), containerDiv);
        ActiveList.prototype.getInstance($('productRelationship_list_' + this.get('productID') + '_' + this.get('ID')), Backend.ProductFile.activeListCallbacks);
        Element.addClassName(li, this.prefix  + 'item');
        
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

Backend.RegisterMVC(Backend.ProductFile);
Backend.RegisterMVC(Backend.ProductFile.Group);