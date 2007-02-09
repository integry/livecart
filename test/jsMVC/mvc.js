var View = Class.create();
View.prototype = {   
    initialize: function(parentNode)
    {
        this.parent = View.prototype;
        
        this.nodes = {};
        this.nodes.parent = parentNode;
    }
}

var Controller = Class.create();
Controller.prototype = {   
    initialize: function(view)
    {
        try
        {        
            if(view) this._view = view;
            else throw Error('You should pass view object as first argument when creating controller');
            
            this._view._controller = this;
            
            if(this._view._findNodes) this._view._findNodes();
            else throw Error('View should have _findNodes method');
            
            if(this._view._bindNodes) this._view._bindNodes();
            else throw Error('View should have _bindNodes method');
            
            if(this._view._initialize) this._view._initialize();
            if(this._initialize) this._initialize();
        }
        catch(e)
        {
            console.error("Controller failed");
            console.info(e)   
            console.trace() 
        }
    }
}