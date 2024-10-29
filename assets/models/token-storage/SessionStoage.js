import {TokenStorage} from "./TokenStorage";

class SessionStorage extends TokenStorage {
    /** @var {String} */
    _name = null;

    constructor (name="authToken") {
        super();

        this._name = name;
    }

    read() {
        return window.sessionStorage.getItem(this._name);
    }

    write(data) {
        window.sessionStorage.setItem(this._name, data);
    }
}

export {SessionStorage};
