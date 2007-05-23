if(!Backend) Backend = {};
if(!Backend.ProductFile) Backend.ProductFile = {};


/******************************************************************************
 * Product files
 * label:files
 *****************************************************************************/
Backend.ProductFile.Callbacks = 
{
    beforeDelete: function(li){ 
        if(confirm(Backend.ProductFile.Messages.areYouSureYouWantToDelete)) 
        {
            return Backend.ProductFile.Links.deleteFile + "/" + this.getRecordId(li);
        }
    },
    afterDelete: function(li, response){
        if(!response.error) {
            this.remove(li);
            var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
            tabControl.setCounter('tabProductFiles', tabControl.getCounter('tabProductFiles') - 1);
        }
    },
    beforeSort: function(li, order){ 
        return Backend.ProductFile.Links.sort + "?target=" + this.ul.id + "&" + order
    },
    afterSort: function(li, response){ console.info('afterSort') },

    
    beforeEdit: function(li) {
        var container = this.getContainer(li, 'edit');
        if(this.isContainerEmpty(li, 'edit')) {
            var container  = this.getContainer(li, 'edit');
            new Insertion.Bottom(container, $('productFile_item_blank').innerHTML);
            container.down('.productFile_form').show();
            return Backend.ProductFile.Links.edit + "/" + this.getRecordId(li);
        }
        else {
            if(container.style.display != 'block')
            {
                this.toggleContainerOn(container);
                setTimeout(function() { 
                    ActiveForm.prototype.initTinyMceFields(li)
                }, 500);
            }
            else
            {
                this.toggleContainerOff(container);
                ActiveForm.prototype.destroyTinyMceFields(li);
            }
        }
    },
    
    afterEdit: function(li, response) {
        var model = new Backend.ProductFile.Model(eval("(" + response + ")"), Backend.availableLanguages);
        var controller = new Backend.ProductFile.Controller(li.down('.productFile_form'), model);
        
        this.toggleContainer(li, 'edit');
        
        setTimeout(function() { 
            ActiveForm.prototype.initTinyMceFields(li);
        }, 500);
    }
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
        
        if(!this.model.isNew) {
            this.createDownloadLink();
        }
        
        this.setTranslationValues();
        
        this.bindActions();
        
        Form.State.backup(this.view.nodes.form);
        
        Backend.ProductFile.Controller.prototype.instances[this.view.nodes.root.id] = this;
        
        new SectionExpander(this.view.nodes.root);
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
    
    createDownloadLink: function()
    {
        this.view.assign('linkText', this.view.nodes.fileName.value + '.' + this.model.get('extension', 'ext'));
        this.view.assign('ID', this.model.get('ID', 0));
        
        this.view.createDownloadLink();
    },
    
    setDefaultValues: function()
    {
        var defaultLanguageID = this.model.getDefaultLanguage()['ID'];
        
        this.view.assign('defaultLanguageID', defaultLanguageID);
        this.view.assign('title', this.model.get('title_' + defaultLanguageID));
        this.view.assign('description', this.model.get('description_' + defaultLanguageID));
        this.view.assign('allowDownloadDays', this.model.get('allowDownloadDays'));
        this.view.assign('ID', this.model.get('ID', ''));
        this.view.assign('productID', this.model.get('Product.ID', ''));
        this.view.assign('isNew', this.model.isNew);
        this.view.assign('fileName', this.model.get('fileName'));
        this.view.assign('extension', this.model.get('extension'));
        
        this.view.setDefaultLanguageValues();
    },
    
    setTranslationValues: function()
    {
        var self = this;
        
        this.view.assign('defaultLanguageID', this.model.getDefaultLanguage()['ID']);
        var description = {};
        var title = {};
        this.model.languages.each(function(lang)
        {
           description[lang.key] = self.model.get('description_' + lang.key);
           title[lang.key] = self.model.get('title_' + lang.key);
        });
        
        this.view.assign('title', title);
        this.view.assign('description', description);
        this.view.assign('languages', this.model.languages);
        this.view.setOtherLanguagesValues(this.model);  
    },
    
    bindActions: function()
    {
        var self = this;
        if(!this.model.isNew) 
        {
            Event.observe(this.view.nodes.title, 'keyup', function(e) { self.onTitleChange(); });
        }
        Event.observe(this.view.nodes.form, 'submit', function(e) { ActiveForm.prototype.resetErrorMessages(self.view.nodes.root); });
        Event.observe(this.view.nodes.cancel, 'click', function(e) { Event.stop(e); self.onCancel(); });
        Event.observe(this.view.nodes.newFileCancelLink, 'click', function(e) { Event.stop(e); self.onCancel(); });
        Event.observe(this.view.nodes.save, 'click', function(e) { self.onSave(); });
    },
    
    onSave: function()
    {
        this.view.nodes.form.action = (this.model.isNew ? Backend.ProductFile.Links.create : Backend.ProductFile.Links.update) + "/?random=" + Math.random() * 100000; 
	    Element.saveTinyMceFields(this.view.nodes.form);  
    },
    
    onCancel: function()
    {
        Form.State.restore(this.view.nodes.root);
        
        ActiveForm.prototype.resetErrorMessages(this.view.nodes.root);
        if(this.model.isNew)
        {
            this.hideNewForm();
            ActiveForm.prototype.resetTinyMceFields(this.view.nodes.root);
        }
        else
        {
            var activeList = ActiveList.prototype.getInstance(this.view.prefix + "list_" + this.model.get('Product.ID', '') + "_" + this.model.get('ProductFileGroup.ID', ''));
            activeList.toggleContainer(activeList.getContainer(li, 'edit'));
        }
    },
    
    onTitleChange: function()
    {
        this.view.nodes.fileHeader.update(this.view.nodes.title.value);
    },
    
    onSaveResponse: function(status)
    {
        if('success' == status)
        {
            if(this.model.isNew)
            {
                this.view.assign('ID', this.model.get('ID'));
                this.view.assign('productID', this.model.get('Product.ID'));
                this.view.createNewFile();
                this.model.store('ID', false);
                this.hideNewForm();
                Form.State.restore(this.view.nodes.form);
                
                var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
                tabControl.setCounter('tabProductFiles', tabControl.getCounter('tabProductFiles') + 1); 
            }
            else
            {
                this.view.nodes.fileHeader.update(this.view.nodes.title.value);
                this.createDownloadLink();

                var activeList = ActiveList.prototype.getInstance(this.view.prefix + "list_" + this.model.get('Product.ID', '') + "_" + this.model.get('ProductFileGroup.ID', ''));
                activeList.toggleContainer(this.view.nodes.root.up("li"), 'edit');
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
        ActiveForm.prototype.hideMenuItems($(this.view.prefix + "menu_" + this.model.get('Product.ID')), [$(this.view.prefix + "new_" +this.model.get('Product.ID') + "_show"), $("productFileGroup_new_" + this.model.get('Product.ID') + "_show")]);
        ActiveForm.prototype.hideNewItemForm($(this.view.prefix + "new_" + this.model.get('Product.ID') + "_show"), this.view.nodes.root);
        
        ActiveForm.prototype.destroyTinyMceFields(this.view.nodes.root); 
    },
    
    showNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems($(this.view.prefix + "menu_" + this.model.get('Product.ID')), [$(this.view.prefix + "new_" + this.model.get('Product.ID') + "_cancel"), $("selectProduct_" + this.model.get('Product.ID')) + "_cancel"]);
        ActiveForm.prototype.showNewItemForm($(this.view.prefix + "new_" + this.model.get('Product.ID') + "_show"), this.view.nodes.root); 
        
        var self = this;
        setTimeout(function() { 
            ActiveForm.prototype.initTinyMceFields(self.view.nodes.root); 
        }, 500);
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
        this.nodes.title = this.nodes.root.down('.' + this.prefix + 'title');
        this.nodes.allowDownloadDays = this.nodes.root.down('.' + this.prefix + 'allowDownloadDays');
        this.nodes.uploadFile = this.nodes.root.down('.' + this.prefix + 'uploadFile');
        
        this.nodes.extension = this.nodes.root.down('.' + this.prefix + 'extension');
        
        if(this.nodes.root.up('li')) 
        {
            this.nodes.fileHeader = this.nodes.root.up('li').down('.' + this.prefix + 'item_title');
        }
        
        this.nodes.fileName = this.nodes.root.down('.' + this.prefix + 'fileName');
        this.nodes.fileNameBlock = this.nodes.root.down('.' + this.prefix + 'fileName_div');
        
        this.nodes.downloadLink = this.nodes.root.down('.' + this.prefix + 'download_link');
        
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
        iframe.hide();
        iframe.name = iframe.id = "productFileUploadIFrame_" + this.get("productID", '') + "_" + this.get("ID", '');
        this.nodes.root.appendChild(iframe);
        this.nodes.form.target = iframe.name;
        
        this.nodes.iframe = iframe;
        
        var controller = this.get('controller', null);
        this.nodes.iframe.controller = controller;
        this.nodes.iframe.action = function(status) { controller.onSaveResponse(status) };
    },
    
    createDownloadLink: function()
    {
        this.nodes.downloadLink.href = Backend.ProductFile.Links.download + "/" + this.get('ID');
        this.nodes.downloadLink.update(this.get('linkText'));  
        this.nodes.downloadLink.show();
        this.clear();
    },
    
    setDefaultLanguageValues: function()
    {
        this.nodes.id.value = this.get('ID', '');
        this.nodes.productID.value = this.get('productID', '');
        
        if(this.get('isNew')) 
        {
           this.nodes.fileNameBlock.hide(); 
        }
        else
        {
            this.nodes.fileNameBlock.show();
            this.nodes.fileName.value = this.get('fileName');
            this.nodes.extension.update('.' + this.get('extension'));
        }
        
        this.nodes.description.name += '_' + this.get('defaultLanguageID');
        this.nodes.description.value = this.get('description', '');
        
        this.nodes.title.name += '_' + this.get('defaultLanguageID');
        this.nodes.title.value = this.get('title', '');
        
        this.nodes.allowDownloadDays.value = this.get('allowDownloadDays', 0);
        
        this.nodes.form.action += "/" + this.get('ID', '');
        
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
            
            var description = translationFieldset.down('.' + self.prefix + 'description');
            description.name += '_' + language.key;
            description.value = self.get('description.' + language.key , '');
            
            var title = translationFieldset.down('.' + self.prefix + 'title');
            title.name += '_' + language.key;
            title.value = self.get('title.' + language.key , '');
            
            self.nodes.translationsFieldsets[language.value.ID] = translationFieldset;
            self.nodes.translations.appendChild(translationFieldset);
            
            altLanguagesCount++;
        });
        
        if(altLanguagesCount < 1) this.nodes.translations.hide();
        
        this.clear();
    }, 
    
    createNewFile: function()
    {
        var activeList = ActiveList.prototype.getInstance(this.prefix + 'list_' + this.get('productID') + '_');
        
        var fileContainer = document.createElement('div');
        fileContainer.update('<span class="' + this.prefix + 'item_title">' + this.nodes.title.value + '</span>');
        
        var li = activeList.addRecord(this.get('ID'), fileContainer)
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
        activeList.toggleContainer(li, 'edit');
        
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
            var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
            tabControl.setCounter('tabProductFiles', tabControl.getCounter('tabProductFiles') - li.getElementsByTagName('li').length);
            this.remove(li);
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
            with(Backend.ProductFile.Group.Controller.prototype.getInstance(li.down('.productFileGroup_form')))
            {
                if(this.getContainer(li, 'edit').style.display != 'block') showForm();
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
        
        var action = this.isNew ? Backend.ProductFile.Group.Links.create : Backend.ProductFile.Group.Links.update;
        
        new Ajax.Request(action,
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
        new SectionExpander(this.view.nodes.root);
        
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
        Event.observe(this.view.nodes.name, 'keyup', function(e) { Event.stop(e); self.onNameChange(); });
        
    },
    
    onSave: function()
    {        
        var self = this;
        ActiveForm.prototype.resetErrorMessages(this.view.nodes.root);
        this.model.save(Form.serialize(this.view.nodes.root), function(status) { 
            self.onSaveResponse(status) ;
        });
    },
    
    onNameChange: function() 
    {
        if(!this.model.isNew) this.view.nodes.title.update(this.view.nodes.name.value); 
    },    
    
    onCancel: function()
    {
        Form.State.restore(this.view.nodes.root);
        ActiveForm.prototype.resetErrorMessages(this.view.nodes.root);
        
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
        
        if(this.nodes.root.up('li')) this.nodes.title = this.nodes.root.up('li').down('.' + this.prefix + 'title');
        
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
        var activeList = ActiveList.prototype.getInstance($(this.prefix + "list_" + this.get('productID')), Backend.ProductFile.Group.Callbacks, Backend.ProductFile.Group.Messages); 
        
        var containerDiv = document.createElement('div');
        containerDiv.update(
            '<span class="' + this.prefix + 'title">' + this.nodes.name.value + '</span>'
            + $('productFileGroup_item_blank').innerHTML
            + '<ul id="productFile_list_' + this.get('productID') + '_' + this.get('ID') + '" class="productFile_list activeList_add_sort activeList_add_edit activeList_add_delete activeList_accept_productFile_list">'
            + '</ul>'
        );
        
        li = activeList.addRecord(this.get('ID'), containerDiv);
        ActiveList.prototype.getInstance($('productFile_list_' + this.get('productID') + '_' + this.get('ID')), Backend.ProductFile.Callbacks, Backend.ProductFile.Messages);
        Element.addClassName(li, this.prefix  + 'item');
                
        activeList.highlight(li);
        activeList.touch();
        
        ActiveList.prototype.recreateVisibleLists();
        
        this.clear();
    },
    
    showForm: function()
    {
        var li = this.nodes.root.up("li");
        var activeList = ActiveList.prototype.getInstance(li.up('ul'));
        li.down('.' + this.prefix + 'form').style.display = 'block';
        ActiveList.prototype.collapseAll();

        activeList.toggleContainerOn(activeList.getContainer(li, 'edit'));
        
        this.clear();
    },
    
    hideForm: function()
    {
        var li = this.nodes.root.up("li");
        var activeList = ActiveList.prototype.getInstance(li.up('ul'));
        
        activeList.toggleContainerOff(activeList.getContainer(li, 'edit'));
        
        this.clear();
    }
    
}

Backend.RegisterMVC(Backend.ProductFile);
Backend.RegisterMVC(Backend.ProductFile.Group);