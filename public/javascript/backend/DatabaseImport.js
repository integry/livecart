Backend.DatabaseImport = Class.create();
Backend.DatabaseImport.prototype =
{
    form: null,

    request: null,

    formerLength: 0,

    types: [],

    initialize: function(form)
    {
        this.form = form;
        this.request = new LiveCart.AjaxRequest(this.form, null, this.formResponse.bind(this), {onInteractive: this.dataResponse.bind(this), onFailure: function(fff) { console.log('failure', fff); } });
    },

    formResponse: function(originalRequest)
    {
        if (originalRequest.responseData.errors)
        {
            ActiveForm.prototype.setErrorMessages(this.form, originalRequest.responseData.errors);
        }
        else
        {
            this.dataResponse(originalRequest);
        }
    },

    dataResponse: function(originalRequest)
    {
        var response = originalRequest.responseText.substr(this.formerLength + 1);
        this.formerLength = originalRequest.responseText.length;

        var portions = response.split('|');

        for (var k = 0; k < portions.length; k++)
        {
			response = eval('(' + decode64(portions[k]) + ')');

            // import completed
            if (null == response)
            {
                new Backend.SaveConfirmationMessage($('completeMessage'));
            }

            // list of record types
            else if (response.types)
            {
                this.setRecordTypes(response.types);
            }

            // progress
            else if (response.type)
            {
                this.setProgress(response);
            }
        }
    },

    setRecordTypes: function(types)
    {
        this.form.down('.controls').down('.progressIndicator').hide();
        $('importProgress').show();

        this.form.down('.controls').down('.submit').disabled = true;

        this.types = types;

        for (var k = 0; k < types.length; k++)
        {
            $('progress_' + types[k]).show();
        }

        $('progress_' + types[0]).addClassName('inProgress');
    },

    setProgress: function(response)
    {
        var li = $('progress_' + response.type);
        li.down('.progressBar').show();
        li.down('.progressTotal').update(response.total);
//console.log(response.progress);
        if (response.progress > 0)
        {
            li.down('.progressCount').update(response.progress);
            var progressWidth = (parseFloat(response.progress) / parseFloat(response.total)) * li.down('.progressBar').clientWidth;
            li.down('.progressBarIndicator').style.width = progressWidth + 'px';
        }
        else
        {
            li.removeClassName('inProgress');
            li.addClassName('completed');
			li.down('.progressCount').update(response.total);
			li.down('.progressBarIndicator').hide();

            // find next record type
            for (var k = 0; k < this.types.length; k++)
            {
                if ((this.types[k] == response.type) && this.types[k + 1])
                {
                    $('progress_' + this.types[k + 1]).addClassName('inProgress');
                }
            }
        }
    }
}