


/***************************************************
 * library\prototype\prototype.js
 ***************************************************/

/*  Prototype JavaScript framework, version 1.5.1
 *  (c) 2005-2007 Sam Stephenson
 *
 *  Prototype is freely distributable under the terms of an MIT-style license.
 *  For details, see the Prototype web site: http://www.prototypejs.org/
 *
/*--------------------------------------------------------------------------*/

var Prototype = {
  Version: '1.5.1',

  Browser: {
    IE:     !!(window.attachEvent && !window.opera),
    Opera:  !!window.opera,
    WebKit: navigator.userAgent.indexOf('AppleWebKit/') > -1,
    Gecko:  navigator.userAgent.indexOf('Gecko') > -1 && navigator.userAgent.indexOf('KHTML') == -1
  },

  BrowserFeatures: {
    XPath: !!document.evaluate,
    ElementExtensions: !!window.HTMLElement,
    SpecificElementExtensions:
      (document.createElement('div').__proto__ !==
       document.createElement('form').__proto__)
  },

  ScriptFragment: '<script[^>]*>([\u0001-\uFFFF]*?)</script>',
  JSONFilter: /^\/\*-secure-\s*(.*)\s*\*\/\s*$/,

  emptyFunction: function() { },
  K: function(x) { return x }
}

var Class = {
  create: function() {
    return function() {
      this.initialize.apply(this, arguments);
    }
  }
}

var Abstract = new Object();

Object.extend = function(destination, source) {
  for (var property in source) {
    destination[property] = source[property];
  }
  return destination;
}

Object.extend(Object, {
  inspect: function(object) {
    try {
      if (object === undefined) return 'undefined';
      if (object === null) return 'null';
      return object.inspect ? object.inspect() : object.toString();
    } catch (e) {
      if (e instanceof RangeError) return '...';
      throw e;
    }
  },

  toJSON: function(object) {
    var type = typeof object;
    switch(type) {
      case 'undefined':
      case 'function':
      case 'unknown': return;
      case 'boolean': return object.toString();
    }
    if (object === null) return 'null';
    if (object.toJSON) return object.toJSON();
    if (object.ownerDocument === document) return;
    var results = [];
    for (var property in object) {
      var value = Object.toJSON(object[property]);
      if (value !== undefined)
        results.push(property.toJSON() + ': ' + value);
    }
    return '{' + results.join(', ') + '}';
  },

  keys: function(object) {
    var keys = [];
    for (var property in object)
      keys.push(property);
    return keys;
  },

  values: function(object) {
    var values = [];
    for (var property in object)
      values.push(object[property]);
    return values;
  },

  clone: function(object) {
    return Object.extend({}, object);
  }
});

Function.prototype.bind = function() {
  var __method = this, args = $A(arguments), object = args.shift();
  return function() {
    return __method.apply(object, args.concat($A(arguments)));
  }
}

Function.prototype.bindAsEventListener = function(object) {
  var __method = this, args = $A(arguments), object = args.shift();
  return function(event) {
    return __method.apply(object, [event || window.event].concat(args));
  }
}

Object.extend(Number.prototype, {
  toColorPart: function() {
    return this.toPaddedString(2, 16);
  },

  succ: function() {
    return this + 1;
  },

  times: function(iterator) {
    $R(0, this, true).each(iterator);
    return this;
  },

  toPaddedString: function(length, radix) {
    var string = this.toString(radix || 10);
    return '0'.times(length - string.length) + string;
  },

  toJSON: function() {
    return isFinite(this) ? this.toString() : 'null';
  }
});

Date.prototype.toJSON = function() {
  return '"' + this.getFullYear() + '-' +
    (this.getMonth() + 1).toPaddedString(2) + '-' +
    this.getDate().toPaddedString(2) + 'T' +
    this.getHours().toPaddedString(2) + ':' +
    this.getMinutes().toPaddedString(2) + ':' +
    this.getSeconds().toPaddedString(2) + '"';
};

var Try = {
  these: function() {
    var returnValue;

    for (var i = 0, length = arguments.length; i < length; i++) {
      var lambda = arguments[i];
      try {
        returnValue = lambda();
        break;
      } catch (e) {}
    }

    return returnValue;
  }
}

/*--------------------------------------------------------------------------*/

var PeriodicalExecuter = Class.create();
PeriodicalExecuter.prototype = {
  initialize: function(callback, frequency) {
    this.callback = callback;
    this.frequency = frequency;
    this.currentlyExecuting = false;

    this.registerCallback();
  },

  registerCallback: function() {
    this.timer = setInterval(this.onTimerEvent.bind(this), this.frequency * 1000);
  },

  stop: function() {
    if (!this.timer) return;
    clearInterval(this.timer);
    this.timer = null;
  },

  onTimerEvent: function() {
    if (!this.currentlyExecuting) {
      try {
        this.currentlyExecuting = true;
        this.callback(this);
      } finally {
        this.currentlyExecuting = false;
      }
    }
  }
}
Object.extend(String, {
  interpret: function(value) {
    return value == null ? '' : String(value);
  },
  specialChar: {
    '\b': '\\b',
    '\t': '\\t',
    '\n': '\\n',
    '\f': '\\f',
    '\r': '\\r',
    '\\': '\\\\'
  }
});

Object.extend(String.prototype, {
  gsub: function(pattern, replacement) {
    var result = '', source = this, match;
    replacement = arguments.callee.prepareReplacement(replacement);

    while (source.length > 0) {
      if (match = source.match(pattern)) {
        result += source.slice(0, match.index);
        result += String.interpret(replacement(match));
        source  = source.slice(match.index + match[0].length);
      } else {
        result += source, source = '';
      }
    }
    return result;
  },

  sub: function(pattern, replacement, count) {
    replacement = this.gsub.prepareReplacement(replacement);
    count = count === undefined ? 1 : count;

    return this.gsub(pattern, function(match) {
      if (--count < 0) return match[0];
      return replacement(match);
    });
  },

  scan: function(pattern, iterator) {
    this.gsub(pattern, iterator);
    return this;
  },

  truncate: function(length, truncation) {
    length = length || 30;
    truncation = truncation === undefined ? '...' : truncation;
    return this.length > length ?
      this.slice(0, length - truncation.length) + truncation : this;
  },

  strip: function() {
    return this.replace(/^\s+/, '').replace(/\s+$/, '');
  },

  stripTags: function() {
    return this.replace(/<\/?[^>]+>/gi, '');
  },

  stripScripts: function() {
    return this.replace(new RegExp(Prototype.ScriptFragment, 'img'), '');
  },

  extractScripts: function() {
    var matchAll = new RegExp(Prototype.ScriptFragment, 'img');
    var matchOne = new RegExp(Prototype.ScriptFragment, 'im');
    return (this.match(matchAll) || []).map(function(scriptTag) {
      return (scriptTag.match(matchOne) || ['', ''])[1];
    });
  },

  evalScripts: function() {
    return this.extractScripts().map(function(script) { return eval(script) });
  },

  escapeHTML: function() {
    var self = arguments.callee;
    self.text.data = this;
    return self.div.innerHTML;
  },

  unescapeHTML: function() {
    var div = document.createElement('div');
    div.innerHTML = this.stripTags();
    return div.childNodes[0] ? (div.childNodes.length > 1 ?
      $A(div.childNodes).inject('', function(memo, node) { return memo+node.nodeValue }) :
      div.childNodes[0].nodeValue) : '';
  },

  toQueryParams: function(separator) {
    var match = this.strip().match(/([^?#]*)(#.*)?$/);
    if (!match) return {};

    return match[1].split(separator || '&').inject({}, function(hash, pair) {
      if ((pair = pair.split('='))[0]) {
        var key = decodeURIComponent(pair.shift());
        var value = pair.length > 1 ? pair.join('=') : pair[0];
        if (value != undefined) value = decodeURIComponent(value);

        if (key in hash) {
          if (hash[key].constructor != Array) hash[key] = [hash[key]];
          hash[key].push(value);
        }
        else hash[key] = value;
      }
      return hash;
    });
  },

  toArray: function() {
    return this.split('');
  },

  succ: function() {
    return this.slice(0, this.length - 1) +
      String.fromCharCode(this.charCodeAt(this.length - 1) + 1);
  },

  times: function(count) {
    var result = '';
    for (var i = 0; i < count; i++) result += this;
    return result;
  },

  camelize: function() {
    var parts = this.split('-'), len = parts.length;
    if (len == 1) return parts[0];

    var camelized = this.charAt(0) == '-'
      ? parts[0].charAt(0).toUpperCase() + parts[0].substring(1)
      : parts[0];

    for (var i = 1; i < len; i++)
      camelized += parts[i].charAt(0).toUpperCase() + parts[i].substring(1);

    return camelized;
  },

  capitalize: function() {
    return this.charAt(0).toUpperCase() + this.substring(1).toLowerCase();
  },

  underscore: function() {
    return this.gsub(/::/, '/').gsub(/([A-Z]+)([A-Z][a-z])/,'#{1}_#{2}').gsub(/([a-z\d])([A-Z])/,'#{1}_#{2}').gsub(/-/,'_').toLowerCase();
  },

  dasherize: function() {
    return this.gsub(/_/,'-');
  },

  inspect: function(useDoubleQuotes) {
    var escapedString = this.gsub(/[\x00-\x1f\\]/, function(match) {
      var character = String.specialChar[match[0]];
      return character ? character : '\\u00' + match[0].charCodeAt().toPaddedString(2, 16);
    });
    if (useDoubleQuotes) return '"' + escapedString.replace(/"/g, '\\"') + '"';
    return "'" + escapedString.replace(/'/g, '\\\'') + "'";
  },

  toJSON: function() {
    return this.inspect(true);
  },

  unfilterJSON: function(filter) {
    return this.sub(filter || Prototype.JSONFilter, '#{1}');
  },

  evalJSON: function(sanitize) {
    var json = this.unfilterJSON();
    try {
      if (!sanitize || (/^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/.test(json)))
        return eval('(' + json + ')');
    } catch (e) { }
    throw new SyntaxError('Badly formed JSON string: ' + this.inspect());
  },

  include: function(pattern) {
    return this.indexOf(pattern) > -1;
  },

  startsWith: function(pattern) {
    return this.indexOf(pattern) === 0;
  },

  endsWith: function(pattern) {
    var d = this.length - pattern.length;
    return d >= 0 && this.lastIndexOf(pattern) === d;
  },

  empty: function() {
    return this == '';
  },

  blank: function() {
    return /^\s*$/.test(this);
  }
});

if (Prototype.Browser.WebKit || Prototype.Browser.IE) Object.extend(String.prototype, {
  escapeHTML: function() {
    return this.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  },
  unescapeHTML: function() {
    return this.replace(/&amp;/g,'&').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
  }
});

String.prototype.gsub.prepareReplacement = function(replacement) {
  if (typeof replacement == 'function') return replacement;
  var template = new Template(replacement);
  return function(match) { return template.evaluate(match) };
}

String.prototype.parseQuery = String.prototype.toQueryParams;

Object.extend(String.prototype.escapeHTML, {
  div:  document.createElement('div'),
  text: document.createTextNode('')
});

with (String.prototype.escapeHTML) div.appendChild(text);

var Template = Class.create();
Template.Pattern = /(^|.|\r|\n)(#\{(.*?)\})/;
Template.prototype = {
  initialize: function(template, pattern) {
    this.template = template.toString();
    this.pattern  = pattern || Template.Pattern;
  },

  evaluate: function(object) {
    return this.template.gsub(this.pattern, function(match) {
      var before = match[1];
      if (before == '\\') return match[2];
      return before + String.interpret(object[match[3]]);
    });
  }
}

var $break = {}, $continue = new Error('"throw $continue" is deprecated, use "return" instead');

var Enumerable = {
  each: function(iterator) {
    var index = 0;
    try {
      this._each(function(value) {
        iterator(value, index++);
      });
    } catch (e) {
      if (e != $break) throw e;
    }
    return this;
  },

  eachSlice: function(number, iterator) {
    var index = -number, slices = [], array = this.toArray();
    while ((index += number) < array.length)
      slices.push(array.slice(index, index+number));
    return slices.map(iterator);
  },

  all: function(iterator) {
    var result = true;
    this.each(function(value, index) {
      result = result && !!(iterator || Prototype.K)(value, index);
      if (!result) throw $break;
    });
    return result;
  },

  any: function(iterator) {
    var result = false;
    this.each(function(value, index) {
      if (result = !!(iterator || Prototype.K)(value, index))
        throw $break;
    });
    return result;
  },

  collect: function(iterator) {
    var results = [];
    this.each(function(value, index) {
      results.push((iterator || Prototype.K)(value, index));
    });
    return results;
  },

  detect: function(iterator) {
    var result;
    this.each(function(value, index) {
      if (iterator(value, index)) {
        result = value;
        throw $break;
      }
    });
    return result;
  },

  findAll: function(iterator) {
    var results = [];
    this.each(function(value, index) {
      if (iterator(value, index))
        results.push(value);
    });
    return results;
  },

  grep: function(pattern, iterator) {
    var results = [];
    this.each(function(value, index) {
      var stringValue = value.toString();
      if (stringValue.match(pattern))
        results.push((iterator || Prototype.K)(value, index));
    })
    return results;
  },

  include: function(object) {
    var found = false;
    this.each(function(value) {
      if (value == object) {
        found = true;
        throw $break;
      }
    });
    return found;
  },

  inGroupsOf: function(number, fillWith) {
    fillWith = fillWith === undefined ? null : fillWith;
    return this.eachSlice(number, function(slice) {
      while(slice.length < number) slice.push(fillWith);
      return slice;
    });
  },

  inject: function(memo, iterator) {
    this.each(function(value, index) {
      memo = iterator(memo, value, index);
    });
    return memo;
  },

  invoke: function(method) {
    var args = $A(arguments).slice(1);
    return this.map(function(value) {
      return value[method].apply(value, args);
    });
  },

  max: function(iterator) {
    var result;
    this.each(function(value, index) {
      value = (iterator || Prototype.K)(value, index);
      if (result == undefined || value >= result)
        result = value;
    });
    return result;
  },

  min: function(iterator) {
    var result;
    this.each(function(value, index) {
      value = (iterator || Prototype.K)(value, index);
      if (result == undefined || value < result)
        result = value;
    });
    return result;
  },

  partition: function(iterator) {
    var trues = [], falses = [];
    this.each(function(value, index) {
      ((iterator || Prototype.K)(value, index) ?
        trues : falses).push(value);
    });
    return [trues, falses];
  },

  pluck: function(property) {
    var results = [];
    this.each(function(value, index) {
      results.push(value[property]);
    });
    return results;
  },

  reject: function(iterator) {
    var results = [];
    this.each(function(value, index) {
      if (!iterator(value, index))
        results.push(value);
    });
    return results;
  },

  sortBy: function(iterator) {
    return this.map(function(value, index) {
      return {value: value, criteria: iterator(value, index)};
    }).sort(function(left, right) {
      var a = left.criteria, b = right.criteria;
      return a < b ? -1 : a > b ? 1 : 0;
    }).pluck('value');
  },

  toArray: function() {
    return this.map();
  },

  zip: function() {
    var iterator = Prototype.K, args = $A(arguments);
    if (typeof args.last() == 'function')
      iterator = args.pop();

    var collections = [this].concat(args).map($A);
    return this.map(function(value, index) {
      return iterator(collections.pluck(index));
    });
  },

  size: function() {
    return this.toArray().length;
  },

  inspect: function() {
    return '#<Enumerable:' + this.toArray().inspect() + '>';
  }
}

Object.extend(Enumerable, {
  map:     Enumerable.collect,
  find:    Enumerable.detect,
  select:  Enumerable.findAll,
  member:  Enumerable.include,
  entries: Enumerable.toArray
});
var $A = Array.from = function(iterable) {
  if (!iterable) return [];
  if (iterable.toArray) {
    return iterable.toArray();
  } else {
    var results = [];
    for (var i = 0, length = iterable.length; i < length; i++)
      results.push(iterable[i]);
    return results;
  }
}

if (Prototype.Browser.WebKit) {
  $A = Array.from = function(iterable) {
    if (!iterable) return [];
    if (!(typeof iterable == 'function' && iterable == '[object NodeList]') &&
      iterable.toArray) {
      return iterable.toArray();
    } else {
      var results = [];
      for (var i = 0, length = iterable.length; i < length; i++)
        results.push(iterable[i]);
      return results;
    }
  }
}

Object.extend(Array.prototype, Enumerable);

if (!Array.prototype._reverse)
  Array.prototype._reverse = Array.prototype.reverse;

Object.extend(Array.prototype, {
  _each: function(iterator) {
    for (var i = 0, length = this.length; i < length; i++)
      iterator(this[i]);
  },

  clear: function() {
    this.length = 0;
    return this;
  },

  first: function() {
    return this[0];
  },

  last: function() {
    return this[this.length - 1];
  },

  compact: function() {
    return this.select(function(value) {
      return value != null;
    });
  },

  flatten: function() {
    return this.inject([], function(array, value) {
      return array.concat(value && value.constructor == Array ?
        value.flatten() : [value]);
    });
  },

  without: function() {
    var values = $A(arguments);
    return this.select(function(value) {
      return !values.include(value);
    });
  },

  indexOf: function(object) {
    for (var i = 0, length = this.length; i < length; i++)
      if (this[i] == object) return i;
    return -1;
  },

  reverse: function(inline) {
    return (inline !== false ? this : this.toArray())._reverse();
  },

  reduce: function() {
    return this.length > 1 ? this : this[0];
  },

  uniq: function(sorted) {
    return this.inject([], function(array, value, index) {
      if (0 == index || (sorted ? array.last() != value : !array.include(value)))
        array.push(value);
      return array;
    });
  },

  clone: function() {
    return [].concat(this);
  },

  size: function() {
    return this.length;
  },

  inspect: function() {
    return '[' + this.map(Object.inspect).join(', ') + ']';
  },

  toJSON: function() {
    var results = [];
    this.each(function(object) {
      var value = Object.toJSON(object);
      if (value !== undefined) results.push(value);
    });
    return '[' + results.join(', ') + ']';
  }
});

Array.prototype.toArray = Array.prototype.clone;

function $w(string) {
  string = string.strip();
  return string ? string.split(/\s+/) : [];
}

if (Prototype.Browser.Opera){
  Array.prototype.concat = function() {
    var array = [];
    for (var i = 0, length = this.length; i < length; i++) array.push(this[i]);
    for (var i = 0, length = arguments.length; i < length; i++) {
      if (arguments[i].constructor == Array) {
        for (var j = 0, arrayLength = arguments[i].length; j < arrayLength; j++)
          array.push(arguments[i][j]);
      } else {
        array.push(arguments[i]);
      }
    }
    return array;
  }
}
var Hash = function(object) {
  if (object instanceof Hash) this.merge(object);
  else Object.extend(this, object || {});
};

Object.extend(Hash, {
  toQueryString: function(obj) {
    var parts = [];
    parts.add = arguments.callee.addPair;

    this.prototype._each.call(obj, function(pair) {
      if (!pair.key) return;
      var value = pair.value;

      if (value && typeof value == 'object') {
        if (value.constructor == Array) value.each(function(value) {
          parts.add(pair.key, value);
        });
        return;
      }
      parts.add(pair.key, value);
    });

    return parts.join('&');
  },

  toJSON: function(object) {
    var results = [];
    this.prototype._each.call(object, function(pair) {
      var value = Object.toJSON(pair.value);
      if (value !== undefined) results.push(pair.key.toJSON() + ': ' + value);
    });
    return '{' + results.join(', ') + '}';
  }
});

Hash.toQueryString.addPair = function(key, value, prefix) {
  key = encodeURIComponent(key);
  if (value === undefined) this.push(key);
  else this.push(key + '=' + (value == null ? '' : encodeURIComponent(value)));
}

Object.extend(Hash.prototype, Enumerable);
Object.extend(Hash.prototype, {
  _each: function(iterator) {
    for (var key in this) {
      var value = this[key];
      if (value && value == Hash.prototype[key]) continue;

      var pair = [key, value];
      pair.key = key;
      pair.value = value;
      iterator(pair);
    }
  },

  keys: function() {
    return this.pluck('key');
  },

  values: function() {
    return this.pluck('value');
  },

  merge: function(hash) {
    return $H(hash).inject(this, function(mergedHash, pair) {
      mergedHash[pair.key] = pair.value;
      return mergedHash;
    });
  },

  remove: function() {
    var result;
    for(var i = 0, length = arguments.length; i < length; i++) {
      var value = this[arguments[i]];
      if (value !== undefined){
        if (result === undefined) result = value;
        else {
          if (result.constructor != Array) result = [result];
          result.push(value)
        }
      }
      delete this[arguments[i]];
    }
    return result;
  },

  toQueryString: function() {
    return Hash.toQueryString(this);
  },

  inspect: function() {
    return '#<Hash:{' + this.map(function(pair) {
      return pair.map(Object.inspect).join(': ');
    }).join(', ') + '}>';
  },

  toJSON: function() {
    return Hash.toJSON(this);
  }
});

function $H(object) {
  if (object instanceof Hash) return object;
  return new Hash(object);
};

// Safari iterates over shadowed properties
if (function() {
  var i = 0, Test = function(value) { this.key = value };
  Test.prototype.key = 'foo';
  for (var property in new Test('bar')) i++;
  return i > 1;
}()) Hash.prototype._each = function(iterator) {
  var cache = [];
  for (var key in this) {
    var value = this[key];
    if ((value && value == Hash.prototype[key]) || cache.include(key)) continue;
    cache.push(key);
    var pair = [key, value];
    pair.key = key;
    pair.value = value;
    iterator(pair);
  }
};
ObjectRange = Class.create();
Object.extend(ObjectRange.prototype, Enumerable);
Object.extend(ObjectRange.prototype, {
  initialize: function(start, end, exclusive) {
    this.start = start;
    this.end = end;
    this.exclusive = exclusive;
  },

  _each: function(iterator) {
    var value = this.start;
    while (this.include(value)) {
      iterator(value);
      value = value.succ();
    }
  },

  include: function(value) {
    if (value < this.start)
      return false;
    if (this.exclusive)
      return value < this.end;
    return value <= this.end;
  }
});

var $R = function(start, end, exclusive) {
  return new ObjectRange(start, end, exclusive);
}

var Ajax = {
  getTransport: function() {
    return Try.these(
      function() {return new XMLHttpRequest()},
      function() {return new ActiveXObject('Msxml2.XMLHTTP')},
      function() {return new ActiveXObject('Microsoft.XMLHTTP')}
    ) || false;
  },

  activeRequestCount: 0
}

Ajax.Responders = {
  responders: [],

  _each: function(iterator) {
    this.responders._each(iterator);
  },

  register: function(responder) {
    if (!this.include(responder))
      this.responders.push(responder);
  },

  unregister: function(responder) {
    this.responders = this.responders.without(responder);
  },

  dispatch: function(callback, request, transport, json) {
    this.each(function(responder) {
      if (typeof responder[callback] == 'function') {
        try {
          responder[callback].apply(responder, [request, transport, json]);
        } catch (e) {}
      }
    });
  }
};

Object.extend(Ajax.Responders, Enumerable);

Ajax.Responders.register({
  onCreate: function() {
    Ajax.activeRequestCount++;
  },
  onComplete: function() {
    Ajax.activeRequestCount--;
  }
});

Ajax.Base = function() {};
Ajax.Base.prototype = {
  setOptions: function(options) {
    this.options = {
      method:       'post',
      asynchronous: true,
      contentType:  'application/x-www-form-urlencoded',
      encoding:     'UTF-8',
      parameters:   ''
    }
    Object.extend(this.options, options || {});

    this.options.method = this.options.method.toLowerCase();
    if (typeof this.options.parameters == 'string')
      this.options.parameters = this.options.parameters.toQueryParams();
  }
}

Ajax.Request = Class.create();
Ajax.Request.Events =
  ['Uninitialized', 'Loading', 'Loaded', 'Interactive', 'Complete'];

Ajax.Request.prototype = Object.extend(new Ajax.Base(), {
  _complete: false,

  initialize: function(url, options) {
    this.transport = Ajax.getTransport();
    this.setOptions(options);
    this.request(url);
  },

  request: function(url) {
    this.url = url;
    this.method = this.options.method;
    var params = Object.clone(this.options.parameters);

    if (!['get', 'post'].include(this.method)) {
      // simulate other verbs over post
      params['_method'] = this.method;
      this.method = 'post';
    }

    this.parameters = params;

    if (params = Hash.toQueryString(params)) {
      // when GET, append parameters to URL
      if (this.method == 'get')
        this.url += (this.url.include('?') ? '&' : '?') + params;
      else if (/Konqueror|Safari|KHTML/.test(navigator.userAgent))
        params += '&_=';
    }

    try {
      if (this.options.onCreate) this.options.onCreate(this.transport);
      Ajax.Responders.dispatch('onCreate', this, this.transport);

      this.transport.open(this.method.toUpperCase(), this.url,
        this.options.asynchronous);

      if (this.options.asynchronous)
        setTimeout(function() { this.respondToReadyState(1) }.bind(this), 10);

      this.transport.onreadystatechange = this.onStateChange.bind(this);
      this.setRequestHeaders();

      this.body = this.method == 'post' ? (this.options.postBody || params) : null;
      this.transport.send(this.body);

      /* Force Firefox to handle ready state 4 for synchronous requests */
      if (!this.options.asynchronous && this.transport.overrideMimeType)
        this.onStateChange();

    }
    catch (e) {
      this.dispatchException(e);
    }
  },

  onStateChange: function() {
    var readyState = this.transport.readyState;
    if (readyState > 1 && !((readyState == 4) && this._complete))
      this.respondToReadyState(this.transport.readyState);
  },

  setRequestHeaders: function() {
    var headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'X-Prototype-Version': Prototype.Version,
      'Accept': 'text/javascript, text/html, application/xml, text/xml, */*'
    };

    if (this.method == 'post') {
      headers['Content-type'] = this.options.contentType +
        (this.options.encoding ? '; charset=' + this.options.encoding : '');

      /* Force "Connection: close" for older Mozilla browsers to work
       * around a bug where XMLHttpRequest sends an incorrect
       * Content-length header. See Mozilla Bugzilla #246651.
       */
      if (this.transport.overrideMimeType &&
          (navigator.userAgent.match(/Gecko\/(\d{4})/) || [0,2005])[1] < 2005)
            headers['Connection'] = 'close';
    }

    // user-defined headers
    if (typeof this.options.requestHeaders == 'object') {
      var extras = this.options.requestHeaders;

      if (typeof extras.push == 'function')
        for (var i = 0, length = extras.length; i < length; i += 2)
          headers[extras[i]] = extras[i+1];
      else
        $H(extras).each(function(pair) { headers[pair.key] = pair.value });
    }

    for (var name in headers)
      this.transport.setRequestHeader(name, headers[name]);
  },

  success: function() {
    return !this.transport.status
        || (this.transport.status >= 200 && this.transport.status < 300);
  },

  respondToReadyState: function(readyState) {
    var state = Ajax.Request.Events[readyState];
    var transport = this.transport, json = this.evalJSON();

    if (state == 'Complete') {
      try {
        this._complete = true;
        (this.options['on' + this.transport.status]
         || this.options['on' + (this.success() ? 'Success' : 'Failure')]
         || Prototype.emptyFunction)(transport, json);
      } catch (e) {
        this.dispatchException(e);
      }

      var contentType = this.getHeader('Content-type');
      if (contentType && contentType.strip().
        match(/^(text|application)\/(x-)?(java|ecma)script(;.*)?$/i))
          this.evalResponse();
    }

    try {
      (this.options['on' + state] || Prototype.emptyFunction)(transport, json);
      Ajax.Responders.dispatch('on' + state, this, transport, json);
    } catch (e) {
      this.dispatchException(e);
    }

    if (state == 'Complete') {
      // avoid memory leak in MSIE: clean up
      this.transport.onreadystatechange = Prototype.emptyFunction;
    }
  },

  getHeader: function(name) {
    try {
      return this.transport.getResponseHeader(name);
    } catch (e) { return null }
  },

  evalJSON: function() {
    try {
      var json = this.getHeader('X-JSON');
      return json ? json.evalJSON() : null;
    } catch (e) { return null }
  },

  evalResponse: function() {
    try {
      return eval((this.transport.responseText || '').unfilterJSON());
    } catch (e) {
      this.dispatchException(e);
    }
  },

  dispatchException: function(exception) {
    (this.options.onException || Prototype.emptyFunction)(this, exception);
    Ajax.Responders.dispatch('onException', this, exception);
  }
});

Ajax.Updater = Class.create();

Object.extend(Object.extend(Ajax.Updater.prototype, Ajax.Request.prototype), {
  initialize: function(container, url, options) {
    this.container = {
      success: (container.success || container),
      failure: (container.failure || (container.success ? null : container))
    }

    this.transport = Ajax.getTransport();
    this.setOptions(options);

    var onComplete = this.options.onComplete || Prototype.emptyFunction;
    this.options.onComplete = (function(transport, param) {
      this.updateContent();
      onComplete(transport, param);
    }).bind(this);

    this.request(url);
  },

  updateContent: function() {
    var receiver = this.container[this.success() ? 'success' : 'failure'];
    var response = this.transport.responseText;

    if (!this.options.evalScripts) response = response.stripScripts();

    if (receiver = $(receiver)) {
      if (this.options.insertion)
        new this.options.insertion(receiver, response);
      else
        receiver.update(response);
    }

    if (this.success()) {
      if (this.onComplete)
        setTimeout(this.onComplete.bind(this), 10);
    }
  }
});

Ajax.PeriodicalUpdater = Class.create();
Ajax.PeriodicalUpdater.prototype = Object.extend(new Ajax.Base(), {
  initialize: function(container, url, options) {
    this.setOptions(options);
    this.onComplete = this.options.onComplete;

    this.frequency = (this.options.frequency || 2);
    this.decay = (this.options.decay || 1);

    this.updater = {};
    this.container = container;
    this.url = url;

    this.start();
  },

  start: function() {
    this.options.onComplete = this.updateComplete.bind(this);
    this.onTimerEvent();
  },

  stop: function() {
    this.updater.options.onComplete = undefined;
    clearTimeout(this.timer);
    (this.onComplete || Prototype.emptyFunction).apply(this, arguments);
  },

  updateComplete: function(request) {
    if (this.options.decay) {
      this.decay = (request.responseText == this.lastText ?
        this.decay * this.options.decay : 1);

      this.lastText = request.responseText;
    }
    this.timer = setTimeout(this.onTimerEvent.bind(this),
      this.decay * this.frequency * 1000);
  },

  onTimerEvent: function() {
    this.updater = new Ajax.Updater(this.container, this.url, this.options);
  }
});
function $(element) {
  if (arguments.length > 1) {
    for (var i = 0, elements = [], length = arguments.length; i < length; i++)
      elements.push($(arguments[i]));
    return elements;
  }
  if (typeof element == 'string')
    element = document.getElementById(element);
  return Element.extend(element);
}

if (Prototype.BrowserFeatures.XPath) {
  document._getElementsByXPath = function(expression, parentElement) {
    var results = [];
    var query = document.evaluate(expression, $(parentElement) || document,
      null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);
    for (var i = 0, length = query.snapshotLength; i < length; i++)
      results.push(query.snapshotItem(i));
    return results;
  };

  document.getElementsByClassName = function(className, parentElement) {
    var q = ".//*[contains(concat(' ', @class, ' '), ' " + className + " ')]";
    return document._getElementsByXPath(q, parentElement);
  }

} else document.getElementsByClassName = function(className, parentElement) {
  var children = ($(parentElement) || document.body).getElementsByTagName('*');
  var elements = [], child;
  for (var i = 0, length = children.length; i < length; i++) {
    child = children[i];
    if (Element.hasClassName(child, className))
      elements.push(Element.extend(child));
  }
  return elements;
};

/*--------------------------------------------------------------------------*/

if (!window.Element) var Element = {};

Element.extend = function(element) {
  var F = Prototype.BrowserFeatures;
  if (!element || !element.tagName || element.nodeType == 3 ||
   element._extended || F.SpecificElementExtensions || element == window)
    return element;

  var methods = {}, tagName = element.tagName, cache = Element.extend.cache,
   T = Element.Methods.ByTag;

  // extend methods for all tags (Safari doesn't need this)
  if (!F.ElementExtensions) {
    Object.extend(methods, Element.Methods),
    Object.extend(methods, Element.Methods.Simulated);
  }

  // extend methods for specific tags
  if (T[tagName]) Object.extend(methods, T[tagName]);

  for (var property in methods) {
    var value = methods[property];
    if (typeof value == 'function' && !(property in element))
      element[property] = cache.findOrStore(value);
  }

  element._extended = Prototype.emptyFunction;
  return element;
};

Element.extend.cache = {
  findOrStore: function(value) {
    return this[value] = this[value] || function() {
      return value.apply(null, [this].concat($A(arguments)));
    }
  }
};

Element.Methods = {
  visible: function(element) {
    return $(element).style.display != 'none';
  },

  toggle: function(element) {
    element = $(element);
    Element[Element.visible(element) ? 'hide' : 'show'](element);
    return element;
  },

  hide: function(element) {
    $(element).style.display = 'none';
    return element;
  },

  show: function(element) {
    $(element).style.display = '';
    return element;
  },

  remove: function(element) {
    element = $(element);
    element.parentNode.removeChild(element);
    return element;
  },

  update: function(element, html) {
    html = typeof html == 'undefined' ? '' : html.toString();
    $(element).innerHTML = html.stripScripts();
    setTimeout(function() {html.evalScripts()}, 10);
    return element;
  },

  replace: function(element, html) {
    element = $(element);
    html = typeof html == 'undefined' ? '' : html.toString();
    if (element.outerHTML) {
      element.outerHTML = html.stripScripts();
    } else {
      var range = element.ownerDocument.createRange();
      range.selectNodeContents(element);
      element.parentNode.replaceChild(
        range.createContextualFragment(html.stripScripts()), element);
    }
    setTimeout(function() {html.evalScripts()}, 10);
    return element;
  },

  inspect: function(element) {
    element = $(element);
    var result = '<' + element.tagName.toLowerCase();
    $H({'id': 'id', 'className': 'class'}).each(function(pair) {
      var property = pair.first(), attribute = pair.last();
      var value = (element[property] || '').toString();
      if (value) result += ' ' + attribute + '=' + value.inspect(true);
    });
    return result + '>';
  },

  recursivelyCollect: function(element, property) {
    element = $(element);
    var elements = [];
    while (element = element[property])
      if (element.nodeType == 1)
        elements.push(Element.extend(element));
    return elements;
  },

  ancestors: function(element) {
    return $(element).recursivelyCollect('parentNode');
  },

  descendants: function(element) {
    return $A($(element).getElementsByTagName('*')).each(Element.extend);
  },

  firstDescendant: function(element) {
    element = $(element).firstChild;
    while (element && element.nodeType != 1) element = element.nextSibling;
    return $(element);
  },

  immediateDescendants: function(element) {
    if (!(element = $(element).firstChild)) return [];
    while (element && element.nodeType != 1) element = element.nextSibling;
    if (element) return [element].concat($(element).nextSiblings());
    return [];
  },

  previousSiblings: function(element) {
    return $(element).recursivelyCollect('previousSibling');
  },

  nextSiblings: function(element) {
    return $(element).recursivelyCollect('nextSibling');
  },

  siblings: function(element) {
    element = $(element);
    return element.previousSiblings().reverse().concat(element.nextSiblings());
  },

  match: function(element, selector) {
    if (typeof selector == 'string')
      selector = new Selector(selector);
    return selector.match($(element));
  },

  up: function(element, expression, index) {
    element = $(element);
    if (arguments.length == 1) return $(element.parentNode);
    var ancestors = element.ancestors();
    return expression ? Selector.findElement(ancestors, expression, index) :
      ancestors[index || 0];
  },

  down: function(element, expression, index) {
    element = $(element);
    if (arguments.length == 1) return element.firstDescendant();
    var descendants = element.descendants();
    return expression ? Selector.findElement(descendants, expression, index) :
      descendants[index || 0];
  },

  previous: function(element, expression, index) {
    element = $(element);
    if (arguments.length == 1) return $(Selector.handlers.previousElementSibling(element));
    var previousSiblings = element.previousSiblings();
    return expression ? Selector.findElement(previousSiblings, expression, index) :
      previousSiblings[index || 0];
  },

  next: function(element, expression, index) {
    element = $(element);
    if (arguments.length == 1) return $(Selector.handlers.nextElementSibling(element));
    var nextSiblings = element.nextSiblings();
    return expression ? Selector.findElement(nextSiblings, expression, index) :
      nextSiblings[index || 0];
  },

  getElementsBySelector: function() {
    var args = $A(arguments), element = $(args.shift());
    return Selector.findChildElements(element, args);
  },

  getElementsByClassName: function(element, className) {
    return document.getElementsByClassName(className, element);
  },

  readAttribute: function(element, name) {
    element = $(element);
    if (Prototype.Browser.IE) {
      if (!element.attributes) return null;
      var t = Element._attributeTranslations;
      if (t.values[name]) return t.values[name](element, name);
      if (t.names[name])  name = t.names[name];
      var attribute = element.attributes[name];
      return attribute ? attribute.nodeValue : null;
    }
    return element.getAttribute(name);
  },

  getHeight: function(element) {
    return $(element).getDimensions().height;
  },

  getWidth: function(element) {
    return $(element).getDimensions().width;
  },

  classNames: function(element) {
    return new Element.ClassNames(element);
  },

  hasClassName: function(element, className) {
    if (!(element = $(element))) return;
    var elementClassName = element.className;
    if (elementClassName.length == 0) return false;
    if (elementClassName == className ||
        elementClassName.match(new RegExp("(^|\\s)" + className + "(\\s|$)")))
      return true;
    return false;
  },

  addClassName: function(element, className) {
    if (!(element = $(element))) return;
    Element.classNames(element).add(className);
    return element;
  },

  removeClassName: function(element, className) {
    if (!(element = $(element))) return;
    Element.classNames(element).remove(className);
    return element;
  },

  toggleClassName: function(element, className) {
    if (!(element = $(element))) return;
    Element.classNames(element)[element.hasClassName(className) ? 'remove' : 'add'](className);
    return element;
  },

  observe: function() {
    Event.observe.apply(Event, arguments);
    return $A(arguments).first();
  },

  stopObserving: function() {
    Event.stopObserving.apply(Event, arguments);
    return $A(arguments).first();
  },

  // removes whitespace-only text node children
  cleanWhitespace: function(element) {
    element = $(element);
    var node = element.firstChild;
    while (node) {
      var nextNode = node.nextSibling;
      if (node.nodeType == 3 && !/\S/.test(node.nodeValue))
        element.removeChild(node);
      node = nextNode;
    }
    return element;
  },

  empty: function(element) {
    return $(element).innerHTML.blank();
  },

  descendantOf: function(element, ancestor) {
    element = $(element), ancestor = $(ancestor);
    while (element = element.parentNode)
      if (element == ancestor) return true;
    return false;
  },

  scrollTo: function(element) {
    element = $(element);
    var pos = Position.cumulativeOffset(element);
    window.scrollTo(pos[0], pos[1]);
    return element;
  },

  getStyle: function(element, style) {
    element = $(element);
    style = style == 'float' ? 'cssFloat' : style.camelize();
    var value = element.style[style];
    if (!value) {
      var css = document.defaultView.getComputedStyle(element, null);
      value = css ? css[style] : null;
    }
    if (style == 'opacity') return value ? parseFloat(value) : 1.0;
    return value == 'auto' ? null : value;
  },

  getOpacity: function(element) {
    return $(element).getStyle('opacity');
  },

  setStyle: function(element, styles, camelized) {
    element = $(element);
    var elementStyle = element.style;

    for (var property in styles)
      if (property == 'opacity') element.setOpacity(styles[property])
      else
        elementStyle[(property == 'float' || property == 'cssFloat') ?
          (elementStyle.styleFloat === undefined ? 'cssFloat' : 'styleFloat') :
          (camelized ? property : property.camelize())] = styles[property];

    return element;
  },

  setOpacity: function(element, value) {
    element = $(element);
    element.style.opacity = (value == 1 || value === '') ? '' :
      (value < 0.00001) ? 0 : value;
    return element;
  },

  getDimensions: function(element) {
    element = $(element);
    var display = $(element).getStyle('display');
    if (display != 'none' && display != null) // Safari bug
      return {width: element.offsetWidth, height: element.offsetHeight};

    // All *Width and *Height properties give 0 on elements with display none,
    // so enable the element temporarily
    var els = element.style;
    var originalVisibility = els.visibility;
    var originalPosition = els.position;
    var originalDisplay = els.display;
    els.visibility = 'hidden';
    els.position = 'absolute';
    els.display = 'block';
    var originalWidth = element.clientWidth;
    var originalHeight = element.clientHeight;
    els.display = originalDisplay;
    els.position = originalPosition;
    els.visibility = originalVisibility;
    return {width: originalWidth, height: originalHeight};
  },

  makePositioned: function(element) {
    element = $(element);
    var pos = Element.getStyle(element, 'position');
    if (pos == 'static' || !pos) {
      element._madePositioned = true;
      element.style.position = 'relative';
      // Opera returns the offset relative to the positioning context, when an
      // element is position relative but top and left have not been defined
      if (window.opera) {
        element.style.top = 0;
        element.style.left = 0;
      }
    }
    return element;
  },

  undoPositioned: function(element) {
    element = $(element);
    if (element._madePositioned) {
      element._madePositioned = undefined;
      element.style.position =
        element.style.top =
        element.style.left =
        element.style.bottom =
        element.style.right = '';
    }
    return element;
  },

  makeClipping: function(element) {
    element = $(element);
    if (element._overflow) return element;
    element._overflow = element.style.overflow || 'auto';
    if ((Element.getStyle(element, 'overflow') || 'visible') != 'hidden')
      element.style.overflow = 'hidden';
    return element;
  },

  undoClipping: function(element) {
    element = $(element);
    if (!element._overflow) return element;
    element.style.overflow = element._overflow == 'auto' ? '' : element._overflow;
    element._overflow = null;
    return element;
  }
};

Object.extend(Element.Methods, {
  childOf: Element.Methods.descendantOf,
  childElements: Element.Methods.immediateDescendants
});

if (Prototype.Browser.Opera) {
  Element.Methods._getStyle = Element.Methods.getStyle;
  Element.Methods.getStyle = function(element, style) {
    switch(style) {
      case 'left':
      case 'top':
      case 'right':
      case 'bottom':
        if (Element._getStyle(element, 'position') == 'static') return null;
      default: return Element._getStyle(element, style);
    }
  };
}
else if (Prototype.Browser.IE) {
  Element.Methods.getStyle = function(element, style) {
    element = $(element);
    style = (style == 'float' || style == 'cssFloat') ? 'styleFloat' : style.camelize();
    var value = element.style[style];
    if (!value && element.currentStyle) value = element.currentStyle[style];

    if (style == 'opacity') {
      if (value = (element.getStyle('filter') || '').match(/alpha\(opacity=(.*)\)/))
        if (value[1]) return parseFloat(value[1]) / 100;
      return 1.0;
    }

    if (value == 'auto') {
      if ((style == 'width' || style == 'height') && (element.getStyle('display') != 'none'))
        return element['offset'+style.capitalize()] + 'px';
      return null;
    }
    return value;
  };

  Element.Methods.setOpacity = function(element, value) {
    element = $(element);
    var filter = element.getStyle('filter'), style = element.style;
    if (value == 1 || value === '') {
      style.filter = filter.replace(/alpha\([^\)]*\)/gi,'');
      return element;
    } else if (value < 0.00001) value = 0;
    style.filter = filter.replace(/alpha\([^\)]*\)/gi, '') +
      'alpha(opacity=' + (value * 100) + ')';
    return element;
  };

  // IE is missing .innerHTML support for TABLE-related elements
  Element.Methods.update = function(element, html) {
    element = $(element);
    html = typeof html == 'undefined' ? '' : html.toString();
    var tagName = element.tagName.toUpperCase();
    if (['THEAD','TBODY','TR','TD'].include(tagName)) {
      var div = document.createElement('div');
      switch (tagName) {
        case 'THEAD':
        case 'TBODY':
          div.innerHTML = '<table><tbody>' +  html.stripScripts() + '</tbody></table>';
          depth = 2;
          break;
        case 'TR':
          div.innerHTML = '<table><tbody><tr>' +  html.stripScripts() + '</tr></tbody></table>';
          depth = 3;
          break;
        case 'TD':
          div.innerHTML = '<table><tbody><tr><td>' +  html.stripScripts() + '</td></tr></tbody></table>';
          depth = 4;
      }
      $A(element.childNodes).each(function(node) { element.removeChild(node) });
      depth.times(function() { div = div.firstChild });
      $A(div.childNodes).each(function(node) { element.appendChild(node) });
    } else {
      element.innerHTML = html.stripScripts();
    }
    setTimeout(function() { html.evalScripts() }, 10);
    return element;
  }
}
else if (Prototype.Browser.Gecko) {
  Element.Methods.setOpacity = function(element, value) {
    element = $(element);
    element.style.opacity = (value == 1) ? 0.999999 :
      (value === '') ? '' : (value < 0.00001) ? 0 : value;
    return element;
  };
}

Element._attributeTranslations = {
  names: {
    colspan:   "colSpan",
    rowspan:   "rowSpan",
    valign:    "vAlign",
    datetime:  "dateTime",
    accesskey: "accessKey",
    tabindex:  "tabIndex",
    enctype:   "encType",
    maxlength: "maxLength",
    readonly:  "readOnly",
    longdesc:  "longDesc"
  },
  values: {
    _getAttr: function(element, attribute) {
      return element.getAttribute(attribute, 2);
    },
    _flag: function(element, attribute) {
      return $(element).hasAttribute(attribute) ? attribute : null;
    },
    style: function(element) {
      return element.style.cssText.toLowerCase();
    },
    title: function(element) {
      var node = element.getAttributeNode('title');
      return node.specified ? node.nodeValue : null;
    }
  }
};

(function() {
  Object.extend(this, {
    href: this._getAttr,
    src:  this._getAttr,
    type: this._getAttr,
    disabled: this._flag,
    checked:  this._flag,
    readonly: this._flag,
    multiple: this._flag
  });
}).call(Element._attributeTranslations.values);

Element.Methods.Simulated = {
  hasAttribute: function(element, attribute) {
    var t = Element._attributeTranslations, node;
    attribute = t.names[attribute] || attribute;
    node = $(element).getAttributeNode(attribute);
    return node && node.specified;
  }
};

Element.Methods.ByTag = {};

Object.extend(Element, Element.Methods);

if (!Prototype.BrowserFeatures.ElementExtensions &&
 document.createElement('div').__proto__) {
  window.HTMLElement = {};
  window.HTMLElement.prototype = document.createElement('div').__proto__;
  Prototype.BrowserFeatures.ElementExtensions = true;
}

Element.hasAttribute = function(element, attribute) {
  if (element.hasAttribute) return element.hasAttribute(attribute);
  return Element.Methods.Simulated.hasAttribute(element, attribute);
};

Element.addMethods = function(methods) {
  var F = Prototype.BrowserFeatures, T = Element.Methods.ByTag;

  if (!methods) {
    Object.extend(Form, Form.Methods);
    Object.extend(Form.Element, Form.Element.Methods);
    Object.extend(Element.Methods.ByTag, {
      "FORM":     Object.clone(Form.Methods),
      "INPUT":    Object.clone(Form.Element.Methods),
      "SELECT":   Object.clone(Form.Element.Methods),
      "TEXTAREA": Object.clone(Form.Element.Methods)
    });
  }

  if (arguments.length == 2) {
    var tagName = methods;
    methods = arguments[1];
  }

  if (!tagName) Object.extend(Element.Methods, methods || {});
  else {
    if (tagName.constructor == Array) tagName.each(extend);
    else extend(tagName);
  }

  function extend(tagName) {
    tagName = tagName.toUpperCase();
    if (!Element.Methods.ByTag[tagName])
      Element.Methods.ByTag[tagName] = {};
    Object.extend(Element.Methods.ByTag[tagName], methods);
  }

  function copy(methods, destination, onlyIfAbsent) {
    onlyIfAbsent = onlyIfAbsent || false;
    var cache = Element.extend.cache;
    for (var property in methods) {
      var value = methods[property];
      if (!onlyIfAbsent || !(property in destination))
        destination[property] = cache.findOrStore(value);
    }
  }

  function findDOMClass(tagName) {
    var klass;
    var trans = {
      "OPTGROUP": "OptGroup", "TEXTAREA": "TextArea", "P": "Paragraph",
      "FIELDSET": "FieldSet", "UL": "UList", "OL": "OList", "DL": "DList",
      "DIR": "Directory", "H1": "Heading", "H2": "Heading", "H3": "Heading",
      "H4": "Heading", "H5": "Heading", "H6": "Heading", "Q": "Quote",
      "INS": "Mod", "DEL": "Mod", "A": "Anchor", "IMG": "Image", "CAPTION":
      "TableCaption", "COL": "TableCol", "COLGROUP": "TableCol", "THEAD":
      "TableSection", "TFOOT": "TableSection", "TBODY": "TableSection", "TR":
      "TableRow", "TH": "TableCell", "TD": "TableCell", "FRAMESET":
      "FrameSet", "IFRAME": "IFrame"
    };
    if (trans[tagName]) klass = 'HTML' + trans[tagName] + 'Element';
    if (window[klass]) return window[klass];
    klass = 'HTML' + tagName + 'Element';
    if (window[klass]) return window[klass];
    klass = 'HTML' + tagName.capitalize() + 'Element';
    if (window[klass]) return window[klass];

    window[klass] = {};
    window[klass].prototype = document.createElement(tagName).__proto__;
    return window[klass];
  }

  if (F.ElementExtensions) {
    copy(Element.Methods, HTMLElement.prototype);
    copy(Element.Methods.Simulated, HTMLElement.prototype, true);
  }

  if (F.SpecificElementExtensions) {
    for (var tag in Element.Methods.ByTag) {
      var klass = findDOMClass(tag);
      if (typeof klass == "undefined") continue;
      copy(T[tag], klass.prototype);
    }
  }

  Object.extend(Element, Element.Methods);
  delete Element.ByTag;
};

var Toggle = { display: Element.toggle };

/*--------------------------------------------------------------------------*/

Abstract.Insertion = function(adjacency) {
  this.adjacency = adjacency;
}

Abstract.Insertion.prototype = {
  initialize: function(element, content) {
    this.element = $(element);
    this.content = content.stripScripts();

    if (this.adjacency && this.element.insertAdjacentHTML) {
      try {
        this.element.insertAdjacentHTML(this.adjacency, this.content);
      } catch (e) {
        var tagName = this.element.tagName.toUpperCase();
        if (['TBODY', 'TR'].include(tagName)) {
          this.insertContent(this.contentFromAnonymousTable());
        } else {
          throw e;
        }
      }
    } else {
      this.range = this.element.ownerDocument.createRange();
      if (this.initializeRange) this.initializeRange();
      this.insertContent([this.range.createContextualFragment(this.content)]);
    }

    setTimeout(function() {content.evalScripts()}, 10);
  },

  contentFromAnonymousTable: function() {
    var div = document.createElement('div');
    div.innerHTML = '<table><tbody>' + this.content + '</tbody></table>';
    return $A(div.childNodes[0].childNodes[0].childNodes);
  }
}

var Insertion = new Object();

Insertion.Before = Class.create();
Insertion.Before.prototype = Object.extend(new Abstract.Insertion('beforeBegin'), {
  initializeRange: function() {
    this.range.setStartBefore(this.element);
  },

  insertContent: function(fragments) {
    fragments.each((function(fragment) {
      this.element.parentNode.insertBefore(fragment, this.element);
    }).bind(this));
  }
});

Insertion.Top = Class.create();
Insertion.Top.prototype = Object.extend(new Abstract.Insertion('afterBegin'), {
  initializeRange: function() {
    this.range.selectNodeContents(this.element);
    this.range.collapse(true);
  },

  insertContent: function(fragments) {
    fragments.reverse(false).each((function(fragment) {
      this.element.insertBefore(fragment, this.element.firstChild);
    }).bind(this));
  }
});

Insertion.Bottom = Class.create();
Insertion.Bottom.prototype = Object.extend(new Abstract.Insertion('beforeEnd'), {
  initializeRange: function() {
    this.range.selectNodeContents(this.element);
    this.range.collapse(this.element);
  },

  insertContent: function(fragments) {
    fragments.each((function(fragment) {
      this.element.appendChild(fragment);
    }).bind(this));
  }
});

Insertion.After = Class.create();
Insertion.After.prototype = Object.extend(new Abstract.Insertion('afterEnd'), {
  initializeRange: function() {
    this.range.setStartAfter(this.element);
  },

  insertContent: function(fragments) {
    fragments.each((function(fragment) {
      this.element.parentNode.insertBefore(fragment,
        this.element.nextSibling);
    }).bind(this));
  }
});

/*--------------------------------------------------------------------------*/

Element.ClassNames = Class.create();
Element.ClassNames.prototype = {
  initialize: function(element) {
    this.element = $(element);
  },

  _each: function(iterator) {
    this.element.className.split(/\s+/).select(function(name) {
      return name.length > 0;
    })._each(iterator);
  },

  set: function(className) {
    this.element.className = className;
  },

  add: function(classNameToAdd) {
    if (this.include(classNameToAdd)) return;
    this.set($A(this).concat(classNameToAdd).join(' '));
  },

  remove: function(classNameToRemove) {
    if (!this.include(classNameToRemove)) return;
    this.set($A(this).without(classNameToRemove).join(' '));
  },

  toString: function() {
    return $A(this).join(' ');
  }
};

Object.extend(Element.ClassNames.prototype, Enumerable);
/* Portions of the Selector class are derived from Jack Slocum’s DomQuery,
 * part of YUI-Ext version 0.40, distributed under the terms of an MIT-style
 * license.  Please see http://www.yui-ext.com/ for more information. */

var Selector = Class.create();

Selector.prototype = {
  initialize: function(expression) {
    this.expression = expression.strip();
    this.compileMatcher();
  },

  compileMatcher: function() {
    // Selectors with namespaced attributes can't use the XPath version
    if (Prototype.BrowserFeatures.XPath && !(/\[[\w-]*?:/).test(this.expression))
      return this.compileXPathMatcher();

    var e = this.expression, ps = Selector.patterns, h = Selector.handlers,
        c = Selector.criteria, le, p, m;

    if (Selector._cache[e]) {
      this.matcher = Selector._cache[e]; return;
    }
    this.matcher = ["this.matcher = function(root) {",
                    "var r = root, h = Selector.handlers, c = false, n;"];

    while (e && le != e && (/\S/).test(e)) {
      le = e;
      for (var i in ps) {
        p = ps[i];
        if (m = e.match(p)) {
          this.matcher.push(typeof c[i] == 'function' ? c[i](m) :
    	      new Template(c[i]).evaluate(m));
          e = e.replace(m[0], '');
          break;
        }
      }
    }

    this.matcher.push("return h.unique(n);\n}");
    eval(this.matcher.join('\n'));
    Selector._cache[this.expression] = this.matcher;
  },

  compileXPathMatcher: function() {
    var e = this.expression, ps = Selector.patterns,
        x = Selector.xpath, le,  m;

    if (Selector._cache[e]) {
      this.xpath = Selector._cache[e]; return;
    }

    this.matcher = ['.//*'];
    while (e && le != e && (/\S/).test(e)) {
      le = e;
      for (var i in ps) {
        if (m = e.match(ps[i])) {
          this.matcher.push(typeof x[i] == 'function' ? x[i](m) :
            new Template(x[i]).evaluate(m));
          e = e.replace(m[0], '');
          break;
        }
      }
    }

    this.xpath = this.matcher.join('');
    Selector._cache[this.expression] = this.xpath;
  },

  findElements: function(root) {
    root = root || document;
    if (this.xpath) return document._getElementsByXPath(this.xpath, root);
    return this.matcher(root);
  },

  match: function(element) {
    return this.findElements(document).include(element);
  },

  toString: function() {
    return this.expression;
  },

  inspect: function() {
    return "#<Selector:" + this.expression.inspect() + ">";
  }
};

Object.extend(Selector, {
  _cache: {},

  xpath: {
    descendant:   "//*",
    child:        "/*",
    adjacent:     "/following-sibling::*[1]",
    laterSibling: '/following-sibling::*',
    tagName:      function(m) {
      if (m[1] == '*') return '';
      return "[local-name()='" + m[1].toLowerCase() +
             "' or local-name()='" + m[1].toUpperCase() + "']";
    },
    className:    "[contains(concat(' ', @class, ' '), ' #{1} ')]",
    id:           "[@id='#{1}']",
    attrPresence: "[@#{1}]",
    attr: function(m) {
      m[3] = m[5] || m[6];
      return new Template(Selector.xpath.operators[m[2]]).evaluate(m);
    },
    pseudo: function(m) {
      var h = Selector.xpath.pseudos[m[1]];
      if (!h) return '';
      if (typeof h === 'function') return h(m);
      return new Template(Selector.xpath.pseudos[m[1]]).evaluate(m);
    },
    operators: {
      '=':  "[@#{1}='#{3}']",
      '!=': "[@#{1}!='#{3}']",
      '^=': "[starts-with(@#{1}, '#{3}')]",
      '$=': "[substring(@#{1}, (string-length(@#{1}) - string-length('#{3}') + 1))='#{3}']",
      '*=': "[contains(@#{1}, '#{3}')]",
      '~=': "[contains(concat(' ', @#{1}, ' '), ' #{3} ')]",
      '|=': "[contains(concat('-', @#{1}, '-'), '-#{3}-')]"
    },
    pseudos: {
      'first-child': '[not(preceding-sibling::*)]',
      'last-child':  '[not(following-sibling::*)]',
      'only-child':  '[not(preceding-sibling::* or following-sibling::*)]',
      'empty':       "[count(*) = 0 and (count(text()) = 0 or translate(text(), ' \t\r\n', '') = '')]",
      'checked':     "[@checked]",
      'disabled':    "[@disabled]",
      'enabled':     "[not(@disabled)]",
      'not': function(m) {
        var e = m[6], p = Selector.patterns,
            x = Selector.xpath, le, m, v;

        var exclusion = [];
        while (e && le != e && (/\S/).test(e)) {
          le = e;
          for (var i in p) {
            if (m = e.match(p[i])) {
              v = typeof x[i] == 'function' ? x[i](m) : new Template(x[i]).evaluate(m);
              exclusion.push("(" + v.substring(1, v.length - 1) + ")");
              e = e.replace(m[0], '');
              break;
            }
          }
        }
        return "[not(" + exclusion.join(" and ") + ")]";
      },
      'nth-child':      function(m) {
        return Selector.xpath.pseudos.nth("(count(./preceding-sibling::*) + 1) ", m);
      },
      'nth-last-child': function(m) {
        return Selector.xpath.pseudos.nth("(count(./following-sibling::*) + 1) ", m);
      },
      'nth-of-type':    function(m) {
        return Selector.xpath.pseudos.nth("position() ", m);
      },
      'nth-last-of-type': function(m) {
        return Selector.xpath.pseudos.nth("(last() + 1 - position()) ", m);
      },
      'first-of-type':  function(m) {
        m[6] = "1"; return Selector.xpath.pseudos['nth-of-type'](m);
      },
      'last-of-type':   function(m) {
        m[6] = "1"; return Selector.xpath.pseudos['nth-last-of-type'](m);
      },
      'only-of-type':   function(m) {
        var p = Selector.xpath.pseudos; return p['first-of-type'](m) + p['last-of-type'](m);
      },
      nth: function(fragment, m) {
        var mm, formula = m[6], predicate;
        if (formula == 'even') formula = '2n+0';
        if (formula == 'odd')  formula = '2n+1';
        if (mm = formula.match(/^(\d+)$/)) // digit only
          return '[' + fragment + "= " + mm[1] + ']';
        if (mm = formula.match(/^(-?\d*)?n(([+-])(\d+))?/)) { // an+b
          if (mm[1] == "-") mm[1] = -1;
          var a = mm[1] ? Number(mm[1]) : 1;
          var b = mm[2] ? Number(mm[2]) : 0;
          predicate = "[((#{fragment} - #{b}) mod #{a} = 0) and " +
          "((#{fragment} - #{b}) div #{a} >= 0)]";
          return new Template(predicate).evaluate({
            fragment: fragment, a: a, b: b });
        }
      }
    }
  },

  criteria: {
    tagName:      'n = h.tagName(n, r, "#{1}", c);   c = false;',
    className:    'n = h.className(n, r, "#{1}", c); c = false;',
    id:           'n = h.id(n, r, "#{1}", c);        c = false;',
    attrPresence: 'n = h.attrPresence(n, r, "#{1}"); c = false;',
    attr: function(m) {
      m[3] = (m[5] || m[6]);
      return new Template('n = h.attr(n, r, "#{1}", "#{3}", "#{2}"); c = false;').evaluate(m);
    },
    pseudo:       function(m) {
      if (m[6]) m[6] = m[6].replace(/"/g, '\\"');
      return new Template('n = h.pseudo(n, "#{1}", "#{6}", r, c); c = false;').evaluate(m);
    },
    descendant:   'c = "descendant";',
    child:        'c = "child";',
    adjacent:     'c = "adjacent";',
    laterSibling: 'c = "laterSibling";'
  },

  patterns: {
    // combinators must be listed first
    // (and descendant needs to be last combinator)
    laterSibling: /^\s*~\s*/,
    child:        /^\s*>\s*/,
    adjacent:     /^\s*\+\s*/,
    descendant:   /^\s/,

    // selectors follow
    tagName:      /^\s*(\*|[\w\-]+)(\b|$)?/,
    id:           /^#([\w\-\*]+)(\b|$)/,
    className:    /^\.([\w\-\*]+)(\b|$)/,
    pseudo:       /^:((first|last|nth|nth-last|only)(-child|-of-type)|empty|checked|(en|dis)abled|not)(\((.*?)\))?(\b|$|\s|(?=:))/,
    attrPresence: /^\[([\w]+)\]/,
    attr:         /\[((?:[\w-]*:)?[\w-]+)\s*(?:([!^$*~|]?=)\s*((['"])([^\]]*?)\4|([^'"][^\]]*?)))?\]/
  },

  handlers: {
    // UTILITY FUNCTIONS
    // joins two collections
    concat: function(a, b) {
      for (var i = 0, node; node = b[i]; i++)
        a.push(node);
      return a;
    },

    // marks an array of nodes for counting
    mark: function(nodes) {
      for (var i = 0, node; node = nodes[i]; i++)
        node._counted = true;
      return nodes;
    },

    unmark: function(nodes) {
      for (var i = 0, node; node = nodes[i]; i++)
        node._counted = undefined;
      return nodes;
    },

    // mark each child node with its position (for nth calls)
    // "ofType" flag indicates whether we're indexing for nth-of-type
    // rather than nth-child
    index: function(parentNode, reverse, ofType) {
      parentNode._counted = true;
      if (reverse) {
        for (var nodes = parentNode.childNodes, i = nodes.length - 1, j = 1; i >= 0; i--) {
          node = nodes[i];
          if (node.nodeType == 1 && (!ofType || node._counted)) node.nodeIndex = j++;
        }
      } else {
        for (var i = 0, j = 1, nodes = parentNode.childNodes; node = nodes[i]; i++)
          if (node.nodeType == 1 && (!ofType || node._counted)) node.nodeIndex = j++;
      }
    },

    // filters out duplicates and extends all nodes
    unique: function(nodes) {
      if (nodes.length == 0) return nodes;
      var results = [], n;
      for (var i = 0, l = nodes.length; i < l; i++)
        if (!(n = nodes[i])._counted) {
          n._counted = true;
          results.push(Element.extend(n));
        }
      return Selector.handlers.unmark(results);
    },

    // COMBINATOR FUNCTIONS
    descendant: function(nodes) {
      var h = Selector.handlers;
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        h.concat(results, node.getElementsByTagName('*'));
      return results;
    },

    child: function(nodes) {
      var h = Selector.handlers;
      for (var i = 0, results = [], node; node = nodes[i]; i++) {
        for (var j = 0, children = [], child; child = node.childNodes[j]; j++)
          if (child.nodeType == 1 && child.tagName != '!') results.push(child);
      }
      return results;
    },

    adjacent: function(nodes) {
      for (var i = 0, results = [], node; node = nodes[i]; i++) {
        var next = this.nextElementSibling(node);
        if (next) results.push(next);
      }
      return results;
    },

    laterSibling: function(nodes) {
      var h = Selector.handlers;
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        h.concat(results, Element.nextSiblings(node));
      return results;
    },

    nextElementSibling: function(node) {
      while (node = node.nextSibling)
	      if (node.nodeType == 1) return node;
      return null;
    },

    previousElementSibling: function(node) {
      while (node = node.previousSibling)
        if (node.nodeType == 1) return node;
      return null;
    },

    // TOKEN FUNCTIONS
    tagName: function(nodes, root, tagName, combinator) {
      tagName = tagName.toUpperCase();
      var results = [], h = Selector.handlers;
      if (nodes) {
        if (combinator) {
          // fastlane for ordinary descendant combinators
          if (combinator == "descendant") {
            for (var i = 0, node; node = nodes[i]; i++)
              h.concat(results, node.getElementsByTagName(tagName));
            return results;
          } else nodes = this[combinator](nodes);
          if (tagName == "*") return nodes;
        }
        for (var i = 0, node; node = nodes[i]; i++)
          if (node.tagName.toUpperCase() == tagName) results.push(node);
        return results;
      } else return root.getElementsByTagName(tagName);
    },

    id: function(nodes, root, id, combinator) {
      var targetNode = $(id), h = Selector.handlers;
      if (!nodes && root == document) return targetNode ? [targetNode] : [];
      if (nodes) {
        if (combinator) {
          if (combinator == 'child') {
            for (var i = 0, node; node = nodes[i]; i++)
              if (targetNode.parentNode == node) return [targetNode];
          } else if (combinator == 'descendant') {
            for (var i = 0, node; node = nodes[i]; i++)
              if (Element.descendantOf(targetNode, node)) return [targetNode];
          } else if (combinator == 'adjacent') {
            for (var i = 0, node; node = nodes[i]; i++)
              if (Selector.handlers.previousElementSibling(targetNode) == node)
                return [targetNode];
          } else nodes = h[combinator](nodes);
        }
        for (var i = 0, node; node = nodes[i]; i++)
          if (node == targetNode) return [targetNode];
        return [];
      }
      return (targetNode && Element.descendantOf(targetNode, root)) ? [targetNode] : [];
    },

    className: function(nodes, root, className, combinator) {
      if (nodes && combinator) nodes = this[combinator](nodes);
      return Selector.handlers.byClassName(nodes, root, className);
    },

    byClassName: function(nodes, root, className) {
      if (!nodes) nodes = Selector.handlers.descendant([root]);
      var needle = ' ' + className + ' ';
      for (var i = 0, results = [], node, nodeClassName; node = nodes[i]; i++) {
        nodeClassName = node.className;
        if (nodeClassName.length == 0) continue;
        if (nodeClassName == className || (' ' + nodeClassName + ' ').include(needle))
          results.push(node);
      }
      return results;
    },

    attrPresence: function(nodes, root, attr) {
      var results = [];
      for (var i = 0, node; node = nodes[i]; i++)
        if (Element.hasAttribute(node, attr)) results.push(node);
      return results;
    },

    attr: function(nodes, root, attr, value, operator) {
      if (!nodes) nodes = root.getElementsByTagName("*");
      var handler = Selector.operators[operator], results = [];
      for (var i = 0, node; node = nodes[i]; i++) {
        var nodeValue = Element.readAttribute(node, attr);
        if (nodeValue === null) continue;
        if (handler(nodeValue, value)) results.push(node);
      }
      return results;
    },

    pseudo: function(nodes, name, value, root, combinator) {
      if (nodes && combinator) nodes = this[combinator](nodes);
      if (!nodes) nodes = root.getElementsByTagName("*");
      return Selector.pseudos[name](nodes, value, root);
    }
  },

  pseudos: {
    'first-child': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++) {
        if (Selector.handlers.previousElementSibling(node)) continue;
          results.push(node);
      }
      return results;
    },
    'last-child': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++) {
        if (Selector.handlers.nextElementSibling(node)) continue;
          results.push(node);
      }
      return results;
    },
    'only-child': function(nodes, value, root) {
      var h = Selector.handlers;
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        if (!h.previousElementSibling(node) && !h.nextElementSibling(node))
          results.push(node);
      return results;
    },
    'nth-child':        function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, formula, root);
    },
    'nth-last-child':   function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, formula, root, true);
    },
    'nth-of-type':      function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, formula, root, false, true);
    },
    'nth-last-of-type': function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, formula, root, true, true);
    },
    'first-of-type':    function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, "1", root, false, true);
    },
    'last-of-type':     function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, "1", root, true, true);
    },
    'only-of-type':     function(nodes, formula, root) {
      var p = Selector.pseudos;
      return p['last-of-type'](p['first-of-type'](nodes, formula, root), formula, root);
    },

    // handles the an+b logic
    getIndices: function(a, b, total) {
      if (a == 0) return b > 0 ? [b] : [];
      return $R(1, total).inject([], function(memo, i) {
        if (0 == (i - b) % a && (i - b) / a >= 0) memo.push(i);
        return memo;
      });
    },

    // handles nth(-last)-child, nth(-last)-of-type, and (first|last)-of-type
    nth: function(nodes, formula, root, reverse, ofType) {
      if (nodes.length == 0) return [];
      if (formula == 'even') formula = '2n+0';
      if (formula == 'odd')  formula = '2n+1';
      var h = Selector.handlers, results = [], indexed = [], m;
      h.mark(nodes);
      for (var i = 0, node; node = nodes[i]; i++) {
        if (!node.parentNode._counted) {
          h.index(node.parentNode, reverse, ofType);
          indexed.push(node.parentNode);
        }
      }
      if (formula.match(/^\d+$/)) { // just a number
        formula = Number(formula);
        for (var i = 0, node; node = nodes[i]; i++)
          if (node.nodeIndex == formula) results.push(node);
      } else if (m = formula.match(/^(-?\d*)?n(([+-])(\d+))?/)) { // an+b
        if (m[1] == "-") m[1] = -1;
        var a = m[1] ? Number(m[1]) : 1;
        var b = m[2] ? Number(m[2]) : 0;
        var indices = Selector.pseudos.getIndices(a, b, nodes.length);
        for (var i = 0, node, l = indices.length; node = nodes[i]; i++) {
          for (var j = 0; j < l; j++)
            if (node.nodeIndex == indices[j]) results.push(node);
        }
      }
      h.unmark(nodes);
      h.unmark(indexed);
      return results;
    },

    'empty': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++) {
        // IE treats comments as element nodes
        if (node.tagName == '!' || (node.firstChild && !node.innerHTML.match(/^\s*$/))) continue;
        results.push(node);
      }
      return results;
    },

    'not': function(nodes, selector, root) {
      var h = Selector.handlers, selectorType, m;
      var exclusions = new Selector(selector).findElements(root);
      h.mark(exclusions);
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        if (!node._counted) results.push(node);
      h.unmark(exclusions);
      return results;
    },

    'enabled': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        if (!node.disabled) results.push(node);
      return results;
    },

    'disabled': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        if (node.disabled) results.push(node);
      return results;
    },

    'checked': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        if (node.checked) results.push(node);
      return results;
    }
  },

  operators: {
    '=':  function(nv, v) { return nv == v; },
    '!=': function(nv, v) { return nv != v; },
    '^=': function(nv, v) { return nv.startsWith(v); },
    '$=': function(nv, v) { return nv.endsWith(v); },
    '*=': function(nv, v) { return nv.include(v); },
    '~=': function(nv, v) { return (' ' + nv + ' ').include(' ' + v + ' '); },
    '|=': function(nv, v) { return ('-' + nv.toUpperCase() + '-').include('-' + v.toUpperCase() + '-'); }
  },

  matchElements: function(elements, expression) {
    var matches = new Selector(expression).findElements(), h = Selector.handlers;
    h.mark(matches);
    for (var i = 0, results = [], element; element = elements[i]; i++)
      if (element._counted) results.push(element);
    h.unmark(matches);
    return results;
  },

  findElement: function(elements, expression, index) {
    if (typeof expression == 'number') {
      index = expression; expression = false;
    }
    return Selector.matchElements(elements, expression || '*')[index || 0];
  },

  findChildElements: function(element, expressions) {
    var exprs = expressions.join(','), expressions = [];
    exprs.scan(/(([\w#:.~>+()\s-]+|\*|\[.*?\])+)\s*(,|$)/, function(m) {
      expressions.push(m[1].strip());
    });
    var results = [], h = Selector.handlers;
    for (var i = 0, l = expressions.length, selector; i < l; i++) {
      selector = new Selector(expressions[i].strip());
      h.concat(results, selector.findElements(element));
    }
    return (l > 1) ? h.unique(results) : results;
  }
});

function $$() {
  return Selector.findChildElements(document, $A(arguments));
}
var Form = {
  reset: function(form) {
    $(form).reset();
    return form;
  },

  serializeElements: function(elements, getHash) {
    var data = elements.inject({}, function(result, element) {
      if (!element.disabled && element.name) {
        var key = element.name, value = $(element).getValue();
        if (value != null) {
         	if (key in result) {
            if (result[key].constructor != Array) result[key] = [result[key]];
            result[key].push(value);
          }
          else result[key] = value;
        }
      }
      return result;
    });

    return getHash ? data : Hash.toQueryString(data);
  }
};

Form.Methods = {
  serialize: function(form, getHash) {
    return Form.serializeElements(Form.getElements(form), getHash);
  },

  getElements: function(form) {
    return $A($(form).getElementsByTagName('*')).inject([],
      function(elements, child) {
        if (Form.Element.Serializers[child.tagName.toLowerCase()])
          elements.push(Element.extend(child));
        return elements;
      }
    );
  },

  getInputs: function(form, typeName, name) {
    form = $(form);
    var inputs = form.getElementsByTagName('input');

    if (!typeName && !name) return $A(inputs).map(Element.extend);

    for (var i = 0, matchingInputs = [], length = inputs.length; i < length; i++) {
      var input = inputs[i];
      if ((typeName && input.type != typeName) || (name && input.name != name))
        continue;
      matchingInputs.push(Element.extend(input));
    }

    return matchingInputs;
  },

  disable: function(form) {
    form = $(form);
    Form.getElements(form).invoke('disable');
    return form;
  },

  enable: function(form) {
    form = $(form);
    Form.getElements(form).invoke('enable');
    return form;
  },

  findFirstElement: function(form) {
    return $(form).getElements().find(function(element) {
      return element.type != 'hidden' && !element.disabled &&
        ['input', 'select', 'textarea'].include(element.tagName.toLowerCase());
    });
  },

  focusFirstElement: function(form) {
    form = $(form);
    form.findFirstElement().activate();
    return form;
  },

  request: function(form, options) {
    form = $(form), options = Object.clone(options || {});

    var params = options.parameters;
    options.parameters = form.serialize(true);

    if (params) {
      if (typeof params == 'string') params = params.toQueryParams();
      Object.extend(options.parameters, params);
    }

    if (form.hasAttribute('method') && !options.method)
      options.method = form.method;

    return new Ajax.Request(form.readAttribute('action'), options);
  }
}

/*--------------------------------------------------------------------------*/

Form.Element = {
  focus: function(element) {
    $(element).focus();
    return element;
  },

  select: function(element) {
    $(element).select();
    return element;
  }
}

Form.Element.Methods = {
  serialize: function(element) {
    element = $(element);
    if (!element.disabled && element.name) {
      var value = element.getValue();
      if (value != undefined) {
        var pair = {};
        pair[element.name] = value;
        return Hash.toQueryString(pair);
      }
    }
    return '';
  },

  getValue: function(element) {
    element = $(element);
    var method = element.tagName.toLowerCase();
    return Form.Element.Serializers[method](element);
  },

  clear: function(element) {
    $(element).value = '';
    return element;
  },

  present: function(element) {
    return $(element).value != '';
  },

  activate: function(element) {
    element = $(element);
    try {
      element.focus();
      if (element.select && (element.tagName.toLowerCase() != 'input' ||
        !['button', 'reset', 'submit'].include(element.type)))
        element.select();
    } catch (e) {}
    return element;
  },

  disable: function(element) {
    element = $(element);
    element.blur();
    element.disabled = true;
    return element;
  },

  enable: function(element) {
    element = $(element);
    element.disabled = false;
    return element;
  }
}

/*--------------------------------------------------------------------------*/

var Field = Form.Element;
var $F = Form.Element.Methods.getValue;

/*--------------------------------------------------------------------------*/

Form.Element.Serializers = {
  input: function(element) {
    switch (element.type.toLowerCase()) {
      case 'checkbox':
      case 'radio':
        return Form.Element.Serializers.inputSelector(element);
      default:
        return Form.Element.Serializers.textarea(element);
    }
  },

  inputSelector: function(element) {
    return element.checked ? element.value : null;
  },

  textarea: function(element) {
    return element.value;
  },

  select: function(element) {
    return this[element.type == 'select-one' ?
      'selectOne' : 'selectMany'](element);
  },

  selectOne: function(element) {
    var index = element.selectedIndex;
    return index >= 0 ? this.optionValue(element.options[index]) : null;
  },

  selectMany: function(element) {
    var values, length = element.length;
    if (!length) return null;

    for (var i = 0, values = []; i < length; i++) {
      var opt = element.options[i];
      if (opt.selected) values.push(this.optionValue(opt));
    }
    return values;
  },

  optionValue: function(opt) {
    // extend element because hasAttribute may not be native
    return Element.extend(opt).hasAttribute('value') ? opt.value : opt.text;
  }
}

/*--------------------------------------------------------------------------*/

Abstract.TimedObserver = function() {}
Abstract.TimedObserver.prototype = {
  initialize: function(element, frequency, callback) {
    this.frequency = frequency;
    this.element   = $(element);
    this.callback  = callback;

    this.lastValue = this.getValue();
    this.registerCallback();
  },

  registerCallback: function() {
    setInterval(this.onTimerEvent.bind(this), this.frequency * 1000);
  },

  onTimerEvent: function() {
    var value = this.getValue();
    var changed = ('string' == typeof this.lastValue && 'string' == typeof value
      ? this.lastValue != value : String(this.lastValue) != String(value));
    if (changed) {
      this.callback(this.element, value);
      this.lastValue = value;
    }
  }
}

Form.Element.Observer = Class.create();
Form.Element.Observer.prototype = Object.extend(new Abstract.TimedObserver(), {
  getValue: function() {
    return Form.Element.getValue(this.element);
  }
});

Form.Observer = Class.create();
Form.Observer.prototype = Object.extend(new Abstract.TimedObserver(), {
  getValue: function() {
    return Form.serialize(this.element);
  }
});

/*--------------------------------------------------------------------------*/

Abstract.EventObserver = function() {}
Abstract.EventObserver.prototype = {
  initialize: function(element, callback) {
    this.element  = $(element);
    this.callback = callback;

    this.lastValue = this.getValue();
    if (this.element.tagName.toLowerCase() == 'form')
      this.registerFormCallbacks();
    else
      this.registerCallback(this.element);
  },

  onElementEvent: function() {
    var value = this.getValue();
    if (this.lastValue != value) {
      this.callback(this.element, value);
      this.lastValue = value;
    }
  },

  registerFormCallbacks: function() {
    Form.getElements(this.element).each(this.registerCallback.bind(this));
  },

  registerCallback: function(element) {
    if (element.type) {
      switch (element.type.toLowerCase()) {
        case 'checkbox':
        case 'radio':
          Event.observe(element, 'click', this.onElementEvent.bind(this));
          break;
        default:
          Event.observe(element, 'change', this.onElementEvent.bind(this));
          break;
      }
    }
  }
}

Form.Element.EventObserver = Class.create();
Form.Element.EventObserver.prototype = Object.extend(new Abstract.EventObserver(), {
  getValue: function() {
    return Form.Element.getValue(this.element);
  }
});

Form.EventObserver = Class.create();
Form.EventObserver.prototype = Object.extend(new Abstract.EventObserver(), {
  getValue: function() {
    return Form.serialize(this.element);
  }
});
if (!window.Event) {
  var Event = new Object();
}

Object.extend(Event, {
  KEY_BACKSPACE: 8,
  KEY_TAB:       9,
  KEY_RETURN:   13,
  KEY_ESC:      27,
  KEY_LEFT:     37,
  KEY_UP:       38,
  KEY_RIGHT:    39,
  KEY_DOWN:     40,
  KEY_DELETE:   46,
  KEY_HOME:     36,
  KEY_END:      35,
  KEY_PAGEUP:   33,
  KEY_PAGEDOWN: 34,

  element: function(event) {
    return $(event.target || event.srcElement);
  },

  isLeftClick: function(event) {
    return (((event.which) && (event.which == 1)) ||
            ((event.button) && (event.button == 1)));
  },

  pointerX: function(event) {
    return event.pageX || (event.clientX +
      (document.documentElement.scrollLeft || document.body.scrollLeft));
  },

  pointerY: function(event) {
    return event.pageY || (event.clientY +
      (document.documentElement.scrollTop || document.body.scrollTop));
  },

  stop: function(event) {
    if (event.preventDefault) {
      event.preventDefault();
      event.stopPropagation();
    } else {
      event.returnValue = false;
      event.cancelBubble = true;
    }
  },

  // find the first node with the given tagName, starting from the
  // node the event was triggered on; traverses the DOM upwards
  findElement: function(event, tagName) {
    var element = Event.element(event);
    while (element.parentNode && (!element.tagName ||
        (element.tagName.toUpperCase() != tagName.toUpperCase())))
      element = element.parentNode;
    return element;
  },

  observers: false,

  _observeAndCache: function(element, name, observer, useCapture) {
    if (!this.observers) this.observers = [];
    if (element.addEventListener) {
      this.observers.push([element, name, observer, useCapture]);
      element.addEventListener(name, observer, useCapture);
    } else if (element.attachEvent) {
      this.observers.push([element, name, observer, useCapture]);
      element.attachEvent('on' + name, observer);
    }
  },

  unloadCache: function() {
    if (!Event.observers) return;
    for (var i = 0, length = Event.observers.length; i < length; i++) {
      Event.stopObserving.apply(this, Event.observers[i]);
      Event.observers[i][0] = null;
    }
    Event.observers = false;
  },

  observe: function(element, name, observer, useCapture) {
    element = $(element);
    useCapture = useCapture || false;

    if (name == 'keypress' &&
      (Prototype.Browser.WebKit || element.attachEvent))
      name = 'keydown';

    Event._observeAndCache(element, name, observer, useCapture);
  },

  stopObserving: function(element, name, observer, useCapture) {
    element = $(element);
    useCapture = useCapture || false;

    if (name == 'keypress' &&
        (Prototype.Browser.WebKit || element.attachEvent))
      name = 'keydown';

    if (element.removeEventListener) {
      element.removeEventListener(name, observer, useCapture);
    } else if (element.detachEvent) {
      try {
        element.detachEvent('on' + name, observer);
      } catch (e) {}
    }
  }
});

/* prevent memory leaks in IE */
if (Prototype.Browser.IE)
  Event.observe(window, 'unload', Event.unloadCache, false);
var Position = {
  // set to true if needed, warning: firefox performance problems
  // NOT neeeded for page scrolling, only if draggable contained in
  // scrollable elements
  includeScrollOffsets: false,

  // must be called before calling withinIncludingScrolloffset, every time the
  // page is scrolled
  prepare: function() {
    this.deltaX =  window.pageXOffset
                || document.documentElement.scrollLeft
                || document.body.scrollLeft
                || 0;
    this.deltaY =  window.pageYOffset
                || document.documentElement.scrollTop
                || document.body.scrollTop
                || 0;
  },

  realOffset: function(element) {
    var valueT = 0, valueL = 0;
    do {
      valueT += element.scrollTop  || 0;
      valueL += element.scrollLeft || 0;
      element = element.parentNode;
    } while (element);
    return [valueL, valueT];
  },

  cumulativeOffset: function(element) {
    var valueT = 0, valueL = 0;
    do {
      valueT += element.offsetTop  || 0;
      valueL += element.offsetLeft || 0;
      element = element.offsetParent;
    } while (element);
    return [valueL, valueT];
  },

  positionedOffset: function(element) {
    var valueT = 0, valueL = 0;
    do {
      valueT += element.offsetTop  || 0;
      valueL += element.offsetLeft || 0;
      element = element.offsetParent;
      if (element) {
        if(element.tagName=='BODY') break;
        var p = Element.getStyle(element, 'position');
        if (p == 'relative' || p == 'absolute') break;
      }
    } while (element);
    return [valueL, valueT];
  },

  offsetParent: function(element) {
    if (element.offsetParent) return element.offsetParent;
    if (element == document.body) return element;

    while ((element = element.parentNode) && element != document.body)
      if (Element.getStyle(element, 'position') != 'static')
        return element;

    return document.body;
  },

  // caches x/y coordinate pair to use with overlap
  within: function(element, x, y) {
    if (this.includeScrollOffsets)
      return this.withinIncludingScrolloffsets(element, x, y);
    this.xcomp = x;
    this.ycomp = y;
    this.offset = this.cumulativeOffset(element);

    return (y >= this.offset[1] &&
            y <  this.offset[1] + element.offsetHeight &&
            x >= this.offset[0] &&
            x <  this.offset[0] + element.offsetWidth);
  },

  withinIncludingScrolloffsets: function(element, x, y) {
    var offsetcache = this.realOffset(element);

    this.xcomp = x + offsetcache[0] - this.deltaX;
    this.ycomp = y + offsetcache[1] - this.deltaY;
    this.offset = this.cumulativeOffset(element);

    return (this.ycomp >= this.offset[1] &&
            this.ycomp <  this.offset[1] + element.offsetHeight &&
            this.xcomp >= this.offset[0] &&
            this.xcomp <  this.offset[0] + element.offsetWidth);
  },

  // within must be called directly before
  overlap: function(mode, element) {
    if (!mode) return 0;
    if (mode == 'vertical')
      return ((this.offset[1] + element.offsetHeight) - this.ycomp) /
        element.offsetHeight;
    if (mode == 'horizontal')
      return ((this.offset[0] + element.offsetWidth) - this.xcomp) /
        element.offsetWidth;
  },

  page: function(forElement) {
    var valueT = 0, valueL = 0;

    var element = forElement;
    do {
      valueT += element.offsetTop  || 0;
      valueL += element.offsetLeft || 0;

      // Safari fix
      if (element.offsetParent == document.body)
        if (Element.getStyle(element,'position')=='absolute') break;

    } while (element = element.offsetParent);

    element = forElement;
    do {
      if (!window.opera || element.tagName=='BODY') {
        valueT -= element.scrollTop  || 0;
        valueL -= element.scrollLeft || 0;
      }
    } while (element = element.parentNode);

    return [valueL, valueT];
  },

  clone: function(source, target) {
    var options = Object.extend({
      setLeft:    true,
      setTop:     true,
      setWidth:   true,
      setHeight:  true,
      offsetTop:  0,
      offsetLeft: 0
    }, arguments[2] || {})

    // find page position of source
    source = $(source);
    var p = Position.page(source);

    // find coordinate system to use
    target = $(target);
    var delta = [0, 0];
    var parent = null;
    // delta [0,0] will do fine with position: fixed elements,
    // position:absolute needs offsetParent deltas
    if (Element.getStyle(target,'position') == 'absolute') {
      parent = Position.offsetParent(target);
      delta = Position.page(parent);
    }

    // correct by body offsets (fixes Safari)
    if (parent == document.body) {
      delta[0] -= document.body.offsetLeft;
      delta[1] -= document.body.offsetTop;
    }

    // set position
    if(options.setLeft)   target.style.left  = (p[0] - delta[0] + options.offsetLeft) + 'px';
    if(options.setTop)    target.style.top   = (p[1] - delta[1] + options.offsetTop) + 'px';
    if(options.setWidth)  target.style.width = source.offsetWidth + 'px';
    if(options.setHeight) target.style.height = source.offsetHeight + 'px';
  },

  absolutize: function(element) {
    element = $(element);
    if (element.style.position == 'absolute') return;
    Position.prepare();

    var offsets = Position.positionedOffset(element);
    var top     = offsets[1];
    var left    = offsets[0];
    var width   = element.clientWidth;
    var height  = element.clientHeight;

    element._originalLeft   = left - parseFloat(element.style.left  || 0);
    element._originalTop    = top  - parseFloat(element.style.top || 0);
    element._originalWidth  = element.style.width;
    element._originalHeight = element.style.height;

    element.style.position = 'absolute';
    element.style.top    = top + 'px';
    element.style.left   = left + 'px';
    element.style.width  = width + 'px';
    element.style.height = height + 'px';
  },

  relativize: function(element) {
    element = $(element);
    if (element.style.position == 'relative') return;
    Position.prepare();

    element.style.position = 'relative';
    var top  = parseFloat(element.style.top  || 0) - (element._originalTop || 0);
    var left = parseFloat(element.style.left || 0) - (element._originalLeft || 0);

    element.style.top    = top + 'px';
    element.style.left   = left + 'px';
    element.style.height = element._originalHeight;
    element.style.width  = element._originalWidth;
  }
}

// Safari returns margins on body which is incorrect if the child is absolutely
// positioned.  For performance reasons, redefine Position.cumulativeOffset for
// KHTML/WebKit only.
if (Prototype.Browser.WebKit) {
  Position.cumulativeOffset = function(element) {
    var valueT = 0, valueL = 0;
    do {
      valueT += element.offsetTop  || 0;
      valueL += element.offsetLeft || 0;
      if (element.offsetParent == document.body)
        if (Element.getStyle(element, 'position') == 'absolute') break;

      element = element.offsetParent;
    } while (element);

    return [valueL, valueT];
  }
}

Element.addMethods();


/***************************************************
 * library\scriptaculous\effects.js
 ***************************************************/

// script.aculo.us effects.js v1.7.1_beta3, Fri May 25 17:19:41 +0200 2007

// Copyright (c) 2005-2007 Thomas Fuchs (http://script.aculo.us, http://mir.aculo.us)
// Contributors:
//  Justin Palmer (http://encytemedia.com/)
//  Mark Pilgrim (http://diveintomark.org/)
//  Martin Bialasinki
// 
// script.aculo.us is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/ 

// converts rgb() and #xxx to #xxxxxx format,  
// returns self (or first argument) if not convertable  
String.prototype.parseColor = function() {  
  var color = '#';
  if(this.slice(0,4) == 'rgb(') {  
    var cols = this.slice(4,this.length-1).split(',');  
    var i=0; do { color += parseInt(cols[i]).toColorPart() } while (++i<3);  
  } else {  
    if(this.slice(0,1) == '#') {  
      if(this.length==4) for(var i=1;i<4;i++) color += (this.charAt(i) + this.charAt(i)).toLowerCase();  
      if(this.length==7) color = this.toLowerCase();  
    }  
  }  
  return(color.length==7 ? color : (arguments[0] || this));  
}

/*--------------------------------------------------------------------------*/

Element.collectTextNodes = function(element) {  
  return $A($(element).childNodes).collect( function(node) {
    return (node.nodeType==3 ? node.nodeValue : 
      (node.hasChildNodes() ? Element.collectTextNodes(node) : ''));
  }).flatten().join('');
}

Element.collectTextNodesIgnoreClass = function(element, className) {  
  return $A($(element).childNodes).collect( function(node) {
    return (node.nodeType==3 ? node.nodeValue : 
      ((node.hasChildNodes() && !Element.hasClassName(node,className)) ? 
        Element.collectTextNodesIgnoreClass(node, className) : ''));
  }).flatten().join('');
}

Element.setContentZoom = function(element, percent) {
  element = $(element);  
  element.setStyle({fontSize: (percent/100) + 'em'});   
  if(Prototype.Browser.WebKit) window.scrollBy(0,0);
  return element;
}

Element.getInlineOpacity = function(element){
  return $(element).style.opacity || '';
}

Element.forceRerendering = function(element) {
  try {
    element = $(element);
    var n = document.createTextNode(' ');
    element.appendChild(n);
    element.removeChild(n);
  } catch(e) { }
};

/*--------------------------------------------------------------------------*/

Array.prototype.call = function() {
  var args = arguments;
  this.each(function(f){ f.apply(this, args) });
}

/*--------------------------------------------------------------------------*/

var Effect = {
  _elementDoesNotExistError: {
    name: 'ElementDoesNotExistError',
    message: 'The specified DOM element does not exist, but is required for this effect to operate'
  },
  tagifyText: function(element) {
    if(typeof Builder == 'undefined')
      throw("Effect.tagifyText requires including script.aculo.us' builder.js library");
      
    var tagifyStyle = 'position:relative';
    if(Prototype.Browser.IE) tagifyStyle += ';zoom:1';
    
    element = $(element);
    $A(element.childNodes).each( function(child) {
      if(child.nodeType==3) {
        child.nodeValue.toArray().each( function(character) {
          element.insertBefore(
            Builder.node('span',{style: tagifyStyle},
              character == ' ' ? String.fromCharCode(160) : character), 
              child);
        });
        Element.remove(child);
      }
    });
  },
  multiple: function(element, effect) {
    var elements;
    if(((typeof element == 'object') || 
        (typeof element == 'function')) && 
       (element.length))
      elements = element;
    else
      elements = $(element).childNodes;
      
    var options = Object.extend({
      speed: 0.1,
      delay: 0.0
    }, arguments[2] || {});
    var masterDelay = options.delay;

    $A(elements).each( function(element, index) {
      new effect(element, Object.extend(options, { delay: index * options.speed + masterDelay }));
    });
  },
  PAIRS: {
    'slide':  ['SlideDown','SlideUp'],
    'blind':  ['BlindDown','BlindUp'],
    'appear': ['Appear','Fade']
  },
  toggle: function(element, effect) {
    element = $(element);
    effect = (effect || 'appear').toLowerCase();
    var options = Object.extend({
      queue: { position:'end', scope:(element.id || 'global'), limit: 1 }
    }, arguments[2] || {});
    Effect[element.visible() ? 
      Effect.PAIRS[effect][1] : Effect.PAIRS[effect][0]](element, options);
  }
};

var Effect2 = Effect; // deprecated

/* ------------- transitions ------------- */

Effect.Transitions = {
  linear: Prototype.K,
  sinoidal: function(pos) {
    return (-Math.cos(pos*Math.PI)/2) + 0.5;
  },
  reverse: function(pos) {
    return 1-pos;
  },
  flicker: function(pos) {
    var pos = ((-Math.cos(pos*Math.PI)/4) + 0.75) + Math.random()/4;
    return (pos > 1 ? 1 : pos);
  },
  wobble: function(pos) {
    return (-Math.cos(pos*Math.PI*(9*pos))/2) + 0.5;
  },
  pulse: function(pos, pulses) { 
    pulses = pulses || 5; 
    return (
      Math.round((pos % (1/pulses)) * pulses) == 0 ? 
            ((pos * pulses * 2) - Math.floor(pos * pulses * 2)) : 
        1 - ((pos * pulses * 2) - Math.floor(pos * pulses * 2))
      );
  },
  none: function(pos) {
    return 0;
  },
  full: function(pos) {
    return 1;
  }
};

/* ------------- core effects ------------- */

Effect.ScopedQueue = Class.create();
Object.extend(Object.extend(Effect.ScopedQueue.prototype, Enumerable), {
  initialize: function() {
    this.effects  = [];
    this.interval = null;    
  },
  _each: function(iterator) {
    this.effects._each(iterator);
  },
  add: function(effect) {
    var timestamp = new Date().getTime();
    
    var position = (typeof effect.options.queue == 'string') ? 
      effect.options.queue : effect.options.queue.position;
    
    switch(position) {
      case 'front':
        // move unstarted effects after this effect  
        this.effects.findAll(function(e){ return e.state=='idle' }).each( function(e) {
            e.startOn  += effect.finishOn;
            e.finishOn += effect.finishOn;
          });
        break;
      case 'with-last':
        timestamp = this.effects.pluck('startOn').max() || timestamp;
        break;
      case 'end':
        // start effect after last queued effect has finished
        timestamp = this.effects.pluck('finishOn').max() || timestamp;
        break;
    }
    
    effect.startOn  += timestamp;
    effect.finishOn += timestamp;

    if(!effect.options.queue.limit || (this.effects.length < effect.options.queue.limit))
      this.effects.push(effect);
    
    if(!this.interval)
      this.interval = setInterval(this.loop.bind(this), 15);
  },
  remove: function(effect) {
    this.effects = this.effects.reject(function(e) { return e==effect });
    if(this.effects.length == 0) {
      clearInterval(this.interval);
      this.interval = null;
    }
  },
  loop: function() {
    var timePos = new Date().getTime();
    for(var i=0, len=this.effects.length;i<len;i++) 
      this.effects[i] && this.effects[i].loop(timePos);
  }
});

Effect.Queues = {
  instances: $H(),
  get: function(queueName) {
    if(typeof queueName != 'string') return queueName;
    
    if(!this.instances[queueName])
      this.instances[queueName] = new Effect.ScopedQueue();
      
    return this.instances[queueName];
  }
}
Effect.Queue = Effect.Queues.get('global');

Effect.DefaultOptions = {
  transition: Effect.Transitions.sinoidal,
  duration:   1.0,   // seconds
  fps:        100,   // 100= assume 66fps max.
  sync:       false, // true for combining
  from:       0.0,
  to:         1.0,
  delay:      0.0,
  queue:      'parallel'
}

Effect.Base = function() {};
Effect.Base.prototype = {
  position: null,
  start: function(options) {
    function codeForEvent(options,eventName){
      return (
        (options[eventName+'Internal'] ? 'this.options.'+eventName+'Internal(this);' : '') +
        (options[eventName] ? 'this.options.'+eventName+'(this);' : '')
      );
    }
    if(options.transition === false) options.transition = Effect.Transitions.linear;
    this.options      = Object.extend(Object.extend({},Effect.DefaultOptions), options || {});
    this.currentFrame = 0;
    this.state        = 'idle';
    this.startOn      = this.options.delay*1000;
    this.finishOn     = this.startOn+(this.options.duration*1000);
    this.fromToDelta  = this.options.to-this.options.from;
    this.totalTime    = this.finishOn-this.startOn;
    this.totalFrames  = this.options.fps*this.options.duration;
    
    eval('this.render = function(pos){ '+
      'if(this.state=="idle"){this.state="running";'+
      codeForEvent(options,'beforeSetup')+
      (this.setup ? 'this.setup();':'')+ 
      codeForEvent(options,'afterSetup')+
      '};if(this.state=="running"){'+
      'pos=this.options.transition(pos)*'+this.fromToDelta+'+'+this.options.from+';'+
      'this.position=pos;'+
      codeForEvent(options,'beforeUpdate')+
      (this.update ? 'this.update(pos);':'')+
      codeForEvent(options,'afterUpdate')+
      '}}');
    
    this.event('beforeStart');
    if(!this.options.sync)
      Effect.Queues.get(typeof this.options.queue == 'string' ? 
        'global' : this.options.queue.scope).add(this);
  },
  loop: function(timePos) {
    if(timePos >= this.startOn) {
      if(timePos >= this.finishOn) {
        this.render(1.0);
        this.cancel();
        this.event('beforeFinish');
        if(this.finish) this.finish(); 
        this.event('afterFinish');
        return;  
      }
      var pos   = (timePos - this.startOn) / this.totalTime,
          frame = Math.round(pos * this.totalFrames);
      if(frame > this.currentFrame) {
        this.render(pos);
        this.currentFrame = frame;
      }
    }
  },
  cancel: function() {
    if(!this.options.sync)
      Effect.Queues.get(typeof this.options.queue == 'string' ? 
        'global' : this.options.queue.scope).remove(this);
    this.state = 'finished';
  },
  event: function(eventName) {
    if(this.options[eventName + 'Internal']) this.options[eventName + 'Internal'](this);
    if(this.options[eventName]) this.options[eventName](this);
  },
  inspect: function() {
    var data = $H();
    for(property in this)
      if(typeof this[property] != 'function') data[property] = this[property];
    return '#<Effect:' + data.inspect() + ',options:' + $H(this.options).inspect() + '>';
  }
}

Effect.Parallel = Class.create();
Object.extend(Object.extend(Effect.Parallel.prototype, Effect.Base.prototype), {
  initialize: function(effects) {
    this.effects = effects || [];
    this.start(arguments[1]);
  },
  update: function(position) {
    this.effects.invoke('render', position);
  },
  finish: function(position) {
    this.effects.each( function(effect) {
      effect.render(1.0);
      effect.cancel();
      effect.event('beforeFinish');
      if(effect.finish) effect.finish(position);
      effect.event('afterFinish');
    });
  }
});

Effect.Event = Class.create();
Object.extend(Object.extend(Effect.Event.prototype, Effect.Base.prototype), {
  initialize: function() {
    var options = Object.extend({
      duration: 0
    }, arguments[0] || {});
    this.start(options);
  },
  update: Prototype.emptyFunction
});

Effect.Opacity = Class.create();
Object.extend(Object.extend(Effect.Opacity.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    if(!this.element) throw(Effect._elementDoesNotExistError);
    // make this work on IE on elements without 'layout'
    if(Prototype.Browser.IE && (!this.element.currentStyle.hasLayout))
      this.element.setStyle({zoom: 1});
    var options = Object.extend({
      from: this.element.getOpacity() || 0.0,
      to:   1.0
    }, arguments[1] || {});
    this.start(options);
  },
  update: function(position) {
    this.element.setOpacity(position);
  }
});

Effect.Move = Class.create();
Object.extend(Object.extend(Effect.Move.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    if(!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({
      x:    0,
      y:    0,
      mode: 'relative'
    }, arguments[1] || {});
    this.start(options);
  },
  setup: function() {
    // Bug in Opera: Opera returns the "real" position of a static element or
    // relative element that does not have top/left explicitly set.
    // ==> Always set top and left for position relative elements in your stylesheets 
    // (to 0 if you do not need them) 
    this.element.makePositioned();
    this.originalLeft = parseFloat(this.element.getStyle('left') || '0');
    this.originalTop  = parseFloat(this.element.getStyle('top')  || '0');
    if(this.options.mode == 'absolute') {
      // absolute movement, so we need to calc deltaX and deltaY
      this.options.x = this.options.x - this.originalLeft;
      this.options.y = this.options.y - this.originalTop;
    }
  },
  update: function(position) {
    this.element.setStyle({
      left: Math.round(this.options.x  * position + this.originalLeft) + 'px',
      top:  Math.round(this.options.y  * position + this.originalTop)  + 'px'
    });
  }
});

// for backwards compatibility
Effect.MoveBy = function(element, toTop, toLeft) {
  return new Effect.Move(element, 
    Object.extend({ x: toLeft, y: toTop }, arguments[3] || {}));
};

Effect.Scale = Class.create();
Object.extend(Object.extend(Effect.Scale.prototype, Effect.Base.prototype), {
  initialize: function(element, percent) {
    this.element = $(element);
    if(!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({
      scaleX: true,
      scaleY: true,
      scaleContent: true,
      scaleFromCenter: false,
      scaleMode: 'box',        // 'box' or 'contents' or {} with provided values
      scaleFrom: 100.0,
      scaleTo:   percent
    }, arguments[2] || {});
    this.start(options);
  },
  setup: function() {
    this.restoreAfterFinish = this.options.restoreAfterFinish || false;
    this.elementPositioning = this.element.getStyle('position');
    
    this.originalStyle = {};
    ['top','left','width','height','fontSize'].each( function(k) {
      this.originalStyle[k] = this.element.style[k];
    }.bind(this));
      
    this.originalTop  = this.element.offsetTop;
    this.originalLeft = this.element.offsetLeft;
    
    var fontSize = this.element.getStyle('font-size') || '100%';
    ['em','px','%','pt'].each( function(fontSizeType) {
      if(fontSize.indexOf(fontSizeType)>0) {
        this.fontSize     = parseFloat(fontSize);
        this.fontSizeType = fontSizeType;
      }
    }.bind(this));
    
    this.factor = (this.options.scaleTo - this.options.scaleFrom)/100;
    
    this.dims = null;
    if(this.options.scaleMode=='box')
      this.dims = [this.element.offsetHeight, this.element.offsetWidth];
    if(/^content/.test(this.options.scaleMode))
      this.dims = [this.element.scrollHeight, this.element.scrollWidth];
    if(!this.dims)
      this.dims = [this.options.scaleMode.originalHeight,
                   this.options.scaleMode.originalWidth];
  },
  update: function(position) {
    var currentScale = (this.options.scaleFrom/100.0) + (this.factor * position);
    if(this.options.scaleContent && this.fontSize)
      this.element.setStyle({fontSize: this.fontSize * currentScale + this.fontSizeType });
    this.setDimensions(this.dims[0] * currentScale, this.dims[1] * currentScale);
  },
  finish: function(position) {
    if(this.restoreAfterFinish) this.element.setStyle(this.originalStyle);
  },
  setDimensions: function(height, width) {
    var d = {};
    if(this.options.scaleX) d.width = Math.round(width) + 'px';
    if(this.options.scaleY) d.height = Math.round(height) + 'px';
    if(this.options.scaleFromCenter) {
      var topd  = (height - this.dims[0])/2;
      var leftd = (width  - this.dims[1])/2;
      if(this.elementPositioning == 'absolute') {
        if(this.options.scaleY) d.top = this.originalTop-topd + 'px';
        if(this.options.scaleX) d.left = this.originalLeft-leftd + 'px';
      } else {
        if(this.options.scaleY) d.top = -topd + 'px';
        if(this.options.scaleX) d.left = -leftd + 'px';
      }
    }
    this.element.setStyle(d);
  }
});

Effect.Highlight = Class.create();
Object.extend(Object.extend(Effect.Highlight.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    if(!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({ startcolor: '#ffff99' }, arguments[1] || {});
    this.start(options);
  },
  setup: function() {
    // Prevent executing on elements not in the layout flow
    if(this.element.getStyle('display')=='none') { this.cancel(); return; }
    // Disable background image during the effect
    this.oldStyle = {};
    if (!this.options.keepBackgroundImage) {
      this.oldStyle.backgroundImage = this.element.getStyle('background-image');
      this.element.setStyle({backgroundImage: 'none'});
    }
    if(!this.options.endcolor)
      this.options.endcolor = this.element.getStyle('background-color').parseColor('#ffffff');
    if(!this.options.restorecolor)
      this.options.restorecolor = this.element.getStyle('background-color');
    // init color calculations
    this._base  = $R(0,2).map(function(i){ return parseInt(this.options.startcolor.slice(i*2+1,i*2+3),16) }.bind(this));
    this._delta = $R(0,2).map(function(i){ return parseInt(this.options.endcolor.slice(i*2+1,i*2+3),16)-this._base[i] }.bind(this));
  },
  update: function(position) {
    this.element.setStyle({backgroundColor: $R(0,2).inject('#',function(m,v,i){
      return m+(Math.round(this._base[i]+(this._delta[i]*position)).toColorPart()); }.bind(this)) });
  },
  finish: function() {
    this.element.setStyle(Object.extend(this.oldStyle, {
      backgroundColor: this.options.restorecolor
    }));
  }
});

Effect.ScrollTo = Class.create();
Object.extend(Object.extend(Effect.ScrollTo.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    this.start(arguments[1] || {});
  },
  setup: function() {
    Position.prepare();
    var offsets = Position.cumulativeOffset(this.element);
    if(this.options.offset) offsets[1] += this.options.offset;
    var max = window.innerHeight ? 
      window.height - window.innerHeight :
      document.body.scrollHeight - 
        (document.documentElement.clientHeight ? 
          document.documentElement.clientHeight : document.body.clientHeight);
    this.scrollStart = Position.deltaY;
    this.delta = (offsets[1] > max ? max : offsets[1]) - this.scrollStart;
  },
  update: function(position) {
    Position.prepare();
    window.scrollTo(Position.deltaX, 
      this.scrollStart + (position*this.delta));
  }
});

/* ------------- combination effects ------------- */

Effect.Fade = function(element) {
  element = $(element);
  var oldOpacity = element.getInlineOpacity();
  var options = Object.extend({
  from: element.getOpacity() || 1.0,
  to:   0.0,
  afterFinishInternal: function(effect) { 
    if(effect.options.to!=0) return;
    effect.element.hide().setStyle({opacity: oldOpacity}); 
  }}, arguments[1] || {});
  return new Effect.Opacity(element,options);
}

Effect.Appear = function(element) {
  element = $(element);
  var options = Object.extend({
  from: (element.getStyle('display') == 'none' ? 0.0 : element.getOpacity() || 0.0),
  to:   1.0,
  // force Safari to render floated elements properly
  afterFinishInternal: function(effect) {
    effect.element.forceRerendering();
  },
  beforeSetup: function(effect) {
    effect.element.setOpacity(effect.options.from).show(); 
  }}, arguments[1] || {});
  return new Effect.Opacity(element,options);
}

Effect.Puff = function(element) {
  element = $(element);
  var oldStyle = { 
    opacity: element.getInlineOpacity(), 
    position: element.getStyle('position'),
    top:  element.style.top,
    left: element.style.left,
    width: element.style.width,
    height: element.style.height
  };
  return new Effect.Parallel(
   [ new Effect.Scale(element, 200, 
      { sync: true, scaleFromCenter: true, scaleContent: true, restoreAfterFinish: true }), 
     new Effect.Opacity(element, { sync: true, to: 0.0 } ) ], 
     Object.extend({ duration: 1.0, 
      beforeSetupInternal: function(effect) {
        Position.absolutize(effect.effects[0].element)
      },
      afterFinishInternal: function(effect) {
         effect.effects[0].element.hide().setStyle(oldStyle); }
     }, arguments[1] || {})
   );
}

Effect.BlindUp = function(element) {
  element = $(element);
  element.makeClipping();
  return new Effect.Scale(element, 0,
    Object.extend({ scaleContent: false, 
      scaleX: false, 
      restoreAfterFinish: true,
      afterFinishInternal: function(effect) {
        effect.element.hide().undoClipping();
      } 
    }, arguments[1] || {})
  );
}

Effect.BlindDown = function(element) {
  element = $(element);
  var elementDimensions = element.getDimensions();
  return new Effect.Scale(element, 100, Object.extend({ 
    scaleContent: false, 
    scaleX: false,
    scaleFrom: 0,
    scaleMode: {originalHeight: elementDimensions.height, originalWidth: elementDimensions.width},
    restoreAfterFinish: true,
    afterSetup: function(effect) {
      effect.element.makeClipping().setStyle({height: '0px'}).show(); 
    },  
    afterFinishInternal: function(effect) {
      effect.element.undoClipping();
    }
  }, arguments[1] || {}));
}

Effect.SwitchOff = function(element) {
  element = $(element);
  var oldOpacity = element.getInlineOpacity();
  return new Effect.Appear(element, Object.extend({
    duration: 0.4,
    from: 0,
    transition: Effect.Transitions.flicker,
    afterFinishInternal: function(effect) {
      new Effect.Scale(effect.element, 1, { 
        duration: 0.3, scaleFromCenter: true,
        scaleX: false, scaleContent: false, restoreAfterFinish: true,
        beforeSetup: function(effect) { 
          effect.element.makePositioned().makeClipping();
        },
        afterFinishInternal: function(effect) {
          effect.element.hide().undoClipping().undoPositioned().setStyle({opacity: oldOpacity});
        }
      })
    }
  }, arguments[1] || {}));
}

Effect.DropOut = function(element) {
  element = $(element);
  var oldStyle = {
    top: element.getStyle('top'),
    left: element.getStyle('left'),
    opacity: element.getInlineOpacity() };
  return new Effect.Parallel(
    [ new Effect.Move(element, {x: 0, y: 100, sync: true }), 
      new Effect.Opacity(element, { sync: true, to: 0.0 }) ],
    Object.extend(
      { duration: 0.5,
        beforeSetup: function(effect) {
          effect.effects[0].element.makePositioned(); 
        },
        afterFinishInternal: function(effect) {
          effect.effects[0].element.hide().undoPositioned().setStyle(oldStyle);
        } 
      }, arguments[1] || {}));
}

Effect.Shake = function(element) {
  element = $(element);
  var oldStyle = {
    top: element.getStyle('top'),
    left: element.getStyle('left') };
    return new Effect.Move(element, 
      { x:  20, y: 0, duration: 0.05, afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x: -40, y: 0, duration: 0.1,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x:  40, y: 0, duration: 0.1,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x: -40, y: 0, duration: 0.1,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x:  40, y: 0, duration: 0.1,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x: -20, y: 0, duration: 0.05, afterFinishInternal: function(effect) {
        effect.element.undoPositioned().setStyle(oldStyle);
  }}) }}) }}) }}) }}) }});
}

Effect.SlideDown = function(element) {
  element = $(element).cleanWhitespace();
  // SlideDown need to have the content of the element wrapped in a container element with fixed height!
  var oldInnerBottom = element.down().getStyle('bottom');
  var elementDimensions = element.getDimensions();
  return new Effect.Scale(element, 100, Object.extend({ 
    scaleContent: false, 
    scaleX: false, 
    scaleFrom: window.opera ? 0 : 1,
    scaleMode: {originalHeight: elementDimensions.height, originalWidth: elementDimensions.width},
    restoreAfterFinish: true,
    afterSetup: function(effect) {
      effect.element.makePositioned();
      effect.element.down().makePositioned();
      if(window.opera) effect.element.setStyle({top: ''});
      effect.element.makeClipping().setStyle({height: '0px'}).show(); 
    },
    afterUpdateInternal: function(effect) {
      effect.element.down().setStyle({bottom:
        (effect.dims[0] - effect.element.clientHeight) + 'px' }); 
    },
    afterFinishInternal: function(effect) {
      effect.element.undoClipping().undoPositioned();
      effect.element.down().undoPositioned().setStyle({bottom: oldInnerBottom}); }
    }, arguments[1] || {})
  );
}

Effect.SlideUp = function(element) {
  element = $(element).cleanWhitespace();
  var oldInnerBottom = element.down().getStyle('bottom');
  return new Effect.Scale(element, window.opera ? 0 : 1,
   Object.extend({ scaleContent: false, 
    scaleX: false, 
    scaleMode: 'box',
    scaleFrom: 100,
    restoreAfterFinish: true,
    beforeStartInternal: function(effect) {
      effect.element.makePositioned();
      effect.element.down().makePositioned();
      if(window.opera) effect.element.setStyle({top: ''});
      effect.element.makeClipping().show();
    },  
    afterUpdateInternal: function(effect) {
      effect.element.down().setStyle({bottom:
        (effect.dims[0] - effect.element.clientHeight) + 'px' });
    },
    afterFinishInternal: function(effect) {
      effect.element.hide().undoClipping().undoPositioned().setStyle({bottom: oldInnerBottom});
      effect.element.down().undoPositioned();
    }
   }, arguments[1] || {})
  );
}

// Bug in opera makes the TD containing this element expand for a instance after finish 
Effect.Squish = function(element) {
  return new Effect.Scale(element, window.opera ? 1 : 0, { 
    restoreAfterFinish: true,
    beforeSetup: function(effect) {
      effect.element.makeClipping(); 
    },  
    afterFinishInternal: function(effect) {
      effect.element.hide().undoClipping(); 
    }
  });
}

Effect.Grow = function(element) {
  element = $(element);
  var options = Object.extend({
    direction: 'center',
    moveTransition: Effect.Transitions.sinoidal,
    scaleTransition: Effect.Transitions.sinoidal,
    opacityTransition: Effect.Transitions.full
  }, arguments[1] || {});
  var oldStyle = {
    top: element.style.top,
    left: element.style.left,
    height: element.style.height,
    width: element.style.width,
    opacity: element.getInlineOpacity() };

  var dims = element.getDimensions();    
  var initialMoveX, initialMoveY;
  var moveX, moveY;
  
  switch (options.direction) {
    case 'top-left':
      initialMoveX = initialMoveY = moveX = moveY = 0; 
      break;
    case 'top-right':
      initialMoveX = dims.width;
      initialMoveY = moveY = 0;
      moveX = -dims.width;
      break;
    case 'bottom-left':
      initialMoveX = moveX = 0;
      initialMoveY = dims.height;
      moveY = -dims.height;
      break;
    case 'bottom-right':
      initialMoveX = dims.width;
      initialMoveY = dims.height;
      moveX = -dims.width;
      moveY = -dims.height;
      break;
    case 'center':
      initialMoveX = dims.width / 2;
      initialMoveY = dims.height / 2;
      moveX = -dims.width / 2;
      moveY = -dims.height / 2;
      break;
  }
  
  return new Effect.Move(element, {
    x: initialMoveX,
    y: initialMoveY,
    duration: 0.01, 
    beforeSetup: function(effect) {
      effect.element.hide().makeClipping().makePositioned();
    },
    afterFinishInternal: function(effect) {
      new Effect.Parallel(
        [ new Effect.Opacity(effect.element, { sync: true, to: 1.0, from: 0.0, transition: options.opacityTransition }),
          new Effect.Move(effect.element, { x: moveX, y: moveY, sync: true, transition: options.moveTransition }),
          new Effect.Scale(effect.element, 100, {
            scaleMode: { originalHeight: dims.height, originalWidth: dims.width }, 
            sync: true, scaleFrom: window.opera ? 1 : 0, transition: options.scaleTransition, restoreAfterFinish: true})
        ], Object.extend({
             beforeSetup: function(effect) {
               effect.effects[0].element.setStyle({height: '0px'}).show(); 
             },
             afterFinishInternal: function(effect) {
               effect.effects[0].element.undoClipping().undoPositioned().setStyle(oldStyle); 
             }
           }, options)
      )
    }
  });
}

Effect.Shrink = function(element) {
  element = $(element);
  var options = Object.extend({
    direction: 'center',
    moveTransition: Effect.Transitions.sinoidal,
    scaleTransition: Effect.Transitions.sinoidal,
    opacityTransition: Effect.Transitions.none
  }, arguments[1] || {});
  var oldStyle = {
    top: element.style.top,
    left: element.style.left,
    height: element.style.height,
    width: element.style.width,
    opacity: element.getInlineOpacity() };

  var dims = element.getDimensions();
  var moveX, moveY;
  
  switch (options.direction) {
    case 'top-left':
      moveX = moveY = 0;
      break;
    case 'top-right':
      moveX = dims.width;
      moveY = 0;
      break;
    case 'bottom-left':
      moveX = 0;
      moveY = dims.height;
      break;
    case 'bottom-right':
      moveX = dims.width;
      moveY = dims.height;
      break;
    case 'center':  
      moveX = dims.width / 2;
      moveY = dims.height / 2;
      break;
  }
  
  return new Effect.Parallel(
    [ new Effect.Opacity(element, { sync: true, to: 0.0, from: 1.0, transition: options.opacityTransition }),
      new Effect.Scale(element, window.opera ? 1 : 0, { sync: true, transition: options.scaleTransition, restoreAfterFinish: true}),
      new Effect.Move(element, { x: moveX, y: moveY, sync: true, transition: options.moveTransition })
    ], Object.extend({            
         beforeStartInternal: function(effect) {
           effect.effects[0].element.makePositioned().makeClipping(); 
         },
         afterFinishInternal: function(effect) {
           effect.effects[0].element.hide().undoClipping().undoPositioned().setStyle(oldStyle); }
       }, options)
  );
}

Effect.Pulsate = function(element) {
  element = $(element);
  var options    = arguments[1] || {};
  var oldOpacity = element.getInlineOpacity();
  var transition = options.transition || Effect.Transitions.sinoidal;
  var reverser   = function(pos){ return transition(1-Effect.Transitions.pulse(pos, options.pulses)) };
  reverser.bind(transition);
  return new Effect.Opacity(element, 
    Object.extend(Object.extend({  duration: 2.0, from: 0,
      afterFinishInternal: function(effect) { effect.element.setStyle({opacity: oldOpacity}); }
    }, options), {transition: reverser}));
}

Effect.Fold = function(element) {
  element = $(element);
  var oldStyle = {
    top: element.style.top,
    left: element.style.left,
    width: element.style.width,
    height: element.style.height };
  element.makeClipping();
  return new Effect.Scale(element, 5, Object.extend({   
    scaleContent: false,
    scaleX: false,
    afterFinishInternal: function(effect) {
    new Effect.Scale(element, 1, { 
      scaleContent: false, 
      scaleY: false,
      afterFinishInternal: function(effect) {
        effect.element.hide().undoClipping().setStyle(oldStyle);
      } });
  }}, arguments[1] || {}));
};

Effect.Morph = Class.create();
Object.extend(Object.extend(Effect.Morph.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    if(!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({
      style: {}
    }, arguments[1] || {});
    if (typeof options.style == 'string') {
      if(options.style.indexOf(':') == -1) {
        var cssText = '', selector = '.' + options.style;
        $A(document.styleSheets).reverse().each(function(styleSheet) {
          if (styleSheet.cssRules) cssRules = styleSheet.cssRules;
          else if (styleSheet.rules) cssRules = styleSheet.rules;
          $A(cssRules).reverse().each(function(rule) {
            if (selector == rule.selectorText) {
              cssText = rule.style.cssText;
              throw $break;
            }
          });
          if (cssText) throw $break;
        });
        this.style = cssText.parseStyle();
        options.afterFinishInternal = function(effect){
          effect.element.addClassName(effect.options.style);
          effect.transforms.each(function(transform) {
            if(transform.style != 'opacity')
              effect.element.style[transform.style] = '';
          });
        }
      } else this.style = options.style.parseStyle();
    } else this.style = $H(options.style)
    this.start(options);
  },
  setup: function(){
    function parseColor(color){
      if(!color || ['rgba(0, 0, 0, 0)','transparent'].include(color)) color = '#ffffff';
      color = color.parseColor();
      return $R(0,2).map(function(i){
        return parseInt( color.slice(i*2+1,i*2+3), 16 ) 
      });
    }
    this.transforms = this.style.map(function(pair){
      var property = pair[0], value = pair[1], unit = null;

      if(value.parseColor('#zzzzzz') != '#zzzzzz') {
        value = value.parseColor();
        unit  = 'color';
      } else if(property == 'opacity') {
        value = parseFloat(value);
        if(Prototype.Browser.IE && (!this.element.currentStyle.hasLayout))
          this.element.setStyle({zoom: 1});
      } else if(Element.CSS_LENGTH.test(value)) {
          var components = value.match(/^([\+\-]?[0-9\.]+)(.*)$/);
          value = parseFloat(components[1]);
          unit = (components.length == 3) ? components[2] : null;
      }

      var originalValue = this.element.getStyle(property);
      return { 
        style: property.camelize(), 
        originalValue: unit=='color' ? parseColor(originalValue) : parseFloat(originalValue || 0), 
        targetValue: unit=='color' ? parseColor(value) : value,
        unit: unit
      };
    }.bind(this)).reject(function(transform){
      return (
        (transform.originalValue == transform.targetValue) ||
        (
          transform.unit != 'color' &&
          (isNaN(transform.originalValue) || isNaN(transform.targetValue))
        )
      )
    });
  },
  update: function(position) {
    var style = {}, transform, i = this.transforms.length;
    while(i--)
      style[(transform = this.transforms[i]).style] = 
        transform.unit=='color' ? '#'+
          (Math.round(transform.originalValue[0]+
            (transform.targetValue[0]-transform.originalValue[0])*position)).toColorPart() +
          (Math.round(transform.originalValue[1]+
            (transform.targetValue[1]-transform.originalValue[1])*position)).toColorPart() +
          (Math.round(transform.originalValue[2]+
            (transform.targetValue[2]-transform.originalValue[2])*position)).toColorPart() :
        transform.originalValue + Math.round(
          ((transform.targetValue - transform.originalValue) * position) * 1000)/1000 + transform.unit;
    this.element.setStyle(style, true);
  }
});

Effect.Transform = Class.create();
Object.extend(Effect.Transform.prototype, {
  initialize: function(tracks){
    this.tracks  = [];
    this.options = arguments[1] || {};
    this.addTracks(tracks);
  },
  addTracks: function(tracks){
    tracks.each(function(track){
      var data = $H(track).values().first();
      this.tracks.push($H({
        ids:     $H(track).keys().first(),
        effect:  Effect.Morph,
        options: { style: data }
      }));
    }.bind(this));
    return this;
  },
  play: function(){
    return new Effect.Parallel(
      this.tracks.map(function(track){
        var elements = [$(track.ids) || $$(track.ids)].flatten();
        return elements.map(function(e){ return new track.effect(e, Object.extend({ sync:true }, track.options)) });
      }).flatten(),
      this.options
    );
  }
});

Element.CSS_PROPERTIES = $w(
  'backgroundColor backgroundPosition borderBottomColor borderBottomStyle ' + 
  'borderBottomWidth borderLeftColor borderLeftStyle borderLeftWidth ' +
  'borderRightColor borderRightStyle borderRightWidth borderSpacing ' +
  'borderTopColor borderTopStyle borderTopWidth bottom clip color ' +
  'fontSize fontWeight height left letterSpacing lineHeight ' +
  'marginBottom marginLeft marginRight marginTop markerOffset maxHeight '+
  'maxWidth minHeight minWidth opacity outlineColor outlineOffset ' +
  'outlineWidth paddingBottom paddingLeft paddingRight paddingTop ' +
  'right textIndent top width wordSpacing zIndex');
  
Element.CSS_LENGTH = /^(([\+\-]?[0-9\.]+)(em|ex|px|in|cm|mm|pt|pc|\%))|0$/;

String.prototype.parseStyle = function(){
  var element = document.createElement('div');
  element.innerHTML = '<div style="' + this + '"></div>';
  var style = element.childNodes[0].style, styleRules = $H();
  
  Element.CSS_PROPERTIES.each(function(property){
    if(style[property]) styleRules[property] = style[property]; 
  });
  if(Prototype.Browser.IE && this.indexOf('opacity') > -1) {
    styleRules.opacity = this.match(/opacity:\s*((?:0|1)?(?:\.\d*)?)/)[1];
  }
  return styleRules;
};

Element.morph = function(element, style) {
  new Effect.Morph(element, Object.extend({ style: style }, arguments[2] || {}));
  return element;
};

['getInlineOpacity','forceRerendering','setContentZoom',
 'collectTextNodes','collectTextNodesIgnoreClass','morph'].each( 
  function(f) { Element.Methods[f] = Element[f]; }
);

Element.Methods.visualEffect = function(element, effect, options) {
  s = effect.dasherize().camelize();
  effect_class = s.charAt(0).toUpperCase() + s.substring(1);
  new Effect[effect_class](element, options);
  return $(element);
};

Element.addMethods();


/***************************************************
 * library\scriptaculous\builder.js
 ***************************************************/

// script.aculo.us builder.js v1.7.1_beta3, Fri May 25 17:19:41 +0200 2007

// Copyright (c) 2005-2007 Thomas Fuchs (http://script.aculo.us, http://mir.aculo.us)
//
// script.aculo.us is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/

var Builder = {
  NODEMAP: {
    AREA: 'map',
    CAPTION: 'table',
    COL: 'table',
    COLGROUP: 'table',
    LEGEND: 'fieldset',
    OPTGROUP: 'select',
    OPTION: 'select',
    PARAM: 'object',
    TBODY: 'table',
    TD: 'table',
    TFOOT: 'table',
    TH: 'table',
    THEAD: 'table',
    TR: 'table'
  },
  // note: For Firefox < 1.5, OPTION and OPTGROUP tags are currently broken,
  //       due to a Firefox bug
  node: function(elementName) {
    elementName = elementName.toUpperCase();
    
    // try innerHTML approach
    var parentTag = this.NODEMAP[elementName] || 'div';
    var parentElement = document.createElement(parentTag);
    try { // prevent IE "feature": http://dev.rubyonrails.org/ticket/2707
      parentElement.innerHTML = "<" + elementName + "></" + elementName + ">";
    } catch(e) {}
    var element = parentElement.firstChild || null;
      
    // see if browser added wrapping tags
    if(element && (element.tagName.toUpperCase() != elementName))
      element = element.getElementsByTagName(elementName)[0];
    
    // fallback to createElement approach
    if(!element) element = document.createElement(elementName);
    
    // abort if nothing could be created
    if(!element) return;

    // attributes (or text)
    if(arguments[1])
      if(this._isStringOrNumber(arguments[1]) ||
        (arguments[1] instanceof Array) ||
        arguments[1].tagName) {
          this._children(element, arguments[1]);
        } else {
          var attrs = this._attributes(arguments[1]);
          if(attrs.length) {
            try { // prevent IE "feature": http://dev.rubyonrails.org/ticket/2707
              parentElement.innerHTML = "<" +elementName + " " +
                attrs + "></" + elementName + ">";
            } catch(e) {}
            element = parentElement.firstChild || null;
            // workaround firefox 1.0.X bug
            if(!element) {
              element = document.createElement(elementName);
              for(attr in arguments[1]) 
                element[attr == 'class' ? 'className' : attr] = arguments[1][attr];
            }
            if(element.tagName.toUpperCase() != elementName)
              element = parentElement.getElementsByTagName(elementName)[0];
          }
        } 

    // text, or array of children
    if(arguments[2])
      this._children(element, arguments[2]);

     return element;
  },
  _text: function(text) {
     return document.createTextNode(text);
  },

  ATTR_MAP: {
    'className': 'class',
    'htmlFor': 'for'
  },

  _attributes: function(attributes) {
    var attrs = [];
    for(attribute in attributes)
      attrs.push((attribute in this.ATTR_MAP ? this.ATTR_MAP[attribute] : attribute) +
          '="' + attributes[attribute].toString().escapeHTML().gsub(/"/,'&quot;') + '"');
    return attrs.join(" ");
  },
  _children: function(element, children) {
    if(children.tagName) {
      element.appendChild(children);
      return;
    }
    if(typeof children=='object') { // array can hold nodes and text
      children.flatten().each( function(e) {
        if(typeof e=='object')
          element.appendChild(e)
        else
          if(Builder._isStringOrNumber(e))
            element.appendChild(Builder._text(e));
      });
    } else
      if(Builder._isStringOrNumber(children))
        element.appendChild(Builder._text(children));
  },
  _isStringOrNumber: function(param) {
    return(typeof param=='string' || typeof param=='number');
  },
  build: function(html) {
    var element = this.node('div');
    $(element).update(html.strip());
    return element.down();
  },
  dump: function(scope) { 
    if(typeof scope != 'object' && typeof scope != 'function') scope = window; //global scope 
  
    var tags = ("A ABBR ACRONYM ADDRESS APPLET AREA B BASE BASEFONT BDO BIG BLOCKQUOTE BODY " +
      "BR BUTTON CAPTION CENTER CITE CODE COL COLGROUP DD DEL DFN DIR DIV DL DT EM FIELDSET " +
      "FONT FORM FRAME FRAMESET H1 H2 H3 H4 H5 H6 HEAD HR HTML I IFRAME IMG INPUT INS ISINDEX "+
      "KBD LABEL LEGEND LI LINK MAP MENU META NOFRAMES NOSCRIPT OBJECT OL OPTGROUP OPTION P "+
      "PARAM PRE Q S SAMP SCRIPT SELECT SMALL SPAN STRIKE STRONG STYLE SUB SUP TABLE TBODY TD "+
      "TEXTAREA TFOOT TH THEAD TITLE TR TT U UL VAR").split(/\s+/);
  
    tags.each( function(tag){ 
      scope[tag] = function() { 
        return Builder.node.apply(Builder, [tag].concat($A(arguments)));  
      } 
    });
  }
}



/***************************************************
 * library\scriptaculous\controls.js
 ***************************************************/

// script.aculo.us controls.js v1.7.1_beta3, Fri May 25 17:19:41 +0200 2007

// Copyright (c) 2005-2007 Thomas Fuchs (http://script.aculo.us, http://mir.aculo.us)
//           (c) 2005-2007 Ivan Krstic (http://blogs.law.harvard.edu/ivan)
//           (c) 2005-2007 Jon Tirsen (http://www.tirsen.com)
// Contributors:
//  Richard Livsey
//  Rahul Bhargava
//  Rob Wills
// 
// script.aculo.us is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/

// Autocompleter.Base handles all the autocompletion functionality 
// that's independent of the data source for autocompletion. This
// includes drawing the autocompletion menu, observing keyboard
// and mouse events, and similar.
//
// Specific autocompleters need to provide, at the very least, 
// a getUpdatedChoices function that will be invoked every time
// the text inside the monitored textbox changes. This method 
// should get the text for which to provide autocompletion by
// invoking this.getToken(), NOT by directly accessing
// this.element.value. This is to allow incremental tokenized
// autocompletion. Specific auto-completion logic (AJAX, etc)
// belongs in getUpdatedChoices.
//
// Tokenized incremental autocompletion is enabled automatically
// when an autocompleter is instantiated with the 'tokens' option
// in the options parameter, e.g.:
// new Ajax.Autocompleter('id','upd', '/url/', { tokens: ',' });
// will incrementally autocomplete with a comma as the token.
// Additionally, ',' in the above example can be replaced with
// a token array, e.g. { tokens: [',', '\n'] } which
// enables autocompletion on multiple tokens. This is most 
// useful when one of the tokens is \n (a newline), as it 
// allows smart autocompletion after linebreaks.

if(typeof Effect == 'undefined')
  throw("controls.js requires including script.aculo.us' effects.js library");

var Autocompleter = {}
Autocompleter.Base = function() {};
Autocompleter.Base.prototype = {
  baseInitialize: function(element, update, options) {
    element          = $(element)
    this.element     = element; 
    this.update      = $(update);  
    this.hasFocus    = false; 
    this.changed     = false; 
    this.active      = false; 
    this.index       = 0;     
    this.entryCount  = 0;

    if(this.setOptions)
      this.setOptions(options);
    else
      this.options = options || {};

    this.options.paramName    = this.options.paramName || this.element.name;
    this.options.tokens       = this.options.tokens || [];
    this.options.frequency    = this.options.frequency || 0.4;
    this.options.minChars     = this.options.minChars || 1;
    this.options.onShow       = this.options.onShow || 
      function(element, update){ 
        if(!update.style.position || update.style.position=='absolute') {
          update.style.position = 'absolute';
          Position.clone(element, update, {
            setHeight: false, 
            offsetTop: element.offsetHeight
          });
        }
        Effect.Appear(update,{duration:0.15});
      };
    this.options.onHide = this.options.onHide || 
      function(element, update){ new Effect.Fade(update,{duration:0.15}) };

    if(typeof(this.options.tokens) == 'string') 
      this.options.tokens = new Array(this.options.tokens);

    this.observer = null;
    
    this.element.setAttribute('autocomplete','off');

    Element.hide(this.update);

    Event.observe(this.element, 'blur', this.onBlur.bindAsEventListener(this));
    Event.observe(this.element, 'keypress', this.onKeyPress.bindAsEventListener(this));

    // Turn autocomplete back on when the user leaves the page, so that the
    // field's value will be remembered on Mozilla-based browsers.
    Event.observe(window, 'beforeunload', function(){ 
      element.setAttribute('autocomplete', 'on'); 
    });
  },

  show: function() {
    if(Element.getStyle(this.update, 'display')=='none') this.options.onShow(this.element, this.update);
    if(!this.iefix && 
      (Prototype.Browser.IE) &&
      (Element.getStyle(this.update, 'position')=='absolute')) {
      new Insertion.After(this.update, 
       '<iframe id="' + this.update.id + '_iefix" '+
       'style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);" ' +
       'src="javascript:false;" frameborder="0" scrolling="no"></iframe>');
      this.iefix = $(this.update.id+'_iefix');
    }
    if(this.iefix) setTimeout(this.fixIEOverlapping.bind(this), 50);
  },
  
  fixIEOverlapping: function() {
    Position.clone(this.update, this.iefix, {setTop:(!this.update.style.height)});
    this.iefix.style.zIndex = 1;
    this.update.style.zIndex = 2;
    Element.show(this.iefix);
  },

  hide: function() {
    this.stopIndicator();
    if(Element.getStyle(this.update, 'display')!='none') this.options.onHide(this.element, this.update);
    if(this.iefix) Element.hide(this.iefix);
  },

  startIndicator: function() {
    if(this.options.indicator) Element.show(this.options.indicator);
  },

  stopIndicator: function() {
    if(this.options.indicator) Element.hide(this.options.indicator);
  },

  onKeyPress: function(event) {
    if(this.active)
      switch(event.keyCode) {
       case Event.KEY_TAB:
       case Event.KEY_RETURN:
         this.selectEntry();
         Event.stop(event);
       case Event.KEY_ESC:
         this.hide();
         this.active = false;
         Event.stop(event);
         return;
       case Event.KEY_LEFT:
       case Event.KEY_RIGHT:
         return;
       case Event.KEY_UP:
         this.markPrevious();
         this.render();
         if(Prototype.Browser.WebKit) Event.stop(event);
         return;
       case Event.KEY_DOWN:
         this.markNext();
         this.render();
         if(Prototype.Browser.WebKit) Event.stop(event);
         return;
      }
     else 
       if(event.keyCode==Event.KEY_TAB || event.keyCode==Event.KEY_RETURN || 
         (Prototype.Browser.WebKit > 0 && event.keyCode == 0)) return;

    this.changed = true;
    this.hasFocus = true;

    if(this.observer) clearTimeout(this.observer);
      this.observer = 
        setTimeout(this.onObserverEvent.bind(this), this.options.frequency*1000);
  },

  activate: function() {
    this.changed = false;
    this.hasFocus = true;
    this.getUpdatedChoices();
  },

  onHover: function(event) {
    var element = Event.findElement(event, 'LI');
    if(this.index != element.autocompleteIndex) 
    {
        this.index = element.autocompleteIndex;
        this.render();
    }
    Event.stop(event);
  },
  
  onClick: function(event) {
    var element = Event.findElement(event, 'LI');
    this.index = element.autocompleteIndex;
    this.selectEntry();
    this.hide();
  },
  
  onBlur: function(event) {
    // needed to make click events working
    setTimeout(this.hide.bind(this), 250);
    this.hasFocus = false;
    this.active = false;     
  }, 
  
  render: function() {
    if(this.entryCount > 0) {
      for (var i = 0; i < this.entryCount; i++)
        this.index==i ? 
          Element.addClassName(this.getEntry(i),"selected") : 
          Element.removeClassName(this.getEntry(i),"selected");
      if(this.hasFocus) { 
        this.show();
        this.active = true;
      }
    } else {
      this.active = false;
      this.hide();
    }
  },
  
  markPrevious: function() {
    if(this.index > 0) this.index--
      else this.index = this.entryCount-1;
    this.getEntry(this.index).scrollIntoView(true);
  },
  
  markNext: function() {
    if(this.index < this.entryCount-1) this.index++
      else this.index = 0;
    this.getEntry(this.index).scrollIntoView(false);
  },
  
  getEntry: function(index) {
    return this.update.firstChild.childNodes[index];
  },
  
  getCurrentEntry: function() {
    return this.getEntry(this.index);
  },
  
  selectEntry: function() {
    this.active = false;
    this.updateElement(this.getCurrentEntry());
  },

  updateElement: function(selectedElement) {
    if (this.options.updateElement) {
      this.options.updateElement(selectedElement);
      return;
    }
    var value = '';
    if (this.options.select) {
      var nodes = document.getElementsByClassName(this.options.select, selectedElement) || [];
      if(nodes.length>0) value = Element.collectTextNodes(nodes[0], this.options.select);
    } else
      value = Element.collectTextNodesIgnoreClass(selectedElement, 'informal');
    
    var lastTokenPos = this.findLastToken();
    if (lastTokenPos != -1) {
      var newValue = this.element.value.substr(0, lastTokenPos + 1);
      var whitespace = this.element.value.substr(lastTokenPos + 1).match(/^\s+/);
      if (whitespace)
        newValue += whitespace[0];
      this.element.value = newValue + value;
    } else {
      this.element.value = value;
    }
    this.element.focus();
    
    if (this.options.afterUpdateElement)
      this.options.afterUpdateElement(this.element, selectedElement);
  },

  updateChoices: function(choices) {
    if(!this.changed && this.hasFocus) {
      this.update.innerHTML = choices;
      Element.cleanWhitespace(this.update);
      Element.cleanWhitespace(this.update.down());

      if(this.update.firstChild && this.update.down().childNodes) {
        this.entryCount = 
          this.update.down().childNodes.length;
        for (var i = 0; i < this.entryCount; i++) {
          var entry = this.getEntry(i);
          entry.autocompleteIndex = i;
          this.addObservers(entry);
        }
      } else { 
        this.entryCount = 0;
      }

      this.stopIndicator();
      this.index = 0;
      
      if(this.entryCount==1 && this.options.autoSelect) {
        this.selectEntry();
        this.hide();
      } else {
        this.render();
      }
    }
  },

  addObservers: function(element) {
    Event.observe(element, "mouseover", this.onHover.bindAsEventListener(this));
    Event.observe(element, "click", this.onClick.bindAsEventListener(this));
  },

  onObserverEvent: function() {
    this.changed = false;   
    if(this.getToken().length>=this.options.minChars) {
      this.getUpdatedChoices();
    } else {
      this.active = false;
      this.hide();
    }
  },

  getToken: function() {
    var tokenPos = this.findLastToken();
    if (tokenPos != -1)
      var ret = this.element.value.substr(tokenPos + 1).replace(/^\s+/,'').replace(/\s+$/,'');
    else
      var ret = this.element.value;

    return /\n/.test(ret) ? '' : ret;
  },

  findLastToken: function() {
    var lastTokenPos = -1;

    for (var i=0; i<this.options.tokens.length; i++) {
      var thisTokenPos = this.element.value.lastIndexOf(this.options.tokens[i]);
      if (thisTokenPos > lastTokenPos)
        lastTokenPos = thisTokenPos;
    }
    return lastTokenPos;
  }
}

Ajax.Autocompleter = Class.create();
Object.extend(Object.extend(Ajax.Autocompleter.prototype, Autocompleter.Base.prototype), {
  initialize: function(element, update, url, options) {
    this.baseInitialize(element, update, options);
    this.options.asynchronous  = true;
    this.options.onComplete    = this.onComplete.bind(this);
    this.options.defaultParams = this.options.parameters || null;
    this.url                   = url;
  },

  getUpdatedChoices: function() {
    this.startIndicator();
    
    var entry = encodeURIComponent(this.options.paramName) + '=' + 
      encodeURIComponent(this.getToken());

    this.options.parameters = this.options.callback ?
      this.options.callback(this.element, entry) : entry;

    if(this.options.defaultParams) 
      this.options.parameters += '&' + this.options.defaultParams;
    
    new Ajax.Request(this.url, this.options);
  },

  onComplete: function(request) {
    this.updateChoices(request.responseText);
  }

});

// The local array autocompleter. Used when you'd prefer to
// inject an array of autocompletion options into the page, rather
// than sending out Ajax queries, which can be quite slow sometimes.
//
// The constructor takes four parameters. The first two are, as usual,
// the id of the monitored textbox, and id of the autocompletion menu.
// The third is the array you want to autocomplete from, and the fourth
// is the options block.
//
// Extra local autocompletion options:
// - choices - How many autocompletion choices to offer
//
// - partialSearch - If false, the autocompleter will match entered
//                    text only at the beginning of strings in the 
//                    autocomplete array. Defaults to true, which will
//                    match text at the beginning of any *word* in the
//                    strings in the autocomplete array. If you want to
//                    search anywhere in the string, additionally set
//                    the option fullSearch to true (default: off).
//
// - fullSsearch - Search anywhere in autocomplete array strings.
//
// - partialChars - How many characters to enter before triggering
//                   a partial match (unlike minChars, which defines
//                   how many characters are required to do any match
//                   at all). Defaults to 2.
//
// - ignoreCase - Whether to ignore case when autocompleting.
//                 Defaults to true.
//
// It's possible to pass in a custom function as the 'selector' 
// option, if you prefer to write your own autocompletion logic.
// In that case, the other options above will not apply unless
// you support them.

Autocompleter.Local = Class.create();
Autocompleter.Local.prototype = Object.extend(new Autocompleter.Base(), {
  initialize: function(element, update, array, options) {
    this.baseInitialize(element, update, options);
    this.options.array = array;
  },

  getUpdatedChoices: function() {
    this.updateChoices(this.options.selector(this));
  },

  setOptions: function(options) {
    this.options = Object.extend({
      choices: 10,
      partialSearch: true,
      partialChars: 2,
      ignoreCase: true,
      fullSearch: false,
      selector: function(instance) {
        var ret       = []; // Beginning matches
        var partial   = []; // Inside matches
        var entry     = instance.getToken();
        var count     = 0;

        for (var i = 0; i < instance.options.array.length &&  
          ret.length < instance.options.choices ; i++) { 

          var elem = instance.options.array[i];
          var foundPos = instance.options.ignoreCase ? 
            elem.toLowerCase().indexOf(entry.toLowerCase()) : 
            elem.indexOf(entry);

          while (foundPos != -1) {
            if (foundPos == 0 && elem.length != entry.length) { 
              ret.push("<li><strong>" + elem.substr(0, entry.length) + "</strong>" + 
                elem.substr(entry.length) + "</li>");
              break;
            } else if (entry.length >= instance.options.partialChars && 
              instance.options.partialSearch && foundPos != -1) {
              if (instance.options.fullSearch || /\s/.test(elem.substr(foundPos-1,1))) {
                partial.push("<li>" + elem.substr(0, foundPos) + "<strong>" +
                  elem.substr(foundPos, entry.length) + "</strong>" + elem.substr(
                  foundPos + entry.length) + "</li>");
                break;
              }
            }

            foundPos = instance.options.ignoreCase ? 
              elem.toLowerCase().indexOf(entry.toLowerCase(), foundPos + 1) : 
              elem.indexOf(entry, foundPos + 1);

          }
        }
        if (partial.length)
          ret = ret.concat(partial.slice(0, instance.options.choices - ret.length))
        return "<ul>" + ret.join('') + "</ul>";
      }
    }, options || {});
  }
});

// AJAX in-place editor
//
// see documentation on http://wiki.script.aculo.us/scriptaculous/show/Ajax.InPlaceEditor

// Use this if you notice weird scrolling problems on some browsers,
// the DOM might be a bit confused when this gets called so do this
// waits 1 ms (with setTimeout) until it does the activation
Field.scrollFreeActivate = function(field) {
  setTimeout(function() {
    Field.activate(field);
  }, 1);
}

Ajax.InPlaceEditor = Class.create();
Ajax.InPlaceEditor.defaultHighlightColor = "#FFFF99";
Ajax.InPlaceEditor.prototype = {
  initialize: function(element, url, options) {
    this.url = url;
    this.element = $(element);

    this.options = Object.extend({
      paramName: "value",
      okButton: true,
      okLink: false,
      okText: "ok",
      cancelButton: false,
      cancelLink: true,
      cancelText: "cancel",
      textBeforeControls: '',
      textBetweenControls: '',
      textAfterControls: '',
      savingText: "Saving...",
      clickToEditText: "Click to edit",
      okText: "ok",
      rows: 1,
      onComplete: function(transport, element) {
        new Effect.Highlight(element, {startcolor: this.options.highlightcolor});
      },
      onFailure: function(transport) {
        alert("Error communicating with the server: " + transport.responseText.stripTags());
      },
      callback: function(form) {
        return Form.serialize(form);
      },
      handleLineBreaks: true,
      loadingText: 'Loading...',
      savingClassName: 'inplaceeditor-saving',
      loadingClassName: 'inplaceeditor-loading',
      formClassName: 'inplaceeditor-form',
      highlightcolor: Ajax.InPlaceEditor.defaultHighlightColor,
      highlightendcolor: "#FFFFFF",
      externalControl: null,
      submitOnBlur: false,
      ajaxOptions: {},
      evalScripts: false
    }, options || {});

    if(!this.options.formId && this.element.id) {
      this.options.formId = this.element.id + "-inplaceeditor";
      if ($(this.options.formId)) {
        // there's already a form with that name, don't specify an id
        this.options.formId = null;
      }
    }
    
    if (this.options.externalControl) {
      this.options.externalControl = $(this.options.externalControl);
    }
    
    this.originalBackground = Element.getStyle(this.element, 'background-color');
    if (!this.originalBackground) {
      this.originalBackground = "transparent";
    }
    
    this.element.title = this.options.clickToEditText;
    
    this.onclickListener = this.enterEditMode.bindAsEventListener(this);
    this.mouseoverListener = this.enterHover.bindAsEventListener(this);
    this.mouseoutListener = this.leaveHover.bindAsEventListener(this);
    Event.observe(this.element, 'click', this.onclickListener);
    Event.observe(this.element, 'mouseover', this.mouseoverListener);
    Event.observe(this.element, 'mouseout', this.mouseoutListener);
    if (this.options.externalControl) {
      Event.observe(this.options.externalControl, 'click', this.onclickListener);
      Event.observe(this.options.externalControl, 'mouseover', this.mouseoverListener);
      Event.observe(this.options.externalControl, 'mouseout', this.mouseoutListener);
    }
  },
  enterEditMode: function(evt) {
    if (this.saving) return;
    if (this.editing) return;
    this.editing = true;
    this.onEnterEditMode();
    if (this.options.externalControl) {
      Element.hide(this.options.externalControl);
    }
    Element.hide(this.element);
    this.createForm();
    this.element.parentNode.insertBefore(this.form, this.element);
    if (!this.options.loadTextURL) Field.scrollFreeActivate(this.editField);
    // stop the event to avoid a page refresh in Safari
    if (evt) {
      Event.stop(evt);
    }
    return false;
  },
  createForm: function() {
    this.form = document.createElement("form");
    this.form.id = this.options.formId;
    Element.addClassName(this.form, this.options.formClassName)
    this.form.onsubmit = this.onSubmit.bind(this);

    this.createEditField();

    if (this.options.textarea) {
      var br = document.createElement("br");
      this.form.appendChild(br);
    }
    
    if (this.options.textBeforeControls)
      this.form.appendChild(document.createTextNode(this.options.textBeforeControls));

    if (this.options.okButton) {
      var okButton = document.createElement("input");
      okButton.type = "submit";
      okButton.value = this.options.okText;
      okButton.className = 'editor_ok_button';
      this.form.appendChild(okButton);
    }
    
    if (this.options.okLink) {
      var okLink = document.createElement("a");
      okLink.href = "#";
      okLink.appendChild(document.createTextNode(this.options.okText));
      okLink.onclick = this.onSubmit.bind(this);
      okLink.className = 'editor_ok_link';
      this.form.appendChild(okLink);
    }
    
    if (this.options.textBetweenControls && 
      (this.options.okLink || this.options.okButton) && 
      (this.options.cancelLink || this.options.cancelButton))
      this.form.appendChild(document.createTextNode(this.options.textBetweenControls));
      
    if (this.options.cancelButton) {
      var cancelButton = document.createElement("input");
      cancelButton.type = "submit";
      cancelButton.value = this.options.cancelText;
      cancelButton.onclick = this.onclickCancel.bind(this);
      cancelButton.className = 'editor_cancel_button';
      this.form.appendChild(cancelButton);
    }

    if (this.options.cancelLink) {
      var cancelLink = document.createElement("a");
      cancelLink.href = "#";
      cancelLink.appendChild(document.createTextNode(this.options.cancelText));
      cancelLink.onclick = this.onclickCancel.bind(this);
      cancelLink.className = 'editor_cancel editor_cancel_link';      
      this.form.appendChild(cancelLink);
    }
    
    if (this.options.textAfterControls)
      this.form.appendChild(document.createTextNode(this.options.textAfterControls));
  },
  hasHTMLLineBreaks: function(string) {
    if (!this.options.handleLineBreaks) return false;
    return string.match(/<br/i) || string.match(/<p>/i);
  },
  convertHTMLLineBreaks: function(string) {
    return string.replace(/<br>/gi, "\n").replace(/<br\/>/gi, "\n").replace(/<\/p>/gi, "\n").replace(/<p>/gi, "");
  },
  createEditField: function() {
    var text;
    if(this.options.loadTextURL) {
      text = this.options.loadingText;
    } else {
      text = this.getText();
    }

    var obj = this;
    
    if (this.options.rows == 1 && !this.hasHTMLLineBreaks(text)) {
      this.options.textarea = false;
      var textField = document.createElement("input");
      textField.obj = this;
      textField.type = "text";
      textField.name = this.options.paramName;
      textField.value = text;
      textField.style.backgroundColor = this.options.highlightcolor;
      textField.className = 'editor_field';
      var size = this.options.size || this.options.cols || 0;
      if (size != 0) textField.size = size;
      if (this.options.submitOnBlur)
        textField.onblur = this.onSubmit.bind(this);
      this.editField = textField;
    } else {
      this.options.textarea = true;
      var textArea = document.createElement("textarea");
      textArea.obj = this;
      textArea.name = this.options.paramName;
      textArea.value = this.convertHTMLLineBreaks(text);
      textArea.rows = this.options.rows;
      textArea.cols = this.options.cols || 40;
      textArea.className = 'editor_field';      
      if (this.options.submitOnBlur)
        textArea.onblur = this.onSubmit.bind(this);
      this.editField = textArea;
    }
    
    if(this.options.loadTextURL) {
      this.loadExternalText();
    }
    this.form.appendChild(this.editField);
  },
  getText: function() {
    return this.element.innerHTML;
  },
  loadExternalText: function() {
    Element.addClassName(this.form, this.options.loadingClassName);
    this.editField.disabled = true;
    new Ajax.Request(
      this.options.loadTextURL,
      Object.extend({
        asynchronous: true,
        onComplete: this.onLoadedExternalText.bind(this)
      }, this.options.ajaxOptions)
    );
  },
  onLoadedExternalText: function(transport) {
    Element.removeClassName(this.form, this.options.loadingClassName);
    this.editField.disabled = false;
    this.editField.value = transport.responseText.stripTags();
    Field.scrollFreeActivate(this.editField);
  },
  onclickCancel: function() {
    this.onComplete();
    this.leaveEditMode();
    return false;
  },
  onFailure: function(transport) {
    this.options.onFailure(transport);
    if (this.oldInnerHTML) {
      this.element.innerHTML = this.oldInnerHTML;
      this.oldInnerHTML = null;
    }
    return false;
  },
  onSubmit: function() {
    // onLoading resets these so we need to save them away for the Ajax call
    var form = this.form;
    var value = this.editField.value;
    
    // do this first, sometimes the ajax call returns before we get a chance to switch on Saving...
    // which means this will actually switch on Saving... *after* we've left edit mode causing Saving...
    // to be displayed indefinitely
    this.onLoading();
    
    if (this.options.evalScripts) {
      new Ajax.Request(
        this.url, Object.extend({
          parameters: this.options.callback(form, value),
          onComplete: this.onComplete.bind(this),
          onFailure: this.onFailure.bind(this),
          asynchronous:true, 
          evalScripts:true
        }, this.options.ajaxOptions));
    } else  {
      new Ajax.Updater(
        { success: this.element,
          // don't update on failure (this could be an option)
          failure: null }, 
        this.url, Object.extend({
          parameters: this.options.callback(form, value),
          onComplete: this.onComplete.bind(this),
          onFailure: this.onFailure.bind(this)
        }, this.options.ajaxOptions));
    }
    // stop the event to avoid a page refresh in Safari
    if (arguments.length > 1) {
      Event.stop(arguments[0]);
    }
    return false;
  },
  onLoading: function() {
    this.saving = true;
    this.removeForm();
    this.leaveHover();
    this.showSaving();
  },
  showSaving: function() {
    this.oldInnerHTML = this.element.innerHTML;
    this.element.innerHTML = this.options.savingText;
    Element.addClassName(this.element, this.options.savingClassName);
    this.element.style.backgroundColor = this.originalBackground;
    Element.show(this.element);
  },
  removeForm: function() {
    if(this.form) {
      if (this.form.parentNode) Element.remove(this.form);
      this.form = null;
    }
  },
  enterHover: function() {
    if (this.saving) return;
    this.element.style.backgroundColor = this.options.highlightcolor;
    if (this.effect) {
      this.effect.cancel();
    }
    Element.addClassName(this.element, this.options.hoverClassName)
  },
  leaveHover: function() {
    if (this.options.backgroundColor) {
      this.element.style.backgroundColor = this.oldBackground;
    }
    Element.removeClassName(this.element, this.options.hoverClassName)
    if (this.saving) return;
    this.effect = new Effect.Highlight(this.element, {
      startcolor: this.options.highlightcolor,
      endcolor: this.options.highlightendcolor,
      restorecolor: this.originalBackground
    });
  },
  leaveEditMode: function() {
    Element.removeClassName(this.element, this.options.savingClassName);
    this.removeForm();
    this.leaveHover();
    this.element.style.backgroundColor = this.originalBackground;
    Element.show(this.element);
    if (this.options.externalControl) {
      Element.show(this.options.externalControl);
    }
    this.editing = false;
    this.saving = false;
    this.oldInnerHTML = null;
    this.onLeaveEditMode();
  },
  onComplete: function(transport) {
    this.leaveEditMode();
    this.options.onComplete.bind(this)(transport, this.element);
  },
  onEnterEditMode: function() {},
  onLeaveEditMode: function() {},
  dispose: function() {
    if (this.oldInnerHTML) {
      this.element.innerHTML = this.oldInnerHTML;
    }
    this.leaveEditMode();
    Event.stopObserving(this.element, 'click', this.onclickListener);
    Event.stopObserving(this.element, 'mouseover', this.mouseoverListener);
    Event.stopObserving(this.element, 'mouseout', this.mouseoutListener);
    if (this.options.externalControl) {
      Event.stopObserving(this.options.externalControl, 'click', this.onclickListener);
      Event.stopObserving(this.options.externalControl, 'mouseover', this.mouseoverListener);
      Event.stopObserving(this.options.externalControl, 'mouseout', this.mouseoutListener);
    }
  }
};

Ajax.InPlaceCollectionEditor = Class.create();
Object.extend(Ajax.InPlaceCollectionEditor.prototype, Ajax.InPlaceEditor.prototype);
Object.extend(Ajax.InPlaceCollectionEditor.prototype, {
  createEditField: function() {
    if (!this.cached_selectTag) {
      var selectTag = document.createElement("select");
      var collection = this.options.collection || [];
      var optionTag;
      collection.each(function(e,i) {
        optionTag = document.createElement("option");
        optionTag.value = (e instanceof Array) ? e[0] : e;
        if((typeof this.options.value == 'undefined') && 
          ((e instanceof Array) ? this.element.innerHTML == e[1] : e == optionTag.value)) optionTag.selected = true;
        if(this.options.value==optionTag.value) optionTag.selected = true;
        optionTag.appendChild(document.createTextNode((e instanceof Array) ? e[1] : e));
        selectTag.appendChild(optionTag);
      }.bind(this));
      this.cached_selectTag = selectTag;
    }

    this.editField = this.cached_selectTag;
    if(this.options.loadTextURL) this.loadExternalText();
    this.form.appendChild(this.editField);
    this.options.callback = function(form, value) {
      return "value=" + encodeURIComponent(value);
    }
  }
});

// Delayed observer, like Form.Element.Observer, 
// but waits for delay after last key input
// Ideal for live-search fields

Form.Element.DelayedObserver = Class.create();
Form.Element.DelayedObserver.prototype = {
  initialize: function(element, delay, callback) {
    this.delay     = delay || 0.5;
    this.element   = $(element);
    this.callback  = callback;
    this.timer     = null;
    this.lastValue = $F(this.element); 
    Event.observe(this.element,'keyup',this.delayedListener.bindAsEventListener(this));
  },
  delayedListener: function(event) {
    if(this.lastValue == $F(this.element)) return;
    if(this.timer) clearTimeout(this.timer);
    this.timer = setTimeout(this.onTimerEvent.bind(this), this.delay * 1000);
    this.lastValue = $F(this.element);
  },
  onTimerEvent: function() {
    this.timer = null;
    this.callback(this.element, $F(this.element));
  }
};



/***************************************************
 * library\scriptaculous\slider.js
 ***************************************************/

// script.aculo.us slider.js v1.7.1_beta3, Fri May 25 17:19:41 +0200 2007

// Copyright (c) 2005-2007 Marty Haught, Thomas Fuchs 
//
// script.aculo.us is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/

if(!Control) var Control = {};
Control.Slider = Class.create();

// options:
//  axis: 'vertical', or 'horizontal' (default)
//
// callbacks:
//  onChange(value)
//  onSlide(value)
Control.Slider.prototype = {
  initialize: function(handle, track, options) {
    var slider = this;
    
    if(handle instanceof Array) {
      this.handles = handle.collect( function(e) { return $(e) });
    } else {
      this.handles = [$(handle)];
    }
    
    this.track   = $(track);
    this.options = options || {};

    this.axis      = this.options.axis || 'horizontal';
    this.increment = this.options.increment || 1;
    this.step      = parseInt(this.options.step || '1');
    this.range     = this.options.range || $R(0,1);
    
    this.value     = 0; // assure backwards compat
    this.values    = this.handles.map( function() { return 0 });
    this.spans     = this.options.spans ? this.options.spans.map(function(s){ return $(s) }) : false;
    this.options.startSpan = $(this.options.startSpan || null);
    this.options.endSpan   = $(this.options.endSpan || null);

    this.restricted = this.options.restricted || false;

    this.maximum   = this.options.maximum || this.range.end;
    this.minimum   = this.options.minimum || this.range.start;

    // Will be used to align the handle onto the track, if necessary
    this.alignX = parseInt(this.options.alignX || '0');
    this.alignY = parseInt(this.options.alignY || '0');
    
    this.trackLength = this.maximumOffset() - this.minimumOffset();

    this.handleLength = this.isVertical() ? 
      (this.handles[0].offsetHeight != 0 ? 
        this.handles[0].offsetHeight : this.handles[0].style.height.replace(/px$/,"")) : 
      (this.handles[0].offsetWidth != 0 ? this.handles[0].offsetWidth : 
        this.handles[0].style.width.replace(/px$/,""));

    this.active   = false;
    this.dragging = false;
    this.disabled = false;

    if(this.options.disabled) this.setDisabled();

    // Allowed values array
    this.allowedValues = this.options.values ? this.options.values.sortBy(Prototype.K) : false;
    if(this.allowedValues) {
      this.minimum = this.allowedValues.min();
      this.maximum = this.allowedValues.max();
    }

    this.eventMouseDown = this.startDrag.bindAsEventListener(this);
    this.eventMouseUp   = this.endDrag.bindAsEventListener(this);
    this.eventMouseMove = this.update.bindAsEventListener(this);

    // Initialize handles in reverse (make sure first handle is active)
    this.handles.each( function(h,i) {
      i = slider.handles.length-1-i;
      slider.setValue(parseFloat(
        (slider.options.sliderValue instanceof Array ? 
          slider.options.sliderValue[i] : slider.options.sliderValue) || 
         slider.range.start), i);
      Element.makePositioned(h); // fix IE
      Event.observe(h, "mousedown", slider.eventMouseDown);
    });
    
    Event.observe(this.track, "mousedown", this.eventMouseDown);
    Event.observe(document, "mouseup", this.eventMouseUp);
    Event.observe(document, "mousemove", this.eventMouseMove);
    
    this.initialized = true;
  },
  dispose: function() {
    var slider = this;    
    Event.stopObserving(this.track, "mousedown", this.eventMouseDown);
    Event.stopObserving(document, "mouseup", this.eventMouseUp);
    Event.stopObserving(document, "mousemove", this.eventMouseMove);
    this.handles.each( function(h) {
      Event.stopObserving(h, "mousedown", slider.eventMouseDown);
    });
  },
  setDisabled: function(){
    this.disabled = true;
  },
  setEnabled: function(){
    this.disabled = false;
  },  
  getNearestValue: function(value){
    if(this.allowedValues){
      if(value >= this.allowedValues.max()) return(this.allowedValues.max());
      if(value <= this.allowedValues.min()) return(this.allowedValues.min());
      
      var offset = Math.abs(this.allowedValues[0] - value);
      var newValue = this.allowedValues[0];
      this.allowedValues.each( function(v) {
        var currentOffset = Math.abs(v - value);
        if(currentOffset <= offset){
          newValue = v;
          offset = currentOffset;
        } 
      });
      return newValue;
    }
    if(value > this.range.end) return this.range.end;
    if(value < this.range.start) return this.range.start;
    return value;
  },
  setValue: function(sliderValue, handleIdx){
    if(!this.active) {
      this.activeHandleIdx = handleIdx || 0;
      this.activeHandle    = this.handles[this.activeHandleIdx];
      this.updateStyles();
    }
    handleIdx = handleIdx || this.activeHandleIdx || 0;
    if(this.initialized && this.restricted) {
      if((handleIdx>0) && (sliderValue<this.values[handleIdx-1]))
        sliderValue = this.values[handleIdx-1];
      if((handleIdx < (this.handles.length-1)) && (sliderValue>this.values[handleIdx+1]))
        sliderValue = this.values[handleIdx+1];
    }
    sliderValue = this.getNearestValue(sliderValue);
    this.values[handleIdx] = sliderValue;
    this.value = this.values[0]; // assure backwards compat
    
    this.handles[handleIdx].style[this.isVertical() ? 'top' : 'left'] = 
      this.translateToPx(sliderValue);
    
    this.drawSpans();
    if(!this.dragging || !this.event) this.updateFinished();
  },
  setValueBy: function(delta, handleIdx) {
    this.setValue(this.values[handleIdx || this.activeHandleIdx || 0] + delta, 
      handleIdx || this.activeHandleIdx || 0);
  },
  translateToPx: function(value) {
    return Math.round(
      ((this.trackLength-this.handleLength)/(this.range.end-this.range.start)) * 
      (value - this.range.start)) + "px";
  },
  translateToValue: function(offset) {
    return ((offset/(this.trackLength-this.handleLength) * 
      (this.range.end-this.range.start)) + this.range.start);
  },
  getRange: function(range) {
    var v = this.values.sortBy(Prototype.K); 
    range = range || 0;
    return $R(v[range],v[range+1]);
  },
  minimumOffset: function(){
    return(this.isVertical() ? this.alignY : this.alignX);
  },
  maximumOffset: function(){
    return(this.isVertical() ? 
      (this.track.offsetHeight != 0 ? this.track.offsetHeight :
        this.track.style.height.replace(/px$/,"")) - this.alignY : 
      (this.track.offsetWidth != 0 ? this.track.offsetWidth : 
        this.track.style.width.replace(/px$/,"")) - this.alignY);
  },  
  isVertical:  function(){
    return (this.axis == 'vertical');
  },
  drawSpans: function() {
    var slider = this;
    if(this.spans)
      $R(0, this.spans.length-1).each(function(r) { slider.setSpan(slider.spans[r], slider.getRange(r)) });
    if(this.options.startSpan)
      this.setSpan(this.options.startSpan,
        $R(0, this.values.length>1 ? this.getRange(0).min() : this.value ));
    if(this.options.endSpan)
      this.setSpan(this.options.endSpan, 
        $R(this.values.length>1 ? this.getRange(this.spans.length-1).max() : this.value, this.maximum));
  },
  setSpan: function(span, range) {
    if(this.isVertical()) {
      span.style.top = this.translateToPx(range.start);
      span.style.height = this.translateToPx(range.end - range.start + this.range.start);
    } else {
      span.style.left = this.translateToPx(range.start);
      span.style.width = this.translateToPx(range.end - range.start + this.range.start);
    }
  },
  updateStyles: function() {
    this.handles.each( function(h){ Element.removeClassName(h, 'selected') });
    Element.addClassName(this.activeHandle, 'selected');
  },
  startDrag: function(event) {
    if(Event.isLeftClick(event)) {
      if(!this.disabled){
        this.active = true;
        
        var handle = Event.element(event);
        var pointer  = [Event.pointerX(event), Event.pointerY(event)];
        var track = handle;
        if(track==this.track) {
          var offsets  = Position.cumulativeOffset(this.track); 
          this.event = event;
          this.setValue(this.translateToValue( 
           (this.isVertical() ? pointer[1]-offsets[1] : pointer[0]-offsets[0])-(this.handleLength/2)
          ));
          var offsets  = Position.cumulativeOffset(this.activeHandle);
          this.offsetX = (pointer[0] - offsets[0]);
          this.offsetY = (pointer[1] - offsets[1]);
        } else {
          // find the handle (prevents issues with Safari)
          while((this.handles.indexOf(handle) == -1) && handle.parentNode) 
            handle = handle.parentNode;
            
          if(this.handles.indexOf(handle)!=-1) {
            this.activeHandle    = handle;
            this.activeHandleIdx = this.handles.indexOf(this.activeHandle);
            this.updateStyles();
            
            var offsets  = Position.cumulativeOffset(this.activeHandle);
            this.offsetX = (pointer[0] - offsets[0]);
            this.offsetY = (pointer[1] - offsets[1]);
          }
        }
      }
      Event.stop(event);
    }
  },
  update: function(event) {
   if(this.active) {
      if(!this.dragging) this.dragging = true;
      this.draw(event);
      if(Prototype.Browser.WebKit) window.scrollBy(0,0);
      Event.stop(event);
   }
  },
  draw: function(event) {
    var pointer = [Event.pointerX(event), Event.pointerY(event)];
    var offsets = Position.cumulativeOffset(this.track);
    pointer[0] -= this.offsetX + offsets[0];
    pointer[1] -= this.offsetY + offsets[1];
    this.event = event;
    this.setValue(this.translateToValue( this.isVertical() ? pointer[1] : pointer[0] ));
    if(this.initialized && this.options.onSlide)
      this.options.onSlide(this.values.length>1 ? this.values : this.value, this);
  },
  endDrag: function(event) {
    if(this.active && this.dragging) {
      this.finishDrag(event, true);
      Event.stop(event);
    }
    this.active = false;
    this.dragging = false;
  },  
  finishDrag: function(event, success) {
    this.active = false;
    this.dragging = false;
    this.updateFinished();
  },
  updateFinished: function() {
    if(this.initialized && this.options.onChange) 
      this.options.onChange(this.values.length>1 ? this.values : this.value, this);
    this.event = null;
  }
}


/***************************************************
 * library\scriptaculous\dragdrop.js
 ***************************************************/

// script.aculo.us dragdrop.js v1.7.1_beta3, Fri May 25 17:19:41 +0200 2007

// Copyright (c) 2005-2007 Thomas Fuchs (http://script.aculo.us, http://mir.aculo.us)
//           (c) 2005-2007 Sammi Williams (http://www.oriontransfer.co.nz, sammi@oriontransfer.co.nz)
// 
// script.aculo.us is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/

if(typeof Effect == 'undefined')
  throw("dragdrop.js requires including script.aculo.us' effects.js library");

var Droppables = {
  drops: [],

  remove: function(element) {
    this.drops = this.drops.reject(function(d) { return d.element==$(element) });
  },

  add: function(element) {
    element = $(element);
    var options = Object.extend({
      greedy:     true,
      hoverclass: null,
      tree:       false
    }, arguments[1] || {});

    // cache containers
    if(options.containment) {
      options._containers = [];
      var containment = options.containment;
      if((typeof containment == 'object') && 
        (containment.constructor == Array)) {
        containment.each( function(c) { options._containers.push($(c)) });
      } else {
        options._containers.push($(containment));
      }
    }
    
    if(options.accept) options.accept = [options.accept].flatten();

    Element.makePositioned(element); // fix IE
    options.element = element;

    this.drops.push(options);
  },
  
  findDeepestChild: function(drops) {
    deepest = drops[0];
      
    for (i = 1; i < drops.length; ++i)
      if (Element.isParent(drops[i].element, deepest.element))
        deepest = drops[i];
    
    return deepest;
  },

  isContained: function(element, drop) {
    var containmentNode;
    if(drop.tree) {
      containmentNode = element.treeNode; 
    } else {
      containmentNode = element.parentNode;
    }
    return drop._containers.detect(function(c) { return containmentNode == c });
  },
  
  isAffected: function(point, element, drop) {
    return (
      (drop.element!=element) &&
      ((!drop._containers) ||
        this.isContained(element, drop)) &&
      ((!drop.accept) ||
        (Element.classNames(element).detect( 
          function(v) { return drop.accept.include(v) } ) )) &&
      Position.within(drop.element, point[0], point[1]) );
  },

  deactivate: function(drop) {
    if(drop.hoverclass)
      Element.removeClassName(drop.element, drop.hoverclass);
    this.last_active = null;
  },

  activate: function(drop) {
    if(drop.hoverclass)
      Element.addClassName(drop.element, drop.hoverclass);
    this.last_active = drop;
  },

  show: function(point, element) {
    if(!this.drops.length) return;
    var affected = [];
    
    if(this.last_active) this.deactivate(this.last_active);
    this.drops.each( function(drop) {
      if(Droppables.isAffected(point, element, drop))
        affected.push(drop);
    });
        
    if(affected.length>0) {
      drop = Droppables.findDeepestChild(affected);
      Position.within(drop.element, point[0], point[1]);
      if(drop.onHover)
        drop.onHover(element, drop.element, Position.overlap(drop.overlap, drop.element));
      
      Droppables.activate(drop);
    }
  },

  fire: function(event, element) {
    if(!this.last_active) return;
    Position.prepare();

    if (this.isAffected([Event.pointerX(event), Event.pointerY(event)], element, this.last_active))
      if (this.last_active.onDrop) {
        this.last_active.onDrop(element, this.last_active.element, event); 
        return true; 
      }
  },

  reset: function() {
    if(this.last_active)
      this.deactivate(this.last_active);
  }
}

var Draggables = {
  drags: [],
  observers: [],
  
  register: function(draggable) {
    if(this.drags.length == 0) {
      this.eventMouseUp   = this.endDrag.bindAsEventListener(this);
      this.eventMouseMove = this.updateDrag.bindAsEventListener(this);
      this.eventKeypress  = this.keyPress.bindAsEventListener(this);
      
      Event.observe(document, "mouseup", this.eventMouseUp);
      Event.observe(document, "mousemove", this.eventMouseMove);
      Event.observe(document, "keypress", this.eventKeypress);
    }
    this.drags.push(draggable);
  },
  
  unregister: function(draggable) {
    this.drags = this.drags.reject(function(d) { return d==draggable });
    if(this.drags.length == 0) {
      Event.stopObserving(document, "mouseup", this.eventMouseUp);
      Event.stopObserving(document, "mousemove", this.eventMouseMove);
      Event.stopObserving(document, "keypress", this.eventKeypress);
    }
  },
  
  activate: function(draggable) {
    if(draggable.options.delay) { 
      this._timeout = setTimeout(function() { 
        Draggables._timeout = null; 
        window.focus(); 
        Draggables.activeDraggable = draggable; 
      }.bind(this), draggable.options.delay); 
    } else {
      window.focus(); // allows keypress events if window isn't currently focused, fails for Safari
      this.activeDraggable = draggable;
    }
  },
  
  deactivate: function() {
    this.activeDraggable = null;
  },
  
  updateDrag: function(event) {
    if(!this.activeDraggable) return;
    var pointer = [Event.pointerX(event), Event.pointerY(event)];
    // Mozilla-based browsers fire successive mousemove events with
    // the same coordinates, prevent needless redrawing (moz bug?)
    if(this._lastPointer && (this._lastPointer.inspect() == pointer.inspect())) return;
    this._lastPointer = pointer;
    
    this.activeDraggable.updateDrag(event, pointer);
  },
  
  endDrag: function(event) {
    if(this._timeout) { 
      clearTimeout(this._timeout); 
      this._timeout = null; 
    }
    if(!this.activeDraggable) return;
    this._lastPointer = null;
    this.activeDraggable.endDrag(event);
    this.activeDraggable = null;
  },
  
  keyPress: function(event) {
    if(this.activeDraggable)
      this.activeDraggable.keyPress(event);
  },
  
  addObserver: function(observer) {
    this.observers.push(observer);
    this._cacheObserverCallbacks();
  },
  
  removeObserver: function(element) {  // element instead of observer fixes mem leaks
    this.observers = this.observers.reject( function(o) { return o.element==element });
    this._cacheObserverCallbacks();
  },
  
  notify: function(eventName, draggable, event) {  // 'onStart', 'onEnd', 'onDrag'
    if(this[eventName+'Count'] > 0)
      this.observers.each( function(o) {
        if(o[eventName]) o[eventName](eventName, draggable, event);
      });
    if(draggable.options[eventName]) draggable.options[eventName](draggable, event);
  },
  
  _cacheObserverCallbacks: function() {
    ['onStart','onEnd','onDrag'].each( function(eventName) {
      Draggables[eventName+'Count'] = Draggables.observers.select(
        function(o) { return o[eventName]; }
      ).length;
    });
  }
}

/*--------------------------------------------------------------------------*/

var Draggable = Class.create();
Draggable._dragging    = {};

Draggable.prototype = {
  initialize: function(element) {
    var defaults = {
      handle: false,
      reverteffect: function(element, top_offset, left_offset) {
        var dur = Math.sqrt(Math.abs(top_offset^2)+Math.abs(left_offset^2))*0.02;
        new Effect.Move(element, { x: -left_offset, y: -top_offset, duration: dur,
          queue: {scope:'_draggable', position:'end'}
        });
      },
      endeffect: function(element) {
        var toOpacity = typeof element._opacity == 'number' ? element._opacity : 1.0;
        new Effect.Opacity(element, {duration:0.2, from:0.7, to:toOpacity, 
          queue: {scope:'_draggable', position:'end'},
          afterFinish: function(){ 
            Draggable._dragging[element] = false 
          }
        }); 
      },
      zindex: 1000,
      revert: false,
      quiet: false,
      scroll: false,
      scrollSensitivity: 20,
      scrollSpeed: 15,
      snap: false,  // false, or xy or [x,y] or function(x,y){ return [x,y] }
      delay: 0
    };
    
    if(!arguments[1] || typeof arguments[1].endeffect == 'undefined')
      Object.extend(defaults, {
        starteffect: function(element) {
          element._opacity = Element.getOpacity(element);
          Draggable._dragging[element] = true;
          new Effect.Opacity(element, {duration:0.2, from:element._opacity, to:0.7}); 
        }
      });
    
    var options = Object.extend(defaults, arguments[1] || {});

    this.element = $(element);
    
    if(options.handle && (typeof options.handle == 'string'))
      this.handle = this.element.down('.'+options.handle, 0);
    
    if(!this.handle) this.handle = $(options.handle);
    if(!this.handle) this.handle = this.element;
    
    if(options.scroll && !options.scroll.scrollTo && !options.scroll.outerHTML) {
      options.scroll = $(options.scroll);
      this._isScrollChild = Element.childOf(this.element, options.scroll);
    }

    Element.makePositioned(this.element); // fix IE    

    this.delta    = this.currentDelta();
    this.options  = options;
    this.dragging = false;   

    this.eventMouseDown = this.initDrag.bindAsEventListener(this);
    Event.observe(this.handle, "mousedown", this.eventMouseDown);
    
    Draggables.register(this);
  },
  
  destroy: function() {
    Event.stopObserving(this.handle, "mousedown", this.eventMouseDown);
    Draggables.unregister(this);
  },
  
  currentDelta: function() {
    return([
      parseInt(Element.getStyle(this.element,'left') || '0'),
      parseInt(Element.getStyle(this.element,'top') || '0')]);
  },
  
  initDrag: function(event) {
    if(typeof Draggable._dragging[this.element] != 'undefined' &&
      Draggable._dragging[this.element]) return;
    if(Event.isLeftClick(event)) {    
      // abort on form elements, fixes a Firefox issue
      var src = Event.element(event);
      if((tag_name = src.tagName.toUpperCase()) && (
        tag_name=='INPUT' ||
        tag_name=='SELECT' ||
        tag_name=='OPTION' ||
        tag_name=='BUTTON' ||
        tag_name=='TEXTAREA')) return;
        
      var pointer = [Event.pointerX(event), Event.pointerY(event)];
      var pos     = Position.cumulativeOffset(this.element);
      this.offset = [0,1].map( function(i) { return (pointer[i] - pos[i]) });
      
      Draggables.activate(this);
      Event.stop(event);
    }
  },
  
  startDrag: function(event) {
    this.dragging = true;
    
    if(this.options.zindex) {
      this.originalZ = parseInt(Element.getStyle(this.element,'z-index') || 0);
      this.element.style.zIndex = this.options.zindex;
    }
    
    if(this.options.ghosting) {
      this._clone = this.element.cloneNode(true);
      Position.absolutize(this.element);
      this.element.parentNode.insertBefore(this._clone, this.element);
    }
    
    if(this.options.scroll) {
      if (this.options.scroll == window) {
        var where = this._getWindowScroll(this.options.scroll);
        this.originalScrollLeft = where.left;
        this.originalScrollTop = where.top;
      } else {
        this.originalScrollLeft = this.options.scroll.scrollLeft;
        this.originalScrollTop = this.options.scroll.scrollTop;
      }
    }
    
    Draggables.notify('onStart', this, event);
        
    if(this.options.starteffect) this.options.starteffect(this.element);
  },
  
  updateDrag: function(event, pointer) {
    if(!this.dragging) this.startDrag(event);
    
    if(!this.options.quiet){
      Position.prepare();
      Droppables.show(pointer, this.element);
    }
    
    Draggables.notify('onDrag', this, event);
    
    this.draw(pointer);
    if(this.options.change) this.options.change(this);
    
    if(this.options.scroll) {
      this.stopScrolling();
      
      var p;
      if (this.options.scroll == window) {
        with(this._getWindowScroll(this.options.scroll)) { p = [ left, top, left+width, top+height ]; }
      } else {
        p = Position.page(this.options.scroll);
        p[0] += this.options.scroll.scrollLeft + Position.deltaX;
        p[1] += this.options.scroll.scrollTop + Position.deltaY;
        p.push(p[0]+this.options.scroll.offsetWidth);
        p.push(p[1]+this.options.scroll.offsetHeight);
      }
      var speed = [0,0];
      if(pointer[0] < (p[0]+this.options.scrollSensitivity)) speed[0] = pointer[0]-(p[0]+this.options.scrollSensitivity);
      if(pointer[1] < (p[1]+this.options.scrollSensitivity)) speed[1] = pointer[1]-(p[1]+this.options.scrollSensitivity);
      if(pointer[0] > (p[2]-this.options.scrollSensitivity)) speed[0] = pointer[0]-(p[2]-this.options.scrollSensitivity);
      if(pointer[1] > (p[3]-this.options.scrollSensitivity)) speed[1] = pointer[1]-(p[3]-this.options.scrollSensitivity);
      this.startScrolling(speed);
    }
    
    // fix AppleWebKit rendering
    if(Prototype.Browser.WebKit) window.scrollBy(0,0);
    
    Event.stop(event);
  },
  
  finishDrag: function(event, success) {
    this.dragging = false;
    
    if(this.options.quiet){
      Position.prepare();
      var pointer = [Event.pointerX(event), Event.pointerY(event)];
      Droppables.show(pointer, this.element);
    }

    if(this.options.ghosting) {
      Position.relativize(this.element);
      Element.remove(this._clone);
      this._clone = null;
    }

    var dropped = false; 
    if(success) { 
      dropped = Droppables.fire(event, this.element); 
      if (!dropped) dropped = false; 
    }
    if(dropped && this.options.onDropped) this.options.onDropped(this.element);
    Draggables.notify('onEnd', this, event);

    var revert = this.options.revert;
    if(revert && typeof revert == 'function') revert = revert(this.element);
    
    var d = this.currentDelta();
    if(revert && this.options.reverteffect) {
      if (dropped == 0 || revert != 'failure')
        this.options.reverteffect(this.element,
          d[1]-this.delta[1], d[0]-this.delta[0]);
    } else {
      this.delta = d;
    }

    if(this.options.zindex)
      this.element.style.zIndex = this.originalZ;

    if(this.options.endeffect) 
      this.options.endeffect(this.element);
      
    Draggables.deactivate(this);
    Droppables.reset();
  },
  
  keyPress: function(event) {
    if(event.keyCode!=Event.KEY_ESC) return;
    this.finishDrag(event, false);
    Event.stop(event);
  },
  
  endDrag: function(event) {
    if(!this.dragging) return;
    this.stopScrolling();
    this.finishDrag(event, true);
    Event.stop(event);
  },
  
  draw: function(point) {
    var pos = Position.cumulativeOffset(this.element);
    if(this.options.ghosting) {
      var r   = Position.realOffset(this.element);
      pos[0] += r[0] - Position.deltaX; pos[1] += r[1] - Position.deltaY;
    }
    
    var d = this.currentDelta();
    pos[0] -= d[0]; pos[1] -= d[1];
    
    if(this.options.scroll && (this.options.scroll != window && this._isScrollChild)) {
      pos[0] -= this.options.scroll.scrollLeft-this.originalScrollLeft;
      pos[1] -= this.options.scroll.scrollTop-this.originalScrollTop;
    }
    
    var p = [0,1].map(function(i){ 
      return (point[i]-pos[i]-this.offset[i]) 
    }.bind(this));
    
    if(this.options.snap) {
      if(typeof this.options.snap == 'function') {
        p = this.options.snap(p[0],p[1],this);
      } else {
      if(this.options.snap instanceof Array) {
        p = p.map( function(v, i) {
          return Math.round(v/this.options.snap[i])*this.options.snap[i] }.bind(this))
      } else {
        p = p.map( function(v) {
          return Math.round(v/this.options.snap)*this.options.snap }.bind(this))
      }
    }}
    
    var style = this.element.style;
    if((!this.options.constraint) || (this.options.constraint=='horizontal'))
      style.left = p[0] + "px";
    if((!this.options.constraint) || (this.options.constraint=='vertical'))
      style.top  = p[1] + "px";
    
    if(style.visibility=="hidden") style.visibility = ""; // fix gecko rendering
  },
  
  stopScrolling: function() {
    if(this.scrollInterval) {
      clearInterval(this.scrollInterval);
      this.scrollInterval = null;
      Draggables._lastScrollPointer = null;
    }
  },
  
  startScrolling: function(speed) {
    if(!(speed[0] || speed[1])) return;
    this.scrollSpeed = [speed[0]*this.options.scrollSpeed,speed[1]*this.options.scrollSpeed];
    this.lastScrolled = new Date();
    this.scrollInterval = setInterval(this.scroll.bind(this), 10);
  },
  
  scroll: function() {
    var current = new Date();
    var delta = current - this.lastScrolled;
    this.lastScrolled = current;
    if(this.options.scroll == window) {
      with (this._getWindowScroll(this.options.scroll)) {
        if (this.scrollSpeed[0] || this.scrollSpeed[1]) {
          var d = delta / 1000;
          this.options.scroll.scrollTo( left + d*this.scrollSpeed[0], top + d*this.scrollSpeed[1] );
        }
      }
    } else {
      this.options.scroll.scrollLeft += this.scrollSpeed[0] * delta / 1000;
      this.options.scroll.scrollTop  += this.scrollSpeed[1] * delta / 1000;
    }
    
    Position.prepare();
    Droppables.show(Draggables._lastPointer, this.element);
    Draggables.notify('onDrag', this);
    if (this._isScrollChild) {
      Draggables._lastScrollPointer = Draggables._lastScrollPointer || $A(Draggables._lastPointer);
      Draggables._lastScrollPointer[0] += this.scrollSpeed[0] * delta / 1000;
      Draggables._lastScrollPointer[1] += this.scrollSpeed[1] * delta / 1000;
      if (Draggables._lastScrollPointer[0] < 0)
        Draggables._lastScrollPointer[0] = 0;
      if (Draggables._lastScrollPointer[1] < 0)
        Draggables._lastScrollPointer[1] = 0;
      this.draw(Draggables._lastScrollPointer);
    }
    
    if(this.options.change) this.options.change(this);
  },
  
  _getWindowScroll: function(w) {
    var T, L, W, H;
    with (w.document) {
      if (w.document.documentElement && documentElement.scrollTop) {
        T = documentElement.scrollTop;
        L = documentElement.scrollLeft;
      } else if (w.document.body) {
        T = body.scrollTop;
        L = body.scrollLeft;
      }
      if (w.innerWidth) {
        W = w.innerWidth;
        H = w.innerHeight;
      } else if (w.document.documentElement && documentElement.clientWidth) {
        W = documentElement.clientWidth;
        H = documentElement.clientHeight;
      } else {
        W = body.offsetWidth;
        H = body.offsetHeight
      }
    }
    return { top: T, left: L, width: W, height: H };
  }
}

/*--------------------------------------------------------------------------*/

var SortableObserver = Class.create();
SortableObserver.prototype = {
  initialize: function(element, observer) {
    this.element   = $(element);
    this.observer  = observer;
    this.lastValue = Sortable.serialize(this.element);
  },
  
  onStart: function() {
    this.lastValue = Sortable.serialize(this.element);
  },
  
  onEnd: function() {
    Sortable.unmark();
    if(this.lastValue != Sortable.serialize(this.element))
      this.observer(this.element)
  }
}

var Sortable = {
  SERIALIZE_RULE: /^[^_\-](?:[A-Za-z0-9\-\_]*)[_](.*)$/,
  
  sortables: {},
  
  _findRootElement: function(element) {
    while (element.tagName.toUpperCase() != "BODY") {  
      if(element.id && Sortable.sortables[element.id]) return element;
      element = element.parentNode;
    }
  },

  options: function(element) {
    element = Sortable._findRootElement($(element));
    if(!element) return;
    return Sortable.sortables[element.id];
  },
  
  destroy: function(element){
    var s = Sortable.options(element);
    
    if(s) {
      Draggables.removeObserver(s.element);
      s.droppables.each(function(d){ Droppables.remove(d) });
      s.draggables.invoke('destroy');
      
      delete Sortable.sortables[s.element.id];
    }
  },

  create: function(element) {
    element = $(element);
    var options = Object.extend({ 
      element:     element,
      tag:         'li',       // assumes li children, override with tag: 'tagname'
      dropOnEmpty: false,
      tree:        false,
      treeTag:     'ul',
      overlap:     'vertical', // one of 'vertical', 'horizontal'
      constraint:  'vertical', // one of 'vertical', 'horizontal', false
      containment: element,    // also takes array of elements (or id's); or false
      handle:      false,      // or a CSS class
      only:        false,
      delay:       0,
      hoverclass:  null,
      ghosting:    false,
      quiet:       false, 
      scroll:      false,
      scrollSensitivity: 20,
      scrollSpeed: 15,
      format:      this.SERIALIZE_RULE,
      
      // these take arrays of elements or ids and can be 
      // used for better initialization performance
      elements:    false,
      handles:     false,
      
      onChange:    Prototype.emptyFunction,
      onUpdate:    Prototype.emptyFunction
    }, arguments[1] || {});

    // clear any old sortable with same element
    this.destroy(element);

    // build options for the draggables
    var options_for_draggable = {
      revert:      true,
      quiet:       options.quiet,
      scroll:      options.scroll,
      scrollSpeed: options.scrollSpeed,
      scrollSensitivity: options.scrollSensitivity,
      delay:       options.delay,
      ghosting:    options.ghosting,
      constraint:  options.constraint,
      handle:      options.handle };

    if(options.starteffect)
      options_for_draggable.starteffect = options.starteffect;

    if(options.reverteffect)
      options_for_draggable.reverteffect = options.reverteffect;
    else
      if(options.ghosting) options_for_draggable.reverteffect = function(element) {
        element.style.top  = 0;
        element.style.left = 0;
      };

    if(options.endeffect)
      options_for_draggable.endeffect = options.endeffect;

    if(options.zindex)
      options_for_draggable.zindex = options.zindex;

    // build options for the droppables  
    var options_for_droppable = {
      overlap:     options.overlap,
      containment: options.containment,
      tree:        options.tree,
      hoverclass:  options.hoverclass,
      onHover:     Sortable.onHover
    }
    
    var options_for_tree = {
      onHover:      Sortable.onEmptyHover,
      overlap:      options.overlap,
      containment:  options.containment,
      hoverclass:   options.hoverclass
    }

    // fix for gecko engine
    Element.cleanWhitespace(element); 

    options.draggables = [];
    options.droppables = [];

    // drop on empty handling
    if(options.dropOnEmpty || options.tree) {
      Droppables.add(element, options_for_tree);
      options.droppables.push(element);
    }

    (options.elements || this.findElements(element, options) || []).each( function(e,i) {
      var handle = options.handles ? $(options.handles[i]) :
        (options.handle ? $(e).getElementsByClassName(options.handle)[0] : e); 
      options.draggables.push(
        new Draggable(e, Object.extend(options_for_draggable, { handle: handle })));
      Droppables.add(e, options_for_droppable);
      if(options.tree) e.treeNode = element;
      options.droppables.push(e);      
    });
    
    if(options.tree) {
      (Sortable.findTreeElements(element, options) || []).each( function(e) {
        Droppables.add(e, options_for_tree);
        e.treeNode = element;
        options.droppables.push(e);
      });
    }

    // keep reference
    this.sortables[element.id] = options;

    // for onupdate
    Draggables.addObserver(new SortableObserver(element, options.onUpdate));

  },

  // return all suitable-for-sortable elements in a guaranteed order
  findElements: function(element, options) {
    return Element.findChildren(
      element, options.only, options.tree ? true : false, options.tag);
  },
  
  findTreeElements: function(element, options) {
    return Element.findChildren(
      element, options.only, options.tree ? true : false, options.treeTag);
  },

  onHover: function(element, dropon, overlap) {
    if(Element.isParent(dropon, element)) return;

    if(overlap > .33 && overlap < .66 && Sortable.options(dropon).tree) {
      return;
    } else if(overlap>0.5) {
      Sortable.mark(dropon, 'before');
      if(dropon.previousSibling != element) {
        var oldParentNode = element.parentNode;
        element.style.visibility = "hidden"; // fix gecko rendering
        dropon.parentNode.insertBefore(element, dropon);
        if(dropon.parentNode!=oldParentNode) 
          Sortable.options(oldParentNode).onChange(element);
        Sortable.options(dropon.parentNode).onChange(element);
      }
    } else {
      Sortable.mark(dropon, 'after');
      var nextElement = dropon.nextSibling || null;
      if(nextElement != element) {
        var oldParentNode = element.parentNode;
        element.style.visibility = "hidden"; // fix gecko rendering
        dropon.parentNode.insertBefore(element, nextElement);
        if(dropon.parentNode!=oldParentNode) 
          Sortable.options(oldParentNode).onChange(element);
        Sortable.options(dropon.parentNode).onChange(element);
      }
    }
  },
  
  onEmptyHover: function(element, dropon, overlap) {
    var oldParentNode = element.parentNode;
    var droponOptions = Sortable.options(dropon);
        
    if(!Element.isParent(dropon, element)) {
      var index;
      
      var children = Sortable.findElements(dropon, {tag: droponOptions.tag, only: droponOptions.only});
      var child = null;
            
      if(children) {
        var offset = Element.offsetSize(dropon, droponOptions.overlap) * (1.0 - overlap);
        
        for (index = 0; index < children.length; index += 1) {
          if (offset - Element.offsetSize (children[index], droponOptions.overlap) >= 0) {
            offset -= Element.offsetSize (children[index], droponOptions.overlap);
          } else if (offset - (Element.offsetSize (children[index], droponOptions.overlap) / 2) >= 0) {
            child = index + 1 < children.length ? children[index + 1] : null;
            break;
          } else {
            child = children[index];
            break;
          }
        }
      }
      
      dropon.insertBefore(element, child);
      
      Sortable.options(oldParentNode).onChange(element);
      droponOptions.onChange(element);
    }
  },

  unmark: function() {
    if(Sortable._marker) Sortable._marker.hide();
  },

  mark: function(dropon, position) {
    // mark on ghosting only
    var sortable = Sortable.options(dropon.parentNode);
    if(sortable && !sortable.ghosting) return; 

    if(!Sortable._marker) {
      Sortable._marker = 
        ($('dropmarker') || Element.extend(document.createElement('DIV'))).
          hide().addClassName('dropmarker').setStyle({position:'absolute'});
      document.getElementsByTagName("body").item(0).appendChild(Sortable._marker);
    }    
    var offsets = Position.cumulativeOffset(dropon);
    Sortable._marker.setStyle({left: offsets[0]+'px', top: offsets[1] + 'px'});
    
    if(position=='after')
      if(sortable.overlap == 'horizontal') 
        Sortable._marker.setStyle({left: (offsets[0]+dropon.clientWidth) + 'px'});
      else
        Sortable._marker.setStyle({top: (offsets[1]+dropon.clientHeight) + 'px'});
    
    Sortable._marker.show();
  },
  
  _tree: function(element, options, parent) {
    var children = Sortable.findElements(element, options) || [];
  
    for (var i = 0; i < children.length; ++i) {
      var match = children[i].id.match(options.format);

      if (!match) continue;
      
      var child = {
        id: encodeURIComponent(match ? match[1] : null),
        element: element,
        parent: parent,
        children: [],
        position: parent.children.length,
        container: $(children[i]).down(options.treeTag)
      }
      
      /* Get the element containing the children and recurse over it */
      if (child.container)
        this._tree(child.container, options, child)
      
      parent.children.push (child);
    }

    return parent; 
  },

  tree: function(element) {
    element = $(element);
    var sortableOptions = this.options(element);
    var options = Object.extend({
      tag: sortableOptions.tag,
      treeTag: sortableOptions.treeTag,
      only: sortableOptions.only,
      name: element.id,
      format: sortableOptions.format
    }, arguments[1] || {});
    
    var root = {
      id: null,
      parent: null,
      children: [],
      container: element,
      position: 0
    }
    
    return Sortable._tree(element, options, root);
  },

  /* Construct a [i] index for a particular node */
  _constructIndex: function(node) {
    var index = '';
    do {
      if (node.id) index = '[' + node.position + ']' + index;
    } while ((node = node.parent) != null);
    return index;
  },

  sequence: function(element) {
    element = $(element);
    var options = Object.extend(this.options(element), arguments[1] || {});
    
    return $(this.findElements(element, options) || []).map( function(item) {
      return item.id.match(options.format) ? item.id.match(options.format)[1] : '';
    });
  },

  setSequence: function(element, new_sequence) {
    element = $(element);
    var options = Object.extend(this.options(element), arguments[2] || {});
    
    var nodeMap = {};
    this.findElements(element, options).each( function(n) {
        if (n.id.match(options.format))
            nodeMap[n.id.match(options.format)[1]] = [n, n.parentNode];
        n.parentNode.removeChild(n);
    });
   
    new_sequence.each(function(ident) {
      var n = nodeMap[ident];
      if (n) {
        n[1].appendChild(n[0]);
        delete nodeMap[ident];
      }
    });
  },
  
  serialize: function(element) {
    element = $(element);
    var options = Object.extend(Sortable.options(element), arguments[1] || {});
    var name = encodeURIComponent(
      (arguments[1] && arguments[1].name) ? arguments[1].name : element.id);
    
    if (options.tree) {
      return Sortable.tree(element, arguments[1]).children.map( function (item) {
        return [name + Sortable._constructIndex(item) + "[id]=" + 
                encodeURIComponent(item.id)].concat(item.children.map(arguments.callee));
      }).flatten().join('&');
    } else {
      return Sortable.sequence(element, arguments[1]).map( function(item) {
        return name + "[]=" + encodeURIComponent(item);
      }).join('&');
    }
  }
}

// Returns true if child is contained within element
Element.isParent = function(child, element) {
  if (!child.parentNode || child == element) return false;
  if (child.parentNode == element) return true;
  return Element.isParent(child.parentNode, element);
}

Element.findChildren = function(element, only, recursive, tagName) {   
  if(!element.hasChildNodes()) return null;
  tagName = tagName.toUpperCase();
  if(only) only = [only].flatten();
  var elements = [];
  $A(element.childNodes).each( function(e) {
    if(e.tagName && e.tagName.toUpperCase()==tagName &&
      (!only || (Element.classNames(e).detect(function(v) { return only.include(v) }))))
        elements.push(e);
    if(recursive) {
      var grandchildren = Element.findChildren(e, only, recursive, tagName);
      if(grandchildren) elements.push(grandchildren);
    }
  });

  return (elements.length>0 ? elements.flatten() : []);
}

Element.offsetSize = function (element, type) {
  return element['offset' + ((type=='vertical' || type=='height') ? 'Height' : 'Width')];
}



/***************************************************
 * backend\Backend.js
 ***************************************************/

function rescape(str) 
{ 
    return srt.replace(/([\/()[\]{}|*+-.,^$?\\])/g, "\\$1"); 
}

function showHelp(url)
{
  	return window.open(url, 'helpWin', 'width=400, height=700, resizable, scrollbars, location=no');
}

var Backend = {};

// set default locale
Backend.locale = 'en';

    
Backend.openedContainersStack = [];
Backend.showContainer = function(containerID)
{
    if(Backend.openedContainersStack.length == 0)
    {
        Backend.openedContainersStack[0] = containerID;
    } 
    else if(Backend.openedContainersStack[Backend.openedContainersStack.length - 1] != containerID)
    {
        Backend.openedContainersStack[Backend.openedContainersStack.length] = containerID;
        $(Backend.openedContainersStack[Backend.openedContainersStack.length - 2]).hide();
    }
    
    $(Backend.openedContainersStack[Backend.openedContainersStack.length - 1]).show();
}

Backend.hideContainer = function()
{       
    if(Backend.openedContainersStack.length  > 0) $(Backend.openedContainersStack[Backend.openedContainersStack.length - 1]).hide();
    Backend.openedContainersStack.splice(Backend.openedContainersStack.length - 1, 1)
    $(Backend.openedContainersStack[Backend.openedContainersStack.length - 1]).show();
}

/*************************************************
	Help context handler
**************************************************/
Backend.setHelpContext = function(context)
{
	$('help').href = 'http://doc.livecart.com/en/' + context;
}

/*************************************************
	onLoad handler
**************************************************/
Backend.onLoad = function()
{
	// AJAX navigation
	dhtmlHistory.initialize();
	dhtmlHistory.addListener(Backend.ajaxNav.handle);
	dhtmlHistory.handleBookmark();
}	

/*************************************************
	AJAX back/forward navigation
**************************************************/
Backend.AjaxNavigationHandler = Class.create();
Backend.AjaxNavigationHandler.prototype = 
{
	ignoreNextAdd: false,
	
	initialize: function()
	{	 	
	},
	
	/**
	 * The AJAX history consists of clicks on certain elements (traditional history uses URL's)
	 * To register a history event, you only have to pass in an element ID, which was clicked. When
	 * the user navigates backward or forward using the browser navigation, these clicks are simply 
	 * repeated by calling the onclick() function for the particular element.
	 *
	 * Sometimes it is necessary to perform more than one "click" to return to previous state. In such case
	 * you can pass in several element ID's delimited with # sign. For example: cat_44#tabImages - would first
	 * emulate a click on cat_44 element and then on tabImages element. This is also useful for bookmarking,
	 * which allows to easily reference certain content on complex pages.
	 *   
	 * @param element string Element ID, which would be clicked 
	 * @param params Probably obsolete, but perhaps we'll find some use for it
	 */
	add: function(element, params)
	{
		if (true == this.ignoreNextAdd)
		{
			//addlog('ignoring ' + element);
			this.ignoreNextAdd = false;
			return false;
		}
		
		dhtmlHistory.add(element + '__');		
		return true;
	},
    
    getHash: function()
    {
        with(document.location)
        {
            return ("#" == hash[0]) ? hash.substring(1, hash.length - 2) : hash.substring(0, hash.length - 1);
        }
    },
	
	handle: function(element, params)
	{
        if(!params) params = {};
        if(!params.recoverFromIndex) params.recoverFromIndex = 0;
        
        var elementId = element.substr(0, element.length - 2);
		var hashElements = elementId.split('#');
		        
        for (var hashPart = params.recoverFromIndex; hashPart < hashElements.length; hashPart++)
		{           
			if ($(hashElements[hashPart]))
			{
                // only register the click for the last element
				if (hashPart < hashElements.length - 1)
				{
					Backend.ajaxNav.ignoreNext();
				}
				
				if ($(hashElements[hashPart]).onclick)
				{
                    $(hashElements[hashPart]).onclick();    
                }                
			}	
            // This is in case element is not yet loaded. If so we wait for all requests to finish and the continue.
            else if(Ajax.activeRequestCount > 0)
            {
                setInterval(function() 
                { 
                    if(this.handle)
                    {
                        this.handle(element, { recoverFromIndex: hashPart });
                    }
                }.bind(this), 10);

                return;
            } 
		}
	},
    
	
	ignoreNext: function()
	{
		this.ignoreNextAdd = true;  
	}	
}

Backend.ajaxNav = new Backend.AjaxNavigationHandler();

/*************************************************
	Layout Control
**************************************************/
Backend.LayoutManager = Class.create();

/**
 * Manage 100% heights
 *
 * IE does this pretty good natively (only the main content div height is changed on window resize),
 * however FF won't handle cascading 100% heights unless the page is being rendered in quirks mode.
 *
 * You can specify a block to take 100% height by assigning a "maxHeight" CSS class to it
 * This class also simulates an "extension" of CSS, that allows to add or substract some height
 * in pixels from percentage defined height (for example 100% minus 40px). This will often be needed
 * to compensate for parent elements padding. For example, if the parent element has a top and bottom
 * padding of 10px, you'll have to substract 20px from child block size. This will also be needed when
 * there are other siblings that consume some known height (like TabControl, which contains a
 * tab bar with known height and content div, which must take 100% of the rest of the available height).
 *
 * Example: 
 * 
 * <code>
 * 		<div class="maxHeight h--50">
 *			This div will take 100% of available space minus 50 pixels		
 *		</div>
 * </code>
 *
 * @todo automatically substract parent padding
 */
Backend.LayoutManager.prototype = 
{
	initialize: function()
	{	  	
		window.onresize = this.onresize.bindAsEventListener(this);
		this.onresize();	
	},	
	
	/**
	 * Set the minimum possible height to all involved elements, so that 
	 * their height could be enlarged to necessary size
	 */
	collapseAll: function(cont)
	{
		el = document.getElementsByClassName("maxHeight", document);

		for (k = 0; k < el.length; k++)
		{
			el[k].style.minHeight = '0px';

			if (document.all) 
			{
				el[k].style.height = '0px';
			}
			else
			{
				el[k].style.minHeight = '0px';
			}

		}
	},

	/**
	 * @todo Figure out why IE needs additional 2px offset
	 * @todo Figure out a better way to determine the body height for all browsers
	 */
	onresize: function()
	{
        if(BrowserDetect.browser == 'Explorer' && BrowserDetect.version == 7) return;
            
		if (document.all)
		{
			$('pageContentContainer').style.height = '0px';
		}
				
		// calculate content area height
		var ph = new PopupMenuHandler();
		var w = ph.getWindowHeight();
		var h = w - 160 - (document.all ? 1 : 0);
		var cont = $('pageContentContainer');

		if (BrowserDetect.browser == 'Explorer')
		{
			cont.style.height = h + 'px';				  
			
			// force re-render for IE
			$('pageContainer').style.display = 'none';
			$('pageContainer').style.display = 'block';
			$('nav').style.display = 'none';
			$('nav').style.display = 'block';
		}
		else // Good browsers
		{
			cont.style.minHeight = h + 'px';		  

			this.collapseAll(cont);
			this.setMaxHeight(cont);
		}
	},

	setMaxHeight: function(parent)
	{
	  	el = document.getElementsByClassName('maxHeight', parent);
	  	for (k = 0; k < el.length; k++)
		{
			var parentHeight = el[k].parentNode.offsetHeight;

			offset = 0;
			if (el[k].className.indexOf(' h-') > 0)
			{
			  	offset = el[k].className.substr(el[k].className.indexOf(' h-') + 3, 10);
			  	if (offset.indexOf(' ') > 0)
			  	{
			  		offset = offset.substr(0, offset.indexOf(' '));
			  	}				  	
			}  
			offset = parseInt(offset);
 			newHeight = parentHeight + offset;
			el[k].style.minHeight = newHeight + 'px';				    
		}
	}	
}

/*************************************************
	Breadcrumb navigation
**************************************************/
Backend.Breadcrumb = Class.create();

/**
 * Builds breadcrumb navigation menu
 */
Backend.Breadcrumb.prototype = 
{
	items: false,
	
	initialize: function()	
	{
		this.items = new Array();
		window.onload = this.display.bindAsEventListener(this);	  
	},
	
	addItem: function(title, url)
	{
		this.items[this.items.length] = {"title": title, "url": url}		
	},
	
	display: function()
	{
		// there must be at least 2 items added for the breadcrumb to be displayed
		if (this.items.length < 2)
		{
			return false;  
		}
	
		cont = $('breadcrumb');
		itemTemplate = $('breadcrumb_item');
		sepTemplate = $('breadcrumb_separator');
		lastItemTemplate = $('breadcrumb_lastItem');
										
		for (k = 0; k < this.items.length; k++)
		{
			if (k + 1 < this.items.length)
			{
				it = itemTemplate.cloneNode(true);
				it.firstChild.href = this.items[k].url;
				it.firstChild.innerHTML = this.items[k].title;			  
								
				it.appendChild(sepTemplate.cloneNode(true));				
			} 
			else
			{
				it = lastItemTemplate.cloneNode(true);
				it.innerHTML = this.items[k].title;			  
				it.id = 'breadcrumbLast';
			}
			
			cont.appendChild(it);	 	
		}  
	}
}

var breadcrumb = new Backend.Breadcrumb();

/*************************************************
	Backend menu 
**************************************************/
Backend.NavMenu = Class.create();

/**
 * Builds navigation menu from passed JSON array
 */
Backend.NavMenu.prototype = 
{
	initialize: function(menuArray, controller, action)
	{	
		var index = 0;
		var subIndex = 0;
        var subItemIndex = 0;
		var match = false;
		
		// find current menu items
		for (topIndex in menuArray)
		{
		  	if('object' == typeof menuArray[topIndex])
		  	{
				mItem = menuArray[topIndex];
				
				if (mItem['controller'] == controller)
				{
				  	index = topIndex;
				}
				
				if (mItem['controller'] == controller && mItem['action'] == action)				
				{
				  	index = topIndex;
					subItemIndex = 0;
					match = true;
					break;    
				}

				match = false;
				
				if ('object' == typeof mItem['items'])
				{
				  	for (subIndex in mItem['items'])
					{
					  	subItem = mItem['items'][subIndex];
					  	
					  	if (subItem['controller'] == controller && subItem['action'] == action)
					  	{
							index = topIndex;
							subItemIndex = subIndex;
							match = true;
							break;    
						}
						else if (controller == subItem['controller'])
						{
							index = topIndex;
							subItemIndex = subIndex;						  
						}						
					}
					
					if (match)
					{
					  	break;
					}	
				}
			}
		}

		// add current menu items to breadcrumb
		breadcrumb.addItem(menuArray[index]['title'], menuArray[index]['url']);
		if (subItemIndex > 0)
		{
			breadcrumb.addItem(menuArray[index]['items'][subItemIndex]['title'], 
					     	   menuArray[index]['items'][subItemIndex]['url']);							
		}

		// build menu
		var topItem = $('navTopItem-template');
		var subItem = $('navSubItem-template');
		
		navCont = $('nav');
		
		for (topIndex in menuArray)
		{
		  	if('object' == typeof menuArray[topIndex])
		  	{
				mItem = menuArray[topIndex];
				
				menuItem = topItem.cloneNode(true);
				
				menuItem.getElementsByTagName('a')[0].href = mItem['url'];
                if(!mItem['url'])
                {
                    menuItem.getElementsByTagName('a')[0].onclick = function() { return false; }
                    menuItem.getElementsByTagName('a')[0].style.textDecoration = 'none';
                }
				menuItem.getElementsByTagName('a')[0].innerHTML = mItem['title'];
				menuItem.style.display = 'block';
									
				if (topIndex == index)
				{
				  	menuItem.id = 'navSelected';
				}
				else
				{
				  	Event.observe(menuItem, 'mouseover', this.hideCurrentSubMenu);
				  	Event.observe(menuItem, 'mouseout', this.showCurrentSubMenu);
				}

				/* for IE >> */
				if ('Explorer' == BrowserDetect.browser)
				{
					menuItem.onmouseover=function() {
						this.className+=" over";
					}
					menuItem.onmouseout=function() {
						this.className=this.className.replace(" over", "");
					}
				}
				/* << IE */

				// submenu container
				ul = menuItem.getElementsByTagName('ul')[0];

				if ('object' == typeof mItem['items'])
				{
				  	for (subIndex in mItem['items'])
					{
					  	sub = mItem['items'][subIndex];

						if ('object' == typeof sub)
						{
						  	subNode = subItem.cloneNode(true);
						  	
						  	subNode.getElementsByTagName('a')[0].href = sub['url'];
						  	subNode.getElementsByTagName('a')[0].innerHTML = sub['title'];
							
							if ((topIndex == index) && (subIndex == subItemIndex))
							{
							  	subNode.id = 'navSubSelected';
							}
							
							ul.appendChild(subNode);						  					  	
						}
					}					
				}
				else 				
				{
				  	// no subitems
				  	ul.parentNode.removeChild(ul);
				}
			
				navCont.appendChild(menuItem);
			}
		}
	},
	
	hideCurrentSubMenu: function()
	{
	  	if ($('navSelected').getElementsByTagName('ul')[0])
	  	{
            $('navSelected').getElementsByTagName('ul')[0].style.visibility = 'hidden';                
        }
	},
	
	showCurrentSubMenu: function()
	{
	  	if ($('navSelected').getElementsByTagName('ul')[0])
	  	{
    	  	$('navSelected').getElementsByTagName('ul')[0].style.visibility = 'visible';
    	}
	}
}
	
/*************************************************
	Language switch menu
*************************************************/
function showLangMenu(display) {		
	menu = $('langMenuContainer');
	if (display)
	{
		menu.style.display = 'block';
		new Ajax.Updater('langMenuContainer', langMenuUrl);
				
		setTimeout("Event.observe(document, 'click', hideLangMenu, true);", 500);
	}
	else
	{
	  	menu.style.display = 'none';
		Event.stopObserving(document, 'click', hideLangMenu, true);
	}
}

function hideLangMenu()
{
	showLangMenu(false);
}

/*************************************************
	Popup Menu Handler
*************************************************/
/** 
 * Popup menu (absolutely positioned DIV's) position handling
 * This class calculates the optimal menu position, so that the 
 * menu would always be within visible window boundaries
 **/
PopupMenuHandler = Class.create();
PopupMenuHandler.prototype = 
{
	x: 0,
	y: 0,
	
	initialize: function(xPos, yPos, width, height)
	{
		scrollX = this.getScrollX();
		scrollY = this.getScrollY();

		if ((xPos + width) > (scrollX + this.getWindowWidth()))
		{
			xPos = scrollX + this.getWindowWidth() - width - 40;
		}
		
		if (xPos < scrollX)
		{
		  	xPos = scrollX + 1;
		}

		if ((yPos + height) > (scrollY + this.getWindowHeight()))
		{
			yPos = scrollY + this.getWindowHeight() - height - 40;
		}

		if (yPos < scrollY)
		{
		  	yPos = scrollY + 1;
		}
		
		this.x = xPos;
		this.y = yPos;
	},
	
	getScrollX: function() 
	{
		var scrOfX = 0;
		if( typeof( window.pageYOffset ) == 'number' ) {
			//Netscape compliant
			scrOfX = window.pageXOffset;
		} 
		else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) 
		{
			//DOM compliant
			scrOfX = document.body.scrollLeft;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) 
		{
			//IE6 standards compliant mode
			scrOfX = document.documentElement.scrollLeft;
		}
		return scrOfX;
	},
	
	getScrollY: function() 
	{
		var scrOfY = 0;
		if( typeof( window.pageYOffset ) == 'number' ) {
			//Netscape compliant
			scrOfY = window.pageYOffset;
		} 
		else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) 
		{
			//DOM compliant
			scrOfY = document.body.scrollTop;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) 
		{
			//IE6 standards compliant mode
			scrOfY = document.documentElement.scrollTop;
		}
		return scrOfY;
	},
	
	getWindowWidth: function() 
	{
		var myWidth = 0;
		if( typeof( window.innerWidth ) == 'number' ) 
		{
			//Non-IE
			myWidth = window.innerWidth;
		} 
		else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) 
		{
			//IE 6+ in 'standards compliant mode'
			myWidth = document.documentElement.clientWidth;
		} 
		else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) 
		{
			//IE 4 compatible
			myWidth = document.body.clientWidth;
		}
		return myWidth;
	},	

	getWindowHeight: function() 
	{
		var myHeight = 0;
		if( typeof( window.innerWidth ) == 'number' ) 
		{
			//Non-IE
			myHeight = window.innerHeight;
		} 
		else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) 
		{
			//IE 6+ in 'standards compliant mode'
			myHeight = document.documentElement.clientHeight;
		} 
		else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) 
		{
			//IE 4 compatible
			myHeight = document.body.clientHeight;
		}
		return myHeight;
	}
}


/*************************************************
	Browser detector
*************************************************/

/**
 * Browser detector
 * @link http://www.quirksmode.org/js/detect.html
 */
var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};

BrowserDetect.init();

/*************************************************
	Save confirmation message animation
*************************************************/
Backend.SaveConfirmationMessage = Class.create();
Backend.SaveConfirmationMessage.prototype = 
{
	counter: 0,
    timers: {},
    
    initialize: function(element, options)
  	{
        this.element = $(element);
        
        if(!this.element.id)
        {
            this.element.id = 'saveConfirmationMessage_' + (Backend.SaveConfirmationMessage.prototype++);
        }
        
        if(!Backend.SaveConfirmationMessage.prototype.timers[this.element.id])
        {
            Backend.SaveConfirmationMessage.prototype.timers[this.element.id] = {};
        }
        
        if(!this.element.down('div')) this.element.appendChild(document.createElement('div'));
        this.innerElement = this.element.down('div');
        
        if(options && options.type) 
        {
            Element.addClassName(this.element, options.type + 'Message')
        }
        
        try {
            if(options && options.message) 
            {
                if(this.innerElement.firstChild) this.innerElement.firstChild.value = options.message;
                else this.innerElement.appendChild(document.createTextNode(options.message));
            }
        } catch(e) { 
            console.info(e);
        }
        
        var closeButton = this.element.down('.closeMessage');
        if(closeButton)
        {
            this.hideCloseButton(closeButton);
            
            Event.observe(closeButton, 'mouseover', function(e) { this.showCloseButton(closeButton) }.bind(this) )
            Event.observe(closeButton, 'mouseout', function(e) { this.hideCloseButton(closeButton) }.bind(this) )
            Event.observe(closeButton, 'click', function(e) { this.hide() }.bind(this) )
        }
        
		this.show();
	},
    
    showCloseButton: function(closeButton)
    {
        try {
            closeButton.setOpacity(1);            
        } catch(e) {
            closeButton.style.visibility = 'visible';
        }
    },
    
    hideCloseButton: function(closeButton)
    {
        try {
            closeButton.setOpacity(0.5);            
        } catch(e) {
            closeButton.style.visibility = 'hidden';
        }
    },
	
	show: function()
	{
        this.stopTimers();
        this.element.hide();
        
        this.displaying = true;
        
        Backend.SaveConfirmationMessage.prototype.timers[this.element.id].scrollEffect = new Effect.ScrollTo(this.element, {offset: -24});
        Backend.SaveConfirmationMessage.prototype.timers[this.element.id].appearEffect = new Effect.Appear(this.element, {duration: 0.4, afterFinish: this.highlight.bind(this)});
	},

	highlight: function()
	{
        this.innerElement.focus();
        Backend.SaveConfirmationMessage.prototype.timers[this.element.id].effectHighlight = new Effect.Highlight(this.innerElement, { duration: 0.4 });
       
        // do not hide error messages
        if (!this.element.hasClassName('redMessage') && !this.element.hasClassName('bugMessage'))
        {
            Backend.SaveConfirmationMessage.prototype.timers[this.element.id].hideTimeout = setTimeout(function() { this.hide() }.bind(this), 4000);   
        }
	},

	hide: function()
	{
        Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeEffect = Effect.Fade(this.element, {duration: 0.4});
        Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeTimeout = setTimeout(function() { this.displaying = false; }.bind(this), 4000);  
	},
    
    stopTimers: function()
    {
        if(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].hideTimeout) clearTimeout(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].hideTimeout);
        if(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeTimeout) clearTimeout(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeTimeout);
        if(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].appearEffect) Backend.SaveConfirmationMessage.prototype.timers[this.element.id].appearEffect.cancel();
        if(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeEffect) Backend.SaveConfirmationMessage.prototype.timers[this.element.id].fadeEffect.cancel();
        if(Backend.SaveConfirmationMessage.prototype.timers[this.element.id].effectHighlight) Backend.SaveConfirmationMessage.prototype.timers[this.element.id].effectHighlight.cancel();
    }
}

/**
 * Unit conventer
 */
Backend.UnitConventer = Class.create();
Backend.UnitConventer.prototype = 
{
	Instances: {},
	
	initialize: function(root)
	{
		// Get all nodes
		this.nodes = {};
		this.nodes.root = $(root);
        this.nodes.normalizedWeightField = this.nodes.root.down(".UnitConventer_NormalizedWeight");
        this.nodes.unitsTypeField = this.nodes.root.down(".UnitConventer_UnitsType");
		this.nodes.hiValue = this.nodes.root.down('.UnitConventer_HiValue');
		this.nodes.loValue = this.nodes.root.down('.UnitConventer_LoValue');
        this.nodes.switchUnits = this.nodes.root.down('.UnitConventer_SwitchUnits');
		
		// Add units after fields
		if(!this.nodes.root.down('.UnitConventer_HiUnit'))
		{
		   new Insertion.After(this.nodes.hiValue, '<span class="UnitConventer_HiUnit"> </span>');
		}
		
        if(!this.nodes.root.down('.UnitConventer_LoUnit'))
        {
            new Insertion.After(this.nodes.loValue, '<span class="UnitConventer_LoUnit"> </span>');
		}
		
		this.reset();
		
		// Bind events
		Event.observe(this.nodes.hiValue, "keyup", function(e){ NumericFilter(this); });
        Event.observe(this.nodes.loValue, "keyup", function(e){ NumericFilter(this); });
		
		Event.observe(this.nodes.hiValue, 'keyup', function(e) { this.updateShippingWeight() }.bind(this));
        Event.observe(this.nodes.loValue, 'keyup', function(e) { this.updateShippingWeight() }.bind(this));
        Event.observe(this.nodes.switchUnits, 'click', function(e) { Event.stop(e); this.switchUnitTypes() }.bind(this));
	},
	
	reset: function()
	{
        this.nodes.switchUnits.update(this.nodes.root.down('.UnitConventer_SwitcgTo' + (this.nodes.unitsTypeField.value == 'ENGLISH' ? 'METRIC' : 'ENGLISH').capitalize() + 'Title').innerHTML);
        this.nodes.root.down('.UnitConventer_HiUnit').innerHTML = this.nodes.root.down('.UnitConventer_'  + this.nodes.unitsTypeField.value.capitalize() + 'HiUnit').innerHTML;
        this.nodes.root.down('.UnitConventer_LoUnit').innerHTML = this.nodes.root.down('.UnitConventer_'  + this.nodes.unitsTypeField.value.capitalize() + 'LoUnit').innerHTML;
    
        this.nodes.hiValue.value = 0;
        this.nodes.loValue.value = 0;
	},
		
	getInstance: function(root)
	{
		if(!Backend.UnitConventer.prototype.Instances[$(root).id])
		{
			Backend.UnitConventer.prototype.Instances[$(root).id] = new Backend.UnitConventer(root);
		}
		
		return Backend.UnitConventer.prototype.Instances[$(root).id];
	},
	
    switchUnitTypes: function()
    {
        this.nodes.switchUnits.update(this.nodes.root.down('.UnitConventer_SwitcgTo' + this.nodes.unitsTypeField.value.capitalize() + 'Title').innerHTML);
		
        this.nodes.unitsTypeField.value = (this.nodes.unitsTypeField.value == 'ENGLISH') ? 'METRIC' : 'ENGLISH';

        // Change captions
        this.nodes.root.down('.UnitConventer_HiUnit').innerHTML = this.nodes.root.down('.UnitConventer_'  + this.nodes.unitsTypeField.value.capitalize() + 'HiUnit').innerHTML;
        this.nodes.root.down('.UnitConventer_LoUnit').innerHTML = this.nodes.root.down('.UnitConventer_'  + this.nodes.unitsTypeField.value.capitalize() + 'LoUnit').innerHTML;

        var multipliers = this.getWeightMultipliers();

        var hiValue = Math.floor(this.nodes.normalizedWeightField.value / multipliers[0]);
        var loValue = (this.nodes.normalizedWeightField.value - (hiValue * multipliers[0])) / multipliers[1];
        loValue = Math.round(loValue * 1000) / 1000;

        if ('english' == this.nodes.unitsTypeField.value)
        {
            loValue = loValue.toFixed(0);
        }

        this.nodes.hiValue.value = hiValue;
        this.nodes.loValue.value = loValue;
    },	
	
    getWeightMultipliers: function()
    {
        switch(this.nodes.unitsTypeField.value)
        {
            case 'ENGLISH': 
                return [0.45359237, 0.0283495231];
            
            case 'METRIC': 
            default:
                return [1, 0.001]
        }
    },
	
    updateShippingWeight: function(field)
    {
        var multipliers = this.getWeightMultipliers();
        this.nodes.normalizedWeightField.value = (this.nodes.hiValue.value * multipliers[0]) + (this.nodes.loValue.value * multipliers[1]);
    }
}

/*************************************************
	...
*************************************************/

function slideForm(id, menuId)
{
	Effect.Appear(id, {duration: 0.50});	  	
	Element.hide($(menuId));
//	setTimeout('$("' +  id + '").focus()', 100);
}

function restoreMenu(blockId, menuId)
{
	Element.hide($(blockId));
//	Effect.Fade(blockId, {duration: 0.15});	  	
	Element.show($(menuId)); 	
}

/***************************************************
 * Language form
 **************************************************/
Backend.LanguageForm = Class.create();
Backend.LanguageForm.prototype = 
{
	initialize: function(root)
	{
		var forms = document.getElementsByClassName('languageForm', root);
		for (var k = 0; k < forms.length; k++)
		{
			var tabs = forms[k].down('ul.languageFormTabs').getElementsByTagName('li');
			for (var t = 0; t < tabs.length; t++)
			{
				tabs[t].onclick = this.handleTabClick.bindAsEventListener(this);
			}
		}		
	},
	
	handleTabClick: function(e)
	{
		var tab = Event.element(e);
		
		// make other tabs inactive
		var tabs = tab.parentNode.getElementsByTagName('li');
		for (var k = 0; k < tabs.length; k++)
		{
			if (tabs[k] != tab)
			{
				Element.removeClassName(tabs[k], 'active');
			}
		}
		
		Element.toggleClassName(tab, 'active');		
				
		// hide tab contents
		var cont = tab.up('.languageForm').down('.languageFormContent').getElementsByClassName('languageFormContainer');
		for (var k = 0; k < cont.length; k++)
		{
			Element.removeClassName(cont[k], 'active');		
		}		
		
		if (Element.hasClassName(tab, 'active'))
		{
			// get language code
			var id = tab.className.match(/languageFormTabs_([a-z]{2})/)[1];
			Element.addClassName(tab.up('.languageForm').down('.languageFormContainer_' + id), 'active');
		}		
	}
}

/***************************************************
 * MVC View
 **************************************************/
Backend.RegisterMVC = function(MVC)
{
    MVC.Messages = {};
    MVC.Links = {};
    
    MVC.Model.prototype.defaultLanguage = false;
    
    MVC.Model.prototype.getDefaultLanguage = function()
    {
        if(this.defaultLanguage === false) 
        {
            this.languages.each(function(language)
            {
                if(parseInt(language.value.isDefault))
                {
                    this.defaultLanguage = language.value;
                }   
            }.bind(this));
        }
        
        return this.defaultLanguage;
    }
    
    MVC.Model.prototype.store = MVC.View.prototype.assign = function(name, value)
    {
        if(arguments.length == 1)
        {
            this._data = name;
        }
        else
        {
            this._data[name] = value;
        }
    },

    MVC.Model.prototype.clear = MVC.View.prototype.clear = function()
    {
        this._data = {};
    },
 
    MVC.Model.prototype.get = MVC.View.prototype.get = function(name, defaultValue)
    {
        var keys = name.split('.');
        var destination = this._data;
        var found = true;
        
        try
        {
            $A(keys).each(function(key) 
            {
                if(destination[key] === undefined) throw new Error('not found');
                destination = destination[key];
            });
        }
        catch(e)
        {
            found = false;
        }
        
        return found ? destination : defaultValue;
    }
}


/********************************************************************
 * Select popup
 ********************************************************************/
Backend.SelectPopup = Class.create();
Backend.SelectPopup.prototype = {
    height: 520,
    width:  1000,
    onObjectSelect: function() {},
    
    initialize: function(link, title, options)
    {
        try
        {
            this.link = link;
            this.title = title;
            
            if(options.onObjectSelect) this.onObjectSelect = options.onObjectSelect;
            
            this.createPopup();
        }
        catch(e)
        {
            console.info(e);
        }
    },
    
    createPopup: function()
    {
        Backend.SelectPopup.prototype.popup = window.open(this.link, this.title, 'resizable=1,width=' + this.width + ',height=' + this.height);
        Backend.SelectPopup.prototype.popup.onunload = function()
		{
			window.selectPopupWindow = null;
		}
		
		Backend.SelectPopup.prototype.popup.focus();
               
	    window.selectPopupWindow = Backend.SelectPopup.prototype.popup;
		
		Backend.SelectPopup.prototype.popup
						
        Event.observe(window, 'unload', function() { Backend.SelectPopup.prototype.popup.close(); });
        
        window.selectProductPopup = this;
    },
    
    getSelectedObject: function(objectID)
    {
        this.objectID = objectID;
        this.onObjectSelect.call(this, objectID);
    }
}


/***************************************************
 * library\livecart.js
 ***************************************************/

var LiveCart = {
    ajaxUpdaterInstance: null
}

LiveCart.AjaxRequest = Class.create();
LiveCart.AjaxRequest.prototype = {
    requestCount: 0,
    
	onComplete: false,
    
    indicatorContainerId: false,
    
	initialize: function(formOrUrl, indicatorId, onComplete)
    {
        var url = "";
        var method = "";
        var params = "";
        
        this.onComplete = onComplete;
        
        if (typeof formOrUrl == "object")
        {
            var form = formOrUrl;
            url = form.action;
            method = form.method;
            params = Form.serialize(form);
        
            if (!indicatorId)
            {
                var controls = form.down('fieldset.controls');
                if (controls)
                {
                    indicatorId = controls.down('.progressIndicator');
                    if(indicatorId.style.visibility == 'hidden')
                    {
                        this.adjustIndicatorVisibility = true;
                    }
                }
            }
        }
        else
        {
            url = formOrUrl;
            method = "post";
        }

        if (indicatorId)
        {
            this.indicatorContainerId = indicatorId;
            Element.show(this.indicatorContainerId);            
        }
        
        var updaterOptions = { method: method,
                               parameters: params,
                               onComplete: this.postProcessResponse.bind(this),
                               onFailure: this.reportError
                               };
       
		document.body.style.cursor = 'progress';

        new Ajax.Request(url, updaterOptions);
    },

	hideIndicator: function()
	{
        if(this.indicatorContainerId)
        {
            Element.hide(this.indicatorContainerId);
        }
	},

	showIndocator: function()
	{
		if(this.indicatorContainerId)
        {
            Element.show(this.indicatorContainerId);
        }
	},

    postProcessResponse: function(response)
    {
		
		this.hideIndicator();
		
		if ('text/javascript' == response.getResponseHeader('Content-type') && $('confirmations'))
		{
            var confirmations = $('confirmations');
            if(!confirmations.down('#yellowZone')) new Insertion.Top('confirmations', '<div id="yellowZone"></div>');
            if(!confirmations.down('#redZone')) new Insertion.Top('confirmations', '<div id="redZone"></div>');
            if(!confirmations.down('#bugZone')) new Insertion.Top('confirmations', '<div id="bugZone"></div>');

            if(window.selectPopupWindow)
			{
				var win = window.selectPopupWindow;
				
	            var confirmations = win.$('confirmations');
                if(confirmations)
                {
		            if(!confirmations.down('#yellowZone')) new win.Insertion.Top('confirmations', '<div id="yellowZone"></div>');
		            if(!confirmations.down('#redZone')) new win.Insertion.Top('confirmations', '<div id="redZone"></div>');
		            if(!confirmations.down('#bugZone')) new win.Insertion.Top('confirmations', '<div id="bugZone"></div>');
				}
            }
			
            try
            {
                response.responseData = response.responseText.evalJSON();
                
                // Show confirmation
                if(response.responseData.status)
                {
                    this.showConfirmation(response.responseData);
                }
            }
            catch (e)  { this.showBug(); }
        }

		document.body.style.cursor = 'default';
        if (this.onComplete)
        {
		  	this.onComplete(response);
		}
    },
    
    showBug: function()
    {
        new Insertion.Top('bugZone', 
        '<div style="display: none;" id="confirmation_' + (++LiveCart.AjaxRequest.prototype.requestCount) + '" class="bugMessage">' + 
            '<img class="closeMessage" src="image/silk/cancel.png"/>' + 
            '<div>' + Backend.internalErrorMessage + '</div>' + 
        '</div>');
        
        new Backend.SaveConfirmationMessage($('confirmation_' + LiveCart.AjaxRequest.prototype.requestCount));	
		
		if(window.selectPopupWindow)
		{
			var win = window.selectPopupWindow;
            if(win.$('confirmations'))
            {
		        new win.Insertion.Top('bugZone', 
		        '<div style="display: none;" id="confirmation_' + (++LiveCart.AjaxRequest.prototype.requestCount) + '" class="bugMessage">' + 
		            '<img class="closeMessage" src="image/silk/cancel.png"/>' + 
		            '<div>' + Backend.internalErrorMessage + '</div>' + 
		        '</div>');
		        
		        new Backend.SaveConfirmationMessage(win.$('confirmation_' + LiveCart.AjaxRequest.prototype.requestCount));  
            }
		}
    },
    
    showConfirmation: function(responseData)
    {       
        var color = null;
        if('success' == responseData.status) color = 'yellow';
        if('failure' == responseData.status) color = 'red';
        
        new Insertion.Top(color + 'Zone', 
        '<div style="display: none;" id="confirmation_' + (++LiveCart.AjaxRequest.prototype.requestCount) + '" class="' + color + 'Message">' + 
            '<img class="closeMessage" src="image/silk/cancel.png"/>' + 
            '<div>' + responseData.message + '</div>' + 
        '</div>');
        
        new Backend.SaveConfirmationMessage($('confirmation_' + LiveCart.AjaxRequest.prototype.requestCount));	
		
		if(window.selectPopupWindow)
		{
			var win = window.selectPopupWindow;
			
	        new win.Insertion.Top(color + 'Zone', 
	        '<div style="display: none;" id="confirmation_' + (++LiveCart.AjaxRequest.prototype.requestCount) + '" class="' + color + 'Message">' + 
	            '<img class="closeMessage" src="image/silk/cancel.png"/>' + 
	            '<div>' + responseData.message + '</div>' + 
	        '</div>');
			
            new win.Backend.SaveConfirmationMessage(win.$('confirmation_' + LiveCart.AjaxRequest.prototype.requestCount));  
	}
    },
    
    reportError: function(response)
    {
        alert('Error!\n\n' + response.responseText);
    }
}

LiveCart.AjaxUpdater = Class.create();
LiveCart.AjaxUpdater.prototype = {

    indicatorContainerId: null,

    initialize: function(formOrUrl, container, indicator, insertionPosition, onComplete)
    {
        var url = "";
        var method = "";
        var params = "";
        this.onComplete = onComplete;
        
        var containerId = $(container);
        var indicatorId = $(indicator);
        
        if (typeof formOrUrl == "object")
        {
            var form = formOrUrl;
            url = form.action;
            method = form.method;
            params = Form.serialize(form);

            if (!indicatorId)
            {
                var controls = form.down('fieldset.controls');
                if (controls)
                {
                    indicatorId = controls.down('.progressIndicator');
                }
            }
        }
        else
        {
            url = formOrUrl;
            method = "post";
        }
        
        LiveCart.ajaxUpdaterInstance = this;

        if (indicatorId)
        {
			this.indicatorContainerId = indicatorId;
	        Element.show(this.indicatorContainerId);			
		}

        var updaterOptions = { method: method,
                               parameters: params,
                               onComplete: this.postProcessResponse.bind(this),
                               onFailure: this.reportError.bind(this)
                               };

        if (insertionPosition != undefined)
        {
            switch(insertionPosition)
            {
                case 'top':
                    updaterOptions.insertion = Insertion.Top;
                break;

                case 'bottom':
                    updaterOptions.insertion = Insertion.Bottom;
                break;

                case 'before':
                    updaterOptions.insertion = Insertion.Before;
                break;

                case 'after':
                    updaterOptions.insertion = Insertion.After;
                break;

                default:
                    alert('Invalid insertion position value in AjaxUpdater');
                break;
            }
        }
        
		document.body.style.cursor = 'progress';
		
        var ajax = new Ajax.Updater({success: containerId},
                         url,
                         updaterOptions); 

    },

	hideIndicator: function()
	{
		if ($(LiveCart.ajaxUpdaterInstance.indicatorContainerId))
		{
			Element.hide(LiveCart.ajaxUpdaterInstance.indicatorContainerId);			
		}
	},

	showIndocator: function()
	{
		Element.show(this.indicatorContainerId);
	},

    postProcessResponse: function(response)
    {
        document.body.style.cursor = 'default';
        response.responseText.evalScripts();  
        LiveCart.ajaxUpdaterInstance.hideIndicator();

        if (this.onComplete)
        {
		  	this.onComplete(response);
		}        
    },

    reportError: function(response)
    {
        alert('Error!\n\n' + response.responseText);
    }
}

/**
 * Converts an XMLDocument into HTMLElement
 *
 * Useful when receiving partial page content as XML via AJAX, which can be transformed to
 * inserted into document as HTMLElement.
 *
 * <code>
 * 		item = xml2HtmlElement(request.responseXML.firstChild);
 *		document.getElementById('someList').appendChild(item);
 * </code>
 *
 * Don't forget to set the correct Content-type header before sending the content:
 * <code>
 *      $response->setHeader('Content-type', 'application/xml');
 * </code>
 *
 * @param xml Element
 * @return HTMLElement
 */
function xml2HtmlElement(xml)
{
	var k = 0;
	var a = 0;
	var el = 0;
	var child = 0;

	if ('#text' == xml.nodeName)
	{
		el = document.createTextNode(xml.nodeValue);
	}
	else
	{
	  	el = document.createElement(xml.nodeName);
		el.nodeValue = xml.nodeValue;
		if (xml.attributes.length > 0)
		{
		  	for (a = 0; a < xml.attributes.length; a++)
		  	{
			    att = xml.attributes[a];
				el.setAttribute(att.name, att.value);
			}
		}
		if (xml.childNodes.length > 0)
		{
			for (k = 0; k < xml.childNodes.length; k++)
			{
				child = xml2HtmlElement(xml.childNodes[k]);
				el.appendChild(child);
			}
		}
	}
	return el;
}


/***************************************************
 * library\KeyboardEvent.js
 ***************************************************/

/**
 * KeyboardEvent's task is to provide cross-browser suport for handling keyboard
 * events. It provides function to get current button code and char, shift status, etc
 *
 * @todo Caps lock support
 *
 * @version 1.1
 * @author Sergej Andrejev, Rinalds Uzkalns
 *
 */
var KeyboardEvent = Class.create();
KeyboardEvent.prototype = {
    /**
     * Tab key
     *
     * @var int
     */
    KEY_TAB:    9,

    /**
     * Enter key
     *
     * @var int
     */
    KEY_ENTER:  13,

    /**
     * Shift key
     *
     * @var int
     */
    KEY_SHIFT:  16,

    /**
     * Escape key
     *
     * @var int
     */
    KEY_ESC:    27,

    /**
     * Up key
     *
     * @var int
     */
    KEY_UP:     38,

    /**
     * Down key
     *
     * @var int
     */
    KEY_DOWN:   40,

    /**
     * Left key
     *
     * @var int
     */
    KEY_LEFT:     37,

    /**
     * Right key
     *
     * @var int
     */
    KEY_RIGHT:   39,

    /**
     * Delete key
     *
     * @var int
     */
    KEY_DEL:    46,

    /**
     * Constructor
     *
     * @param WindowEvent e Event object (for explorer it will be auto-detected. If you decide to pass it anyway then passed event will be used)
     *
     * @access public
     */
    initialize: function(e)
    {
        if (!e && window.event)
        {
            e = window.event; // IE
            e.target = e.srcElement;
        }

        this.event = e;
    },
    
    init: function(e)
    {
        return new KeyboardEvent(e);
    },

    /**
     * Determines which key (number) was pressed
     *
     * @access public
     *
     * @return int Key number
     */
    getKey: function()
    {
        return this.event.which ? this.event.which : ( this.event.keyCode ? this.event.keyCode : ( this.event.charCode ? this.event.charCode : 0 ) );
    },


    /**
     * Determines which char was pressed. It will also check if shift button
     * was hold and return upercase if it was. So far no support for caps lock
     *
     * @access public
     *
     * @todo Caps lock support
     *
     * @return int Key number
     */
    getChar: function()
    {
        var string = String.fromCharCode(this.getKey());

        if(!this.isShift()) string = string.toLowerCase();

        return string;
    },

    /**
     * Check if shift was pressed while inputing the letter
     *
     * @access public
     *
     * @return bool
     */
    isShift: function()
    {
        return this.event.shiftKey || ( this.event.modifiers && ( this.event.modifiers & 4 ) );;
    },

    isPrintable: function()
    {
        var key = this.getKey();

        // [A-Za-z0-9]
        return (key > 64 && key < 91) || (key > 96 && key < 123) || (key > 47 && key < 58);
    },

    /**
     * Deselects any window text (except in controls)
     *
     * @access public
     */
    deselectText: function()
    {
        if (document.selection)
        {
            document.selection.empty();
        }
        else if (window.getSelection)
        {
            window.getSelection().removeAllRanges();
        }
    },

    /**
     * Get cursor position in the text
     *
     * @access public
     */
    getCursorPosition: function()
    {
        if(document.selection)
        {
            return document.selection;
        }
        else if(this.event.target.selectionStart)
        {
            return this.event.target.selectionStart;
        }
		else
		{
		    return false;
		}
    },
    
    isEnter: function()
    {
        return this.getKey() == this.KEY_ENTER;
    }
}




/***************************************************
 * library\dhtmlxtree\dhtmlXCommon.js
 ***************************************************/

/*
Copyright Scand LLC http://www.scbr.com
To use this component please contact info@scbr.com to obtain license
*/
		/**
          *     @desc: xmlLoader object
          *     @type: private
          *     @param: funcObject - xml parser function
          *     @param: object - jsControl object
          *     @param: async - sync/async mode (async by default)
          *     @param: rSeed - enable/disable random seed ( prevent IE caching)
		  *     @topic: 0
          */	
function dtmlXMLLoaderObject(funcObject, dhtmlObject,async,rSeed){
	this.xmlDoc="";
	if(arguments.length==2)
		this.async=false;
	else
		this.async=async;
	this.onloadAction=funcObject||null;
	this.mainObject=dhtmlObject||null;
    this.waitCall=null;
	this.rSeed=rSeed||false;
	return this;
};
		/**
          *     @desc: xml loading handler
          *     @type: private
          *     @param: dtmlObject - xmlLoader object
		  *     @topic: 0
          */
	dtmlXMLLoaderObject.prototype.waitLoadFunction=function(dhtmlObject){
		this.check=function (){
			if(dhtmlObject.onloadAction!=null){
				if  ((!dhtmlObject.xmlDoc.readyState)||(dhtmlObject.xmlDoc.readyState == 4)){                       
					dhtmlObject.onloadAction(dhtmlObject.mainObject,null,null,null,dhtmlObject);
                    if (dhtmlObject.waitCall) { dhtmlObject.waitCall(); dhtmlObject.waitCall=null; }
                    dhtmlObject=null;
                    }
			}
		};
		return this.check;
	};

		/**
          *     @desc: return XML top node
		  *     @param: tagName - top XML node tag name (not used in IE, required for Safari and Mozilla)
          *     @type: private
		  *     @returns: top XML node
		  *     @topic: 0  
          */
	dtmlXMLLoaderObject.prototype.getXMLTopNode=function(tagName){ 
			if (this.xmlDoc.responseXML)  { 
				var temp=this.xmlDoc.responseXML.getElementsByTagName(tagName);
				var z=temp[0];  
			}else
				var z=this.xmlDoc.documentElement;
			if (z){
				this._retry=false;
				return z;
				}

            if ((_isIE)&&(!this._retry)){    
                //fall back to MS.XMLDOM
                var xmlString=this.xmlDoc.responseText;
                this._retry=true;
           			this.xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
           			this.xmlDoc.async=false;
            		this.xmlDoc.loadXML(xmlString);

                    return this.getXMLTopNode(tagName);
            }
            dhtmlxError.throwError("LoadXML","Incorrect XML",[this.xmlDoc,this.mainObject]);
			return document.createElement("DIV");
	};

		/**
          *     @desc: load XML from string
          *     @type: private
          *     @param: xmlString - xml string
		  *     @topic: 0  
          */
	dtmlXMLLoaderObject.prototype.loadXMLString=function(xmlString){
     try
	 {
		 var parser = new DOMParser();
		 this.xmlDoc = parser.parseFromString(xmlString,"text/xml");
 }
	 catch(e){
		this.xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
		this.xmlDoc.async=this.async;
		this.xmlDoc.loadXML(xmlString);
	 }
	  this.onloadAction(this.mainObject);
      if (this.waitCall) { this.waitCall(); this.waitCall=null; }
	}
		/**
          *     @desc: load XML
          *     @type: private
          *     @param: filePath - xml file path
          *     @param: postMode - send POST request
          *     @param: postVars - list of vars for post request
		  *     @topic: 0
          */
	dtmlXMLLoaderObject.prototype.loadXML=function(filePath,postMode,postVars){
	 if (this.rSeed) filePath+=((filePath.indexOf("?")!=-1)?"&":"?")+"a_dhx_rSeed="+(new Date()).valueOf();
     this.filePath=filePath;

     if (window.XMLHttpRequest){
	 	this.xmlDoc = new XMLHttpRequest();
		this.xmlDoc.open(postMode?"POST":"GET",filePath,this.async);
        if (postMode)
              this.xmlDoc.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		this.xmlDoc.onreadystatechange=new this.waitLoadFunction(this);
		this.xmlDoc.send(null||postVars);
	}
	else{

    		if (document.implementation && document.implementation.createDocument)
    		{
    			this.xmlDoc = document.implementation.createDocument("", "", null);
    			this.xmlDoc.onload = new this.waitLoadFunction(this);
				this.xmlDoc.load(filePath);
    		}
    		else
    		{
        			this.xmlDoc = new ActiveXObject("Microsoft.XMLHTTP");
            		this.xmlDoc.open(postMode?"POST":"GET",filePath,this.async);
                    if (postMode) this.xmlDoc.setRequestHeader('Content-type','application/x-www-form-urlencoded');
        			this.xmlDoc.onreadystatechange=new this.waitLoadFunction(this);
            		this.xmlDoc.send(null||postVars);
    		}
  		}
	};
		/**
          *     @desc: destructor, cleans used memory
          *     @type: private
		  *     @topic: 0
          */
    dtmlXMLLoaderObject.prototype.destructor=function(){
        this.onloadAction=null;
	    this.mainObject=null;
        this.xmlDoc=null;
        return null;
    }
	
		/**  
          *     @desc: Call wrapper
          *     @type: private
          *     @param: funcObject - action handler
          *     @param: dhtmlObject - user data
		  *     @returns: function handler
		  *     @topic: 0  
          */
function callerFunction(funcObject,dhtmlObject){
	this.handler=function(e){
		if (!e) e=event;
		funcObject(e,dhtmlObject);
		return true;
	};
	return this.handler;
};

		/**  
          *     @desc: Calculate absolute position of html object
          *     @type: private
          *     @param: htmlObject - html object
		  *     @topic: 0  
          */
function getAbsoluteLeft(htmlObject){
        var xPos = htmlObject.offsetLeft;
        var temp = htmlObject.offsetParent;
        while (temp != null) {
            xPos += temp.offsetLeft;
            temp = temp.offsetParent;
        }
        return xPos;
    }
		/**
          *     @desc: Calculate absolute position of html object
          *     @type: private
          *     @param: htmlObject - html object
		  *     @topic: 0  
          */	
function getAbsoluteTop(htmlObject) {
        var yPos = htmlObject.offsetTop;
        var temp = htmlObject.offsetParent;
        while (temp != null) {
            yPos += temp.offsetTop;
            temp = temp.offsetParent;
        }
        return yPos;
   }
   
   
/**  
*     @desc: Convert string to it boolean representation
*     @type: private
*     @param: inputString - string for covertion
*     @topic: 0  
*/	  
function convertStringToBoolean(inputString){ if (typeof(inputString)=="string") inputString=inputString.toLowerCase();
	switch(inputString){
		case "1":
		case "true":
		case "yes":
		case "y":
		case 1:		
		case true:		
					return true; 
					break;
		default: 	return false;
	}
}

/**  
*     @desc: find out what symbol to use as url param delimiters in further params
*     @type: private
*     @param: str - current url string
*     @topic: 0  
*/
function getUrlSymbol(str){
		if(str.indexOf("?")!=-1)
			return "&"
		else
			return "?"
	}
	
	
function dhtmlDragAndDropObject(){
		this.lastLanding=0;
		this.dragNode=0;
		this.dragStartNode=0;
		this.dragStartObject=0;
		this.tempDOMU=null;
		this.tempDOMM=null;
		this.waitDrag=0;
		if (window.dhtmlDragAndDrop) return window.dhtmlDragAndDrop;
		window.dhtmlDragAndDrop=this;

		return this;
	};
	
	dhtmlDragAndDropObject.prototype.removeDraggableItem=function(htmlNode){
		htmlNode.onmousedown=null;
		htmlNode.dragStarter=null;
		htmlNode.dragLanding=null;
	}
	dhtmlDragAndDropObject.prototype.addDraggableItem=function(htmlNode,dhtmlObject){
		htmlNode.onmousedown=this.preCreateDragCopy;
		htmlNode.dragStarter=dhtmlObject;
		this.addDragLanding(htmlNode,dhtmlObject);
	}
	dhtmlDragAndDropObject.prototype.addDragLanding=function(htmlNode,dhtmlObject){
		htmlNode.dragLanding=dhtmlObject;
	}
	dhtmlDragAndDropObject.prototype.preCreateDragCopy=function(e)
	{
		if (window.dhtmlDragAndDrop.waitDrag) {
			 window.dhtmlDragAndDrop.waitDrag=0;
			 document.body.onmouseup=window.dhtmlDragAndDrop.tempDOMU;
			 document.body.onmousemove=window.dhtmlDragAndDrop.tempDOMM;
			 return false;
		}

		window.dhtmlDragAndDrop.waitDrag=1;
		window.dhtmlDragAndDrop.tempDOMU=document.body.onmouseup;
		window.dhtmlDragAndDrop.tempDOMM=document.body.onmousemove;
		window.dhtmlDragAndDrop.dragStartNode=this;
		window.dhtmlDragAndDrop.dragStartObject=this.dragStarter;
		document.body.onmouseup=window.dhtmlDragAndDrop.preCreateDragCopy;
		document.body.onmousemove=window.dhtmlDragAndDrop.callDrag;

            	if ((e)&&(e.preventDefault)) { e.preventDefault(); return false; }
            	return false;
	};
	dhtmlDragAndDropObject.prototype.callDrag=function(e){
		if (!e) e=window.event;
		dragger=window.dhtmlDragAndDrop;

	   	if ((e.button==0)&&(_isIE)) return dragger.stopDrag();
		if (!dragger.dragNode) {
			dragger.dragNode=dragger.dragStartObject._createDragNode(dragger.dragStartNode,e);
            if (!dragger.dragNode) return dragger.stopDrag();
			dragger.gldragNode=dragger.dragNode;
			document.body.appendChild(dragger.dragNode);
			document.body.onmouseup=dragger.stopDrag;
			dragger.waitDrag=0;
			dragger.dragNode.pWindow=window;
		   	dragger.initFrameRoute();
			}


		if (dragger.dragNode.parentNode!=window.document.body){
			var grd=dragger.gldragNode;
			if (dragger.gldragNode.old) grd=dragger.gldragNode.old;

			//if (!document.all) dragger.calculateFramePosition();
			grd.parentNode.removeChild(grd);
			var oldBody=dragger.dragNode.pWindow;
			if (_isIE){
			var div=document.createElement("Div");
			div.innerHTML=dragger.dragNode.outerHTML;
			dragger.dragNode=div.childNodes[0];	}
			else dragger.dragNode=dragger.dragNode.cloneNode(true);
			dragger.dragNode.pWindow=window;
			dragger.gldragNode.old=dragger.dragNode;
			document.body.appendChild(dragger.dragNode);
			oldBody.dhtmlDragAndDrop.dragNode=dragger.dragNode;
		}

			dragger.dragNode.style.left=e.clientX+15+(dragger.fx?dragger.fx*(-1):0)+(document.body.scrollLeft||document.documentElement.scrollLeft)+"px";
			dragger.dragNode.style.top=e.clientY+3+(dragger.fy?dragger.fy*(-1):0)+(document.body.scrollTop||document.documentElement.scrollTop)+"px";
		if (!e.srcElement) 	var z=e.target; 	else 	z=e.srcElement;
		dragger.checkLanding(z,e.clientX, e.clientY );
	}
	
	dhtmlDragAndDropObject.prototype.calculateFramePosition=function(n){
		//this.fx = 0, this.fy = 0;
		if (window.name)  {
		  var el =parent.frames[window.name].frameElement.offsetParent;
		  var fx=0;
		  var fy=0;
		  while (el)	{      fx += el.offsetLeft;      fy += el.offsetTop;      el = el.offsetParent;   }
		  if 	((parent.dhtmlDragAndDrop))	{ 	 var ls=parent.dhtmlDragAndDrop.calculateFramePosition(1);  fx+=ls.split('_')[0]*1;  fy+=ls.split('_')[1]*1;  }
		  if (n) return fx+"_"+fy;
		  else this.fx=fx; this.fy=fy;
		  }
		  return "0_0";
	}
	dhtmlDragAndDropObject.prototype.checkLanding=function(htmlObject,x,y){

		if ((htmlObject)&&(htmlObject.dragLanding)) { if (this.lastLanding) this.lastLanding.dragLanding._dragOut(this.lastLanding);
										 this.lastLanding=htmlObject; this.lastLanding=this.lastLanding.dragLanding._dragIn(this.lastLanding,this.dragStartNode,x,y); }
		else {
			 if ((htmlObject)&&(htmlObject.tagName!="BODY")) this.checkLanding(htmlObject.parentNode,x,y);
			 else  {
			 	 if (this.lastLanding) this.lastLanding.dragLanding._dragOut(this.lastLanding,x,y); this.lastLanding=0;
				 if (this._onNotFound) this._onNotFound();
				 }
			 }
	}
	dhtmlDragAndDropObject.prototype.stopDrag=function(e,mode){
		dragger=window.dhtmlDragAndDrop;
		if (!mode)
			{
			  dragger.stopFrameRoute();
              var temp=dragger.lastLanding;
        	  dragger.lastLanding=null;
			  if (temp) temp.dragLanding._drag(dragger.dragStartNode,dragger.dragStartObject,temp);
			}
        dragger.lastLanding=null;
		if ((dragger.dragNode)&&(dragger.dragNode.parentNode==document.body)) dragger.dragNode.parentNode.removeChild(dragger.dragNode);
		dragger.dragNode=0;
		dragger.gldragNode=0;
		dragger.fx=0;
		dragger.fy=0;
		dragger.dragStartNode=0;
		dragger.dragStartObject=0;
		document.body.onmouseup=dragger.tempDOMU;
		document.body.onmousemove=dragger.tempDOMM;
		dragger.tempDOMU=null;
		dragger.tempDOMM=null;
		dragger.waitDrag=0;
	}	
	
	dhtmlDragAndDropObject.prototype.stopFrameRoute=function(win){
		if (win)
			window.dhtmlDragAndDrop.stopDrag(1,1);
				
		for (var i=0; i<window.frames.length; i++)
			if ((window.frames[i]!=win)&&(window.frames[i].dhtmlDragAndDrop))
				window.frames[i].dhtmlDragAndDrop.stopFrameRoute(window);
		if ((parent.dhtmlDragAndDrop)&&(parent!=window)&&(parent!=win)) 
				parent.dhtmlDragAndDrop.stopFrameRoute(window);	
	}
	dhtmlDragAndDropObject.prototype.initFrameRoute=function(win,mode){
		if (win)	{


        			    window.dhtmlDragAndDrop.preCreateDragCopy();
					window.dhtmlDragAndDrop.dragStartNode=win.dhtmlDragAndDrop.dragStartNode;
					window.dhtmlDragAndDrop.dragStartObject=win.dhtmlDragAndDrop.dragStartObject;
					window.dhtmlDragAndDrop.dragNode=win.dhtmlDragAndDrop.dragNode;
					window.dhtmlDragAndDrop.gldragNode=win.dhtmlDragAndDrop.dragNode;
					window.document.body.onmouseup=window.dhtmlDragAndDrop.stopDrag;
					window.waitDrag=0;
					if (((!_isIE)&&(mode))&&((!_isFF)||(_FFrv<1.8)))
                         window.dhtmlDragAndDrop.calculateFramePosition();
				}
	if ((parent.dhtmlDragAndDrop)&&(parent!=window)&&(parent!=win))
				parent.dhtmlDragAndDrop.initFrameRoute(window);
		for (var i=0; i<window.frames.length; i++)
			if ((window.frames[i]!=win)&&(window.frames[i].dhtmlDragAndDrop))
				window.frames[i].dhtmlDragAndDrop.initFrameRoute(window,((!win||mode)?1:0));

	}

var _isFF=false; var _isIE=false; var _isOpera=false; var _isKHTML=false; var _isMacOS=false;

if (navigator.userAgent.indexOf('Macintosh') != -1) _isMacOS=true;
if ((navigator.userAgent.indexOf('Safari') != -1)||(navigator.userAgent.indexOf('Konqueror')!= -1))
    _isKHTML=true;
else if (navigator.userAgent.indexOf('Opera') != -1){
    _isOpera=true;
    _OperaRv=parseFloat(navigator.userAgent.substr(navigator.userAgent.indexOf('Opera')+6,3));
    }
else if(navigator.appName.indexOf("Microsoft")!=-1)
    _isIE=true;
else {
    _isFF=true;
    var _FFrv=parseFloat(navigator.userAgent.split("rv:")[1])
    }

//deprecated, use global constant instead
//determines if current browser is IE
function isIE(){
	if(navigator.appName.indexOf("Microsoft")!=-1)
        if (navigator.userAgent.indexOf('Opera') == -1)
    		return true;
	return false;
}

//multibrowser Xpath processor
dtmlXMLLoaderObject.prototype.doXPath = function(xpathExp,docObj){  
    if ((_isOpera)||(_isKHTML)) return this.doXPathOpera(xpathExp,docObj);
	if(_isIE){//IE
		if(!docObj)
			if(!this.xmlDoc.nodeName)
				docObj = this.xmlDoc.responseXML
			else
				docObj = this.xmlDoc;
		return docObj.selectNodes(xpathExp);
	}else{//Mozilla
		var nodeObj = docObj;
		if(!docObj){
			if(!this.xmlDoc.nodeName){
			docObj = this.xmlDoc.responseXML
			}else{
			docObj = this.xmlDoc;
			}
		}
		if(docObj.nodeName.indexOf("document")!=-1){
			nodeObj = docObj;
		}else{
			nodeObj = docObj;
			docObj = docObj.ownerDocument;

		}
		var rowsCol = new Array();
    		var col = docObj.evaluate(xpathExp, nodeObj, null, XPathResult.ANY_TYPE,null);
    		var thisColMemb = col.iterateNext();
	    	while (thisColMemb) {
		    	rowsCol[rowsCol.length] = thisColMemb;
			    thisColMemb = col.iterateNext();
    		}
	    	return rowsCol;
	}
}
   
if  (( window.Node )&&(!_isKHTML))
Node.prototype.removeNode = function( removeChildren )
{
	var self = this;
	if ( Boolean( removeChildren ) )
	{
		return this.parentNode.removeChild( self );
	}
	else
	{
		var range = document.createRange();
		range.selectNodeContents( self );
		return this.parentNode.replaceChild( range.extractContents(), self );
	}
}

function _dhtmlxError(type,name,params){
    if (!this.catches)
        this.catches=new Array();

    return this;
}

_dhtmlxError.prototype.catchError=function(type,func_name){
    this.catches[type]=func_name;
}
_dhtmlxError.prototype.throwError=function(type,name,params){
        if (this.catches[type]) return  this.catches[type](type,name,params);
        if (this.catches["ALL"]) return  this.catches["ALL"](type,name,params);
        alert("Error type: " + arguments[0]+"\nDescription: " + arguments[1] );
        return null;
}

window.dhtmlxError=new  _dhtmlxError();


//opera fake, while 9.0 not released
//multibrowser Xpath processor
dtmlXMLLoaderObject.prototype.doXPathOpera = function(xpathExp,docObj){
    //this is fake for Opera
    var z=xpathExp.replace(/[\/]+/gi,"/").split('/');
    var obj=null;
    var i=1;

    if (!z.length) return [];
    if (z[0]==".")
        obj=[docObj];
    else if (z[0]=="")
        {
        obj=this.xmlDoc.responseXML.getElementsByTagName(z[i].replace(/\[[^\]]*\]/g,""));
        i++;
        }
    else return [];

    for (i; i<z.length; i++)
        obj=this._getAllNamedChilds(obj,z[i]);

    if (z[i-1].indexOf("[")!=-1)
        obj=this._filterXPath(obj,z[i-1]);
    return obj;
}

dtmlXMLLoaderObject.prototype._filterXPath = function(a,b){
    var c=new Array();
    var b=b.replace(/[^\[]*\[\@/g,"").replace(/[\[\]\@]*/g,"");
    for (var i=0; i<a.length; i++)
        if (a[i].getAttribute(b))
            c[c.length]=a[i];

    return c;
}
dtmlXMLLoaderObject.prototype._getAllNamedChilds = function(a,b){
    var c=new Array();
    for (var i=0; i<a.length; i++)
        for (var j=0; j<a[i].childNodes.length; j++)
            if (a[i].childNodes[j].tagName==b) c[c.length]=a[i].childNodes[j];

    return c;
}

function dhtmlXHeir(a,b){
	for (c in b)
		if (typeof(b[c])=="function") a[c]=b[c];
	return a;
}
function dhtmlxEvent(el,event,handler){
    if (el.addEventListener)
		el.addEventListener(event,handler,false);
	else if (el.attachEvent)
		el.attachEvent("on"+event,handler);
}



/***************************************************
 * library\dhtmlxtree\dhtmlXTree.js
 ***************************************************/

/*
Copyright Scand LLC http://www.scbr.com
To use this component please contact info@scbr.com to obtain license
*/

/*_TOPICS_
@0:Initialization
@1:Selection control
@2:Add/delete
@3:Private
@4:Node/level control
@5:Checkboxes/user data manipulation
@6:Appearence control
@7:Event Handlers
*/

/**
*     @desc: tree constructor
*     @param: htmlObject - parent html object or id of parent html object
*     @param: width - tree width
*     @param: height - tree height
*     @param: rootId - id of virtual root node
*     @type: public
*     @topic: 0
*/
function dhtmlXTreeObject(htmlObject, width, height, rootId){
	if (_isIE) try { document.execCommand("BackgroundImageCache", false, true); } catch (e){}
	if (typeof(htmlObject)!="object")
      this.parentObject=document.getElementById(htmlObject);
	else
      this.parentObject=htmlObject;

   	this._itim_dg=true;
    this.dlmtr=",";
    this.dropLower=false;
   this.xmlstate=0;
   this.mytype="tree";
   this.smcheck=true;   //smart checkboxes
   this.width=width;
   this.height=height;
   this.rootId=rootId;
   this.childCalc=null;
      this.def_img_x="18px";
      this.def_img_y="18px";

    this._dragged=new Array();
   this._selected=new Array();

   this.style_pointer="pointer";
   if (navigator.appName == 'Microsoft Internet Explorer')  this.style_pointer="hand";

   this._aimgs=true;
   this.htmlcA=" [";
   this.htmlcB="]";
   this.lWin=window;
   this.cMenu=0;
   this.mlitems=0;
   this.dadmode=0;
   this.slowParse=false;
   this.autoScroll=true;
   this.hfMode=0;
   this.nodeCut=new Array();
   this.XMLsource=0;
   this.XMLloadingWarning=0;
   this._globalIdStorage=new Array();
   this.globalNodeStorage=new Array();
   this._globalIdStorageSize=0;
   this.treeLinesOn=true;
   this.checkFuncHandler=0;
   this._spnFH=0;
   this.dblclickFuncHandler=0;
   this.tscheck=false;
   this.timgen=true;

   this.dpcpy=false;
    this._ld_id=null;

   this.imPath="treeGfx/";
   this.checkArray=new Array("iconUnCheckAll.gif","iconCheckAll.gif","iconCheckGray.gif","iconUncheckDis.gif","iconCheckDis.gif","iconCheckDis.gif");
   this.radioArray=new Array("radio_off.gif","radio_on.gif","radio_on.gif","radio_off.gif","radio_on.gif","radio_on.gif");

   this.lineArray=new Array("line2.gif","line3.gif","line4.gif","blank.gif","blank.gif","line1.gif");
   this.minusArray=new Array("minus2.gif","minus3.gif","minus4.gif","minus.gif","minus5.gif");
   this.plusArray=new Array("plus2.gif","plus3.gif","plus4.gif","plus.gif","plus5.gif");
   this.imageArray=new Array("leaf.gif","folderOpen.gif","folderClosed.gif");
   this.cutImg= new Array(0,0,0);
   this.cutImage="but_cut.gif";

   this.dragger= new dhtmlDragAndDropObject();
//create root
   this.htmlNode=new dhtmlXTreeItemObject(this.rootId,"",0,this);
   this.htmlNode.htmlNode.childNodes[0].childNodes[0].style.display="none";
   this.htmlNode.htmlNode.childNodes[0].childNodes[0].childNodes[0].className="hiddenRow";
//init tree structures
   this.allTree=this._createSelf();
   this.allTree.appendChild(this.htmlNode.htmlNode);
    if(_isFF) this.allTree.childNodes[0].width="100%";

   this.allTree.onselectstart=new Function("return false;");
   this.XMLLoader=new dtmlXMLLoaderObject(this._parseXMLTree,this,true,this.no_cashe);
   if (_isIE) this.preventIECashing(true);

//#__pro_feature:01112006{
//#complex_move:01112006{
   this.selectionBar=document.createElement("DIV");
   this.selectionBar.className="selectionBar";
   this.selectionBar.innerHTML="&nbsp;";
   this.selectionBar.style.display="none";
   this.allTree.appendChild(this.selectionBar);
//#}
//#}

    var self=this;
    if (window.addEventListener) window.addEventListener("unload",function(){try{  self.destructor(); } catch(e){}},false);
    if (window.attachEvent) window.attachEvent("onunload",function(){ try{ self.destructor(); } catch(e){}});

   return this;
};

dhtmlXTreeObject.prototype.destructor=function(){
    for (var i=0; i<this._globalIdStorageSize; i++){
        var z=this.globalNodeStorage[i];
        z.parentObject=null;z.treeNod=null;z.childNodes=null;z.span=null;z.tr.nodem=null;z.tr=null;z.htmlNode.objBelong=null;z.htmlNode=null;
        this.globalNodeStorage[i]=null;
        }
    this.allTree.innerHTML="";
    this.XMLLoader.destructor();
    for(var a in this){
        this[a]=null;
        }
}

function cObject(){
    return this;
}
cObject.prototype= new Object;
cObject.prototype.clone = function () {
       function _dummy(){};
       _dummy.prototype=this;
       return new _dummy();
    }

/**
*   @desc: tree node constructor
*   @param: itemId - node id
*   @param: itemText - node label
*   @param: parentObject - parent item object
*   @param: treeObject - tree object
*   @param: actionHandler - onclick event handler(optional)
*   @param: mode - do not show images
*   @type: private
*   @topic: 0
*/
function dhtmlXTreeItemObject(itemId,itemText,parentObject,treeObject,actionHandler,mode){
   this.htmlNode="";
   this.acolor="";
   this.scolor="";
   this.tr=0;
   this.childsCount=0;
   this.tempDOMM=0;
   this.tempDOMU=0;
   this.dragSpan=0;
   this.dragMove=0;
   this.span=0;
   this.closeble=1;
   this.childNodes=new Array();
   this.userData=new cObject();


   this.checkstate=0;
   this.treeNod=treeObject;
   this.label=itemText;
   this.parentObject=parentObject;
   this.actionHandler=actionHandler;
   this.images=new Array(treeObject.imageArray[0],treeObject.imageArray[1],treeObject.imageArray[2]);


   this.id=treeObject._globalIdStorageAdd(itemId,this);
   if (this.treeNod.checkBoxOff ) this.htmlNode=this.treeNod._createItem(1,this,mode);
   else  this.htmlNode=this.treeNod._createItem(0,this,mode);
      
   this.htmlNode.objBelong=this;
   return this;
   };   


/**
*     @desc: register node
*     @type: private
*     @param: itemId - node id
*     @param: itemObject - node object
*     @topic: 3  
*/
   dhtmlXTreeObject.prototype._globalIdStorageAdd=function(itemId,itemObject){
      if (this._globalIdStorageFind(itemId,1,1)) {     d=new Date(); itemId=d.valueOf()+"_"+itemId; return this._globalIdStorageAdd(itemId,itemObject); }
         this._globalIdStorage[this._globalIdStorageSize]=itemId;
         this.globalNodeStorage[this._globalIdStorageSize]=itemObject;
         this._globalIdStorageSize++;
      return itemId;
   };

/**
*     @desc: unregister node
*     @type: private
*     @param: itemId - node id
*     @topic: 3
*/
   dhtmlXTreeObject.prototype._globalIdStorageSub=function(itemId){
      for (var i=0; i<this._globalIdStorageSize; i++)
         if (this._globalIdStorage[i]==itemId)
            {
      this._globalIdStorage[i]=this._globalIdStorage[this._globalIdStorageSize-1];
      this.globalNodeStorage[i]=this.globalNodeStorage[this._globalIdStorageSize-1];
      this._globalIdStorageSize--;
      this._globalIdStorage[this._globalIdStorageSize]=0;
      this.globalNodeStorage[this._globalIdStorageSize]=0;
            }
   };
   
/**
*     @desc: return node object
*     @param: itemId - node id
*     @type: private
*     @topic: 3
*/
   dhtmlXTreeObject.prototype._globalIdStorageFind=function(itemId,skipXMLSearch,skipParsing,isreparse){
//   if (confirm(itemId)) { window.asdasd.asdasd(); }
      for (var i=0; i<this._globalIdStorageSize; i++)
         if (this._globalIdStorage[i]==itemId)
            {
//#__pro_feature:01112006{
//#smart_parsing:01112006{
            if ((this.globalNodeStorage[i].unParsed)&&(!skipParsing))
                    {
                    this.reParse(this.globalNodeStorage[i],0);
                    }
                if ((isreparse)&&(this._edsbpsA)){
                    for (var j=0; j<this._edsbpsA.length; j++)
                        if (this._edsbpsA[j][2]==itemId){
                            dhtmlxError.throwError("getItem","Requested item still in parsing process.",itemId);
                            return null;
                        }
                    }
//#}
//#}
            return this.globalNodeStorage[i];
            }
//#__pro_feature:01112006{
//#smart_parsing:01112006{
      if ((this.slowParse)&&(itemId!=0)&&(!skipXMLSearch)) return this.preParse(itemId);
      else
//#}
//#}
	  	return null;
   };
//#__pro_feature:01112006{
//#smart_parsing:01112006{

    dhtmlXTreeObject.prototype._getSubItemsXML=function(temp){
      var z="";
        for (var i=0; i<temp.childNodes.length; i++)
        {
            if (temp.childNodes[i].tagName=="item")
            {
         if (!z) z=temp.childNodes[i].getAttribute("id");
            else z+=this.dlmtr+temp.childNodes[i].getAttribute("id");
            }
        }
        return z;
    }

/**
*     @desc: enable/disable smart XML parsing mode (usefull for big, well structured XML)
*     @beforeInit: 1
*     @param: mode - 1 - on, 0 - off;
*     @type: public
*     @edition: Professional
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableSmartXMLParsing=function(mode) { this.slowParse=convertStringToBoolean(mode); };

/**
*     @desc: search data in xml
*     @param: node - XML node
*     @param: par - attribute name
*     @param: val - attribute value
*     @type: private
*     @edition: Professional
*     @topic: 3
*/
   dhtmlXTreeObject.prototype.findXML=function(node,par,val){

      for (var i=0; i<node.childNodes.length; i++)
            if (node.childNodes[i].nodeType==1)
            {
               if (node.childNodes[i].getAttribute(par)==val)
                  return node;
                  var z=this.findXML(node.childNodes[i],par,val);
                  if (z) return (z);
            }
      return false;
   }

dhtmlXTreeObject.prototype._getAllCheckedXML=function(htmlNode,list,mode){
      var j=htmlNode.childNodes.length;

      for (var i=0; i<j; i++)
      {
            var tNode=htmlNode.childNodes[i];
            if (tNode.tagName=="item")
            {
            var z=tNode.getAttribute("checked");

            var flag=false;

                if (mode==2){
                     if (z=="-1")
                        flag=true;
                }
                else
                if (mode==1){
                    if ((z)&&(z!="0")&&(z!="-1"))
                        flag=true;
                }
                else
                if (mode==0){
                    if ((!z)||(z=="0"))
                        flag=true;
                }

                if (flag)
                    {
                 if (list) list+=this.dlmtr+tNode.getAttribute("id");
                    else list=tNode.getAttribute("id");
                    }
         list=this._getAllCheckedXML(tNode,list,mode);
            }
      };

      if (list) return list; else return "";
   };


/**
*     @desc: change state of node's checkbox and all childnodes checkboxes
*     @type: private
*     @param: itemId - target node id
*     @param: state - checkbox state
*     @param: sNode - target node object (optional, used by private methods)
*     @topic: 5
*/
dhtmlXTreeObject.prototype._setSubCheckedXML=function(state,sNode){
   if (!sNode) return;
    if (!_isOpera){
        var val= state?"1":"";
        var z=this.XMLLoader.doXPath(".//item",sNode);
        for (var i=0; i<z.length; i++)
            z[i].setAttribute("checked",val);
        }
    else
    for (var i=0; i<sNode.childNodes.length; i++){
      var tag=sNode.childNodes[i];
      if ((tag)&&(tag.tagName=="item")) {
         if (state) tag.setAttribute("checked",1);
         else  tag.setAttribute("checked","");
         this._setSubCheckedXML(state,tag);
         }
      }
}

       dhtmlXTreeObject.prototype._getAllScraggyItemsXML=function(node,x){
        var z="";
        var flag=false;
        for (var i=0; i<node.childNodes.length; i++)
            if ((node.childNodes[i].tagName=="item")){
                flag=true;
                var zb=this._getAllScraggyItemsXML(node.childNodes[i],0);
                if (zb!="")
                    if (z)
                        z+=this.dlmtr+zb;
                    else
                        z=zb;
                }
        if ((!x)&&(!flag))
            if (z)
                z+=this.dlmtr+ node.getAttribute("id");
            else z=node.getAttribute("id");

        return z;
    }
    dhtmlXTreeObject.prototype._getAllFatItemsXML=function(node,x){
        var z="";
        var flag=false;
        for (var i=0; i<node.childNodes.length; i++)
            if ((node.childNodes[i].tagName=="item")){
                flag=true;
                var zb=this._getAllFatItemsXML(node.childNodes[i],0);
                if (zb!="")
                    if (z)
                        z+=this.dlmtr+zb;
                    else
                        z=zb;
                }
        if ((!x)&&(flag))
            if (z)
                z+=this.dlmtr+ node.getAttribute("id");
            else z=node.getAttribute("id");

        return z;
    }

    dhtmlXTreeObject.prototype._getAllSubItemsXML=function(itemId,z,node){
        for (var i=0; i<node.childNodes.length; i++)
            if (node.childNodes[i].tagName=="item"){
                if (!z) z=node.childNodes[i].getAttribute("id");
                else    z+=this.dlmtr+ node.childNodes[i].getAttribute("id");
                z=this._getAllSubItemsXML(itemId,z,node.childNodes[i]);
                }
        return z;
    }

/**
*     @desc: parse stored xml
*     @param: node - XML node
*     @type: private
*     @edition: Professional
*     @topic: 3  
*/
   dhtmlXTreeObject.prototype.reParse=function(node){
        var that=this;
      if ((this.onXLS)&&(!this.parsCount)) that.onXLS(that,node.id);
      this.xmlstate=1;

      var tmp=node.unParsed;
      node.unParsed=0;
//               if (confirm("reParse "+node.id)) { window.asdasd.asdasd(); }
      this.XMLloadingWarning=1;
        var oldpid=this.parsingOn;
      this.parsingOn=node.id;
      this.parsedArray=new Array();

         this.setCheckList="";
         this._parseXMLTree(this,tmp,node.id,2,node);
         var chArr=this.setCheckList.split(this.dlmtr);

      for (var i=0; i<this.parsedArray.length; i++)
         node.htmlNode.childNodes[0].appendChild(this.parsedArray[i]);

            this.oldsmcheck=this.smcheck;
            this.smcheck=false;

         for (var n=0; n<chArr.length; n++)
            if (chArr[n])  this.setCheck(chArr[n],1);
            this.smcheck=this.oldsmcheck;

      this.parsingOn=oldpid;
      this.XMLloadingWarning=0;
      this._redrawFrom(this,node);
      return true;
   }

/**
*     @desc: search for item in unparsed chunks
*     @param: itemId - item ID
*     @type: private
*     @edition: Professional
*     @topic: 3
*/
   dhtmlXTreeObject.prototype.preParse=function(itemId){
   if (!itemId) return null;
      var z=this.XMLLoader.getXMLTopNode("tree");
      var i=0;
      var k=0;

            if (!z) return;
         for (i=0; i<z.childNodes.length; i++)
            if (z.childNodes[i].nodeType==1)
               {
                 var zNode=this.findXML(z.childNodes[i],"id",itemId);
                 if (zNode!==false)
                 {
                        var nArr=new Array();
                        while (1){
                            nArr[nArr.length]=zNode.getAttribute("id");
                        z=this._globalIdStorageFind(zNode.getAttribute("id"),true,true,true);
                            if (z) break;
                            zNode=zNode.parentNode;
                        }
                        for (var i=nArr.length-1; i>=0; i--)
                             this._globalIdStorageFind(nArr[i],true,false);

                        z=this._globalIdStorageFind(itemId,true,false);
                        if (!z) dhtmlxError.throwError("getItem","The item "+itemId+" not operable. Seems you have non-unique IDs in tree's XML.",itemId);
                        return z;
                 }
                }

         return null;
   }

//#}
//#}

/**
*     @desc: escape string
*     @param: itemId - item ID
*     @type: private
*     @topic: 3
*/
   dhtmlXTreeObject.prototype._escape=function(str){
        switch(this.utfesc){
        case "none":
            return str;
            break;
        case "utf8":
         return encodeURI(str);
            break;
        default:
         return escape(str);
            break;
        }
   }



/**
*     @desc: create and return  new line in tree
*     @type: private
*     @param: htmlObject - parent Node object
*     @param: node - item object
*     @topic: 2  
*/
   dhtmlXTreeObject.prototype._drawNewTr=function(htmlObject,node)
   {
      var tr =document.createElement('tr');
      var td1=document.createElement('td');
      var td2=document.createElement('td');
      td1.appendChild(document.createTextNode(" "));
       td2.colSpan=3;
      td2.appendChild(htmlObject);
      tr.appendChild(td1);  tr.appendChild(td2);
      return tr;
   };
/**
*     @desc: load tree from xml string
*     @type: public
*     @param: xmlString - XML string
*     @param: afterCall - function which will be called after xml loading
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.loadXMLString=function(xmlString,afterCall){
        var that=this;
      if ((this.onXLS)&&(!this.parsCount)) that.onXLS(that,null);
      this.xmlstate=1;

        if (afterCall) this.XMLLoader.waitCall=afterCall;
      this.XMLLoader.loadXMLString(xmlString);  };
/**
*     @desc: load tree from xml file
*     @type: public
*     @param: file - link to XML file
*     @param: afterCall - function which will be called after xml loading
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.loadXML=function(file,afterCall){
        var that=this;
      if ((this.onXLS)&&(!this.parsCount)) that.onXLS(that,this._ld_id);
        this._ld_id=null;
      this.xmlstate=1;
       this.XMLLoader=new dtmlXMLLoaderObject(this._parseXMLTree,this,true,this.no_cashe);

        if (afterCall) this.XMLLoader.waitCall=afterCall;
      this.XMLLoader.loadXML(file);  };
/**
*     @desc: create new child node
*     @type: private
*     @param: parentObject - parent node object
*     @param: itemId - new node id
*     @param: itemText - new node text
*     @param: itemActionHandler - function fired on node select event
*     @param: image1 - image for node without childrens;
*     @param: image2 - image for closed node;
*     @param: image3 - image for opened node
*     @param: optionStr - string of otions
*     @param: childs - node childs flag (for dynamical trees) (optional)
*     @param: beforeNode - node, after which new node will be inserted (optional)
*     @topic: 2
*/
   dhtmlXTreeObject.prototype._attachChildNode=function(parentObject,itemId,itemText,itemActionHandler,image1,image2,image3,optionStr,childs,beforeNode,afterNode){

         if (beforeNode) parentObject=beforeNode.parentObject;
         if (((parentObject.XMLload==0)&&(this.XMLsource))&&(!this.XMLloadingWarning))
         {
            parentObject.XMLload=1;
                this._loadDynXML(parentObject.id);

         }

         var Count=parentObject.childsCount;
         var Nodes=parentObject.childNodes;


            if (afterNode){
            if (afterNode.tr.previousSibling.previousSibling){
               beforeNode=afterNode.tr.previousSibling.nodem;
               }
            else
               optionStr=optionStr.replace("TOP","")+",TOP";
               }

         if (beforeNode)
            {
            var ik,jk;
            for (ik=0; ik<Count; ik++)
               if (Nodes[ik]==beforeNode)
               {
               for (jk=Count; jk!=ik; jk--)
                  Nodes[1+jk]=Nodes[jk];
               break;
               }
            ik++;
            Count=ik;
            }


         if ((!itemActionHandler)&&(this.aFunc))   itemActionHandler=this.aFunc;

         if (optionStr) {
             var tempStr=optionStr.split(",");
            for (var i=0; i<tempStr.length; i++)
            {
               switch(tempStr[i])
               {
                  case "TOP": if (parentObject.childsCount>0) { beforeNode=new Object; beforeNode.tr=parentObject.childNodes[0].tr.previousSibling; }
				  	 parentObject._has_top=true;
                     for  (ik=Count; ik>0; ik--)
                        Nodes[ik]=Nodes[ik-1];
                        Count=0;
                     break;
               }
            };
          };

         Nodes[Count]=new dhtmlXTreeItemObject(itemId,itemText,parentObject,this,itemActionHandler,1);
		 itemId = Nodes[Count].id;

         if(image1) Nodes[Count].images[0]=image1;
         if(image2) Nodes[Count].images[1]=image2;
         if(image3) Nodes[Count].images[2]=image3;

         parentObject.childsCount++;
         var tr=this._drawNewTr(Nodes[Count].htmlNode);
         if ((this.XMLloadingWarning)||(this._hAdI))
            Nodes[Count].htmlNode.parentNode.parentNode.style.display="none";


            if ((beforeNode)&&(beforeNode.tr.nextSibling))
               parentObject.htmlNode.childNodes[0].insertBefore(tr,beforeNode.tr.nextSibling);
            else
               if (this.parsingOn==parentObject.id){
                  this.parsedArray[this.parsedArray.length]=tr;
                        }
               else
                   parentObject.htmlNode.childNodes[0].appendChild(tr);


               if ((beforeNode)&&(!beforeNode.span)) beforeNode=null;

            if (this.XMLsource) if ((childs)&&(childs!=0)) Nodes[Count].XMLload=0; else Nodes[Count].XMLload=1;
            Nodes[Count].tr=tr;
            tr.nodem=Nodes[Count];

            if (parentObject.itemId==0)
                tr.childNodes[0].className="hiddenRow";

            if ((parentObject._r_logic)||(this._frbtr))
                    Nodes[Count].htmlNode.childNodes[0].childNodes[0].childNodes[1].childNodes[0].src=this.imPath+this.radioArray[0];


          if (optionStr) {
             var tempStr=optionStr.split(",");

            for (var i=0; i<tempStr.length; i++)
            {
               switch(tempStr[i])
               {
                     case "SELECT": this.selectItem(itemId,false); break;
                  case "CALL": this.selectItem(itemId,true);   break;
                  case "CHILD":  Nodes[Count].XMLload=0;  break;
                  case "CHECKED":
                     if (this.XMLloadingWarning)
                        this.setCheckList+=this.dlmtr+itemId;
                     else
                        this.setCheck(itemId,1);
                        break;
                  case "HCHECKED":
                        this._setCheck(Nodes[Count],"unsure");
                        break;                        
                  case "OPEN": Nodes[Count].openMe=1;  break;
               }
            };
          };

      if (!this.XMLloadingWarning)
      {
             if ((this._getOpenState(parentObject)<0)&&(!this._hAdI)) this.openItem(parentObject.id);

             if (beforeNode)
                {
             this._correctPlus(beforeNode);
             this._correctLine(beforeNode);
                }
             this._correctPlus(parentObject);
             this._correctLine(parentObject);
             this._correctPlus(Nodes[Count]);
             if (parentObject.childsCount>=2)
             {
                   this._correctPlus(Nodes[parentObject.childsCount-2]);
                   this._correctLine(Nodes[parentObject.childsCount-2]);
             }
             if (parentObject.childsCount!=2) this._correctPlus(Nodes[0]);

         if (this.tscheck) this._correctCheckStates(parentObject);

            if (this._onradh) {
				if (this.xmlstate==1){
					var old=this.onXLE;
					this.onXLE=function(id){ this._onradh(itemId); if (old) old(id); }
					}
				else
					this._onradh(itemId);
			}

      }
//#__pro_feature:01112006{
//#context_menu:01112006{
      if (this.cMenu) this.cMenu.setContextZone(Nodes[Count].span,Nodes[Count].id);
//#}
//#}
   return Nodes[Count];
};


//#__pro_feature:01112006{
//#context_menu:01112006{

/**
*     @desc: enable context menu
*     @param: menu - dhtmlXmenu object
*     @edition: Professional
*     @type: public
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableContextMenu=function(menu){  if (menu) this.cMenu=menu; };

/**
*     @desc: set context menu to individual nodes
*     @type: public
*     @param: itemId - node id
*     @param: cMenu - context menu object
*     @edition: Professional
*     @topic: 2
*/
dhtmlXTreeObject.prototype.setItemContextMenu=function(itemId,cMenu){
   var l=itemId.split(this.dlmtr);
   for (var i=0; i<l.length; i++)
      {
      var temp=this._globalIdStorageFind(l[i]);
      if (!temp) continue;
      cMenu.setContextZone(temp.span,temp.id);
      }
}

//#}
//#}

/**
*     @desc: create new node as a child to specified with parentId
*     @type: deprecated
*     @param: parentId - parent node id
*     @param: itemId - new node id
*     @param: itemText - new node text
*     @param: itemActionHandler - function fired on node select event (optional)
*     @param: image1 - image for node without childrens; (optional)
*     @param: image2 - image for closed node; (optional)
*     @param: image3 - image for opened node (optional)
*     @param: optionStr - options string (optional)            
*     @param: childs - node childs flag (for dynamical trees) (optional)
*     @topic: 2  
*/
   dhtmlXTreeObject.prototype.insertNewItem=function(parentId,itemId,itemText,itemActionHandler,image1,image2,image3,optionStr,childs){
      var parentObject=this._globalIdStorageFind(parentId);
      if (!parentObject) return (-1);
      var nodez=this._attachChildNode(parentObject,itemId,itemText,itemActionHandler,image1,image2,image3,optionStr,childs);
//#__pro_feature:01112006{
//#child_calc:01112006{
      if ((!this.XMLloadingWarning)&&(this.childCalc))  this._fixChildCountLabel(parentObject);
//#}
//#}
        return nodez;
   };
/**
*     @desc: create new node as a child to specified with parentId
*     @type: public
*     @param: parentId - parent node id
*     @param: itemId - new node id
*     @param: itemText - new node label
*     @param: itemActionHandler - function fired on node select event (optional)
*     @param: image1 - image for node without childrens; (optional)
*     @param: image2 - image for closed node; (optional)
*     @param: image3 - image for opened node (optional)
*     @param: optionStr - options string (optional)            
*     @param: childs - node children flag (for dynamical trees) (optional)
*     @topic: 2  
*/
   dhtmlXTreeObject.prototype.insertNewChild=function(parentId,itemId,itemText,itemActionHandler,image1,image2,image3,optionStr,childs){
      return this.insertNewItem(parentId,itemId,itemText,itemActionHandler,image1,image2,image3,optionStr,childs);
   }   
/**  
*     @desc: parse xml
*     @type: private
*     @param: dhtmlObject - jsTree object
*     @param: node - top XML node
*     @param: parentId - parent node id
*     @param: level - level of tree
*     @topic: 2
*/
   dhtmlXTreeObject.prototype._parseXMLTree=function(dhtmlObject,node,parentId,level,xml_obj,start){
    if (!xml_obj) xml_obj=dhtmlObject.XMLLoader;
    dhtmlObject.skipLock=true;
      if (!dhtmlObject.parsCount)  dhtmlObject.parsCount=1; else dhtmlObject.parsCount++;
      dhtmlObject.XMLloadingWarning=1;
      var nodeAskingCall="";
      if (!node) {
         node=xml_obj.getXMLTopNode("tree");
         parentId=node.getAttribute("id");
            if (node.getAttribute("radio"))
                 dhtmlObject.htmlNode._r_logic=true;
         dhtmlObject.parsingOn=parentId;
         dhtmlObject.parsedArray=new Array();
         dhtmlObject.setCheckList="";
         }

      var temp=dhtmlObject._globalIdStorageFind(parentId);

        if ((temp.childsCount)&&(!start)&&(!dhtmlObject._edsbps)&&(!temp._has_top))
            var preNode=temp.childNodes[temp.childsCount-1];
        else
            var preNode=0;

      if (node.getAttribute("order"))
                 dhtmlObject._reorderXMLBranch(node);

        var npl=0;

      for(var i=start||0; i<node.childNodes.length; i++)
      {

           if ((node.childNodes[i].nodeType==1)&&(node.childNodes[i].tagName == "item"))
         {
                temp.XMLload=1;
                if ((dhtmlObject._epgps)&&(dhtmlObject._epgpsC==npl)){
                    this._setNextPageSign(temp,npl+1*(start||0),level,node);
                    break;
                }

            var nodx=node.childNodes[i];
            var name=nodx.getAttribute("text");
//#__pro_feature:01112006{
//#data_from_xml:01112006{
			if ((name===null)||(typeof(name)=="unknown"))
				for (var ci=0; ci<nodx.childNodes.length; ci++)
					if (nodx.childNodes[ci].tagName=="itemtext"){
						name=nodx.childNodes[ci].firstChild.data;
						break;
					}
//#}
//#}
              var cId=nodx.getAttribute("id");

              if ((typeof(dhtmlObject.waitUpdateXML)=="object")&&(!dhtmlObject.waitUpdateXML[cId])){
                dhtmlObject._parseXMLTree(dhtmlObject,node.childNodes[i],cId,1,xml_obj);
			  	continue;
				}

              var im0=nodx.getAttribute("im0");
              var im1=nodx.getAttribute("im1");
              var im2=nodx.getAttribute("im2");
            
              var aColor=nodx.getAttribute("aCol");
              var sColor=nodx.getAttribute("sCol");
            
              var chd=nodx.getAttribute("child");

            var imw=nodx.getAttribute("imwidth");
              var imh=nodx.getAttribute("imheight");

              var atop=nodx.getAttribute("top");
              var aradio=nodx.getAttribute("radio");
                var topoffset=nodx.getAttribute("topoffset");
              var aopen=nodx.getAttribute("open"); //can be disabled, because we have another code for open via Xpaths
              var aselect=nodx.getAttribute("select");
              var acall=nodx.getAttribute("call");
              var achecked=nodx.getAttribute("checked");
              var closeable=nodx.getAttribute("closeable");
            var tooltip = nodx.getAttribute("tooltip");
            var nocheckbox = nodx.getAttribute("nocheckbox");
            var disheckbox = nodx.getAttribute("disabled");
            var style = nodx.getAttribute("style");

            var locked = nodx.getAttribute("locked");

                  var zST="";
                  if (aselect) zST+=",SELECT";
                  if (atop) zST+=",TOP";
                  //if (acall) zST+=",CALL";
                  if (acall) nodeAskingCall=cId;

                  if (achecked==-1) zST+=",HCHECKED";
                     else if (achecked) zST+=",CHECKED";
                  if (aopen) zST+=",OPEN";

    	          if (dhtmlObject.waitUpdateXML){
				  		if (dhtmlObject._globalIdStorageFind(cId))
	    	            	var newNode=dhtmlObject.updateItem(cId,name,im0,im1,im2,achecked);
						else{
							if (npl==0) zST+=",TOP";
							else preNode=temp.childNodes[npl];

		                    var newNode=dhtmlObject._attachChildNode(temp,cId,name,0,im0,im1,im2,zST,chd,0,preNode);
							preNode=null;
						}
					 }
                  else
                     var newNode=dhtmlObject._attachChildNode(temp,cId,name,0,im0,im1,im2,zST,chd,0,preNode);
                  if (tooltip)
//#__pro_feature:01112006{
//#dhtmlxtootip:01112006{
				  	  if (dhtmlObject._dhxTT) dhtmlxTooltip.setTooltip(newNode.span.parentNode,tooltip);
					  else
//#}
//#}
					  	newNode.span.parentNode.title=tooltip;
                  if (style)
                            if (newNode.span.style.cssText)
                                newNode.span.style.cssText+=(";"+style);
                            else
                                newNode.span.setAttribute("style",newNode.span.getAttribute("style")+"; "+style);

                        if (aradio) newNode._r_logic=true;

                  if (nocheckbox){
                     newNode.span.parentNode.previousSibling.previousSibling.childNodes[0].style.display='none';
                     newNode.nocheckbox=true;
                  }
                        if (disheckbox){
                            if (achecked!=null) dhtmlObject._setCheck(newNode,convertStringToBoolean(achecked));
                            dhtmlObject.disableCheckbox(newNode,1);
                            }


                  newNode._acc=chd||0;

                  if (dhtmlObject.parserExtension) dhtmlObject.parserExtension._parseExtension(node.childNodes[i],dhtmlObject.parserExtension,cId,parentId);

                  dhtmlObject.setItemColor(newNode,aColor,sColor);
                        if (locked=="1")    dhtmlObject._lockItem(newNode,true,true);

                  if ((imw)||(imh))   dhtmlObject.setIconSize(imw,imh,newNode);
                  if ((closeable=="0")||(closeable=="1"))  dhtmlObject.setItemCloseable(newNode,closeable);
                  var zcall="";
                        if (topoffset) this.setItemTopOffset(newNode,topoffset);
                  if (!dhtmlObject.slowParse)
                    zcall=dhtmlObject._parseXMLTree(dhtmlObject,node.childNodes[i],cId,1,xml_obj);
//#__pro_feature:01112006{
//#smart_parsing:01112006{
                  else{
                   if (node.childNodes[i].childNodes.length>0) {
                      for (var a=0; a<node.childNodes[i].childNodes.length; a++)
                        if (node.childNodes[i].childNodes[a].tagName=="item")
                           newNode.unParsed=node.childNodes[i];
                                else
                                    dhtmlObject.checkUserData(node.childNodes[i].childNodes[a],newNode.id);
                     }
                   }
//#}
//#}

                  if (zcall!="") nodeAskingCall=zcall;




//#__pro_feature:01112006{
//#distributed_load:01112006{
              if ((dhtmlObject._edsbps)&&(npl==dhtmlObject._edsbpsC)){
                dhtmlObject._distributedStart(node,i+1,parentId,level,temp.childsCount);
                break;
              }
//#}
//#}
              npl++;
         }
         else
                 dhtmlObject.checkUserData(node.childNodes[i],parentId);
      };

      if (!level) {
         if (dhtmlObject.waitUpdateXML){
            dhtmlObject.waitUpdateXML=false;
			for (var i=temp.childsCount-1; i>=0; i--)
				if (temp.childNodes[i]._dmark)
					dhtmlObject.deleteItem(temp.childNodes[i].id);
			}

         var parsedNodeTop=dhtmlObject._globalIdStorageFind(dhtmlObject.parsingOn);

         for (var i=0; i<dhtmlObject.parsedArray.length; i++)
               parsedNodeTop.htmlNode.childNodes[0].appendChild(dhtmlObject.parsedArray[i]);

         dhtmlObject.lastLoadedXMLId=parentId;
         dhtmlObject.XMLloadingWarning=0;

         var chArr=dhtmlObject.setCheckList.split(dhtmlObject.dlmtr);
         for (var n=0; n<chArr.length; n++)
            if (chArr[n]) dhtmlObject.setCheck(chArr[n],1);

               if ((dhtmlObject.XMLsource)&&(dhtmlObject.tscheck)&&(dhtmlObject.smcheck)&&(temp.id!=dhtmlObject.rootId)){
                if (temp.checkstate===0)
                    dhtmlObject._setSubChecked(0,temp);
                else if (temp.checkstate===1)
                    dhtmlObject._setSubChecked(1,temp);
            }


         //special realization for IE 5.5 (should avoid IE crash for autoloading. In other cases probably will not help)
         if(navigator.appVersion.indexOf("MSIE")!=-1 && navigator.appVersion.indexOf("5.5")!=-1){
            window.setTimeout(function(){dhtmlObject._redrawFrom(dhtmlObject,null,start)},10);
         }else{
            dhtmlObject._redrawFrom(dhtmlObject,null,start)
         }

         if (nodeAskingCall!="")   dhtmlObject.selectItem(nodeAskingCall,true);

      }


      if (dhtmlObject.parsCount==1) {
//#__pro_feature:01112006{
//#smart_parsing:01112006{
          if ((dhtmlObject.slowParse)&&(dhtmlObject.parsingOn==dhtmlObject.rootId))
            {
            var nodelist=xml_obj.doXPath("//item[@open]",xml_obj.xmlDoc.responseXML);
            for (var i=0; i<nodelist.length; i++)
                dhtmlObject.openItem(nodelist[i].getAttribute("id"));
            }
//#}
//#}
         dhtmlObject.parsingOn=null;
         if ((!dhtmlObject._edsbps)||(!dhtmlObject._edsbpsA.length)){
                if (dhtmlObject.onXLE)
                 window.setTimeout( function(){dhtmlObject.onXLE(dhtmlObject,parentId)},1);
                dhtmlObject.xmlstate=0;
                }
             dhtmlObject.skipLock=false;
         }
      dhtmlObject.parsCount--;

//#__pro_feature:01112006{
//#distributed_load:01112006{
        if (dhtmlObject._edsbps) window.setTimeout(function(){ dhtmlObject._distributedStep(parentId); },dhtmlObject._edsbpsD);
//#}
//#}

        if ((dhtmlObject._epgps)&&(start))
            this._setPrevPageSign(temp,(start||0),level,node);

      return nodeAskingCall;
   };


  dhtmlXTreeObject.prototype.checkUserData=function(node,parentId){
      if ((node.nodeType==1)&&(node.tagName == "userdata"))
      {
         var name=node.getAttribute("name");
            if ((name)&&(node.childNodes[0]))
               this.setUserData(parentId,name,node.childNodes[0].data);
      }
  }




/**  
*     @desc: reset tree images from selected level
*     @type: private
*     @param: dhtmlObject - tree
*     @param: itemObject - current item
*     @topic: 6
*/
   dhtmlXTreeObject.prototype._redrawFrom=function(dhtmlObject,itemObject,start,visMode){
      if (!itemObject) {
      var tempx=dhtmlObject._globalIdStorageFind(dhtmlObject.lastLoadedXMLId);
      dhtmlObject.lastLoadedXMLId=-1;
      if (!tempx) return 0;
      }
      else tempx=itemObject;
      var acc=0;

      for (var i=(start?start-1:0); i<tempx.childsCount; i++)
      {
         if ((!itemObject)||(visMode==1)) tempx.childNodes[i].htmlNode.parentNode.parentNode.style.display="";
         if (tempx.childNodes[i].openMe==1)
            {
            this._openItem(tempx.childNodes[i]);
            tempx.childNodes[i].openMe=0;
            }

         dhtmlObject._redrawFrom(dhtmlObject,tempx.childNodes[i]);
//#__pro_feature:01112006{
//#child_calc:01112006{
      if (this.childCalc!=null){

      if ((tempx.childNodes[i].unParsed)||((!tempx.childNodes[i].XMLload)&&(this.XMLsource)))
      {

         if (tempx.childNodes[i]._acc)
         tempx.childNodes[i].span.innerHTML=tempx.childNodes[i].label+this.htmlcA+tempx.childNodes[i]._acc+this.htmlcB;
         else
         tempx.childNodes[i].span.innerHTML=tempx.childNodes[i].label;
      }
         if ((tempx.childNodes[i].childNodes.length)&&(this.childCalc))
         {
            if (this.childCalc==1)
               {
               tempx.childNodes[i].span.innerHTML=tempx.childNodes[i].label+this.htmlcA+tempx.childNodes[i].childsCount+this.htmlcB;
               }
            if (this.childCalc==2)
               {
               var zCount=tempx.childNodes[i].childsCount-(tempx.childNodes[i].pureChilds||0);
               if (zCount)
                  tempx.childNodes[i].span.innerHTML=tempx.childNodes[i].label+this.htmlcA+zCount+this.htmlcB;
               if (tempx.pureChilds) tempx.pureChilds++; else tempx.pureChilds=1;
               }
            if (this.childCalc==3)
               {
               tempx.childNodes[i].span.innerHTML=tempx.childNodes[i].label+this.htmlcA+tempx.childNodes[i]._acc+this.htmlcB;
               }
            if (this.childCalc==4)
               {
               var zCount=tempx.childNodes[i]._acc;
               if (zCount)
                  tempx.childNodes[i].span.innerHTML=tempx.childNodes[i].label+this.htmlcA+zCount+this.htmlcB;
               }               
         }
            else if (this.childCalc==4)   {
               acc++;
               }   
            
         acc+=tempx.childNodes[i]._acc;
         
         if (this.childCalc==3){
            acc++;
         }

         }
//#}
//#}

      };

      if ((!tempx.unParsed)&&((tempx.XMLload)||(!this.XMLsource)))
      tempx._acc=acc;
      dhtmlObject._correctLine(tempx);
      dhtmlObject._correctPlus(tempx);
//#__pro_feature:01112006{
//#child_calc:01112006{
      if ((this.childCalc)&&(!itemObject)) dhtmlObject._fixChildCountLabel(tempx);
//#}
//#}
   };

/**
*     @desc: create and return main html element of tree
*     @type: private
*     @topic: 0  
*/
   dhtmlXTreeObject.prototype._createSelf=function(){
      var div=document.createElement('div');
      div.className="containerTableStyle";
      div.style.width=this.width;
      div.style.height=this.height;
      this.parentObject.appendChild(div);
      return div;
   };

/**
*     @desc: collapse target node
*     @type: private
*     @param: itemObject - item object
*     @topic: 4  
*/
   dhtmlXTreeObject.prototype._xcloseAll=function(itemObject)
   {
        if (itemObject.unParsed) return;
      if (this.rootId!=itemObject.id) {
          var Nodes=itemObject.htmlNode.childNodes[0].childNodes;
            var Count=Nodes.length;

          for (var i=1; i<Count; i++)
             Nodes[i].style.display="none";

          this._correctPlus(itemObject);
      }

       for (var i=0; i<itemObject.childsCount; i++)
            if (itemObject.childNodes[i].childsCount)
             this._xcloseAll(itemObject.childNodes[i]);
   };
/**
*     @desc: expand target node
*     @type: private
*     @param: itemObject - item object
*     @topic: 4
*/      
   dhtmlXTreeObject.prototype._xopenAll=function(itemObject)
   {
      this._HideShow(itemObject,2);
      for (var i=0; i<itemObject.childsCount; i++)
         this._xopenAll(itemObject.childNodes[i]);
   };      
/**  
*     @desc: set correct tree-line and node images
*     @type: private
*     @param: itemObject - item object
*     @topic: 6  
*/
   dhtmlXTreeObject.prototype._correctPlus=function(itemObject){
        var imsrc=itemObject.htmlNode.childNodes[0].childNodes[0].childNodes[0].lastChild;
        var imsrc2=itemObject.htmlNode.childNodes[0].childNodes[0].childNodes[2].childNodes[0];

       var workArray=this.lineArray;
      if ((this.XMLsource)&&(!itemObject.XMLload))
      {
            var workArray=this.plusArray;
            imsrc2.src=this.imPath+itemObject.images[2];
                if (this._txtimg) return (imsrc.innerHTML="[+]");
      }
      else
      if ((itemObject.childsCount)||(itemObject.unParsed))
      {
         if ((itemObject.htmlNode.childNodes[0].childNodes[1])&&( itemObject.htmlNode.childNodes[0].childNodes[1].style.display!="none" ))
            {
            if (!itemObject.wsign) var workArray=this.minusArray;
            imsrc2.src=this.imPath+itemObject.images[1];
                if (this._txtimg) return (imsrc.innerHTML="[-]");
            }
         else
            {
            if (!itemObject.wsign) var workArray=this.plusArray;
            imsrc2.src=this.imPath+itemObject.images[2];
                if (this._txtimg) return (imsrc.innerHTML="[+]");
            }
      }
      else
      {
         imsrc2.src=this.imPath+itemObject.images[0];
       }


      var tempNum=2;
      if (!itemObject.treeNod.treeLinesOn) imsrc.src=this.imPath+workArray[3];
      else {
          if (itemObject.parentObject) tempNum=this._getCountStatus(itemObject.id,itemObject.parentObject);
         imsrc.src=this.imPath+workArray[tempNum];
         }
   };

/**
*     @desc: set correct tree-line images
*     @type: private
*     @param: itemObject - item object
*     @topic: 6
*/
   dhtmlXTreeObject.prototype._correctLine=function(itemObject){
      var sNode=itemObject.parentObject;
      if (sNode)
         if ((this._getLineStatus(itemObject.id,sNode)==0)||(!this.treeLinesOn))
               for(var i=1; i<=itemObject.childsCount; i++)
                                 {
                  itemObject.htmlNode.childNodes[0].childNodes[i].childNodes[0].style.backgroundImage="";
                   itemObject.htmlNode.childNodes[0].childNodes[i].childNodes[0].style.backgroundRepeat="";
                                  }
            else
               for(var i=1; i<=itemObject.childsCount; i++)
                           {
                itemObject.htmlNode.childNodes[0].childNodes[i].childNodes[0].style.backgroundImage="url("+this.imPath+this.lineArray[5]+")";
               itemObject.htmlNode.childNodes[0].childNodes[i].childNodes[0].style.backgroundRepeat="repeat-y";
                   }
   };
/**
*     @desc: return type of node
*     @type: private
*     @param: itemId - item id
*     @param: itemObject - parent node object
*     @topic: 6
*/
   dhtmlXTreeObject.prototype._getCountStatus=function(itemId,itemObject){

      if (itemObject.childsCount<=1) { if (itemObject.id==this.rootId) return 4; else  return 0; }

      if (itemObject.childNodes[0].id==itemId) if (!itemObject.id) return 2; else return 1;
      if (itemObject.childNodes[itemObject.childsCount-1].id==itemId) return 0;

      return 1;
   };
/**
*     @desc: return type of node
*     @type: private
*     @param: itemId - node id        
*     @param: itemObject - parent node object
*     @topic: 6
*/      
   dhtmlXTreeObject.prototype._getLineStatus =function(itemId,itemObject){
         if (itemObject.childNodes[itemObject.childsCount-1].id==itemId) return 0;
         return 1;
      }

/**  
*     @desc: open/close node 
*     @type: private
*     @param: itemObject - node object        
*     @param: mode - open/close mode [1-close 2-open](optional)
*     @topic: 6
*/      
   dhtmlXTreeObject.prototype._HideShow=function(itemObject,mode){
      if ((this.XMLsource)&&(!itemObject.XMLload)) {
            if (mode==1) return; //close for not loaded node - ignore it
            itemObject.XMLload=1;
            this._loadDynXML(itemObject.id);
            return; };
//#__pro_feature:01112006{
//#smart_parsing:01112006{
        if (itemObject.unParsed) this.reParse(itemObject);
//#}
//#}
      var Nodes=itemObject.htmlNode.childNodes[0].childNodes; var Count=Nodes.length;
      if (Count>1){
         if ( ( (Nodes[1].style.display!="none") || (mode==1) ) && (mode!=2) ) {
//nb:solves standard doctype prb in IE
          this.allTree.childNodes[0].border = "1";
          this.allTree.childNodes[0].border = "0";
         nodestyle="none";
         }
         else  nodestyle="";

      for (var i=1; i<Count; i++)
         Nodes[i].style.display=nodestyle;
      }
      this._correctPlus(itemObject);
   }

/**
*     @desc: return node state
*     @type: private
*     @param: itemObject - node object        
*     @topic: 6
*/      
   dhtmlXTreeObject.prototype._getOpenState=function(itemObject){
      var z=itemObject.htmlNode.childNodes[0].childNodes;
      if (z.length<=1) return 0;
      if    (z[1].style.display!="none") return 1;
      else return -1;
   }

   
   
/**  
*     @desc: ondblclick item  event handler
*     @type: private
*     @topic: 0  
*/      
   dhtmlXTreeObject.prototype.onRowClick2=function(){
   if    (this.parentObject.treeNod.dblclickFuncHandler) if (!this.parentObject.treeNod.dblclickFuncHandler(this.parentObject.id,this.parentObject.treeNod)) return 0;
      if ((this.parentObject.closeble)&&(this.parentObject.closeble!="0"))
         this.parentObject.treeNod._HideShow(this.parentObject);
      else
         this.parentObject.treeNod._HideShow(this.parentObject,2);
   };
/**
*     @desc: onclick item event handler
*     @type: private
*     @topic: 0  
*/
   dhtmlXTreeObject.prototype.onRowClick=function(){
    var that=this.parentObject.treeNod;
   if    (that._spnFH) if (!that._spnFH(this.parentObject.id,that._getOpenState(this.parentObject))) return 0;
      if ((this.parentObject.closeble)&&(this.parentObject.closeble!="0"))
         that._HideShow(this.parentObject);
      else
         that._HideShow(this.parentObject,2);

//#on_open_end_event:11052006{
   if    (that._epnFH)
           if (!that.xmlstate)
                that._epnFH(this.parentObject.id,that._getOpenState(this.parentObject));
            else{
                that._oie_onXLE=that.onXLE;
                that.onXLE=that._epnFHe;
                }
//#}
   };
//#on_open_end_event:11052006{
      dhtmlXTreeObject.prototype._epnFHe=function(that,id){
        if (that._epnFH)
            that._epnFH(id,that.getOpenState(id));
        that.onXLE=that._oie_onXLE;
        if (that.onXLE) that.onXLE(that,id);
    }

//#}

/**
*     @desc: onclick item image event handler
*     @type: private
*     @edition: Professional
*     @topic: 0  
*/
   dhtmlXTreeObject.prototype.onRowClickDown=function(e){
            e=e||window.event;
         var that=this.parentObject.treeNod;
         that._selectItem(this.parentObject,e);
      };


/*****
SELECTION
*****/

/**
*     @desc: retun selected item id
*     @type: public
*     @return: id of selected item
*     @topic: 1
*/
   dhtmlXTreeObject.prototype.getSelectedItemId=function()
   {
        var str=new Array();
        for (var i=0; i<this._selected.length; i++) str[i]=this._selected[i].id;
      return (str.join(this.dlmtr));
   };

/**
*     @desc: visual select item in tree
*     @type: private
*     @param: node - tree item object
*     @edition: Professional
*     @topic: 0
*/
   dhtmlXTreeObject.prototype._selectItem=function(node,e){
//#__pro_feature:01112006{
//#multiselect:01112006{
        if ((!this._amsel)||(!e)||((!e.ctrlKey)&&(!e.shiftKey)))
//#}
//#}
            this._unselectItems();
//#__pro_feature:01112006{
//#multiselect:01112006{
            if ((node.i_sel)&&(this._amsel)&&(e)&&(e.ctrlKey))
                this._unselectItem(node);
            else
            if ((!node.i_sel)&&((!this._amselS)||(this._selected.length==0)||(this._selected[0].parentObject==node.parentObject)))
                if ((this._amsel)&&(e)&&(e.shiftKey)&&(this._selected.length!=0)&&(this._selected[this._selected.length-1].parentObject==node.parentObject)){
                    var a=this._getIndex(this._selected[this._selected.length-1]);
                    var b=this._getIndex(node);
                    if (b<a) { var c=a; a=b; b=c; }
                    for (var i=a; i<=b; i++)
                        if (!node.parentObject.childNodes[i].i_sel)
                            this._markItem(node.parentObject.childNodes[i]);
                    }
                else
//#}
//#}
					this._markItem(node);
         }
    dhtmlXTreeObject.prototype._markItem=function(node){
              if (node.scolor)  node.span.style.color=node.scolor;
              node.span.className="selectedTreeRow";
             node.i_sel=true;
             this._selected[this._selected.length]=node;
    }

/**
*     @desc: retun node index in childs collection by Id
*     @type: public
*     @param: itemId - node id
*     @return: node index
*     @topic: 2
*/
   dhtmlXTreeObject.prototype.getIndexById=function(itemId){
         var z=this._globalIdStorageFind(itemId);
         if (!z) return null;
         return this._getIndex(z);
   };
   dhtmlXTreeObject.prototype._getIndex=function(w){
        var z=w.parentObject;
        for (var i=0; i<z.childsCount; i++)
            if (z.childNodes[i]==w) return i;
   };





/**
*     @desc: visual unselect item in tree
*     @type: private
*     @param: node - tree item object
*     @edition: Professional
*     @topic: 0
*/
   dhtmlXTreeObject.prototype._unselectItem=function(node){
        if ((node)&&(node.i_sel))
            {

          node.span.className="standartTreeRow";
          if (node.acolor)  node.span.style.color=node.acolor;
            node.i_sel=false;
            for (var i=0; i<this._selected.length; i++)
                    if (!this._selected[i].i_sel) {
                        this._selected.splice(i,1);
                        break;
                 }

            }
       }

/**
*     @desc: visual unselect item in tree
*     @type: private
*     @param: node - tree item object
*     @edition: Professional
*     @topic: 0
*/
   dhtmlXTreeObject.prototype._unselectItems=function(){
      for (var i=0; i<this._selected.length; i++){
            var node=this._selected[i];
         node.span.className="standartTreeRow";
          if (node.acolor)  node.span.style.color=node.acolor;
         node.i_sel=false;
         }
         this._selected=new Array();
       }


/**  
*     @desc: select node text event handler
*     @type: private
*     @param: e - event object
*     @param: htmlObject - node object     
*     @param: mode - if false - call onSelect event
*     @topic: 0  
*/
   dhtmlXTreeObject.prototype.onRowSelect=function(e,htmlObject,mode){
      e=e||window.event;

        var obj=this.parentObject;
      if (htmlObject) obj=htmlObject.parentObject;
        var that=obj.treeNod;

        var lastId=that.getSelectedItemId();
		if ((!e)||(!e.skipUnSel))
	        that._selectItem(obj,e);

      if (!mode) {
         if ((e)&&(e.button==2)&&(that.arFunc)) that.arFunc(obj.id,e);
         if (obj.actionHandler) obj.actionHandler(obj.id,lastId);
         }
   };




   
/**
*     @desc: fix checkbox state
*     @type: private
*     @topic: 0
*/
dhtmlXTreeObject.prototype._correctCheckStates=function(dhtmlObject){
   if (!this.tscheck) return;
   if (dhtmlObject.id==this.rootId) return;
   //calculate state
   var act=dhtmlObject.childNodes;
   var flag1=0; var flag2=0;
   if (dhtmlObject.childsCount==0) return;
   for (var i=0; i<dhtmlObject.childsCount; i++){
   	  if (act[i].dscheck) continue;
      if (act[i].checkstate==0) flag1=1;
      else if (act[i].checkstate==1) flag2=1;
         else { flag1=1; flag2=1; break; }
		 }

   if ((flag1)&&(flag2)) this._setCheck(dhtmlObject,"unsure");
   else if (flag1)  this._setCheck(dhtmlObject,false);
      else  this._setCheck(dhtmlObject,true);

      this._correctCheckStates(dhtmlObject.parentObject);
}

/**  
*     @desc: checbox select action
*     @type: private
*     @topic: 0
*/   
   dhtmlXTreeObject.prototype.onCheckBoxClick=function(e)   {
      if (this.parentObject.dscheck) return true;
      if (this.treeNod.tscheck)
         if (this.parentObject.checkstate==1) this.treeNod._setSubChecked(false,this.parentObject);
         else this.treeNod._setSubChecked(true,this.parentObject);
      else
         if (this.parentObject.checkstate==1) this.treeNod._setCheck(this.parentObject,false);
         else this.treeNod._setCheck(this.parentObject,true);
      this.treeNod._correctCheckStates(this.parentObject.parentObject);

      if (this.treeNod.checkFuncHandler) return (this.treeNod.checkFuncHandler(this.parentObject.id,this.parentObject.checkstate));
      else return true;
   };
/**
*     @desc: create HTML elements for tree node
*     @type: private
*     @param: acheck - enable/disable checkbox
*     @param: itemObject - item object
*     @param: mode - mode
*     @topic: 0
*/
   dhtmlXTreeObject.prototype._createItem=function(acheck,itemObject,mode){
      var table=document.createElement('table');
         table.cellSpacing=0;table.cellPadding=0;
         table.border=0;
          if (this.hfMode) table.style.tableLayout="fixed";
         table.style.margin=0; table.style.padding=0;

      var tbody=document.createElement('tbody');
      var tr=document.createElement('tr');
//            tr.height="16px"; tr.style.overflow="hidden";
      var td1=document.createElement('td');
         td1.className="standartTreeImage";

            if (this._txtimg){
         var img0=document.createElement("div");
            td1.appendChild(img0);
                img0.className="dhx_tree_textSign";
            }
            else
            {
         var img0=document.createElement((itemObject.id==this.rootId)?"div":"img");
            img0.border="0"; //img0.src='treeGfx/line1.gif';
            if (itemObject.id!=this.rootId) img0.align="absmiddle";
            td1.appendChild(img0); img0.style.padding=0; img0.style.margin=0;
            }

      var td11=document.createElement('td');
//         var inp=document.createElement("input");            inp.type="checkbox"; inp.style.width="12px"; inp.style.height="12px";
         var inp=document.createElement(((this.cBROf)||(itemObject.id==this.rootId))?"div":"img");
         inp.checked=0; inp.src=this.imPath+this.checkArray[0]; inp.style.width="16px"; inp.style.height="16px";
            //can cause problems with hide/show check
         if (!acheck) (((_isOpera)||(_isKHTML))?td11:inp).style.display="none";

         // td11.className="standartTreeImage";
               //if (acheck)
            td11.appendChild(inp);
            if ((!this.cBROf)&&(itemObject.id!=this.rootId)) inp.align="absmiddle";
            inp.onclick=this.onCheckBoxClick;
            inp.treeNod=this;
            inp.parentObject=itemObject;
            td11.width="20px";

      var td12=document.createElement('td');
         td12.className="standartTreeImage";
         var img=document.createElement((itemObject.id==this.rootId)?"div":"img"); img.onmousedown=this._preventNsDrag; img.ondragstart=this._preventNsDrag;
            img.border="0";
            if (this._aimgs){
               img.parentObject=itemObject;
               if (itemObject.id!=this.rootId) img.align="absmiddle";
               img.onclick=this.onRowSelect; }
            if (!mode) img.src=this.imPath+this.imageArray[0];
            td12.appendChild(img); img.style.padding=0; img.style.margin=0;
         if (this.timgen)
            {  img.style.width=this.def_img_x; img.style.height=this.def_img_y; }
         else
            {
                img.style.width="0px"; img.style.height="0px";
                if (_isOpera)    td12.style.display="none";
                }


      var td2=document.createElement('td');
         td2.className="standartTreeRow";

            itemObject.span=document.createElement('span');
            itemObject.span.className="standartTreeRow";
            if (this.mlitems) {
				itemObject.span.style.width=this.mlitems;
			   //	if (!_isIE)
					itemObject.span.style.display="block";
				}
            else td2.noWrap=true;
                if (!_isKHTML) td2.style.width="100%";

//      itemObject.span.appendChild(document.createTextNode(itemObject.label));
         itemObject.span.innerHTML=itemObject.label;
      td2.appendChild(itemObject.span);
      td2.parentObject=itemObject;        td1.parentObject=itemObject;
      td2.onclick=this.onRowSelect; td1.onclick=this.onRowClick; td2.ondblclick=this.onRowClick2;
      if (this.ettip)
//#__pro_feature:01112006{
//#dhtmlxtootip:01112006{
	  	  if (this._dhxTT) dhtmlxTooltip.setTooltip(td2,itemObject.label);
		  else
//#}
//#}
		  	td2.title=itemObject.label;

      if (this.dragAndDropOff) {
         if (this._aimgs) { this.dragger.addDraggableItem(td12,this); td12.parentObject=itemObject; }
         this.dragger.addDraggableItem(td2,this);
         }

      itemObject.span.style.paddingLeft="5px";      itemObject.span.style.paddingRight="5px";   td2.style.verticalAlign="";
       td2.style.fontSize="10pt";       td2.style.cursor=this.style_pointer;
      tr.appendChild(td1);            tr.appendChild(td11);            tr.appendChild(td12);
      tr.appendChild(td2);
      tbody.appendChild(tr);
      table.appendChild(tbody);
	  if (this.ehlt){//highlighting
		tr.onmousemove=this._itemMouseIn;
        tr[(_isIE)?"onmouseleave":"onmouseout"]=this._itemMouseOut;
      }

      if (this.arFunc){
         //disable context handler
         tr.oncontextmenu=function(e){ this.childNodes[0].parentObject.treeNod.arFunc(this.childNodes[0].parentObject.id,(e||event)); return false; };
      }
      return table;
   };
   

/**  
*     @desc: set path to image directory
*     @param: newPath - path to image directory
*     @type: public
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.setImagePath=function( newPath ){ this.imPath=newPath; };

//#__pro_feature:01112006{
//#child_calc:01112006{

/**
*     @desc: return count of leafs
*     @param: itemNode -  node object
*     @type: private
*     @edition: Professional
*     @topic: 4
*/
   dhtmlXTreeObject.prototype._getLeafCount=function(itemNode){
      var a=0;
      for (var b=0; b<itemNode.childsCount; b++)
         if (itemNode.childNodes[b].childsCount==0) a++;
      return a;
   }

/**
*     @desc: get value of child counter (child counter must be enabled)
*     @type: private
*     @param: itemId - id of selected item
*     @edition: Professional
*     @return: counter value (related to counter mode)
*     @topic: 6
*/
   dhtmlXTreeObject.prototype._getChildCounterValue=function(itemId){
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
      if ((temp.unParsed)||((!temp.XMLload)&&(this.XMLsource)))
      return temp._acc
      switch(this.childCalc)
      {
         case 1: return temp.childsCount; break;
         case 2: return this._getLeafCount(temp); break;
         case 3: return temp._acc; break;
         case 4: return temp._acc; break;
      }
   }

  /**
*     @desc: fix node child counter
*     @param: itemNode -  node object
*     @type: private
*     @edition: Professional
*     @topic: 4
*/
   dhtmlXTreeObject.prototype._fixChildCountLabel=function(itemNode,index){
      if (this.childCalc==null) return;
      if ((itemNode.unParsed)||((!itemNode.XMLload)&&(this.XMLsource)))
      {
         if (itemNode._acc)
         itemNode.span.innerHTML=itemNode.label+this.htmlcA+itemNode._acc+this.htmlcB;
         else
         itemNode.span.innerHTML=itemNode.label;

      return;
      }

      switch(this.childCalc){
         case 1:
            if (itemNode.childsCount!=0)
               itemNode.span.innerHTML=itemNode.label+this.htmlcA+itemNode.childsCount+this.htmlcB;
            else itemNode.span.innerHTML=itemNode.label;
            break;
         case 2:
            var z=this._getLeafCount(itemNode);
            if (z!=0)
               itemNode.span.innerHTML=itemNode.label+this.htmlcA+z+this.htmlcB;
            else itemNode.span.innerHTML=itemNode.label;
            break;
         case 3:
            if (itemNode.childsCount!=0)
               {
               var bcc=0;
               for (var a=0; a<itemNode.childsCount; a++)   {
                  if (!itemNode.childNodes[a]._acc) itemNode.childNodes[a]._acc=0;
                  bcc+=itemNode.childNodes[a]._acc*1;      }
                  bcc+=itemNode.childsCount*1;

               itemNode.span.innerHTML=itemNode.label+this.htmlcA+bcc+this.htmlcB;
               itemNode._acc=bcc;
               }
            else { itemNode.span.innerHTML=itemNode.label;   itemNode._acc=1; }
            if ((itemNode.parentObject)&&(itemNode.parentObject!=this.htmlNode))
               this._fixChildCountLabel(itemNode.parentObject);
            break;
         case 4:
            if (itemNode.childsCount!=0)
               {
               var bcc=0;
               for (var a=0; a<itemNode.childsCount; a++)   {
                  if (!itemNode.childNodes[a]._acc) itemNode.childNodes[a]._acc=1;
                  bcc+=itemNode.childNodes[a]._acc*1;      }

               itemNode.span.innerHTML=itemNode.label+this.htmlcA+bcc+this.htmlcB;
               itemNode._acc=bcc;
               }
            else { itemNode.span.innerHTML=itemNode.label;   itemNode._acc=1; }
            if ((itemNode.parentObject)&&(itemNode.parentObject!=this.htmlNode))
               this._fixChildCountLabel(itemNode.parentObject);
            break;
      }
   }

/**
*     @desc: set child calculation mode
*     @param: mode - mode name as string . Possible values: child - child, no recursive; leafs - child without subchilds, no recursive;  ,childrec - child, recursive; leafsrec - child without subchilds, recursive; disabled (disabled by default)
*     @type: public
*     @edition: Professional
*     @topic: 0
*/ 
   dhtmlXTreeObject.prototype.setChildCalcMode=function( mode ){
      switch(mode){
         case "child": this.childCalc=1; break;
         case "leafs": this.childCalc=2; break;
         case "childrec": this.childCalc=3; break;
         case "leafsrec": this.childCalc=4; break;
         case "disabled": this.childCalc=null; break;
         default: this.childCalc=4;
      }
    }
/**  
*     @desc: set child calculation prefix and postfix
*     @param: htmlA - postfix ([ - by default)
*     @param: htmlB - postfix (] - by default)
*     @type: public
*     @edition: Professional
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.setChildCalcHTML=function( htmlA,htmlB ){
      this.htmlcA=htmlA;      this.htmlcB=htmlB;
    }
//#}
//#}

/**
*     @desc: set function called when tree node selected
*     @param: (function) func - event handling function
*     @type: public
*     @topic: 0,7
*     @event: onRightClick
*     @eventdesc:  Event occured after right mouse button was clicked.
         Assigning this handler can disable default context menu, and noncompattible with dhtmlXMenu integration.
*     @eventparam: (string) ID of clicked item
*     @eventparam: (object) event object
*/
   dhtmlXTreeObject.prototype.setOnRightClickHandler=function(func){  if (typeof(func)=="function") this.arFunc=func; else this.arFunc=eval(func);  };

/**
*     @desc: set function called when tree node selected
*     @param: func - event handling function
*     @type: public
*     @topic: 0,7
*     @event: onClick
*     @eventdesc: Event raised immideatly after text part of item in tree was clicked, but after default onClick functionality was processed.
              Richt mouse button click can be catched by onRightClick handler.
*     @eventparam:  ID of clicked item
*/
   dhtmlXTreeObject.prototype.setOnClickHandler=function(func){  if (typeof(func)=="function") this.aFunc=func; else this.aFunc=eval(func);  };


/**
*     @desc: enables dynamic loading from XML
*     @type: public
*     @param: filePath - name of script returning XML; in case of virtual loading - user defined function
*     @topic: 0  
*/
   dhtmlXTreeObject.prototype.setXMLAutoLoading=function(filePath){  this.XMLsource=filePath; };

   /**
*     @desc: set function called before checkbox checked/unchecked
*     @param: func - event handling function
*     @type: public
*     @topic: 0,7
*     @event: onCheck
*     @eventdesc: Event raised immideatly after item in tree was checked/unchecked.
*     @eventparam: ID of item which will be checked/unchecked
*     @eventparam: Current checkbox state. 1 - item checked, 0 - item unchecked.
*/
   dhtmlXTreeObject.prototype.setOnCheckHandler=function(func){  if (typeof(func)=="function") this.checkFuncHandler=func; else this.checkFuncHandler=eval(func); };


/**
*     @desc: set function called before tree node opened/closed
*     @param: func - event handling function
*     @type: deprecated
*     @topic: 0,7
*     @event:  onOpen
*     @eventdesc: Event raised immideatly after item in tree got command to open/close , and before item was opened//closed. Event also raised for unclosable nodes and nodes without open/close functionality - in that case result of function will be ignored.
            Event not raised if node opened by dhtmlXtree API.
*     @eventparam: ID of node which will be opened/closed
*     @eventparam: Current open state of tree item. 0 - item has not childs, -1 - item closed, 1 - item opened.
*     @eventreturn: true - confirm opening/closing; false - deny opening/closing;
*/
   dhtmlXTreeObject.prototype.setOnOpenHandler=function(func){  if (typeof(func)=="function") this._spnFH=func; else this._spnFH=eval(func);  };
/**
*     @desc: set function called before tree node opened/closed
*     @param: func - event handling function
*     @type: public
*     @topic: 0,7
*     @event:  onOpenStart
*     @eventdesc: Event raised immideatly after item in tree got command to open/close , and before item was opened//closed. Event also raised for unclosable nodes and nodes without open/close functionality - in that case result of function will be ignored.
            Event not raised if node opened by dhtmlXtree API.
*     @eventparam: ID of node which will be opened/closed
*     @eventparam: Current open state of tree item. 0 - item has not childs, -1 - item closed, 1 - item opened.
*     @eventreturn: true - confirm opening/closing; false - deny opening/closing;
*/
   dhtmlXTreeObject.prototype.setOnOpenStartHandler=function(func){  if (typeof(func)=="function") this._spnFH=func; else this._spnFH=eval(func);  };

/**
*     @desc: set function called after tree node opened/closed
*     @param: func - event handling function
*     @type: public
*     @topic: 0,7
*     @event:  onOpenEnd
*     @eventdesc: Event raised immideatly after item in tree got command to open/close , and before item was opened//closed. Event also raised for unclosable nodes and nodes without open/close functionality - in that case result of function will be ignored.
            Event not raised if node opened by dhtmlXtree API.
*     @eventparam: ID of node which will be opened/closed
*     @eventparam: Current open state of tree item. 0 - item has not childs, -1 - item closed, 1 - item opened.
*/
   dhtmlXTreeObject.prototype.setOnOpenEndHandler=function(func){  if (typeof(func)=="function") this._epnFH=func; else this._epnFH=eval(func);  };

   /**
*     @desc: set function called when tree node double clicked
*     @param: func - event handling function
*     @type: public
*     @topic: 0,7
*     @event: onDblClick
*     @eventdesc: Event raised immideatly after item in tree was doubleclicked, before default onDblClick functionality was processed.
         Beware using both onClick and onDblClick events, because component can  generate onClick event before onDblClick event while doubleclicking item in tree.
         ( that behavior depend on used browser )
*     @eventparam:  ID of item which was doubleclicked
*     @eventreturn:  true - confirm opening/closing; false - deny opening/closing;
*/
   dhtmlXTreeObject.prototype.setOnDblClickHandler=function(func){  if (typeof(func)=="function") this.dblclickFuncHandler=func; else this.dblclickFuncHandler=eval(func); };









   /**
*     @desc: expand target node and all child nodes
*     @type: public
*     @param: itemId - node id
*     @topic: 4
*/
   dhtmlXTreeObject.prototype.openAllItems=function(itemId)
   {
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
      this._xopenAll(temp);
   };
   
/**
*     @desc: return open/close state
*     @type: public
*     @param: itemId - node id
*     @return: -1 - close, 1 - opened, 0 - node doen't have childs
*     @topic: 4
*/   
   dhtmlXTreeObject.prototype.getOpenState=function(itemId){
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return "";
      return this._getOpenState(temp);
   };

/**  
*     @desc: collapse target node and all child nodes
*     @type: public
*     @param: itemId - node id
*     @topic: 4  
*/      
   dhtmlXTreeObject.prototype.closeAllItems=function(itemId)
   {
        if (itemId===window.undefined) itemId=this.rootId;
        
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
      this._xcloseAll(temp);

//nb:solves standard doctype prb in IE
         this.allTree.childNodes[0].border = "1";
       this.allTree.childNodes[0].border = "0";

   };
   
   
/**
*     @desc: set user data for target node
*     @type: public
*     @param: itemId - target node id
*     @param: name - key for user data
*     @param: value - user data
*     @topic: 5
*/
   dhtmlXTreeObject.prototype.setUserData=function(itemId,name,value){
      var sNode=this._globalIdStorageFind(itemId,0,true);
         if (!sNode) return;
         if(name=="hint")
//#__pro_feature:01112006{
//#dhtmlxtootip:01112006{
		 if (this._dhxTT) dhtmlxTooltip.setTooltip(sNode.htmlNode.childNodes[0].childNodes[0],value);
		 else
//#}
//#}
			 sNode.htmlNode.childNodes[0].childNodes[0].title=value;
            if (sNode.userData["t_"+name]===undefined){
                 if (!sNode._userdatalist) sNode._userdatalist=name;
                else sNode._userdatalist+=","+name;
            }
            sNode.userData["t_"+name]=value;
   };
   
/**  
*     @desc: return user data from target node
*     @type: public
*     @param: itemId - target node id
*     @param: name - key for user data
*     @return: value of user data
*     @topic: 5
*/
   dhtmlXTreeObject.prototype.getUserData=function(itemId,name){
      var sNode=this._globalIdStorageFind(itemId,0,true);
      if (!sNode) return;
      return sNode.userData["t_"+name];
   };




/**
*     @desc: get node color
*     @param: itemId - id of node
*     @type: public
*     @return: color of node (empty string for default color);
*     @topic: 6  
*/   
   dhtmlXTreeObject.prototype.getItemColor=function(itemId)
   {
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;

      var res= new Object();
      if (temp.acolor) res.acolor=temp.acolor;
      if (temp.acolor) res.scolor=temp.scolor;      
      return res;
   };
/**  
*     @desc: set node color
*     @param: itemId - id of node
*     @param: defaultColor - node color
*     @param: selectedColor - selected node color
*     @type: public
*     @topic: 6
*/
   dhtmlXTreeObject.prototype.setItemColor=function(itemId,defaultColor,selectedColor)
   {
      if ((itemId)&&(itemId.span))
         var temp=itemId;
      else
         var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
         else {
         if (temp.i_sel)
            {  if (selectedColor) temp.span.style.color=selectedColor; }
         else
            {  if (defaultColor) temp.span.style.color=defaultColor;  }

         if (selectedColor) temp.scolor=selectedColor;
         if (defaultColor) temp.acolor=defaultColor;
         }
   };

/**
*     @desc: return item text
*     @param: itemId - id of node
*     @type: public
*     @return: text of item (with HTML formatting, if any)
*     @topic: 6
*/
   dhtmlXTreeObject.prototype.getItemText=function(itemId)
   {
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
      return(temp.htmlNode.childNodes[0].childNodes[0].childNodes[3].childNodes[0].innerHTML);
   };
/**  
*     @desc: return parent item id
*     @param: itemId - id of node
*     @type: public
*     @return: id of parent item
*     @topic: 4
*/         
   dhtmlXTreeObject.prototype.getParentId=function(itemId)
   {
      var temp=this._globalIdStorageFind(itemId);
      if ((!temp)||(!temp.parentObject)) return "";
      return temp.parentObject.id;
   };



/**  
*     @desc: change item id
*     @type: public
*     @param: itemId - old node id
*     @param: newItemId - new node id        
*     @topic: 4
*/    
   dhtmlXTreeObject.prototype.changeItemId=function(itemId,newItemId)
   {
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
      temp.id=newItemId;
        temp.span.contextMenuId=newItemId;
      for (var i=0; i<this._globalIdStorageSize; i++)
         if (this._globalIdStorage[i]==itemId) 
            {
            this._globalIdStorage[i]=newItemId;
            }
   };


/**
*     @desc: mark selected item as cutted
*     @type: public
*     @topic: 2  
*/    
   dhtmlXTreeObject.prototype.doCut=function(){
      if (this.nodeCut) this.clearCut();
      this.nodeCut=(new Array()).concat(this._selected);
        for (var i=0; i<this.nodeCut.length; i++){
          var tempa=this.nodeCut[i];
            tempa._cimgs=new Array();
          tempa._cimgs[0]=tempa.images[0];
          tempa._cimgs[1]=tempa.images[1];
          tempa._cimgs[2]=tempa.images[2];
          tempa.images[0]=tempa.images[1]=tempa.images[2]=this.cutImage;
          this._correctPlus(tempa);
        }
   };

/**
*     @desc: insert previously cutted branch
*     @param: itemId - id of new parent node
*     @type: public
*     @topic: 2  
*/    
   dhtmlXTreeObject.prototype.doPaste=function(itemId){
      var tobj=this._globalIdStorageFind(itemId);
      if (!tobj) return 0;
        for (var i=0; i<this.nodeCut.length; i++){
               if (this._checkPNodes(tobj,this.nodeCut[i])) continue;
                this._moveNode(this.nodeCut[i],tobj);
               }
      this.clearCut();
   };

/**  
*     @desc: clear cut
*     @type: public
*     @topic: 2  
*/
   dhtmlXTreeObject.prototype.clearCut=function(){
      for (var i=0; i<this.nodeCut.length; i++)
         {
          var tempa=this.nodeCut[i];
          tempa.images[0]=tempa._cimgs[0];
          tempa.images[1]=tempa._cimgs[1];
          tempa.images[2]=tempa._cimgs[2];
          this._correctPlus(tempa);
         }
          this.nodeCut=new Array();
   };
   


   /**  
*     @desc: move node with subnodes
*     @type: private
*     @param: itemObject - moved node object
*     @param: targetObject - new parent node
*     @topic: 2  
*/
   dhtmlXTreeObject.prototype._moveNode=function(itemObject,targetObject){
//#__pro_feature:01112006{
//#complex_move:01112006{
      var mode=this.dadmodec;
      if (mode==1)
        {
            var z=targetObject;
         if (this.dadmodefix<0)
         {

                while (true){
            z=this._getPrevNode(z);
            if ((z==-1)) { z=this.htmlNode; break; }
                if ((z.tr==0)||(z.tr.style.display=="")||(!z.parentObject)) break;
                }

                var nodeA=z;
                var nodeB=targetObject;

            }
            else
            {
                while (true){
            z=this._getNextNode(z);
            if ((z==-1)) { z=this.htmlNode; break; }
                if ((z.tr.style.display=="")||(!z.parentObject)) break;
                }

                var nodeB=z;
                var nodeA=targetObject;
            }


            if (this._getNodeLevel(nodeA,0)>this._getNodeLevel(nodeB,0))
                {
                if (!this.dropLower)
                    return this._moveNodeTo(itemObject,nodeA.parentObject);
                else
                    if  (nodeB.id!=this.rootId)
                        return this._moveNodeTo(itemObject,nodeB.parentObject,nodeB);
                    else
                        return this._moveNodeTo(itemObject,this.htmlNode,null);
                }
            else
                {
                return this._moveNodeTo(itemObject,nodeB.parentObject,nodeB);
                }


      }
      else
//#}
//#}
	  return this._moveNodeTo(itemObject,targetObject);

   }

   /**
*     @desc: fix order of nodes in collection
*     @type: private
*     @param: target - parent item node
*     @param: zParent - before node
*     @edition: Professional
*     @topic: 2
*/

dhtmlXTreeObject.prototype._fixNodesCollection=function(target,zParent){
      var flag=0; var icount=0;
      var Nodes=target.childNodes;
      var Count=target.childsCount-1;

      if (zParent==Nodes[Count]) return;
      for (var i=0; i<Count; i++)
         if (Nodes[i]==Nodes[Count]) {  Nodes[i]=Nodes[i+1]; Nodes[i+1]=Nodes[Count]; }

//         Count=target.childsCount;
      for (var i=0; i<Count+1; i++)      
         {
         if (flag) { 
            var temp=Nodes[i];
            Nodes[i]=flag; 
            flag=temp; 
               }
         else 
         if (Nodes[i]==zParent) {   flag=Nodes[i]; Nodes[i]=Nodes[Count];  }
         }
   };
   
/**  
*     @desc: recreate branch
*     @type: private
*     @param: itemObject - moved node object
*     @param: targetObject - new parent node
*     @param: level - top level flag
*     @param: beforeNode - node for sibling mode
*     @mode: mode - DragAndDrop mode (0 - as child, 1 as sibling)
*     @edition: Professional
*     @topic: 2
*/
dhtmlXTreeObject.prototype._recreateBranch=function(itemObject,targetObject,beforeNode,level){
    var i; var st="";
    if (beforeNode){
    for (i=0; i<targetObject.childsCount; i++)
        if (targetObject.childNodes[i]==beforeNode) break;

    if (i!=0)
        beforeNode=targetObject.childNodes[i-1];
    else{
        st="TOP";
        beforeNode="";
        }
    }

   var newNode=this._attachChildNode(targetObject,itemObject.id,itemObject.label,0,itemObject.images[0],itemObject.images[1],itemObject.images[2],st,0,beforeNode);

   //copy user data
   newNode._userdatalist=itemObject._userdatalist;
   newNode.userData=itemObject.userData.clone();
    newNode.XMLload=itemObject.XMLload;

//#__pro_feature:01112006{
//#smart_parsing:01112006{
   //copy unparsed chunk
   if (itemObject.unParsed)
      {
      newNode.unParsed=itemObject.unParsed;
      this._correctPlus(newNode);
      //this._correctLine(newNode);
      }
   else
//#}
//#}
   for (var i=0; i<itemObject.childsCount; i++)
      this._recreateBranch(itemObject.childNodes[i],newNode,0,1);

//#__pro_feature:01112006{
//#child_calc:01112006{
      if ((!level)&&(this.childCalc)) { this._redrawFrom(this,targetObject);  }
//#}
//#}
   return newNode;
}

/**
*     @desc: move single node
*     @type: private
*     @param: itemObject - moved node object
*     @param: targetObject - new parent node
*     @edition: Professional
*     @mode: mode - DragAndDrop mode (0 - as child, 1 as sibling)
*     @topic: 2
*/
   dhtmlXTreeObject.prototype._moveNodeTo=function(itemObject,targetObject,beforeNode){
    //return;
    if   (itemObject.treeNod._nonTrivialNode)
        return itemObject.treeNod._nonTrivialNode(this,targetObject,beforeNode,itemObject);

    if    (targetObject.mytype)
       var framesMove=(itemObject.treeNod.lWin!=targetObject.lWin);
    else
          var framesMove=(itemObject.treeNod.lWin!=targetObject.treeNod.lWin);

   if (this.dragFunc) if (!this.dragFunc(itemObject.id,targetObject.id,(beforeNode?beforeNode.id:null),itemObject.treeNod,targetObject.treeNod)) return false;
      if ((targetObject.XMLload==0)&&(this.XMLsource))
         {
         targetObject.XMLload=1;
            this._loadDynXML(targetObject.id);
         }
      this.openItem(targetObject.id);

   var oldTree=itemObject.treeNod;
   var c=itemObject.parentObject.childsCount;
   var z=itemObject.parentObject;

   if ((framesMove)||(oldTree.dpcpy)) {//interframe drag flag
        var _otiid=itemObject.id;
      itemObject=this._recreateBranch(itemObject,targetObject,beforeNode);
        if (!oldTree.dpcpy) oldTree.deleteItem(_otiid);
        }
   else
      {

      var Count=targetObject.childsCount; var Nodes=targetObject.childNodes;
           Nodes[Count]=itemObject;
            itemObject.treeNod=targetObject.treeNod;
            targetObject.childsCount++;         

            var tr=this._drawNewTr(Nodes[Count].htmlNode);

            if (!beforeNode)
               {
                  targetObject.htmlNode.childNodes[0].appendChild(tr);
               if (this.dadmode==1) this._fixNodesCollection(targetObject,beforeNode);
               }
            else
               {
               targetObject.htmlNode.childNodes[0].insertBefore(tr,beforeNode.tr);
               this._fixNodesCollection(targetObject,beforeNode);
               Nodes=targetObject.childNodes;
               }


         }

            if ((!oldTree.dpcpy)&&(!framesMove))   {
                var zir=itemObject.tr;

                if ((document.all)&&(navigator.appVersion.search(/MSIE\ 5\.0/gi)!=-1))
                    {
                    window.setTimeout(function() { zir.removeNode(true); } , 250 );
                    }
                else   //if (zir.parentNode) zir.parentNode.removeChild(zir,true);

                itemObject.parentObject.htmlNode.childNodes[0].removeChild(itemObject.tr);

                //itemObject.tr.removeNode(true);
            if ((!beforeNode)||(targetObject!=itemObject.parentObject)){
               for (var i=0; i<z.childsCount; i++){
                  if (z.childNodes[i].id==itemObject.id) {
                  z.childNodes[i]=0;
                  break;            }}}
               else z.childNodes[z.childsCount-1]=0;

            oldTree._compressChildList(z.childsCount,z.childNodes);
            z.childsCount--;
            }


      if ((!framesMove)&&(!oldTree.dpcpy)) {
       itemObject.tr=tr;
      tr.nodem=itemObject;
      itemObject.parentObject=targetObject;

      if (oldTree!=targetObject.treeNod) {   if(itemObject.treeNod._registerBranch(itemObject,oldTree)) return;      this._clearStyles(itemObject);  this._redrawFrom(this,itemObject.parentObject);   };

      this._correctPlus(targetObject);
      this._correctLine(targetObject);

      this._correctLine(itemObject);
      this._correctPlus(itemObject);

         //fix target siblings
      if (beforeNode)
      {

         this._correctPlus(beforeNode);
         //this._correctLine(beforeNode);
      }
      else 
      if (targetObject.childsCount>=2)
      {

         this._correctPlus(Nodes[targetObject.childsCount-2]);
         this._correctLine(Nodes[targetObject.childsCount-2]);
      }
      
      this._correctPlus(Nodes[targetObject.childsCount-1]);
      //this._correctLine(Nodes[targetObject.childsCount-1]);


      if (this.tscheck) this._correctCheckStates(targetObject);
      if (oldTree.tscheck) oldTree._correctCheckStates(z);

      }

      //fix source parent

      if (c>1) { oldTree._correctPlus(z.childNodes[c-2]);
               oldTree._correctLine(z.childNodes[c-2]);
               }


//      if (z.childsCount==0)
          oldTree._correctPlus(z);
            oldTree._correctLine(z);

//#__pro_feature:01112006{
//#child_calc:01112006{
      this._fixChildCountLabel(targetObject);
      oldTree._fixChildCountLabel(z);
//#}
//#}
      if (this.dropFunc) this.dropFunc(itemObject.id,targetObject.id,(beforeNode?beforeNode.id:null),oldTree,targetObject.treeNod);
      return itemObject.id;
   };

   

/**
*     @desc: recursive set default styles for node
*     @type: private
*     @param: itemObject - target node object
*     @topic: 6  
*/   
   dhtmlXTreeObject.prototype._clearStyles=function(itemObject){
         var td1=itemObject.htmlNode.childNodes[0].childNodes[0].childNodes[1];
         var td3=td1.nextSibling.nextSibling;

         itemObject.span.innerHTML=itemObject.label;
		 itemObject.i_sel=false;

         if (this.checkBoxOff) { td1.childNodes[0].style.display=""; td1.childNodes[0].onclick=this.onCheckBoxClick;  }
         else td1.childNodes[0].style.display="none";
         td1.childNodes[0].treeNod=this;
//#__pro_feature:01112006{
//#context_menu:01112006{
            if (this.cMenu) {
                itemObject.onmousedown=itemObject.contextOnclick||null;
                this.cMenu.setContextZone(itemObject.span,itemObject.id);
                }
            else
//#}
//#}
			itemObject.span.onmousedown=function(){};

         this.dragger.removeDraggableItem(td3);
         if (this.dragAndDropOff) this.dragger.addDraggableItem(td3,this);
         td3.childNodes[0].className="standartTreeRow";
         td3.onclick=this.onRowSelect; td3.ondblclick=this.onRowClick2;
         td1.previousSibling.onclick=this.onRowClick;

         this._correctLine(itemObject);
         this._correctPlus(itemObject);
         for (var i=0; i<itemObject.childsCount; i++) this._clearStyles(itemObject.childNodes[i]); 

   };
/**
*     @desc: register node and all childs nodes
*     @type: private
*     @param: itemObject - node object
*     @topic: 2  
*/
   dhtmlXTreeObject.prototype._registerBranch=function(itemObject,oldTree){
   /*for (var i=0; i<itemObject.childsCount; i++)
      if (confirm(itemObject.childNodes[i].id)) return;*/
      itemObject.id=this._globalIdStorageAdd(itemObject.id,itemObject);
      itemObject.treeNod=this;
         if (oldTree) oldTree._globalIdStorageSub(itemObject.id);
         for (var i=0; i<itemObject.childsCount; i++)
            this._registerBranch(itemObject.childNodes[i],oldTree);
      return 0;
   };

   
/**  
*     @desc: enable three state checkboxes
*     @beforeInit: 1
*     @param: mode - 1 - on, 0 - off;
*     @type: public
*     @topic: 0  
*/
   dhtmlXTreeObject.prototype.enableThreeStateCheckboxes=function(mode) { this.tscheck=convertStringToBoolean(mode); };


/**
*     @desc: set function called when mouse is over tree node
*     @param: func - event handling function
*     @type: public
*     @topic: 0,7
*     @event: onMouseIN
*     @eventdesc: Event raised immideatly after mouse hovered over item
*     @eventparam:  ID of item
*/
   dhtmlXTreeObject.prototype.setOnMouseInHandler=function(func){
    	this.ehlt=true;
   		if (typeof(func)=="function") this._onMSI=func; else this.aFunc=eval(func);  };

/**
*     @desc: set function called when mouse is out of tree node
*     @param: func - event handling function
*     @type: public
*     @topic: 0,7
*     @event: onMouseOut
*     @eventdesc: Event raised immideatly after mouse moved out of item
*     @eventparam:  ID of clicked item
*/
   dhtmlXTreeObject.prototype.setOnMouseOutHandler=function(func){
		this.ehlt=true;
		if (typeof(func)=="function") this._onMSO=func; else this.aFunc=eval(func);  };





//#__pro_feature:01112006{
//#mercy_drag:01112006{
/**
*     @desc: enable drag without removing (copy instead of move)
*     @beforeInit: 1
*     @param: mode - 1 - on, 0 - off;
*     @type: public
*     @edition:Professional
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableMercyDrag=function(mode){ this.dpcpy=convertStringToBoolean(mode); };
//#}
//#}



/**
*     @desc: enable tree images
*     @beforeInit: 1
*     @param: mode - 1 - on, 0 - off;
*     @type: public
*     @topic: 0  
*/         
   dhtmlXTreeObject.prototype.enableTreeImages=function(mode) { this.timgen=convertStringToBoolean(mode); };
   

   
/**
*     @desc: enable mode with fixed tables (look better, but hasn't horisontal scrollbar)
*     @beforeInit: 1
*     @param: mode - 1 - on, 0 - off;
*     @type: private
*     @topic: 0  
*/
   dhtmlXTreeObject.prototype.enableFixedMode=function(mode) { this.hfMode=convertStringToBoolean(mode); };
   
/**  
*     @desc: hide checkboxes (all checkboxes in tree)
*     @type: public
*     @param: mode - enabled/disabled
*     @param: hidden - if set to true, checkboxes not rendered but can be shown by showItemCheckbox
*     @topic: 0  
*/
   dhtmlXTreeObject.prototype.enableCheckBoxes=function(mode, hidden){ this.checkBoxOff=convertStringToBoolean(mode); this.cBROf=(!(this.checkBoxOff||convertStringToBoolean(hidden))); };
/**
*     @desc: set default images for nodes (must be called before XML loading)
*     @type: public
*     @param: a0 - image for node without childrens;
*     @param: a1 - image for closed node;
*     @param: a2 - image for opened node                  
*     @topic: 6  
*/
   dhtmlXTreeObject.prototype.setStdImages=function(image1,image2,image3){
                  this.imageArray[0]=image1; this.imageArray[1]=image2; this.imageArray[2]=image3;};

/**
*     @desc: enable/disable tree lines (parent-child threads)
*     @type: public
*     @param: mode - enable/disable tree lines
*     @topic: 6
*/                  
   dhtmlXTreeObject.prototype.enableTreeLines=function(mode){
      this.treeLinesOn=convertStringToBoolean(mode);
   }

/**
*     @desc: set images used for parent-child threads drawing
*     @type: public
*     @param: arrayName - name of array: plus, minus
*     @param: image1 - line crossed image
*     @param: image2 - image with top line
*     @param: image3 - image with bottom line
*     @param: image4 - image without line
*     @param: image5 - single root image
*     @topic: 6
*/      
   dhtmlXTreeObject.prototype.setImageArrays=function(arrayName,image1,image2,image3,image4,image5){
      switch(arrayName){
      case "plus": this.plusArray[0]=image1; this.plusArray[1]=image2; this.plusArray[2]=image3; this.plusArray[3]=image4; this.plusArray[4]=image5; break;
      case "minus": this.minusArray[0]=image1; this.minusArray[1]=image2; this.minusArray[2]=image3; this.minusArray[3]=image4;  this.minusArray[4]=image5; break;
      }
   };

/**  
*     @desc: expand node
*     @param: itemId - id of node
*     @type: public
*     @topic: 4  
*/ 
   dhtmlXTreeObject.prototype.openItem=function(itemId){
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
      else return this._openItem(temp);
   };

/**  
*     @desc: expand node
*     @param: item - tree node object
*     @type: private
*     @editing: pro
*     @topic: 4  
*/ 
   dhtmlXTreeObject.prototype._openItem=function(item){
           if    ((this._spnFH)&&(!this._spnFH(item.id,this._getOpenState(item)))) return 0;
           this._HideShow(item,2);
            //#on_open_end_event:11052006{
               if    (this._epnFH)
                       if (!this.xmlstate)
                            this._epnFH(item.id,this._getOpenState(item));
                        else{
                            this._oie_onXLE=this.onXLE;
                            this.onXLE=this._epnFHe;
                            }
            //#}

         if ((item.parentObject)&&(this._getOpenState(item.parentObject)<0))
               this._openItem(item.parentObject);
   };
   
/**  
*     @desc: collapse node
*     @param: itemId - id of node
*     @type: public
*     @topic: 4  
*/ 
   dhtmlXTreeObject.prototype.closeItem=function(itemId){
      if (this.rootId==itemId) return 0;
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
         if (temp.closeble)
            this._HideShow(temp,1);
   };
   
   

   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
      
/**
*     @desc: return node level (position in hierarchy)
*     @param: itemId - id of node
*     @type: public
*     @return: node level
*     @topic: 4
*/
   dhtmlXTreeObject.prototype.getLevel=function(itemId){
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
      return this._getNodeLevel(temp,0);
   };
   
      

/**  
*     @desc: prevent node from closing
*     @param: itemId - id of node
*     @param: flag -  if 0 - node can't be closed, else node can be closed
*     @type: public
*     @topic: 4  
*/ 
   dhtmlXTreeObject.prototype.setItemCloseable=function(itemId,flag)
   {
      flag=convertStringToBoolean(flag);
      if ((itemId)&&(itemId.span)) 
         var temp=itemId;
      else      
         var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
         temp.closeble=flag;
   };

   /**  
*     @desc: recursive function used fo node level calculation
*     @param: itemObject - pointer to node object
*     @param: count - counter of levels        
*     @type: private
*     @topic: 4  
*/   
   dhtmlXTreeObject.prototype._getNodeLevel=function(itemObject,count){
      if (itemObject.parentObject) return this._getNodeLevel(itemObject.parentObject,count+1);
      return(count);
   };
   
   /**  
*     @desc: return number of childrens
*     @param: itemId - id of node
*     @type: public
*     @return: count of child items; true - for not loaded branches
*     @topic: 4
*/
   dhtmlXTreeObject.prototype.hasChildren=function(itemId){
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
      else 
         {
            if ( (this.XMLsource)&&(!temp.XMLload) ) return true;
            else 
               return temp.childsCount;
         };
   };
   

   /**
*     @desc: return count of leafs
*     @param: itemNode -  node object
*     @type: private
*     @edition: Professional
*     @topic: 4
*/
   dhtmlXTreeObject.prototype._getLeafCount=function(itemNode){
      var a=0;
      for (var b=0; b<itemNode.childsCount; b++)
         if (itemNode.childNodes[b].childsCount==0) a++;
      return a;
   }

   
/**
*     @desc: set new node text (HTML allowed)
*     @param: itemId - id of node
*     @param: newLabel - node text
*     @param: newTooltip - (optional)tooltip for the node
*     @type: public
*     @topic: 6
*/
   dhtmlXTreeObject.prototype.setItemText=function(itemId,newLabel,newTooltip)
   {
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
      temp.label=newLabel;
      temp.span.innerHTML=newLabel;
//#__pro_feature:01112006{
//#child_calc:01112006{
        if (this.childCalc) this._fixChildCountLabel(temp);
//#}

//#dhtmlxtootip:01112006{
	  if (this._dhxTT)
	      dhtmlxTooltip.setTooltip(temp.span.parentNode,(newTooltip||""));
	  else
//#}
//#}
	      temp.span.parentNode.title=newTooltip||"";
   };

/**
*     @desc: get item's tooltip
*     @param: itemId - id of node
*     @type: public
*     @topic: 6
*/
    dhtmlXTreeObject.prototype.getItemTooltip=function(itemId){
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return "";
	  return (temp.span.parentNode.title||"");
   };

/**  
*     @desc: refresh tree branch from xml (XML with description of child nodes rerequested from server)
*     @param: itemId - id of node, if not defined tree super root used.
*     @type: public
*     @topic: 6  
*/
   dhtmlXTreeObject.prototype.refreshItem=function(itemId){
      if (!itemId) itemId=this.rootId;
      var temp=this._globalIdStorageFind(itemId);
      this.deleteChildItems(itemId);
        this._loadDynXML(itemId);
   };

   /**  
*     @desc: set item images
*     @param: itemId - id of node
*     @param: image1 - node without childrens image
*     @param: image2 - closed node image          
*     @param: image3 - open node image         
*     @type: public
*     @topic: 6
*/
   dhtmlXTreeObject.prototype.setItemImage2=function(itemId, image1,image2,image3){
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
            temp.images[1]=image2;
            temp.images[2]=image3;
            temp.images[0]=image1;
      this._correctPlus(temp);
   };
/**
*     @desc: set item images
*     @param: itemId - id of node
*     @param: image1 - node without childrens image or closed node image (if image2 specified)
*     @param: image2 - open node image (optional)        
*     @type: public
*     @topic: 6  
*/   
   dhtmlXTreeObject.prototype.setItemImage=function(itemId,image1,image2)
   {
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
         if (image2)
         {
            temp.images[1]=image1;
            temp.images[2]=image2;
         }
         else temp.images[0]=image1;
      this._correctPlus(temp);
   };


/**
*     @desc: Returns the list of all children items from the next level of tree, separated by commas.
*     @param: itemId - id of node
*     @type: public
*     @return: list of all children items from the next level of tree, separated by commas.
*     @topic: 6
*/
   dhtmlXTreeObject.prototype.getSubItems =function(itemId)
   {
      var temp=this._globalIdStorageFind(itemId,0,1);
      if (!temp) return 0;
//#__pro_feature:01112006{
//#smart_parsing:01112006{
        if(temp.unParsed)
            return (this._getSubItemsXML(temp.unParsed));
//#}
//#}
      var z="";
      for (i=0; i<temp.childsCount; i++){
         if (!z) z=temp.childNodes[i].id;
            else z+=this.dlmtr+temp.childNodes[i].id;

                                                         }

      return z;
   };




/**
*     @desc: Returns the list of all children items from all next levels of tree, separated by commas.
*     @param: itemId - id of node
*     @edition: Professional
*     @type: private
*     @topic: 6
*/
   dhtmlXTreeObject.prototype._getAllScraggyItems =function(node)
   {
      var z="";
      for (var i=0; i<node.childsCount; i++)
        {
            if ((node.childNodes[i].unParsed)||(node.childNodes[i].childsCount>0))
            {
                    if (node.childNodes[i].unParsed)
                        var zb=this._getAllScraggyItemsXML(node.childNodes[i].unParsed,1);
                    else
                       var zb=this._getAllScraggyItems(node.childNodes[i])

                 if (zb)
                        if (z) z+=this.dlmtr+zb;
                        else z=zb;
         }
            else
               if (!z) z=node.childNodes[i].id;
             else z+=this.dlmtr+node.childNodes[i].id;
         }
          return z;
   };





/**
*     @desc: Returns the list of all children items from all next levels of tree, separated by commas.
*     @param: itemId - id of node
*     @type: private
*     @edition: Professional
*     @topic: 6
*/

   dhtmlXTreeObject.prototype._getAllFatItems =function(node)
   {
      var z="";
      for (var i=0; i<node.childsCount; i++)
        {
            if ((node.childNodes[i].unParsed)||(node.childNodes[i].childsCount>0))
            {
             if (!z) z=node.childNodes[i].id;
                else z+=this.dlmtr+node.childNodes[i].id;

                    if (node.childNodes[i].unParsed)
                        var zb=this._getAllFatItemsXML(node.childNodes[i].unParsed,1);
                    else
                       var zb=this._getAllFatItems(node.childNodes[i])

                 if (zb) z+=this.dlmtr+zb;
         }
         }
          return z;
   };


/**
*     @desc: Returns the list of all children items from all next levels of tree, separated by commas.
*     @param: itemId - id of node
*     @type: private
*     @topic: 6
*/
   dhtmlXTreeObject.prototype._getAllSubItems =function(itemId,z,node)
   {
      if (node) temp=node;
      else {
      var temp=this._globalIdStorageFind(itemId);
         };
      if (!temp) return 0;

      z="";
      for (var i=0; i<temp.childsCount; i++)
         {
         if (!z) z=temp.childNodes[i].id;
            else z+=this.dlmtr+temp.childNodes[i].id;
         var zb=this._getAllSubItems(0,z,temp.childNodes[i])

         if (zb) z+=this.dlmtr+zb;
         }

//#__pro_feature:01112006{
//#smart_parsing:01112006{
        if (temp.unParsed)
            z=this._getAllSubItemsXML(itemId,z,temp.unParsed);
//#}
//#}
          return z;
   };




   
/**  
*     @desc: select node ( and optionaly fire onselect event)
*     @type: public
*     @param: itemId - node id
*     @param: mode - If true, script function for selected node will be called.
*     @param: preserve - preserve earlie selected nodes
*     @topic: 1
*/
   dhtmlXTreeObject.prototype.selectItem=function(itemId,mode,preserve){
      mode=convertStringToBoolean(mode);
         var temp=this._globalIdStorageFind(itemId);
      if ((!temp)||(!temp.parentObject)) return 0;


      if (this._getOpenState(temp.parentObject)==-1)
            if (this.XMLloadingWarning)
                temp.parentObject.openMe=1;
            else
             this._openItem(temp.parentObject);

      //temp.onRowSelect(0,temp.htmlNode.childNodes[0].childNodes[0].childNodes[3],mode);
        var ze=null;
        if (preserve)  {
			ze=new Object; ze.ctrlKey=true;
			if (temp.i_sel) ze.skipUnSel=true;
		}
      if (mode)
         this.onRowSelect(ze,temp.htmlNode.childNodes[0].childNodes[0].childNodes[3],false);
      else
         this.onRowSelect(ze,temp.htmlNode.childNodes[0].childNodes[0].childNodes[3],true);
   };
   
/**
*     @desc: retun selected node text
*     @type: public
*     @return: text of selected node
*     @topic: 1
*/
   dhtmlXTreeObject.prototype.getSelectedItemText=function()
   {
        var str=new Array();
        for (var i=0; i<this._selected.length; i++) str[i]=this._selected[i].span.innerHTML;
      return (str.join(this.dlmtr));
   };




/**  
*     @desc: correct childNode list after node deleting
*     @type: private
*     @param: Count - childNodes collection length        
*     @param: Nodes - childNodes collection
*     @topic: 4  
*/   
   dhtmlXTreeObject.prototype._compressChildList=function(Count,Nodes)
   {
      Count--;
      for (var i=0; i<Count; i++)
      {
         if (Nodes[i]==0) { Nodes[i]=Nodes[i+1]; Nodes[i+1]=0;}
      };
   };
/**  
*     @desc: delete node
*     @type: private
*     @param: itemId - target node id
*     @param: htmlObject - target node object        
*     @param: skip - node unregistration mode (optional, used by private methods)
*     @topic: 2
*/      
   dhtmlXTreeObject.prototype._deleteNode=function(itemId,htmlObject,skip){

      if (!skip) {
        this._globalIdStorageRecSub(htmlObject);
                 }
                  
   if ((!htmlObject)||(!htmlObject.parentObject)) return 0;
   var tempos=0; var tempos2=0;
   if (htmlObject.tr.nextSibling)  tempos=htmlObject.tr.nextSibling.nodem;
   if (htmlObject.tr.previousSibling)  tempos2=htmlObject.tr.previousSibling.nodem;
   
      var sN=htmlObject.parentObject;
      var Count=sN.childsCount;
      var Nodes=sN.childNodes;
            for (var i=0; i<Count; i++)
            {
               if (Nodes[i].id==itemId) { 
               if (!skip) sN.htmlNode.childNodes[0].removeChild(Nodes[i].tr);
               Nodes[i]=0;
               break;
               }
            }
      this._compressChildList(Count,Nodes);
      if (!skip) {
        sN.childsCount--;
                 }

      if (tempos) {
      this._correctPlus(tempos);
      this._correctLine(tempos);
               }
      if (tempos2) {
      this._correctPlus(tempos2);
      this._correctLine(tempos2);
               }   
      if (this.tscheck) this._correctCheckStates(sN);
   };
/**
*     @desc: change state of node's checkbox
*     @type: public
*     @param: itemId - target node id
*     @param: state - checkbox state (0/1/unsure)
*     @topic: 5
*/
   dhtmlXTreeObject.prototype.setCheck=function(itemId,state){
      var sNode=this._globalIdStorageFind(itemId,0,1);
      if (!sNode) return;

        if (state==="unsure")
            this._setCheck(sNode,state);
        else
        {
      state=convertStringToBoolean(state);
        if ((this.tscheck)&&(this.smcheck)) this._setSubChecked(state,sNode);
      else this._setCheck(sNode,state);
        }
      if (this.smcheck)
         this._correctCheckStates(sNode.parentObject);
   };

   dhtmlXTreeObject.prototype._setCheck=function(sNode,state){
        if (((sNode.parentObject._r_logic)||(this._frbtr))&&(state))
			if (this._frbtrs){
				if (this._frbtrL)   this._setCheck(this._frbtrL,0);
				this._frbtrL=sNode;
			} else
    	        for (var i=0; i<sNode.parentObject.childsCount; i++)
	                this._setCheck(sNode.parentObject.childNodes[i],0);

      var z=sNode.htmlNode.childNodes[0].childNodes[0].childNodes[1].childNodes[0];

      if (state=="unsure") sNode.checkstate=2;
      else if (state) sNode.checkstate=1; else sNode.checkstate=0;
      if (sNode.dscheck) sNode.checkstate=sNode.dscheck;
      z.src=this.imPath+((sNode.parentObject._r_logic||this._frbtr)?this.radioArray:this.checkArray)[sNode.checkstate];
   };

/**
*     @desc: change state of node's checkbox and all childnodes checkboxes
*     @type: public
*     @param: itemId - target node id
*     @param: state - checkbox state
*     @topic: 5  
*/
dhtmlXTreeObject.prototype.setSubChecked=function(itemId,state){
   var sNode=this._globalIdStorageFind(itemId);
   this._setSubChecked(state,sNode);
   this._correctCheckStates(sNode.parentObject);
}



/**  
*     @desc: change state of node's checkbox and all childnodes checkboxes
*     @type: private
*     @param: itemId - target node id
*     @param: state - checkbox state
*     @param: sNode - target node object (optional, used by private methods)
*     @topic: 5  
*/
   dhtmlXTreeObject.prototype._setSubChecked=function(state,sNode){
      state=convertStringToBoolean(state);
      if (!sNode) return;
        if (((sNode.parentObject._r_logic)||(this._frbtr))&&(state))
            for (var i=0; i<sNode.parentObject.childsCount; i++)
                this._setSubChecked(0,sNode.parentObject.childNodes[i]);

//#__pro_feature:01112006{
//#smart_parsing:01112006{
      if (sNode.unParsed)
         this._setSubCheckedXML(state,sNode.unParsed)
//#}
//#}
        if (sNode._r_logic||this._frbtr)
           this._setSubChecked(state,sNode.childNodes[0]);
        else
      for (var i=0; i<sNode.childsCount; i++)
         {
             this._setSubChecked(state,sNode.childNodes[i]);
         };
      var z=sNode.htmlNode.childNodes[0].childNodes[0].childNodes[1].childNodes[0];

      if (state) sNode.checkstate=1;
      else    sNode.checkstate=0;
      if (sNode.dscheck)  sNode.checkstate=sNode.dscheck;



      z.src=this.imPath+((sNode.parentObject._r_logic||this._frbtr)?this.radioArray:this.checkArray)[sNode.checkstate];
   };

/**
*     @desc: return state of nodes's checkbox
*     @type: public
*     @param: itemId - target node id
*     @return: node state (0 - unchecked,1 - checked, 2 - third state)
*     @topic: 5  
*/      
   dhtmlXTreeObject.prototype.isItemChecked=function(itemId){
      var sNode=this._globalIdStorageFind(itemId);
      if (!sNode) return;      
      return   sNode.checkstate;
   };







/**
*     @desc: delete all children of node
*     @type: public
*     @param: itemId - node id
*     @topic: 2
*/
    dhtmlXTreeObject.prototype.deleteChildItems=function(itemId)
   {
      var sNode=this._globalIdStorageFind(itemId);
      if (!sNode) return;
      var j=sNode.childsCount;
      for (var i=0; i<j; i++)
      {
         this._deleteNode(sNode.childNodes[0].id,sNode.childNodes[0]);
      };
   };

/**
*     @desc: delete node
*     @type: public
*     @param: itemId - node id
*     @param: selectParent - If true parent of deleted item get selection, else no selected items leaving in tree.
*     @topic: 2  
*/      
dhtmlXTreeObject.prototype.deleteItem=function(itemId,selectParent){
    if ((!this._onrdlh)||(this._onrdlh(itemId))){
		var z=this._deleteItem(itemId,selectParent);
//#__pro_feature:01112006{
//#child_calc:01112006{
   		this._fixChildCountLabel(z);
//#}
//#}
	}

    //nb:solves standard doctype prb in IE
      this.allTree.childNodes[0].border = "1";
      this.allTree.childNodes[0].border = "0";
}
/**
*     @desc: delete node
*     @type: private
*     @param: id - node id
*     @param: selectParent - If true parent of deleted item get selection, else no selected items leaving in tree.
*     @param: skip - unregistering mode (optional, used by private methods)        
*     @topic: 2  
*/      
dhtmlXTreeObject.prototype._deleteItem=function(itemId,selectParent,skip){
      selectParent=convertStringToBoolean(selectParent);
      var sNode=this._globalIdStorageFind(itemId);
      if (!sNode) return;
        var pid=this.getParentId(itemId);
      if  ((selectParent)&&(pid!=this.rootId)) this.selectItem(pid,1);
      else
           this._unselectItem(sNode);

      if (!skip)
         this._globalIdStorageRecSub(sNode);

      var zTemp=sNode.parentObject;
      this._deleteNode(itemId,sNode,skip);
      this._correctPlus(zTemp);
      this._correctLine(zTemp);
      return    zTemp;
   };

/**  
*     @desc: uregister all child nodes of target node
*     @type: private
*     @param: itemObject - node object
*     @topic: 3  
*/      
   dhtmlXTreeObject.prototype._globalIdStorageRecSub=function(itemObject){
      for(var i=0; i<itemObject.childsCount; i++)
      {
         this._globalIdStorageRecSub(itemObject.childNodes[i]);
         this._globalIdStorageSub(itemObject.childNodes[i].id);
      };
      this._globalIdStorageSub(itemObject.id);
   };

/**  
*     @desc: create new node next to specified
*     @type: public
*     @param: itemId - node id
*     @param: newItemId - new node id
*     @param: itemText - new node text
*     @param: itemActionHandler - function fired on node select event (optional)
*     @param: image1 - image for node without childrens; (optional)
*     @param: image2 - image for closed node; (optional)
*     @param: image3 - image for opened node (optional)    
*     @param: optionStr - options string (optional)            
*     @param: childs - node childs flag (for dynamical trees) (optional)
*     @topic: 2  
*/
   dhtmlXTreeObject.prototype.insertNewNext=function(itemId,newItemId,itemText,itemActionHandler,image1,image2,image3,optionStr,childs){
      var sNode=this._globalIdStorageFind(itemId);
      if ((!sNode)||(!sNode.parentObject)) return (0);

      var nodez=this._attachChildNode(0,newItemId,itemText,itemActionHandler,image1,image2,image3,optionStr,childs,sNode);
//#__pro_feature:01112006{
//#child_calc:01112006{
      if ((!this.XMLloadingWarning)&&(this.childCalc))  this._fixChildCountLabel(sNode.parentObject);
//#}
//#}
        return nodez;
   };


   
/**
*     @desc: retun node id by index
*     @type: public
*     @param: itemId - parent node id
*     @param: index - index of node, 0 based
*     @return: node id
*     @topic: 1
*/
   dhtmlXTreeObject.prototype.getItemIdByIndex=function(itemId,index){
       var z=this._globalIdStorageFind(itemId);
       if ((!z)||(index>z.childsCount)) return null;
          return z.childNodes[index].id;
   };

/**
*     @desc: retun child node id by index
*     @type: public
*     @param: itemId - parent node id        
*     @param: index - index of child node
*     @return: node id
*     @topic: 1
*/      
   dhtmlXTreeObject.prototype.getChildItemIdByIndex=function(itemId,index){
       var z=this._globalIdStorageFind(itemId);
       if ((!z)||(index>=z.childsCount)) return null;
          return z.childNodes[index].id;
   };



   

/**
*     @desc: set function called when drag-and-drop event occured
*     @param: aFunc - event handling function
*     @type: public
*     @topic: 0,7
*     @event:    onDrag
*     @eventdesc: Event occured after item was dragged and droped on another item, but before item moving processed.
      Event also raised while programmatic moving nodes.
*     @eventparam:  ID of source item
*     @eventparam:  ID of target item
*     @eventparam:  if node droped as sibling then contain id of item before whitch source node will be inserted
*     @eventparam:  source Tree object
*     @eventparam:  target Tree object
*     @eventreturn:  true - confirm drag-and-drop; false - deny drag-and-drop;
*/
   dhtmlXTreeObject.prototype.setDragHandler=function(func){  if (typeof(func)=="function") this.dragFunc=func; else this.dragFunc=eval(func);  };
   
   /**
*     @desc: clear selection from node
*     @param: htmlNode - pointer to node object
*     @type: private
*     @topic: 1
*/
    dhtmlXTreeObject.prototype._clearMove=function(){
		if (this._lastMark){
	   		this._lastMark.className=this._lastMark.className.replace(/dragAndDropRow/g,"");
	   		this._lastMark=null;
		}
//#__pro_feature:01112006{
//#complex_move:01112006{
		this.selectionBar.style.display="none";
//#}
//#}
		this.allTree.className=this.allTree.className.replace(" selectionBox","");
   };

   /**  
*     @desc: enable/disable drag-and-drop
*     @type: public
*     @param: mode - enabled/disabled [ can be true/false/temporary_disabled - last value mean that tree can be D-n-D can be switched to true later ]
*     @param: rmode - enabled/disabled drag and drop on super root
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableDragAndDrop=function(mode,rmode){
        if  (mode=="temporary_disabled"){
            this.dADTempOff=false;
            mode=true;                  }
        else
            this.dADTempOff=true;

      this.dragAndDropOff=convertStringToBoolean(mode);
         if (this.dragAndDropOff) this.dragger.addDragLanding(this.allTree,this);
        if (arguments.length>1)
            this._ddronr=(!convertStringToBoolean(rmode));
       };   

/**
*     @desc: set selection on node
*     @param: node - pointer to node object
*     @type: private
*     @topic: 1
*/    
   dhtmlXTreeObject.prototype._setMove=function(htmlNode,x,y){
      if (htmlNode.parentObject.span) {
      //window.status=x;
      var a1=getAbsoluteTop(htmlNode);
      var a2=getAbsoluteTop(this.allTree);

      this.dadmodec=this.dadmode;//this.dadmode;
      this.dadmodefix=0;
//#__pro_feature:01112006{
//#complex_move:01112006{
      if (this.dadmode==2)
      {

      var z=y-a1+this.allTree.scrollTop+(document.body.scrollTop||document.documentElement.scrollTop)-2-htmlNode.offsetHeight/2;
      if ((Math.abs(z)-htmlNode.offsetHeight/6)>0)
      {
         this.dadmodec=1;
         //sibbling zone
         if (z<0)
            this.dadmodefix=0-htmlNode.offsetHeight;
      }
      else this.dadmodec=0;

      }
      if (this.dadmodec==0)
         {
//#}
//#}

			var zN=htmlNode.parentObject.span;
			zN.className+=" dragAndDropRow";
			this._lastMark=zN;
//#__pro_feature:01112006{
//#complex_move:01112006{
         }
      else{
 	  	 this._clearMove();
         this.selectionBar.style.top=(a1-a2+((parseInt(htmlNode.parentObject.span.parentNode.previousSibling.childNodes[0].style.height)||18)-1)+this.dadmodefix)+"px";
         this.selectionBar.style.left="5px";
           if (this.allTree.offsetWidth>20)
                this.selectionBar.style.width=(this.allTree.offsetWidth-(_isFF?30:25))+"px";
         this.selectionBar.style.display="";
         }
//#}
//#}
         if (this.autoScroll)
         {
               //scroll down
               if ( (a1-a2-parseInt(this.allTree.scrollTop))>(parseInt(this.allTree.offsetHeight)-50) )
                  this.allTree.scrollTop=parseInt(this.allTree.scrollTop)+20;
               //scroll top
               if ( (a1-a2)<(parseInt(this.allTree.scrollTop)+30) )
                  this.allTree.scrollTop=parseInt(this.allTree.scrollTop)-20;
         }
      }
   };



/**
*     @desc: create html element for dragging
*     @type: private
*     @param: htmlObject - html node object
*     @topic: 1
*/
dhtmlXTreeObject.prototype._createDragNode=function(htmlObject,e){
      if (!this.dADTempOff) return null;

     var obj=htmlObject.parentObject;
    if (!obj.i_sel)
         this._selectItem(obj,e);

//#__pro_feature:01112006{
//#multiselect:01112006{
      this._checkMSelectionLogic();
//#}
//#}
      var dragSpan=document.createElement('div');

            var text=new Array();
            if (this._itim_dg)
                    for (var i=0; i<this._selected.length; i++)
                        text[i]="<table cellspacing='0' cellpadding='0'><tr><td><img width='18px' height='18px' src='"+this._selected[i].span.parentNode.previousSibling.childNodes[0].src+"'></td><td>"+this._selected[i].span.innerHTML+"</td></tr><table>";
            else
                text=this.getSelectedItemText().split(this.dlmtr);

            dragSpan.innerHTML=text.join("");
         dragSpan.style.position="absolute";
         dragSpan.className="dragSpanDiv";
      this._dragged=(new Array()).concat(this._selected);
     return dragSpan;
}



/**  
*     @desc: focus item in tree
*     @type: private
*     @param: item - node object
*     @edition: Professional
*     @topic: 0  
*/
dhtmlXTreeObject.prototype._focusNode=function(item){
      var z=getAbsoluteTop(item.htmlNode)-getAbsoluteTop(this.allTree);
      if ((z>(this.allTree.scrollTop+this.allTree.offsetHeight-30))||(z<this.allTree.scrollTop))
      this.allTree.scrollTop=z;
   };




              








///DragAndDrop

dhtmlXTreeObject.prototype._preventNsDrag=function(e){
   if ((e)&&(e.preventDefault)) { e.preventDefault(); return false; }
   return false;
}

dhtmlXTreeObject.prototype._drag=function(sourceHtmlObject,dhtmlObject,targetHtmlObject){

      if (this._autoOpenTimer) clearTimeout(this._autoOpenTimer);

      if (!targetHtmlObject.parentObject){
            targetHtmlObject=this.htmlNode.htmlNode.childNodes[0].childNodes[0].childNodes[1].childNodes[0];
            this.dadmodec=0;
            }

      this._clearMove();
      var z=sourceHtmlObject.parentObject.treeNod;
        if ((z)&&(z._clearMove))   z._clearMove("");

       if ((!this.dragMove)||(this.dragMove()))
          {
              if ((!z)||(!z._clearMove)||(!z._dragged)) var col=new Array(sourceHtmlObject.parentObject);
              else var col=z._dragged;

                for (var i=0; i<col.length; i++){
                   var newID=this._moveNode(col[i],targetHtmlObject.parentObject);

                   if ((newID)&&(!this._sADnD)) this.selectItem(newID,0,1);
                }

         }
        if (z) z._dragged=new Array();


}

dhtmlXTreeObject.prototype._dragIn=function(htmlObject,shtmlObject,x,y){

                    if (!this.dADTempOff) return 0;
                    var fobj=shtmlObject.parentObject;
                    var tobj=htmlObject.parentObject;
	                if ((!tobj)&&(this._ddronr)) return;
                    if ((this._onDrInFunc)&&(!this._onDrInFunc(fobj.id,tobj?tobj.id:null,fobj.treeNod,this)))
						return 0;


					if (!tobj)
		               this.allTree.className+=" selectionBox";
					else
					{
	                    if (fobj.childNodes==null){
		                	this._setMove(htmlObject,x,y);
        	             	return htmlObject;
                    	}

	                    var stree=fobj.treeNod;
    	                for (var i=0; i<stree._dragged.length; i++)
                        if (this._checkPNodes(tobj,stree._dragged[i]))
                            return 0;
//#__pro_feature:01112006{
//#complex_move:01112006{
                       tobj.span.parentNode.appendChild(this.selectionBar);
//#}
//#}
                       this._setMove(htmlObject,x,y);
                       if (this._getOpenState(tobj)<=0){
                           this._autoOpenId=tobj.id;
                             this._autoOpenTimer=window.setTimeout(new callerFunction(this._autoOpenItem,this),1000);
                                    }
					}

				return htmlObject;

}
dhtmlXTreeObject.prototype._autoOpenItem=function(e,treeObject){
   treeObject.openItem(treeObject._autoOpenId);
};
dhtmlXTreeObject.prototype._dragOut=function(htmlObject){
this._clearMove();
if (this._autoOpenTimer) clearTimeout(this._autoOpenTimer);
 }


//#__pro_feature:01112006{

/**  
*     @desc: return next node
*     @type: private
*     @param: item - node object
*     @param: mode - inner flag
*     @return: next node or -1
*     @topic: 2
*/
dhtmlXTreeObject.prototype._getNextNode=function(item,mode){
   if ((!mode)&&(item.childsCount)) return item.childNodes[0];
   if (item==this.htmlNode)
      return -1;
   if ((item.tr)&&(item.tr.nextSibling)&&(item.tr.nextSibling.nodem))
   return item.tr.nextSibling.nodem;

   return this._getNextNode(item.parentObject,true);
};

/**  
*     @desc: return last child of item (include all sub-child collections)
*     @type: private
*     @param: item - node object
*     @topic: 2  
*/
dhtmlXTreeObject.prototype._lastChild=function(item){
   if (item.childsCount)
      return this._lastChild(item.childNodes[item.childsCount-1]);
   else return item;
};

/**  
*     @desc: return previous node
*     @type: private
*     @param: item - node object
*     @param: mode - inner flag
*     @return: previous node or -1
*     @topic: 2  
*/
dhtmlXTreeObject.prototype._getPrevNode=function(node,mode){
   if ((node.tr)&&(node.tr.previousSibling)&&(node.tr.previousSibling.nodem))
   return this._lastChild(node.tr.previousSibling.nodem);

   if (node.parentObject)
      return node.parentObject;
   else return -1;
};



//#find_item:01112006{

/**
*     @desc: find tree item by text, select and focus it
*     @type: public
*     @param: searchStr - search text
*     @param: direction - 0: top -> bottom; 1: bottom -> top
*     @param: top - 1: start searching from top
*     @return: node id
*     @edition: Professional
*     @topic: 2
*/
dhtmlXTreeObject.prototype.findItem=function(searchStr,direction,top){
   var z=this._findNodeByLabel(searchStr,direction,(top?this.htmlNode:null));
   if (z){
      this.selectItem(z.id,true);
      this._focusNode(z);
      return z.id;
      }
      else return null;
}

/**  
*     @desc: find tree item by text
*     @type: public
*     @param: searchStr - search text
*     @param: direction - 0: top -> bottom; 1: bottom -> top
*     @param: top - 1: start searching from top
*     @return: node id
*     @edition: Professional
*     @topic: 2  
*/
dhtmlXTreeObject.prototype.findItemIdByLabel=function(searchStr,direction,top){
   var z=this._findNodeByLabel(searchStr,direction,(top?this.htmlNode:null));
   if (z)
      return z.id
   else return null;
}

//#smart_parsing:01112006{
/**  
*     @desc: find tree item by text in unParsed XML
*     @type: private
*     @param: node - start xml node
*     @param: field - name of xml attribute
*     @param: cvalue - search text
*     @return: true/false
*     @topic: 2  
*/
dhtmlXTreeObject.prototype.findStrInXML=function(node,field,cvalue){
   for (var i=0; i<node.childNodes.length; i++)
   {
   if (node.childNodes[i].nodeType==1)
      {
        var z=node.childNodes[i].getAttribute(field);
      if ((z)&&(z.toLowerCase().search(cvalue)!=-1))
         return true;
      if (this.findStrInXML(node.childNodes[i],field,cvalue)) return true;
      }
   }
   return false;
}
//#}

/**  
*     @desc: find tree item by text
*     @type: private
*     @param: searchStr - search text
*     @param: direction - 0: top -> bottom; 1: bottom -> top
*     @param: fromNode - node from which search begin
*     @return: node id
*     @topic: 2  
*/
dhtmlXTreeObject.prototype._findNodeByLabel=function(searchStr,direction,fromNode){
   //trim
   var searchStr=searchStr.replace(new RegExp("^( )+"),"").replace(new RegExp("( )+$"),"");
   searchStr =  new RegExp(searchStr.replace(/([\*\+\\\[\]\(\)]{1})/gi,"\\$1").replace(/ /gi,".*"),"gi");

   //get start node
   if (!fromNode)
      {
      fromNode=this._selected[0];
      if (!fromNode) fromNode=this.htmlNode;
      }

   var startNode=fromNode;

   //first step
   if (!direction){
      if ((fromNode.unParsed)&&(this.findStrInXML(fromNode.unParsed,"text",searchStr)))
      this.reParse(fromNode);
   fromNode=this._getNextNode(startNode);
   if (fromNode==-1) fromNode=this.htmlNode.childNodes[0];
   }
   else
   {
      var z2=this._getPrevNode(startNode);
      if (z2==-1) z2=this._lastChild(this.htmlNode);
      if ((z2.unParsed)&&(this.findStrInXML(z2.unParsed,"text",searchStr)))
      {   this.reParse(z2); fromNode=this._getPrevNode(startNode); }
      else fromNode=z2;
      if (fromNode==-1) fromNode=this._lastChild(this.htmlNode);
   }



   while ((fromNode)&&(fromNode!=startNode)){
      if ((fromNode.label)&&(fromNode.label.search(searchStr)!=-1))
            return (fromNode);

      if (!direction){
      if (fromNode==-1) { if (startNode==this.htmlNode) break; fromNode=this.htmlNode.childNodes[0]; }
      if ((fromNode.unParsed)&&(this.findStrInXML(fromNode.unParsed,"text",searchStr)))
         this.reParse(fromNode);
      fromNode=this._getNextNode(fromNode);
      }
      else
      {
      var z2=this._getPrevNode(fromNode);
      if (z2==-1) z2=this._lastChild(this.htmlNode);
      if ((z2.unParsed)&&(this.findStrInXML(z2.unParsed,"text",searchStr)))
         {   this.reParse(z2); fromNode=this._getPrevNode(fromNode); }
      else fromNode=z2;
      if (fromNode==-1) fromNode=this._lastChild(this.htmlNode);
      }
   }
   return null;
};

//#}
//#}

//#__pro_feature:01112006{
//#complex_move:01112006{

/**
*     @desc: set Drag-And-Drop behavior (child - drop as chils, sibling - drop as sibling, complex - complex drop behaviour )
*     @type: public
*     @edition: Professional
*     @param: mode - behavior name (child,sibling,complex)
*     @param: select - select droped node after drag-n-drop, true by default
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.setDragBehavior=function(mode,select){
		this._sADnD=(!convertStringToBoolean(select));
		switch (mode) {
			case "child": this.dadmode=0; break;
			case "sibling": this.dadmode=1; break;
			case "complex": this.dadmode=2; break;
		}    };


/**  
*     @desc: move item (inside of tree)
*     @type:  public
*     @param: itemId - item Id
*     @param: mode - moving mode (left,up,down,item_child,item_sibling,item_sibling_next,up_strict,down_strict)
*     @param: targetId - target Node in item_child and item_sibling mode
*     @param: targetTree - used for moving between trees (optional)
*     @return: node id
*     @edition: Professional
*     @topic: 2
*/
dhtmlXTreeObject.prototype.moveItem=function(itemId,mode,targetId,targetTree)
{
      var sNode=this._globalIdStorageFind(itemId);
      if (!sNode) return (0);

      switch(mode){
      case "right": alert('Not supported yet');
         break;
      case "item_child":
              var tNode=(targetTree||this)._globalIdStorageFind(targetId);
              if (!tNode) return (0);
            (targetTree||this)._moveNodeTo(sNode,tNode,0);
         break;
      case "item_sibling":
              var tNode=(targetTree||this)._globalIdStorageFind(targetId);
              if (!tNode) return (0);
            (targetTree||this)._moveNodeTo(sNode,tNode.parentObject,tNode);
         break;
      case "item_sibling_next":
              var tNode=(targetTree||this)._globalIdStorageFind(targetId);
              if (!tNode) return (0);
                  if ((tNode.tr)&&(tNode.tr.nextSibling)&&(tNode.tr.nextSibling.nodem))
                (targetTree||this)._moveNodeTo(sNode,tNode.parentObject,tNode.tr.nextSibling.nodem);
                else
                    (targetTree||this)._moveNodeTo(sNode,tNode.parentObject);
         break;
      case "left": if (sNode.parentObject.parentObject)
            this._moveNodeTo(sNode,sNode.parentObject.parentObject,sNode.parentObject);
         break;
      case "up": var z=this._getPrevNode(sNode);
               if ((z==-1)||(!z.parentObject)) return;
               this._moveNodeTo(sNode,z.parentObject,z);
         break;
      case "up_strict": var z=this._getIndex(sNode);
                          if (z!=0)
                         this._moveNodeTo(sNode,sNode.parentObject,sNode.parentObject.childNodes[z-1]);
         break;
      case "down_strict": var z=this._getIndex(sNode);
                            var count=sNode.parentObject.childsCount-2;
                            if (z==count)
                             this._moveNodeTo(sNode,sNode.parentObject);
                            else if (z<count)
                             this._moveNodeTo(sNode,sNode.parentObject,sNode.parentObject.childNodes[z+2]);
         break;
      case "down": var z=this._getNextNode(this._lastChild(sNode));
               if ((z==-1)||(!z.parentObject)) return;
               if (z.parentObject==sNode.parentObject)
                  var z=this._getNextNode(z);
                        if (z==-1){
                        this._moveNodeTo(sNode,sNode.parentObject);
                        }
                        else
                        {
                       if ((z==-1)||(!z.parentObject)) return;
                       this._moveNodeTo(sNode,z.parentObject,z);
                        }
         break;                           
      }
}

//#}
//#}







/**
*     @desc: load xml for tree branch
*     @param: id - id of parent node
*     @param: src - path to xml, optional
*     @type: private
*     @topic: 1
*/
   dhtmlXTreeObject.prototype._loadDynXML=function(id,src) {
   		src=src||this.XMLsource;
        var sn=(new Date()).valueOf();
        this._ld_id=id;
//#__pro_feature:01112006{
        if (this.xmlalb=="function"){
            if (src) src(this._escape(id));
            }
        else
        if (this.xmlalb=="name")
            this.loadXML(src+this._escape(id));
        else
        if (this.xmlalb=="xmlname")
            this.loadXML(src+this._escape(id)+".xml?uid="+sn);
        else
//#}
            this.loadXML(src+getUrlSymbol(src)+"uid="+sn+"&id="+this._escape(id));
        };


//#__pro_feature:01112006{
//#multiselect:01112006{
/**
*     @desc: enable multiselection
*     @beforeInit: 1
*     @param: mode - 1 - on, 0 - off;
*     @param: strict - 1 - on, 0 - off; in strict mode only items on the same level can be selected
*     @type: public
*     @edition: Professional
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableMultiselection=function(mode,strict) {
        this._amsel=convertStringToBoolean(mode);
        this._amselS=convertStringToBoolean(strict);
        };

/**
*     @desc: check logic of selection
*     @type: private
*     @edition: Professional
*     @topic: 0
*/
dhtmlXTreeObject.prototype._checkMSelectionLogic=function() {
            var usl=new Array();
         for (var i=0; i<this._selected.length; i++)
            for (var j=0; j<this._selected.length; j++)
                  if ((i!=j)&&(this._checkPNodes(this._selected[j],this._selected[i])))
                            usl[usl.length]=this._selected[j];

         for (var i=0; i<usl.length; i++)
             this._unselectItem(usl[i]);

         };
//#}
//#}




/**
*     @desc: check possibility of drag-and-drop
*     @type: private
*     @param: itemId - draged node id
*     @param: htmlObject - droped node object
*     @param: shtmlObject - sourse node object
*     @topic: 6
*/
    dhtmlXTreeObject.prototype._checkPNodes=function(item1,item2){
      if (item2==item1) return 1
      if (item1.parentObject) return this._checkPNodes(item1.parentObject,item2); else return 0;
   };



//#__pro_feature:01112006{
//#distributed_load:01112006{

/**
*     @desc: enable distributed parsing of long items list
*     @type: public
*     @edition: Professional
*     @param: mode - true/false
*     @param: count - critical count to start distibuting (optional)
*     @param: delay - delay between distributed calls, ms (optional)
*     @topic: 2
*/
dhtmlXTreeObject.prototype.enableDistributedParsing=function(mode,count,delay){
    this._edsbps=convertStringToBoolean(mode);
    this._edsbpsA=new Array();
    this._edsbpsC=count||10;
    this._edsbpsD=delay||250;
}
/**
*     @desc: get current state of distributed parsing
*     @type: public
*     @edition: Professional
*     @returns: true - still parsing; false - parsing finished
*     @topic: 2
*/
dhtmlXTreeObject.prototype.getDistributedParsingState=function(){
    return (!((!this._edsbpsA)||(!this._edsbpsA.length)));
}
/**
*     @desc: get current parsing state of item
*     @type: public
*     @edition: Professional
*     @returns: 1 - item already parsed; 0 - item not parsed yet; -1 - item in parsing process
*     @topic: 2
*/
dhtmlXTreeObject.prototype.getItemParsingState=function(itemId){
    var z=this._globalIdStorageFind(itemId,true,true)
    if (!z) return 0;
    if (this._edsbpsA)
        for (var i=0; i<this._edsbpsA.length; i++)
            if (this._edsbpsA[i][2]==itemId) return -1;

    return 1;
}

dhtmlXTreeObject.prototype._distributedStart=function(node,start,parentId,level,start2){
    if (!this._edsbpsA)
        this._edsbpsA=new Array();
    this._edsbpsA[this._edsbpsA.length]=[node,start,parentId,level,start2];
}

dhtmlXTreeObject.prototype._distributedStep=function(pId){
    var self=this;
    if ((!this._edsbpsA)||(!this._edsbpsA.length)) {
         self.XMLloadingWarning=0;
         return;
         }
    var z=this._edsbpsA[0];
    this.parsedArray=new Array();
    this._parseXMLTree(this,z[0],z[2],z[3],null,z[1]);
    var zkx=this._globalIdStorageFind(z[2]);
   this._redrawFrom(this,zkx,z[4],this._getOpenState(zkx));
    var chArr=this.setCheckList.split(this.dlmtr);
   for (var n=0; n<chArr.length; n++)
      if (chArr[n]) this.setCheck(chArr[n],1);

    this._edsbpsA=(new Array()).concat(this._edsbpsA.slice(1));


    if ((!this._edsbpsA.length)&&(this.onXLE)){
         window.setTimeout( function(){self.onXLE(self,pId)},1);
            self.xmlstate=0;
            }
}

dhtmlXTreeObject.prototype.enablePaging=function(mode,page_size){
    this._epgps=convertStringToBoolean(mode);
    this._epgpsC=page_size||50;
}


dhtmlXTreeObject.prototype._setPrevPageSign=function(node,pos,level,xmlnode){
    var z=document.createElement("DIV");
    z.innerHTML="Previous "+this._epgpsC+" items";
    z.className="dhx_next_button";
    var self=this;
    z.onclick=function(){
        self._prevPageCall(this);
    }
    z._pageData=[node,pos,level,xmlnode];
    var w=node.childNodes[0];
    var w2=w.span.parentNode.parentNode.parentNode.parentNode.parentNode;
    w2.insertBefore(z,w2.firstChild);
}

dhtmlXTreeObject.prototype._setNextPageSign=function(node,pos,level,xmlnode){
    var z=document.createElement("DIV");
    z.innerHTML="Next "+this._epgpsC+" items";
    z.className="dhx_next_button";
    var self=this;
    z.onclick=function(){
        self._nextPageCall(this);
    }
    z._pageData=[node,pos,level,xmlnode];
    var w=node.childNodes[node.childsCount-1];
    w.span.parentNode.parentNode.parentNode.parentNode.parentNode.appendChild(z);
}

dhtmlXTreeObject.prototype._nextPageCall=function(node){
    //delete all existing nodes
    tree.deleteChildItems(node._pageData[0].id);
    node.parentNode.removeChild(node);
    var f=this._getOpenState(node._pageData[0]);
    this._parseXMLTree(this,node._pageData[3],node._pageData[0].id,node._pageData[2],null,node._pageData[1]);
   this._redrawFrom(this,node._pageData[0],0);
    if (f>-1) this._openItem(node._pageData[0]);
    node._pageData=null;
}

dhtmlXTreeObject.prototype._prevPageCall=function(node){
    //delete all existing nodes
    tree.deleteChildItems(node._pageData[0].id);
    node.parentNode.removeChild(node);
    var f=this._getOpenState(node._pageData[0]);
    var xz=node._pageData[1]-this._epgpsC;
    if (xz<0) xz=0;
    this._parseXMLTree(this,node._pageData[3],node._pageData[0].id,node._pageData[2],null,xz);
   this._redrawFrom(this,node._pageData[0],0);
    if (f>-1) this._openItem(node._pageData[0]);
    node._pageData=null;
}

//#}
//#}




//#__pro_feature:01112006{
//#text_tree:01112006{
/**
*     @desc: replace images with text signs
*     @type: public
*     @param: mode - true/false
*     @edition: Professional
*     @topic: 1
*/
dhtmlXTreeObject.prototype.enableTextSigns=function(mode){
    this._txtimg=convertStringToBoolean(mode);
}
//#}
//#}

/**
*   @desc:  prevent caching in IE  by adding random seed to URL string
*   @param: mode - enable/disable random seed ( disabled by default )
*   @type: public
*   @topic: 0
*/
dhtmlXTreeObject.prototype.preventIECashing=function(mode){
      this.no_cashe = convertStringToBoolean(mode);
      this.XMLLoader.rSeed=this.no_cashe;
}



//#tree_extra:01112006{
//#__pro_feature:01112006{


/**
*     @desc: refresh specified tree branch (get XML from server, add new nodes, remove not used nodes)
*     @param: itemId -  top node in branch
*     @param: source - server side script , optional
*     @type: public
*     @edition: Professional
*     @topic: 6
*/
   dhtmlXTreeObject.prototype.smartRefreshItem=function(itemId,source){
		var sNode=this._globalIdStorageFind(itemId);
		for (var i=0; i<sNode.childsCount; i++)
			sNode.childNodes[i]._dmark=true;

		this.waitUpdateXML=true;
		this._loadDynXML(itemId,source);
   };


/**
*     @desc: refresh specified tree nodes (get XML from server and updat only nodes included in itemIdList)
*     @param: itemIdList - list of node identificators
*     @param: source - server side script
*     @type: public
*     @edition: Professional
*     @topic: 6
*/
   dhtmlXTreeObject.prototype.refreshItems=function(itemIdList,source){
   		var z=itemIdList.toString().split(this.dlmtr);
		this.waitUpdateXML=new Array();
		for (var i=0; i<z.length; i++)
			this.waitUpdateXML[z[i]]=true;
        this.loadXML((source||this.XMLsource)+getUrlSymbol(source||this.XMLsource)+"ids="+this._escape(itemIdList));
   };


/**
*     @desc: update item properties
*     @param: itemId - list of node identificators
*     @param: name - list of node identificators, optional
*     @param: im0 - list of node identificators, optional
*     @param: im1 - list of node identificators, optional
*     @param: im2 - list of node identificators, optional
*     @param: achecked - list of node identificators, optional
*     @type: public
*     @edition: Professional
*     @topic: 6
*/
   dhtmlXTreeObject.prototype.updateItem=function(itemId,name,im0,im1,im2,achecked){
      var sNode=this._globalIdStorageFind(itemId);
      if (name) sNode.label=name;
      sNode.images=new Array(im0||this.imageArray[0],im1||this.imageArray[1],im2||this.imageArray[2]);
	  this.setItemText(itemId,name);
      if (achecked) this._setCheck(sNode,true);
      this._correctPlus(sNode);
	  sNode._dmark=false;
      return sNode;
   };

/**
*     @desc: set function called after drag-and-drap event occured
*     @param: func - event handling function
*     @type: public
*     @edition: Professional
*     @topic: 0,7
*     @event:  onDrop
*     @eventdesc:  Event raised after drag-and-drop processed. Event also raised while programmatic moving nodes.
*     @eventparam:  ID of source item (ID after inserting in tree, my be not equal to initial ID)
*     @eventparam:  ID of target item
*     @eventparam:  if node droped as sibling then contain id of item before whitch source node will be inserted
*     @eventparam:  source Tree object
*     @eventparam:  target Tree object
*/
   dhtmlXTreeObject.prototype.setDropHandler=function(func){  if (typeof(func)=="function") this.dropFunc=func; else this.dropFunc=eval(func);  };

/**
*     @desc: set function called before xml loading/parsing started
*     @param: func - event handling function
*     @type: public
*     @edition: Professional
*     @topic: 0,7
*     @event:  onXMLLoadingStart
*     @eventdesc: event fired simultaneously with starting XML parsing
*     @eventparam: tree object
*     @eventparam: item id, for which xml loaded
*/
   dhtmlXTreeObject.prototype.setOnLoadingStart=function(func){  if (typeof(func)=="function") this.onXLS=func; else this.onXLS=eval(func); };
      /**
*     @desc: set function called after xml loading/parsing ended
*     @param: func - event handling function
*     @type: public
*     @edition: Professional
*     @topic: 0,7
*     @event:  onXMLLoadingEnd
*     @eventdesc: event fired simultaneously with ending XML parsing, new items already available in tree
*     @eventparam: tree object
*     @eventparam: last parsed parent id
*/
     dhtmlXTreeObject.prototype.setOnLoadingEnd=function(func){  if (typeof(func)=="function") this.onXLE=func; else this.onXLE=eval(func); };

/**
*     @desc: disable checkbox
*     @param: itemId - Id of tree item
*     @param: mode - 1 - on, 0 - off;
*     @type: public
*     @edition: Professional
*     @topic: 5
*/
   dhtmlXTreeObject.prototype.disableCheckbox=function(itemId,mode) {
            if (typeof(itemId)!="object")
             var sNode=this._globalIdStorageFind(itemId,0,1);
            else
                var sNode=itemId;
         if (!sNode) return;
            sNode.dscheck=convertStringToBoolean(mode)?(((sNode.checkstate||0)%3)+3):((sNode.checkstate>2)?(sNode.checkstate-3):sNode.checkstate);
            this._setCheck(sNode);
                if (sNode.dscheck<3) sNode.dscheck=false;
         };

/**
*     @desc: define which script be called on dynamic loading
*     @param: mode - id for some_script?id=item_id ;  name for  some_scriptitem_id, xmlname for  some_scriptitem_id.xml ; function for calling user defined handler
*     @type: public
*     @edition: Professional
*     @topic: 1
*/
   dhtmlXTreeObject.prototype.setXMLAutoLoadingBehaviour=function(mode) {
            this.xmlalb=mode;
         };


/**
*     @desc: enable smart checkboxes ,true by default (auto checking childs and parents for 3-state checkboxes)
*     @param: mode - 1 - on, 0 - off;
*     @type: public
*     @edition: Professional
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableSmartCheckboxes=function(mode) { this.smcheck=convertStringToBoolean(mode); };

/**
*     @desc: return current state of XML loading
*     @type: public
*     @edition: Professional
*     @return: current state, true - xml loading now
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.getXMLState=function(){ return (this.xmlstate==1); };

/**
*     @desc: set top offset for item
*     @type: public
*     @param: itemId - id of item
*     @param: value - value of top offset
*     @edition: Professional
*     @topic: 2
*/
dhtmlXTreeObject.prototype.setItemTopOffset=function(itemId,value){
  if (typeof(itemId)=="string")
    var node=this._globalIdStorageFind(itemId);
  else
    var node=itemId;

  var z=node.span.parentNode.parentNode;
  for (var i=0; i<z.childNodes.length; i++){
    if (i!=0)
      z.childNodes[i].style.height=18+parseInt(value)+"px";
    else{
      var w=z.childNodes[i].firstChild;
      if (z.childNodes[i].firstChild.tagName!='DIV'){
          w=document.createElement("DIV");
          z.childNodes[i].insertBefore(w,z.childNodes[i].firstChild);
      }
      w.style.height=parseInt(value)+"px";
      w.style.backgroundImage="url("+this.imPath+this.lineArray[5]+")";
      w.innerHTML="&nbsp;";
      w.style.overflow='hidden';
          if (parseInt(value)==0)
            z.childNodes[i].removeChild(w);
    }
      z.childNodes[i].vAlign="bottom";
  }

}

/**
*     @desc: set size of gfx icons
*     @type:  public
*     @param: newWidth - new icon width
*     @param: newHeight - new icon height
*     @param: itemId - item Id, if skipped set default value for all new icons, optional
*     @edition: Professional
*     @topic: 2
*/
dhtmlXTreeObject.prototype.setIconSize=function(newWidth,newHeight,itemId)
{
      if (itemId){
         if ((itemId)&&(itemId.span))
            var sNode=itemId;
         else
            var sNode=this._globalIdStorageFind(itemId);

         if (!sNode) return (0);
         var img=sNode.span.parentNode.previousSibling.childNodes[0];
            img.style.width=newWidth;
            img.style.height=newHeight;
         }
      else{
         this.def_img_x=newWidth;
         this.def_img_y=newHeight;
      }
}

/**
*     @desc: get source of item's image
*     @type: public
*     @param: itemId - id of item
*     @param: imageInd - index of image ( 0 - leaf, 1 - closed folder, 2 - opened folder)
*     @param: value - value of top offset
*     @edition: Professional
*     @topic: 2
*/
dhtmlXTreeObject.prototype.getItemImage=function(itemId,imageInd,fullPath){
    var node=this._globalIdStorageFind(itemId);
    if (!node) return "";
    var img=node.images[imageInd||0];
    if (fullPath) img=this.imPath+img;
    return img;
}

/**
*     @desc: replace checkboxes with radio buttons
*     @type: public
*     @param: mode - true/false
*     @param: itemId - node for which replacement called (optional)
*     @edition: Professional
*     @topic: 1
*/
dhtmlXTreeObject.prototype.enableRadioButtons=function(itemId,mode){
    if (arguments.length==1){
        this._frbtr=convertStringToBoolean(itemId);
        this.checkBoxOff=this.checkBoxOff||this._frbtr;
        return;
        }


    var node=this._globalIdStorageFind(itemId);
    if (!node) return "";
    mode=convertStringToBoolean(mode);
    if ((mode)&&(!node._r_logic)){
            node._r_logic=true;
            for (var i=0; i<node.childsCount; i++)
                this._setCheck(node.childNodes[i],node.childNodes[i].checkstate);
        }

    if ((!mode)&&(node._r_logic)){
            node._r_logic=false;
            for (var i=0; i<node.childsCount; i++)
                this._setCheck(node.childNodes[i],node.childNodes[i].checkstate);
        }
}
/**
*     @desc: replace checkboxes with radio buttons
*     @type: public
*     @param: mode - true/false
*     @param: itemId - node for which replacement called (optional)
*     @edition: Professional
*     @topic: 1
*/
dhtmlXTreeObject.prototype.enableSingleRadioMode=function(mode){
     this._frbtrs=convertStringToBoolean(mode);
}


/**
*     @desc: configure if parent node will be expanded immideatly after child item adding
*     @type: public
*     @param: mode - true/false
*     @edition: Professional
*     @topic: 2
*/
dhtmlXTreeObject.prototype.openOnItemAdding=function(mode){
    this._hAdI=!convertStringToBoolean(mode);
}

/**
*     @desc: enable multi line items
*     @beforeInit: 1
*     @param: width - text width, if equls zero then use single lines items;
*     @type: public
*     @edition: Professional
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableMultiLineItems=function(width) { if (width===true) this.mlitems="100%"; else this.mlitems=width; }

/**
*     @desc: enable auto tooltips (node text as tooltip)
*     @beforeInit: 1
*     @param: mode - 1 - on, 0 - off;
*     @type: public
*     @edition:Professional
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableAutoTooltips=function(mode) { this.ettip=convertStringToBoolean(mode); };


//#dhtmlxtootip:01112006{
/**
*     @desc: enable DHTMLX tootltips
*     @param: mode - true/false
*     @edition: Professional
*     @type: public
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableDHTMLXTooltips=function(mode){  this._dhxTT=convertStringToBoolean(mode); };
//#}


/**
*     @desc: unselect item in tree
*     @type: public
*     @param: itemId - used in multi selection tree
*     @edition: Professional
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.clearSelection=function(itemId){
       if (itemId)
            this._unselectItem(this._globalIdStorageFind(itemId));
            else
            this._unselectItems();
            }

/**
*     @desc: show/hide (+/-) icon (work only for individual items, not for all tree )
*     @type: public
*     @param: itemId - id of selected item
*     @param: state - show state : 0/1
*     @edition: Professional
*     @topic: 6
*/
   dhtmlXTreeObject.prototype.showItemSign=function(itemId,state){
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;

      var z=temp.span.parentNode.previousSibling.previousSibling.previousSibling;
      if (!convertStringToBoolean(state)){
         this._openItem(temp)
         temp.closeble=false;
         temp.wsign=true;
      }
      else
      {
         temp.closeble=true;
         temp.wsign=false;
      }
      this._correctPlus(temp);
   }
/**
*     @desc: show/hide checkbox for tree item (work only for individual items, not for all tree )
*     @type: public
*     @param: itemId - id of selected item, optional, set null to change states of all items
*     @param: state - checkbox show state : 0/1
*     @edition: Professional
*     @topic: 5
*/
   dhtmlXTreeObject.prototype.showItemCheckbox=function(itemId,state){
      if (!itemId)
		for (var i=0; i<this._globalIdStorageSize; i++)
			this.showItemCheckbox(this.globalNodeStorage[i],state);

      if (typeof(itemId)!="object")
	      itemId=this._globalIdStorageFind(itemId,0,1);

      if (!itemId) return 0;
   	  itemId.nocheckbox=!convertStringToBoolean(state);
      itemId.span.parentNode.previousSibling.previousSibling.childNodes[0].style.display=(!itemId.nocheckbox)?"":"none";
   }

/**
*     @desc: set list separator (comma by default)
*     @type: public
*     @param: separator - char or string using for separating items in lists
*     @edition: Professional
*     @topic: 0
*/
dhtmlXTreeObject.prototype.setListDelimeter=function(separator){
    this.dlmtr=separator;
}

//#}


/**
*     @desc: set escaping mode (used for escaping ID in server requests)
*     @param: mode - escaping mode ("utf8" for UTF escaping)
*     @type: public
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.setEscapingMode=function(mode){
        this.utfesc=mode;
        }


/**
*     @desc: enable item highlighting (item text highlited on mouseover)
*     @beforeInit: 1
*     @param: mode - 1 - on, 0 - off;
*     @type: public
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableHighlighting=function(mode) { this.ehlt=true; this.ehlta=convertStringToBoolean(mode); };

/**
*     @desc: called on mouse out
*     @type: private
*     @topic: 0
*/
   dhtmlXTreeObject.prototype._itemMouseOut=function(){
   		var that=this.childNodes[3].parentObject;
		var tree=that.treeNod;
 		if (tree._onMSO) that.treeNod._onMSO(that.id);
		if (that.id==tree._l_onMSI) tree._l_onMSI=null;
        if (!tree.ehlt) return;
 	    that.span.className=that.span.className.replace("_lor","");
   }
/**
*     @desc: called on mouse in
*     @type: private
*     @topic: 0
*/
   dhtmlXTreeObject.prototype._itemMouseIn=function(){
   		var that=this.childNodes[3].parentObject;
		var tree=that.treeNod;

		if ((tree._onMSI)&&(tree._l_onMSI!=that.id)) tree._onMSI(that.id);
		tree._l_onMSI=that.id;
        if (!tree.ehlt) return;
 	    that.span.className=that.span.className.replace("_lor","");
 	    that.span.className=that.span.className.replace(/((standart|selected)TreeRow)/,"$1_lor");
   }

/**
*     @desc: enable active images (clickable and dragable)
*     @beforeInit: 1
*     @param: mode - 1 - on, 0 - off;
*     @type: public
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableActiveImages=function(mode){this._aimgs=convertStringToBoolean(mode); };

/**
*     @desc: focus item in tree
*     @type: public
*     @param: itemId - item Id
*     @topic: 0
*/
dhtmlXTreeObject.prototype.focusItem=function(itemId){
      var sNode=this._globalIdStorageFind(itemId);
      if (!sNode) return (0);
      this._focusNode(sNode);
   };


/**
*     @desc: Returns the list of all children items from all next levels of tree, separated by commas.
*     @param: itemId - id of node
*     @type: public
*     @return: list of all children items from all next levels of tree, separated by commas
*     @topic: 6
*/
   dhtmlXTreeObject.prototype.getAllSubItems =function(itemId){
      return this._getAllSubItems(itemId);
   }

/**
*     @desc: Returns the list of all items which doesn't have child nodes.
*     @type: public
*     @return: list of all items which doesn't have child nodes.
*     @topic: 6
*/
	dhtmlXTreeObject.prototype.getAllChildless =function(){
		return this._getAllScraggyItems(this.htmlNode);
	}
	dhtmlXTreeObject.prototype.getAllLeafs=dhtmlXTreeObject.prototype.getAllChildless;


/**
*     @desc: Returns the list of all children items from all next levels of tree, separated by commas.
*     @param: itemId - id of node
*     @edition: Professional
*     @type: private
*     @topic: 6
*/
   dhtmlXTreeObject.prototype._getAllScraggyItems =function(node)
   {
      var z="";
      for (var i=0; i<node.childsCount; i++)
        {
            if ((node.childNodes[i].unParsed)||(node.childNodes[i].childsCount>0))
            {
                    if (node.childNodes[i].unParsed)
                        var zb=this._getAllScraggyItemsXML(node.childNodes[i].unParsed,1);
                    else
                       var zb=this._getAllScraggyItems(node.childNodes[i])

                 if (zb)
                        if (z) z+=this.dlmtr+zb;
                        else z=zb;
         }
            else
               if (!z) z=node.childNodes[i].id;
             else z+=this.dlmtr+node.childNodes[i].id;
         }
          return z;
   };





/**
*     @desc: Returns the list of all children items from all next levels of tree, separated by commas.
*     @param: itemId - id of node
*     @type: private
*     @edition: Professional
*     @topic: 6
*/
   dhtmlXTreeObject.prototype._getAllFatItems =function(node)
   {
      var z="";
      for (var i=0; i<node.childsCount; i++)
        {
            if ((node.childNodes[i].unParsed)||(node.childNodes[i].childsCount>0))
            {
             if (!z) z=node.childNodes[i].id;
                else z+=this.dlmtr+node.childNodes[i].id;

                    if (node.childNodes[i].unParsed)
                        var zb=this._getAllFatItemsXML(node.childNodes[i].unParsed,1);
                    else
                       var zb=this._getAllFatItems(node.childNodes[i])

                 if (zb) z+=this.dlmtr+zb;
         }
         }
          return z;
   };

/**
*     @desc: Returns the list of all items which has child nodes, separated by commas.
*     @type: public
*     @return: list of all items which has child nodes, separated by commas.
*     @topic: 6
*/
	dhtmlXTreeObject.prototype.getAllItemsWithKids =function(){
		return this._getAllFatItems(this.htmlNode);
	}
	dhtmlXTreeObject.prototype.getAllFatItems=dhtmlXTreeObject.prototype.getAllItemsWithKids;



/**
*     @desc: return list of identificators of nodes with checked checkboxes, separated by comma
*     @type: public
*     @return: list of ID of items with checked checkboxes, separated by comma
*     @topic: 5
*/
   dhtmlXTreeObject.prototype.getAllChecked=function(){
      return this._getAllChecked("","",1);
   }
/**
*     @desc: return list of identificators of nodes with unchecked checkboxes, separated by comma
*     @type: public
*     @return: list of ID of items with unchecked checkboxes, separated by comma
*     @topic: 5
*/
   dhtmlXTreeObject.prototype.getAllUnchecked=function(itemId){
        if (itemId)
            itemId=this._globalIdStorageFind(itemId);
      return this._getAllChecked(itemId,"",0);
    }


/**
*     @desc: return list of identificators of nodes with third state checkboxes, separated by comma
*     @type: public
*     @return: list of ID of items with third state checkboxes, separated by comma
*     @topic: 5
*/
   dhtmlXTreeObject.prototype.getAllPartiallyChecked=function(){
      return this._getAllChecked("","",2);
   }


/**
*     @desc: return list of identificators of nodes with checked and third state checkboxes, separated by comma
*     @type: public
*     @return: list of ID of items with checked and third state checkboxes, separated by comma
*     @topic: 5
*/
   dhtmlXTreeObject.prototype.getAllCheckedBranches=function(){
        var temp= this._getAllChecked("","",1);
        if (temp!="") temp+=this.dlmtr;
         return temp+this._getAllChecked("","",2);
   }

/**
*     @desc: return list of identificators of nodes with checked checkboxes
*     @type: private
*     @param: node - node object (optional, used by private methods)
*     @param: list - initial identificators list (optional, used by private methods)
*     @topic: 5
*/
   dhtmlXTreeObject.prototype._getAllChecked=function(htmlNode,list,mode){
      if (!htmlNode) htmlNode=this.htmlNode;

      if (htmlNode.checkstate==mode)
         if (!htmlNode.nocheckbox)  { if (list) list+=this.dlmtr+htmlNode.id; else list=htmlNode.id;  }
      var j=htmlNode.childsCount;
      for (var i=0; i<j; i++)
      {
         list=this._getAllChecked(htmlNode.childNodes[i],list,mode);
      };
//#__pro_feature:01112006{
//#smart_parsing:01112006{
        if  (htmlNode.unParsed)
            list=this._getAllCheckedXML(htmlNode.unParsed,list,mode);
//#}
//#}

      if (list) return list; else return "";
   };

/**
*     @desc: set individual item style
*     @type: public
*     @param: itemId - node id
*     @param: style_string - valid CSS string
*     @topic: 2
*/
dhtmlXTreeObject.prototype.setItemStyle=function(itemId,style_string){
      var temp=this._globalIdStorageFind(itemId);
      if (!temp) return 0;
      if (!temp.span.style.cssText)
            temp.span.setAttribute("style",temp.span.getAttribute("style")+"; "+style_string);
        else
          temp.span.style.cssText+=(";"+style_string);
}

/**
*     @desc: create enable draging of item image with item text
*     @type: public
*     @param: mode - true/false
*     @topic: 1
*/
dhtmlXTreeObject.prototype.enableImageDrag=function(mode){
    this._itim_dg=convertStringToBoolean(mode);
}

/**
*     @desc: set function called when tree item draged over another item
*     @param: func - event handling function
*     @type: public
*     @edition: Professional
*     @topic: 4
*     @event: onDragIn
*     @eventdesc: Event raised when item draged other other dropable target
*     @eventparam:  ID draged item
*     @eventparam:  ID potencial drop landing
*     @eventparam:  source object
*     @eventparam:  target object
*     @eventreturn: true - allow drop; false - deny drop;
*/
	dhtmlXTreeObject.prototype.setOnDragIn=function(func){
        if (typeof(func)=="function") this._onDrInFunc=func; else this._onDrInFunc=eval(func);
        };

/**
*     @desc: enable/disable auto scrolling while drag-and-drop
*     @type: public
*     @param: mode - enabled/disabled
*     @topic: 0
*/
   dhtmlXTreeObject.prototype.enableDragAndDropScrolling=function(mode){ this.autoScroll=convertStringToBoolean(mode); };

//#}



/***************************************************
 * library\form\Validator.js
 ***************************************************/

function validateForm(form)
{
	Element.saveTinyMceFields(form);
    ActiveForm.prototype.resetErrorMessages(form);

    var validatorData = form._validator.value;
	var validator = validatorData.evalJSON();   
    var isFormValid = true;
    var focus = true;

	$H(validator).each(function(field)
	{
		if (!form[field.key]) return;
        
		var formElement = form[field.key];
        $H(field.value).each(function(func) 
		{                
			if (window[func.key] && !window[func.key](formElement, func.value.param)) // If element is not valid
			{
                // radio button group
                if (!formElement.parentNode && formElement.length)
                {
                    formElement = formElement[formElement.length - 1];
                }
                
                ActiveForm.prototype.setErrorMessage(formElement, func.value.error, focus);
				isFormValid = false;
                focus = false;
			}
	    });
	});
    
	return isFormValid;
}

function applyFilters(form, ev)
{	
    if(!ev || !ev.target) 
    { 
        ev = window.event; 
        ev.target = ev.srcElement;
    }

	var filterData = form.elements.namedItem('_filter').value;
	var filter = filterData.evalJSON();

	element = ev.target;	
	elementFilters = filter[element.name];

	if ('undefined' == 'elementFilters')
	{
	  	return false;
	}

	for (k in elementFilters)
	{
		if(typeof elementFilters[k] == 'object')
		{
		  	eval(k + '(element, elementFilters[k]);');
		}
	}	
}

/*********************************************
	Checks (validators)
*********************************************/
function trim(strValue)
{
 	var objRegExp = /^(\s*)$/;
    //check for all spaces
    if(objRegExp.test(strValue))
    {
		strValue = strValue.replace(objRegExp, '');
       	if( strValue.length == 0)
       	{
        	return strValue;
       	}
    }
   	//check for leading & trailing spaces
   	objRegExp = /^(\s*)([\W\w]*)(\b\s*$)/;
   	if(objRegExp.test(strValue))
   	{
       //remove leading and trailing whitespace characters
       strValue = strValue.replace(objRegExp, '$2');
    }
  	return strValue;
}


function IsNotEmptyCheck(element, params)
{
	// radio buttons
    if (!element.parentNode && element.length)
	{
        for (k = 0; k < element.length; k++)
        {
            if (element[k].checked)
            {
                return true;
            }
        }   
    }
    
    else
    {
        if (element.getAttribute("type") == "checkbox") 
        {
    		return element.checked;
    	}
    	
    	return (element.value.length > 0);       
    }
}

function MinLengthCheck(element, params)
{
	return (element.value.length >= params.minLength);
}

function PasswordEqualityCheck(element, params)
{
    return (element.value == element.form.elements.namedItem(params.secondPasswordFieldname).value);
}

function MaxLengthCheck(element, params)
{
	return (element.value.length <= params.maxLength);
}

function IsValidEmailCheck(element, params)
{
	re = new RegExp(/^[a-zA-Z0-9][a-zA-Z0-9\._\-]+@[a-zA-Z0-9_\-][a-zA-Z0-9\._\-]+\.[a-zA-Z]{2,}$/);
	return (re.exec(element.value));
}

function IsValueInSetCheck(element, params)
{

}

function IsNumericCheck(element, constraint)
{
  	if (element.value == '')
  	{
  		return true;
  	}
	re = new RegExp(/(^-?\d+\.\d+$)|(^-?\d+$)|(^-?\.\d+$)/);
	return(re.exec(element.value));
}

function IsIntegerCheck(element, constraint)
{
  	if (constraint.letEmptyString && element.value == '')
  	{
  		return true;
  	}
	re = new RegExp(/^-?\d+$/);
	return(re.exec(element.value));
}

function MinValueCheck(element, constraint)
{
  	return element.value >= constraint.minValue || element.value == '';
}

function MaxValueCheck(element, constraint)
{
  	return element.value <= constraint.maxValue || element.value == '';
}

/*********************************************
	Filters
*********************************************/
function NumericFilter(elm, params)
{
    elm.focus();
    
	var value = elm.value;
	
	// Remove leading zeros
	value = value.replace(/^0+/, '');
	if(!value) value = "0";
	
	value = value.replace(',' , '.');
	
	// only keep the last comma
	parts = value.split('.');

	value = '';
	for (k = 0; k < parts.length; k++)
	{
		value += parts[k] + ((k == (parts.length - 2)) && (parts.length > 1) ? '.' : '');
	}

	// split digits and decimal part
	parts = value.split('.');
	
	// leading comma (for example: .5 converted to 0.5)
	if ('' == parts[0] && 2 == parts.length)
	{
	  	parts[0] = '0';
	}
	
	//next remove all characters save 0 though 9
	//in both elms of the array
	dollars = parts[0].replace(/^-?[^0-9]-/gi, '');

	if ('' != dollars && '-' != dollars)
	{
        dollars = parseInt(dollars);	  

        if(!dollars) dollars = 0;
	}
	
	if (2 == parts.length)
	{
		cents = parts[1].replace(/[^0-9]/gi, '');
		dollars += '.' + cents;
	}
	
	elm.value = dollars;
}

function IntegerFilter(element, params)
{
    element.focus();
    
	element.value = element.value.replace(/[^\d]/, '');
	element.value = element.value.replace(/^0/, '');
    
    if(element.value == '') 
    {
        element.value = 0;
    }
}

function RegexFilter(element, params)
{
	var regex = new RegExp(params['regex'], 'gi');
	element.value = element.value.replace(regex, '');
}


/***************************************************
 * library\form\ActiveForm.js
 ***************************************************/

/**
 * ActiveForm will most likely work in pair with ActiveList. While ActiveList handles ActiveRecords ActiveForm handles new instances, which are not yet saved in database. 
 * 
 * It's main feature is to show/hide the new form and the link to this form. It allso show/hide 
 * the progress indicator for new forms and generates valid handle from title
 * 
 * @author Sergej Andrejev <sandrejev@gmail.com>
 */
ActiveForm = Class.create();
ActiveForm.prototype = {
    /**
     * Show form and hide "Show this form" link
     * @param HTMLElement link
     * @param HTMLElement form Form should have display block set to use animation. In other case you should pass div instead of form.
     * @param boolean animate If true or not passed then try to animate this action, else just hide link and show form
     */
    showNewItemForm: function(link, form, animate) 
    {
        animate = animate !== false ? true : animate;
        
        if (link) $(link).addClassName('hidden');  
        if (animate && BrowserDetect.browser != 'Explorer')
        {             
            if (form) 
            {
                Effect.BlindDown(form, {duration: 0.3});
                Effect.Appear(form, {duration: 0.66});
                
                setTimeout(function() { 
                    form.style.display = 'block'; 
                    form.style.height = 'auto';
                }, 700);
            }
        }
        else
        {
            if (form) form.style.display = 'block'; 
        }
    },
    
    /**
     * Show "Show this form" link and hide form
     * 
     * @param HTMLElement link
     * @param HTMLElement form Form should have display block set to use animation. In other case you should pass div instead of form.
     * @param boolean animate If true or not passed then try to animate this action, else just hide link and show form
     */
    hideNewItemForm: function(link, form, animate)
    {
        animate = animate !== false ? true : animate;
        
        if (animate && BrowserDetect.browser != 'Explorer')
        {
            if (form) 
            {
                Effect.Fade(form, {duration: 0.2});
                Effect.BlindUp(form, {duration: 0.3});
                setTimeout(function() { form.style.display = 'none'; }, 300);   
            }
            
            if (link) 
            {
                setTimeout(function() { $(link).removeClassName('hidden'); }, 300);   
            }
        }
        else
        {
            if (link) $(link).removeClassName('hidden');
            if (form) form.style.display = 'none';
        }
    },
    
    /**
     * Generate valid handle from item title
     * 
     * @param string title Input title
     * @return string valid handle
     */
    generateHandle: function(title)
    {
		handle = title.toLowerCase();  
		
		handle = handle.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,""); // trim
		handle = handle.replace(/[^a-z_\d \.]/g, ""); // remove all illegal simbols
		// handle = handle.replace(/^[\d\_]+/g, "."); // replace first digits with "."
		handle = handle.replace(/ /g, "."); // replace spaces with "."

		// replace repeating dots with one
		var oldHandle = '';
		while (oldHandle != handle) 
		{
		  	oldHandle = handle;
		  	handle = handle.replace(/\.\./g, ".");
		}		 
		
		// replace leading and ending dots
		handle = handle.replace(/^\./g, "");
		handle = handle.replace(/\.$/g, "");
				       
        return handle;
    },
    
    /**
     * Show translations
     * 
     * To use this method you must have appropriate HTML structure shown bellow
     * 
     * <code>
     *   <fieldset class="dom_template specField_step_translations_language specField_step_translations_language_">
     *       <legend>
     *           <span class="expandIcon">[+]</span>
     *           <span class="specField_legend_text">Language</span>
     *       </legend>
     *       <div class="activeForm_translation_values">
     *           <p>
     *               <label>Title</label>
     *               <input type="text" name="name" />
     *           </p>
     *          
     *           ...
     *       </div>
     *   </fieldset>
     * </code>
     * 
     * @param HTMLFieldsetElement fieldst
     */
    showTranslations: function(fieldset) 
    {
        var values = document.getElementsByClassName("activeForm_translation_values", fieldset)[0];
        var legend = fieldset.getElementsByTagName('legend')[0];     
        values.style.display = 'block';
        document.getElementsByClassName("expandIcon", legend)[0].innerHTML = '[-] ';    
    },
    
    /**
     * Hide translations
     * 
     * To use this method you must have appropriate HTML structure shown bellow
     * 
     * @see ActiveForm.prototype.showTranslations
     * @param HTMLFieldsetElement form
     */
    hideTranslations: function(fieldset) 
    {
        var values = document.getElementsByClassName("activeForm_translation_values", fieldset)[0];
        var legend = fieldset.getElementsByTagName('legend')[0];     
        values.style.display = 'none';
        document.getElementsByClassName("expandIcon", legend)[0].innerHTML = '[+] ';    
    },
    
    /**
     * Toggle translations
     * 
     * To use this method you must have appropriate HTML structure shown bellow
     * 
     * @see ActiveForm.prototype.showTranslations
     * @param HTMLFieldsetElement form
     */
    toggleTranslations: function(fieldset) 
    {
        if ('block' != document.getElementsByClassName("activeForm_translation_values", fieldset)[0].style.display)
        {
            ActiveForm.prototype.showTranslations(fieldset);
        }
        else
        {
            ActiveForm.prototype.hideTranslations(fieldset);
        } 
    },
    
    resetErrorMessages: function(form)
    {
        if ('form' != form.tagName.toLowerCase()) 
        {
            form = form.down('form');
        }
      
        var messages = document.getElementsByClassName('errorText', form);
        for (k = 0; k < messages.length; k++)
        {
            messages[k].innerHTML = '';
            messages[k].style.display = 'none';
        }
	},
    
    resetErrorMessage: function(formElement) 
    {
        var errorText = formElement.up().down(".errorText");

        if (errorText)
        {
    	  	errorText.innerHTML = '';
    	  	errorText.style.display = 'none';
    	  	Element.addClassName(errorText, 'hidden');
        }
    },

    setErrorMessages: function(form, errorMessages)
    {
        if ('form' != form.tagName.toLowerCase()) form = form.down('form');
        
        try
        {
            var focus = true;
    		$H(errorMessages).each(function(error)
    		{
    			if (form.elements.namedItem(error.key))
    		  	{                
                    var formElement = form.elements.namedItem(error.key);
                    var errorMessage = error.value;
                    
                    ActiveForm.prototype.setErrorMessage(formElement, errorMessage, focus);
                    focus = false;
    			}
    		}); 	
        } catch(e) {
            console.info(e);
        }
	},
    
    setErrorMessage: function(formElement, errorMessage, focus)
    {
        try
        {
            if (focus) 
            {
                alert(errorMessage);
                Element.focus(formElement);
            }
            
            var errorContainer = formElement.up().down(".errorText");		
            if (errorContainer)	
            {
        		errorContainer.innerHTML = errorMessage;
        	  	Element.removeClassName(errorContainer, 'hidden');
        		Effect.Appear(errorContainer);
            }
            else
            {
                console.info("Please add \n...\n <div class=\"errorText hidden\"></div> \n...\n after " + formElement.name);   
            }
        } catch(e) {
            console.info(e);
        }
    },
    
    updateNewFields: function(className, ids, parent)
    {     
        ids = $H(ids);
        ids.each(function(transformation) { transformation.value = new RegExp(transformation.value);   });  
        var attributes = ['class', 'name', 'id', 'for'];  
        var attributesLength = attributes.length;
        var fields = $A(document.getElementsByClassName(className));
        
        fields.each(function(element)
        {
            for(var a = 0; a < attributesLength; a++)
            {
               var attr = attributes[a];
               ids.each(function(transformation) { 
                   if (element[attr]) element[attr] = element[attr].replace(transformation.value, transformation.key); 
               });
            };
        });
    },
    
    hideMenuItems: function(menu, except)
    {
        menu = $(menu);
        
        $A(menu.getElementsByTagName('li')).each(function(li) {
            a = $(li).down('a');
            a.hide();
            $A(except).each(function(el) { if (a == $(el)) a.style.display = 'inline';  });
        });
    },
    
    lastTinyMceId: 0,
    
    disabledTextareas: {},
    lastDisabledTextareaId: 1,
    
    initTinyMceFields: function(container) 
    {
		var textareas = container.getElementsByTagName('textarea');
		for (k = 0; k < textareas.length; k++)
		{
            if (textareas[k].readOnly)
            {
                textareas[k].style.display = 'none';
                new Insertion.After(textareas[k], '<div class="disabledTextarea" id="disabledTextareas_' + (ActiveForm.prototype.lastDisabledTextareaId++) + '">' + textareas[k].value + '</div>'); 
                var disabledTextarea = textareas[k].up().down('.disabledTextarea');
                ActiveForm.prototype.disabledTextareas[disabledTextarea.id] = disabledTextarea;
                
                var hoverFunction = function()
                {
                    try
                    {
                        $H(ActiveForm.prototype.disabledTextareas).each(function(iter)
                        {
                            iter.value.style.backgroundColor = '';
                            iter.value.style.borderStyle = '';
                            iter.value.style.borderWidth = '';
                        });
                    }
                    catch(e)
                    {
                        console.info(e)
                    }
                }
                
                Event.observe(document.body, 'mousedown', hoverFunction, true);
                Event.observe(disabledTextarea, 'click', function()
                {
                    this.style.backgroundColor = '#ffc';
                    this.style.borderStyle = 'inset';
                    this.style.borderWidth = '2px';
                }, true);
                
            }
            else
            {
                if (!textareas[k].id) 
                {    
                    textareas[k].id = 'tinyMceControll_' + (this.lastTinyMceId++);
                }
                tinyMCE.execCommand('mceAddControl', true, textareas[k].id);
            }
		}
    },
    
    destroyTinyMceFields: function(container) {
        var textareas = container.getElementsByTagName('textarea');
		for (k = 0; k < textareas.length; k++)
		{
            if (textareas[k].readOnly)
            {
                textareas[k].style.display = 'block';
                var disabledTextarea = textareas[k].up().down('.disabledTextarea');
                if (disabledTextarea)
                {
                    Element.remove(disabledTextarea);
                    delete ActiveForm.prototype.disabledTextareas[disabledTextarea.id];
                }
                
            }
            else
            {
                if (!textareas[k].id) textareas[k].id = 'tinyMceControll_' + (this.lastTinyMceId++);
    			tinyMCE.execCommand('mceRemoveControl', true, textareas[k].id);
            }
		}
    },
    
	resetTinyMceFields: function(container)
	{
		var textareas = container.getElementsByTagName('textarea');
		for(k = 0; k < textareas.length; k++)
		{
            if (textareas[k].readonly) 
            {
                continue;    
            }
			tinyMCE.execInstanceCommand(textareas[k].id, 'mceSetContent', true, '', true);
		}
	}
}

/**
 * Extend focus to use it with TinyMce fields. 
 * 
 * @example
 *     <code> Element.focus(element) </code>
 *     
 *   This won't work
 *     <code>
 *         $(element).focus();
 *         element.focus();
 *     </code>
 * 
 * @param HTMLElement element
 */
Element.focus = function(element)
{
    var styleDisplay = element.style.display;
    var styleHeight = element.style.height;
    var styleVisibility = element.style.visibility;
    var elementType = element.type;

    if ('none' == element.style.display || "hidden" == element.type)
    {
        if (Element.isTinyMce(element)) element.style.height = '80px';
        
        element.style.visibility = 'hidden';
        element.style.display = 'block';
        try { element.type = elementType; } catch(e) {}
        element.focus();
        element.style.display = styleDisplay;
        element.style.height = styleHeight;
        element.style.visibility = styleVisibility;
        try { element.type = elementType; } catch(e) {}
        
        if (Element.isTinyMce(element)) element.style.height = '1px';
    }
    else
    {
        element.focus();
    }
    
    if (Element.isTinyMce(element))
    {
        var inst = tinyMCE.getInstanceById(element.nextSibling.down(".mceEditorIframe").id);  
        tinyMCE.execCommand("mceStartTyping");
        inst.contentWindow.focus();
    }
}
   
/**
 * Check if field is tinyMce field
 * 
 * @example
 *     <code> Element.isTinyMce(element) </code>
 * 
 * @param HTMLElement element
 */
Element.isTinyMce = function(element)
{
    return element.nextSibling && element.nextSibling.nodeType != 3 && Element.hasClassName(element.nextSibling, "mceEditorContainer");
}
    
/**
 * Copies data from TinyMce to textarea. 
 * 
 * Normally it would be copied automatically on form submit, but since validator overrides
 * form.submit() we should submit all fields ourself. Note that I'm calling this funciton
 * from validation, so most of the time there is no need to worry.
 * 
 * @example
 *     <code> Element.saveTinyMceFields(element) </code>
 * 
 * @param HTMLElement element
 */
Element.saveTinyMceFields = function(element)
{
    document.getElementsByClassName("mceEditorIframe", element).each(function(mceControl)
    {
         tinyMCE.getInstanceById(mceControl.id).triggerSave();
    });
}



/***************************************************
 * library\form\State.js
 ***************************************************/

/**
 * Form.State - is used to remember last valid form state.
 *
 * @example Assume you have changed original form values and then saved it with ajax request.
 *          Now if you hit the reset button form fields will be set to original values which are
 *          out of date because you have saved form with ajax.
 *
 *          The solution is to save form (Form.backup(form);) state (all form values) when you click save and get success
 *          response. Later when you click reset button you should prevent it's default action and restore last valid
 *          form values (Form.restore(form);)
 *
 *
 * This class etends "Prototype" framework's Form class with these static methods:
 *     Form.backup(form)     - Create form's backup copy
 *     Form.restore(form)    - Restore form's backup copy
 *     Form.hasBackup(form)  - Check if form has a backup copy
 *
 * Be aware as the backup does not store all form elemens (only values), so if you dinamically removed form field
 * after backup was done there is no way to restore.
 *
 * @version 1.0
 * @author Sergej Andrejev
 *
 */
if (Form == undefined)
{
	var Form = {}
}

Form.State = {
    /**
     * Hash table of all backups. Every backed up form should store it's backup id (this is done in backup method)
     * Also fields are indexed by field name and not the id, s therefore there is no need to add id to every field
     *
     * @var array
     *
     * @access private
     * @static
     */
    backups: [],

    /**
     * Backup id autoincrementing value
     *
     * @var int
     *
     * @access private
     * @static
     */
    counter: 1,

    /**
     * Get new ID for the form
     */
    getNewId: function()
    {
        return this.counter++;
    },


    /**
     * Backup form values
     *
     * @param HtmlFormElement form Form node
     *
     * @access public
     * @static
     */
    backup: function(form)
    {
        if(!this.hasBackup(form))
        {
            form.backupId = this.getNewId();
        }

        this.backups[form.backupId] = {};

        var elements = Form.getElements(form);
        for(var i = 0; i < elements.length; i++)
        {
            if(elements[i].name == '') continue;

            var name = elements[i].name;

            var value = {}
            value.value = elements[i].value;
            value.selectedIndex = elements[i].selectedIndex;
            value.checked = elements[i].checked;

            if(elements[i].options)
            {
                value.options = {};
                for(var j = 0; j < elements[i].options.length; j++)
                {
                    var oval = elements[i].options[j].firstChild ? elements[i].options[j].firstChild.nodeValue : elements[i].options[j].value;
                    value.options[elements[i].options[j].value] = oval;
                }
            }

            if(!this.backups[form.backupId][elements[i].name])
            {
                this.backups[form.backupId][elements[i].name] = [];
                this.backups[form.backupId][elements[i].name][0] = value;
            }
            else
            {
                this.backups[form.backupId][elements[i].name][this.backups[form.backupId][elements[i].name].length] = value;
            }
        }
    },


    /**
     * Create form backup from json object.
     *
     * @param HTMLElementForm Form node
     * @param Object Backup data. This object should be organized so that keys would be form fields names (not ids)
     *        and values vould be arrays of field values
     *
     * @example
     *        json = {
     *              id: [{ value: 15}],
     *            name: [{ value: test}],
     *           radio: [
     *                      {value: 1, checked: false },
     *                      {value: 2, checked: true },
     *                      {value: 3, checked: false },
     *                  ],
     *          select: [
     *                      {
     *                                value: 5,
     *                        selectedIndex: 2, // you should precalculate it yourself
     *                              options: { // keys here are values and values are the text which appears in dropdown box
     *                                 3: "text",
     *                                 4: "processor",
     *                                 5: "selector",
     *                                 6: "date"
     *                               }
     *                      }
     *                  ]
     *             }
     *
     */
    backupFromJson: function(form, json)
    {
        if(!this.hasBackup(form))
        {
            form.backupId = this.getNewId();
        }

        this.backups[form.backupId] = {};
        this.backups[form.backupId] = json;
    },


    /**
     * Check if form has a backup
     *
     * @param HtmlFormElement form Form node
     * @return bool
     *
     * @access public
     * @static
     */
    hasBackup: function(form)
    {
        return form.backupId && this.backups[form.backupId];
    },


    /**
     * Restore form values
     *
     * @param HtmlFormElement form Form node
     *
     * @access public
     * @static
     */
    restore: function(form, ignoreFields)
    {
        if(!ignoreFields) ignoreFields = [];
        ignoreFields = $A(ignoreFields);
        if(!this.hasBackup(form)) return;
        self = this;

        var occurencies = {};
        var elements = $A(Form.getElements(form));
        try
        {
            $A(Form.getElements(form)).each(function(element)
            {
                if(ignoreFields.member(element.name)) return;
                if(element.name == '' || !self.backups[form.backupId][element.name]) return;

                occurencies[element.name] = (occurencies[element.name] == undefined) ? 0 : occurencies[element.name] + 1;

                var value = self.backups[form.backupId][element.name][occurencies[element.name]];

                if(value)
                {
                    element.value = value.value;
                    element.checked = value.checked;

                    if(element.options && value.options)
                    {
                        element.options.length = 0;
                        $H(value.options).each(function(option) {
                            element.options[element.options.length] = new Option(option.value, option.key);
                        });
                    }

                    element.selectedIndex = value.selectedIndex;
                }
            });
        }
        catch(e)
        {
            console.info(e);
        }
    }
}

Object.extend(Form, Form.State);


/***************************************************
 * library\TabControl.js
 ***************************************************/

Event.fire = function(element, event) 
{
   Event.observers.each(function(observer) 
   {
        if(observer[1] == event && observer[0] == element)
        {
            var func = observer[2];
            func();
        }
   });
};


var TabControl = Class.create();
TabControl.prototype = {
    __instances__: {},
    
	activeTab: null,
	indicatorImageName: "image/indicator.gif",

	initialize: function(tabContainerName, urlParserCallback, idParserCallback, callbacks)
	{
        try
        {  
            this.tabContainerName = tabContainerName;
            this.urlParserCallback = urlParserCallback;
            this.idParserCallback = idParserCallback;
            this.callbacks = callbacks ? callbacks : {};
            this.loadedContents = {};
            
            this.__nodes__();
            this.__bind__();

            this.decorateTabs();
            this.countersCache = {};
        }
        catch(e)
        {
            console.info(e)
        }
	},
    
    __nodes__: function()
    {
        this.nodes = {};
        this.nodes.tabContainer = $(this.tabContainerName);
		this.nodes.tabList = this.nodes.tabContainer.down(".tabList");
		this.nodes.tabListElements = document.getElementsByClassName("tab", this.nodes.tabList);
		this.nodes.sectionContainer = this.nodes.tabContainer.down(".sectionContainer");
    },
    
    __bind__: function()
    {
        var self = this;   
        this.nodes.tabListElements.each(function(li) 
        {
			var link = li.down('a');
            var indicator = '<img src="' + self.indicatorImageName + '" class="tabIndicator" alt="Tab indicator" style="display: none" /> ';
            
            Event.observe(link, 'click', function(e) { if(e) Event.stop(e); });
            
            li.onclick = function(e) { 
                if(!e) e = window.event;
                if(e) Event.stop(e);
                
                if (e)
                {
                    var keyboard = new KeyboardEvent(e);
                    if (keyboard.isShift())
                    {                        
                        self.resetContent(Event.element(e).up('li'));
                    }
                }

                self.handleTabClick({'target': li});                 
            }
   
			Event.observe(li, 'mouseover', function(e) { 
                if(e) Event.stop(e); 
                self.handleTabMouseOver({'target': li}) 
            });
			Event.observe(li, 'mouseout', function(e) { 
                if(e) Event.stop(e); 
                self.handleTabMouseOut({'target': li}) 
            });
            
            li.update(indicator + li.innerHTML);
		});
    },

    decorateTabs: function()
    {
        this.nodes.tabListElements.each(function(tab)
        {
            var firstLink = tab.down('a');
            new Insertion.After(firstLink, '<span class="tabCounter"> </span>');
        });  
    },

    getInstance: function(tabContainerName, urlParserCallback, idParserCallback, callbacks)
    {
        if(!TabControl.prototype.__instances__[tabContainerName])
        {
            TabControl.prototype.__instances__[tabContainerName] = new TabControl(tabContainerName, urlParserCallback, idParserCallback, callbacks);
        }
                
        return TabControl.prototype.__instances__[tabContainerName];
    },

	handleTabMouseOver: function(args)
	{
		if (this.activeTab != args.target)
		{
			Element.removeClassName(args.target, 'inactive');
			Element.addClassName(args.target, 'hover');
		}
	},

	handleTabMouseOut: function(args)
	{
		if (this.activeTab != args.target)
		{
			Element.removeClassName(args.target, 'hover');
			Element.addClassName(args.target, 'inactive');
		}
	},

	handleTabClick: function(args)
	{
        // hide all error messages within a background process (to avoid potential tab switching delays)
        window.setTimeout(function()
            {
                document.getElementsByClassName('redMessage').each(function(message){ message.hide(); });
                document.getElementsByClassName('bugMessage').each(function(message){ message.hide(); });   
            }, 10);

        if(this.callbacks.beforeClick) this.callbacks.beforeClick.call(this);
        this.activateTab(args.target);
        if(this.callbacks.afterClick) this.callbacks.afterClick.call(this);
	},
    
    addHistory: function()
    {
        var self = this;
        setTimeout(function()
        {
            var locationHash = "#" + Backend.ajaxNav.getHash();
            try
            {
                self.nodes.tabListElements.each(function(tab)
                {
                    if(locationHash.indexOf("#" + tab.id) !== -1)
                    {
                        locationHash = locationHash.substring(0, locationHash.indexOf(tab.id) - 1);
                        throw new Error('stop');
                    }
                });
            }
            catch(e) { }
            
            Backend.ajaxNav.add(locationHash.substring(1) + "#" + self.activeTab.id);
        }, dhtmlHistory.currentWaitTime);
    },

	activateTab: function(targetTab, onComplete)
	{
        targetTab = $(targetTab);
        
		if(!targetTab) 
		{
			targetTab = this.nodes.tabListElements[0];	
		}
                
		// get help context
		var helpContext = document.getElementsByClassName('tabHelp', targetTab);
		if (helpContext.length > 0)
		{
			Backend.setHelpContext(helpContext[0].firstChild.nodeValue);
		}
		
		var contentId = this.idParserCallback(targetTab.id);
        
        // Cancel loading tab if false was returned
        if(!contentId)
        {
            return;
        }
        
        if(!$(contentId)) new Insertion.Top(this.nodes.sectionContainer, '<div id="' + contentId + '" class="tabPageContainer"></div>');		

        var self = this;
        $A(this.nodes.tabListElements).each(function(tab) {
			Element.removeClassName(tab, 'active');
			Element.addClassName(tab, 'inactive');
        });
        
        document.getElementsByClassName("tabPageContainer", this.nodes.sectionContainer).each(function(container) {
            Element.hide(container);
        })
        
		this.activeTab = targetTab;
        this.activeContent = $(contentId);
                
        Element.removeClassName(this.activeTab, 'hover');
		Element.addClassName(this.activeTab, 'active');
		Element.show(contentId);
        
        if(!onComplete && this.callbacks.onComplete)
        {
            onComplete = this.callbacks.onComplete;
        }
        
		if (!this.loadedContents[this.urlParserCallback(targetTab.down('a').href) + contentId] && Element.empty($(contentId)))
		{
            this.loadedContents[this.urlParserCallback(targetTab.down('a').href) + contentId] = true;
            new LiveCart.AjaxUpdater(this.urlParserCallback(targetTab.down('a').href), contentId, targetTab.down('.tabIndicator'), 'bottom',  onComplete);
		}
        else if(onComplete)
        {
            onComplete();
        }
       
        this.addHistory();
	},

	/**
	 * Reset content related to a given tab. When tab will be activated content must
	 * be resent
	 */
	resetContent: function(tabObj)
	{
        if (!tabObj)
        {
            return false;
        }
        
        var id = this.idParserCallback(tabObj.id);
        this.loadedContents[this.urlParserCallback(tabObj.down('a').href) + id] = false;
		if ($(id))
		{
            $(id).update();
        }
	},

	reloadActiveTab: function()
	{
		this.resetContent(this.activeTab);
		this.activateTab(this.activeTab);
	},

	getActiveTab: function()
	{
		return this.activeTab;
	},

	setTabUrl: function(tabId, url)
	{
		$(tabId).url = url;
	},
    
    setCounter: function(tab, value, hashId)
    {
        if(!this.countersCache[hashId]) this.countersCache[hashId] = {};
        
        tab = $(tab);
        
        if(!tab) throw new Error('Could not find tab!');
        
        var counter = tab.down('.tabCounter');
        if(false === value)
        {
            counter.update('');
            delete this.countersCache[hashId][tab.id];
        }
        else
        {
            counter.update("(" + value + ")");
            this.countersCache[hashId][tab.id] = value;
        }
    },
        
    setAllCounters: function(counters, hashId)
    {     
        var self = this;
        $H(counters).each(function(tab) {
            self.setCounter(tab[0], tab[1], hashId);
        });
    },
    
    restoreCounter: function(tab, hashId)
    {
        tab = $(tab);

        if(tab && this.countersCache[hashId][tab.id])
        {
            this.setCounter(tab.id, this.countersCache[hashId][tab.id]);
            return true;
        }
        
        return false;
    },
    
    restoreAllCounters: function(hashId)
    {
        var self = this;
        var restored = false;
        if(this.countersCache[hashId])
        {
            $A(this.nodes.tabListElements).each(function(tab) {
                restored = self.restoreCounter(tab, hashId) ? true : restored;    
            });
        }
        
        return restored;  
    },
    
    getCounter: function(tab)
    {
        tab = $(tab);
        
        if(!tab) throw new Error('Could not find tab!');
        
        var counter = tab.down('.tabCounter');      
        var match = counter.innerHTML.match(/\((\d+)\)/);
        return match ? parseInt(match[1]) : 0;
    }
}


/***************************************************
 * library\ActiveList.js
 ***************************************************/

/**
 * ActiveList
 *
 * Sortable list
 *
 * @example
 * <code>
 * <ul id="specField_items_list" class="activeList_add_sort activeList_add_edit activeList_add_delete">
 *    <li id="specField_items_list_96" class="">Item 1</li>
 *    <li id="specField_items_list_95"  class="">Item 2</li>
 *    <li id="specField_items_list_100" class="activeList_remove_sort">Item 3</li>
 *    <li id="specField_items_list_101" class="">Item 4</li>
 *    <li id="specField_items_list_102" class="">Item 5</li>
 * </ul>
 *
 * <script type="text/javascript">
 *     new ActiveList('specField_items_list', {
 *         beforeEdit:     function(li)
 *         {
 *             if(this.isContainerEmpty()) return 'edit.php?id='+this.getRecordId(li)
 *             else his.toggleContainer()
 *         },
 *         beforeSort:     function(li, order) { return 'sort.php?' + order },
 *         beforeDelete:   function(li)
 *         {
 *             if(confirm('Are you sure you wish to remove record #' + this.getRecordId(li) + '?')) return 'delete.php?id='+this.getRecordId(li)
 *         },
 *         afterEdit:      function(li, response) { this.getContainer(li, 'edit').innerHTML = response; this.toggleContainer();  },
 *         afterSort:      function(li, response) { alert( 'Record #' + this.getRecordId(li) + ' changed position'); },
 *         afterDelete:    function(li, response)  { this.remove(li); }
 *     });
 * </script>
 * </code>
 *
 * First argument passed to active list constructor is list id, and the second is hash object of callbacks
 * Events in active list will automatically call two actions one before ajax request to server and one after.
 * Those callbacks which are called before the request hase "before" prefix. Those which will be called after - "after".
 *
 * Functions which are called before request must return a link or a false value. If a link returned then
 * request to that link is made. On the other hand if false is returned then no request is send and "after" function
 * is not called. This is useful for caching.
 *
 * Note that there are some usefful function you can use inside your callbacks
 * this.isContainerEmpty() - Returns if container is empty
 * this.getRecordId(li) - Get real item's id (used to identify that item in database)
 * this.getContainer() - Get items container. Also every action has it's own container
 *
 * There are also some usefull variables available to you in callback
 * this - A reference to ActiveList object.
 * li - Current item
 * order - Serialized order
 * response - Ajax response text
 *
 * @version 1.2
 * @author Sergej Andrejev, Rinalds Uzkalns
 *
 */
if (LiveCart == undefined)
{
    var LiveCart = {}
}

ActiveList = Class.create();
ActiveList.prototype = {
    /**
     * Item icons which will apear in top left corner on each item of the list
     *
     * @var Hash
     */
    icons: {
        'sort':     "image/silk/arrow_switch.png",
        'edit':     "image/silk/pencil.png",
        'delete':   "image/silk/cancel.png",
        'view':     "image/silk/zoom.png",
        'progress': "image/indicator.gif"
    },

    /**
     * User obligated to pass this callbacks to constructor when he creates
     * new active list.
     *
     * @var array
     */
    requiredCallbacks: [],

    /**
     * When active list is created it depends on automatically generated html
     * content.That means that active list uses class names to find icons and
     * containers in list. Be sure you are using unique prefix
     *
     * @var string
     */
    cssPrefix: 'activeList_',

    /**
     * List order is send back only if last sort accured more then M milliseconds ago.
     * M is that value
     *
     * @var int
     */
    keyboardSortTimeout: 1000,

    /**
     * Tab index of every active list element. Most of the time this value is not important
     * so any would work fine
     *
     * @var int
     */
    tabIndex: 666,
    
    /**
     * The alpha level of menu when it is hidden
     * 
     * @var double [0,1]
     */
    visibleMenuOpacity: 1, 
    
    /**
     * The alpha level of menu when it is visible
     * 
     * @var double [0,1]
     */
    hiddenMenuOpacity: 0.15, 

    activeListsUsers: {},
    
    messages: {},

    /**
     * Constructor
     *
     * @param string|ElementUl ul List id field or an actual reference to list
     * @param Hash callbacks Function which will be executed on various events (like sorting, deleting editing)
     *
     * @access public
     */
    initialize: function(ul, callbacks, messages)
    {
        try
        {
            this.ul = $(ul);
    
            if(!this.ul)
            {
                throw Error('No list found');
                return false;
            }
    
            this.messages = messages;
                        
            Element.addClassName(this.ul, this.ul.id);
    
            // Check if ul has an id
            if(!this.ul.id)
            {
                throw Error('Active record main UL element is required to have an id. Also all list items should take that id plus "_"  as a prefix');
                return false;
            }
    
            // Check if all required callbacks are passed
            var missedCallbacks = [];
            for(var i = 0; i < this.requiredCallbacks.length; i++)
            {
                var before = ('before-' + this.requiredCallbacks[i]).camelize();
                var after = ('after-' + this.requiredCallbacks[i]).camelize();
    
                if(!callbacks[before]) missedCallbacks[missedCallbacks.length] = before;
                if(!callbacks[after]) missedCallbacks[missedCallbacks.length] = after;
            }
            if(missedCallbacks.length > 0)
            {
                    throw Error('Callback' + (missedCallbacks.length > 1 ? 's' : '') + ' are missing (' + missedCallbacks.join(', ') +')' );
                    return false;
            }
    
            this.callbacks = callbacks;
            this.dragged = false;
    
            this.generateAcceptFromArray();
            this.createSortable();
            this.decorateItems();
        } 
        catch(e) 
        {
            console.info(e);
        }
    },
    
    /**
     * Get active list singleton. If ul list is allready an ActiveList then use it's instance. In other case create new instance
     * 
     * @param HTMLUlElement ul
     * @param object callbacks
     * @param object messages
     */
    getInstance: function(ul, callbacks, messages)
    {  
        var ulElement = $(ul);       
        if(!ulElement.id)
        {
            throw Error('Active record main UL element is required to have an id. Also all list items should take that id plus "_"  as a prefix');
            return false;
        }
       
        if(!ActiveList.prototype.activeListsUsers[ulElement.id]) 
        {
            ActiveList.prototype.activeListsUsers[ulElement.id] = new ActiveList(ulElement.id, callbacks, messages);
        }
        
        return ActiveList.prototype.activeListsUsers[ulElement.id];

    },

    /**
     * Destroy active list object associated with given list
     * 
     * @param HTMLUlElement ul    destroy: function(ul)
     */    
    destroy: function(ul)
    {  
       var id = ul.id ? ul.id : ul;
       
       if(ActiveList.prototype.activeListsUsers[id]) 
       {
           delete this.activeListsUsers[id];
       }
    },

    destroySortable: function()
    {
       Sortable.destroy(this.ul);
       console.info('destroy sortable')
    },

    makeStatic: function()
    {
       Sortable.destroy(this.ul);
       Element.removeClassName(this.ul, 'activeList_add_sort')
       document.getElementsByClassName('activeList_icons', this.ul).each(function(iconContainer)
       {
           iconContainer.hide();
           iconContainer.style.visibility = 'hidden';
       });
    },

    /**
     * Split list by odd and even active records by adding ActiveList_odd or ActiveList_even to each element
     */
    colorizeItems: function()
    {
        var liArray = this.ul.getElementsByTagName("li");

        var k = 0;
        for(var i = 0; i < liArray.length; i++)
        {
            if(this.ul == liArray[i].parentNode && !Element.hasClassName(liArray[i], 'ignore') && !Element.hasClassName(liArray[i], 'dom_template'))
            {
                this.colorizeItem(liArray[i], k);
                k++;
            }
        }
    },

    /**
     * Adds classes ActiveList_odd and ActiveList_even to separate odd elements from even
     * 
     * @param HtmlElementLi A reference to item element. Default is current item
     * @param {Object} position Element position in ActiveList
     */
    colorizeItem: function(li, position)
    {
        if(position % 2 == 0)
        {
            Element.removeClassName(li, this.cssPrefix + "odd");
            Element.addClassName(li, this.cssPrefix + "even");
        }
        else
        {
            Element.removeClassName(li, this.cssPrefix + "even");
            Element.addClassName(li, this.cssPrefix + "odd");
        }
    },

    /**
     * Toggle item container On/Off
     *
     * @param HtmlElementLi A reference to item element. Default is current item
     * @param string action Every action has its own container. You could toggle another action container, but default is to toggle current action's container
     *
     * @access public
     */
    toggleContainer: function(li, action)
    {
        var container = this.getContainer(li, action);
        
        if(container.style.display == 'none') this.toggleContainerOn(container);
        else this.toggleContainerOff(container);
    },
    
    /**
     * Expand data container 
     * 
     * @param HTMLElementDiv container Reference to the container
     */
    toggleContainerOn: function(container)
    {       
        container = $(container);
        ActiveList.prototype.collapseAll();
        
        Sortable.destroy(this.ul);
        if(BrowserDetect.browser != 'Explorer')
        {
            Effect.BlindDown(container, { duration: 0.5 });
            Effect.Appear(container, { duration: 1.0 });
            setTimeout(function() { container.style.height = 'auto'; container.style.display = 'block'}, 300);
        } 
        else
        {
            container.style.display = 'block';
        }
    },

    /**
     * Collapse data container 
     * 
     * @param HTMLElementDiv container Reference to the container
     */
    toggleContainerOff: function(container)
    {
        var container = $(container);
        this.createSortable();
        if(BrowserDetect.browser != 'Explorer')
        {
            Effect.BlindUp(container, {duration: 0.2});
            setTimeout(function() { container.style.display = 'none'}, 40);
        } 
        else
        {
            container.style.display = 'none';
        }
    },
    
    /**
     * Check if item container is empty
     *
     * @param HtmlElementLi A reference to item element. Default is current item
     * @param string action Every action has its own container. You could toggle another action container, but default is to toggle current action's container
     *
     * @access public
     *
     * @return bool
     */
    isContainerEmpty: function(li, action)
    {
        return this.getContainer(li, action).firstChild ? false : true;
    },

    /**
     * Get item container
     *
     * @param HtmlElementLi A reference to item element. Default is current item
     * @param string action Every action has its own container. You could toggle another action container, but default is to toggle current action's container
     *
     * @access private
     *
     * @return ElementDiv A refference to container node
     */
    getContainer: function(li, action)
    {
        if(!li) li = this._currentLi;

        return document.getElementsByClassName(this.cssPrefix + action + 'Container' , li)[0];
    },

    /**
     * Get item's id. Not as a dom element but real id, which is used id database
     *
     * @param HtmlElementLi li A reference to item element
     *
     * @access public
     *
     * @return string element id
     */
    getRecordId: function(li, level)
    {
        if(!level) level = 1;
        var matches = li.id.match(/_([a-zA-Z0-9]*)(?=(?:_|\b))/g);
        
        var id = matches[matches.length-level];
        return id ? id.substr(1) : false;
    },

    /**
     * Rebind all icons in item
     *
     * @param HtmlElementLi li A reference to item element
     *
     * @access public
     */
    rebindIcons: function(li)
    {
        var self = this;
        $A(this.ul.className.split(' ')).each(function(className)
        {
            var container = document.getElementsByClassName(self.cssPrefix + 'icons', li)[0];

            var regex = new RegExp('^' + self.cssPrefix + '(add|remove)_(\\w+)(_(before|after)_(\\w+))*');
            var tmp = regex.exec(className);

            if(!tmp) return;

            var icon = {};
            icon.type = tmp[1];
            icon.action = tmp[2];
            icon.image = self.icons[icon.action];
            icon.position = tmp[4];
            icon.sibling = tmp[5];

            if(icon.action != 'sort') 
            {
                li[icon.action + 'Container'] = document.getElementsByClassName(self.cssPrefix + icon.action + 'Container', li)[0];
            }
        });
        
        li.prevParentId = this.ul.id;
    },

    /**
     * Add new item to Active Record. You have 3 choices. Either to add whole element, add array of elements or add all elements
     * inside given dom element
     *
     * @param int id Id of new element (Same ID which is stored in database)
     * @param HTMLElement|array dom Any HTML Dom element or array array of Dom elements
     * @param bool insights Use elements inside of given node
     *
     * @access public
     *
     * @return HTMLElementLi Reference to new active list record
     */
    addRecord: function(id, dom, touch)
    {
        var li = document.createElement('li');
        li.id = this.ul.id + "_" + id;
        this.ul.appendChild(li);

        if(typeof dom == 'string')
        {
            li.innerHTML = dom; 
        }
        else if (dom[0])
        {
            for(var i = 0; i < dom.length; i++)
            {
                // Sory for cloning, but JS just sucks hard at dom :''(
                // I just hope that every boy will use it in such situations where cloning is OK
                // Please forgive me if you will create links to elements you want to add and they will just not work
                // My suggestion is to create those links after you have added new record to list
                var cloned_dom = dom[i].cloneNode(true);
                while(cloned_dom.childNodes.length > 0) li.appendChild(cloned_dom.childNodes[0]);    
            }
        }
        else
        {
            var cloned_dom = dom.cloneNode(true);
            while(cloned_dom.childNodes.length > 0) li.appendChild(cloned_dom.childNodes[0]);
            li.className = dom.className;
        }
        if(touch)
        {
            this.createSortable();
	  	}
        
        this.decorateLi(li);
        this.colorizeItem(li, this.ul.childNodes.length);		    

        return li;
    },
    
    highlight: function(li, color)
    {
        if(!li) li = this._currentLi;
        li = $(li);
        
        switch(color)
        {
            case 'red':
                new Effect.Highlight(li, {startcolor:'#FFF1F1', endcolor:'#F5F5F5'});
                break;
            case 'yellow':
            default:
                new Effect.Highlight(li, {startcolor:'#FBFF85', endcolor:'#F5F5F5'});
                break;
        }
    },


    /***************************************************************************
    /*           Private methods                                               *
    /***************************************************************************

    /**
     * Go throug all list elements and decorate them with icons, containers, etc
     *
     * @access private
     */
    decorateItems: function()
    {

        // This fixes some strange explorer bug/"my stypidity"
        // Basically, what is happening is thet when I push edit button (pencil)
        // on first element, everything just dissapears. All other elements
        // are fine though. To fix this I am adding an hidden first element
        var liArray = this.getChildList();
        for(var i = 0; i < liArray.length; i++)
        {
            this.decorateLi(liArray[i]);
            this.colorizeItem(liArray[i], i);
        }
    },

    /**
     * Decorate list element with icons, progress bar, container, tabIndex, etc
     *
     * @param HtmlElementLi Element to decorate
     *
     * @access private
     */
    decorateLi: function(li)
    {
        var self = this;
        
        // Bind events
        Event.observe(li, "mouseover", function(e) { self.showMenu(this) });
        Event.observe(li, "mouseout",  function(e) { self.hideMenu(this) });

        // Create icons container. All icons will be placed incide it
        if(!li.down('.' + self.cssPrefix + 'icons'))
        {
            var iconsDiv = document.createElement('span');
            Element.addClassName(iconsDiv, self.cssPrefix + 'icons');
            li.insertBefore(iconsDiv, li.firstChild);
    
            // add all icons
            $A(this.ul.className.split(' ')).each(function(className)
            {
                // If icon is not progress and it was added to a whole list or only this item then put that icon into container
                self.addIconToContainer(li, className);
            });
    
            // progress is not a div like all other icons. It has no fixed size and is not clickable.
            // This is done to properly handle animated images because i am not sure if all browsers will
            // handle animated backgrounds in the same way. Also differently from icons progress icon
            // can vary in size while all other icons are always the same size
            iconProgress = document.createElement('img');
            iconProgress.src = this.icons.progress;
            
            if (this.messages && this.messages._activeList_progress)
            {
                iconImage.alt = this.messages._activeList_progress;                
            }
            
            if (this.messages && this.messages._activeList_progress)
            {
                iconImage.title = iconImage.alt = this.messages._activeList_progress;            
            }
            
            iconProgress.style.visibility = 'hidden';
            
            Element.addClassName(iconProgress, self.cssPrefix + 'progress');
            iconsDiv.appendChild(iconProgress);
    
    
            li.progress = iconProgress;
            li.prevParentId = this.ul.id;
        }
    },

    /**
     * Add icon to container according to active list classes current record classes
     * 
     * @param HtmlElementLi Element 
     * @param string className ActiveList(ul) classes separated by space
     */
    addIconToContainer: function(li, className)
    {
        var container = li.down("span." + this.cssPrefix + 'icons');
        
        var regex = new RegExp('^' + this.cssPrefix + '(add|remove)_(\\w+)(_(before|after)_(\\w+))*');
        var tmp = regex.exec(className);

        if(!tmp) return;

        var icon = {};   

        icon.type = tmp[1];
        icon.action = tmp[2];
        icon.image = this.icons[icon.action];
        icon.position = tmp[4];
        icon.sibling = tmp[5];

        if(icon.action == 'accept') return true;
        
        if(icon.action != 'sort')
        {
            var iconImage = document.createElement('img');
            
            iconImage.src = icon.image;
            if(this.messages && this.messages['_activeList_' + icon.action])
            {
                iconImage.title = iconImage.alt = this.messages['_activeList_' + icon.action];             
            }
            
            Element.addClassName(iconImage, this.cssPrefix + icon.action);
            Element.addClassName(iconImage, this.cssPrefix + 'icons_container');     
            
            // If icon is removed from this item than do not display the icon
            if((Element.hasClassName(li, this.cssPrefix + 'remove_' + icon.action) || !Element.hasClassName(this.ul, this.cssPrefix + 'add_' + icon.action)) && !Element.hasClassName(li, this.cssPrefix + 'add_' + icon.action))
            {
                iconImage.style.display = 'none';
            }

            // Show icon
            container.appendChild(iconImage);
            iconImage.setOpacity(this.hiddenMenuOpacity);
            li[icon.action] = iconImage;

            var self = this;
            Event.observe(iconImage, "click", function() { self.bindAction(li, icon.action) });

            // Append content container
            if('delete' != icon.action && !this.getContainer(li, icon.action))
            {
                var contentContainer = document.createElement('div');
                contentContainer.style.display = 'none';
                Element.addClassName(contentContainer, self.cssPrefix + icon.action + 'Container');
                Element.addClassName(contentContainer, self.cssPrefix + 'container');
                contentContainer.id = self.cssPrefix + icon.action + 'Container_' + li.id;
                li.appendChild(contentContainer);
                li[icon.action + 'Container'] = contentContainer;
            }
        } 
    },

    /**
     * This function executes user specified callback. For example if action was
     * 'delete' then the beforeDelete function will be called
     * which should return a valud url adress. After that when AJAX response has
     * arrived the afterDelete function will be called
     *
     * @param HtmlElementLi A reference to item element
     * @param string action Action
     *
     * @access private
     */
    bindAction: function(li, action)
    {
        this.rebindIcons(li);

        if(action != 'sort')
        {
            this._currentLi = li;
            
            var url = this.callbacks[('before-'+action).camelize()].call(this, li);

            if(!url) return false;

            var self = this;
            // display feedback
            this.onProgress(li);

            // execute the action
            new LiveCart.AjaxRequest(
                url,
                false,
                // the object context mystically dissapears when onComplete function is called,
                // so the only way I could make it work is this
                function(param)
                {
                    self.callUserCallback(action, param, li);
                }
            );
        }
    },

    /**
     * Toggle progress bar on list element
     *
     * @param HtmlElementLi A reference to item element
     *
     * @access private
     */
    toggleProgress: function(li)
    {
        if(li.progress && li.progress.style.visibility == 'hidden')
        {
            this.onProgress(li);
        }
        else
        {
            this.offProgress(li);
        }
    },

    /**
     * Toggle progress indicator off
     * 
     * @param HtmlElementLi li A reference to item element
     */
    offProgress: function(li)
    {
        if(li.progress) li.progress.style.visibility = 'hidden';
    },

    /**
     * Toggle progress indicator on
     * 
     * @param HtmlElementLi li A reference to item element
     */
    onProgress: function(li)
    {
        if(li.progress) li.progress.style.visibility = 'visible';
    },

    /**
     * Call a user defined callback function
     *
     * @param string action Action
     * @param XMLHttpRequest response An AJAX response object
     * @param HtmlElementLi A reference to item element. Default is current item
     *
     * @access private
     */
    callUserCallback: function(action, response, li)
    {
        this._currentLi = li;
        this.callbacks[('after-'+action).camelize()].call(this, li, response.responseText);
        this.offProgress(li);
    },

    /**
     * Generate array of elements from wich this active list can accept elements.
     * This array is generated from class name. Example: If this ul had "aciveList_accept_otherALClass"
     * then the list would accept elements from all active lists with class otherALClass
     * 
     */
    generateAcceptFromArray: function()
    {
        var self = this;
        var regex = new RegExp('^' + self.cssPrefix + 'accept_(\\w+)');
        
        this.acceptFromLists = [this.ul];
        $A(this.ul.className.split(' ')).each(function(className)
        {
            var tmp = regex.exec(className);
            if(!tmp) return;
            var allowedClassName = tmp[1];
            
            self.acceptFromLists = $$('ul.' + allowedClassName);
        });
    },

    /**
     * Initialize Scriptaculous Sortable on the list
     *
     * @access private
     */
    createSortable: function ()
    {
        var self = this;

        Element.addClassName(this.ul, this.cssPrefix.substr(0, this.cssPrefix.length-1));
        
        if(Element.hasClassName(this.ul, this.cssPrefix + 'add_sort'))
        {
            Sortable.create(this.ul.id,
            {
                dropOnEmpty:   true,
                containment:   this.acceptFromLists,
                onChange:      function(elementObj) 
                { 
                    self.dragged = elementObj;
                },
                onUpdate:      function() { 
                    self.saveSortOrder(); 
                },
                
                starteffect: function(){ self.scrollStart() },
                endeffect: function(){ self.scrollEnd() }
            });
        }        
    },

    getWindowScroll: function() {
      var T, L, W, H;
      var w = window;
      with (w.document) {
        if (w.document.documentElement && documentElement.scrollTop) {
          T = documentElement.scrollTop;
          L = documentElement.scrollLeft;
        } else if (w.document.body) {
          T = body.scrollTop;
          L = body.scrollLeft;
        }
        if (w.innerWidth) {
          W = w.innerWidth;
          H = w.innerHeight;
        } else if (w.document.documentElement && documentElement.clientWidth) {
          W = documentElement.clientWidth;
          H = documentElement.clientHeight;
        } else {
          W = body.offsetWidth;
          H = body.offsetHeight
        }
      }
      return { top: T, left: L, width: W, height: H };
    },

    findTopY: function(obj) {
      var curtop = 0;
      if (obj.offsetParent) {
        while (obj.offsetParent) {
          curtop += obj.offsetTop;
          obj = obj.offsetParent;
        }
      }
      else if (obj.y)
        curtop += obj.y;
      return curtop;
    },
    
    findBottomY: function(obj) {
      return this.findTopY(obj) + obj.offsetHeight;
    },
    
    scrollSome: function() {
      var scroller = this.getWindowScroll();
      var yTop = this.findTopY(this.dragged);
      var yBottom = this.findBottomY(this.dragged);

      if (yBottom > scroller.top + scroller.height - 20)
        window.scrollTo(0,scroller.top + 30);
        else if (yTop < scroller.top + 20)
        window.scrollTo(0,scroller.top - 30);
    },
    
    scrollStart: function(e) {
      var $this = this;
      this.dragged = e;
  //    this.scrollPoll = setInterval(function() { $this.scrollSome() } , 10);
    },
    
    scrollEnd: function(e) {
      clearInterval(this.scrollPoll);
    },

    /**
     * Display list item's menu. Show all item icons except progress
     *
     * @param HtmlElementLi li A reference to item element
     *
     * @access private
     */
    showMenu: function(li)
    {
        var self = this;    
        
        $H(this.icons).each(function(icon)
        {
            if(!li[icon.key] || icon.key == 'progress') return;
            
            try {
                li[icon.key].setOpacity(self.visibleMenuOpacity);            
            } catch(e) {
                li[icon.key].style.visibility = 'visible';
            }
        });
    },

    /**
     * Hides list item's menu
     *
     * @param HtmlElementLi li A reference to item element
     *
     * @access private
     */
    hideMenu: function(li)
    {
        var self = this;    
    
        $H(this.icons).each(function(icon)
        {
            if(!li[icon.key] || icon.key == 'progress') return;
            
            try {
                li[icon.key].setOpacity(self.hiddenMenuOpacity);
            } catch(e) {
                li[icon.key].style.visibility = 'hidden';
            }
        });
    },

    /**
     * Initiates item order (position) saving action
     *
     * @access private
     */
    saveSortOrder: function()
    {
        var self = this;
        
        var order = Sortable.serialize(this.ul.id);
        if(order)
        {
            // display feedback
            this.onProgress(this.dragged);

            // execute the action
            this._currentLi = this.dragged;
          
            var url = this.callbacks.beforeSort.call(this, this.dragged, order);
            new LiveCart.AjaxRequest(
                url,
                false,
                // the object context mystically dissapears when onComplete function is called,
                // so the only way I could make it work is this
                function(param)
                {
                    self.restoreDraggedItem(param.responseText);
                }
            );
        }
    },

    /**
     * This function is called when sort response arives
     *
     * @param XMLHttpRequest originalRequest Ajax request object
     *
     * @access private
     */
    restoreDraggedItem: function(item)
    {
        // if moving elements from one active list to another we should also change the id of the HTMLLElement 
        if(this.dragged.prevParentId != this.dragged.parentNode.id && this.dragged.parentNode.id == this.ul.id)
        {
            this.dragged.id = this.dragged.parentNode.id + "_" + this.dragged.id.substring(this.dragged.prevParentId.length + 1); 
        }
        
        this.rebindIcons(this.dragged);
        this.hideMenu(this.dragged);

        this._currentLi = this.dragged;
        
        var url = this.callbacks.afterSort.call(this, this.dragged, item);
        this.colorizeItems();
        this.dragged.prevParentId = this.ul.id;
        this.offProgress(this.dragged);

        this.dragged = false;
    },

    /**
     * Keyboard access functionality
     *     - navigate list using up/down arrow keys
     *     - move items up/down using Shift + up/down arrow keys
     *     - delete items with Del key
     *     - drop focus ("exit" list) with Esc key
     *
     * @param KeyboardEvent keyboard KeyboardEvent object
     * @param HtmlElementLi li A reference to item element
     *
     * @access private
     *
     * @todo Edit items with Enter key
     */
    navigate: function(keyboard, li)
    {
        switch(keyboard.getKey())
        {
            case keyboard.KEY_UP: // sort/navigate up
                if (keyboard.isShift())
                {
                    prev = this.getPrevSibling(li);

                    prev = (prev == prev.parentNode.lastChild) ? null : prev;

                    this.moveNode(li, prev);
                }
            break;

            case keyboard.KEY_DOWN: // sort/navigate down

                if (keyboard.isShift())
                {
                    var next = this.getNextSibling(li);
                    if (next != next.parentNode.firstChild) next = next.nextSibling;

                    this.moveNode(li, next);
                }
            break;

            case keyboard.KEY_DEL: // delete
                if(this.icons['delete']) this.bindAction(li, 'delete');
            break;

            case keyboard.KEY_ESC:  // escape - lose focus
                li.blur();
            break;
        }
    },

    /**
     * Moves list node
     *
     * @param HtmlElementLi li A reference to item element
     * @param HtmlElementLi beforeNode A reference to item element
     *
     * @access private
     */
    moveNode: function(li, beforeNode)
    {
        var self = this;

        this.dragged = li;

        li.parentNode.insertBefore(this.dragged, beforeNode);

        this.sortTimerStart = (new Date()).getTime();
        setTimeout(function(e)
        {
            if((new Date()).getTime() - self.sortTimerStart >= 1000)
            {
                self.saveSortOrder();
            }
        }, this.keyboardSortTimeout);
    },

    /**
     * Gets next sibling for element in node list.
     * If the element is the last node, the first node is being returned
     *
     * @param HtmlElementLi li A reference to item element
     *
     * @access private
     *
     * @return HtmlElementLi Next sibling
     */
    getNextSibling: function(element)
    {
        return element.nextSibling ? element.nextSibling : element.parentNode.firstChild;
    },

    /**
     * Gets previous sibling for element in node list.
     * If the element is the first node, the last node is being returned
     *
     * @param HtmlElementLi li A reference to item element
     *
     * @access private
     *
     * @return Node Previous sibling
     */
    getPrevSibling: function(element)
    {
        return !element.previousSibling ? element.parentNode.lastChild : element.previousSibling;
    },

    /**
     * Remove record from active list
     * 
     * @param HtmlElementLi li A reference to item element
     */
    remove: function(li, touch)
    {
        if(touch !== false) touch = true;
        
        if(touch && BrowserDetect.browser != 'Explorer')
        {
            Effect.SwitchOff(li, {duration: 1});
            setTimeout(function() { 
                Element.remove(li); 
            }, 10);
        }
        else
        {
            Element.remove(li);
        }
    },
    
    /**
     * Collapse all opened records
     * 
     * @param lists You can specify wich lists to collapse
     */
    collapseAll: function()
    {
        var activeLists = {};
        
        if(!this.ul)
        {
            activeLists = ActiveList.prototype.activeListsUsers;
        }
        else
        {
            activeLists[this.ul.id] = true;
        }
        
        $H(activeLists).each(function(activeList) 
        {
            if(!activeList.value.ul || 0 >= activeList.value.ul.offsetHeight) return; // if list is invisible there is no need to collapse it
            
            var containers = document.getElementsByClassName('activeList_container', activeList.value.ul);
            
            for(var i = 0; i < containers.length; i++)
            {
                if(0 >= containers[i].offsetHeight) continue;

                activeList.value.toggleContainerOff(containers[i]);
            }
        });
    },
    
    
    recreateVisibleLists: function()
    {
        $H(ActiveList.prototype.activeListsUsers).each(function(activeList) 
        {
            if(!activeList.value.ul || 0 >= activeList.value.ul.offsetHeight) return; // if list is invisible there is no need to collapse it
            ActiveList.prototype.getInstance(activeList.value.ul).touch();
        });
    },
    
    /**
     * Get list of references to all ActiveList ActiveRecords (li)
     */
    getChildList: function()
    {
        
        var liArray = this.ul.getElementsByTagName("li");
        var childList = [];
        
        for(var i = 0; i < liArray.length; i++)
        {
            if(this.ul == liArray[i].parentNode && !Element.hasClassName(liArray[i], 'ignore') && !Element.hasClassName(liArray[i], 'dom_template'))
            {
                childList[childList.length] = liArray[i];
            }
        }
        
        return childList;
    },
    
    /**
     * Make list work again
     */
    touch: function()
    {
        this.generateAcceptFromArray();
        this.createSortable();
    }
}


/***************************************************
 * backend\DeliveryZone.js
 ***************************************************/

Backend.DeliveryZone = Class.create();
Backend.DeliveryZone.prototype = 
{
  	Links: {},
    Messages: {},
    
    treeBrowser: null,
  	
  	urls: new Array(),
	  
	initialize: function(zones)
	{
        
		Backend.DeliveryZone.prototype.treeBrowser = new dhtmlXTreeObject("deliveryZoneBrowser","","", false);
		Backend.DeliveryZone.prototype.treeBrowser.setOnClickHandler(this.activateZone);
		
		Backend.DeliveryZone.prototype.treeBrowser.def_img_x = 'auto';
		Backend.DeliveryZone.prototype.treeBrowser.def_img_y = 'auto';
				
		Backend.DeliveryZone.prototype.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		Backend.DeliveryZone.prototype.treeBrowser.setOnClickHandler(this.activateZone.bind(this));
        
        Event.observe($("newZoneInputButton"), 'click', function(e){ Event.stop(e); this.addNewZone(); }.bind(this))
        
		Backend.DeliveryZone.prototype.treeBrowser.showFeedback = 
			function(itemId) 
			{
				if (!this.iconUrls)
				{
					this.iconUrls = new Object();	
				}
				
				this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
				this.setItemImage(itemId, '../../../image/indicator.gif');
			}
		
		Backend.DeliveryZone.prototype.treeBrowser.hideFeedback = 
			function()
			{
				for (var itemId in this.iconUrls)
				{
					this.setItemImage(itemId, this.iconUrls[itemId]);	
				}				
			}
            
    	this.insertTreeBranch(zones, 0);    
        
        if(!Backend.ajaxNav.getHash().match(/zone_-?\d+#\w+/)) window.location.hash = '#zone_-1#tabDeliveryZoneShipping__';
		this.tabControl = TabControl.prototype.getInstance('deliveryZoneManagerContainer', this.craftTabUrl, this.craftContainerId, {}); 
        
        this.bindEvents();
	},

    bindEvents: function()
    {
        Event.observe($("deliveryZone_delete"), 'click', function(e) 
        {
            Event.stop(e);
            this.deleteZone();
        }.bind(this));
    },
    
    deleteZone: function()
    {
        var $this = this;
        
        if(confirm(Backend.DeliveryZone.prototype.Messages.confirmZoneDelete)) 
        {
		    new LiveCart.AjaxRequest(
    			Backend.DeliveryZone.prototype.Links.remove + '/' + Backend.DeliveryZone.prototype.activeZone,
    			false,
                function(response) 
                { 
                    response = eval("(" + response.responseText + ")");
                    if('success' == response.status)
                    {
                        Backend.DeliveryZone.prototype.treeBrowser.deleteItem(Backend.DeliveryZone.prototype.activeZone, true);
                        var firstId = false;
                        if(firstId = parseInt(Backend.DeliveryZone.prototype.treeBrowser._globalIdStorage[1]))
                        {
                            Backend.DeliveryZone.prototype.treeBrowser.selectItem(firstId, true);
                        }
                    }
                }
            );
        }
    },
    
	addNewZone: function()
	{
		new LiveCart.AjaxRequest(
			Backend.DeliveryZone.prototype.Links.create,
			false,
            function(response) 
            { 
                this.afterNewZoneAdded(eval("(" + response.responseText + ")")); 
            }.bind(this)
		);
	},

	afterNewZoneAdded: function(response)
	{
        Backend.DeliveryZone.prototype.treeBrowser.insertNewItem(0, response.zone.ID, response.zone.name, 0, 0, 0, 0, 'SELECT');
        this.activateZone(response.ID);
	},
    
    craftTabUrl: function(url)
    {
        return url.replace(/_id_/, Backend.DeliveryZone.prototype.treeBrowser.getSelectedItemId());
    },

    craftContainerId: function(tabId)
    {
        return tabId + '_' +  Backend.DeliveryZone.prototype.treeBrowser.getSelectedItemId() + 'Content';
    },
	
	insertTreeBranch: function(treeBranch, rootId)
	{
        $A(treeBranch).each(function(node)
		{
            Backend.DeliveryZone.prototype.treeBrowser.insertNewItem(rootId, node.ID, node.name, null, 0, 0, 0, '', 1);
            this.treeBrowser.showItemSign(node.ID, 0);
            var zone = document.getElementsByClassName("standartTreeRow", $("deliveryZoneBrowser")).last();
            zone.id = 'zone_' + node.ID;
            zone.onclick = function()
            {
                Backend.DeliveryZone.prototype.treeBrowser.selectItem(node.ID, true);
            }
		}.bind(this));
	},
	
	activateZone: function(id)
	{
        if(id == -1)
        {
            if(Backend.ajaxNav.getHash().match(/tabDeliveryZoneCountry/))
            {
                Backend.ajaxNav.ignoreNextAdd = false;
                Backend.ajaxNav.add('zone_' + id + '#tabDeliveryZoneShipping');
                Backend.ajaxNav.ignoreNextAdd = true;
            }
            
            var activateTab = $('tabDeliveryZoneShipping');
            $("tabDeliveryZoneCountry").hide();
            $("deliveryZone_delete").hide();
        }
        else
        {
            var activateTab = $('tabDeliveryZoneCountry');
            $("tabDeliveryZoneCountry").show();
            $("deliveryZone_delete").show();
        }
        
        if(Backend.DeliveryZone.prototype.activeZone && Backend.DeliveryZone.prototype.activeZone != id)
        {
            Backend.DeliveryZone.prototype.activeZone = id;
    		Backend.DeliveryZone.prototype.treeBrowser.showFeedback(id);
            
            Backend.ajaxNav.add('zone_' + id);
            
            this.tabControl.activateTab(activateTab, function() { 
                Backend.DeliveryZone.prototype.treeBrowser.hideFeedback(id);
            });
        }
        
        Backend.DeliveryZone.prototype.activeZone = id;
	},
	
	displayCategory: function(response)
	{
		Backend.DeliveryZone.prototype.treeBrowser.hideFeedback();	
		var cancel = document.getElementsByClassName('cancel', $('deliveryZoneContent'))[0];
		Event.observe(cancel, 'click', this.resetForm.bindAsEventListener(this));
	},
	
	resetForm: function(e)
	{
		var el = Event.element(e);
		while (el.tagName != 'FORM')
		{
			el = el.parentNode;
		}
		
		el.reset();		
	},
	
	save: function(form)
	{
		var indicator = document.getElementsByClassName('progressIndicator', form)[0];
		new LiveCart.AjaxRequest(form, indicator, this.displaySaveConfirmation.bind(this));	
	},
	
	displaySaveConfirmation: function()
	{
		new Backend.SaveConfirmationMessage(document.getElementsByClassName('yellowMessage')[0]);			
	} 
}

Backend.DeliveryZone.CountriesAndStates = Class.create();
Backend.DeliveryZone.CountriesAndStates.prototype = 
{
    Links: {},
    Messages: {},
    
    CallbacksCity: {
        'beforeDelete': function(li) 
        {
            if(confirm(Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete))
            {
                return Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteCityMask + "/" + this.getRecordId(li);
            }
        },
        'afterDelete': function(li, response)
        {
            response = eval('(' + response + ')')
            
            if('success' == response.status) {
                this.remove(li);     
            }
        },
        'beforeEdit': function(li) 
        {
            Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li);
        },
        'afterEdit': function(li, response) {}
    },
    CallbacksZip: {
        'beforeDelete': function(li) 
        {
            if(confirm(Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete))
            {
                return Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteZipMask + "/" + this.getRecordId(li);  
            }              
        },
        'afterDelete': function(li, response)
        {
            response = eval('(' + response + ')')
            
            if('success' == response.status) {
                this.remove(li);     
            }
        },
        'beforeEdit': function(li) 
        {
            Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li);                
        },
        'afterEdit': function(li, response) {}
    },
    CallbacksAddress: {
        'beforeDelete': function(li) 
        {
            if(confirm(Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete))
            {
                return Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteAddressMask + "/" + this.getRecordId(li);
            }
        },
        'afterDelete': function(li, response)
        {
            response = eval('(' + response + ')');
            
            if('success' == response.status) {
                this.remove(li);     
            }
        },
        'beforeEdit': function(li) 
        {
            Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li);
        },
        'afterEdit': function(li, response) {}
    },
    
    
    prefix: 'countriesAndStates_',
    instances: {},
    
    initialize: function(root, zoneID) 
    {
        this.zoneID = zoneID;

        this.findNodes(root);
        this.bindEvents();
		
		Backend.DeliveryZone.prototype.treeBrowser.setItemText(Backend.DeliveryZone.prototype.activeZone, this.nodes.name.value)        
        
		// I did't found out if sorting the fields realy matters, but they are slowing things down for sure
//        this.sortSelect(this.nodes.inactiveCountries);
//        this.sortSelect(this.nodes.activeCountries);
//        this.sortSelect(this.nodes.inactiveStates);
//        this.sortSelect(this.nodes.activeStates);
    },
    
    getInstance: function(root, zoneID) 
    {
        if(!Backend.DeliveryZone.CountriesAndStates.prototype.instances[$(root).id])
        {
            Backend.DeliveryZone.CountriesAndStates.prototype.instances[$(root).id] = new Backend.DeliveryZone.CountriesAndStates(root, zoneID);
        }
        
        return Backend.DeliveryZone.CountriesAndStates.prototype.instances[$(root).id];
    },
    
    findNodes: function(root)
    {
        this.nodes = {};
        this.nodes.root = $(root);
        
        this.nodes.form = this.nodes.root.tagName == 'FORM' ? this.nodes.root : this.nodes.root.down('form');

        this.nodes.name = this.nodes.form.down('.' + this.prefix + 'name');

        this.nodes.addCountryButton     = this.nodes.root.down('.' + this.prefix + 'addCountry');
        this.nodes.removeCountryButton  = this.nodes.root.down('.' + this.prefix + 'removeCountry');
        this.nodes.addStateButton       = this.nodes.root.down('.' + this.prefix + 'addState');
        this.nodes.removeStateButton    = this.nodes.root.down('.' + this.prefix + 'removeState');
        
        this.nodes.inactiveCountries    = this.nodes.root.down('.' + this.prefix + 'inactiveCountries');
        this.nodes.activeCountries      = this.nodes.root.down('.' + this.prefix + 'activeCountries');
        this.nodes.inactiveStates       = this.nodes.root.down('.' + this.prefix + 'inactiveStates');
        this.nodes.activeStates         = this.nodes.root.down('.' + this.prefix + 'activeStates');
        
        this.nodes.cityMasks            = this.nodes.root.down('.' + this.prefix + 'cityMasks');
        this.nodes.cityMasksList        = this.nodes.cityMasks.down('.' + this.prefix + 'cityMasksList');
        this.nodes.cityMaskNew          = this.nodes.cityMasks.down('.' + this.prefix + 'newMask');
        this.nodes.cityMaskNewButton    = this.nodes.cityMasks.down('.' + this.prefix + 'newMaskButton');
        
        this.nodes.zipMasks            = this.nodes.root.down('.' + this.prefix + 'zipMasks');
        this.nodes.zipMasksList        = this.nodes.zipMasks.down('.' + this.prefix + 'zipMasksList');
        this.nodes.zipMaskNew          = this.nodes.zipMasks.down('.' + this.prefix + 'newMask');
        this.nodes.zipMaskNewButton    = this.nodes.zipMasks.down('.' + this.prefix + 'newMaskButton');
        
        this.nodes.addressMasks            = this.nodes.root.down('.' + this.prefix + 'addressMasks');
        this.nodes.addressMasksList        = this.nodes.addressMasks.down('.' + this.prefix + 'addressMasksList');
        this.nodes.addressMaskNew          = this.nodes.addressMasks.down('.' + this.prefix + 'newMask');
        this.nodes.addressMaskNewButton    = this.nodes.addressMasks.down('.' + this.prefix + 'newMaskButton');
        
        this.nodes.zonesAndUnions       = this.nodes.root.down('.' + this.prefix + 'regionsAndUnions').getElementsByTagName('a');
        
        this.nodes.observedElements = document.getElementsByClassName("observed", this.nodes.form);
    },
    
    toggleMask: function(li)
    {
        var self = Backend.DeliveryZone.CountriesAndStates.prototype.getInstance(li.up('form'));
        var input = li.down('input');
        var title = li.down('.maskTitle');
        if(li.down('input').style.display == 'inline')
        {      
            var list = null;     
            if(list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'cityMasksList')) 
            {
                var url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveAddressMask;
            }
            else if(list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'zipMasksList'))
            {
                var url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveZipMask;
            }
            else if(list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'addressMasksList'))
            {
                var url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveCityMask;
            }
            
            var activeList = ActiveList.prototype.getInstance(list);
            new LiveCart.AjaxRequest(
                url + "/" + activeList.getRecordId(li) + '&mask=' + li.down('input').value,
                false,
                function(response) {
                    var response = eval('(' + response.responseText + ')');
                    
                    if(response.status == 'success') 
                    {
                        input.style.display = 'none';
                        title.style.display = 'inline';
                        title.update(input.value);
                        ActiveForm.prototype.resetErrorMessage(input);
                    }
                    else
                    {
                        ActiveForm.prototype.setErrorMessage(input, response.errors.mask, true);
                    }
                }.bind(self)
            );
        }
        else
        {
            input.style.display = 'inline';
            title.style.display = 'none';
        }
    },
    
    bindEvents: function()
    {
        Event.observe(this.nodes.addCountryButton, 'click', function(e) { Event.stop(e); this.addCountry(); }.bind(this));
        Event.observe(this.nodes.removeCountryButton, 'click', function(e) { Event.stop(e); this.removeCountry(); }.bind(this));
        Event.observe(this.nodes.addStateButton, 'click', function(e) { Event.stop(e); this.addState(); }.bind(this));
        Event.observe(this.nodes.form, 'submit', function(e) { Event.stop(e); this.save(); }.bind(this));
        Event.observe(this.nodes.removeStateButton, 'click', function(e) { Event.stop(e); this.removeState(); }.bind(this));
        Event.observe(this.nodes.cityMaskNewButton, 'click', function(e) { Event.stop(e); this.addNewCityMask(this.nodes.cityMaskNew); }.bind(this));
        Event.observe(this.nodes.zipMaskNewButton, 'click', function(e) { Event.stop(e); this.addNewZipMask(this.nodes.zipMaskNew); }.bind(this));
        Event.observe(this.nodes.addressMaskNewButton, 'click', function(e) { Event.stop(e); this.addNewAddressMask(this.nodes.addressMaskNew); }.bind(this));

        $A(this.nodes.zonesAndUnions).each(function(zoneOrUnion) {
            Event.observe(zoneOrUnion, 'click', function(e) { Event.stop(e); this.selectZoneOrUnion(zoneOrUnion.hash.substring(0,1) == '#' ? zoneOrUnion.hash.substring(1) : zoneOrUnion.hash); }.bind(this));
        }.bind(this));
        
        $A(this.nodes.observedElements).each(function(element) {
            Event.observe(element, 'change', function(e) { this.save() }.bind(this));
        }.bind(this));
		
		
		
        window.onunload = function(e) { this.save(); }.bind(this);
		
        
        Event.observe(this.nodes.addressMaskNew, 'keyup', function(e) 
        {
           if(KeyboardEvent.prototype.init(e).isEnter()) 
           {
               if(!e.target) e.target = e.srcElement;
               this.addNewAddressMask(e.target);
           }
        }.bind(this));
        
        Event.observe(this.nodes.zipMaskNew, 'keyup', function(e) 
        {
           if(KeyboardEvent.prototype.init(e).isEnter()) 
           {
               if(!e.target) e.target = e.srcElement;
               this.addNewZipMask(e.target);
           }
        }.bind(this));
        
        Event.observe(this.nodes.cityMaskNew, 'keyup', function(e) 
        {
           if(KeyboardEvent.prototype.init(e).isEnter()) 
           {
               if(!e.target) e.target = e.srcElement;
               this.addNewCityMask(e.target);
           }
        }.bind(this));
        
        document.getElementsByClassName(this.prefix + 'mask').each(function(mask) {
           this.bindMask(mask);
        }.bind(this));
    },
    
    bindMask: function(mask) 
    {
       Event.observe(mask, 'keyup', function(e) 
       { 
           if(KeyboardEvent.prototype.init(e).isEnter()) {
               if(!e.target) e.target = e.srcElement;
               this.toggleMask(e.target.up('li'));
           }
       }.bind(this));
    },
    
    save: function() {
        this.saving = true;
        
        Backend.DeliveryZone.prototype.treeBrowser.setItemText(Backend.DeliveryZone.prototype.activeZone, this.nodes.name.value)
        new LiveCart.AjaxRequest(this.nodes.form);
    
        this.saving = false;
    },
    
    selectZoneOrUnion: function(regionName) 
    {
        includedCountriesCodes = Backend.DeliveryZone.countryGroups[regionName];
        
        $A(this.nodes.inactiveCountries.options).each(function(option) 
        {
            option.selected = includedCountriesCodes.indexOf(option.value) !== -1 ? true : false;
        });
    },
    
    addNewCityMask: function(mask)
    {
        new LiveCart.AjaxRequest(
            Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveCityMask + "/" + '?zoneID=' + this.zoneID + '&mask=' + mask.value,
            false,
            function(response) 
            {
                response = eval('(' + response.responseText + ')');
                if('success' == response.status)
                {
                    var activeList = ActiveList.prototype.getInstance(this.nodes.cityMasksList);
                    var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />');

                    mask.value = '';
                    this.bindMask(li.down('input'));
                    ActiveForm.prototype.resetErrorMessage(mask);
                }
                else
                {
                    ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
                }
            }.bind(this)
        );
    },
    
    addNewZipMask: function(mask)
    {
        new LiveCart.AjaxRequest (
            Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveZipMask + "/" + '?zoneID=' + this.zoneID + '&mask=' + mask.value,
            false,
            function(response) 
            {
                response = eval('(' + response.responseText + ')');
                if('success' == response.status)
                {
                    var activeList = ActiveList.prototype.getInstance(this.nodes.zipMasksList);
                    var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />');

                    mask.value = '';
                    this.bindMask(li.down('input'));
                    ActiveForm.prototype.resetErrorMessage(mask);
                }
                else
                {
                    ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
                }
            }.bind(this)
        );
    },
    
    addNewAddressMask: function(mask)
    {
        new LiveCart.AjaxRequest(
            Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveAddressMask + "/" + '?zoneID=' + this.zoneID + '&mask=' + mask.value,
            false,
            function(response) 
            {
                response = eval('(' + response.responseText + ')');
                if('success' == response.status)
                {
                    var activeList = ActiveList.prototype.getInstance(this.nodes.addressMasksList);
                    var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />');
    
                    mask.value = '';
                    this.bindMask(li.down('input'));
                    ActiveForm.prototype.resetErrorMessage(mask);
                }
                else
                {
                    ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
                }
            }.bind(this)
        );
    },
    
    sortSelect: function(select)
    {
        var options = [];
        $A(select.options).each(function(option) { options[options.length] = option; });
        select.options.length = 0;
        optionso = options.sort(function(a, b){ return (b.text < a.text) - (a.text < b.text); });
        $A(options).each(function(option) { select.options[select.options.length] = option; });
    },
    
    moveSelectedOptions: function(fromSelect, toSelect)
    {
        fromSelectOptions = fromSelect.options;
        toSelectOptions = toSelect.options;
        
        // Deselect options
        $A(toSelectOptions).each(function(option) { option.selected = false; });
        
        // move options to another list
        $A(fromSelectOptions).each(function(option) 
        {
            if(option.selected) 
            {
                toSelectOptions[toSelectOptions.length] = option;
            }
        });
        
        this.sortSelect(toSelect);
    },
    
    sendSelectsData: function(activeSelect, inactiveSelect, url)
    {
        var active = "";
        $A(activeSelect.options).each(function(option) {
            active += "active[]=" + option.value + "&";
        });
        active.substr(0, active.length - 1);
        
        
        var inactive = "";
        $A(inactiveSelect.options).each(function(option) {
            inactive += "inactive[]=" + option.value + "&";
        });
        inactive.substr(0, active.length - 1);
        
        
        new LiveCart.AjaxRequest(url + "/" + this.zoneID + "?" + active + inactive);
    },
    
    addCountry: function()
    {
        this.moveSelectedOptions(this.nodes.inactiveCountries, this.nodes.activeCountries);
        this.sendSelectsData(this.nodes.activeCountries, this.nodes.inactiveCountries, Backend.DeliveryZone.prototype.Links.saveCountries);
    },
    
    removeCountry: function()
    {
        this.moveSelectedOptions(this.nodes.activeCountries, this.nodes.inactiveCountries);
        this.sendSelectsData(this.nodes.activeCountries, this.nodes.inactiveCountries, Backend.DeliveryZone.prototype.Links.saveCountries);
    },
    
    addState: function()
    {
        this.moveSelectedOptions(this.nodes.inactiveStates, this.nodes.activeStates);
        this.sendSelectsData(this.nodes.activeStates, this.nodes.inactiveStates, Backend.DeliveryZone.prototype.Links.saveStates);
    },
    
    removeState: function()
    {
        this.moveSelectedOptions(this.nodes.activeStates, this.nodes.inactiveStates);
        this.sendSelectsData(this.nodes.activeStates, this.nodes.inactiveStates, Backend.DeliveryZone.prototype.Links.saveStates);
    },
}








Backend.DeliveryZone.ShippingService = Class.create();
Backend.DeliveryZone.ShippingService.prototype = 
{
    Links: {},
    Messages: {},
 
    Callbacks: {
        'beforeDelete': function(li) 
        {
            if(confirm(Backend.DeliveryZone.ShippingService.prototype.Messages.confirmDelete))
            {
                return Backend.DeliveryZone.ShippingService.prototype.Links.remove + "/" + this.getRecordId(li);
            }
        },
        'afterDelete': function(li, response)
        {
            response = eval('(' + response + ')')
            
            if('success' == response.status) {
                this.remove(li);     
            }
        },
        
        beforeEdit:     function(li)
        {
            if(this.isContainerEmpty(li, 'edit')) 
            {
                return Backend.DeliveryZone.ShippingService.prototype.Links.edit + '/' + this.getRecordId(li)
            }
            else 
            {
                var newServiceForm = $("shippingService_" + this.getRecordId(li, 2) + '_');
                if(newServiceForm.up().style.display == 'block')
                {
                    Backend.DeliveryZone.ShippingService.prototype.getInstance(newServiceForm).hideNewForm();
                }

                this.toggleContainer(li, 'edit');
            }
        },
       
        afterEdit:      function(li, response)
        {
            var newServiceForm = $("shippingService_" + this.getRecordId(li, 2) + '_');
            if(newServiceForm.up().style.display == 'block')
            {
                Backend.DeliveryZone.ShippingService.prototype.getInstance(newServiceForm).hideNewForm();
            }
            this.getContainer(li, 'edit').update(response);
            this.toggleContainer(li, 'edit');
        },
         
        beforeSort:     function(li, order)
        {
            return Backend.DeliveryZone.ShippingService.prototype.Links.sortServices + '?target=' + "shippingService_servicesList_" + this.getRecordId(li, 2) + "&" + order
        },
    
        afterSort:      function(li, response) { }
    },
     
    instances: {},
    
    prefix: 'shippingService_',
    
    initialize: function(root, service)
    {
        try
        {
            this.service = service;
            this.deliveryZoneId = this.service.DeliveryZone ? this.service.DeliveryZone.ID : '';
            this.findUsedNodes(root);
            this.bindEvents();
            this.rangeTypeChanged();
            this.servicesActiveList = ActiveList.prototype.getInstance(this.nodes.servicesList);
            
            if(this.service.ID)
            {
                Form.State.backup(this.nodes.form);
				
				this.nodes.root.down('.rangeTypeStatic').show();
				document.getElementsByClassName('rangeType', this.nodes.root).each(function(rangeTypeField) { rangeTypeField.hide(); }.bind(this));
            }
        }
        catch(e)
        {
            console.info(e);
        }
    },
        
    getInstance: function(rootNode, service)
    {
        var rootId = $(rootNode).id;
        if(!Backend.DeliveryZone.ShippingService.prototype.instances[rootId])
        {
            Backend.DeliveryZone.ShippingService.prototype.instances[rootId] = new Backend.DeliveryZone.ShippingService(rootId, service);
        }
        
        return Backend.DeliveryZone.ShippingService.prototype.instances[rootId];
    },
    
    findUsedNodes: function(root)
    {
        this.nodes = {};
        
        this.nodes.root = $(root);
        this.nodes.form = this.nodes.root;

        this.nodes.controls = this.nodes.root.down('.' + this.prefix + 'controls');
        this.nodes.save = this.nodes.controls.down('.' + this.prefix + 'save');
        this.nodes.cancel = this.nodes.controls.down('.' + this.prefix + 'cancel');
        
        this.nodes.rangeTypes = document.getElementsByClassName(this.prefix + 'rangeType', this.nodes.root);
        
        this.nodes.servicesList = $$('.' + this.prefix + 'servicesList_' + this.deliveryZoneId)[0];
        this.nodes.ratesList = $(this.prefix + 'ratesList_' + this.deliveryZoneId + '_' + (this.service.ID ? this.service.ID : ''));
        this.nodes.ratesNewForm = $(this.prefix + 'new_rate_' + this.deliveryZoneId + '_' + (this.service.ID ? this.service.ID : '') + '_form');
        
        
        
        if(!this.service.ID)
        {
            this.nodes.menu = $(this.prefix + "menu_" + this.deliveryZoneId);
            this.nodes.menuCancelLink = $(this.prefix + "new_" + this.deliveryZoneId + "_cancel");
            this.nodes.menuShowLink = $(this.prefix + "new_" + this.deliveryZoneId + "_show");
            this.nodes.menuForm = $(this.prefix + "new_service_" + this.deliveryZoneId + "_form");
        }
        
        this.nodes.name = this.nodes.root.down('.' + this.prefix + 'name');
    },
    
    bindEvents: function()
    {
       Event.observe(this.nodes.save, 'click', function(e) { Event.stop(e); this.save(); }.bind(this));
       Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
       if(!this.service.ID)
       {
           Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
       }
       
       $A(this.nodes.rangeTypes).each(function(radio)
       {
           Event.observe(radio, 'click', function(e) { this.rangeTypeChanged(); }.bind(this));
       }.bind(this));
       
       $A(document.getElementsByClassName('shippingService_rateFloatValue', this.nodes.root)).each(function(input)
       {
           Event.observe(input, "keyup", function(e){ NumericFilter(this); });
       }.bind(this));
	   
            
        Event.observe(this.nodes.form, 'submit', function(e)
        {
            Event.stop(e);
            this.save();
        }.bind(this), false);
    },
    
    rangeTypeChanged: function()
    {
        var radio = null;
        $A(this.nodes.rangeTypes).each(function(r){ if(r.checked) radio = r; });
        
        if(radio.value == 0) 
        {
            document.getElementsByClassName(this.prefix + "subtotalRange").each(function(fieldset) { fieldset.style.display = 'none'; });
            document.getElementsByClassName(this.prefix + "subtotalPercentCharge").each(function(fieldset) { fieldset.up('fieldset').style.display = 'none'; });
            document.getElementsByClassName(this.prefix + "perKgCharge").each(function(fieldset) { fieldset.up('fieldset').style.display = 'block'; });
            document.getElementsByClassName(this.prefix + "weightRange").each(function(fieldset) { fieldset.style.display = 'block'; });
        }
        else
        {
            document.getElementsByClassName(this.prefix + "subtotalRange").each(function(fieldset) { fieldset.style.display = 'block'; });
            document.getElementsByClassName(this.prefix + "subtotalPercentCharge").each(function(fieldset) { fieldset.up('fieldset').style.display = 'block'; });
            document.getElementsByClassName(this.prefix + "perKgCharge").each(function(fieldset) { fieldset.up('fieldset').style.display = 'none'; });
            document.getElementsByClassName(this.prefix + "weightRange").each(function(fieldset) { fieldset.style.display = 'none'; });
        }
    },
    
    showNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuCancelLink]);
        ActiveForm.prototype.showNewItemForm(this.nodes.menuShowLink, this.nodes.menuForm); 
        ActiveList.prototype.collapseAll();
    },
    
    hideNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuShowLink]);
        ActiveForm.prototype.hideNewItemForm(this.nodes.menuCancelLink, this.nodes.menuForm); 
                
        $A(this.nodes.ratesList.getElementsByTagName('li')).each(function(li) {
           Element.remove(li);
        });
        
        $A(this.nodes.root.getElementsByTagName('input')).each(function(input) {
            if(input.type == 'text') input.value = ''; 
        });
    },
    
    save: function()
    {
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        var action = this.service.ID 
            ? Backend.DeliveryZone.ShippingService.prototype.Links.update
            : Backend.DeliveryZone.ShippingService.prototype.Links.create;
            
        new LiveCart.AjaxRequest(
            action + '?' + Form.serialize(this.nodes.form),
            false,
            function(response) 
            { 
                var response = eval("(" + response.responseText + ")");
                this.afterSave(response);     
            }.bind(this)
        );
    },
    
    afterSave: function(response)
    {
        if(response.status == 'success')
        {
            ActiveForm.prototype.resetErrorMessages(this.nodes.form);
            if(!this.service.ID)
            {
                ratesCount = this.nodes.root.down(".activeList").getElementsByTagName("li").length;
                rangeTypeString = response.service.rangeType == 0 ? Backend.DeliveryZone.TaxRate.prototype.Messages.weightBasedRates : Backend.DeliveryZone.TaxRate.prototype.Messages.subtotalBasedRates;
				
                var li = this.servicesActiveList.addRecord(response.service.ID, '<span class="' + this.prefix + 'servicesList_title">' + this.nodes.name.value + ' ( <b class="ratesCount">' + ratesCount + '</b>' + rangeTypeString  + ' )</span>');
                this.hideNewForm();
            }
            else
            {
                Form.State.backup(this.nodes.form);
				
				this.nodes.root.up('li').down('.ratesCount').innerHTML = this.nodes.root.down(".activeList").getElementsByTagName("li").length;
                this.servicesActiveList.toggleContainer(this.nodes.root.up('li'), 'edit');
				
                if($H(response.service.newRates).size() > 0)
                {
                    var regexps = {};
                    $H(response.service.newRates).each(function(id) { regexps[id.value] = new RegExp(id.key); }.bind(this));
                    
                    $A(this.nodes.root.down('.activeList').getElementsByTagName('*')).each(function(elem)
                    {
                        if(elem.id) 
						{
						    $H(regexps).each(function(id) { 
							    if(elem.id.match(id.value))
								{
							        elem.id = elem.id.replace(id.value, id.key);
									return;
								} 
							}.bind(this));
						}
						
                        if(elem.name) 
                        {
                            $H(regexps).each(function(id) { 
                                elem.name = elem.name.replace(id.value, id.key);
                            }.bind(this));
                        }
                    }.bind(this));
                }
            }
        }
        else
        {
            ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors);
        }
    },
    
    cancel: function()
    {
        if(!this.service.ID)
        {
            this.hideNewForm();
        }
        else
        {
            this.servicesActiveList.toggleContainerOff(this.nodes.root.up('.activeList_editContainer'));
            Form.State.restore(this.nodes.form);
        }
    }
}








Backend.DeliveryZone.ShippingRate = Class.create();
Backend.DeliveryZone.ShippingRate.prototype = 
{
    Links: {},
    Messages: {},
 
    Callbacks: {
        'beforeDelete': function(li) 
        {
            Backend.DeliveryZone.ShippingRate.prototype.getInstance(li).remove();
        },
        'afterDelete': function(li) 
        {
        },
        'beforeEdit': function(li) 
        {
            console.info('before edit');
        },
        'afterEdit': function(li, response) 
        {
            console.info('after edit');
        },

        'beforeSort':     function(li, order)
        {
            return Backend.DeliveryZone.ShippingRate.prototype.Links.sortRates + '?target=' + "shippingService_ratesList&" + order
        },
    
        'afterSort':      function(li, response) { }
    },
    
    instances: {},
    
    prefix: 'shippingService_',
    
    newRateLastId: 0,
    
    initialize: function(root, rate)
    {
        this.rate = rate;
        this.deliveryZoneId = this.rate.ShippingService.DeliveryZone ? this.rate.ShippingService.DeliveryZone.ID : '';
        this.switchMetricsEnd = Backend.UnitConventer.prototype.getInstance("UnitConventer_Root_shippingService_" + (this.deliveryZoneId ? this.deliveryZoneId : '') + "_" + (rate.ShippingService.ID ? rate.ShippingService.ID : '') + "_" + (this.rate.ID ? this.rate.ID : '') + "_weightRangeEnd");
        this.switchMetricsStart = Backend.UnitConventer.prototype.getInstance("UnitConventer_Root_shippingService_" + (this.deliveryZoneId ? this.deliveryZoneId : '') + "_" + (rate.ShippingService.ID ? rate.ShippingService.ID : '') + "_" + (this.rate.ID ? this.rate.ID : '') + "_weightRangeStart");
    
 
        this.findUsedNodes(root);
        this.bindEvents();
        
        this.ratesActiveList = ActiveList.prototype.getInstance(this.nodes.ratesActiveList, Backend.DeliveryZone.ShippingRate.prototype.Callbacks);
        
        if(this.rate.ID)
        {
            this.nodes.controls.hide();
            this.nodes.weightRangeStart.name = 'rate_' + this.rate.ID + '_weightRangeStart';
            this.nodes.weightRangeEnd.name = 'rate_' + this.rate.ID + '_weightRangeEnd';
            this.nodes.subtotalRangeStart.name = 'rate_' + this.rate.ID + '_subtotalRangeStart';
            this.nodes.subtotalRangeEnd.name = 'rate_' + this.rate.ID + '_subtotalRangeEnd';
            this.nodes.flatCharge.name = 'rate_' + this.rate.ID + '_flatCharge';
            this.nodes.perItemCharge.name = 'rate_' + this.rate.ID + '_perItemCharge';
            this.nodes.subtotalPercentCharge.name = 'rate_' + this.rate.ID + '_subtotalPercentCharge';
            this.nodes.perKgCharge.name = 'rate_' + this.rate.ID + '_perKgCharge';
        }
        else
        {
            this.nodes.controls.show();
        }
    },
        
    getInstance: function(rootNode, rate)
    {
        var rootId = $(rootNode).id;
        if(!Backend.DeliveryZone.ShippingRate.prototype.instances[rootId])
        {
            Backend.DeliveryZone.ShippingRate.prototype.instances[rootId] = new Backend.DeliveryZone.ShippingRate(rootId, rate);
        }
        
        return Backend.DeliveryZone.ShippingRate.prototype.instances[rootId];
    },
    
    findUsedNodes: function(root)
    {
        this.nodes = {};
        
        this.nodes.root = $(root);

        this.nodes.controls = this.nodes.root.down('.' + this.prefix +     'rate_controls');
        this.nodes.save     = this.nodes.controls.down('.' + this.prefix + 'rate_save');
        this.nodes.cancel   = this.nodes.controls.down('.' + this.prefix + 'rate_cancel');
        
        if(!this.rate.ID)
        {
            this.nodes.menuCancelLink   = $(this.prefix + "new_rate_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_cancel");
            this.nodes.menuShowLink = $(this.prefix + "new_rate_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_show");
            this.nodes.menu =$(this.prefix + "rate_menu_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID);
            this.nodes.menuForm = $(this.prefix + "new_rate_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_form");
        }
        
        this.nodes.ratesActiveList = $(this.prefix + 'ratesList_' + this.deliveryZoneId + '_' + this.rate.ShippingService.ID);
        
        this.nodes.weightRangeStart = this.nodes.root.down('.weightRangeStart').down('.UnitConventer_NormalizedWeight');
        this.nodes.weightRangeStartHiValue = this.nodes.root.down('.weightRangeStart').down('.UnitConventer_HiValue');
        this.nodes.weightRangeStartLoValue = this.nodes.root.down('.weightRangeStart').down('.UnitConventer_LoValue');
        
		this.nodes.weightRangeEnd = this.nodes.root.down('.weightRangeEnd').down('.UnitConventer_NormalizedWeight');
        this.nodes.weightRangeEndHiValue = this.nodes.root.down('.weightRangeEnd').down('.UnitConventer_HiValue');
        this.nodes.weightRangeEndLoValue = this.nodes.root.down('.weightRangeEnd').down('.UnitConventer_LoValue');

		
		this.nodes.subtotalRangeStart = this.nodes.root.down('.' + this.prefix + 'subtotalRangeStart');
        this.nodes.subtotalRangeEnd = this.nodes.root.down('.' + this.prefix + 'subtotalRangeEnd');
        this.nodes.flatCharge = this.nodes.root.down('.' + this.prefix + 'flatCharge');
        this.nodes.perItemCharge = this.nodes.root.down('.' + this.prefix + 'perItemCharge');
        this.nodes.subtotalPercentCharge = this.nodes.root.down('.' + this.prefix + 'subtotalPercentCharge');
        this.nodes.perKgCharge = this.nodes.root.down('.' + this.prefix + 'perKgCharge');
    },
    
    bindEvents: function()
    {
       if(!this.rate.ID)
       {
	       $A(this.nodes.root.getElementsByTagName('input')).each(function(input)
	       {
	           Event.observe(input, 'keypress', function(e) { 
                  if(e.keyCode == 13)
                  {
                      Event.stop(e);
                      this.save(e);
                  } 
	           }.bind(this), false)
	       }.bind(this)); 
		
           Event.observe(this.nodes.save, 'click', function(e) { Event.stop(e); this.save(e); }.bind(this));
           Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); this.cancel();}.bind(this));
           Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
       }

       Event.observe(this.switchMetricsEnd.nodes.switchUnits, 'click', function(e) { this.switchMetricsStart.switchUnitTypes(); }.bind(this));
    },
    
    showNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuCancelLink ]);
        ActiveForm.prototype.showNewItemForm(this.nodes.menuShowLink, this.nodes.menuForm); 
    },
    
    hideNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuShowLink]);
        ActiveForm.prototype.hideNewItemForm(this.nodes.menuCancelLink, this.nodes.menuForm); 
    },
    
    save: function(event)
    {
        if(!this.rate.ID)
        {
            var rate = {
                'weightRangeStart': this.nodes.weightRangeStart.value,
                'weightRangeEnd': this.nodes.weightRangeEnd.value,
                'subtotalRangeStart': this.nodes.subtotalRangeStart.value,
                'subtotalRangeEnd': this.nodes.subtotalRangeEnd.value,
                'flatCharge': this.nodes.flatCharge.value,
                'perItemCharge': this.nodes.perItemCharge.value,
                'subtotalPercentCharge': this.nodes.subtotalPercentCharge.value,
                'perKgCharge': this.nodes.perKgCharge.value,
                'ShippingService': this.rate.ShippingService,
                'ID': 'new' + Backend.DeliveryZone.ShippingRate.prototype.newRateLastId
            };
            
            var rangeTypeRadio = this.nodes.root.up('form').down('.' + this.prefix + 'rangeType');
            if(!rangeTypeRadio.checked) rangeTypeRadio = this.nodes.root.up('form').down('.' + this.prefix + 'rangeType', 1);
            
            var rangeType = rangeTypeRadio.value;
            
            ActiveForm.prototype.resetErrorMessages(this.nodes.root.up('form'));
            new LiveCart.AjaxRequest(
                Backend.DeliveryZone.ShippingService.prototype.Links.validateRates + "?" + 
                    'rate__weightRangeStart=' + rate.weightRangeStart + '&' +
                    'rate__weightRangeEnd=' + rate.weightRangeEnd + '&' +
                    'rate__subtotalRangeStart=' + rate.subtotalRangeStart + '&' +
                    'rate__subtotalRangeEnd=' + rate.subtotalRangeEnd + '&' +
                    'rate__flatCharge=' + rate.flatCharge + '&' +
                    'rate__perItemCharge=' + rate.perItemCharge + '&' +
                    'rate__subtotalPercentCharge=' + rate.subtotalPercentCharge + '&' +
                    'rate__perKgCharge=' + rate.perKgCharge + '&' +
                    'rangeType=' + rangeType,
                false,
                function(resp) 
                { 
                    var resp = eval("(" + resp.responseText + ")");
                    this.afterAdd(resp, rate) 
                }.bind(this)
            );
            

            
        }
    },
    
    afterAdd: function(response, rate)
    {
        if(response.validation == 'success')
        {
            var newId = Backend.DeliveryZone.ShippingRate.prototype.newRateLastId;
            
            var li = this.ratesActiveList.addRecord('new' + newId, this.nodes.root);
            
            var idStart = this.prefix + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_";
            var idStartRegexp = new RegExp(idStart);
            document.getElementsByClassName(this.prefix + 'rateFloatValue', li).each(function(input) {
                Event.observe(input, "keyup", function(e){ NumericFilter(this) });
                input.id = input.id.replace(idStartRegexp, idStart + 'new' + newId);
                Event.observe(input.up().down('label'), 'click', function(e) { Event.stop(e); input.focus(); });
            }.bind(this));
			
            document.getElementsByClassName('UnitConventer_Root', li).each(function(el) {
                el.id = el.id.replace(idStartRegexp, idStart + 'new' + newId);
            }.bind(this));
			
			var startHi = li.down('.weightRangeStart').down('.UnitConventer_HiValue');
            var startLo = li.down('.weightRangeStart').down('.UnitConventer_LoValue');
            var endHi = li.down('.weightRangeEnd').down('.UnitConventer_HiValue');
            var endLo = li.down('.weightRangeEnd').down('.UnitConventer_LoValue');
			
			startHi.id = startHi.id.replace(idStartRegexp, idStart + 'new' + newId)
            Event.observe(startHi.up(2).down('label'), 'click', function(e) { Event.stop(e); startHi.focus(); });
            startLo.id = startLo.id.replace(idStartRegexp, idStart + 'new' + newId)
			
            endHi.id = endHi.id.replace(idStartRegexp, idStart + 'new' + newId)
            endLo.id = endLo.id.replace(idStartRegexp, idStart + 'new' + newId)	
			
			var newForm = Backend.DeliveryZone.ShippingRate.prototype.getInstance(li, rate);
            
            this.nodes.weightRangeStart.value = '0';
            this.nodes.weightRangeStartHiValue.value = '0';
            this.nodes.weightRangeStartLoValue.value = '0';
			
            this.nodes.weightRangeEnd.value = '0';
            this.nodes.weightRangeEndHiValue.value = '0';
            this.nodes.weightRangeEndLoValue.value = '0';
            
			this.nodes.subtotalRangeStart.value = '0';
            this.nodes.subtotalRangeEnd.value = '0';
            this.nodes.flatCharge.value = '0';
            this.nodes.perItemCharge.value = '0';
            this.nodes.subtotalPercentCharge.value = '0';
            this.nodes.perKgCharge.value = '0';
			
			
            Backend.DeliveryZone.ShippingRate.prototype.newRateLastId++;
        }
        else
        {
            ActiveForm.prototype.setErrorMessages(this.nodes.root.up('form'), response.errors);
        }
    },
    
    cancel: function()
    {
        if(!this.rate.ID)
        {
            this.hideNewForm();
        }
    },
    
    remove: function()
    {
        if(confirm(Backend.DeliveryZone.ShippingRate.prototype.Messages.confirmDelete))
        {
            if(!this.rate.ID.match(/^new/))
            {
                new LiveCart.AjaxRequest(Backend.DeliveryZone.ShippingService.prototype.Links.deleteRate + '/' + this.rate.ID);    
            }
            
			var rates = this.nodes.root.up(".activeList")
			var service = this.nodes.root.up('li');
			
            Element.remove(this.nodes.root);
			
            service.down('.ratesCount').innerHTML = rates.getElementsByTagName('li').length;
        }
    }
}




Backend.DeliveryZone.TaxRate = Class.create();
Backend.DeliveryZone.TaxRate.prototype = 
{
    Links: {},
    Messages: {},
 
    Callbacks: {
        'beforeDelete': function(li) 
        {
            if(confirm(Backend.DeliveryZone.TaxRate.prototype.Messages.confirmDelete))
            {
                return Backend.DeliveryZone.TaxRate.prototype.Links.remove + "/" + this.getRecordId(li);
            }
        },
        'afterDelete': function(li, response)
        {
            var response = eval('(' + response + ')')
            
            if('success' == response.status) {
                if(Backend.DeliveryZone.TaxRate.prototype.instances[$(Backend.DeliveryZone.TaxRate.prototype.prefix + "new_taxRate_" + this.getRecordId(li, 2) + "_form").down('form')])
                {
                    var taxForm = Backend.DeliveryZone.TaxRate.prototype.getInstance($(Backend.DeliveryZone.TaxRate.prototype.prefix + "new_taxRate_" + this.getRecordId(li, 2) + "_form").down('form'));
                    taxForm.addTaxOption(response.tax.ID, response.tax.name);
                }
                this.remove(li);     
                
            }
        },
        
        beforeEdit:     function(li)
        {
            if(this.isContainerEmpty(li, 'edit')) 
            {
                return Backend.DeliveryZone.TaxRate.prototype.Links.edit + '/' + this.getRecordId(li)
            }
            else 
            {
                var newRateForm = $("taxRate_" + this.getRecordId(li, 2) + '_');
                if(newRateForm.up().style.display == 'block')
                {
                    Backend.DeliveryZone.TaxRate.prototype.getInstance(newRateForm).hideNewForm();
                }

                this.toggleContainer(li, 'edit');
            }
        },
       
        afterEdit:      function(li, response)
        {
            var newRateForm = $("taxRate_" + this.getRecordId(li, 2) + '_');
            if(newRateForm.up().style.display == 'block')
            {
                Backend.DeliveryZone.TaxRate.prototype.getInstance(newRateForm).hideNewForm();
            }
            this.getContainer(li, 'edit').update(response);
            this.toggleContainer(li, 'edit');
        }
    },
     
    instances: {},
    
    prefix: 'taxRate_',
    
    initialize: function(root, rate)
    {
        try
        {
            this.rate = rate;
            this.deliveryZoneId = this.rate.DeliveryZone ? this.rate.DeliveryZone.ID : '';
            this.findUsedNodes(root);
            this.bindEvents();
            this.ratesActiveList = ActiveList.prototype.getInstance(this.nodes.ratesList);

            Form.State.backup(this.nodes.form);
        }
        catch(e)
        {
            console.info(e);
        }
    },
        
    getInstance: function(rootNode, rate)
    {
        var rootId = $(rootNode).id;
        if(!Backend.DeliveryZone.TaxRate.prototype.instances[rootId])
        {
            Backend.DeliveryZone.TaxRate.prototype.instances[rootId] = new Backend.DeliveryZone.TaxRate(rootId, rate);
        }
        
        return Backend.DeliveryZone.TaxRate.prototype.instances[rootId];
    },
    
    addTaxOption: function(id, name)
    {
        var formBackupId = this.nodes.form.backupId;
        Form.State.backups[formBackupId]['taxID'][0]['options'][id] = name;
        this.nodes.taxID.options[this.nodes.taxID.options.length] = new Option(name, id);
    },
    
    removeTaxOption: function(id)
    {
        try
        {
            $A(this.nodes.taxID.options).each(function(option)
            {
                if(option.value == id)
                {
                    var formBackupId = this.nodes.form.backupId;
                    delete Form.State.backups[formBackupId]['taxID'][0]['options'][id];
                    Element.remove(option);
                    throw new Error('Found');
                }
            }.bind(this));
        }
        catch(e) { }
    },
    
    findUsedNodes: function(root)
    {
        this.nodes = {};
        
        this.nodes.root = $(root);
        this.nodes.form = this.nodes.root;

        this.nodes.controls = this.nodes.root.down('.' + this.prefix + 'controls');
        this.nodes.save = this.nodes.controls.down('.' + this.prefix + 'save');
        this.nodes.cancel = this.nodes.controls.down('.' + this.prefix + 'cancel');
        
        this.nodes.rate = this.nodes.root.down('.' + this.prefix  + 'rate');
        this.nodes.taxID = this.nodes.root.down('.' + this.prefix + 'taxID');
        this.nodes.deliveryZoneID = this.nodes.root.down('.' + this.prefix + 'deliveryZoneID');
        this.nodes.taxRateID = this.nodes.root.down('.' + this.prefix + 'taxRateID');
        
        this.nodes.ratesList = $(this.prefix + "taxRatesList_" + this.deliveryZoneId)
        
        if(!this.rate.ID)
        {
            this.nodes.menu = $(this.prefix + "menu_" + this.deliveryZoneId);
            this.nodes.menuCancelLink = $(this.prefix + "new_" + this.deliveryZoneId + "_cancel");
            this.nodes.menuShowLink = $(this.prefix + "new_" + this.deliveryZoneId + "_show");
            this.nodes.menuForm = $(this.prefix + "new_taxRate_" + this.deliveryZoneId + "_form");
        }
    },
    
    bindEvents: function()
    {
       Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
      
       Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
       if(!this.rate.ID)
       {
           Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
       }
       
       $A(this.nodes.rangeTypes).each(function(radio)
       {
           Event.observe(radio, 'click', function(e) { this.rangeTypeChanged(); }.bind(this));
       }.bind(this));
    },
    
    showNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuCancelLink]);
        ActiveForm.prototype.showNewItemForm(this.nodes.menuShowLink, this.nodes.menuForm); 
        ActiveList.prototype.collapseAll();
    },
    
    hideNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuShowLink]);
        ActiveForm.prototype.hideNewItemForm(this.nodes.menuCancelLink, this.nodes.menuForm); 
        
        Form.State.restore(this.nodes.form);
    },
    
    save: function(event)
    {
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        var action = this.rate.ID 
            ? Backend.DeliveryZone.TaxRate.prototype.Links.update
            : Backend.DeliveryZone.TaxRate.prototype.Links.create;
            
        new LiveCart.AjaxRequest(
            action + '?' + Form.serialize(this.nodes.form),
            false,
            function(response) 
            { 
                var response = eval("(" + response.responseText + ")");
                this.afterSave(response);     
            }.bind(this)
        );
    },
    
    afterSave: function(response)
    {
        if(response.status == 'success')
        {
            if(!this.rate.ID)
            {
                var li = this.ratesActiveList.addRecord(response.rate.ID, '<span class="' + this.prefix + 'taxRatesList_title">' + response.rate.Tax.name + '</span>');
                this.removeTaxOption(this.nodes.taxID.value);
                this.hideNewForm();
            }
            else
            {
                Form.State.backup(this.nodes.form);
                this.ratesActiveList.toggleContainer(this.nodes.root.up('li'), 'edit');
            }
        }
        else
        {
            ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors);
        }
    },
    
    cancel: function()
    {
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        if(!this.rate.ID)
        {
            this.hideNewForm();
        }
        else
        {
            this.ratesActiveList.toggleContainerOff(this.nodes.root.up('.activeList_editContainer'));
            Form.State.restore(this.nodes.form);
        }
    }
}



/***************************************************
 * library\Debug.js
 ***************************************************/

TimeTrack = Class.create();
TimeTrack.prototype = 
{
  	start: false,
  	
	initialize: function()
  	{
		this.start = this.dateToSec(new Date());    
	},
	
	track: function(pointName)
	{
	  	var curr = this.dateToSec(new Date());
	 	console.log(pointName + ' - ' + (curr - this.start));
		this.start = curr; 	
	},
	
	dateToSec: function(dat)
	{
		var ret = (dat.getHours() * 3600) + (dat.getMinutes() * 60) + dat.getSeconds() + '.' + dat.getMilliseconds();
	  	return ret;
	}
	
}

function print_r(input, _indent)
{
    if(typeof(_indent) == 'string') {
        var indent = _indent + '    ';
        var paren_indent = _indent + '  ';
    } else {
        var indent = '    ';
        var paren_indent = '';
    }
    switch(typeof(input)) {
        case 'boolean':
            var output = (input ? 'true' : 'false') + "\n";
            break;
        case 'object':
            if ( input===null ) {
                var output = "null\n";
                break;
            }
            var output = ((input.reverse) ? 'Array' : 'Object') + " (\n";
            for(var i in input) {
                output += indent + "[" + i + "] => " + print_r(input[i], indent);
            }
            output += paren_indent + ")\n";
            break;
        case 'number':
        case 'string':
        default:
            var output = "" + input  + "\n";
    }
    return output;
}

function addlog(info)
{
	document.getElementById('log').innerHTML += info + '<br />';
}


/***************************************************
 * library\dhtmlHistory\dhtmlHistory.js
 ***************************************************/

/** 
   Copyright (c) 2005, Brad Neuberg, bkn3@columbia.edu
   http://codinginparadise.org
   
   Permission is hereby granted, free of charge, to any person obtaining 
   a copy of this software and associated documentation files (the "Software"), 
   to deal in the Software without restriction, including without limitation 
   the rights to use, copy, modify, merge, publish, distribute, sublicense, 
   and/or sell copies of the Software, and to permit persons to whom the 
   Software is furnished to do so, subject to the following conditions:
   
   The above copyright notice and this permission notice shall be 
   included in all copies or substantial portions of the Software.
   
   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, 
   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES 
   OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. 
   IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY 
   CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT 
   OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR 
   THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/** An object that provides DHTML history, history data, and bookmarking 
    for AJAX applications. */
window.dhtmlHistory = {
   /** Initializes our DHTML history. You should
       call this after the page is finished loading. */
   /** public */ initialize: function() {
      // only Internet Explorer needs to be explicitly initialized;
      // other browsers don't have its particular behaviors.
      // Basicly, IE doesn't autofill form data until the page
      // is finished loading, which means historyStorage won't
      // work until onload has been fired.
      if (this.isInternetExplorer() == false) {
         return;
      }
         
      // if this is the first time this page has loaded...
      if (historyStorage.hasKey("DhtmlHistory_pageLoaded") == false) {
         this.fireOnNewListener = false;
         this.firstLoad = true;
         historyStorage.put("DhtmlHistory_pageLoaded", true);
      }
      // else if this is a fake onload event
      else {
         this.fireOnNewListener = true;
         this.firstLoad = false;   
      }
   },
             
   /** Adds a history change listener. Note that
       only one listener is supported at this
       time. */
   /** public */ addListener: function(callback) {
      this.listener = callback;
      
      // if the page was just loaded and we
      // should not ignore it, fire an event
      // to our new listener now
      if (this.fireOnNewListener == true) {
         //this.fireHistoryEvent(this.currentLocation);
         //this.fireOnNewListener = false;
      }
   },
   
	/**
	 * @author Integry Systems
	 */
	handleBookmark: function()
	{
		hash = window.location.hash.substr(1);
		if(window.historyStorage.hasKey(hash))
		{
			this.fireHistoryEvent(hash);
		}
		else
		{
			Backend.ajaxNav.handle(hash);
		}
	},
   
   /** public */ add: function(newLocation, historyData) {
      // most browsers require that we wait a certain amount of time before changing the
      // location, such as 200 milliseconds; rather than forcing external callers to use
      // window.setTimeout to account for this to prevent bugs, we internally handle this
      // detail by using a 'currentWaitTime' variable and have requests wait in line
      var self = this;
      var addImpl = function() {
         // indicate that the current wait time is now less
         if (self.currentWaitTime > 0)
            self.currentWaitTime = self.currentWaitTime - self.WAIT_TIME;
            
         // remove any leading hash symbols on newLocation
         newLocation = self.removeHash(newLocation);
         
         // IE has a strange bug; if the newLocation
         // is the same as _any_ preexisting id in the
         // document, then the history action gets recorded
         // twice; throw a programmer exception if there is
         // an element with this ID
         var idCheck = document.getElementById(newLocation);
         if (idCheck != undefined || idCheck != null) {
            var message = 
               "Exception: History locations can not have "
               + "the same value as _any_ id's "
               + "that might be in the document, "
               + "due to a bug in Internet "
               + "Explorer; please ask the "
               + "developer to choose a history "
               + "location that does not match "
               + "any HTML id's in this "
               + "document. The following ID "
               + "is already taken and can not "
               + "be a location: " 
               + newLocation;
               
            throw message; 
         }
         
         // store the history data into history storage
         historyStorage.put(newLocation, historyData);
         
         // indicate to the browser to ignore this upcomming 
         // location change
         self.ignoreLocationChange = true;
 
         // indicate to IE that this is an atomic location change
         // block
         this.ieAtomicLocationChange = true;
                 
         // save this as our current location
         self.currentLocation = newLocation;
         
         // change the browser location
         window.location.hash = newLocation;
         
         // change the hidden iframe's location if on IE
         if (self.isInternetExplorer())
            self.iframe.src = "javascript/library/dhtmlhistory/history.php?" + newLocation;
            
         // end of atomic location change block
         // for IE
         this.ieAtomicLocationChange = false;
      };

      // now execute this add request after waiting a certain amount of time, so as to
      // queue up requests
      window.setTimeout(addImpl, this.currentWaitTime);
   
      // indicate that the next request will have to wait for awhile
      this.currentWaitTime = this.currentWaitTime + this.WAIT_TIME;
   },
   
   /** public */ isFirstLoad: function() {
      if (this.firstLoad == true) {
         return true;
      }
      else {
         return false;
      }
   },
   
   /** public */ isInternational: function() {
      return false;
   },
   
   /** public */ getVersion: function() {
      return "0.05";
   },
   
   /** Gets the current hash value that is in the browser's
       location bar, removing leading # symbols if they are present. */
   /** public */ getCurrentLocation: function() {
      var currentLocation = this.removeHash(window.location.hash);
         
      return currentLocation;
   },
   
   
   
   
   
   /** Our current hash location, without the "#" symbol. */
   /** private */ currentLocation: null,
   
   /** Our history change listener. */
   /** private */ listener: null,
   
   /** A hidden IFrame we use in Internet Explorer to detect history
       changes. */
   /** private */ iframe: null,
   
   /** Indicates to the browser whether to ignore location changes. */
   /** private */ ignoreLocationChange: null,
 
   /** The amount of time in milliseconds that we should wait between add requests. 
       Firefox is okay with 200 ms, but Internet Explorer needs 400. */
   /** private */ WAIT_TIME: 200,

   /** The amount of time in milliseconds an add request has to wait in line before being
       run on a window.setTimeout. */
   /** private */ currentWaitTime: 0,
   
   /** A flag that indicates that we should fire a history change event
       when we are ready, i.e. after we are initialized and
       we have a history change listener. This is needed due to 
       an edge case in browsers other than Internet Explorer; if
       you leave a page entirely then return, we must fire this
       as a history change event. Unfortunately, we have lost
       all references to listeners from earlier, because JavaScript
       clears out. */
   /** private */ fireOnNewListener: null,
   
   /** A variable that indicates whether this is the first time
       this page has been loaded. If you go to a web page, leave
       it for another one, and then return, the page's onload
       listener fires again. We need a way to differentiate
       between the first page load and subsequent ones.
       This variable works hand in hand with the pageLoaded
       variable we store into historyStorage.*/
   /** private */ firstLoad: null,
   
   /** A variable to handle an important edge case in Internet
       Explorer. In IE, if a user manually types an address into
       their browser's location bar, we must intercept this by
       continiously checking the location bar with an timer 
       interval. However, if we manually change the location
       bar ourselves programmatically, when using our hidden
       iframe, we need to ignore these changes. Unfortunately,
       these changes are not atomic, so we surround them with
       the variable 'ieAtomicLocationChange', that if true,
       means we are programmatically setting the location and
       should ignore this atomic chunked change. */
   /** private */ ieAtomicLocationChange: null,          
   
   /** Creates the DHTML history infrastructure. */
   /** private */ create: function() {
      // get our initial location
      var initialHash = this.getCurrentLocation();
      
      // save this as our current location
      this.currentLocation = initialHash;
      
      // write out a hidden iframe for IE and
      // set the amount of time to wait between add() requests
      if (this.isInternetExplorer()) {
         document.write("<iframe style='border: 0px; width: 200px;"
                               + "height: 100px; position: absolute; top: 0px; "
                               + "left: 500px; z-index: 50000; visibility: visible; display: none;' "
                               + "name='DhtmlHistoryFrame' id='DhtmlHistoryFrame'  onload='window.dhtmlHistory.frameLoad(this);' "
                               + "src='javascript/library/dhtmlhistory/history.php?" + initialHash + "'>"
                               + "</iframe>");
         // wait 400 milliseconds between history
         // updates on IE, versus 200 on Firefox
         this.WAIT_TIME = 400;
      }
      
      // add an unload listener for the page; this is
      // needed for Firefox 1.5+ because this browser caches all
      // dynamic updates to the page, which can break some of our 
      // logic related to testing whether this is the first instance
      // a page has loaded or whether it is being pulled from the cache
      var self = this;
      window.onunload = function() {
         self.firstLoad = null;
      };
      
      // determine if this is our first page load;
      // for Internet Explorer, we do this in 
      // this.iframeLoaded(), which is fired on
      // page load. We do it there because
      // we have no historyStorage at this point
      // in IE, which only exists after the page
      // is finished loading for that browser
      if (this.isInternetExplorer() == false) {
         if (historyStorage.hasKey("DhtmlHistory_pageLoaded") == false) {
            this.ignoreLocationChange = true;
            this.firstLoad = true;
            historyStorage.put("DhtmlHistory_pageLoaded", true);
         }
         else {
            // indicate that we want to pay attention
            // to this location change
            this.ignoreLocationChange = false;
            // For browser's other than IE, fire
            // a history change event; on IE,
            // the event will be thrown automatically
            // when it's hidden iframe reloads
            // on page load.
            // Unfortunately, we don't have any 
            // listeners yet; indicate that we want
            // to fire an event when a listener
            // is added.
            this.fireOnNewListener = true;
         }
      }
      else { // Internet Explorer
         // the iframe will get loaded on page
         // load, and we want to ignore this fact
         this.ignoreLocationChange = true;
      }
      
      if (this.isInternetExplorer()) {
            this.iframe = document.getElementById("DhtmlHistoryFrame");
      }                                                              

      // other browsers can use a location handler that checks
      // at regular intervals as their primary mechanism;
      // we use it for Internet Explorer as well to handle
      // an important edge case; see checkLocation() for
      // details
      var self = this;
      var locationHandler = function() {
         self.checkLocation();
      };
      setInterval(locationHandler, 1000);
   },
   
   /** Notify the listener of new history changes. */
   /** private */ fireHistoryEvent: function(newHash) {
      // extract the value from our history storage for
      // this hash
      var historyData = historyStorage.get(newHash);

      // call our listener      
	  if (this.listener)
	  {
		  this.listener(newHash, historyData);  
	  }	  
   },
   
      /**
       * @author Integry Systems
       */
	  frameLoad: function(frame)
      {
		  var hash = window.frames[frame.id].document.body.firstChild.nodeValue;
		  if (window.location.hash == '#' + hash)
		  {
  		  	  return false;  
		  }
//		  addlog(hash);
		  window.dhtmlHistory.fireHistoryEvent(hash);
	  },
	  
   /** Sees if the browsers has changed location.  This is the primary history mechanism
       for Firefox. For Internet Explorer, we use this to handle an important edge case:
       if a user manually types in a new hash value into their Internet Explorer location
       bar and press enter, we want to intercept this and notify any history listener. */
   /** private */ checkLocation: function() {
      // ignore any location changes that we made ourselves
      // for browsers other than Internet Explorer
      if (this.isInternetExplorer() == false
         && this.ignoreLocationChange == true) {
         this.ignoreLocationChange = false;
         return;
      }
      
      // if we are dealing with Internet Explorer
      // and we are in the middle of making a location
      // change from an iframe, ignore it
      if (this.isInternetExplorer() == false
          && this.ieAtomicLocationChange == true) {
         return;
      }
           
      // get hash location
      var hash = this.getCurrentLocation();
      
      // see if there has been a change
      if (hash == this.currentLocation)
         return;
         
      // on Internet Explorer, we need to intercept users manually
      // entering locations into the browser; we do this by comparing
      // the browsers location against the iframes location; if they
      // differ, we are dealing with a manual event and need to
      // place it inside our history, otherwise we can return
      this.ieAtomicLocationChange = true;
      
      if (this.isInternetExplorer()
          && this.getIFrameHash() != hash) {
         this.iframe.src = "javascript/library/dhtmlhistory/history.php?" + hash;
      }
      else if (this.isInternetExplorer()) {
         // the iframe is unchanged
         return;
      }
         
      // save this new location
      this.currentLocation = hash;
      
      this.ieAtomicLocationChange = false;
      
      // notify listeners of the change
      this.fireHistoryEvent(hash);
   },  

   /** Gets the current location of the hidden IFrames
       that is stored as history. For Internet Explorer. */
   /** private */ getIFrameHash: function() {
      // get the new location
      var historyFrame = document.getElementById("DhtmlHistoryFrame");
      var doc = historyFrame.contentWindow.document;
      var hash = new String(doc.location.search);

      if (hash.length == 1 && hash.charAt(0) == "?")
         hash = "";
      else if (hash.length >= 2 && hash.charAt(0) == "?")
         hash = hash.substring(1); 
    
    
      return hash;
   },          
   
   /** Removes any leading hash that might be on a location. */
   /** private */ removeHash: function(hashValue) {
      if (hashValue == null || hashValue == undefined)
         return null;
      else if (hashValue == "")
         return "";
      else if (hashValue.length == 1 && hashValue.charAt(0) == "#")
         return "";
      else if (hashValue.length > 1 && hashValue.charAt(0) == "#")
         return hashValue.substring(1);
      else
         return hashValue;     
   },          
   
   /** For IE, says when the hidden iframe has finished loading. */
   /** private */ iframeLoaded: function(newLocation) {
      // ignore any location changes that we made ourselves
      if (this.ignoreLocationChange == true) {
         this.ignoreLocationChange = false;
         return;
      }
      
      // get the new location
      var hash = new String(newLocation.search);
      if (hash.length == 1 && hash.charAt(0) == "?")
         hash = "";
      else if (hash.length >= 2 && hash.charAt(0) == "?")
         hash = hash.substring(1);
      
      // move to this location in the browser location bar
      // if we are not dealing with a page load event
      if (this.pageLoadEvent != true) {
         window.location.hash = hash;
      }

      // notify listeners of the change
      this.fireHistoryEvent(hash);
   },
   
   /** Determines if this is Internet Explorer. */
   /** private */ isInternetExplorer: function() {
      var userAgent = navigator.userAgent.toLowerCase();
      if (document.all && userAgent.indexOf('msie')!=-1) {
         return true;
      }
      else {
         return false;
      }
   }
};












/** An object that uses a hidden form to store history state 
    across page loads. The chief mechanism for doing so is using
    the fact that browser's save the text in form data for the
    life of the browser and cache, which means the text is still
    there when the user navigates back to the page. See
    http://codinginparadise.org/weblog/2005/08/ajax-tutorial-saving-session-across.html
    for full details. */
window.historyStorage = {
   /** If true, we are debugging and show the storage textfield. */
   /** public */ debugging: false,
   
   /** Our hash of key name/values. */
   /** private */ storageHash: {},
   
   /** If true, we have loaded our hash table out of the storage form. */
   /** private */ hashLoaded: false, 
   
   /** public */ put: function(key, value) {
       this.assertValidKey(key);
       
       // if we already have a value for this,
       // remove the value before adding the
       // new one
       if (this.hasKey(key)) {
         this.remove(key);
       }
       
       if (!this.storageHash)
       {
            this.storageHash = {};
       }
       
       // store this new key       
       this.storageHash[key] = value;
       
       // save and serialize the hashtable into the form
       this.saveHashTable(); 
   },
   
   /** public */ get: function(key) {
      this.assertValidKey(key);
      
      // make sure the hash table has been loaded
      // from the form
      this.loadHashTable();
      
      var value = this.storageHash[key];

      if (value == undefined)
         return null;
      else
         return value; 
   },
   
   /** public */ remove: function(key) {
      this.assertValidKey(key);
      
      // make sure the hash table has been loaded
      // from the form
      this.loadHashTable();
      
      // delete the value
      delete this.storageHash[key];
      
      // serialize and save the hash table into the 
      // form
      this.saveHashTable();
   },
   
   /** Clears out all saved data. */
   /** public */ reset: function() {
      this.storageField.value = "";
      this.storageHash = new Object();
   },
   
   /** public */ hasKey: function(key) {
      this.assertValidKey(key);
      
      // make sure the hash table has been loaded
      // from the form
      this.loadHashTable();
      
      if (!this.storageHash || !this.storageHash[key])
         return false;
      else
         return true;
   },
   
   /** Determines whether the key given is valid;
       keys can only have letters, numbers, the dash,
       underscore, spaces, or one of the 
       following characters:
       !@#$%^&*()+=:;,./?|\~{}[] */
   /** public */ isValidKey: function(key) {
      // allow all strings, since we don't use XML serialization
      // format anymore
      return (typeof key == "string");
      
      /*
      if (typeof key != "string")
         key = key.toString();
      
      
      var matcher = 
         /^[a-zA-Z0-9_ \!\@\#\$\%\^\&\*\(\)\+\=\:\;\,\.\/\?\|\\\~\{\}\[\]]*$/;
                     
      return matcher.test(key);*/
   },
   
   
   
   
   /** A reference to our textarea field. */
   /** private */ storageField: null,
   
   /** private */ init: function() {
      // write a hidden form into the page
      var styleValue = "position: absolute; top: -1000px; left: -1000px;";
      if (this.debugging == true) {
         styleValue = "width: 30em; height: 30em;";
      }   
      
      var newContent =
         "<form id='historyStorageForm' " 
               + "method='GET' "
               + "style='" + styleValue + "'>"
            + "<textarea id='historyStorageField' "
                      + "style='" + styleValue + "'"
                              + "left: -1000px;' "
                      + "name='historyStorageField'></textarea>"
         + "</form>";
      document.write(newContent);
      
      this.storageField = document.getElementById("historyStorageField");
   },
   
   /** Asserts that a key is valid, throwing
       an exception if it is not. */
   /** private */ assertValidKey: function(key) {
      if (this.isValidKey(key) == false) {
         throw "Please provide a valid key for "
               + "window.historyStorage, key= "
               + key;
       }
   },
   
   /** Loads the hash table up from the form. */
   /** private */ loadHashTable: function() {
      if (this.hashLoaded == false) {
         // get the hash table as a serialized
         // string
         var serializedHashTable = this.storageField.value;
         
         if (serializedHashTable != "" &&
             serializedHashTable != null) {
            // destringify the content back into a 
            // real JavaScript object
            this.storageHash = eval('(' + serializedHashTable + ')');  
         }
         
         this.hashLoaded = true;
      }
   },
   
   /** Saves the hash table into the form. */
   /** private */ saveHashTable: function() {
      this.loadHashTable();
      
      // serialized the hash table
      var serializedHashTable = Object.toJSON(this.storageHash);
      
      // save this value
      this.storageField.value = serializedHashTable;
   }   
};

 console.info('All javascript files were glued together successfully in following order:\n  prototype.js\n  effects.js\n  builder.js\n  controls.js\n  slider.js\n  dragdrop.js\n  Backend.js\n  livecart.js\n  KeyboardEvent.js\n  dhtmlXCommon.js\n  dhtmlXTree.js\n  Validator.js\n  ActiveForm.js\n  State.js\n  TabControl.js\n  ActiveList.js\n  DeliveryZone.js\n  Debug.js\n  dhtmlHistory.js')
