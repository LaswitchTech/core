class Endpoint {

    _route = null;
    _endpoint = null;
    #error = [];
    #success = [];
    _data = null;
    _debug = false;
    _type = 'GET';
    _dataType = 'json';
    _headers = {};
    _suppressErrors = false;

    constructor(route = null){

        // Set Root of API
        this._route = route;
    }

    header(key = null, value = null){
        if(typeof key === 'string' && typeof value === 'string'){
            this._headers[key] = value;
        }
        return this;
    }

    endpoint(endpoint = null){
        this._endpoint = (typeof endpoint === 'string') ? endpoint : this._endpoint;
        return this;
    }

    data(data = null){
        this._data = (typeof data === 'object') ? data : this._data;
        this._type = (this._data !== null) ? 'POST' : 'GET';
        return this;
    }

    debug(state = true){
        this._debug = (state === true);
        return this;
    }

    error(callback = null){
        if(typeof callback === 'function') this.#error.push(callback);
        return this;
    }

    success(callback = null){
        if(typeof callback === 'function') this.#success.push(callback);
        return this;
    }

    #trace() {
        const e = new Error();
        if (!e.stack) return '';
        const lines = e.stack.split('\n');

        // Skip frames belonging to this class; find the first "external" frame.
        const idx = lines.findIndex((ln, i) =>
            i > 1 && !/Endpoint\._?(execute|endpoint|data|debug|success|error|header)\b/.test(ln)
        );

        const frame = lines[Math.max(2, idx)]; // fallback to 3rd line if not found

        // Chrome/Edge: "    at fn (http://.../api.js:83:17)"
        let m = frame.match(/\(?([^\s)]+):(\d+):(\d+)\)?$/);
        if (m) return `${m[1].split('/').pop()}(${m[2]})`;

        // Firefox/Safari: "fn@http://.../api.js:83:17"
        m = frame.match(/@(.+):(\d+):(\d+)/);
        if (m) return `${m[1].split('/').pop()}(${m[2]})`;

        return '';
    }

    clear(){
        this._endpoint = null;
        this._data = null;
        this._type = 'GET';
        this._suppressErrors = false;
        return this;
    }

    suppress(state = true){
        this._suppressErrors = (state === true);
        return this;
    }

    execute(resolve = null, reject = null){
        const self = this;
        if(this._endpoint === null){
            if(this._debug) console.warn('API.endpoint(String)');
            return false;
        }
        const endpoint = (this._route !== null) ? this._route + this._endpoint : this._endpoint;
        const trace = this.#trace();
        const prefix = `${trace}${endpoint} Response:`;
        const suppress = this._suppressErrors;
        $.ajax({
            url: endpoint,
            type: this._type,
            dataType: this._dataType,
            headers: this._headers,
            data: (this._data !== null) ? this._data : {},
            error: function(xhr, status, error){
                if(!suppress) {
                    if(self._debug) console.error(prefix, xhr, xhr.status, error);
                    if(self.#error.length > 0) for(const callback of self.#error) { callback(xhr, xhr.status, error, endpoint); }
                }
                if(typeof reject === 'function') reject(xhr, xhr.status, error, endpoint);
            },
            success: function(response){
                if(self._debug) console.log(prefix, response);
                if(self.#success.length > 0) for(const callback of self.#success) { callback(response, endpoint); }
                if(typeof resolve === 'function') resolve(response, endpoint);
            }
        });
        this.clear();
        return this;
    }
}

// Create Endpoint Instance
const API = new Endpoint('/api').header('X-CSRF-Authorization', CSRF_KEY);
