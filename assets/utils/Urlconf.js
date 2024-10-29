class Urlconf {
    /**
     * @param {String} baseUrl
     * @param {Object.<string, string>} paths
     */
    constructor(baseUrl, paths) {
        this._baseUrl = baseUrl.replace(/\/$/, '');
        this._paths = paths;
    }

    /**
     * @returns {String}
     */
    getBaseUrl () {
        return this._baseUrl;
    }

    /**
     * @param {String} name
     *
     * @returns {String|null}
     */
    get (name) {
        return this._join(this._paths[name] || null);
    }

    _join (path) {
        if (!path) {
            return null;
        }

        return [this._baseUrl, path.replace(/^\//, '')].join('/');
    }
}

export {Urlconf};
