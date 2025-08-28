// Extend Date Object
Date.prototype.today = function () {
	return this.getFullYear() + "-" +(((this.getMonth()+1) < 10)?"0":"") + (this.getMonth()+1) + "-" + ((this.getDate() < 10)?"0":"") + this.getDate();
};
Date.prototype.timeNow = function () {
	return ((this.getHours() < 10)?"0":"") + this.getHours() + ":" + ((this.getMinutes() < 10)?"0":"") + this.getMinutes() + ":" + ((this.getSeconds() < 10)?"0":"") + this.getSeconds();
};

// Extend jQuery
if (typeof $ !== 'undefined') {
    jQuery.expr[':'].contains = function(a, i, m){
        return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0
    };

    // Extend Timeago
    if (typeof $.timeago !== 'undefined') {

        // Enable Timeago Future Dates
        $.timeago.settings.allowFuture = true;
        $.extend($.timeago.settings.strings, {
            prefixFromNow: 'in',
            suffixFromNow: '',
        });
    } else {
        console.warn("jQuery Timeago is not loaded");
    }
} else {
    alert("jQuery is not loaded");
}

// Open IndexedDB
let __dbPromise = null;
function openIDB() {
    if (__dbPromise) return __dbPromise;

    __dbPromise = new Promise((resolve, reject) => {
        const request = indexedDB.open('StorageAPI', 1);

        request.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('kv')) {
                db.createObjectStore('kv');
            }
        };

        request.onsuccess = () => {
            const db = request.result;
            db.onversionchange = () => db.close();
            resolve(db);
        };

        request.onerror = () => reject(request.error);
    });

    return __dbPromise;
}

// Builder Class
class Builder {
    Search = null;
    Helper = null;
    Toast = null;
    Message = null;
    Notification = null;
    #count = 0;

    constructor(){

        // Create Utilities
        this.Locale = this.Utility('locale');
        this.Parser = this.Utility('parser');
        this.Avatar = this.Utility('avatar');
        this.Status = this.Utility('status');
        this.Rating = this.Utility('rating');
        this.Priority = this.Utility('priority');
        this.Option = this.Utility('option');
        this.Search = this.Utility('search');
        this.Helper = this.Utility('helper');
        this.Toast = this.Utility('toast');
        this.Message = this.Utility('message');
        this.Notification = this.Utility('notification');
        this.Storage = this.Utility('storage');
    }

    count(){
        this.#count++;
        return this.#count;
    }

    UtilityClass = class  {

        _builder = null;
        _id = null;

        constructor(builder){

            // Set Builder
            this._builder = builder;

            // Generate Incremental ID
            this._id = this._builder.count();
        }
    }

    ComponentClass = class extends this.UtilityClass {

        _bootstrap = null;
        _selector = null;
        _options = {};
        _callback = null;
        _component = null;
        _properties = {};
        _counter = 0;

        constructor(builder, param1 = null, param2 = null, param3 = null){

            // Call Parent
            super(builder);

            // Set Self
            const self = this;

            // Set selector, options, and callback
            [param1, param2, param3].forEach(param => {
                if(param !== null){
                    if (typeof param === 'string' || param instanceof jQuery) {
                        if(typeof param === 'string'){
                            param = $(param);
                        }
                        self._selector = param;
                    } else if (typeof param === 'object') {
                        self._options = param;
                    } else if (typeof param === 'function') {
                        self._callback = param;
                    }
                }
            });

            // Init Component
            this._init();

            // Configure Options
            this.config(this._options);

            // Create Component
            var component = this._create()

            // Check if component is a promise
            if (component instanceof Promise) {
                component.then(function (result) {
                    self._load();
                }).catch(function (error) {
                    console.error('Error creating component:', error);
                });
            } else {
                this._load();
            }

            // Execute Callback
            if(typeof this._callback === 'function'){
                setTimeout(() => this._callback(this, this._component), 0);
            }

            // Timeout
            setTimeout(() => this._timeout(), 0);

            // Insert Component
            this._insert();
        }

        _init(){}

        _create(){}

        _load(){}

        _timeout(){}

        _config(name,options){
            if(typeof this._properties[name] !== 'undefined'){
                switch(name){
                    default:
                        this._properties[name] = options;
                        break;
                }
            }
        }

        _insert(){
            // Check if Selector is Set
            if(this._component && this._selector){

                // Append to Selector
                this.appendTo(this._selector);
            }
        }

        _count(){
            this._counter++;
            return this._counter;
        }

        component(){
            return this._component;
        }

        config(options = null){

            // Check if Options is Set
            if(options == null){
                options = this._options;
            }

            // Configure Options
            for(const [key, value] of Object.entries(options)){
                if(typeof this._properties[key] !== 'undefined'){
                    switch(key){
                        case"callback":
                        case"properties":
                            for(const [k, v] of Object.entries(value)){
                                if(typeof this._properties[key][k] !== 'undefined'){
                                    this._properties[key][k] = v;
                                }
                            }
                            break;
                        case"class":
                            for(const [section, classes] of Object.entries(value)){
                                if(this._properties[key][section] != null){
                                    this._properties[key][section] += ' ' + classes;
                                } else {
                                    this._properties[key][section] = classes;
                                }
                            }
                            break;
                        default:
                            if(typeof value === 'object'){
                                this._config(key,value);
                            } else {
                                this._properties[key] = value;
                            }
                            break;
                    }
                }
            }

            // Return Object
            return this;
        }

        appendTo(object){

            // Append Object To
            this._component.appendTo(object);

            // Return Object
            return this;
        }

        prependTo(object){

            // Prepend Object To
            this._component.prependTo(object);

            // Return Object
            return this;
        }

        append(object){

            // Append Object
            this._component.append(object);

            // Return Object
            return this;
        }

        prepend(object){

            // Prepend Object
            this._component.prepend(object);

            // Return Object
            return this;
        }

        text(){

            // Return Object
            return this._component.text();
        }

        html(){

            // Return Object
            return this._component.html();
        }

        outerHTML(){

            // Return Object
            return this._component[0].outerHTML;
        }

        show(){

            // Show Object
            this._component.show();

            // Return Object
            return this;
        }

        hide(){

            // Hide Object
            this._component.hide();

            // Return Object
            return this;
        }
    }

    InputClass = class extends this.ComponentClass {

        constructor(builder, param1 = null, param2 = null, param3 = null){

            // Call Parent
            super(builder, param1, param2, param3);
        }

        _init(){
            this._properties = {
                name: null,
                label: null,
                color: null,
                icon: null,
                value: null,
                placeholder: null,
                parent: null,
                required: false,
                style: 'group', // 'group' or 'floating'
                // target: null,
                // step: null,
                // min: 0,
                // max: 100,
                class: {
                    component: null,
                    input: null,
                    label: null,
                },
                callback: {
                    onChange: null,
                    condition: null,
                    format: null,
                }
            };
        }

        _extend(){}

        _timeout(){

            // Set Search
            this._builder.Search.set(this._component);
        }

        _input(){

            // Create Input
            return $(document.createElement('input')).attr({
                'class': 'form-control',
                'autocomplete': 'off',
                'type': 'text',
                'value': this._properties.value,
            });
        }

        _create(){

            // Set Self
            const self = this;

            // Set ID
            let id = this._builder.count();

            // Create Component
            this._component = $(document.createElement('div')).attr({
                'id': this._properties.name+'-' + id,
            });

            // Set Component ID
            this._component.id = this._component.attr('id');

            // Set Component Class
            if(this._properties.class.component){
                this._component.addClass(this._properties.class.component);
            }

            // Create Label
            this._component.label = $(document.createElement('label')).attr({
                // 'class': 'input-group-text',
                'for': this._component.id + '-input',
            }).text(this._properties.label).appendTo(this._component);

            // Set Label Class
            if(this._properties.class.label){
                this._component.label.addClass(this._properties.class.label);
            }

            // Create Icon
            if(this._properties.icon !== null){
                this._component.label.icon = $(document.createElement('i')).addClass('bi bi-' + this._properties.icon).prependTo(this._component.label);

                // Add Margin if Label is not Empty
                if(this._properties.label){
                    this._component.label.icon.addClass('me-1');
                }
            }

            // Create Labels for Valid, Invalid, and Feedback
            this._component.valid = $(document.createElement('div')).attr({
                'class': 'valid-tooltip',
            });
            this._component.invalid = $(document.createElement('div')).attr({
                'class': 'invalid-tooltip',
            });
            this._component.help = $(document.createElement('div')).attr({
                'class': 'form-text',
            });

            // Create Input
            this._component.input = this._input();
            this._component.input.attr({
                'id': this._component.id + '-input',
                'name': this._properties.name,
                'placeholder': this._properties.placeholder,
            }).appendTo(this._component);

            // Set Input Required
            if(this._properties.required){
                this._component.input.attr('required', 'required');
            }

            // Set Input Class
            if(typeof this._component.input !== "undefined" && this._properties.class.input){
                this._component.input.addClass(this._properties.class.input);
            }

            // Check Style
            switch(this._properties.style){
                case'floating':
                    // Create Floating Input
                    this._component.addClass('form-floating');
                    this._component.input.insertBefore(this._component.label);
                    break;
                case'group':
                    // Create Group Input
                    this._component.addClass('input-group');
                    this._component.label.addClass('input-group-text');
                    break;
                default:
                    // Create Default Input
                    this._component.addClass('form-control');
                    break;
            }

            // Check if we should remove the label
            if((this._properties.label === null || this._properties.label === '') && (this._properties.icon === null || this._properties.icon === '')){
                // Remove Label
                this._component.label.remove();
            }

            // Extend Component
            this._extend();

            // Set Event Listeners
            this._component.input.on('input change keyup',function(){
                self.onChange();
            });

            // Clear & Reset Input
            this.clear().reset();
        }

        name(){
            return this._properties.name;
        }

        help(string = null){

            // Set Help Text
            if(string !== null){
                this._component.help.html(string).appendTo(this._component);
            } else {
                this._component.help.html('').remove();
            }
        }

        invalid(string = null){

            // Set Invalid Feedback
            if(string !== null){
                this.valid(null);
                this._component.addClass('has-validation');
                this._component.input.addClass('is-invalid');
                this._component.invalid.html(string).appendTo(this._component);
            } else {
                this._component.removeClass('has-validation');
                this._component.input.removeClass('is-invalid');
                this._component.invalid.html('').remove();
            }
        }

        valid(string = null){

            // Set Valid Feedback
            if(string !== null){
                this.invalid(null);
                this._component.addClass('has-validation');
                this._component.input.addClass('is-valid');
                this._component.valid.html(string).appendTo(this._component);
            } else {
                this._component.removeClass('has-validation');
                this._component.input.removeClass('is-valid');
                this._component.valid.html('').remove();
            }
        }

        val(value = null){

            // Set Value
            if(value !== null){

                // Set Value
                this._component.input.val(value).trigger('change');
            }

            // Return Value
            return this._component.input.val();
        }

        onChange(){
            // Test Condition
            this.condition();

            // Execute Callback
            if(typeof this._properties.callback.onChange === 'function'){
                this._properties.callback.onChange(this, this._component);
            }
        }

        condition(){
            if(typeof this._properties.callback.condition === 'function'){
                if(!this._properties.callback.condition(this, this._component)){
                    this._component.addClass('d-none');
                } else {
                    this._component.removeClass('d-none');
                }
                return this._properties.callback.condition(this, this._component);
            }
            return typeof this._properties.callback.condition !== 'function';
        }

        focus(){
            // Focus Input
            this._component.input.focus();
        }

        clear(){
            this._component.input.val('');
            return this;
        }

        reset(){
            this.val(this._properties.value);
            return this;
        }
    }

    add(type, name, object){
        const self = this;
        if(typeof type !== 'string' || typeof name !== 'string' || (!self.Helper.isClass(object) && typeof object !== 'function')){
            console.log('Builder.add(String, String, Class)');
            return false;
        }
        switch(type){
            case'utilities':
                if(typeof this.#utilities[name] !== 'undefined'){
                    console.log('Utility Already Exist');
                    return false;
                }
                this.#utilities[name] = object;
                break;
            case'layouts':
                if(typeof this.#layouts[name] !== 'undefined'){
                    console.log('Layout Already Exist');
                    return false;
                }
                this.#layouts[name] = object;
                break;
            case'components':
                if(typeof this.#components[name] !== 'undefined'){
                    console.log('Component Already Exist');
                    return false;
                }
                this.#components[name] = object;
                break;
            case'inputs':
                if(typeof this.#inputs[name] !== 'undefined'){
                    console.log('Input Already Exist');
                    return false;
                }
                this.#inputs[name] = object;
                break;
            case'renderers':
                if(typeof this.#renderers[name] !== 'undefined'){
                    console.log('Renderer Already Exist');
                    return false;
                }
                this.#renderers[name] = object;
                break;
            case'widgets':
                if(typeof this.#widgets[name] !== 'undefined'){
                    console.log('Widget Already Exist');
                    return false;
                }
                this.#widgets[name] = object;
                break;
            default:
                console.log('Unknown Builder Object');
                break;
        }
        return this;
    }

    Utility(name, param1 = null, param2 = null, param3 = null){
        const self = this;
        if(typeof name !== 'string'){
            console.log('Builder.utility(String)');
            return false;
        }
        if(typeof this.#utilities[name] === 'undefined'){
            console.log('Unknown Utility');
            return false;
        }
        return new this.#utilities[name](self, param1, param2, param3);
    }

    Component(name, param1 = null, param2 = null, param3 = null){
        const self = this;
        if(typeof name !== 'string'){
            console.log('Builder.Component(String)');
            return false;
        }
        if(typeof this.#components[name] === 'undefined'){
            console.log('Unknown Component');
            return false;
        }
        return new this.#components[name](self, param1, param2, param3);
    }

    Input(name, param1 = null, param2 = null, param3 = null){
        const self = this;
        if(typeof name !== 'string'){
            console.log('Builder.Form(String)');
            return false;
        }
        if(typeof this.#inputs[name] === 'undefined'){
            console.log('Unknown Form');
            return false;
        }
        return new this.#inputs[name](self, param1, param2, param3);
    }

    Widget(name, param1 = null, param2 = null, param3 = null){
        const self = this;
        if(typeof name !== 'string'){
            console.log('Builder.Widget(String)');
            return false;
        }
        if(typeof this.#widgets[name] === 'undefined'){
            console.log('Unknown Widget');
            return false;
        }
        return new this.#widgets[name](self, param1, param2, param3);
    }

    Layout(name, param1 = null, param2 = null, param3 = null){
        const self = this;
        if(typeof name !== 'string'){
            console.log('Builder.layout(String)');
            return false;
        }
        if(typeof this.#layouts[name] === 'undefined'){
            console.log('Unknown Layout');
            return false;
        }
        return new this.#layouts[name](self, param1, param2, param3);
    }

    Render(name, value, data = {}, type = null){

        // Check if the renderer exists
        if (typeof this.#renderers[name] === 'undefined') {
            return '<div>'+value+'</div>';
        }

        return this.#renderers[name](value, data, type);
    }

    #utilities = {
        locale: class extends this.UtilityClass {

            #locales = {};
            #locale = 'en-ca';
            #default = 'en-ca';
            _callback = function(key, locale){
                // console.log('['+locale+'] Missing String: '+key);
            };

            constructor(builder){

                // Call Parent
                super(builder);

                // Set Self
                const self = this;
            }

            save(locale, data){

                // Set Self
                const self = this;

                // Check if locale is set
                if(typeof locale === 'string'){

                    // Loop through data
                    for(const [key, value] of Object.entries(data)){

                        // Check if locale is set
                        if(typeof self.#locales[locale] === 'undefined'){
                            self.#locales[locale] = {};
                        }

                        // Set Value
                        self.#locales[locale][key] = value;
                    }
                }
            }

            set(locale){

                // Set Self
                const self = this;

                // Check if locale is set
                if(typeof locale === 'string'){
                    self.#locale = locale;
                }
            }

            current(){

                // Set Self
                const self = this;

                // Return Current Locale
                return self.#locale;
            }

            get(key, locale = null){

                // Set Self
                const self = this;

                // Set Locale
                if(locale === null || typeof self.#locales[locale] === 'undefined'){
                    locale = self.#locale;
                }

                // Initialize String
                let string = key;

                // Retrieve Value from Locale
                if(typeof self.#locales[locale] === 'undefined' || typeof self.#locales[locale][key] === 'undefined'){

                    // Check if Callback is Set
                    if(typeof self._callback === 'function'){
                        self._callback(key,locale);
                    }

                    // Check if local is different from default
                    if(locale !== self.#default){

                        // Retrieve Value from Default Locale
                        if(locale !== self.#default && (typeof self.#locales[self.#default] === 'undefined' || typeof self.#locales[self.#default][key] === 'undefined')){

                            // Check if Callback is Set
                            if(typeof self._callback === 'function'){
                                self._callback(key,self.#default);
                            }

                        } else {

                            // Retrieve Value from Default Locale
                            string = self.#locales[self.#default][key];
                        }
                    }
                } else {

                    // Retrieve Value from Locale
                    string = self.#locales[locale][key];
                }

                // Return String
                return string;
            }

            parse(string, locale = null){

                // Set Self
                const self = this;

                // Set Matches
                let matches = string.match(/{{(.*?)}}/g);

                // Check if Matches
                if(matches){

                    // Loop through Matches
                    for(const match of matches){

                        // Set Key
                        let key = match.replace('{{','').replace('}}','');

                        // Replace Match
                        string = string.replace(match,self.get(key));
                    }
                }

                // Return
                return string;
            }
        },
        parser: class extends this.UtilityClass {
            constructor(builder){

                // Call Parent
                super(builder);

                // Set Self
                const self = this;
            }

            parse(string, locale = null){

                // Set Self
                const self = this;

                // Check if String
                if(typeof string !== 'string'){
                    return string;
                }

                // 0) Replace various patterns in a string with HTML elements.
                //    The following patterns are supported:
                //     - %today% => current date (YYYY-MM-DD)
                //     - %now% => current date and time (YYYY-MM-DD HH:MM:SS)
                //     - %this_year% => current year
                //     - %this_month% => current month
                //     - %this_day% => current day
                //     - %next_2year% => date in 2 years
                //     - %next_18months% => date in 18 months
                //     - %next_year% => date in 1 year
                //     - %next_month% => date in 1 month
                //     - %next_week% => date in 1 week
                //     - %next_day% => date in 1 day
                //     - %tomorrow% => tomorrow's date
                //     - %yesterday% => yesterday's date
                //     - %last_week% => date 1 week ago
                //     - %last_month% => date 1 month ago
                //     - %last_year% => date 1 year ago
                //     - %last_18months% => date 18 months ago
                //     - %last_2year% => date 2 years ago
                //     - %locale% => current locale
                string = string.replace(/%today%/g, new Date().today());
                string = string.replace(/%now%/g, new Date().today() + ' ' + new Date().timeNow());
                string = string.replace(/%this_year%/g, new Date().getFullYear());
                string = string.replace(/%this_month%/g, new Date().getMonth() + 1);
                string = string.replace(/%this_day%/g, new Date().getDate());
                string = string.replace(/%next_2year%/g, new Date(new Date().setFullYear(new Date().getFullYear() + 2)).today());
                string = string.replace(/%next_18months%/g, new Date(new Date().setMonth(new Date().getMonth() + 18)).today());
                string = string.replace(/%next_year%/g, new Date(new Date().setFullYear(new Date().getFullYear() + 1)).today());
                string = string.replace(/%next_month%/g, new Date(new Date().setMonth(new Date().getMonth() + 1)).today());
                string = string.replace(/%next_week%/g, new Date(new Date().setDate(new Date().getDate() + 7)).today());
                string = string.replace(/%next_day%/g, new Date(new Date().setDate(new Date().getDate() + 1)).today());
                string = string.replace(/%tomorrow%/g, new Date(new Date().setDate(new Date().getDate() + 1)).today());
                string = string.replace(/%yesterday%/g, new Date(new Date().setDate(new Date().getDate() - 1)).today());
                string = string.replace(/%last_week%/g, new Date(new Date().setDate(new Date().getDate() - 7)).today());
                string = string.replace(/%last_month%/g, new Date(new Date().setMonth(new Date().getMonth() - 1)).today());
                string = string.replace(/%last_year%/g, new Date(new Date().setFullYear(new Date().getFullYear() - 1)).today());
                string = string.replace(/%last_18months%/g, new Date(new Date().setMonth(new Date().getMonth() - 18)).today());
                string = string.replace(/%last_2year%/g, new Date(new Date().setFullYear(new Date().getFullYear() - 2)).today());
                string = string.replace(/%locale%/g, self._builder.Locale.current());

                // 1) Replace [[class:link|label]] => <a class="class" href="link">label</a>
                //    Use a capturing group for link (group 1) and label (group 2).
                //    e.g., [[btn btn-sm btn-primary rounded-pill:https://www.google.com|Google]]
                //          => <a class="btn btn-sm btn-primary rounded-pill" href="https://www.google.com">Google</a>
                string = string.replace(/\[\[([^\|\]]+):([^\|\]]+)\|([^\]]+)\]\]/g, function(match, className, link, label) {
                    return `<a class="${className}" href="${link}">${label}</a>`;
                });

                // 2) Replace [[link|label]] => <a href="link">label</a>
                //    Use a capturing group for link (group 1) and label (group 2).
                //    e.g., [[https://www.google.com|Google]]
                //          => <a href="https://www.google.com">Google</a>
                string = string.replace(/\[\[([^\|\]]+)\|([^\]]+)\]\]/g, function(match, link, label) {
                    return `<a href="${link}">${label}</a>`;
                });

                // 3) Replace [[link]] => <a href="link">link</a>
                //    This is for the case with no label, so the link itself is used as the label.
                //    e.g., [[https://www.google.com]]
                //          => <a href="https://www.google.com">https://www.google.com</a>
                string = string.replace(/\[\[([^\]]+)\]\]/g, function(match, link) {
                    return `<a href="${link}">${link}</a>`;
                });

                // 4) Replace <i>iconName</i> => <i class="bi bi-iconName"></i>
                //    Capture the text between <i> and </i>, use that in "bi-iconName".
                //    e.g., <i>flag</i> => <i class="bi bi-flag"></i>
                string = string.replace(/<i>([^<]+)<\/i>/g, function(match, iconName) {
                    return `<i class="bi bi-${iconName}"></i>`;
                });

                // 5) Replace <vcard>id:name</vcard> => <a data-vcard="id" class="btn btn-sm btn-primary m-1 rounded-pill cursor-pointer"><i class="me-1 bi bi-person-vcard"></i>name</a>
                //    Capture the text between <vcard> and </vcard>, split it by ":", and use the first part in "data-vcard" and the second part in "name".
                //    e.g., <vcard>1:John Doe</vcard> => <a data-vcard="1" class="btn btn-sm btn-primary m-1 rounded-pill cursor-pointer"><i class="me-1 bi bi-person-vcard"></i>John Doe</a>
                string = string.replace(
                    /<vcard\s*([^>]*)>([^<]+)<\/vcard>/g,
                    function(match, attributes = "", idName) {
                        // Split out the "id:name"
                        const [id, name] = idName.split(":");

                        // Base classes that are always present
                        let baseClasses = ["btn", "btn-sm", "btn-primary", "m-1", "rounded-pill", "cursor-pointer"];

                        // Capture recognized Bootstrap color classes to swap out with "btn-{color}"
                        const recognizedColors = ["primary","secondary","success","danger","warning","info","dark","light"];

                        // Split the attributes string into an array of class-like tokens
                        const tokens = attributes.split(/\s+/).filter(Boolean);

                        // Keep track if a recognized color was found
                        let foundColor = null;

                        // Examine each token
                        tokens.forEach(token => {
                            const lowerToken = token.toLowerCase();

                            if (recognizedColors.includes(lowerToken)) {
                                // If we find e.g. "success", we’ll remove the default 'btn-primary'
                                // and later add 'btn-success'
                                foundColor = lowerToken;
                            } else {
                                // For any classes not in recognizedColors, just add them
                                baseClasses.push(token);
                            }
                        });

                        // If a recognized color was found, replace any "btn-primary" with "btn-{that color}"
                        if (foundColor) {
                            baseClasses = baseClasses.map(cls => {
                                if (cls === "btn-primary") {
                                    return `btn-${foundColor}`;
                                }
                                return cls;
                            });
                        }

                        // Create final classes string
                        const finalClasses = [...new Set(baseClasses)].join(" ");

                        // Return the transformed <a> tag
                        return `<a data-vcard="${id}" data-vcard-name="${name}" class="${finalClasses}">
                                    <i class="me-1 bi bi-person-vcard"></i>${name}
                                </a>`;
                    }
                );

                // 6) Replace <priority>level</priority> => <span data-priority="level" class="btn btn-sm btn-color m-1 rounded-pill"><i class="me-1 bi bi-icon"></i>name</span>
                //    Capture the text between <priority> and </priority>, use that in "data-priority".
                //    e.g., <priority>3</priority> => <span data-priority="3" class="btn btn-sm btn-warning m-1 rounded-pill"><i class="me-1 bi bi-exclamation-diamond"></i>Urgent</span>
                string = string.replace(/<priority>([^<]+)<\/priority>/g, function(match, level) {
                    let color = ['secondary','info','primary','warning','danger'];
                    let icon = ['exclamation-triangle','info-circle','exclamation-circle','exclamation-diamond','exclamation-square'];
                    let name = ['Low','Normal','High','Urgent','Critical'];
                    name = builder.Locale.get(name[level]);
                    return `<span data-priority="${level}" class="badge text-bg-${color[level]} mx-1 rounded-pill"><i class="me-1 bi bi-${icon[level]}"></i>${name}</span>`;
                });

                // 7) Replace <date>date</date> => <span class="badge text-bg-primary mx-1 rounded-pill"><i class="me-1 bi bi-calendar-event"></i>date</span>
                //    Capture the text between <date> and </date>, use that in "data-date".
                //    e.g., <date>2021-12-31</date> => <span class="badge text-bg-primary mx-1 rounded-pill"><i class="me-1 bi bi-calendar-event"></i>2021-12-31</span>
                string = string.replace(/<date>([^<]+)<\/date>/g, function(match, date) {
                    return `<span class="badge text-bg-primary mx-1 rounded-pill"><i class="me-1 bi bi-calendar-event"></i>${date}</span>`;
                });

                // 8) Replace <note>content</note> => <span class="badge text-bg-warning mx-1 rounded-pill"><i class="me-1 bi bi-sticky"></i>content</span>
                //    Capture the text between <note> and </note>, use that in "data-note".
                //    e.g., <note>Important</note> => <span class="badge text-bg-warning mx-1 rounded-pill"><i class="me-1 bi bi-sticky"></i>Important</span>
                string = string.replace(/<note>([^<]+)<\/note>/g, function(match, content) {
                    return `<span class="badge text-bg-warning mx-1 rounded-pill"><i class="me-1 bi bi-sticky"></i>${content}</span>`;
                });

                // 9) Replace <tel>content</tel> => <span class="badge text-bg-warning mx-1 rounded-pill"><i class="me-1 bi bi-sticky"></i>content</span>
                //    Capture the text between <tel> and </tel>, use that in "data-tel".
                //    e.g., <tel>Important</tel> => <a href="tel:${content}" class="btn btn-sm btn-info m-1 rounded-pill cursor-pointer"><i class="me-1 bi bi-telephone"></i>Important</a>
                string = string.replace(/<tel>([^<]+)<\/tel>/g, function(match, content) {
                    return `<a href="tel:${content}" class="btn btn-sm btn-info m-1 rounded-pill cursor-pointer"><i class="me-1 bi bi-telephone"></i>${content}</a>`;
                });

                // Parse through locale
                if(typeof string === 'string'){
                    string = self._builder.Locale.parse(string,locale);
                }

                // Parse through status
                if(typeof string === 'string'){
                    string = self._builder.Status.parse(string,locale);
                }

                // Parse through rating
                if(typeof string === 'string'){
                    string = self._builder.Rating.parse(string,locale);
                }

                // Parse through priority
                if(typeof string === 'string'){
                    string = self._builder.Priority.parse(string,locale);
                }

                // Parse through avatar
                if(typeof string === 'string'){
                    string = self._builder.Avatar.parse(string,locale);
                }

                // Return
                return string;
            }
        },
        avatar: class extends this.UtilityClass {

            constructor(builder){

                // Call Parent
                super(builder);

                // Set Self
                const self = this;
            }

            avatar(email){

                // Set Self
                const self = this;

                // Create avatar
                var avatar = $(document.createElement('span'))
                    .attr({
                        'class': 'cursor-help align-middle avatar',
                    });

                // Set Label
                avatar.text(email);

                // Prepend Icon
                avatar.prepend($(document.createElement('img')).attr({
                    'class': 'me-1 rounded-circle',
                    'width': '48',
                    'height': '48',
                    'src': self._builder.Helper.gravatar(email),
                }));

                avatar.attr({
                    'data-bs-toggle': 'tooltip',
                    'data-bs-placement': 'top',
                    'title': status.email,
                    'data-bs-title': status.email,
                });

                // Enable Tooltip
                var tooltip = new bootstrap.Tooltip(avatar[0]);

                // Return avatar
                return avatar;
            }

            parse(string, locale = null){

                // Set Self
                const self = this;

                // Set Matches [[utility:type:level]]
                let matches = string.match(/\[\[(.*?)\]\]/g);

                // Check if Matches
                if(matches){

                    // Loop through Matches
                    for(const match of matches){

                        // Set Key
                        let key = match.replace('[[','').replace(']]','');
                        let parts = key.split(':');
                        let utility = parts[0];
                        let email = parts[1];

                        if(utility === 'avatar'){

                            // Get avatar
                            let avatar = self.avatar(email);

                            // Get html
                            let html = avatar[0].outerHTML;

                            // Replace Match
                            string = string.replace(match,html);
                        }
                    }
                }

                // Return
                return string;
            }
        },
        status: class extends this.UtilityClass {

            #statuses = {};
            _callback = function(targetTable, level){
                console.log('Missing Status: '+targetTable+' ['+level+']');
            };

            constructor(builder){

                // Call Parent
                super(builder);

                // Set Self
                const self = this;
            }

            save(status){

                // Set Self
                const self = this;

                // Loop through each required field
                for(const [key, value] of Object.entries(['targetTable','label','description','icon','color','level'])){

                    // Check if field is set
                    if(typeof status[value] === 'undefined'){
                        console.log('Missing Field: '+value);
                        return;
                    }
                }

                // Check if targetTable is set
                if(typeof self.#statuses[status.targetTable] === 'undefined'){
                    self.#statuses[status.targetTable] = {};
                }

                // Set Value
                self.#statuses[status.targetTable][status.level] = {
                    'targetTable': status.targetTable,
                    'label': status.label,
                    'description': status.description,
                    'icon': status.icon,
                    'color': status.color,
                    'level': status.level,
                };
            }

            get(targetTable, level){

                // Set Self
                const self = this;

                // Check if targetTable and level are set
                if(typeof self.#statuses[targetTable] === 'undefined' || typeof self.#statuses[targetTable][level] === 'undefined'){

                    // Check if callback is set
                    if(typeof self._callback === 'function'){
                        self._callback(targetTable, level);
                    }

                    // Return Empty Status
                    return {
                        'targetTable': null,
                        'label': null,
                        'description': null,
                        'icon': null,
                        'color': null,
                        'level': null,
                    };
                } else {

                    // Return Status
                    return self.#statuses[targetTable][level];
                }
            }

            badge(targetTable, level){

                // Set Self
                const self = this;

                // Get Status
                const status = self.get(targetTable, level);

                // Create Badge
                var badge = $(document.createElement('span'))
                    .attr({
                        'class': 'cursor-help badge',
                    });

                // Check if label is set
                if(status.label !== null){

                    // Set Label
                    badge.text(self._builder.Locale.get(status.label));
                }

                // Check if color is set
                if(status.color !== null){

                    // Set Color
                    badge.addClass('text-bg-'+status.color);
                }

                // Check if icon is set
                if(status.icon !== null){

                    // Prepend Icon
                    badge.prepend($(document.createElement('i')).addClass('me-1 bi bi-'+status.icon));
                }

                // Check if description is set
                if(status.description !== null){
                    badge.attr({
                        'data-bs-toggle': 'tooltip',
                        'data-bs-placement': 'top',
                        'title': status.description,
                        'data-bs-title': status.description,
                    });

                    // Enable Tooltip
                    var tooltip = new bootstrap.Tooltip(badge[0]);
                }

                // Check if targetTable is set
                if(status.targetTable !== null){

                    // Set targetTable
                    badge.attr('data-targetTable', status.targetTable);
                }

                // Check if Level is set
                if(status.level !== null){

                    // Set Level
                    badge.attr('data-level', status.level);
                }

                // Return Badge
                return badge;
            }

            parse(string){

                // Set Self
                const self = this;

                // Set Matches [[utility:targetTable:level]]
                let matches = string.match(/\[\[(.*?)\]\]/g);

                // Check if Matches
                if(matches){

                    // Loop through Matches
                    for(const match of matches){

                        // Set Key
                        let key = match.replace('[[','').replace(']]','');
                        let parts = key.split(':');
                        let utility = parts[0];
                        let targetTable = parts[1];
                        let level = parts[2];

                        if(utility === 'status'){

                            // Get Status
                            let status = self.badge(targetTable, level);

                            // Get html
                            let html = status[0].outerHTML;

                            // Replace Match
                            string = string.replace(match,html);
                        }
                    }
                }

                // Return
                return string;
            }
        },
        rating: class extends this.ComponentClass {

            #ratings = {};
            _callback = function(targetTable, level){
                console.log('Missing Rating: '+targetTable+' ['+level+']');
            };

            constructor(builder){

                // Call Parent
                super(builder);

                // Set Self
                const self = this;
            }

            save(rating){

                // Set Self
                const self = this;

                // Loop through each required field
                for(const [key, value] of Object.entries(['targetTable','label','description','icon','color','level'])){

                    // Check if field is set
                    if(typeof rating[value] === 'undefined'){
                        console.log('Missing Field: '+value);
                        return;
                    }
                }

                // Check if targetTable is set
                if(typeof self.#ratings[rating.targetTable] === 'undefined'){
                    self.#ratings[rating.targetTable] = {};
                }

                // Set Value
                self.#ratings[rating.targetTable][rating.level] = {
                    'targetTable': rating.targetTable,
                    'label': rating.label,
                    'description': rating.description,
                    'icon': rating.icon,
                    'color': rating.color,
                    'level': rating.level,
                };
            }

            get(targetTable, level = null){

                // Set Self
                const self = this;

                // Check if level is null
                if(level === null){

                    // Check if targetTable and level are set
                    if(typeof self.#ratings[targetTable] === 'undefined'){

                        // Return Empty Array
                        return [];
                    } else {

                        // Return Rating
                        return self.#ratings[targetTable];
                    }
                } else {

                    // Check if targetTable and level are set
                    if(typeof self.#ratings[targetTable] === 'undefined' || typeof self.#ratings[targetTable][level] === 'undefined'){

                        // Check if callback is set
                        if(typeof self._callback === 'function'){
                            self._callback(targetTable, level);
                        }

                        // Return Empty Rating
                        return {
                            'targetTable': null,
                            'label': null,
                            'description': null,
                            'icon': null,
                            'color': null,
                            'level': null,
                        };
                    } else {

                        // Return Rating
                        return self.#ratings[targetTable][level];
                    }
                }
            }

            badge(targetTable, level){

                // Set Self
                const self = this;

                // Get Rating
                const rating = self.get(targetTable, level);

                // Create Badge
                var badge = $(document.createElement('span'))
                    .attr({
                        'class': 'cursor-help badge',
                    });

                // Check if label is set
                if(rating.label !== null){

                    // Set Label
                    badge.text(self._builder.Locale.get(rating.label));
                }

                // Check if color is set
                if(rating.color !== null){

                    // Set Color
                    badge.addClass('text-bg-'+rating.color);
                }

                // Check if icon is set
                if(rating.icon !== null){

                    // Prepend Icon
                    badge.prepend($(document.createElement('i')).addClass('me-1 bi bi-'+rating.icon));
                }

                // Check if description is set
                if(rating.description !== null){
                    badge.attr({
                        'data-bs-toggle': 'tooltip',
                        'data-bs-placement': 'top',
                        'title': rating.description,
                        'data-bs-title': rating.description,
                    });

                    // Enable Tooltip
                    var tooltip = new bootstrap.Tooltip(badge[0]);
                }

                // Check if targetTable is set
                if(rating.targetTable !== null){

                    // Set targetTable
                    badge.attr('data-targetTable', rating.targetTable);
                }

                // Check if Level is set
                if(rating.level !== null){

                    // Set Level
                    badge.attr('data-level', rating.level);
                }

                // Return Badge
                return badge;
            }

            parse(string){

                // Set Self
                const self = this;

                // Set Matches [[utility:targetTable:level]]
                let matches = string.match(/\[\[(.*?)\]\]/g);

                // Check if Matches
                if(matches){

                    // Loop through Matches
                    for(const match of matches){

                        // Set Key
                        let key = match.replace('[[','').replace(']]','');
                        let parts = key.split(':');
                        let utility = parts[0];
                        let targetTable = parts[1];
                        let level = parts[2];

                        if(utility === 'rating'){

                            // Get rating
                            let rating = self.badge(targetTable, level);

                            // Get html
                            let html = rating[0].outerHTML;

                            // Replace Match
                            string = string.replace(match,html);
                        }
                    }
                }

                // Return
                return string;
            }
        },
        priority: class extends this.ComponentClass {

            #priorities = {};
            _callback = function(targetTable, level){
                console.log('Missing Priority: '+targetTable+' ['+level+']');
            };

            constructor(builder){

                // Call Parent
                super(builder);

                // Set Self
                const self = this;
            }

            save(priority){

                // Set Self
                const self = this;

                // Loop through each required field
                for(const [key, value] of Object.entries(['targetTable','label','description','icon','color','level'])){

                    // Check if field is set
                    if(typeof priority[value] === 'undefined'){
                        console.log('Missing Field: '+value);
                        return;
                    }
                }

                // Check if targetTable is set
                if(typeof self.#priorities[priority.targetTable] === 'undefined'){
                    self.#priorities[priority.targetTable] = {};
                }

                // Set Value
                self.#priorities[priority.targetTable][priority.level] = {
                    'targetTable': priority.targetTable,
                    'label': priority.label,
                    'description': priority.description,
                    'icon': priority.icon,
                    'color': priority.color,
                    'level': priority.level,
                };
            }

            get(targetTable, level){

                // Set Self
                const self = this;

                // Check if targetTable and level are set
                if(typeof self.#priorities[targetTable] === 'undefined' || typeof self.#priorities[targetTable][level] === 'undefined'){

                    // Check if callback is set
                    if(typeof self._callback === 'function'){
                        self._callback(targetTable, level);
                    }

                    // Return Empty Priority
                    return {
                        'targetTable': null,
                        'label': null,
                        'description': null,
                        'icon': null,
                        'color': null,
                        'level': null,
                    };
                } else {

                    // Return Priority
                    return self.#priorities[targetTable][level];
                }
            }

            badge(targetTable, level){

                // Set Self
                const self = this;

                // Get Priority
                const Priority = self.get(targetTable, level);

                // Create Badge
                var badge = $(document.createElement('span'))
                    .attr({
                        'class': 'cursor-help badge',
                    });

                // Check if label is set
                if(priority.label !== null){

                    // Set Label
                    badge.text(self._builder.Locale.get(priority.label));
                }

                // Check if color is set
                if(priority.color !== null){

                    // Set Color
                    badge.addClass('text-bg-'+priority.color);
                }

                // Check if icon is set
                if(priority.icon !== null){

                    // Prepend Icon
                    badge.prepend($(document.createElement('i')).addClass('me-1 bi bi-'+priority.icon));
                }

                // Check if description is set
                if(priority.description !== null){
                    badge.attr({
                        'data-bs-toggle': 'tooltip',
                        'data-bs-placement': 'top',
                        'title': priority.description,
                        'data-bs-title': priority.description,
                    });

                    // Enable Tooltip
                    var tooltip = new bootstrap.Tooltip(badge[0]);
                }

                // Check if targetTable is set
                if(priority.targetTable !== null){

                    // Set targetTable
                    badge.attr('data-targetTable', priority.targetTable);
                }

                // Check if Level is set
                if(priority.level !== null){

                    // Set Level
                    badge.attr('data-level', priority.level);
                }

                // Return Badge
                return badge;
            }

            parse(string){

                // Set Self
                const self = this;

                // Set Matches [[utility:targetTable:level]]
                let matches = string.match(/\[\[(.*?)\]\]/g);

                // Check if Matches
                if(matches){

                    // Loop through Matches
                    for(const match of matches){

                        // Set Key
                        let key = match.replace('[[','').replace(']]','');
                        let parts = key.split(':');
                        let utility = parts[0];
                        let targetTable = parts[1];
                        let level = parts[2];

                        if(utility === 'priority'){

                            // Get priority
                            let priority = self.badge(targetTable, level);

                            // Get html
                            let html = priority[0].outerHTML;

                            // Replace Match
                            string = string.replace(match,html);
                        }
                    }
                }

                // Return
                return string;
            }
        },
        option: class extends this.ComponentClass {

            #options = {};
            _callback = function(targetTable, target = null){
                if(target === null){
                    console.log('Missing Options: '+targetTable);
                } else {
                    console.log('Missing Options: '+targetTable+' ['+target+']');
                }
            };

            constructor(builder){

                // Call Parent
                super(builder);

                // Set Self
                const self = this;
            }

            save(targetTable, options, target = null){

                // Set Self
                const self = this;

                // Check if options array exists for targetTable
                if(typeof self.#options[targetTable] === 'undefined'){
                    self.#options[targetTable] = [];
                }

                // Check if options array exists for targetTable
                if(target !== null && typeof self.#options[targetTable][target] === 'undefined'){
                    self.#options[targetTable][target] = [];
                }

                // Loop through each options
                for(const [key, option] of Object.entries(options)){

                    // Check if option is an object and contains the required fields
                    if(typeof option === 'object' && (option.id || option.id === 0 || option.id === false) && (option.text || option.text === 0 || option.text === false)){
                        if(target !== null){
                            self.#options[targetTable][target].push(option);
                        } else {
                            self.#options[targetTable].push(option);
                        }
                    } else {
                        console.log('Missing Field: id or text',option);
                    }
                }
            }

            get(targetTable, target = null){

                // Set Self
                const self = this;

                // Check if targetTable and level are set
                if(typeof self.#options[targetTable] === 'undefined'){

                    // Check if callback is set
                    if(typeof self._callback === 'function'){
                        self._callback(targetTable, target);
                    }

                    // Return Empty array
                    return [];
                }

                // Check if target is null
                if(target === null){

                    // Return Options
                    return self.#options[targetTable];
                } else {

                    // Check if targetTable and level are set
                    if(typeof self.#options[targetTable][target] === 'undefined'){

                        // Check if callback is set
                        if(typeof self._callback === 'function'){
                            self._callback(targetTable, target);
                        }

                        // Return Empty array
                        return [];
                    }

                    // Return Options
                    return self.#options[targetTable][target];
                }
            }
        },
        search: class extends this.UtilityClass {

            #field = null

            constructor(builder){

                // Call Parent
                super(builder);

                // Scan Search Field
                this.scan('input.search');
            }

            scan(selector = 'input.search'){

                // Set Search Field
                this.#field = $(selector);
            }

            get(){

                // Return Search Field
                return this.#field;
            }

            set(object, data = null){

                // Set object as Object
                if(typeof object === 'string'){
                    object = $(object);
                }

                // Initialize String
                let string = '';

                // Set Search Attribute
                if(typeof object === 'object' && object != null){

                    // Check if the data-search attribute is set
                    if(typeof object.attr('data-search') !== "undefined"){

                        // Set Search Attribute
                        string += object.attr('data-search').toString().toUpperCase();
                    } else {

                        // Check if Text is Set and append the text to the data-search attribute
                        string += object.text().toString().toUpperCase();
                    }

                    // Check if Data is Set and append the data to the data-search attribute
                    if(data !== null){

                        // Check if Data is an Array or Object
                        if(typeof data === 'object'){

                            // Loop through Data
                            for(const [key, value] of Object.entries(data)){

                                // Check if Value is not null
                                if(value !== null){

                                    // Append Data to String
                                    string += ' ' + value.toString().toUpperCase();
                                }
                            }
                        }
                    }

                    // Set Search Attribute
                    object.attr('data-search',string.toString().toUpperCase());
                }
            }

            add(object){

                // Set object as Object
                if(typeof object === 'string'){
                    object = $(object);
                }

                // Add Search Event
                if(typeof object === 'object' && object != null){
                    this.#field.on('input propertychange',function(){
                        if($(this).val() !== ''){
                            object.find('[data-search]').hide();
                            object.find('[data-search*="'+$(this).val().toString().toUpperCase()+'"]').show();
                        } else {
                            object.find('[data-search]').show();
                        }
                    });
                }
            }
        },
        notification: class extends this.ComponentClass {

            _init(){
                this._properties = {
                    class: {
                        component: null,
                    },
                    callback: {
                        click: null,
                        readAll: null,
                        onRead: null,
                        format: null,
                    },
                    icon: "bell",
                    color: "danger",
                    onReadDelay: 500,
                    properties: {
                        class: {
                            item: null,
                        },
                        click: null,
                        onRead: null,
                        icon: "bell",
                        color: "primary",
                        datetime: null,
                        label: null,
                        isRead: false,
                    },
                };
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'notifications' + this._id,
                    'class': 'dropdown notificationArea',
                });
                this._component.id = this._component.attr('id');

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }

                // Create Button
                this._component.btn = $(document.createElement('button')).attr({
                    'class': 'nav-link text-decoration-none py-2 animate-slide-hover-top-20',
                    'type': 'button',
                    'data-bs-toggle': 'dropdown',
                    'aria-expanded': 'false',
                }).appendTo(this._component);
                this._component.btn.icon = $(document.createElement('i')).attr({
                    'class': 'fs-4 bi bi-' + this._properties.icon,
                    'style': 'height: 2.25rem !important;width: 1.5rem !important',
                }).appendTo(this._component.btn);
                this._component.btn.badge = $(document.createElement('span')).attr({
                    'class': 'position-absolute top-25 start-75 translate-middle border border-light rounded-circle d-none',
                    'style': 'padding: 8px;',
                }).appendTo(this._component.btn);

                // Create Menu
                this._component.menu = $(document.createElement('ul')).attr({
                    'class': 'dropdown-menu dropdown-list dropdown-menu-end pb-0',
                    'style': 'min-width: 400px; max-width: 500px;',
                }).appendTo(this._component);

                // Create Header
                this._component.menu.header = $(document.createElement('li')).appendTo(this._component.menu);
                this._component.menu.header.title = $(document.createElement('h5')).addClass('py-2 px-3 m-0 cursor-default d-flex justify-content-center align-items-center').appendTo(this._component.menu.header);
                this._component.menu.header.title.label = $(document.createElement('span')).text('Notifications').appendTo(this._component.menu.header.title);
                this._component.menu.header.title.count = $(document.createElement('span')).addClass('badge rounded-pill ms-2 d-none').appendTo(this._component.menu.header.title);

                // Create Seperators
                this._component.menu.seperator = {};
                this._component.menu.seperator = $(document.createElement('li')).appendTo(this._component.menu);
                this._component.menu.seperator.hr = $(document.createElement('hr')).addClass('dropdown-divider m-0').appendTo(this._component.menu.seperator);

                // Create Items List
                this._component.menu.list = $(document.createElement('div')).attr({
                    'class': 'overflow-auto',
                    'style': 'max-height: 500px;',
                }).appendTo(this._component.menu);

                // Create Footer
                this._component.menu.footer = $(document.createElement('li')).appendTo(this._component.menu);
                this._component.menu.footer.btn = $(document.createElement('button')).attr({
                    'class': 'dropdown-item text-center py-2 rounded-bottom btn btn-link',
                    'type': 'button',
                }).appendTo(this._component.menu.footer);
                this._component.menu.footer.btn.label = $(document.createElement('small')).text('Mark All as Read').appendTo(this._component.menu.footer.btn);

                // Set Icon
                if(this._properties.icon === null){
                    this._component.btn.icon.remove();
                }

                // Set Color
                if(this._properties.color){
                    this._component.btn.badge.addClass('text-bg-' + this._properties.color);
                    this._component.menu.header.title.count.addClass('text-bg-' + this._properties.color);
                }

                // Add Callback
                self._component.menu.footer.btn.on('click',function(){
                    self.readAll();
                });
            }

            count(){

                // Set Self
                const self = this;

                // Count the number of new notifications
                let count = this._component.find('[data-isRead="false"]').length;

                // Set Count
                this._component.menu.header.title.count.text(count);

                // Show Badge
                if(count > 0){
                    this._component.menu.header.title.count.removeClass('d-none');
                    this._component.btn.badge.removeClass('d-none');
                    this._component.btn.addClass('animate-wobble');
                } else {
                    this._component.menu.header.title.count.addClass('d-none');
                    this._component.btn.badge.addClass('d-none');
                    this._component.btn.removeClass('animate-wobble');
                }

                // Return Count
                return count;
            }

            readAll(){

                // Set Self
                const self = this;

                // Get all unread notifications
                let items = this._component.find('[data-isRead="false"]');

                // Set all notifications as read
                items.attr('data-isRead', 'true').removeClass('blink-primary');
                items.find('span.text-wrap').removeClass('fw-bold');

                // Count the number of new notifications
                this.count();

                // Execute Callback
                if(typeof self._properties.callback.readAll === 'function'){
                    self._properties.callback.readAll(self,self._component);
                }

                // Return Object
                return this;
            }

            add(param1 = null, param2 = null){

                // Set Self
                const self = this;

                let options = {};
                let callback = null;

                let properties = {};

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Configure Options
                for(const [key, value] of Object.entries(this._properties.properties)){
                    if(typeof properties[key] === 'undefined'){
                        properties[key] = value;
                    }
                }
                for(const [key, value] of Object.entries(options)){
                    if(typeof properties[key] !== 'undefined'){
                        switch(key){
                            case"label":
                                properties[key] = self._builder.Locale.parse(value);
                                break;
                            case"class":
                                for(const [section, classes] of Object.entries(value)){
                                    if(properties[key][section] != null){
                                        properties[key][section] += ' ' + classes;
                                    } else {
                                        properties[key][section] = classes;
                                    }
                                }
                                break;
                            default:
                                properties[key] = value;
                                break;
                        }
                    }
                }

                // Set ID
                let id = this._count();

                // Create Item
                let item = $(document.createElement('li')).attr({
                    'id': this._component.id + 'item' + id,
                }).prependTo(this._component.menu.list);

                // Create Button
                item.btn = $(document.createElement('button')).attr({
                    'class': 'dropdown-item d-flex align-items-center py-2',
                    'type': 'button',
                }).appendTo(item);

                // Add Icon
                item.btn.icon = $(document.createElement('div')).addClass('me-3').appendTo(item.btn);
                item.btn.icon.frame = $(document.createElement('div')).attr({
                    'class': 'd-flex align-items-center justify-content-center rounded-circle',
                    'style': 'width: 48px; height: 48px;',
                }).appendTo(item.btn.icon);
                item.btn.icon.frame.icon = $(document.createElement('i')).addClass('bi').appendTo(item.btn.icon.frame);

                // Add Label
                item.btn.label = $(document.createElement('div')).addClass('d-flex flex-column align-items-justify').appendTo(item.btn);
                item.btn.label.time = $(document.createElement('small')).addClass('text-muted').appendTo(item.btn.label);
                item.btn.label.time.timeago = $(document.createElement('time')).attr({
                    'class': 'timeago',
                    'data-bs-toggle': 'tooltip',
                }).appendTo(item.btn.label.time);
                item.btn.label.text = $(document.createElement('span')).addClass('text-wrap').html(properties.label).appendTo(item.btn.label);

                // Add Seperator
                item.seperator = $(document.createElement('li')).insertAfter(item);
                item.seperator.hr = $(document.createElement('hr')).addClass('dropdown-divider m-0').appendTo(item.seperator);

                // Configure Color
                if(properties.color !== null){
                    item.btn.icon.frame.addClass('text-bg-' + properties.color);
                } else {
                    item.btn.icon.frame.addClass('text-bg-primary');
                }

                // Configure Icon
                if(properties.icon !== null){
                    item.btn.icon.frame.icon.addClass('bi-' + properties.icon);
                } else {
                    item.btn.icon.frame.icon.addClass('bi-bell');
                }

                // Configure Date Time
                let datetime = null;
                if(properties.datetime !== null){
                    datetime = new Date(properties.datetime);
                } else {
                    datetime = new Date();
                }
                item.btn.label.time.timeago.attr({
                    'title': datetime.toLocaleString(),
                    'datetime': datetime.toLocaleString(),
                    'data-bs-title': datetime.toLocaleString(),
                    'data-bs-toggle': 'tooltip',
                    'data-bs-placement': 'top',
                }).text(datetime.toLocaleString());
                setTimeout(function(){ item.btn.label.time.timeago.timeago(); }, 0);
                item.btn.label.time.timeago.bootstrap = new bootstrap.Tooltip(item.btn.label.time.timeago);

                // Configure isRead
                item.attr('data-isRead',properties.isRead);
                if(!properties.isRead){
                    item.addClass('blink-primary');
                    item.btn.label.text.addClass('fw-bold');
                }

                // Set Item Class
                if(properties.class.item){
                    item.addClass(properties.class.item);
                }

                // Add read function
                item.read = function(){
                    item.attr('data-isRead', 'true').removeClass('blink-primary');
                    item.btn.label.text.removeClass('fw-bold');
                    self.count();
                };

                // Add onRead Callback
                if(!properties.isRead){
                    item.timer;
                    item.hover(function() {
                        item.timer = setTimeout(function() {
                            item.read();
                            if(typeof properties.onRead === 'function'){
                                properties.onRead(item,self,self._component);
                            }
                            if(typeof self._properties.callback.onRead === 'function'){
                                self._properties.callback.onRead(item,self,self._component);
                            }
                        }, self._properties.onReadDelay);
                    }, function() {
                        clearTimeout(item.timer);
                    });
                }

                // Add Callback
                item.on('click',function(){
                    if(typeof self._properties.callback.click === 'function'){
                        self._properties.callback.click(item,self,self._component);
                    }
                    if(typeof properties.click === 'function'){
                        properties.click(item,self,self._component);
                    }
                });

                // Execute Callback
                if(typeof self._properties.callback.format === 'function'){
                    self._properties.callback.format(item,this);
                }
                if(typeof callback === 'function'){
                    callback(item,this);
                }

                // Set Count
                this.count();

                // Return Object
                return this;
            }
        },
        message: class extends this.ComponentClass {

            _init(){
                this._properties = {
                    class: {
                        component: null,
                    },
                    callback: {
                        click: null,
                        viewAll: null,
                        onRead: null,
                    },
                    icon: "envelope",
                    color: "info",
                    onReadDelay: 500,
                    properties: {
                        class: {
                            item: null,
                        },
                        click: null,
                        onRead: null,
                        datetime: null,
                        label: null,
                        email: null,
                        name: null,
                        isRead: false,
                    },
                };
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'messages' + this._id,
                    'class': 'dropdown messageArea',
                });
                this._component.id = this._component.attr('id');

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }

                // Create Button
                this._component.btn = $(document.createElement('button')).attr({
                    'class': 'nav-link text-decoration-none py-2 animate-slide-hover-top-20',
                    'type': 'button',
                    'data-bs-toggle': 'dropdown',
                    'aria-expanded': 'false',
                }).appendTo(this._component);
                this._component.btn.icon = $(document.createElement('i')).attr({
                    'class': 'fs-4 bi bi-' + this._properties.icon,
                    'style': 'height: 2.25rem !important;width: 1.5rem !important;',
                }).appendTo(this._component.btn);
                this._component.btn.badge = $(document.createElement('span')).attr({
                    'class': 'position-absolute top-25 start-75 translate-middle border border-light rounded-circle d-none',
                    'style': 'padding: 8px;',
                }).appendTo(this._component.btn);

                // Create Menu
                this._component.menu = $(document.createElement('ul')).attr({
                    'class': 'dropdown-menu dropdown-list dropdown-menu-end pb-0',
                    'style': 'min-width: 400px;max-width: 500px;',
                }).appendTo(this._component);

                // Create Header
                this._component.menu.header = $(document.createElement('li')).appendTo(this._component.menu);
                this._component.menu.header.title = $(document.createElement('h5')).addClass('py-2 px-3 m-0 cursor-default d-flex justify-content-center align-items-center').appendTo(this._component.menu.header);
                this._component.menu.header.title.label = $(document.createElement('span')).text('Messages').appendTo(this._component.menu.header.title);
                this._component.menu.header.title.count = $(document.createElement('span')).addClass('badge rounded-pill ms-2 d-none').appendTo(this._component.menu.header.title);

                // Create Seperators
                this._component.menu.seperator = {};
                this._component.menu.seperator = $(document.createElement('li')).appendTo(this._component.menu);
                this._component.menu.seperator.hr = $(document.createElement('hr')).addClass('dropdown-divider m-0').appendTo(this._component.menu.seperator);

                // Create Items List
                this._component.menu.list = $(document.createElement('div')).attr({
                    'class': 'overflow-auto',
                    'style': 'max-height: 500px;',
                }).appendTo(this._component.menu);

                // Create Footer
                this._component.menu.footer = $(document.createElement('li')).appendTo(this._component.menu);
                this._component.menu.footer.btn = $(document.createElement('button')).attr({
                    'class': 'dropdown-item text-center py-2 rounded-bottom btn btn-link',
                    'type': 'button',
                }).appendTo(this._component.menu.footer);
                this._component.menu.footer.btn.label = $(document.createElement('small')).text('View All').appendTo(this._component.menu.footer.btn);

                // Set Icon
                if(this._properties.icon === null){
                    this._component.btn.icon.remove();
                }

                // Set Color
                if(this._properties.color){
                    this._component.btn.badge.addClass('text-bg-' + this._properties.color);
                    this._component.menu.header.title.count.addClass('text-bg-' + this._properties.color);
                }

                // Add Callback
                self._component.menu.footer.btn.on('click',function(){
                    if(typeof self._properties.callback.viewAll === 'function'){
                        self._properties.callback.viewAll();
                    }
                });
            }

            count(){

                // Set Self
                const self = this;

                // Count the number of new notifications
                let count = this._component.find('[data-isRead="false"]').length;

                // Set Count
                this._component.menu.header.title.count.text(count);

                // Show Badge
                if(count > 0){
                    this._component.menu.header.title.count.removeClass('d-none');
                    this._component.btn.badge.removeClass('d-none');
                    this._component.btn.addClass('animate-bounce');
                } else {
                    this._component.menu.header.title.count.addClass('d-none');
                    this._component.btn.badge.addClass('d-none');
                    this._component.btn.removeClass('animate-bounce');
                }

                // Return Count
                return count;
            }

            add(param1 = null, param2 = null){

                // Set Self
                const self = this;

                let options = {};
                let callback = null;

                let properties = {};

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Configure Options
                for(const [key, value] of Object.entries(this._properties.properties)){
                    if(typeof properties[key] === 'undefined'){
                        properties[key] = value;
                    }
                }
                for(const [key, value] of Object.entries(options)){
                    if(typeof properties[key] !== 'undefined'){
                        switch(key){
                            case"class":
                                for(const [section, classes] of Object.entries(value)){
                                    if(properties[key][section] != null){
                                        properties[key][section] += ' ' + classes;
                                    } else {
                                        properties[key][section] = classes;
                                    }
                                }
                                break;
                            default:
                                properties[key] = value;
                                break;
                        }
                    }
                }

                // Set ID
                let id = this._count();

                // Create Item
                let item = $(document.createElement('li')).attr({
                    'id': this._component.id + 'item' + id,
                }).prependTo(this._component.menu.list);
                item.id = item.attr('id');
                item.properties = properties;

                // Create Button
                item.btn = $(document.createElement('button')).attr({}).addClass('dropdown-item d-flex align-items-center py-2').attr('type','button').appendTo(item);

                // Add Icon
                item.btn.icon = $(document.createElement('div')).addClass('me-3').appendTo(item.btn);
                item.btn.icon.frame = $(document.createElement('div')).attr({}).addClass('d-flex align-items-center justify-content-center rounded-circle text-bg-primary').css({"width":"48px","height":"48px"}).appendTo(item.btn.icon);

                // Add Label
                item.btn.label = $(document.createElement('div')).addClass('d-flex flex-column align-items-justify').appendTo(item.btn);
                item.btn.label.text = $(document.createElement('span')).addClass('text-wrap').text(properties.label).appendTo(item.btn.label);
                item.btn.label.meta = $(document.createElement('small')).addClass('text-muted').appendTo(item.btn.label);
                item.btn.label.name = $(document.createElement('span')).text(properties.name).appendTo(item.btn.label.meta);
                item.btn.label.timeago = $(document.createElement('time')).attr({}).addClass('timeago ms-2').attr('data-bs-toggle','tooltip').appendTo(item.btn.label.meta);

                // Add Seperator
                item.seperator = $(document.createElement('li')).insertAfter(item);
                item.seperator.hr = $(document.createElement('hr')).addClass('dropdown-divider m-0').appendTo(item.seperator);

                // Configure Avatar
                item.btn.icon.frame.avatar = this._builder.Component(
                    "avatar",
                    item.btn.icon.frame,
                    {
                        class: {
                            object: "rounded-circle",
                        },
                        email: properties.email,
                        size: "48px",
                    },
                    function(avatar,component){},
                );

                // Configure Date Time
                let datetime = null;
                if(properties.datetime !== null){
                    datetime = new Date(properties.datetime);
                } else {
                    datetime = new Date();
                }
                item.btn.label.timeago.attr({
                    'title': datetime.toLocaleString(),
                    'datetime': datetime.toLocaleString(),
                    'data-bs-title': datetime.toLocaleString(),
                    'data-bs-toggle': 'tooltip',
                    'data-bs-placement': 'top',
                }).text(datetime.toLocaleString());
                item.btn.label.timeago.bootstrap = new bootstrap.Tooltip(item.btn.label.timeago);
                setTimeout(function(){ item.btn.label.timeago.timeago(); }, 0);

                // Configure isRead
                item.attr('data-isRead',properties.isRead);
                if(!properties.isRead){
                    item.addClass('blink-primary');
                    item.btn.label.text.addClass('fw-bold');
                }

                // Set Item Class
                if(properties.class.item){
                    item.addClass(properties.class.item);
                }

                // Add a read function
                item.read = function(callback = null){
                    if(item.attr('data-isRead') === 'false'){
                        item.attr('data-isRead', 'true').removeClass('blink-primary');
                        item.btn.label.text.removeClass('fw-bold');
                        self.count();
                        if(typeof self._properties.callback.onRead === 'function'){
                            self._properties.callback.onRead(item,self,self._component);
                        }
                        if(typeof properties.onRead === 'function'){
                            properties.onRead(item,self,self._component);
                        }
                    }
                    if(typeof callback === 'function'){
                        callback(item,self,self._component);
                    }
                };

                // Add onRead Callback
                if(!properties.isRead){
                    item.timer;
                    item.hover(function() {
                        item.timer = setTimeout(function() {
                            item.read();
                        }, self._properties.onReadDelay);
                    }, function() {
                        clearTimeout(item.timer);
                    });
                }

                // Add click Callback
                item.click(function(){
                    if(typeof properties.click === 'function'){
                        properties.click(item,self,self._component);
                    }
                });

                // Add View All Click Callback
                this._component.menu.footer.btn.on('click',function(){
                    if(typeof self._properties.callback.click === 'function'){
                        self._properties.callback.click(item,self,self._component);
                    }
                });

                // Execute Callback
                if(typeof callback === 'function'){
                    callback(item,this);
                }

                // Set Count
                this.count();

                // Return Object
                return this;
            }
        },
        toast: class extends this.ComponentClass {

            _init(){
                this._properties = {
                    class: {
                        component: null,
                    },
                    callback: {
                        click: null,
                    },
                    position: 'bottom-end',
                    properties: {
                        class: {
                            item: null,
                        },
                        callback: {
                            click: null,
                        },
                        color: null,
                        icon: null,
                        title: null,
                        body: null,
                        datetime: null,
                        delay: 5000,
                        autohide: true,
                        animation: true,
                        dismissible: true,
                    },
                };
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'toasts' + self._id,
                    'class': 'toast-container position-fixed p-3',
                });
                this._component.id = this._component.attr('id');

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }

                // Set Position
                if(this._properties.position){
                    this.position(this._properties.position);
                }
            }

            position(position){

                // Remove existing positions
                this._component.removeClass('top-0 start-0 top-0 start-50 translate-middle-x top-0 end-0 bottom-0 start-0 bottom-0 start-50 translate-middle-x bottom-0 end-0');

                // Set Position
                if(typeof position === 'string'){
                    switch(position){
                        case"top-start":
                            this._component.addClass('top-0 start-0');
                            break;
                        case"top-center":
                            this._component.addClass('top-0 start-50 translate-middle-x');
                            break;
                        case"top-end":
                            this._component.addClass('top-0 end-0');
                            break;
                        case"bottom-start":
                            this._component.addClass('bottom-0 start-0');
                            break;
                        case"bottom-center":
                            this._component.addClass('bottom-0 start-50 translate-middle-x');
                            break;
                        case"bottom-end":
                            this._component.addClass('bottom-0 end-0');
                            break;
                    }
                }
            }

            add(param1 = null, param2 = null){

                // Set Self
                const self = this;

                let options = {};
                let callback = null;

                let properties = {};

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Configure Options
                for(const [key, value] of Object.entries(this._properties.properties)){
                    if(typeof properties[key] === 'undefined'){
                        properties[key] = value;
                    }
                }
                for(const [key, value] of Object.entries(options)){
                    if(typeof properties[key] !== 'undefined'){
                        switch(key){
                            case"callback":
                                if(typeof properties[key] !== 'undefined'){
                                    for(const [k, v] of Object.entries(value)){
                                        if(typeof properties[key][k] !== 'undefined'){
                                            properties[key][k] = v;
                                        }
                                    }
                                }
                                break;
                            case"class":
                                for(const [section, classes] of Object.entries(value)){
                                    if(properties[key][section] != null){
                                        properties[key][section] += ' ' + classes;
                                    } else {
                                        properties[key][section] = classes;
                                    }
                                }
                                break;
                            default:
                                properties[key] = value;
                                break;
                        }
                    }
                }

                // Set ID
                let id = this._count();

                // Create Toast
                let toast = $(document.createElement('div')).attr({
                    'id': this._component.id + 'toast' + id,
                    'class': 'toast show animate-wobble-once animate-pulse-hover',
                    'role': 'alert',
                    'aria-live': 'assertive',
                    'aria-atomic': 'true',
                }).prependTo(this._component);
                toast.id = toast.attr('id');
                toast.properties = properties;

                // Create Toast Header
                toast.header = $(document.createElement('div')).addClass('toast-header').appendTo(toast);
                toast.header.icon = $(document.createElement('i')).addClass('me-1 bi bi-' + properties.icon).appendTo(toast.header);
                toast.header.title = $(document.createElement('strong')).addClass('me-auto').html(self._builder.Parser.parse(properties.title)).appendTo(toast.header);
                toast.header.time = $(document.createElement('small')).addClass('text-muted').appendTo(toast.header);
                toast.header.time.ago = $(document.createElement('time')).attr({
                    'class': 'timeago',
                    'data-bs-toggle': 'tooltip',
                }).appendTo(toast.header.time);
                toast.header.close = $(document.createElement('button')).attr({
                    'class': 'btn-close',
                    'type': 'button',
                    'data-bs-dismiss': 'toast',
                    'aria-label': 'Close'
                }).appendTo(toast.header);

                // Create Toast Body
                toast.body = $(document.createElement('div')).addClass('toast-body').html(self._builder.Parser.parse(properties.body)).appendTo(toast);

                // Configure Color
                if(properties.color){
                    toast.addClass('text-bg-' + properties.color);
                }

                // Configure Icon
                if(properties.icon === null){
                    toast.header.icon.remove();
                }

                // Configure Title
                if(properties.title === null){
                    toast.header.title.remove();
                }

                // Configure Body
                if(properties.body === null){
                    toast.body.remove();
                }

                // Configure Date Time
                let datetime = null;
                if(properties.datetime !== null){
                    datetime = new Date(properties.datetime);
                } else {
                    datetime = new Date();
                }
                toast.header.time.ago.attr({
                    'datetime': datetime.toLocaleString(),
                }).text(datetime.toLocaleString());
                setTimeout(function(){ toast.header.time.ago.timeago(); }, 0);

                // Configure Animation
                if(properties.animation){
                    toast.addClass('fade');
                }

                // Configure Autohide
                if(properties.autohide){
                    toast.timer = setTimeout(function(){
                        toast.addClass('opacity-0').delay(500).queue(function(){
                            toast.removeClass('show opacity-0').addClass('hide').dequeue();
                        });
                    }, properties.delay);
                }

                // Configure Dismissible
                if(!properties.dismissible){
                    toast.header.close.remove();
                }

                // Execute Callback
                if(typeof callback === 'function'){
                    callback(toast,this);
                }

                // Return Object
                return this;
            }
        },
        helper: class extends this.UtilityClass {

            // Log all event names of an element
            logEvents(element){
                // List of common events
                var events = [
                    'abort',
                    'afterprint',
                    'animationend',
                    'animationiteration',
                    'animationstart',
                    'beforeprint',
                    'beforeunload',
                    'blur',
                    'canplay',
                    'canplaythrough',
                    'change',
                    'click',
                    'contextmenu',
                    'copy',
                    'cut',
                    'dblclick',
                    'drag',
                    'dragend',
                    'dragenter',
                    'dragleave',
                    'dragover',
                    'dragstart',
                    'drop',
                    'durationchange',
                    'ended',
                    'error',
                    'focus',
                    'focusin',
                    'focusout',
                    'fullscreenchange',
                    'fullscreenerror',
                    'hashchange',
                    'input',
                    'invalid',
                    'keydown',
                    'keypress',
                    'keyup',
                    'load',
                    'loadeddata',
                    'loadedmetadata',
                    'loadstart',
                    'message',
                    'mousedown',
                    'mouseenter',
                    'mouseleave',
                    'mousemove',
                    'mouseout',
                    'mouseover',
                    'mouseup',
                    'mousewheel',
                    'offline',
                    'online',
                    'open',
                    'pagehide',
                    'pageshow',
                    'paste',
                    'pause',
                    'play',
                    'playing',
                    'popstate',
                    'progress',
                    'ratechange',
                    'resize',
                    'reset',
                    'scroll',
                    'search',
                    'seeked',
                    'seeking',
                    'select',
                    'show',
                    'stalled',
                    'submit',
                    'suspend',
                    'timeupdate',
                    'toggle',
                    'touchcancel',
                    'touchend',
                    'touchmove',
                    'touchstart',
                    'transitionend',
                    'unload',
                    'volumechange',
                    'waiting',
                    'wheel'
                ];

                // Add Listeners
                $.each(events, function(i, eventName) {
                    element.on(eventName, function(e) {
                        console.log('Event type:', e.type);
                    });
                });
            }

            // Convert a string into ASCII
            stringToAscii(string){

                // Set the Base62 character set
                const Base62 = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

                // Convert string into a string
                string = string.toString();

                // Convert each character in the string to its ASCII value and form a large integer
                let numericValue = BigInt(0);

                for (let i = 0; i < string.length; i++) {
                    numericValue = numericValue * BigInt(256) + BigInt(string.charCodeAt(i));
                }

                // Convert the large integer to a Base62 string
                let ascii = '';
                while (numericValue > 0) {
                    let index = Number(numericValue % BigInt(62));
                    ascii = Base62[index] + ascii;
                    numericValue = numericValue / BigInt(62);
                }

                // Return the result
                return ascii;
            }

            // Retrieve the favicon of a domain
            favicon(domain) {

                // Check if the domain is a string
                if (typeof domain !== 'string') {
                    throw new Error('Domain must be a string');
                }

                // Sanitize the domain removing any protocol and any path
                domain = domain.replace('https://', '').replace('http://', '').split('/')[0];

                // DuckDuckGo's API for fetching favicons
                const faviconUrl = `https://icons.duckduckgo.com/ip3/${domain}.ico`;

                return faviconUrl;
            }

            // Get the value of a query string from a URL
            getParameterByName(name, url = window.location.href) {
                name = name.replace(/[\[\]]/g, "\\$&");
                var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
                    results = regex.exec(url);
                if (!results) return null;
                if (!results[2]) return '';
                return decodeURIComponent(results[2].replace(/\+/g, " "));
            }

            // Generate a UUID
            uuid() {
                let d = new Date().getTime();

                // If available, use high-precision timer (performance.now) to reduce collisions
                if (typeof performance !== 'undefined' && typeof performance.now === 'function') {
                    d += performance.now();
                }

                // The template includes placeholders for each hex digit.
                // The "1" in the 3rd block is just to indicate "version 1 style"
                // but again, this is not strictly RFC 4122-compliant.
                const uuidTemplate = 'xxxxxxxx-xxxx-1xxx-yxxx-xxxxxxxxxxxx';

                return uuidTemplate.replace(/[xy]/g, function (c) {
                    const r = (d + Math.random() * 16) % 16 | 0;
                    d = Math.floor(d / 16);

                    // For the 'y' placeholder, force bits 8, 9, A, or B
                    if (c === 'y') {
                        return (r & 0x3 | 0x8).toString(16);
                    }
                    return r.toString(16);
                });
            }

            // Convert float to currency
            currency(value){
                return '$ ' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }

            // Normalize value
            normalize(value, type="float"){
                switch(type){
                    case"float":
                        return parseFloat(value);
                    case"int":
                        return parseInt(value);
                    case"nfd":
                        return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    default:
                        return value;
                }
            }

            // Check if a string is a valid base64 string
            isBase64(str) {
                try {
                    return btoa(atob(str)) === str;
                } catch (err) {
                    return false;
                }
            }

            // Convert URL to Base64
            async urlToBase64(url){
                const blob = await fetch(url,{credentials: 'include'}).then(r => r.blob());
                return await new Promise(res => {
                    const fr = new FileReader();
                    fr.onloadend = () => res(fr.result);
                    fr.readAsDataURL(blob);
                });
            }

            // Create Emphasis on element
            emphasis(element = null){

                // If the element is null, remove any existing emphasis
                if(element === null){
                    $('.emphasized-element').removeClass('emphasized-element');
                    $('.emphasized-overlay').remove();
                    return true;
                }

                // If the element is a jQuery object
                if(!element instanceof jQuery){
                    element = $(element);
                }

                // Check if element is in DOM
                if(element.length <= 0){
                    return false;
                }

                // If the overlay already exists, remove it
                if($('.emphasized-overlay').length <= 0){

                    // Append the overlay div
                    $('body').append('<div class="emphasized-overlay"></div>');
                }

                // Clear any previous emphasized elements
                $('.emphasized-element').removeClass('emphasized-element');

                // Add the emphasized class to the target element
                $(element).addClass('emphasized-element');
            }

            // Generate a random number between min and max
            randomNumber(min = -10, max = 10){
                return Math.floor(Math.random() * (max - min + 1) + min);
            }

            // Validate an email address
            validateEmail($email) {
                var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/
                return ( $email.length > 0 && emailReg.test($email))
            }

            // Check if a value is in an array
            inArray(needle, haystack) {
                var length = haystack.length;
                for(var i = 0; i < length; i++) {
                if(haystack[i] == needle) return true;
                }
                return false;
            }

            // Format a number of bytes into a human readable string
            formatBytes(bytes, decimals = 2) {
                if (!+bytes) return '0 Bytes'
                const k = 1024
                const dm = decimals < 0 ? 0 : decimals
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
                const i = Math.floor(Math.log(bytes) / Math.log(k))
                return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
            }

            // Copy a string or object content to the clipboard
            copyToClipboard(object){
                if(typeof object !== 'undefined' && typeof object !== null && typeof object !== 'function'){
                    let string = ''
                    let input = $(document.createElement('textarea')).appendTo('body')
                    if(typeof object === 'object'){ string = object.text(); }
                    if(typeof object === 'number'){ string = object.toString(); }
                    if(typeof object === 'boolean'){ string = object.toString(); }
                    if(typeof object === 'string'){ string = object; }
                    input.val(string).select();
                    document.execCommand("copy");
                    navigator.clipboard.writeText(input.val());
                    input.remove();
                }
            }

            // Format a phone number
            formatPhoneNumber(phoneNumberString) {
                var cleaned = ('' + phoneNumberString).replace(/\D/g, '')
                var match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/)
                if (match) {
                return '(' + match[1] + ') ' + match[2] + '-' + match[3]
                }
                return null
            }

            // Convert a string containing HTML entities to regular characters
            htmlEntities(str) {
                return str.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
                   return '&#' + i.charCodeAt(0) + ';';
                });
            }

            // Check if a variable is constant
            isConstant(variable) {
                const descriptor = Object.getOwnPropertyDescriptor(Object.getPrototypeOf(variable), Object.keys(variable)[0]);
                return descriptor && descriptor.writable === false;
            }

            // Format a string to a slug
            formatSlug (value) {
                return value
                    .toLowerCase()
                    .replace(/-+/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/[^a-z0-9-]/g, '');
            };

            // Check if the current device is a mobile device
            isMobileDevice() {
                const userAgent = navigator.userAgent;
                var mobileDeviceUserAgents = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i;
                return mobileDeviceUserAgents.test(userAgent);
            }

            // Function to retrieve the available Bootstrap Icons
            bootstrapIcons() {
                const styleSheets = document.styleSheets;
                var icons = [];

                for (const styleSheet of styleSheets) {
                    const rules = styleSheet.rules || styleSheet.cssRules;

                    for (const rule of rules) {
                        if(typeof rule.selectorText === 'undefined'){ continue; }

                        if (rule.selectorText.startsWith(".bi-")) {

                            // Remove ".bi-" from the icon name
                            const iconName = rule.selectorText.slice(4);

                            // Remove "::before" from the icon name
                            const cleanedIconName = iconName.replace("::before", "");

                            // Check if duplicate
                            if(!icons.includes(cleanedIconName)){
                                icons.push(cleanedIconName);
                            }
                        }
                    }
                }

                return icons;
            }

            // Function to retrieve the available Bootstrap Text Backgrounds
            bootstrapTextBg() {
                const styleSheets = document.styleSheets;
                var colors = [];

                for (const styleSheet of styleSheets) {
                    const rules = styleSheet.rules || styleSheet.cssRules;

                    for (const rule of rules) {
                        if(typeof rule.selectorText === 'undefined'){ continue; }

                        if (rule.selectorText.startsWith(".text-bg-")) {

                            // Remove ".text-bg-" from the color name
                            const colorName = rule.selectorText.slice(9);

                            // Remove "::before" from the icon name
                            const cleanedColorName = colorName.replace("::before", "").replace(" a", "").replace(" .text-muted", "");

                            // Check if duplicate
                            if(!colors.includes(cleanedColorName)){
                                colors.push(cleanedColorName);
                            }
                        }
                    }
                }

                return colors;
            }

            // Convert to Markdown
            htmlToMarkdown(html) {
                const converter = new TurndownService();

                // Use '#' for headers
                converter.addRule('heading', {
                    filter: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
                    replacement: function(content, node) {
                        var hLevel = node.nodeName.charAt(1);
                        var hPrefix = '';
                        for(var i=0; i<hLevel; i++){
                            hPrefix += '#';
                        }
                        return '\n\n' + hPrefix + ' ' + content + '\n\n';
                    }
                });

                // Use triple backticks for code blocks
                converter.addRule('codeBlock', {
                    filter: 'pre',
                    replacement: function(content) {
                        return '\n\n```' + '\n' + content + '\n' + '```\n\n';
                    }
                });

                return converter.turndown(html);
            }

            // Convert to HTML
            markdownToHTML(markdown) {
                const converter = new showdown.Converter();
                return converter.makeHtml(markdown);
            }

            // Check the OS Type
            detectOperatingSystem() {
                const userAgent = navigator.userAgent;

                if (/iPad|iPhone|iPod/.test(userAgent)) {
                    return 'iOS';
                }
                if (/Android/.test(userAgent)) {
                    return 'Android';
                }
                if (/Mac OS X/.test(userAgent)) {
                    return 'macOS';
                }
                if (/Windows NT/.test(userAgent)) {
                    return 'Windows';
                }
                if (/Linux/.test(userAgent)) {
                    return 'Linux';
                }
                return 'Unknown OS';
            }

            // Convert to MD5
            md5(string, callback = null) {
                function MD5(d) {
                    return M(V(Y(X(d), 8 * d.length)))
                }
                function M(d) {
                    for (var _, m = '0123456789abcdef', f = '', r = 0; r < d.length; r++) {
                        _ = d.charCodeAt(r)
                        f += m.charAt(_ >>> 4 & 15) + m.charAt(15 & _)
                    }
                    return f
                }
                function X(d) {
                    var _ = Array(d.length >> 2)
                    for (var m = 0; m < _.length; m++) {
                        _[m] = 0
                    }
                    for (m = 0; m < 8 * d.length; m += 8) {
                        _[m >> 5] |= (255 & d.charCodeAt(m / 8)) << (m % 32)
                    }
                    return _
                }
                function V(d) {
                    var _ = ''
                    for (var m = 0; m < 32 * d.length; m += 8) {
                        _ += String.fromCharCode((d[m >> 5] >>> (m % 32)) & 255)
                    }
                    return _
                }
                function Y(d, _) {
                    d[_ >> 5] |= 128 << (_ % 32)
                    d[14 + ((_ + 64 >>> 9) << 4)] = _
                    var m = 1732584193
                    var f = -271733879
                    var r = -1732584194
                    var i = 271733878
                    for (var n = 0; n < d.length; n += 16) {
                        var h = m
                        var t = f
                        var g = r
                        var e = i
                        f = md5ii(f = md5ii(f = md5ii(f = md5ii(
                            f = md5hh(f = md5hh(f = md5hh(f = md5hh(
                                f = md5gg(f = md5gg(f = md5gg(f = md5gg(
                                    f = md5ff(f = md5ff(f = md5ff(f = md5ff(
                                        f,
                                        r = md5ff(r, i = md5ff(i, m = md5ff(m, f, r, i, d[n + 0], 7, -680876936), f, r, d[n + 1], 12, -389564586), m, f, d[n + 2], 17, 606105819),
                                        i, m, d[n + 3], 22, -1044525330
                                    ),
                                    r = md5ff(r, i = md5ff(i, m = md5ff(m, f, r, i, d[n + 4], 7, -176418897), f, r, d[n + 5], 12, 1200080426), m, f, d[n + 6], 17, -1473231341),
                                    i, m, d[n + 7], 22, -45705983
                                ),
                                r = md5ff(r, i = md5ff(i, m = md5ff(m, f, r, i, d[n + 8], 7, 1770035416), f, r, d[n + 9], 12, -1958414417), m, f, d[n + 10], 17, -42063),
                                i, m, d[n + 11], 22, -1990404162
                            ),
                            r = md5ff(r, i = md5ff(i, m = md5ff(m, f, r, i, d[n + 12], 7, 1804603682), f, r, d[n + 13], 12, -40341101), m, f, d[n + 14], 17, -1502002290),
                            i, m, d[n + 15], 22, 1236535329
                        ),
                        r = md5gg(r, i = md5gg(i, m = md5gg(m, f, r, i, d[n + 1], 5, -165796510), f, r, d[n + 6], 9, -1069501632), m, f, d[n + 11], 14, 643717713),
                        i, m, d[n + 0], 20, -373897302
                        ),
                        r = md5gg(r, i = md5gg(i, m = md5gg(m, f, r, i, d[n + 5], 5, -701558691), f, r, d[n + 10], 9, 38016083), m, f, d[n + 15], 14, -660478335),
                        i, m, d[n + 4], 20, -405537848
                        ),
                        r = md5gg(r, i = md5gg(i, m = md5gg(m, f, r, i, d[n + 9], 5, 568446438), f, r, d[n + 14], 9, -1019803690), m, f, d[n + 3], 14, -187363961),
                        i, m, d[n + 8], 20, 1163531501
                        ),
                        r = md5gg(r, i = md5gg(i, m = md5gg(m, f, r, i, d[n + 13], 5, -1444681467), f, r, d[n + 2], 9, -51403784), m, f, d[n + 7], 14, 1735328473),
                        i, m, d[n + 12], 20, -1926607734
                        ),
                        r = md5hh(r, i = md5hh(i, m = md5hh(m, f, r, i, d[n + 5], 4, -378558), f, r, d[n + 8], 11, -2022574463), m, f, d[n + 11], 16, 1839030562),
                        i, m, d[n + 14], 23, -35309556
                        ),
                        r = md5hh(r, i = md5hh(i, m = md5hh(m, f, r, i, d[n + 1], 4, -1530992060), f, r, d[n + 4], 11, 1272893353), m, f, d[n + 7], 16, -155497632),
                        i, m, d[n + 10], 23, -1094730640
                        ),
                        r = md5hh(r, i = md5hh(i, m = md5hh(m, f, r, i, d[n + 13], 4, 681279174), f, r, d[n + 0], 11, -358537222), m, f, d[n + 3], 16, -722521979),
                        i, m, d[n + 6], 23, 76029189
                        ),
                        r = md5hh(r, i = md5hh(i, m = md5hh(m, f, r, i, d[n + 9], 4, -640364487), f, r, d[n + 12], 11, -421815835), m, f, d[n + 15], 16, 530742520),
                        i, m, d[n + 2], 23, -995338651
                        ),
                        r = md5ii(r, i = md5ii(i, m = md5ii(m, f, r, i, d[n + 0], 6, -198630844), f, r, d[n + 7], 10, 1126891415), m, f, d[n + 14], 15, -1416354905),
                        i, m, d[n + 5], 21, -57434055
                        ),
                        r = md5ii(r, i = md5ii(i, m = md5ii(m, f, r, i, d[n + 12], 6, 1700485571), f, r, d[n + 3], 10, -1894986606), m, f, d[n + 10], 15, -1051523),
                        i, m, d[n + 1], 21, -2054922799
                        ),
                        r = md5ii(r, i = md5ii(i, m = md5ii(m, f, r, i, d[n + 8], 6, 1873313359), f, r, d[n + 15], 10, -30611744), m, f, d[n + 6], 15, -1560198380),
                        i, m, d[n + 13], 21, 1309151649
                        ),
                        r = md5ii(r, i = md5ii(i, m = md5ii(m, f, r, i, d[n + 4], 6, -145523070), f, r, d[n + 11], 10, -1120210379), m, f, d[n + 2], 15, 718787259),
                        i, m, d[n + 9], 21, -343485551
                        )
                        m = safeadd(m, h)
                        f = safeadd(f, t)
                        r = safeadd(r, g)
                        i = safeadd(i, e)
                    }
                    return [m, f, r, i]
                }
                function md5cmn(d, _, m, f, r, i) {
                    return safeadd(bitrol(safeadd(safeadd(_, d), safeadd(f, i)), r), m)
                }
                function md5ff(d, _, m, f, r, i, n) {
                    return md5cmn((_ & m) | ((~_) & f), d, _, r, i, n)
                }
                function md5gg(d, _, m, f, r, i, n) {
                    return md5cmn((_ & f) | (m & ~f), d, _, r, i, n)
                }
                function md5hh(d, _, m, f, r, i, n) {
                    return md5cmn(_ ^ m ^ f, d, _, r, i, n)
                }
                function md5ii(d, _, m, f, r, i, n) {
                    return md5cmn(m ^ (_ | ~f), d, _, r, i, n)
                }
                function safeadd(d, _) {
                    var m = (d & 65535) + (_ & 65535)
                    return ((d >> 16) + (_ >> 16) + (m >> 16) << 16) | (m & 65535)
                }
                function bitrol(d, _) {
                    return (d << _) | (d >>> (32 - _))
                }
                function MD5Unicode(buffer) {
                    if (!(buffer instanceof Uint8Array)) {
                        buffer = new TextEncoder().encode(
                            typeof buffer === 'string' ? buffer : JSON.stringify(buffer)
                        )
                    }
                    var binary = []
                    var bytes = new Uint8Array(buffer)
                    for (var i = 0; i < bytes.byteLength; i++) {
                        binary.push(String.fromCharCode(bytes[i]))
                    }
                    return MD5(binary.join(''))
                }

                // Compute the MD5 of the input string (or buffer)
                var hash = MD5Unicode(string)

                // If a callback is provided, call it with the hash
                if (callback) {
                    callback(hash)
                }

                // Also return the generated MD5 string
                return hash
            }

            // Generate a Gravatar URL
            gravatar(param1 = null, param2 = null){

                const self = this;

                let email = null;
                let options = {};

                // Set email, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'string' || param instanceof jQuery) {
                            email = param;
                        } else if (typeof param === 'object') {
                            options = param;
                        }
                    }
                });

                let properties = {
                    extension: false, //in request
                    size: false, //s
                    default: 'mp', //d
                    force: false, //f
                    rating: false, //r
                };

                let extensions = ['jpg','jpeg','png','gif'];
                let defaults = ['404','mp','identicon','monsterid','wavatar','retro','robohash','blank'];
                let ratings = ['g','pg','r','x'];
                let api = 'https://www.gravatar.com/avatar/';

                // Configure Options
                for(const [key, value] of Object.entries(options)){
                    if(typeof properties[key] !== 'undefined'){
                        switch(key){
                            default:
                                properties[key] = value;
                                break;
                        }
                    }
                }

                // Set URL
                let url = api + self.md5(email);

                // Configure URL
                for(const [key, value] of Object.entries(properties)){
                    if(value){
                        switch(key){
                            case"extension":
                                if(self.inArray(value,extensions)){
                                    url += '.' + value
                                }
                                break;
                            case"size":
                                if(url.toLowerCase().indexOf("?") >= 0){
                                    url +=  '&s=' + parseInt(value)
                                } else {
                                    url +=  '?s=' + parseInt(value)
                                }
                                break;
                            case"default":
                                if(self.inArray(value,defaults)){
                                    if(url.toLowerCase().indexOf("?") >= 0){
                                        url +=  '&d=' + value
                                    } else {
                                        url +=  '?d=' + value
                                    }
                                }
                                break;
                            case"force":
                                if(url.toLowerCase().indexOf("?") >= 0){
                                    url +=  '&f=y'
                                } else {
                                    url +=  '?f=y'
                                }
                                break;
                            case"rating":
                                if(self.inArray(value,ratings)){
                                    if(url.toLowerCase().indexOf("?") >= 0){
                                        url +=  '&r=' + value
                                    } else {
                                        url +=  '?r=' + value
                                    }
                                }
                                break;
                        }
                    }
                }

                // Return URL
                return url
            }

            // Check if a variable is a class
            isClass(input) {
                return typeof input === 'function' && /^class\s/.test(Function.prototype.toString.call(input));
            }

            // Check if an element is overflowing
            isOverflowing(element){
                return element[0].scrollWidth > element[0].clientWidth || element[0].scrollHeight > element[0].clientHeight;
            }

            // Convert a string to camel case
            ucwords(str){
                return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
                    return $1.toUpperCase();
                });
            }
        },
        storage: class extends this.UtilityClass {

            // Properties
            _db = null;
            _callbacks = [];
            _currentKey = null;
            _exclude = ["breadcrumbs"];

            constructor(builder) {
                super(builder);
            }

            async _ensureReady() {
                if (this._db) return;
                this._db = await openIDB();
            }

            _store(mode = 'readonly') {
                return this._db.transaction('kv', mode).objectStore('kv');
            }

            async clear() {
                await this._ensureReady();
                const store = this._store('readwrite');

                return new Promise((res, rej) => {
                    const req = store.openCursor();
                    req.onsuccess = e => {
                        const cursor = e.target.result;
                        if (!cursor) return res();
                        if (!this._exclude.includes(cursor.key)) cursor.delete();
                        cursor.continue();
                    };
                    req.onerror = () => rej(req.error);
                });
            }

            add(cb) {
                if ($.isFunction(cb)) this._callbacks.push(cb);
            }

            setKey(k) {
                if (k) this._currentKey = k;
            }

            getKey() {
                if (this._currentKey) return this._currentKey;
                const path = window.location.pathname
                    .replace(/^\/+|\/+$/g, '')
                    .replace(/\//g, ':')
                    .replace(/^:+|:+$/g, '');
                const id = new URLSearchParams(location.search).get('id');
                return (path || 'index') + (id ? ':' + id : '');
            }

            async set(value, subkey = null, key = null) {
                await this._ensureReady();
                key = key || this.getKey();

                let object = subkey ? await this.get(null, key) : null;

                if (subkey) {
                    const path = subkey.split(':');
                    let current = (object && typeof object === 'object') ? object : {};
                    for (let i = 0; i < path.length - 1; i++) {
                        const seg = path[i];
                        if (typeof current[seg] !== 'object' || current[seg] === null)
                            current[seg] = {};
                        current = current[seg];
                    }
                    current[path[path.length - 1]] = value;
                    value = object;
                }

                await new Promise((res, rej) => {
                    const req = this._store('readwrite').put(JSON.stringify(value), key);
                    req.onsuccess = () => res();
                    req.onerror = () => rej(req.error);
                });

                for (const cb of this._callbacks) cb(value, subkey, key);
                return this;
            }

            async get(subkey = null, key = null) {
                await this._ensureReady();
                key = key || this.getKey();

                const value = await new Promise((res, rej) => {
                    const req = this._store().get(key);
                    req.onsuccess = () => res(req.result);
                    req.onerror = () => rej(req.error);
                });

                let object = value ? JSON.parse(value) : null;
                if (!subkey) return object;

                const path = subkey.split(':');
                let current = object;
                for (const seg of path) {
                    if (!current || typeof current !== 'object') return null;
                    current = current[seg];
                }

                return current ?? null;
            }

            async remove(key = null) {
                await this._ensureReady();
                key = key || this.getKey();
                const oldVal = await this.get(null, key);

                await new Promise((res, rej) => {
                    const req = this._store('readwrite').delete(key);
                    req.onsuccess = () => res();
                    req.onerror = () => rej(req.error);
                });

                for (const cb of this._callbacks) cb(null, null, key);
                return this;
            }
        },
        form: class extends this.ComponentClass {

            _inputs = {};

            _init(){
                this._properties = {
                    class:{
                        component: null,
                    },
                    callback:{
                        submit: function(form){},
                        val: function(values){ return values; },
                        init: function(form){},
                        onChange: function(form){},
                        reset: function(form){},
                        clear: function(form){},
                    },
                };
            }

            _timeout(){

                // Set Search
                this._builder.Search.set(this._component);
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('form')).attr({
                    'id': 'form' + this._id,
                    'class': '',
                    'method': 'post',
                    'autocomplete': 'off',
                    'enctype': 'multipart/form-data',
                });
                this._component.id = this._component.attr('id');

                // Reset Event
                this._component.on('reset', function(e){
                    self.reset();
                });

                // Submit Event
                this._component.on('submit', function(e){
                    e.preventDefault();
                    self.submit();
                });

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }
            }

            submit(){

                // Set Self
                const self = this;

                // Callback
                if(typeof this._properties.callback.submit === 'function'){
                    this._properties.callback.submit(this);
                }

                // Return
                return this;
            };

            reset(){

                // Set Self
                const self = this;

                // Reset Values
                for(const [key, input] of Object.entries(self._inputs)){
                    input.reset();
                }

                // Callback
                if(typeof self._properties.callback.reset === 'function'){
                    self._properties.callback.reset(self);
                }

                // Return
                return this;
            };

            focus(){

                // Set Self
                const self = this;

                // Set focus on form
                self._component.focus();

                // Return
                return this;
            }

            init(){

                // Set Self
                const self = this;

                // Reset Values
                for(const [key, input] of Object.entries(self._inputs)){
                    if(typeof input.init === 'function'){
                        input.init();
                    }
                }

                // Callback
                if(typeof self._properties.callback.init === 'function'){
                    self._properties.callback.init(self);
                }

                // Return
                return this;
            }

            clear(){

                // Set Self
                const self = this;

                // Reset Values
                for(const [key, input] of Object.entries(self._inputs)){
                    input.clear();
                }

                // Callback
                if(typeof self._properties.callback.clear === 'function'){
                    self._properties.callback.clear(self);
                }

                // Return
                return this;
            };

            onChange(){

                // Set Self
                const self = this;

                // Callback
                if(typeof self._properties.callback.onChange === 'function'){
                    self._properties.callback.onChange(self);
                }

                // Return
                return this;
            }

            val(values = null){

                // Set Self
                const self = this;

                // Set Values
                if(typeof values === 'object'){
                    if(values !== null){
                        for(const [key, value] of Object.entries(values)){
                            if(typeof self._inputs[key] !== 'undefined'){
                                self._inputs[key].val(value);
                            }
                        }
                    }
                } else {

                    // Check if values is a string and matches a key
                    if(typeof values === 'string' && typeof self._inputs[values] !== 'undefined'){
                        return self._inputs[values].val();
                    }
                }

                // Retrieve Values
                let object = {};
                for(const [key, input] of Object.entries(self._inputs)){
                    object[key] = input.val();
                }

                // Callback
                if(typeof self._properties.callback.val === 'function'){
                    object = self._properties.callback.val(object);
                }

                // Return
                return object;
            };

            add(type, param1 = null, param2 = null){

                // Set Self
                const self = this;

                // Set Properties
                let options = {
                    parent: this._component,
                };
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Create Input
                const input = this._builder.Input(type, this._component, options, callback);

                // Store Input
                if(input){
                    this._inputs[input.name()] = input;
                }

                // return Input
                return input;

                // Create Input
                switch(properties.type){
                    case 'clear':
                        field.input = $(document.createElement('button')).attr({
                            'id': field.id + 'clear',
                            'class': 'flex-grow-1 btn btn-' + properties.color,
                            'name': properties.name,
                            'type': 'button',
                            'value': properties.value,
                        }).html(field.label.html()).appendTo(field.group);
                        field.label.remove();
                        if(this._properties.class.label){
                            field.input.addClass(this._properties.class.label);
                        }
                        if(properties.class.label){
                            field.input.addClass(properties.class.label);
                        }
                        field.input.click(function(){
                            self.clear();
                        });
                        break;
                    case 'reset':
                        field.input = $(document.createElement('button')).attr({
                            'id': field.id + 'reset',
                            'class': 'flex-grow-1 btn btn-' + properties.color,
                            'name': properties.name,
                            'type': properties.type,
                            'value': properties.value,
                        }).html(field.label.html()).appendTo(field.group);
                        field.label.remove();
                        if(this._properties.class.label){
                            field.input.addClass(this._properties.class.label);
                        }
                        if(properties.class.label){
                            field.input.addClass(properties.class.label);
                        }
                        break;
                    case 'submit':
                        field.input = $(document.createElement('button')).attr({
                            'id': field.id + 'submit',
                            'class': 'flex-grow-1 btn btn-' + properties.color,
                            'name': properties.name,
                            'type': properties.type,
                            'value': properties.value,
                        }).html(field.label.html()).appendTo(field.group);
                        field.label.remove();
                        if(this._properties.class.label){
                            field.input.addClass(this._properties.class.label);
                        }
                        if(properties.class.label){
                            field.input.addClass(properties.class.label);
                        }
                        break;
                    case 'color':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'form-control form-control-color flex-grow-1',
                            'type': 'color',
                            'name': properties.name,
                        }).val(properties.value).appendTo(field.group);
                        // Execute onChange
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'ide':
                        field.input = $(document.createElement('div')).addClass('ide form-control p-0 flex-grow-1').appendTo(field.group);
                        field.input.lines = $(document.createElement('div')).addClass('ide-lines px-0').appendTo(field.input);
                        field.input.editor = $(document.createElement('textarea')).attr({
                            'id': field.id + 'input',
                            'class': 'ide-input',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).text(properties.value).appendTo(field.input);
                        field.input.val = function(value = null){
                            if(value !== null){
                                field.input.editor.val(value);
                                field.input.editor.trigger('propertychange');
                            }
                            return field.input.editor.val();
                        };
                        field.input.editor
                            .keydown(function(e) {
                                if(e.keyCode === 9) {
                                    e.preventDefault();

                                    var start = this.selectionStart;
                                    var end = this.selectionEnd;

                                    this.value = this.value.substring(0, start) + "\t" + this.value.substring(end);
                                    this.selectionStart = this.selectionEnd = start + 1;
                                }
                            })
                            .on('input propertychange', function() {
                                var lines = $(this).val().split('\n').length;
                                field.input.lines.empty();
                                for (var i = 1; i <= lines; i++) {
                                    field.input.lines.append(`<div class="p-0 px-2">${i}</div>`);
                                }
                            })
                            .trigger('propertychange')
                        field.del = function(startLine, endLine = startLine) {

                            // Get current lines from textarea
                            let lines = field.input.editor.val().split('\n');

                            // Ensure the specified lines are within the bounds of the current text
                            if (startLine < 1 || endLine > lines.length || startLine > endLine) {
                                console.warn('Invalid line range');
                                return;
                            }

                            // Remove the specified lines
                            lines.splice(startLine - 1, endLine - startLine + 1);

                            // Update the textarea with the modified text
                            field.input.editor.val(lines.join('\n'));
                            field.input.editor.trigger('propertychange'); // Update line numbers and other listeners
                        }
                        field.highlight = function(...args) {

                            if (args.length === 0) {

                                // If no arguments are provided, highlight all lines
                                field.input.lines.children().each(function () {
                                    $(this).addClass('text-bg-pink');
                                });
                            } else {

                                // Loop through each argument
                                args.forEach(arg => {
                                    if (Array.isArray(arg)) {

                                        // If the argument is an array, treat it as a range [start, end]
                                        let [startLine, endLine] = arg;
                                        for (let i = startLine; i <= endLine; i++) {
                                            if (i > 0 && i <= field.input.lines.children().length) {
                                                let lineElement = field.input.lines.children().eq(i - 1);
                                                if (!lineElement.hasClass('text-bg-pink')) {
                                                    lineElement.addClass('text-bg-pink');
                                                }
                                            }
                                        }
                                    } else {

                                        // If the argument is a single line number
                                        let line = arg;
                                        if (line > 0 && line <= field.input.lines.children().length) {
                                            let lineElement = field.input.lines.children().eq(line - 1);
                                            if (!lineElement.hasClass('text-bg-pink')) {
                                                lineElement.addClass('text-bg-pink');
                                            }
                                        }
                                    }
                                });
                            }
                        }
                        field.unhighlight = function (...args) {

                            if (args.length === 0) {

                                // If no arguments are provided, unhighlight all lines
                                field.input.lines.children().removeClass('text-bg-pink');
                            } else {

                                // Loop through each argument
                                args.forEach(arg => {
                                    if (Array.isArray(arg)) {

                                        // If the argument is an array, treat it as a range [start, end]
                                        let [startLine, endLine] = arg;
                                        for (let i = startLine; i <= endLine; i++) {
                                            if (i > 0 && i <= field.input.lines.children().length) {
                                                let lineElement = field.input.lines.children().eq(i - 1);
                                                if (lineElement.hasClass('text-bg-pink')) {
                                                    lineElement.removeClass('text-bg-pink');
                                                }
                                            }
                                        }
                                    } else {

                                        // If the argument is a single line number
                                        let line = arg;
                                        if (line > 0 && line <= field.input.lines.children().length) {
                                            let lineElement = field.input.lines.children().eq(line - 1);
                                            if (lineElement.hasClass('text-bg-pink')) {
                                                lineElement.removeClass('text-bg-pink');
                                            }
                                        }
                                    }
                                });
                            }
                        }
                        field.highlighted = function (...args) {

                            let isHighlighted = false;

                            // Loop through each argument
                            args.forEach(arg => {
                                if (Array.isArray(arg)) {
                                    // If the argument is an array, treat it as a range [start, end]
                                    let [startLine, endLine] = arg;
                                    for (let i = startLine; i <= endLine; i++) {
                                        if (i > 0 && i <= field.input.lines.children().length) {
                                            let lineElement = field.input.lines.children().eq(i - 1);
                                            if (lineElement.hasClass('text-bg-pink')) {
                                                isHighlighted = true;
                                                return; // Exit as soon as any highlighted line is found
                                            }
                                        }
                                    }
                                } else {
                                    // If the argument is a single line number
                                    let line = arg;
                                    if (line > 0 && line <= field.input.lines.children().length) {
                                        let lineElement = field.input.lines.children().eq(line - 1);
                                        if (lineElement.hasClass('text-bg-pink')) {
                                            isHighlighted = true;
                                            return; // Exit as soon as any highlighted line is found
                                        }
                                    }
                                }
                            });

                            return isHighlighted;
                        }
                        // Execute onChange
                        field.input.editor.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'textarea':
                        field.input = $(document.createElement('textarea')).attr({
                            'id': field.id + 'input',
                            'class': 'form-control flex-grow-1',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).text(properties.value).appendTo(field.group);
                        // Execute onChange
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'select':
                        field.input = {};
                        field.input.select = $(document.createElement('select')).attr({
                            'id': field.id + 'input',
                            'class': 'form-select flex-grow-1',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).appendTo(field.group);
                        if(properties.multiple){
                            field.input.select.attr('multiple',true);
                            field.input.select.attr('name','[]' + properties.name);
                        }
                        field.options = {};
                        field.delete = function(id = null){
                            if(id){
                                if(typeof field.options[id] !== 'undefined'){
                                    field.options.remove();
                                    delete field.options[id];
                                }
                            } else {
                                for(const [key, element] of Object.entries(field.options)){
                                    element.remove();
                                    delete field.options[key];
                                }
                            }
                        };
                        field.add = function(id,text){
                            if(typeof field.options[id] === 'undefined'){
                                field.options[id] = $(document.createElement('option')).attr('value',id).text(text).appendTo(field.input.select);
                            }
                        }
                        if(properties.options !== null){
                            for(const [key, option] of Object.entries(properties.options)){
                                field.add(option.id,option.text);
                            }
                        }
                        field.init = function(){}
                        field.input.val = function(value = null){
                            if(value !== null && value !== field.input.select.val()){
                                field.input.select.val(value).trigger('change');
                            }
                            return field.input.select.val();
                        }
                        field.init();
                        field.input.val(properties.value);
                        // Execute onChange
                        field.input.select.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'locale':
                        field.input = {};
                        field.input.select = $(document.createElement('select')).attr({
                            'id': field.id + 'input',
                            'class': 'form-select flex-grow-1',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).appendTo(field.group);
                        if(properties.multiple){
                            field.input.select.attr('multiple',true);
                            field.input.select.attr('name','[]' + properties.name);
                        }
                        field.options = {};
                        field.delete = function(id = null){
                            if(id){
                                if(typeof field.options[id] !== 'undefined'){
                                    field.options.remove();
                                    delete field.options[id];
                                }
                            } else {
                                for(const [key, element] of Object.entries(field.options)){
                                    element.remove();
                                    delete field.options[key];
                                }
                            }
                        };
                        field.add = function(id,text){
                            if(typeof field.options[id] === 'undefined'){
                                field.options[id] = $(document.createElement('option')).attr('value',id).text(text).appendTo(field.input.select);
                            }
                        }
                        for(const [key, option] of Object.entries(self._builder.Option.get('locales'))){
                            field.add(option.id,option.text);
                        }
                        field.init = function(){
                            let defaults = {
                                theme: "bootstrap-5",
                                width: 'calc(100% - ' + field.label.outerWidth() + 'px)'
                            }
                            if(properties.multiple){
                                defaults.allowClear = true;
                            }
                            if(properties.allowNew){
                                defaults.tags = true;
                            }
                            if(properties.modal !== null){
                                defaults.dropdownParent = properties.modal;
                            }
                            if(typeof properties.callback.format === 'function'){
                                defaults.templateResult = properties.callback.format;
                            }
                            field.select2 = field.input.select.select2(defaults);
                        }
                        field.input.val = function(value = null){
                            if(value !== null && value !== field.input.select.val()){
                                field.input.select.val(value).trigger('change');
                            }
                            return field.input.select.val();
                        }
                        field.init();
                        field.input.val(properties.value);
                        // Execute onChange
                        field.input.select.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'rating':
                    case 'status':
                    case 'priority':
                        properties.callback.format = function(option, component){

                            // Check if the option is a placeholder
                            if (!option.id) { return option.text; }

                            // Retrieve the rating
                            let targetData = {
                                label: 'Unknown',
                                color: 'secondary',
                                icon: 'question',
                            };
                            switch(properties.type){
                                case 'rating':
                                    targetData = self._builder.Rating.get(properties.target,option.id);
                                    break;
                                case 'status':
                                    targetData = self._builder.Status.get(properties.target,option.id);
                                    break;
                                case 'priority':
                                    targetData = self._builder.Priority.get(properties.target,option.id);
                                    break;
                            }

                            // Create the option
                            var $option = $('<div class="px-3 py-2 animate-flicker-hover text-bg-'+targetData.color+'" style="margin: -.375rem -.75rem!important;"><i class="me-1 bi bi-'+targetData.icon+'"></i>'+targetData.label+'</div>');

                            // Return the option
                            return $option;
                        };
                    case 'source':
                    case 'type':
                        switch(properties.type){
                            case 'rating':
                                properties.targetTable = 'ratings';
                                break;
                            case 'status':
                                properties.targetTable = 'statuses';
                                break;
                            case 'priority':
                                properties.targetTable = 'priorities';
                                break;
                            case 'source':
                                properties.targetTable = 'sources';
                                break;
                            case 'type':
                                properties.targetTable = 'types';
                                break;
                        }
                        field.input = {};
                        field.input.select = $(document.createElement('select')).attr({
                            'id': field.id + 'input',
                            'class': 'form-select flex-grow-1',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).appendTo(field.group);
                        if(properties.multiple){
                            field.input.select.attr('multiple',true);
                            field.input.select.attr('name','[]' + properties.name);
                        }
                        field.options = {};
                        field.delete = function(id = null){
                            if(id){
                                if(typeof field.options[id] !== 'undefined'){
                                    field.options.remove();
                                    delete field.options[id];
                                }
                            } else {
                                for(const [key, element] of Object.entries(field.options)){
                                    element.remove();
                                    delete field.options[key];
                                }
                            }
                        };
                        field.add = function(id,text){
                            if(typeof field.options[id] === 'undefined'){
                                field.options[id] = $(document.createElement('option')).attr('value',id).text(text).appendTo(field.input.select);
                            }
                        }
                        for(const [key, option] of Object.entries(self._builder.Option.get(properties.targetTable,properties.target))){
                            field.add(option.id,option.text);
                        }
                        field.init = function(){
                            let defaults = {
                                theme: "bootstrap-5",
                                width: 'calc(100% - ' + field.label.outerWidth() + 'px)'
                            }
                            if(properties.multiple){
                                defaults.allowClear = true;
                            }
                            if(properties.allowNew){
                                defaults.tags = true;
                            }
                            if(properties.modal !== null){
                                defaults.dropdownParent = properties.modal;
                            }
                            if(typeof properties.callback.format === 'function'){
                                defaults.templateResult = properties.callback.format;
                            }
                            field.select2 = field.input.select.select2(defaults);
                        }
                        field.input.val = function(value = null){
                            if(value !== null && value !== field.input.select.val()){
                                field.input.select.val(value).trigger('change');
                            }
                            return field.input.select.val();
                        }
                        field.init();
                        field.input.val(properties.value);
                        // Execute onChange
                        field.input.select.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'range':
                        if(properties.value === null){
                            properties.value = properties.min;
                        }
                        field.input = $(document.createElement('div')).addClass('tooltip-range form-control border border-start-0 rounded-end flex-grow-1 px-2 d-flex align-items-center').appendTo(field.group);
                        field.input.range = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-range',
                            'type': properties.type,
                            'step': properties.step,
                            'value': properties.value,
                            'min': properties.min,
                            'max': properties.max,
                        }).appendTo(field.input);
                        field.input.output = $(document.createElement('output')).attr({
                            'for': field.id + 'input',
                        }).appendTo(field.input);
                        if(properties.options !== null && typeof properties.options === "object" && typeof properties.options[properties.value] !== "undefined"){
                            field.input.output.html(properties.options[properties.value]);
                        } else {
                            field.input.output.html(properties.value);
                        }
                        field.input.range.on('input',function(){
                            if(properties.options !== null && typeof properties.options === "object" && typeof properties.options[field.input.range.val()] !== "undefined"){
                                field.input.output.html(properties.options[field.input.range.val()]);
                            } else {
                                field.input.output.html(field.input.range.val());
                            }
                        });
                        field.input.val = function(value = null){
                            if(value !== null){
                                field.input.range.val(value);
                                field.input.range.trigger('input');
                            }

                            return field.input.range.val();
                        };
                        // Execute onChange
                        field.input.range.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'excel':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'file',
                            'value': properties.value,
                        }).appendTo(field.group);

                        // Check if multiple files are allowed
                        if(properties.multiple){
                            field.input.attr('multiple',true);
                            field.input.attr('name','[]' + properties.name);
                        }

                        // Execute onChange
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });

                        // Create a Handler for the file input
                        field.input.val = function(){
                            return new Promise((resolve, reject) => {
                                // Initialize Values
                                let values = [];

                                // Retrieve the files
                                let files = field.input[0].files;

                                // Check if any file is selected
                                if (files.length === 0) {
                                    resolve(values);
                                    return;
                                }

                                // Function to read a single file
                                function readFile(file) {
                                    return new Promise((resolve, reject) => {
                                        let reader = new FileReader();
                                        reader.onload = function(e) {

                                            // e.target.result will be a data URL (Base64-encoded string)
                                            const dataURL = e.target.result;

                                            // 1) Get the Base64-encoded portion by splitting on the comma
                                            //    "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,UEsDB..."
                                            const base64String = dataURL.split(',')[1];

                                            // 2) Decode the Base64 string to get the raw binary data
                                            const binaryString = atob(base64String);

                                            // 3) Now pass the binary string to XLSX
                                            const workbook = XLSX.read(binaryString, { type: 'binary', cellDates: true, cellNF: true, cellText: false });

                                            // 4) Get the name of the first worksheet
                                            const sheetName = workbook.SheetNames[0];

                                            // 5) Get the worksheet contents
                                            const worksheet = workbook.Sheets[sheetName];

                                            // 6) Convert worksheet to JSON, array, etc.
                                            const jsonData = XLSX.utils.sheet_to_json(worksheet, { raw: false, defval: null, dateNF: 'yyyy-mm-dd hh:mm:ss' });

                                            // Resolve the promise with the file data
                                            resolve({
                                                name: file.name,
                                                size: file.size,
                                                type: file.type,
                                                extension: file.name.split('.').pop().toLowerCase(),
                                                content: e.target.result,
                                                json: jsonData,
                                                icon: getFileIcon(file.name)
                                            });
                                        };
                                        reader.onerror = reject;
                                        reader.readAsDataURL(file);
                                    });
                                }

                                // Function to determine file icon based on extension
                                function getFileIcon(fileName) {
                                    let extension = fileName.split('.').pop().toLowerCase();
                                    switch(extension) {
                                        case 'pdf': return 'file-earmark-pdf';
                                        case 'doc':
                                        case 'docx': return 'file-earmark-word';
                                        case 'xls':
                                        case 'xlsx': return 'file-earmark-excel';
                                        case 'ppt':
                                        case 'pptx': return 'file-earmark-ppt';
                                        case 'zip':
                                        case 'rar': return 'file-earmark-zip';
                                        case 'jpg':
                                        case 'jpeg':
                                        case 'png':
                                        case 'gif':
                                        case 'tiff':
                                        case 'bmp':
                                        case 'webp': return 'file-earmark-image';
                                        case 'mp3':
                                        case 'wav':
                                        case 'wma':
                                        case 'ogg':
                                        case 'm4a': return 'file-earmark-music';
                                        case 'mp4':
                                        case 'avi':
                                        case 'mkv':
                                        case 'wmv':
                                        case 'mov': return 'file-earmark-play';
                                        case 'css':
                                        case 'less':
                                        case 'scss':
                                        case 'sass':
                                        case 'js':
                                        case 'json':
                                        case 'xml':
                                        case 'html':
                                        case 'htm':
                                        case 'php':
                                        case 'asp':
                                        case 'aspx':
                                        case 'jsp':
                                        case 'cfm': return 'file-earmark-code';
                                        case 'txt':
                                        case 'log':
                                        case 'csv':
                                        case 'tsv': return 'file-earmark-text';
                                        case 'msg':
                                        case 'eml': return 'envelope-at';
                                        default: return 'file-earmark';
                                    }
                                }

                                // Read all files
                                let promises = [];
                                for (let i = 0; i < files.length; i++) {
                                    promises.push(readFile(files[i]));
                                }

                                // Resolve all promises
                                Promise.all(promises).then(fileData => {
                                    resolve(fileData);
                                }).catch(error => {
                                    reject(error);
                                });
                            });
                        };
                        break;
                    case 'stopwatch':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["99:99", "99:99:99"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'ca-tax':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["999999999RT9999"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'ca-gst':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["999999999RT9999"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'ca-importer':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["999999999RM9999"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    default:
                        break;
                }
            };
        },
    };

    #renderers = {}

    #layouts = {
        quiz: class extends this.ComponentClass {

            _init(){
                this._properties = {
                    class: {
                        component: null,
                    },
                    callback:{
                        val: function(values){ return values; },
                        reset: function(form){},
                        clear: function(form){},
                        submit: function(form){},
                    },
                    title: null,
                    clear: true,
                    reset: true,
                    submit: true,
                };
            }

            _config(name,options){
                if(typeof this._properties[name] !== 'undefined'){
                    switch(name){
                        default:
                            this._properties[name] = options;
                            break;
                    }
                }
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'layout' + this._id,
                    'class': 'row',
                });
                this._component.id = this._component.attr('id');

                // Add a Deprecated Warning
                this._component.deprecated = $(document.createElement('div')).addClass('alert alert-warning alert-dismissible fade show m-4').appendTo(this._component);
                this._component.deprecated.html('<strong>Deprecated:</strong> This layout is deprecated and will be removed in a future version.');
                this._component.deprecated.close = $(document.createElement('button')).addClass('btn-close').attr({"type": "button", "data-bs-dismiss": "alert", "aria-label": "Close"}).appendTo(this._component.deprecated);

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }

                // Create Title
                this._component.header = $(document.createElement('div')).addClass('col-12 mb-2').appendTo(this._component);
                this._component.header.title = $(document.createElement('h1')).addClass('text-center').text(this._properties.title).appendTo(this._component.header);

                // Create Form
                this._component.form = this._builder.Component(
                    "form",
                    this._component,
                    {
                        callback:{
                            val: function(values){
                                var array = {};
                                for(var [key, value] of Object.entries(values)){
                                    if(key !== 'clear' && key !== 'reset' && key !== 'submit'){
                                        var page = key.split('question')[1];
                                        page = page.split('range')[0];
                                        page = page.split('comment')[0];
                                        var id = key.split(page)[1];
                                        if(typeof array[page] === 'undefined'){
                                            array[page] = {};
                                        }
                                        array[page][id] = value;
                                    }
                                }
                                if(typeof self._properties.callback.val === 'function'){
                                    array = self._properties.callback.val(array);
                                }
                                return array;
                            },
                            reset: this._properties.callback.reset,
                            clear: this._properties.callback.clear,
                            submit: this._properties.callback.submit,
                        },
                    },
                    function(form, component){
                        // Create Stepper
                        self._component.stepper = self._builder.Component(
                            "stepper",
                            component,
                            {
                                class: {
                                    stepper: null,
                                    controls: 'card card-body mb-2',
                                    steps: 'card card-body my-2',
                                    pagination: 'card card-body mt-2',
                                },
                                color: null,
                                properties: {},
                            },
                            function(stepper, component){
                                // Create Form Controls
                                self._component.controls = $(document.createElement('div')).addClass('btn-group').appendTo(component.pagination.list);

                                // Add Clear Control
                                if(self._properties.clear){
                                    form.add(
                                        {
                                            name: 'clear',
                                            label: 'Clear',
                                            icon: 'x-lg',
                                            type: 'clear',
                                        },
                                        function(input){
                                            input.input.appendTo(self._component.controls);
                                        },
                                    );
                                }

                                // Add Reset Control
                                if(self._properties.reset){
                                    form.add(
                                        {
                                            name: 'reset',
                                            label: 'Reset',
                                            icon: 'arrow-clockwise',
                                            type: 'reset',
                                        },
                                        function(input){
                                            input.input.appendTo(self._component.controls);
                                        },
                                    );
                                }

                                // Add Submit Control
                                if(self._properties.submit){
                                    form.add(
                                        {
                                            name: 'submit',
                                            label: 'Submit',
                                            icon: 'save',
                                            type: 'submit',
                                        },
                                        function(input){
                                            input.input.appendTo(self._component.controls);
                                        },
                                    );
                                }
                            },
                        );
                    }
                );
            }

            #addStep(properties){

                const self = this;

                // Add Step
                this._component.stepper.add(
                    function(step){

                        // Save Properties
                        step.properties = properties;

                        // Create Question
                        step.content.question = $(document.createElement('h3')).html(properties.label).appendTo(step.content);

                        // Create Range
                        step.content.range = self._component.form.add( //Add Inputs
                            {
                                name: 'question'+properties.id+'range',
                                label: properties.scaleDescription,
                                icon: null,
                                type: 'range',
                                value: 1,
                                min: 1,
                                max: Object.entries(properties.scale).length,
                                options: properties.scale,
                                class: {
                                    input: null,
                                    label: null,
                                    field: 'my-3',
                                },
                            },
                        ).appendTo(step.content);

                        // Create MCE
                        step.content.comment = self._component.form.add(
                            {
                                name: 'question'+properties.id+'comment',
                                label: 'Comments',
                                icon: null,
                                type: 'mce',
                                class: {
                                    input: null,
                                    label: null,
                                    field: null,
                                },
                            },
                        ).appendTo(step.content);
                    },
                );
            }

            add(param1 =null, param2 =null){

                const self = this;

                let options = {};
                let callback = null;

                let properties = {
                    label: null,
                    range: true,
                    comment: true,
                    scaleDescription: null,
                    scale: null,
                };

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Configure Options
                for(const [key, value] of Object.entries(options)){
                    if(typeof properties[key] !== 'undefined'){
                        switch(key){
                            case"scale":
                                var opts = null;
                                if(typeof value === 'object'){
                                    opts = value;
                                    if($.isArray(value)){
                                        opts = {};
                                        for(var [k, v] of Object.entries(value)){
                                            var kv = parseInt(k) + 1;
                                            opts[kv] = v;
                                        }
                                    }
                                }
                                properties[key] = opts;
                                break;
                            case"class":
                                for(const [section, classes] of Object.entries(value)){
                                    if(properties[key][section] != null){
                                        properties[key][section] += ' ' + classes;
                                    } else {
                                        properties[key][section] = classes;
                                    }
                                }
                                break;
                            default:
                                properties[key] = value;
                                break;
                        }
                    }
                }

                // Set ID
                let id = this._count();
                properties.id = id;

                // Add Step
                if(typeof this._component.stepper !== 'undefined'){
                    self.#addStep(properties);
                } else {
                    var interval = setInterval(function() {
                        if(typeof self._component.stepper !== 'undefined'){
                            clearInterval(interval);
                            self.#addStep(properties);
                        }
                    }, 100);
                }

                // Return
                return this;
            }

            val(values = null){
                return this._component.form.val(values);
            }

            reset(){
                return this._component.form.reset();
            }

            clear(){
                return this._component.form.clear();
            }

            submit(){
                return this._component.form.submit();
            }
        },
        faq: class extends this.ComponentClass {

            _init(){
                this._properties = {
                    class: {
                        component: null,
                    },
                    title: 'Frequently Asked Questions',
                    contact: '?t=layouts&p=contact',
                };
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'layout' + this._id,
                    'class': 'row',
                });
                this._component.id = this._component.attr('id');

                // Add a Deprecated Warning
                this._component.deprecated = $(document.createElement('div')).addClass('alert alert-warning alert-dismissible fade show m-4').appendTo(this._component);
                this._component.deprecated.html('<strong>Deprecated:</strong> This layout is deprecated and will be removed in a future version.');
                this._component.deprecated.close = $(document.createElement('button')).addClass('btn-close').attr({"type": "button", "data-bs-dismiss": "alert", "aria-label": "Close"}).appendTo(this._component.deprecated);

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }

                // Create Title
                this._component.header = $(document.createElement('div')).addClass('col-12 mb-2').appendTo(this._component);
                this._component.header.title = $(document.createElement('h1')).addClass('text-center').text(this._properties.title).appendTo(this._component.header);

                // Create Accordion
                this._component.faqs = this._builder.Component(
                    'accordion',
                    this._component,
                    {
                        class: {
                            component: 'col-12 mt-2 bg-transparent',
                            collapse: "mb-2 rounded",
                            button: "rounded",
                        },
                        flush: false,
                        alwaysOpen: true,
                    },
                );

                // Create Footer
                this._component.footer = $(document.createElement('div')).addClass('col-12 mt-2').appendTo(this._component);
                this._component.footer.paragraph = $(document.createElement('p')).addClass('lead text-center').appendTo(this._component.footer);
                this._component.footer.paragraph.link = $(document.createElement('a')).attr({
                    href: this._properties.contact,
                }).text('Contact us').appendTo(this._component.footer.paragraph);
                this._component.footer.paragraph.text = $(document.createElement('span')).text(', if you did not find the right anwser or you have an other question?').appendTo(this._component.footer.paragraph);
            }

            add(param1 =null, param2 =null){

                const self = this;

                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Add FAQ
                this._component.faqs.add(options,callback);

                // Return
                return this;
            }
        },
        list: class extends this.ComponentClass {

            _init(){
                this._properties = {
                    class: {
                        component: null,
                        object: null,
                        buttons: null,
                        searchBuilder: null,
                        table: null,
                        footer: null,
                    },
                    title: null,
                    icon: null,
                    actions:{},
                    buttons:[],
                    columnDefs:[],
                    dblclick:function(event, table, node, data){},
                    advancedSearch:true,
                    exportTools:true,
                    columnsVisibility:true,
                    selectTools:true,
                };
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'layout' + this._id,
                    'class': 'row',
                });
                this._component.id = this._component.attr('id');

                // Add a Deprecated Warning
                this._component.deprecated = $(document.createElement('div')).addClass('alert alert-warning alert-dismissible fade show m-4').appendTo(this._component);
                this._component.deprecated.html('<strong>Deprecated:</strong> This layout is deprecated and will be removed in a future version.');
                this._component.deprecated.close = $(document.createElement('button')).addClass('btn-close').attr({"type": "button", "data-bs-dismiss": "alert", "aria-label": "Close"}).appendTo(this._component.deprecated);

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }

                // Create Card
                this._component.card = this._builder.Component(
                    'card',
                    this._component,
                    {
                        class: {
                            collapse: "w-100",
                        },
                        icon: this._properties.icon,
                        title: this._properties.title,
                    },
                    function(card,component){
                        component.body.addClass('p-0');
                        self._component.table = self._builder.Component(
                            'table',
                            component.body,
                            {
                                class: {
                                    component: self._properties.class.object,
                                    searchBuilder: self._properties.class.searchBuilder,
                                    buttons: "px-4 pt-4 " + self._properties.class.buttons,
                                    table: "border-top " + self._properties.class.table,
                                    footer: "px-4 pt-2 pb-4 " + self._properties.class.footer,
                                },
                                showButtonsLabel: true,
                                selectTools:true,
                                actions:self._properties.actions,
                                datatable:{
                                    columnDefs:self._properties.columnDefs,
                                    buttons:self._properties.buttons,
                                },
                                dblclick:self._properties.dblclick,
                                advancedSearch:self._properties.advancedSearch,
                                exportTools:self._properties.exportTools,
                                columnsVisibility:self._properties.columnsVisibility,
                                selectTools:self._properties.selectTools,
                            },
                        );
                    },
                );
            }

            add(data){

                this._component.table.add(data);
            }

            update(row, data){

                this._component.table.update(row, data);
            }

            delete(row){

                this._component.table.delete(row);
            }
        },
        details: class extends this.ComponentClass {

            _init(){
                this._properties = {
                    class: {
                        component: null,
                    },
                    title: 'Details',
                    icon: 'person',
                };
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'layout' + this._id,
                    'class': 'row',
                });
                this._component.id = this._component.attr('id');

                // Add a Deprecated Warning
                this._component.deprecated = $(document.createElement('div')).addClass('alert alert-warning alert-dismissible fade show m-4').appendTo(this._component);
                this._component.deprecated.html('<strong>Deprecated:</strong> This layout is deprecated and will be removed in a future version.');
                this._component.deprecated.close = $(document.createElement('button')).addClass('btn-close').attr({"type": "button", "data-bs-dismiss": "alert", "aria-label": "Close"}).appendTo(this._component.deprecated);

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }

                // Create Columns
                this._component.col1 = $(document.createElement('div')).attr({
                    class: 'col-4',
                }).appendTo(this._component);
                this._component.col2 = $(document.createElement('div')).attr({
                    class: 'col-8',
                }).appendTo(this._component);

                // Create Profile Details Section
                this._component.detail = this._builder.Component(
                    'card',
                    this._component.col1,
                    {
                        title: self._properties.title,
                        icon: self._properties.icon,
                        class: {
                            card: 'mb-3',
                        },
                    },
                    function(card,component){
                        component.body.addClass('p-0');
                        self._component.detailList = self._builder.Component(
                            'list',
                            component.body,
                            {
                                callback: {
                                    item: function(item){},
                                },
                                class: {
                                    component: 'bg-transparent rounded-bottom',
                                },
                            },
                        );
                        card.hide();
                    },
                );

                // Create Profile Tabs Section
                this._component.tabs = this._builder.Component(
                    'tabs',
                    this._component.col2,
                    {
                        class: {
                            navbar: 'nav-pills'
                        },
                        properties: {
                            class: {
                                tab: 'fade',
                            },
                        },
                    },
                    function(tabs,component){
                        tabs.hide();
                    }
                );
            }

            detail(param1 =null, param2 =null){

                const self = this;

                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Show Detail
                this._component.detail.show();

                // Add Detail
                this._component.detailList.add(options,callback);

                // Return
                return this;
            }

            tab(param1 =null, param2 =null, param3 =null){

                const self = this;

                let name = {};
                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2, param3].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'string') {
                            name = param;
                        } else if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Show Tabs
                this._component.tabs.show();

                // Add Tab
                this._component.tabs.add(name,options,callback);

                // Return
                return this;
            }
        },
        profile: class extends this.ComponentClass {

            modal = null;

            _init(){
                this._properties = {
                    class: {
                        component: null,
                    },
                    status: null,
                    isActive: false,
                    isDeleted: false,
                    isBanned: false,
                    statuses: {},
                    callback: {
                        isActive: function(value, button, modal, layout){
                            console.log('isActive',value, button, modal, layout);
                        },
                        isDeleted: function(value, button, modal, layout){
                            console.log('isDeleted',value, button, modal, layout);
                        },
                        isBanned: function(value, button, modal, layout){
                            console.log('isBanned',value, button, modal, layout);
                        },
                    }
                };
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'layout' + this._id,
                    'class': 'row',
                });
                this._component.id = this._component.attr('id');

                // Add a Deprecated Warning
                this._component.deprecated = $(document.createElement('div')).addClass('alert alert-warning alert-dismissible fade show m-4').appendTo(this._component);
                this._component.deprecated.html('<strong>Deprecated:</strong> This layout is deprecated and will be removed in a future version.');
                this._component.deprecated.close = $(document.createElement('button')).addClass('btn-close').attr({"type": "button", "data-bs-dismiss": "alert", "aria-label": "Close"}).appendTo(this._component.deprecated);

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }

                // Create Columns
                this._component.col1 = $(document.createElement('div')).attr({
                    class: 'col-4',
                }).appendTo(this._component);
                this._component.col2 = $(document.createElement('div')).attr({
                    class: 'col-8',
                }).appendTo(this._component);

                // Create Profile Details Section
                this._component.detail = this._builder.Component(
                    'card',
                    this._component.col1,
                    {
                        title: 'Details',
                        icon: 'person',
                        class: {
                            card: 'mb-3',
                        },
                    },
                    function(card,component){

                        // Remove Padding
                        component.body.addClass('p-0');

                        // Create List
                        self._component.detailList = self._builder.Component(
                            'list',
                            component.body,
                            {
                                callback: {
                                    item: function(item){},
                                },
                                class: {
                                    component: 'bg-transparent',
                                },
                            },
                        );

                        // Add status icon
                        component.footer.removeClass('d-none').addClass('p-0');
                        component.footer.group = $(document.createElement('div')).addClass('btn-group-vertical rounded-0 rounded-bottom w-100').appendTo(component.footer);
                        component.footer.group.status = $(document.createElement('button')).addClass('btn rounded-0 rounded-bottom').appendTo(component.footer.group);
                        component.footer.group.status.icon = $(document.createElement('i')).addClass('bi me-1').appendTo(component.footer.group.status);
                        component.footer.group.status.label = $(document.createElement('span')).appendTo(component.footer.group.status);
                        self._component.status = component.footer.group.status;

                        // Add Status Button Events
                        let body = $(document.createElement('div'));
                        body.p1 = $(document.createElement('p')).text('This modal allows you to manage various settings for the user\'s profile. By toggling the buttons below, you can control the following aspects:').appendTo(body);
                        body.list = $(document.createElement('ul')).appendTo(body);
                        body.list.item1 = $(document.createElement('li')).text('Delete/Restore: Toggling this button will either delete or restore the user\'s account. Deleting the account will result in permanent removal of their profile and associated data. Restoring the account will reverse this action.').appendTo(body.list);
                        body.list.item2 = $(document.createElement('li')).text('Ban/Unban: Toggling this button will either ban or unban the user. Banning a user will restrict their access and participation on the platform. Unbanning the user will lift the restrictions.').appendTo(body.list);
                        body.list.item3 = $(document.createElement('li')).text('Activate/Deactivate: Toggling this button will either activate or deactivate the user\'s account. Activating the account will enable the user to access and use the platform. Deactivating the account will temporarily disable their access.').appendTo(body.list);
                        body.p2 = $(document.createElement('p')).text('Please note that these actions can have significant consequences, so exercise caution when making changes to a user\'s profile. Always review the situation and ensure the appropriate action is taken.').appendTo(body);
                        self.modal = self._builder.Component(
                            'modal',
                            component.footer.group.status,
                            {
                                onEnter: false,
                                destroy: false,
                                icon: "gear",
                                title: "User Profile Controls",
                                body: body,
                                submit: false,
                                size: "lg",
                            },
                            function(modal,element){
                                element.btn = {
                                    deactivate: modal.add(
                                        {
                                            label: "Deactivate",
                                            color: "danger",
                                        },
                                        function(action,button,modal){
                                            action.click(function(){
                                                if(typeof self._properties.callback.isActive === 'function'){
                                                    self._properties.callback.isActive(0,action,modal,self);
                                                }
                                                modal.hide();
                                            });
                                        },
                                    ),
                                    activate: modal.add(
                                        {
                                            label: "Activate",
                                            color: "info",
                                        },
                                        function(action,button,modal){
                                            action.click(function(){
                                                if(typeof self._properties.callback.isActive === 'function'){
                                                    self._properties.callback.isActive(1,action,modal,self);
                                                }
                                                modal.hide();
                                            });
                                        },
                                    ),
                                    unban: modal.add(
                                        {
                                            label: "Unban",
                                            color: "info",
                                        },
                                        function(action,button,modal){
                                            action.click(function(){
                                                if(typeof self._properties.callback.isBanned === 'function'){
                                                    self._properties.callback.isBanned(1,action,modal,self);
                                                }
                                                modal.hide();
                                            });
                                        },
                                    ),
                                    ban: modal.add(
                                        {
                                            label: "Ban",
                                            color: "danger",
                                        },
                                        function(action,button,modal){
                                            action.click(function(){
                                                if(typeof self._properties.callback.isBanned === 'function'){
                                                    self._properties.callback.isBanned(0,action,modal,self);
                                                }
                                                modal.hide();
                                            });
                                        },
                                    ),
                                    restore: modal.add(
                                        {
                                            label: "Restore",
                                            color: "info",
                                        },
                                        function(action){
                                            action.attr('style','border-bottom-left-radius:var(--bs-border-radius)!important;border-top-left-radius:0!important;');
                                            action.click(function(){
                                                if(typeof self._properties.callback.isDeleted === 'function'){
                                                    self._properties.callback.isDeleted(1,action,modal,self);
                                                }
                                                modal.hide();
                                            });
                                        },
                                    ),
                                    delete: modal.add(
                                        {
                                            label: "Delete",
                                            color: "danger",
                                        },
                                        function(action){
                                            action.attr('style','border-bottom-left-radius:var(--bs-border-radius)!important;border-top-left-radius:0!important;');
                                            action.click(function(){
                                                if(typeof self._properties.callback.isDeleted === 'function'){
                                                    self._properties.callback.isDeleted(0,action,modal,self);
                                                }
                                                modal.hide();
                                            });
                                        },
                                    ),
                                }
                                self.refresh();
                            },
                        );
                        setTimeout(function(){ self.status(); }, 0);
                        card.hide();
                    },
                );

                // Create Profile About Section
                this._component.about = this._builder.Component(
                    'card',
                    this._component.col1,
                    {
                        title: 'About',
                        icon: 'person-vcard',
                        class: {
                            container: 'mb-3',
                        },
                    },
                    function(card,component){
                        component.body.addClass('p-0');
                        self._component.aboutList = self._builder.Component(
                            'list',
                            component.body,
                            {
                                callback: {
                                    item: function(item){},
                                },
                                class: {
                                    component: 'bg-transparent rounded-bottom',
                                },
                            },
                        );
                        card.hide();
                    },
                );

                // Create Profile Tabs Section
                this._component.tabs = this._builder.Component(
                    'tabs',
                    this._component.col2,
                    {
                        class: {
                            navbar: 'nav-pills'
                        },
                        properties: {
                            class: {
                                tab: 'fade',
                            },
                        },
                    },
                    function(tabs,component){
                        tabs.hide();
                    }
                );
            }

            refresh(){
                for(const [name, btn] of Object.entries(this.modal._component.btn)){
                    btn.hide();
                }
                if(this._properties.isActive){
                    this.modal._component.btn.deactivate.show();
                } else {
                    this.modal._component.btn.activate.show();
                }
                if(this._properties.isBanned){
                    this.modal._component.btn.unban.show();
                } else {
                    this.modal._component.btn.ban.show();
                }
                if(this._properties.isDeleted){
                    this.modal._component.btn.restore.show();
                } else {
                    this.modal._component.btn.delete.show();
                }
            }

            status(value = null){

                // Set Self
                const self = this;

                // Check Value
                if(typeof value === 'number'){
                    this._properties.status = value;
                }

                // Reset Status
                this._component.status.removeClass (function (index, className) {
                    return (className.match (/(^|\s)text-bg-\S+/g) || []).join(' ');
                });
                this._component.status.removeClass (function (index, className) {
                    return (className.match (/(^|\s)bg-\S+/g) || []).join(' ');
                });
                this._component.status.removeClass (function (index, className) {
                    return (className.match (/(^|\s)btn-\S+/g) || []).join(' ');
                });
                this._component.status.icon.removeClass (function (index, className) {
                    return (className.match (/(^|\s)bi-\S+/g) || []).join(' ');
                });
                this._component.status.label.text('');

                // Configure Status
                setTimeout(function(){
                    if(typeof self._properties.statuses[value] !== 'undefined'){
                        self._component.status.addClass('text-bg-' + self._properties.statuses[value].color);
                        self._component.status.icon.addClass('bi-' + self._properties.statuses[value].icon);
                        self._component.status.label.text(self._properties.statuses[value].label);
                    }
                }, 0);

                // Return
                return this._properties.status;
            }

            detail(param1 =null, param2 =null){

                const self = this;

                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Show Detail
                this._component.detail.show();

                // Add Detail
                this._component.detailList.add(options,callback);

                // Return
                return this;
            }

            about(param1 =null, param2 =null){

                const self = this;

                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Show About
                this._component.about.show();

                // Add About
                this._component.aboutList.add(options,callback);

                // Return
                return this;
            }

            tab(param1 =null, param2 =null, param3 =null){

                const self = this;

                let name = {};
                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2, param3].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'string') {
                            name = param;
                        } else if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Show Tabs
                this._component.tabs.show();

                // Add Tab
                this._component.tabs.add(name,options,callback);

                // Return
                return this;
            }
        },
        help: class extends this.ComponentClass {

            _init(){
                this._properties = {
                    class: {
                        component: null,
                    },
                    title: null,
                    link: {
                        articles: null,
                        contactus: null,
                    },
                };
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'layout' + this._id,
                    'class': 'row',
                });
                this._component.id = this._component.attr('id');

                // Add a Deprecated Warning
                this._component.deprecated = $(document.createElement('div')).addClass('alert alert-warning alert-dismissible fade show m-4').appendTo(this._component);
                this._component.deprecated.html('<strong>Deprecated:</strong> This layout is deprecated and will be removed in a future version.');
                this._component.deprecated.close = $(document.createElement('button')).addClass('btn-close').attr({"type": "button", "data-bs-dismiss": "alert", "aria-label": "Close"}).appendTo(this._component.deprecated);

                // Create Title
                this._component.title = $(document.createElement('h1')).addClass('text-center').text(this._properties.title).appendTo(this._component);

                // Create Search
                this._component.search = $(document.createElement('div')).addClass('col-12 mb-2 d-flex justify-content-center').appendTo(this._component);
                this._component.search.form = $(document.createElement('form')).attr({
                    'class': 'text-center col-6',
                    'method': 'post',
                }).appendTo(this._component.search);
                this._component.search.form.input = $(document.createElement('input')).attr({
                    'class': 'form-control search',
                    'type': 'text',
                    'name': 'search',
                    'placeholder': 'Search...',
                    'value': '',
                }).appendTo(this._component.search.form);

                // Create Announcements
                this._component.announcements = $(document.createElement('div')).addClass('col-12 my-2 d-none').appendTo(this._component);
                this._component.announcements.container = $(document.createElement('div')).addClass('container').appendTo(this._component.announcements);
                this._component.announcements.container.title = $(document.createElement('h1')).addClass('text-center').text('Announcements').appendTo(this._component.announcements.container);
                this._component.announcements.container.announcements = $(document.createElement('div')).addClass('d-flex justify-content-center align-items-center').appendTo(this._component.announcements.container);

                // Create Ressources
                this._component.ressources = $(document.createElement('div')).addClass('col-12 my-2 d-none').appendTo(this._component);
                this._component.ressources.container = $(document.createElement('div')).addClass('container').appendTo(this._component.ressources);
                this._component.ressources.container.title = $(document.createElement('h1')).addClass('text-center').text('Ressources').appendTo(this._component.ressources.container);
                this._component.ressources.container.ressources = $(document.createElement('div')).addClass('row row-cols-1 row-cols-md-3').appendTo(this._component.ressources.container);

                // Create Articles
                this._component.articles = $(document.createElement('div')).addClass('col-12 my-2 d-none').appendTo(this._component);
                this._component.articles.container = $(document.createElement('div')).addClass('container').appendTo(this._component.articles);
                this._component.articles.container.title = $(document.createElement('h1')).addClass('text-center').text('Featured Articles').appendTo(this._component.articles.container);
                this._component.articles.container.articles = $(document.createElement('div')).addClass('row row-cols-1 row-cols-md-3').appendTo(this._component.articles.container);
                this._component.articles.more = $(document.createElement('div')).addClass('row my-2').appendTo(this._component.articles);
                this._component.articles.more.container = $(document.createElement('div')).addClass('col-12').appendTo(this._component.articles.more);
                this._component.articles.more.container.quote = $(document.createElement('p')).addClass('lead text-center').text('Browse a broad library of manuals, training materials, articles and more.').appendTo(this._component.articles.more.container);
                this._component.articles.more.container.button = $(document.createElement('div')).addClass('d-flex justify-content-center align-items-center').appendTo(this._component.articles.more.container);
                this._component.articles.more.container.button.link = $(document.createElement('a')).attr({
                    class: 'btn btn-secondary',
                    href: this._properties.link.articles,
                }).text('View all articles').appendTo(this._component.articles.more.container.button);

                // Create Contact
                this._component.articles.contact = $(document.createElement('div')).addClass('col-12 my-2').appendTo(this._component);
                this._component.articles.contact.container = $(document.createElement('p')).addClass('lead text-center').appendTo(this._component.articles.contact);
                this._component.articles.contact.container.link = $(document.createElement('a')).attr({href:this._properties.link.contactus}).text('Contact us').appendTo(this._component.articles.contact.container);
                this._component.articles.contact.container.span = $(document.createElement('span')).text(', if you did not find the right anwser or you have an other question?').appendTo(this._component.articles.contact.container);

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }
            }

            _config(name,options){
                if(typeof this._properties[name] !== 'undefined'){
                    switch(name){
                        case'link':
                            for(const [key, value] of Object.entries(options)){
                                if(typeof this._properties[name][key] !== 'undefined'){
                                    this._properties[name][key] = value;
                                }
                            }
                            break;
                        default:
                            this._properties[name] = options;
                            break;
                    }
                }
            }

            alert(param1 =null, param2 =null){

                const self = this;

                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                let properties = {
                    class: { //Add Classes
                        alert: null, //Alert Element
                    },
                    color: "danger", //Set Color
                    dismissible: true, //Set Dismissible
                    icon: null, //Set Icon
                    title: null, //Set Title
                    content: null, //Set Content
                    datetime: null, //Set Datetime
                };

                // Configure Options
                for(const [key, value] of Object.entries(options)){
                    if(typeof properties[key] !== 'undefined'){
                        switch(key){
                            case"callback":
                                if(typeof properties[key] !== 'undefined'){
                                    for(const [k, v] of Object.entries(value)){
                                        if(typeof properties[key][k] !== 'undefined'){
                                            properties[key][k] = v;
                                        }
                                    }
                                }
                                break;
                            case"class":
                                for(const [section, classes] of Object.entries(value)){
                                    if(properties[key][section] != null){
                                        properties[key][section] += ' ' + classes;
                                    } else {
                                        properties[key][section] = classes;
                                    }
                                }
                                break;
                            default:
                                properties[key] = value;
                                break;
                        }
                    }
                }

                // Unhide Announcements
                this._component.announcements.removeClass('d-none');

                // Create Alert
                var alert = this._builder.Component(
                    'alert',
                    this._component.announcements.container.announcements,
                    properties,
                    function(alert,component){

                        // Save Properties
                        component.properties = properties;

                        // Add padding
                        component.addClass('p-2 ps-3');

                        // Remove margin
                        component.content.addClass('m-0');

                        // Create Icon
                        component.content.icon = $(document.createElement('i')).addClass('me-1 bi bi-clock').appendTo(component.content);

                        // Configure Datetime
                        let datetime = null;
                        if(properties.datetime !== null){
                            datetime = new Date(properties.datetime);
                        } else {
                            datetime = new Date();
                        }

                        // Create Time
                        component.content.time = $(document.createElement('time')).attr({
                            'class': 'timeago',
                            'title': datetime.toLocaleString(),
                            'datetime': datetime.toLocaleString(),
                            'data-bs-title': datetime.toLocaleString(),
                            'data-bs-toggle': 'tooltip',
                            'data-bs-placement': 'top',
                        }).text(datetime.toLocaleString()).appendTo(component.content);

                        // Initialize Tooltip
                        component.content.time.bootstrap = new bootstrap.Tooltip(component.content.time);

                        // Initialize Timeago
                        setTimeout(function(){ component.content.time.timeago(); }, 0);

                        // Check if callback is a function
                        if(typeof callback === "function"){
                            callback(alert,component);
                        }
                    },
                );

                // Return
                return this;
            }

            ressource(param1 =null, param2 =null){

                const self = this;

                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                let properties = {
                    class: { //Add Classes
                        ressource: null, //Ressource Element
                    },
                    icon: null, //Set Icon
                    title: null, //Set Title
                    content: null, //Set Content
                };

                // Configure Options
                for(const [key, value] of Object.entries(options)){
                    if(typeof properties[key] !== 'undefined'){
                        switch(key){
                            case"callback":
                                if(typeof properties[key] !== 'undefined'){
                                    for(const [k, v] of Object.entries(value)){
                                        if(typeof properties[key][k] !== 'undefined'){
                                            properties[key][k] = v;
                                        }
                                    }
                                }
                                break;
                            case"class":
                                for(const [section, classes] of Object.entries(value)){
                                    if(properties[key][section] != null){
                                        properties[key][section] += ' ' + classes;
                                    } else {
                                        properties[key][section] = classes;
                                    }
                                }
                                break;
                            default:
                                properties[key] = value;
                                break;
                        }
                    }
                }

                // Unhide Ressources
                this._component.ressources.removeClass('d-none');

                // Set ID
                let id = this._count();

                // Create Ressource
                var ressource = $(document.createElement('div')).attr({
                    'id': this._component.id + 'ressource' + id,
                    'class': 'col',
                }).appendTo(this._component.ressources.container.ressources);
                ressource.id = ressource.attr('id');
                ressource.properties = properties;

                // Set Ressource Class
                if(properties.class.ressource){
                    ressource.addClass(properties.class.ressource);
                }

                // Create Card
                ressource.card = $(document.createElement('div')).addClass('card').appendTo(ressource);

                // Create Card Header
                ressource.card.header = $(document.createElement('div')).addClass('card-header d-flex flex-column justify-content-center align-items-center').appendTo(ressource.card);

                // Create Card Title
                ressource.card.header.icon = $(document.createElement('i')).addClass('bi bi-' + properties.icon).css('font-size', '4rem').appendTo(ressource.card.header);
                ressource.card.header.title = $(document.createElement('h3')).addClass('card-title').text(properties.title).appendTo(ressource.card.header);

                // Create Card Body
                ressource.card.body = $(document.createElement('div')).addClass('card-body').html(properties.content).appendTo(ressource.card);

                // Check if callback is a function
                if(typeof callback === "function"){
                    callback(ressource);
                }

                // Return
                return this;
            }

            article(param1 =null, param2 =null){

                const self = this;

                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                let properties = {
                    class: { //Add Classes
                        article: null, //Ressource Element
                    },
                    icon: null, //Set Icon
                    title: null, //Set Title
                    content: null, //Set Content
                };

                // Configure Options
                for(const [key, value] of Object.entries(options)){
                    if(typeof properties[key] !== 'undefined'){
                        switch(key){
                            case"callback":
                                if(typeof properties[key] !== 'undefined'){
                                    for(const [k, v] of Object.entries(value)){
                                        if(typeof properties[key][k] !== 'undefined'){
                                            properties[key][k] = v;
                                        }
                                    }
                                }
                                break;
                            case"class":
                                for(const [section, classes] of Object.entries(value)){
                                    if(properties[key][section] != null){
                                        properties[key][section] += ' ' + classes;
                                    } else {
                                        properties[key][section] = classes;
                                    }
                                }
                                break;
                            default:
                                properties[key] = value;
                                break;
                        }
                    }
                }

                // Unhide Articles
                this._component.articles.removeClass('d-none');

                // Set ID
                let id = this._count();

                // Create Article
                var article = $(document.createElement('div')).attr({
                    'id': this._component.id + 'article' + id,
                    'class': 'col my-2',
                }).appendTo(this._component.articles.container.articles);
                article.id = article.attr('id');
                article.properties = properties;

                // Set Article Class
                if(properties.class.article){
                    article.addClass(properties.class.article);
                }

                // Set Content
                if (properties.content.length > 100) {
                    properties.content = properties.content.slice(0, 100) + '...';
                }

                // Create Card
                article.card = $(document.createElement('div')).addClass('card card-body').appendTo(article);

                // Create Card Header
                article.card.title = $(document.createElement('h4')).addClass('card-title').text(properties.title).appendTo(article.card);

                // Create Card Body
                article.card.body = $(document.createElement('p')).addClass('card-text mt-2').html(properties.content).appendTo(article.card);

                // Check if callback is a function
                if(typeof callback === "function"){
                    callback(article);
                }

                // Return
                return this;
            }
        },
        settings: class extends this.ComponentClass {

            #forms = {};

            _init(){
                this._properties = {
                    class: {
                        component: null,
                        content: null,
                        menu: null,
                    },
                };
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'layout' + this._id,
                    'class': 'row',
                });
                this._component.id = this._component.attr('id');

                // Add a Deprecated Warning
                this._component.deprecated = $(document.createElement('div')).addClass('alert alert-warning alert-dismissible fade show m-4').appendTo(this._component);
                this._component.deprecated.html('<strong>Deprecated:</strong> This layout is deprecated and will be removed in a future version.');
                this._component.deprecated.close = $(document.createElement('button')).addClass('btn-close').attr({"type": "button", "data-bs-dismiss": "alert", "aria-label": "Close"}).appendTo(this._component.deprecated);

                // Create side menu
                this._component.menu = $(document.createElement('div')).addClass('col-4 col-lg-3').appendTo(this._component);
                this._component.menu.accordion = $(document.createElement('div')).attr({
                    'id': this._component.id + 'menu',
                    'class': 'accordion',
                }).appendTo(this._component.menu);
                this._component.menu.id = this._component.menu.accordion.attr('id');

                // Create content
                this._component.content = $(document.createElement('div')).addClass('col-8 col-lg-9').appendTo(this._component);
                this._component.content.card = $(document.createElement('div')).addClass('card').appendTo(this._component.content);
                this._component.content.accordion = $(document.createElement('div')).attr({
                    'id': this._component.id + 'content',
                    'class': 'accordion',
                }).appendTo(this._component.content.card);
                this._component.content.id = this._component.content.accordion.attr('id');

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }

                // Set Menu Class
                if(this._properties.class.menu){
                    this._component.menu.addClass(this._properties.class.menu);
                }

                // Set Content Class
                if(this._properties.class.content){
                    this._component.content.addClass(this._properties.class.content);
                }
            }

            add(param1 = null, param2 = null){

                const self = this;

                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                let properties = {
                    icon: 'gear',
                    label: null,
                    class: null,
                };

                // Configure Options
                for(const [key, value] of Object.entries(options)){
                    if(typeof properties[key] !== 'undefined'){
                        switch(key){
                            case"callback":
                                if(typeof properties[key] !== 'undefined'){
                                    for(const [k, v] of Object.entries(value)){
                                        if(typeof properties[key][k] !== 'undefined'){
                                            properties[key][k] = v;
                                        }
                                    }
                                }
                                break;
                            default:
                                properties[key] = value;
                                break;
                        }
                    }
                }

                // Set ID
                let id = this._count();

                // Create Category
                var category = $(document.createElement('div')).attr({
                    'id': this._component.menu.id + 'category' + id,
                    'class': 'accordion-item bg-transparent',
                }).appendTo(this._component.menu.accordion);
                category.id = category.attr('id');
                category.properties = properties;

                // Create Header
                category.header = $(document.createElement('h2')).addClass('accordion-header').appendTo(category);

                // Create Button
                category.button = $(document.createElement('button')).attr({
                    'id': category.id + 'button',
                    'class': 'accordion-button collapsed',
                    'type': 'button',
                    'data-bs-toggle': 'collapse',
                    'aria-expanded': 'false',
                }).text(properties.label).appendTo(category.header);
                category.button.id = category.button.attr('id');
                category.button.icon = $(document.createElement('i')).addClass('me-1 bi bi-' + properties.icon).prependTo(category.button);

                // Create Collapse
                category.collapse = $(document.createElement('div')).attr({
                    'id': category.id + 'collapse',
                    'class': 'accordion-collapse collapse',
                    'data-bs-parent': '#' + this._component.menu.id,
                }).appendTo(category);
                category.collapse.id = category.collapse.attr('id');

                // Configure Button
                category.button.attr({
                    'aria-controls': category.collapse.id,
                    'data-bs-target': '#' + category.collapse.id,
                });

                // Create Menu
                category.menu = $(document.createElement('div')).attr({
                    'id': category.id + 'menu',
                    'class': 'accordion-body p-0',
                    'data-bs-parent': '#' + this._component.menu.id,
                }).appendTo(category.collapse);
                category.menu.id = category.menu.attr('id');

                // Create Menu List
                category.menu.list = $(document.createElement('ul')).attr({
                    'id': category.menu.id + 'list',
                    'class': 'list-group list-group-flush w-100',
                }).appendTo(category.menu);
                category.menu.list.id = category.menu.list.attr('id');

                // Add method to add content
                category.add = function(param1 = null, param2 = null){
                    self.#item(category,param1,param2);
                }

                if(properties.icon == null){
                    category.button.icon.remove();
                }

                if(properties.class){
                    category.addClass(properties.class);
                }

                if(this._count <= 1){
                    category.button.removeClass('collapsed').attr('aria-expanded',true);
                    category.collapse.addClass('show');
                }

                if(typeof callback === "function"){
                    callback(category,self);
                }

                // Return
                return this;
            }

            #item(category, param1 = null, param2 = null){

                const self = this;

                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                let properties = {
                    icon: 'gear',
                    label: null,
                    class: null,
                    body: false,
                    form: true,
                    callback:{
                        submit: function(form){},
                        val: function(values){ return values; },
                        reset: function(form){},
                        clear: function(form){},
                    },
                };

                // Configure Options
                for(const [key, value] of Object.entries(options)){
                    if(typeof properties[key] !== 'undefined'){
                        switch(key){
                            case"callback":
                                if(typeof properties[key] !== 'undefined'){
                                    for(const [k, v] of Object.entries(value)){
                                        if(typeof properties[key][k] !== 'undefined'){
                                            properties[key][k] = v;
                                        }
                                    }
                                }
                                break;
                            default:
                                properties[key] = value;
                                break;
                        }
                    }
                }

                // Set ID
                let id = this._count();

                // Create Content Item
                var content = $(document.createElement('div')).attr({
                    'id': category.id + 'content' + id,
                    'class': 'accordion-collapse collapse',
                    'data-bs-parent': '#' + this._component.content.id,
                }).appendTo(this._component.content.accordion);
                content.id = content.attr('id');
                content.properties = properties;

                // Create Header
                content.header = $(document.createElement('div')).attr({
                    'class': 'accordion-header card-body pb-0',
                }).appendTo(content);
                content.header.title = $(document.createElement('h4')).text(properties.label).appendTo(content.header);
                content.header.title.icon = $(document.createElement('i')).addClass('me-1 bi bi-' + properties.icon).prependTo(content.header.title);

                // Create Form
                content.form = this._builder.Component(
                    'form',
                    content,
                    {
                        class: {
                            component: 'card-body',
                        },
                        callback:{
                            submit: function(form){

                                // Check if Submit Callback is a function
                                if(typeof properties.callback.submit === 'function'){
                                    properties.callback.submit(form);
                                }
                            },
                            val: function(values){

                                // Check if Val Callback is a function
                                if(typeof properties.callback.val === 'function'){
                                    return properties.callback.val(values);
                                } else {
                                    return values;
                                }
                            },
                            reset: function(form){

                                // Check if Reset Callback is a function
                                if(typeof properties.callback.reset === 'function'){
                                    properties.callback.reset(form);
                                }
                            },
                            clear: function(form){

                                // Check if Clear Callback is a function
                                if(typeof properties.callback.clear === 'function'){
                                    properties.callback.clear(form);
                                }
                            },
                        },
                    },
                    function(form,component){},
                );

                // Create Body
                content.body = $(document.createElement('div')).appendTo(content.form._component);

                // Create Menu Item
                var item = $(document.createElement('li')).attr({
                    'id': category.id + 'item' + id,
                    'class': 'list-group-item item user-select-none cursor-pointer',
                    'data-bs-toggle': 'collapse',
                    'data-bs-target': '#' + content.id,
                    'style': 'transition: all 300ms ease 0s;',
                }).appendTo(category.menu.list);
                item.id = item.attr('id');
                item.properties = properties;

                // Create Flex
                item.flex = $(document.createElement('div')).addClass('d-flex align-items-center').appendTo(item);
                item.flex.icon = $(document.createElement('div')).addClass('flex-shrink-1 px-1').appendTo(item.flex);
                item.flex.icon.i = $(document.createElement('i')).addClass('bi bi-' + properties.icon).appendTo(item.flex.icon);
                item.flex.label = $(document.createElement('div')).addClass('flex-grow-1 px-1 text-break').text(properties.label).appendTo(item.flex);

                // Check for an icon
                if(properties.icon == null){
                    content.header.title.icon.remove();
                    item.flex.icon.remove();
                }

                // Check for classes to add
                if(properties.class){
                    content.addClass(properties.class);
                }

                // Add Event Listener
                content.on('show.bs.collapse', function(){
                    // Clear Active
                    self._component.menu.find('.active').removeClass('active');
                    // Set Active
                    self._component.menu.find('[data-bs-target="#'+content.id+'"]').addClass('active');
                });

                // Open first item
                if(this._component.content.accordion.children().length > 0){

                    var firstContent = this._component.content.accordion.children().first();
                    firstContent.addClass('show');

                    var firstItem = this._component.menu.find('[data-bs-target="#'+firstContent.attr('id')+'"]');
                    firstItem.addClass('active');

                    var firstCategory = firstItem.parents('.accordion-item');

                    var firstCategoryCollapse = firstCategory.find('.collapse');
                    firstCategoryCollapse.addClass('show');

                    var firstCategoryBtn = firstCategory.find('[data-bs-target="#'+firstCategoryCollapse.attr('id')+'"]');
                    firstCategoryBtn.removeClass('collapsed');
                }

                if(typeof callback === "function"){
                    callback(item,content,self);
                }

                // Return
                return this;
            }
        },
    }

    #components = {
        calendar: class extends this.ComponentClass {

            #events = {};

            _init(){
                this._properties = {
                    themeSystem: 'bootstrap5',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'multiMonthYear,dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    height: 'auto',
                    initialDate: null,
                    initialView: 'dayGridMonth',
                    selectable: false,
                    editable: false,
                    eventDragMinDistance: 0,
                    class: {
                        component: null,
                        calendar: null,
                        header: null,
                    },
                    callback: {
                        dateClick: function(info) {},
                        select: function(info) {},
                        eventClick: function(info) {},
                        eventMouseEnter: function(info) {},
                        eventMouseLeave: function(info) {},
                        eventDrop: function(info) {},
                        eventResize: function(info) {},
                        eventDidMount: function(info) {},
                    },
                    events: [],
                    properties: {
                        start: null,
                        end: null,
                        allDay: false,
                        isBackground: false,
                        color: null,
                        icon: null,
                        title: null,
                        description: null,
                        popover: true,
                        callback: {
                            click: function(info) {},
                            mouseEnter: function(info) {},
                            mouseLeave: function(info) {},
                            drop: function(info) {},
                            resize: function(info) {},
                        },
                    },
                };
            }

            _config(name,options){
                if(typeof this._properties[name] !== 'undefined'){
                    switch(name){
                        case'headerToolbar':
                            for(const [key, value] of Object.entries(options)){
                                if(typeof this._properties[name][key] !== 'undefined'){
                                    this._properties[name][key] = value;
                                }
                            }
                            break;
                        default:
                            this._properties[name] = options;
                            break;
                    }
                }
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'calendar' + this._id,
                    'class': '',
                });
                this._component.id = this._component.attr('id');

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }

                // Set intialDate
                if(this._properties.initialDate === null){
                    this._properties.initialDate = new Date();
                }

                // Set Options
                this._component.properties = {
                    themeSystem: this._properties.themeSystem,
                    headerToolbar: this._properties.headerToolbar,
                    initialDate: this._properties.initialDate,
                    initialView: this._properties.initialView,
                    selectable: this._properties.selectable,
                    editable: this._properties.editable,
                    height: this._properties.height,
                    eventDragMinDistance: this._properties.eventDragMinDistance,
                    dateClick: function(info) { self.#dateClick(info); },
                    select: function(info) { self.#select(info); },
                    eventClick: function(info) { self.#eventClick(info); },
                    eventMouseEnter: function(info) { self.#eventMouseEnter(info); },
                    eventMouseLeave: function(info) { self.#eventMouseLeave(info); },
                    eventDrop: function(info) { self.#eventDrop(info); },
                    eventResize: function(info) { self.#eventResize(info); },
                    eventDidMount: function(info) { self.#eventDidMount(info); },
                    eventContent: function(arg) { return self.#eventContent(arg); },
                };
            }

            _timeout(){

                // Set Self
                const self = this;

                // Create Calendar
                this._component.fullCalendar = new FullCalendar.Calendar(this._component[0], this._component.properties);

                // Set header Class
                setTimeout(function() {
                    if(self._properties.class.header){
                        self._component.find('.fc-header-toolbar').addClass(self._properties.class.header);
                    }
                }, 0);

                // Initialize Calendar
                this.render();

                // Add Events
                for(const [key, value] of Object.entries(this._properties.events)){
                    this.add(value);
                }

                // Add Event to Calendar on Sidebar Toggle
                $('#sidebarToggle').click(function(){
                    self._component.fullCalendar.render();
                });
            }

            render(){

                // Set Self
                const self = this;

                // Get all ancestors of the calendar element
                var ancestors = this._selector.parents();

                // Filter out only the collapsible ancestors
                var collapsibles = ancestors.filter(function() {
                    return $(this).hasClass('collapse');
                });

                // Listen for the shown.bs.collapse event on each collapsible ancestor
                collapsibles.on('shown.bs.collapse', function () {
                    // Call the updateSize method after the collapsible is shown
                    self._component.fullCalendar.updateSize();
                });

                // Render Calendar
                this._component.fullCalendar.render();

                setTimeout(function() {
                    // Call the updateSize method after the timeout
                    self._component.fullCalendar.updateSize();
                }, 0);
            }

            add(param1 = null, param2 = null){

                // Set Self
                const self = this;

                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                let properties = {};

                // Configure Options
                for(const [key, value] of Object.entries(this._properties.properties)){
                    if(typeof properties[key] === 'undefined'){
                        properties[key] = value;
                    }
                }
                for(const [key, value] of Object.entries(options)){
                    if(typeof properties[key] !== 'undefined'){
                        switch(key){
                            case"callback":
                                if(typeof properties[key] !== 'undefined'){
                                    for(const [k, v] of Object.entries(value)){
                                        if(typeof properties[key][k] !== 'undefined'){
                                            properties[key][k] = v;
                                        }
                                    }
                                }
                                break;
                            case"class":
                                for(const [section, classes] of Object.entries(value)){
                                    if(properties[key][section] != null){
                                        properties[key][section] += ' ' + classes;
                                    } else {
                                        properties[key][section] = classes;
                                    }
                                }
                                break;
                            default:
                                properties[key] = value;
                                break;
                        }
                    }
                }

                // Set ID
                const EventID = this._count();

                // Set Event
                var event = {
                    title: properties.title,
                    start: properties.start,
                    end: properties.end,
                    allDay: properties.allDay,
                    id: EventID,
                };

                if(properties.color){
                    event.classNames = 'text-bg-' + properties.color + ' border-' + properties.color;
                }

                if(properties.icon){
                    event.extendedProps = {icon: properties.icon};
                }

                if(properties.isBackground){
                    event.display = 'background';
                }

                // Set Event
                this.#events[EventID] = {
                    id: EventID,
                    event: event,
                    properties: properties,
                    callback: properties.callback,
                };

                // Add Timeout
                setTimeout(function(){

                    // Add Event to Calendar
                    var calendarEvent = self._component.fullCalendar.addEvent(event);

                    // Set Calendar Event
                    self.#events[EventID].calEvent = calendarEvent;

                }, 0);
            }

            #dateClick(info){

                // Execute Callback
                if(typeof this._properties.callback.dateClick === 'function'){
                    this._properties.callback.dateClick(info,this);
                }
            }

            #select(info){

                // Execute Callback
                if(typeof this._properties.callback.select === 'function'){
                    this._properties.callback.select(info,this);
                }
            }

            #eventContent(arg) {
                let arrayOfDomNodes = [];

                let spacerElement = document.createElement('span');
                spacerElement.classList.add('ms-1');
                arrayOfDomNodes.push(spacerElement);

                if (arg.event.extendedProps.icon) {
                    let iconElement = document.createElement('i');
                    iconElement.classList.add('me-1','bi', 'bi-' + arg.event.extendedProps.icon);
                    arrayOfDomNodes.push(iconElement);
                }

                let titleElement = document.createElement('span');
                titleElement.innerText = arg.event.title;
                arrayOfDomNodes.push(titleElement);

                return { domNodes: arrayOfDomNodes };
            }

            #eventClick(info){
                const EventID = info.event._def.publicId;
                const EventName = {calendar: 'eventClick', event: 'click'};

                // Check if Event Exists
                if(typeof this.#events[EventID] !== 'undefined'){

                    const Event = this.#events[EventID];

                    // Execute Calendar Callback
                    if(typeof this._properties.callback[EventName.calendar] === 'function'){
                        this._properties.callback[EventName.calendar](Event,info,this);
                    }

                    // Execute Event Callback
                    if(typeof Event.callback[EventName.event] === 'function'){
                        Event.callback[EventName.event](Event,info,this);
                    }
                }
            }

            #eventMouseEnter(info){
                const EventID = info.event._def.publicId;
                const EventName = {calendar: 'eventMouseEnter', event: 'mouseEnter'};

                // Check if Event Exists
                if(typeof this.#events[EventID] !== 'undefined'){

                    const Event = this.#events[EventID];

                    // Execute Calendar Callback
                    if(typeof this._properties.callback[EventName.calendar] === 'function'){
                        this._properties.callback[EventName.calendar](Event,info,this);
                    }

                    // Execute Event Callback
                    if(typeof Event.callback[EventName.event] === 'function'){
                        Event.callback[EventName.event](Event,info,this);
                    }
                }
            }

            #eventMouseLeave(info){
                const EventID = info.event._def.publicId;
                const EventName = {calendar: 'eventMouseLeave', event: 'mouseLeave'};

                // Check if Event Exists
                if(typeof this.#events[EventID] !== 'undefined'){

                    const Event = this.#events[EventID];

                    // Execute Calendar Callback
                    if(typeof this._properties.callback[EventName.calendar] === 'function'){
                        this._properties.callback[EventName.calendar](Event,info,this);
                    }

                    // Execute Event Callback
                    if(typeof Event.callback[EventName.event] === 'function'){
                        Event.callback[EventName.event](Event,info,this);
                    }
                }
            }

            #eventDrop(info){
                const EventID = info.event._def.publicId;
                const EventName = {calendar: 'eventDrop', event: 'drop'};

                // Check if Event Exists
                if(typeof this.#events[EventID] !== 'undefined'){

                    const Event = this.#events[EventID];

                    // Execute Calendar Callback
                    if(typeof this._properties.callback[EventName.calendar] === 'function'){
                        this._properties.callback[EventName.calendar](Event,info,this);
                    }

                    // Execute Event Callback
                    if(typeof Event.callback[EventName.event] === 'function'){
                        Event.callback[EventName.event](Event,info,this);
                    }
                }
            }

            #eventResize(info){
                const EventID = info.event._def.publicId;
                const EventName = {calendar: 'eventResize', event: 'resize'};

                // Check if Event Exists
                if(typeof this.#events[EventID] !== 'undefined'){

                    const Event = this.#events[EventID];

                    // Execute Calendar Callback
                    if(typeof this._properties.callback[EventName.calendar] === 'function'){
                        this._properties.callback[EventName.calendar](Event,info,this);
                    }

                    // Execute Event Callback
                    if(typeof Event.callback[EventName.event] === 'function'){
                        Event.callback[EventName.event](Event,info,this);
                    }
                }
            }

            #eventDidMount(info){
                const EventID = info.event._def.publicId;
                const EventName = {calendar: 'eventDidMount', event: 'didMount'};

                // Check if Event Exists
                if(typeof this.#events[EventID] !== 'undefined'){

                    const Event = this.#events[EventID];

                    // Create Popover
                    if(Event.properties.popover){
                        // Create focus trigger
                        $(info.el).hover(function() {
                            $(this).trigger('focus');
                        });

                        // Create Popover
                        var title = $(document.createElement('span')).text(Event.properties.title);
                        if(Event.properties.icon){
                            title.icon = $(document.createElement('i')).addClass('me-1 bi bi-' + Event.properties.icon).prependTo(title);
                        }
                        info.el.setAttribute('data-bs-toggle','popover');
                        info.el.setAttribute('data-bs-trigger','focus');
                        info.el.setAttribute('data-bs-html','true');
                        info.el.setAttribute('data-bs-title',title.html());
                        info.el.setAttribute('data-bs-content',Event.properties.description);
                        const popover = bootstrap.Popover.getOrCreateInstance(info.el);
                    }

                    // Execute Calendar Callback
                    if(typeof this._properties.callback[EventName.calendar] === 'function'){
                        this._properties.callback[EventName.calendar](Event,info,this);
                    }

                    // Execute Event Callback
                    if(typeof Event.callback[EventName.event] === 'function'){
                        Event.callback[EventName.event](Event,info,this);
                    }
                }
            }
        },
        form: class extends this.ComponentClass {

            _inputs = {};

            _init(){
                this._properties = {
                    class:{
                        component: null,
                        form: null,
                        input: null,
                        label: null,
                        field: null,
                    },
                    callback:{
                        submit: function(form){},
                        val: function(values){ return values; },
                        init: function(form){},
                        onChange: function(form){},
                        reset: function(form){},
                        clear: function(form){},
                    },
                };
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('form')).attr({
                    'id': 'form' + this._id,
                    'class': '',
                    'method': 'post',
                    'autocomplete': 'off',
                    'enctype': 'multipart/form-data',
                });
                this._component.id = this._component.attr('id');

                // Add a Deprecated Warning
                this._component.deprecated = $(document.createElement('div')).addClass('alert alert-warning alert-dismissible fade show m-4').appendTo(this._component);
                this._component.deprecated.html('<strong>Deprecated:</strong> This component is deprecated and will be removed in a future version. Please use the <code>Form</code> utility instead.');
                this._component.deprecated.close = $(document.createElement('button')).addClass('btn-close').attr({"type": "button", "data-bs-dismiss": "alert", "aria-label": "Close"}).appendTo(this._component.deprecated);

                // Reset Event
                this._component.on('reset', function(e){
                    self.reset();
                });

                // Submit Event
                this._component.on('submit', function(e){
                    e.preventDefault();
                    self.submit();
                });

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }

                // Set Form Class
                if(this._properties.class.form){
                    this._component.addClass(this._properties.class.form);
                }

                // Adding Search Support
                this._builder.Search.add(this._component);
            }

            submit(){

                // Set Self
                const self = this;

                // Callback
                if(typeof this._properties.callback.submit === 'function'){
                    this._properties.callback.submit(this);
                }

                // Return
                return this;
            };

            reset(){

                // Set Self
                const self = this;

                // Reset Values
                for(const [key, input] of Object.entries(self._inputs)){
                    input.reset();
                }

                // Callback
                if(typeof self._properties.callback.reset === 'function'){
                    self._properties.callback.reset(self);
                }

                // Return
                return this;
            };

            focus(){

                // Set Self
                const self = this;

                // Set focus on form
                self._component.focus();

                // Return
                return this;
            }

            init(){

                // Set Self
                const self = this;

                // Reset Values
                for(const [key, input] of Object.entries(self._inputs)){
                    if(typeof input.init === 'function'){
                        input.init();
                    }
                }

                // Callback
                if(typeof self._properties.callback.init === 'function'){
                    self._properties.callback.init(self);
                }

                // Return
                return this;
            }

            clear(){

                // Set Self
                const self = this;

                // Reset Values
                for(const [key, input] of Object.entries(self._inputs)){
                    input.clear();
                }

                // Callback
                if(typeof self._properties.callback.clear === 'function'){
                    self._properties.callback.clear(self);
                }

                // Return
                return this;
            };

            onChange(){

                // Set Self
                const self = this;

                // Reset Values
                for(const [key, input] of Object.entries(self._inputs)){
                    if(typeof input.onChange === 'function'){
                        input.onChange();
                    }
                }

                // Callback
                if(typeof self._properties.callback.onChange === 'function'){
                    self._properties.callback.onChange(self);
                }

                // Return
                return this;
            }

            val(values = null){

                // Set Self
                const self = this;

                // Set Values
                if(typeof values === 'object'){
                    if(values !== null){
                        for(const [key, value] of Object.entries(values)){
                            if(typeof self._inputs[key] !== 'undefined'){
                                self._inputs[key].val(value);
                            }
                        }
                    }
                } else {

                    // Check if values is a string and matches a key
                    if(typeof values === 'string' && typeof self._inputs[values] !== 'undefined'){
                        return self._inputs[values].val();
                    }
                }

                // Retrieve Values
                let object = {};
                for(const [key, input] of Object.entries(self._inputs)){
                    object[key] = input.val();
                }

                // Callback
                if(typeof self._properties.callback.val === 'function'){
                    object = self._properties.callback.val(object);
                }

                // Return
                return object;
            };

            add(param1, param2 = null){

                // Set Self
                const self = this;

                let selector = this._component;
                let options = {};
                let callback = null;

                // Set selector, options, and callback
                [param1, param2].forEach(param => {
                    if(param !== null){
                        if (typeof param === 'object') {
                            options = param;
                        } else if (typeof param === 'function') {
                            callback = param;
                        }
                    }
                });

                // Configure Default Options
                let properties = {
                    name: null,
                    label: null,
                    color: null,
                    icon: 'input-cursor-text',
                    type: 'text',
                    target: null,
                    value: null,
                    step: null,
                    min: 0,
                    max: 100,
                    options: null,
                    modal: null,
                    multiple: false,
                    allowNew: false,
                    class: {
                        input: null,
                        label: null,
                        field: null,
                    },
                    callback: {
                        onChange: function(field,form){},
                        condition: function(form){ return true; },
                        format: function(option, component){ return option.text; },
                    }
                };

                // Overwrite Default Options
                for(const [key, value] of Object.entries(options)){
                    switch(key){
                        case 'class':
                        case 'callback':
                            if(typeof properties[key] === 'object'){
                                for(const [classKey, classValue] of Object.entries(value)){
                                    if(typeof properties[key][classKey] !== 'undefined'){
                                        properties[key][classKey] = classValue;
                                    }
                                }
                            }
                            break;
                        default:
                            if(typeof properties[key] !== 'undefined'){
                                properties[key] = value;
                            }
                            break;
                    }
                }

                // Add Default Options
                switch(properties.type){
                    case 'color':
                        if(properties.icon === null){
                            properties.icon = 'palette';
                        }
                        if(properties.color === null){
                            properties.color = null;
                        }
                        break;
                    case 'textarea':
                        if(properties.icon === null){
                            properties.icon = 'textarea-t';
                        }
                        if(properties.color === null){
                            properties.color = null;
                        }
                        break;
                    case 'select':
                        if(properties.icon === null){
                            properties.icon = 'list';
                        }
                        if(properties.color === null){
                            properties.color = null;
                        }
                        break;
                    case 'ide':
                        if(properties.icon === null){
                            properties.icon = 'code';
                        }
                        if(properties.color === null){
                            properties.color = null;
                        }
                        break;
                    case 'mce':
                        if(properties.icon === null){
                            properties.icon = 'input-cursor-text';
                        }
                        if(properties.color === null){
                            properties.color = null;
                        }
                        break;
                    case 'submit':
                        if(properties.icon === null){
                            properties.icon = 'save';
                        }
                        if(properties.color === null){
                            properties.color = 'success';
                        }
                        break;
                    case 'reset':
                        if(properties.icon === null){
                            properties.icon = 'arrow-clockwise';
                        }
                        if(properties.color === null){
                            properties.color = 'info';
                        }
                        break;
                    case 'clear':
                        if(properties.icon === null){
                            properties.icon = 'x-lg';
                        }
                        if(properties.color === null){
                            properties.color = 'light';
                        }
                        break;
                    default:
                        if(properties.icon === null){
                            properties.icon = 'input-cursor';
                        }
                        if(properties.color === null){
                            properties.color = null;
                        }
                        break;
                }

                // Set ID
                let id = this._count();

                // Create Field
                let field = $(document.createElement('div')).attr({
                    'id': this._component.id + 'group' + id,
                }).appendTo(selector);
                field.id = field.attr('id');
                field.properties = properties;

                // Set Field Class
                if(this._properties.class.field){
                    field.addClass(this._properties.class.field);
                }
                if(properties.class.field){
                    field.addClass(properties.class.field);
                }

                // condition method
                field.condition = function(){

                    // Set Condition
                    if(typeof properties.callback.condition === 'function'){
                        if(!properties.callback.condition(self)){
                            field.addClass('d-none');
                        } else {
                            field.removeClass('d-none');
                        }
                    }
                };

                // onChange method
                field.onChange = function(){

                    // Test Condition
                    field.condition();

                    // Execute Callback
                    if(typeof properties.callback.onChange === 'function'){
                        properties.callback.onChange(field, self);
                    }
                }

                // Create Group
                field.group = $(document.createElement('div')).attr({
                    'class': 'input-group d-flex flex-nowrap',
                }).appendTo(field);

                // Create Label
                field.label = $(document.createElement('label')).attr({
                    'class': 'input-group-text',
                    'for': field.id + 'input',
                }).text(properties.label).appendTo(field.group);

                // Set Label Class
                if(this._properties.class.label){
                    field.label.addClass(this._properties.class.label);
                }
                if(properties.class.label){
                    field.label.addClass(properties.class.label);
                }

                // Create Icon
                field.label.icon = $(document.createElement('i')).addClass('bi bi-' + properties.icon).prependTo(field.label);

                // Add Margin if Label is not Empty
                if(properties.label){
                    field.label.icon.addClass('me-1');
                }

                // Create Input
                switch(properties.type){
                    case 'clear':
                        field.input = $(document.createElement('button')).attr({
                            'id': field.id + 'clear',
                            'class': 'flex-grow-1 btn btn-' + properties.color,
                            'name': properties.name,
                            'type': 'button',
                            'value': properties.value,
                        }).html(field.label.html()).appendTo(field.group);
                        field.label.remove();
                        if(this._properties.class.label){
                            field.input.addClass(this._properties.class.label);
                        }
                        if(properties.class.label){
                            field.input.addClass(properties.class.label);
                        }
                        field.input.click(function(){
                            self.clear();
                        });
                        break;
                    case 'reset':
                        field.input = $(document.createElement('button')).attr({
                            'id': field.id + 'reset',
                            'class': 'flex-grow-1 btn btn-' + properties.color,
                            'name': properties.name,
                            'type': properties.type,
                            'value': properties.value,
                        }).html(field.label.html()).appendTo(field.group);
                        field.label.remove();
                        if(this._properties.class.label){
                            field.input.addClass(this._properties.class.label);
                        }
                        if(properties.class.label){
                            field.input.addClass(properties.class.label);
                        }
                        break;
                    case 'submit':
                        field.input = $(document.createElement('button')).attr({
                            'id': field.id + 'submit',
                            'class': 'flex-grow-1 btn btn-' + properties.color,
                            'name': properties.name,
                            'type': properties.type,
                            'value': properties.value,
                        }).html(field.label.html()).appendTo(field.group);
                        field.label.remove();
                        if(this._properties.class.label){
                            field.input.addClass(this._properties.class.label);
                        }
                        if(properties.class.label){
                            field.input.addClass(properties.class.label);
                        }
                        break;
                    case 'color':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'form-control form-control-color flex-grow-1',
                            'type': 'color',
                            'name': properties.name,
                        }).val(properties.value).appendTo(field.group);
                        // Execute onChange
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'ide':
                        field.input = $(document.createElement('div')).addClass('ide form-control p-0 flex-grow-1').appendTo(field.group);
                        field.input.lines = $(document.createElement('div')).addClass('ide-lines px-0').appendTo(field.input);
                        field.input.editor = $(document.createElement('textarea')).attr({
                            'id': field.id + 'input',
                            'class': 'ide-input',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).text(properties.value).appendTo(field.input);
                        field.input.val = function(value = null){
                            if(value !== null){
                                field.input.editor.val(value);
                                field.input.editor.trigger('propertychange');
                            }
                            return field.input.editor.val();
                        };
                        field.input.editor
                            .keydown(function(e) {
                                if(e.keyCode === 9) {
                                    e.preventDefault();

                                    var start = this.selectionStart;
                                    var end = this.selectionEnd;

                                    this.value = this.value.substring(0, start) + "\t" + this.value.substring(end);
                                    this.selectionStart = this.selectionEnd = start + 1;
                                }
                            })
                            .on('input propertychange', function() {
                                var lines = $(this).val().split('\n').length;
                                field.input.lines.empty();
                                for (var i = 1; i <= lines; i++) {
                                    field.input.lines.append(`<div class="p-0 px-2">${i}</div>`);
                                }
                            })
                            .trigger('propertychange')
                        field.del = function(startLine, endLine = startLine) {

                            // Get current lines from textarea
                            let lines = field.input.editor.val().split('\n');

                            // Ensure the specified lines are within the bounds of the current text
                            if (startLine < 1 || endLine > lines.length || startLine > endLine) {
                                console.warn('Invalid line range');
                                return;
                            }

                            // Remove the specified lines
                            lines.splice(startLine - 1, endLine - startLine + 1);

                            // Update the textarea with the modified text
                            field.input.editor.val(lines.join('\n'));
                            field.input.editor.trigger('propertychange'); // Update line numbers and other listeners
                        }
                        field.highlight = function(...args) {

                            if (args.length === 0) {

                                // If no arguments are provided, highlight all lines
                                field.input.lines.children().each(function () {
                                    $(this).addClass('text-bg-pink');
                                });
                            } else {

                                // Loop through each argument
                                args.forEach(arg => {
                                    if (Array.isArray(arg)) {

                                        // If the argument is an array, treat it as a range [start, end]
                                        let [startLine, endLine] = arg;
                                        for (let i = startLine; i <= endLine; i++) {
                                            if (i > 0 && i <= field.input.lines.children().length) {
                                                let lineElement = field.input.lines.children().eq(i - 1);
                                                if (!lineElement.hasClass('text-bg-pink')) {
                                                    lineElement.addClass('text-bg-pink');
                                                }
                                            }
                                        }
                                    } else {

                                        // If the argument is a single line number
                                        let line = arg;
                                        if (line > 0 && line <= field.input.lines.children().length) {
                                            let lineElement = field.input.lines.children().eq(line - 1);
                                            if (!lineElement.hasClass('text-bg-pink')) {
                                                lineElement.addClass('text-bg-pink');
                                            }
                                        }
                                    }
                                });
                            }
                        }
                        field.unhighlight = function (...args) {

                            if (args.length === 0) {

                                // If no arguments are provided, unhighlight all lines
                                field.input.lines.children().removeClass('text-bg-pink');
                            } else {

                                // Loop through each argument
                                args.forEach(arg => {
                                    if (Array.isArray(arg)) {

                                        // If the argument is an array, treat it as a range [start, end]
                                        let [startLine, endLine] = arg;
                                        for (let i = startLine; i <= endLine; i++) {
                                            if (i > 0 && i <= field.input.lines.children().length) {
                                                let lineElement = field.input.lines.children().eq(i - 1);
                                                if (lineElement.hasClass('text-bg-pink')) {
                                                    lineElement.removeClass('text-bg-pink');
                                                }
                                            }
                                        }
                                    } else {

                                        // If the argument is a single line number
                                        let line = arg;
                                        if (line > 0 && line <= field.input.lines.children().length) {
                                            let lineElement = field.input.lines.children().eq(line - 1);
                                            if (lineElement.hasClass('text-bg-pink')) {
                                                lineElement.removeClass('text-bg-pink');
                                            }
                                        }
                                    }
                                });
                            }
                        }
                        field.highlighted = function (...args) {

                            let isHighlighted = false;

                            // Loop through each argument
                            args.forEach(arg => {
                                if (Array.isArray(arg)) {
                                    // If the argument is an array, treat it as a range [start, end]
                                    let [startLine, endLine] = arg;
                                    for (let i = startLine; i <= endLine; i++) {
                                        if (i > 0 && i <= field.input.lines.children().length) {
                                            let lineElement = field.input.lines.children().eq(i - 1);
                                            if (lineElement.hasClass('text-bg-pink')) {
                                                isHighlighted = true;
                                                return; // Exit as soon as any highlighted line is found
                                            }
                                        }
                                    }
                                } else {
                                    // If the argument is a single line number
                                    let line = arg;
                                    if (line > 0 && line <= field.input.lines.children().length) {
                                        let lineElement = field.input.lines.children().eq(line - 1);
                                        if (lineElement.hasClass('text-bg-pink')) {
                                            isHighlighted = true;
                                            return; // Exit as soon as any highlighted line is found
                                        }
                                    }
                                }
                            });

                            return isHighlighted;
                        }
                        // Execute onChange
                        field.input.editor.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'mce':
                        field.input = $(document.createElement('div')).addClass('mce form-control p-0 flex-grow-1').appendTo(field.group);
                        field.input.editor = $(document.createElement('textarea')).attr({
                            'id': field.id + 'input',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).val(properties.value).appendTo(field.input);
                        field.init = function(){
                            tinymce.init({
                                selector: '#' + field.id + 'input',
                                height: 400,
                                width: '100%',
                                menubar: false,
                                skin: 'oxide-dark',
                                plugins: [
                                    'advlist', 'autolink',
                                    'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'searchreplace', 'visualblocks',
                                    'fullscreen', 'insertdatetime', 'media', 'table', 'help', 'wordcount'
                                ],
                                toolbar: 'undo redo | a11ycheck casechange blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist checklist outdent indent | removeformat | code table help',
                                init_instance_callback: function (editor) {
                                    field.input.editor.mce = editor;
                                    field.input.editor.mce.getBody().setAttribute('contenteditable', true);
                                    var container = $(editor.getContainer());
                                    container.find('.tox-statusbar').addClass("d-none");
                                    if (field.hasClass('rounded-0')) {
                                        container.addClass("rounded-0 border-0");
                                    } else {
                                        container.addClass("rounded-0 rounded-end border-0");
                                    }
                                },
                                setup: function (editor) {
                                    editor.on('change', function(){
                                        self.onChange();
                                    });
                                }
                            });
                        };
                        // Setting or getting the value of the TinyMCE editor
                        field.input.val = function(value = null){
                            if (value !== null) {

                                // If the editor is already initialized, just update the content without removing/re-initializing TinyMCE
                                if (typeof field.input.editor.mce !== 'undefined') {
                                    field.input.editor.mce.setContent(value);  // Set content safely
                                    field.input.editor.mce.focus(); // Ensure focus is on the editor
                                } else {

                                    // If TinyMCE is not initialized, initialize it and set the content
                                    field.input.editor.val(value);
                                    field.init();
                                }
                            }

                            // Return the content of the editor
                            if (typeof field.input.editor.mce !== 'undefined') {
                                return field.input.editor.mce.getContent();
                            } else {
                                return field.input.editor.val();
                            }
                        };
                        field.init();
                        break;
                    case 'textarea':
                        field.input = $(document.createElement('textarea')).attr({
                            'id': field.id + 'input',
                            'class': 'form-control flex-grow-1',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).text(properties.value).appendTo(field.group);
                        // Execute onChange
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'select':
                        field.input = {};
                        field.input.select = $(document.createElement('select')).attr({
                            'id': field.id + 'input',
                            'class': 'form-select flex-grow-1',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).appendTo(field.group);
                        if(properties.multiple){
                            field.input.select.attr('multiple',true);
                            field.input.select.attr('name','[]' + properties.name);
                        }
                        field.options = {};
                        field.delete = function(id = null){
                            if(id){
                                if(typeof field.options[id] !== 'undefined'){
                                    field.options.remove();
                                    delete field.options[id];
                                }
                            } else {
                                for(const [key, element] of Object.entries(field.options)){
                                    element.remove();
                                    delete field.options[key];
                                }
                            }
                        };
                        field.add = function(id,text){
                            if(typeof field.options[id] === 'undefined'){
                                field.options[id] = $(document.createElement('option')).attr('value',id).text(text).appendTo(field.input.select);
                            }
                        }
                        if(properties.options !== null){
                            for(const [key, option] of Object.entries(properties.options)){
                                field.add(option.id,option.text);
                            }
                        }
                        field.init = function(){}
                        field.input.val = function(value = null){
                            if(value !== null && value !== field.input.select.val()){
                                field.input.select.val(value).trigger('change');
                            }
                            return field.input.select.val();
                        }
                        field.init();
                        field.input.val(properties.value);
                        // Execute onChange
                        field.input.select.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'select2':
                        field.input = {};
                        field.input.select = $(document.createElement('select')).attr({
                            'id': field.id + 'input',
                            'class': 'form-select flex-grow-1',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).appendTo(field.group);
                        if(properties.multiple){
                            field.input.select.attr('multiple',true);
                            field.input.select.attr('name','[]' + properties.name);
                        }
                        field.options = {};
                        field.delete = function(id = null){
                            if(id){
                                if(typeof field.options[id] !== 'undefined'){
                                    field.options.remove();
                                    delete field.options[id];
                                }
                            } else {
                                for(const [key, element] of Object.entries(field.options)){
                                    element.remove();
                                    delete field.options[key];
                                }
                            }
                        };
                        field.add = function(id,text){
                            if(typeof field.options[id] === 'undefined'){
                                field.options[id] = $(document.createElement('option')).attr('value',id).text(text).appendTo(field.input.select);
                            }
                        }
                        if(properties.options !== null){
                            for(const [key, option] of Object.entries(properties.options)){
                                field.add(option.id,option.text);
                            }
                        }
                        field.init = function(){
                            let defaults = {
                                theme: "bootstrap-5",
                                width: 'calc(100% - ' + field.label.outerWidth() + 'px)'
                            }
                            if(properties.multiple){
                                defaults.allowClear = true;
                            }
                            if(properties.allowNew){
                                defaults.tags = true;
                            }
                            if(properties.modal !== null){
                                defaults.dropdownParent = properties.modal;
                            }
                            if(typeof properties.callback.format === 'function'){
                                defaults.templateResult = properties.callback.format;
                            }
                            field.select2 = field.input.select.select2(defaults);
                        }
                        field.input.val = function(value = null){
                            if(value !== null && value !== field.input.select.val()){
                                field.input.select.val(value).trigger('change');
                            }
                            return field.input.select.val();
                        }
                        field.init();
                        field.input.val(properties.value);
                        // Execute onChange
                        field.input.select.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'locale':
                        field.input = {};
                        field.input.select = $(document.createElement('select')).attr({
                            'id': field.id + 'input',
                            'class': 'form-select flex-grow-1',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).appendTo(field.group);
                        if(properties.multiple){
                            field.input.select.attr('multiple',true);
                            field.input.select.attr('name','[]' + properties.name);
                        }
                        field.options = {};
                        field.delete = function(id = null){
                            if(id){
                                if(typeof field.options[id] !== 'undefined'){
                                    field.options.remove();
                                    delete field.options[id];
                                }
                            } else {
                                for(const [key, element] of Object.entries(field.options)){
                                    element.remove();
                                    delete field.options[key];
                                }
                            }
                        };
                        field.add = function(id,text){
                            if(typeof field.options[id] === 'undefined'){
                                field.options[id] = $(document.createElement('option')).attr('value',id).text(text).appendTo(field.input.select);
                            }
                        }
                        for(const [key, option] of Object.entries(self._builder.Option.get('locales'))){
                            field.add(option.id,option.text);
                        }
                        field.init = function(){
                            let defaults = {
                                theme: "bootstrap-5",
                                width: 'calc(100% - ' + field.label.outerWidth() + 'px)'
                            }
                            if(properties.multiple){
                                defaults.allowClear = true;
                            }
                            if(properties.allowNew){
                                defaults.tags = true;
                            }
                            if(properties.modal !== null){
                                defaults.dropdownParent = properties.modal;
                            }
                            if(typeof properties.callback.format === 'function'){
                                defaults.templateResult = properties.callback.format;
                            }
                            field.select2 = field.input.select.select2(defaults);
                        }
                        field.input.val = function(value = null){
                            if(value !== null && value !== field.input.select.val()){
                                field.input.select.val(value).trigger('change');
                            }
                            return field.input.select.val();
                        }
                        field.init();
                        field.input.val(properties.value);
                        // Execute onChange
                        field.input.select.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'rating':
                    case 'status':
                    case 'priority':
                        properties.callback.format = function(option, component){

                            // Check if the option is a placeholder
                            if (!option.id) { return option.text; }

                            // Retrieve the rating
                            let targetData = {
                                label: 'Unknown',
                                color: 'secondary',
                                icon: 'question',
                            };
                            switch(properties.type){
                                case 'rating':
                                    targetData = self._builder.Rating.get(properties.target,option.id);
                                    break;
                                case 'status':
                                    targetData = self._builder.Status.get(properties.target,option.id);
                                    break;
                                case 'priority':
                                    targetData = self._builder.Priority.get(properties.target,option.id);
                                    break;
                            }

                            // Create the option
                            var $option = $('<div class="px-3 py-2 animate-flicker-hover text-bg-'+targetData.color+'" style="margin: -.375rem -.75rem!important;"><i class="me-1 bi bi-'+targetData.icon+'"></i>'+targetData.label+'</div>');

                            // Return the option
                            return $option;
                        };
                    case 'source':
                    case 'type':
                        switch(properties.type){
                            case 'rating':
                                properties.targetTable = 'ratings';
                                break;
                            case 'status':
                                properties.targetTable = 'statuses';
                                break;
                            case 'priority':
                                properties.targetTable = 'priorities';
                                break;
                            case 'source':
                                properties.targetTable = 'sources';
                                break;
                            case 'type':
                                properties.targetTable = 'types';
                                break;
                        }
                        field.input = {};
                        field.input.select = $(document.createElement('select')).attr({
                            'id': field.id + 'input',
                            'class': 'form-select flex-grow-1',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).appendTo(field.group);
                        if(properties.multiple){
                            field.input.select.attr('multiple',true);
                            field.input.select.attr('name','[]' + properties.name);
                        }
                        field.options = {};
                        field.delete = function(id = null){
                            if(id){
                                if(typeof field.options[id] !== 'undefined'){
                                    field.options.remove();
                                    delete field.options[id];
                                }
                            } else {
                                for(const [key, element] of Object.entries(field.options)){
                                    element.remove();
                                    delete field.options[key];
                                }
                            }
                        };
                        field.add = function(id,text){
                            if(typeof field.options[id] === 'undefined'){
                                field.options[id] = $(document.createElement('option')).attr('value',id).text(text).appendTo(field.input.select);
                            }
                        }
                        for(const [key, option] of Object.entries(self._builder.Option.get(properties.targetTable,properties.target))){
                            field.add(option.id,option.text);
                        }
                        field.init = function(){
                            let defaults = {
                                theme: "bootstrap-5",
                                width: 'calc(100% - ' + field.label.outerWidth() + 'px)'
                            }
                            if(properties.multiple){
                                defaults.allowClear = true;
                            }
                            if(properties.allowNew){
                                defaults.tags = true;
                            }
                            if(properties.modal !== null){
                                defaults.dropdownParent = properties.modal;
                            }
                            if(typeof properties.callback.format === 'function'){
                                defaults.templateResult = properties.callback.format;
                            }
                            field.select2 = field.input.select.select2(defaults);
                        }
                        field.input.val = function(value = null){
                            if(value !== null && value !== field.input.select.val()){
                                field.input.select.val(value).trigger('change');
                            }
                            return field.input.select.val();
                        }
                        field.init();
                        field.input.val(properties.value);
                        // Execute onChange
                        field.input.select.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'country':
                    case 'state':
                        switch(properties.type){
                            case 'country':
                                properties.targetTable = 'countries';
                                break;
                            case 'state':
                                properties.targetTable = 'states';
                                break;
                        }
                        field.input = {};
                        field.input.select = $(document.createElement('select')).attr({
                            'id': field.id + 'input',
                            'class': 'form-select flex-grow-1',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).appendTo(field.group);
                        if(properties.multiple){
                            field.input.select.attr('multiple',true);
                            field.input.select.attr('name','[]' + properties.name);
                        }
                        field.options = {};
                        field.delete = function(id = null){
                            if(id){
                                if(typeof field.options[id] !== 'undefined'){
                                    field.options.remove();
                                    delete field.options[id];
                                }
                            } else {
                                for(const [key, element] of Object.entries(field.options)){
                                    element.remove();
                                    delete field.options[key];
                                }
                            }
                        };
                        field.add = function(id,text){
                            if(typeof field.options[id] === 'undefined'){
                                field.options[id] = $(document.createElement('option')).attr('value',id).text(text).appendTo(field.input.select);
                            }
                        }
                        if(properties.type === 'country'){
                            for(const [key, option] of Object.entries(self._builder.Option.get(properties.targetTable))){
                                field.add(option.id,option.text);
                            }
                        }
                        if(properties.type === 'state' && typeof self._inputs.country !== 'undefined'){
                            for(const [key, option] of Object.entries(self._builder.Option.get(properties.targetTable,self._inputs.country.val()))){
                                field.add(option.id,option.text);
                            }
                        }
                        field.init = function(){
                            let defaults = {
                                theme: "bootstrap-5",
                                width: 'calc(100% - ' + field.label.outerWidth() + 'px)'
                            }
                            if(properties.multiple){
                                defaults.allowClear = true;
                            }
                            if(properties.allowNew){
                                defaults.tags = true;
                            }
                            if(properties.modal !== null){
                                defaults.dropdownParent = properties.modal;
                            }
                            if(typeof properties.callback.format === 'function'){
                                defaults.templateResult = properties.callback.format;
                            }
                            field.select2 = field.input.select.select2(defaults);
                        }
                        field.input.val = function(value = null){
                            if(value !== null && value !== field.input.select.val()){
                                field.input.select.val(value).trigger('change');
                            }
                            return field.input.select.val();
                        }
                        field.init();
                        field.input.val(properties.value);
                        // Execute onChange
                        field.input.select.on('input change keyup',function(){
                            if(properties.type === 'country' && typeof self._inputs.state !== 'undefined'){
                                self._inputs.state.delete();
                                for(const [key, option] of Object.entries(self._builder.Option.get('states',field.input.val()))){
                                    self._inputs.state.add(option.id,option.text);
                                }
                            }
                            self.onChange();
                        });
                        break;
                    case 'tags':
                    case 'industries':
                        field.input = {};
                        field.input.select = $(document.createElement('select')).attr({
                            'id': field.id + 'input',
                            'class': 'form-select flex-grow-1',
                            'name': properties.name,
                            'autocomplete': 'off',
                        }).appendTo(field.group);
                        field.input.select.attr('multiple',true);
                        field.input.select.attr('name','[]' + properties.name);
                        field.options = {};
                        field.delete = function(id = null){
                            if(id){
                                if(typeof field.options[id] !== 'undefined'){
                                    field.options.remove();
                                    delete field.options[id];
                                }
                            } else {
                                for(const [key, element] of Object.entries(field.options)){
                                    element.remove();
                                    delete field.options[key];
                                }
                            }
                        };
                        field.add = function(id,text){
                            if(typeof field.options[id] === 'undefined'){
                                field.options[id] = $(document.createElement('option')).attr('value',id).text(text).appendTo(field.input.select);
                            }
                        }
                        for(const [key, option] of Object.entries(self._builder.Option.get(properties.type))){
                            field.add(option.id,option.text);
                        }
                        // field.format = function(value) {
                        //     if (!value.id) {
                        //         return value.text;
                        //     }

                        //     var icon = (properties.type == 'tags') ? 'tag' : 'crosshair';
                        //     var color = (properties.type == 'tags') ? 'warning' : 'info';
                        //     var $value = $('<span class="badge text-bg-'+color+'"><i class="me-1 bi-'+icon+'"></i><span></span></span>');

                        //     // Use .text() instead of HTML string concatenation to avoid script injection issues
                        //     $value.find("span").text(value.text);

                        //     return $value;
                        // };
                        field.init = function(){
                            let defaults = {
                                theme: "bootstrap-5",
                                tags: true,
                                // templateSelection: field.format,
                                width: 'calc(100% - ' + field.label.outerWidth() + 'px)'
                            }
                            defaults.allowClear = true;
                            if(properties.allowNew){
                                defaults.tags = true;
                            }
                            if(properties.modal !== null){
                                defaults.dropdownParent = properties.modal;
                            }
                            if(typeof properties.callback.format === 'function'){
                                defaults.templateResult = properties.callback.format;
                            }
                            field.select2 = field.input.select.select2(defaults);
                        }
                        field.input.val = function(value = null){
                            if(value !== null && value !== field.input.select.val()){
                                field.input.select.val(value).trigger('change');
                            }
                            return field.input.select.val();
                        }
                        field.init();
                        field.input.val(properties.value);
                        // Execute onChange
                        field.input.select.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'range':
                        if(properties.value === null){
                            properties.value = properties.min;
                        }
                        field.input = $(document.createElement('div')).addClass('tooltip-range form-control border border-start-0 rounded-end flex-grow-1 px-2 d-flex align-items-center').appendTo(field.group);
                        field.input.range = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-range',
                            'type': properties.type,
                            'step': properties.step,
                            'value': properties.value,
                            'min': properties.min,
                            'max': properties.max,
                        }).appendTo(field.input);
                        field.input.output = $(document.createElement('output')).attr({
                            'for': field.id + 'input',
                        }).appendTo(field.input);
                        if(properties.options !== null && typeof properties.options === "object" && typeof properties.options[properties.value] !== "undefined"){
                            field.input.output.html(properties.options[properties.value]);
                        } else {
                            field.input.output.html(properties.value);
                        }
                        field.input.range.on('input',function(){
                            if(properties.options !== null && typeof properties.options === "object" && typeof properties.options[field.input.range.val()] !== "undefined"){
                                field.input.output.html(properties.options[field.input.range.val()]);
                            } else {
                                field.input.output.html(field.input.range.val());
                            }
                        });
                        field.input.val = function(value = null){
                            if(value !== null){
                                field.input.range.val(value);
                                field.input.range.trigger('input');
                            }

                            return field.input.range.val();
                        };
                        // Execute onChange
                        field.input.range.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'switch':
                        field.input = $(document.createElement('div')).addClass('form-control form-check form-switch m-0 px-2 border border-start-0 rounded-end flex-grow-1 d-flex align-items-center').appendTo(field.group);
                        field.input.switch = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-check-input m-0',
                            'type': 'checkbox',
                            'role': 'switch',
                            'value': properties.value,
                            'style': 'height: 24px;',
                        }).appendTo(field.input);
                        field.input.val = function(value = null){
                            if(value !== null){
                                if(value){
                                    field.input.switch.attr('value',true);
                                    field.input.switch.attr('checked','checked');
                                } else {
                                    field.input.switch.attr('value',false);
                                    field.input.switch.removeAttr('checked');
                                }
                            }

                            return field.input.switch.prop('checked');
                        };
                        field.input.val(properties.value);
                        field.input.switch.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'file':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': properties.type,
                            'value': properties.value,
                        }).appendTo(field.group);

                        // Check if multiple files are allowed
                        if(properties.multiple){
                            field.input.attr('multiple',true);
                            field.input.attr('name','[]' + properties.name);
                        }

                        // Execute onChange
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });

                        // Create a Handler for the file input
                        field.input.val = function(){
                            return new Promise((resolve, reject) => {
                                // Initialize Values
                                let values = [];

                                // Retrieve the files
                                let files = field.input[0].files;

                                // Check if any file is selected
                                if (files.length === 0) {
                                    resolve(values);
                                    return;
                                }

                                // Function to read a single file
                                function readFile(file) {
                                    return new Promise((resolve, reject) => {
                                        let reader = new FileReader();
                                        reader.onload = function(e) {
                                            resolve({
                                                name: file.name,
                                                size: file.size,
                                                type: file.type,
                                                extension: file.name.split('.').pop().toLowerCase(),
                                                content: e.target.result,
                                                icon: getFileIcon(file.name)
                                            });
                                        };
                                        reader.onerror = reject;
                                        reader.readAsDataURL(file);
                                    });
                                }

                                // Function to determine file icon based on extension
                                function getFileIcon(fileName) {
                                    let extension = fileName.split('.').pop().toLowerCase();
                                    switch(extension) {
                                        case 'pdf': return 'file-earmark-pdf';
                                        case 'doc':
                                        case 'docx': return 'file-earmark-word';
                                        case 'xls':
                                        case 'xlsx': return 'file-earmark-excel';
                                        case 'ppt':
                                        case 'pptx': return 'file-earmark-ppt';
                                        case 'zip':
                                        case 'rar': return 'file-earmark-zip';
                                        case 'jpg':
                                        case 'jpeg':
                                        case 'png':
                                        case 'gif':
                                        case 'tiff':
                                        case 'bmp':
                                        case 'webp': return 'file-earmark-image';
                                        case 'mp3':
                                        case 'wav':
                                        case 'wma':
                                        case 'ogg':
                                        case 'm4a': return 'file-earmark-music';
                                        case 'mp4':
                                        case 'avi':
                                        case 'mkv':
                                        case 'wmv':
                                        case 'mov': return 'file-earmark-play';
                                        case 'css':
                                        case 'less':
                                        case 'scss':
                                        case 'sass':
                                        case 'js':
                                        case 'json':
                                        case 'xml':
                                        case 'html':
                                        case 'htm':
                                        case 'php':
                                        case 'asp':
                                        case 'aspx':
                                        case 'jsp':
                                        case 'cfm': return 'file-earmark-code';
                                        case 'txt':
                                        case 'log':
                                        case 'csv':
                                        case 'tsv': return 'file-earmark-text';
                                        case 'msg':
                                        case 'eml': return 'envelope-at';
                                        default: return 'file-earmark';
                                    }
                                }

                                // Read all files
                                let promises = [];
                                for (let i = 0; i < files.length; i++) {
                                    promises.push(readFile(files[i]));
                                }

                                // Resolve all promises
                                Promise.all(promises).then(fileData => {
                                    resolve(fileData);
                                }).catch(error => {
                                    reject(error);
                                });
                            });
                        };
                        break;
                    case 'excel':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'file',
                            'value': properties.value,
                        }).appendTo(field.group);

                        // Check if multiple files are allowed
                        if(properties.multiple){
                            field.input.attr('multiple',true);
                            field.input.attr('name','[]' + properties.name);
                        }

                        // Execute onChange
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });

                        // Create a Handler for the file input
                        field.input.val = function(){
                            return new Promise((resolve, reject) => {
                                // Initialize Values
                                let values = [];

                                // Retrieve the files
                                let files = field.input[0].files;

                                // Check if any file is selected
                                if (files.length === 0) {
                                    resolve(values);
                                    return;
                                }

                                // Function to read a single file
                                function readFile(file) {
                                    return new Promise((resolve, reject) => {
                                        let reader = new FileReader();
                                        reader.onload = function(e) {

                                            // e.target.result will be a data URL (Base64-encoded string)
                                            const dataURL = e.target.result;

                                            // 1) Get the Base64-encoded portion by splitting on the comma
                                            //    "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,UEsDB..."
                                            const base64String = dataURL.split(',')[1];

                                            // 2) Decode the Base64 string to get the raw binary data
                                            const binaryString = atob(base64String);

                                            // 3) Now pass the binary string to XLSX
                                            const workbook = XLSX.read(binaryString, { type: 'binary', cellDates: true, cellNF: true, cellText: false });

                                            // 4) Get the name of the first worksheet
                                            const sheetName = workbook.SheetNames[0];

                                            // 5) Get the worksheet contents
                                            const worksheet = workbook.Sheets[sheetName];

                                            // 6) Convert worksheet to JSON, array, etc.
                                            const jsonData = XLSX.utils.sheet_to_json(worksheet, { raw: false, defval: null, dateNF: 'yyyy-mm-dd hh:mm:ss' });

                                            // Resolve the promise with the file data
                                            resolve({
                                                name: file.name,
                                                size: file.size,
                                                type: file.type,
                                                extension: file.name.split('.').pop().toLowerCase(),
                                                content: e.target.result,
                                                json: jsonData,
                                                icon: getFileIcon(file.name)
                                            });
                                        };
                                        reader.onerror = reject;
                                        reader.readAsDataURL(file);
                                    });
                                }

                                // Function to determine file icon based on extension
                                function getFileIcon(fileName) {
                                    let extension = fileName.split('.').pop().toLowerCase();
                                    switch(extension) {
                                        case 'pdf': return 'file-earmark-pdf';
                                        case 'doc':
                                        case 'docx': return 'file-earmark-word';
                                        case 'xls':
                                        case 'xlsx': return 'file-earmark-excel';
                                        case 'ppt':
                                        case 'pptx': return 'file-earmark-ppt';
                                        case 'zip':
                                        case 'rar': return 'file-earmark-zip';
                                        case 'jpg':
                                        case 'jpeg':
                                        case 'png':
                                        case 'gif':
                                        case 'tiff':
                                        case 'bmp':
                                        case 'webp': return 'file-earmark-image';
                                        case 'mp3':
                                        case 'wav':
                                        case 'wma':
                                        case 'ogg':
                                        case 'm4a': return 'file-earmark-music';
                                        case 'mp4':
                                        case 'avi':
                                        case 'mkv':
                                        case 'wmv':
                                        case 'mov': return 'file-earmark-play';
                                        case 'css':
                                        case 'less':
                                        case 'scss':
                                        case 'sass':
                                        case 'js':
                                        case 'json':
                                        case 'xml':
                                        case 'html':
                                        case 'htm':
                                        case 'php':
                                        case 'asp':
                                        case 'aspx':
                                        case 'jsp':
                                        case 'cfm': return 'file-earmark-code';
                                        case 'txt':
                                        case 'log':
                                        case 'csv':
                                        case 'tsv': return 'file-earmark-text';
                                        case 'msg':
                                        case 'eml': return 'envelope-at';
                                        default: return 'file-earmark';
                                    }
                                }

                                // Read all files
                                let promises = [];
                                for (let i = 0; i < files.length; i++) {
                                    promises.push(readFile(files[i]));
                                }

                                // Resolve all promises
                                Promise.all(promises).then(fileData => {
                                    resolve(fileData);
                                }).catch(error => {
                                    reject(error);
                                });
                            });
                        };
                        break;
                    case 'phone-international':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: "+9 (999) 999-9999[ x9{1,8}]",
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'phone-extension':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: "(999) 999-9999[ x9{1,8}]",
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'phone':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["(999) 999-9999", "+9 (999) 999-9999"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'stopwatch':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["99:99", "99:99:99"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'zipcode':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["99999", "A9A 9A9"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'business-number':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["999999999"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'tax-extension':
                    case 'importer-extension':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["9999"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'ca-tax':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["999999999RT9999"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'ca-gst':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["999999999RT9999"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    case 'ca-importer':
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': 'text',
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.inputmask({
                            mask: ["999999999RM9999"],
                            placeholder: " ",
                            greedy: false,
                            showMaskOnHover: false,
                            showMaskOnFocus: true
                        });
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                    default:
                        field.input = $(document.createElement('input')).attr({
                            'id': field.id + 'input',
                            'class': 'flex-grow-1 form-control',
                            'name': properties.name,
                            'autocomplete': 'off',
                            'type': properties.type,
                            'value': properties.value,
                        }).appendTo(field.group);
                        field.input.on('input change keyup',function(){
                            self.onChange();
                        });
                        break;
                }

                // Set Input Class
                if(typeof field.input !== "undefined" && this._properties.class.input){
                    field.input.addClass(this._properties.class.input);
                }
                if(typeof field.input !== "undefined" && properties.class.input){
                    field.input.addClass(properties.class.input);
                }

                // Set Name
                field.name = properties.name;

                // Val method
                field.val = function(param1 = null){

                    // Set Value
                    if(param1 !== null){

                        // Set Value
                        field.input.val(param1);
                    }

                    // Return Value
                    return field.input.val();
                }

                // Clear method
                field.clear = function(){
                    field.input.val('');
                }

                // Reset method
                field.reset = function(){
                    field.input.val(field.properties.value);
                }

                // Store Input
                this._inputs[field.name] = field;

                // Check for icon
                if(field.properties.icon === null){
                    field.label.icon.remove();
                }

                // Callback
                if(typeof callback === 'function'){
                    callback(field, self);
                }

                // Set Search
                this._builder.Search.set(field);

                // Execute onChange
                self.onChange();

                // Return Field
                return field;
            };
        },
    }

    #inputs = {
        text: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                return $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'text',
                });
            }
        },
        textarea: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                return $(document.createElement('textarea')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                });
            }
        },
        number: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                return $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'number',
                });
            }
        },
        email: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                return $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'email',
                });
            }
        },
        password: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                return $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'password',
                });
            }
        },
        file: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
                this._properties.multiple = false;
            }

            _input(){

                // Create Input
                return $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'file',
                });
            }

            _extend(){
                if (this._properties.multiple) {
                    this._component.input.attr('multiple', true);
                    this._component.input.attr('name', this._properties.name + '[]');
                }
            }

            icon(fileName) {
                let extension = fileName.split('.').pop().toLowerCase();
                switch(extension) {
                    case 'pdf': return 'file-earmark-pdf';
                    case 'doc':
                    case 'docx': return 'file-earmark-word';
                    case 'xls':
                    case 'xlsx': return 'file-earmark-excel';
                    case 'ppt':
                    case 'pptx': return 'file-earmark-ppt';
                    case 'zip':
                    case 'rar': return 'file-earmark-zip';
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                    case 'tiff':
                    case 'bmp':
                    case 'webp': return 'file-earmark-image';
                    case 'mp3':
                    case 'wav':
                    case 'wma':
                    case 'ogg':
                    case 'm4a': return 'file-earmark-music';
                    case 'mp4':
                    case 'avi':
                    case 'mkv':
                    case 'wmv':
                    case 'mov': return 'file-earmark-play';
                    case 'css':
                    case 'less':
                    case 'scss':
                    case 'sass':
                    case 'js':
                    case 'json':
                    case 'xml':
                    case 'html':
                    case 'htm':
                    case 'php':
                    case 'asp':
                    case 'aspx':
                    case 'jsp':
                    case 'cfm': return 'file-earmark-code';
                    case 'txt':
                    case 'log':
                    case 'csv':
                    case 'tsv': return 'file-earmark-text';
                    case 'msg':
                    case 'eml': return 'envelope-at';
                    default: return 'file-earmark';
                }
            }

            read(file) {

                // Set Self
                const self = this;

                // Create File Reader
                return new Promise((resolve, reject) => {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        resolve({
                            name: file.name,
                            size: file.size,
                            type: file.type,
                            extension: file.name.split('.').pop().toLowerCase(),
                            content: e.target.result,
                            icon: self.icon(file.name)
                        });
                    };
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });
            }

            val(value = null){

                // Set Self
                const self = this;

                // Create File Readers
                return new Promise((resolve, reject) => {
                    // Initialize Values
                    let values = [];

                    // Retrieve the files
                    let files = this._component.input[0].files;

                    // Check if any file is selected
                    if (files.length === 0) {
                        resolve(values);
                        return;
                    }

                    // Read all files
                    let promises = [];
                    for (let i = 0; i < files.length; i++) {
                        promises.push(self.read(files[i]));
                    }

                    // Resolve all promises
                    Promise.all(promises).then(fileData => {
                        resolve(fileData);
                    }).catch(error => {
                        reject(error);
                    });
                });
            }
        },
        zipcode: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                const input = $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'text',
                });

                // Configure Input Mask
                input.inputmask({
                    mask: ["99999", "A9A 9A9"],
                    placeholder: " ",
                    greedy: false,
                    showMaskOnHover: false,
                    showMaskOnFocus: true
                });

                // Return Input
                return input;
            }
        },
        phone: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                const input = $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'text',
                });

                // Configure Input Mask
                input.inputmask({
                    mask: ["(999) 999-9999", "+9 (999) 999-9999"],
                    placeholder: " ",
                    greedy: false,
                    showMaskOnHover: false,
                    showMaskOnFocus: true
                });

                // Return Input
                return input;
            }
        },
        phoneExt: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                const input = $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'text',
                });

                // Configure Input Mask
                input.inputmask({
                    mask: "(999) 999-9999[ x9{1,8}]",
                    placeholder: " ",
                    greedy: false,
                    showMaskOnHover: false,
                    showMaskOnFocus: true
                });

                // Return Input
                return input;
            }
        },
        phoneInt: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                const input = $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'text',
                });

                // Configure Input Mask
                input.inputmask({
                    mask: "+9 (999) 999-9999[ x9{1,8}]",
                    placeholder: " ",
                    greedy: false,
                    showMaskOnHover: false,
                    showMaskOnFocus: true
                });

                // Return Input
                return input;
            }
        },
        businessNumber: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                const input = $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'text',
                });

                // Configure Input Mask
                input.inputmask({
                    mask: ["999999999"],
                    placeholder: " ",
                    greedy: false,
                    showMaskOnHover: false,
                    showMaskOnFocus: true
                });

                // Return Input
                return input;
            }
        },
        importerExtension: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                const input = $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'text',
                });

                // Configure Input Mask
                input.inputmask({
                    mask: ["RM9999"],
                    placeholder: " ",
                    greedy: false,
                    showMaskOnHover: false,
                    showMaskOnFocus: true
                });

                // Return Input
                return input;
            }
        },
        taxExtension: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                const input = $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'text',
                });

                // Configure Input Mask
                input.inputmask({
                    mask: ["RT9999"],
                    placeholder: " ",
                    greedy: false,
                    showMaskOnHover: false,
                    showMaskOnFocus: true
                });

                // Return Input
                return input;
            }
        },
        date: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                const input = $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'date',
                });

                // Return Input
                return input;
            }
        },
        time: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                const input = $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'time',
                });

                // // Configure Input Mask
                // input.inputmask({
                //     mask: ["99:99", "99:99:99"],
                //     placeholder: " ",
                //     greedy: false,
                //     showMaskOnHover: false,
                //     showMaskOnFocus: true
                // });

                // Return Input
                return input;
            }
        },
        timer: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                const input = $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-control',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'time',
                });

                // Configure Input Mask
                input.inputmask({
                    mask: ["99:99", "99:99:99"],
                    placeholder: " ",
                    greedy: false,
                    showMaskOnHover: false,
                    showMaskOnFocus: true
                });

                // Return Input
                return input;
            }
        },
        select: class extends this.InputClass {

            _init(){
                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
                this._properties.multiple = false;
                this._properties.options = [];
            }

            _input(){

                // Initialize options
                this._component.options = {};

                // Create Select
                return $(document.createElement('select')).attr({
                    'id': this._component.id + '-input',
                    'class': 'form-select',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                });
            }

            _extend(){
                // Runs during _create() and before insertion.
                // Fix name for multiple after base attributes were set.
                if (this._properties.multiple) {
                    this._component.input.attr('multiple', true);
                    this._component.input.attr('name', this._properties.name + '[]'); // role[]
                }

                // Add Options
                if(Array.isArray(this._properties.options)){
                    for(const [key, option] of Object.entries(this._properties.options)){
                        this.add(option.id, option.text);
                    }
                }

                // Placeholder
                if(this._properties.placeholder){
                    this._component.input.prepend($(document.createElement('option')).attr({
                        'value': '',
                        'disabled': true,
                        'selected': true,
                    }).text(this._properties.placeholder));
                }
            }

            delete(id = null){
                if(id){
                    if(typeof this._component.options[id] !== 'undefined'){
                        this._component.options[id].remove();
                        delete this._component.options[id];
                    }
                } else {
                    for(const [key, element] of Object.entries(this._component.options)){
                        element.remove();
                        delete this._component.options[key];
                    }
                }
            }

            add(id,text){
                if(typeof this._component.options[id] === 'undefined'){
                    this._component.options[id] = $(document.createElement('option')).attr('value',id).text(text).appendTo(this._component.input);
                }
            }
        },
        submit: class extends this.InputClass {

            _init(){

                // Execute Parent Init
                super._init();

                // Set Additional Properties
                this._properties.autocomplete = 'off';
            }

            _input(){

                // Create Input
                return $(document.createElement('input')).attr({
                    'id': this._component.id + '-input',
                    'class': 'btn btn-primary',
                    'name': this._properties.name,
                    'autocomplete': this._properties.autocomplete,
                    'type': 'submit',
                    'value': this._properties.value || 'Submit',
                });
            }
        },
    }

    #widgets = {
        template: class extends this.ComponentClass {

            _init(){
                this._properties = {};
            }

            _create(){

                // Set Self
                const self = this;

                // Create Component
                this._component = $(document.createElement('div')).attr({
                    'id': 'component' + this._id,
                    'class': '',
                });
                this._component.id = this._component.attr('id');

                // Set Component Class
                if(this._properties.class.component){
                    this._component.addClass(this._properties.class.component);
                }
            }
        },
    }
}

// Create Builder
const builder = new Builder();
