import {Backbone} from "backbone_es6";

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
        this.setupEvents();
        this.load();
    }

    setupEvents () {
        this.on("all", (eventName, eventData) => console.log("Auth event", eventName, eventData));
    }

    load() {
        this._token = this._tokenStorage.read();
        this.trigger("load");
    }

    save() {
        this._tokenStorage.write(this._token || '');
        this.trigger("save");
    }

    get token() {
        return this._token;
    }

    set token(token) {
        if (token!==this._token) {
            this.trigger("change", {value: token});
        }

        this._token = token;
    }
}
Object.assign(Auth.prototype, Backbone.Events);

export {Auth};
