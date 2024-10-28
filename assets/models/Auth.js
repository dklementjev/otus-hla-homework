
class Auth {
    /** @var {TokenStorage} */
    _tokenStorage = null;
    /** @var {String} */
    _token = null;

    /**
     * @param {TokenStorage} tokenStorage
     */
    constructor(tokenStorage) {
        this._tokenStorage = tokenStorage;
        this.load();
    }

    load() {
        this._token = this._tokenStorage.read();
    }

    save() {
        this._tokenStorage.write(this._token || '');
    }

    get token() {
        return this._token;
    }

    set token(token) {
        this._token = token;
    }
}

export {Auth};
