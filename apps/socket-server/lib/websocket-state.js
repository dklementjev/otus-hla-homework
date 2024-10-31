class WebsocketState {
    /** @var {String|null} */
    _token = null;

    /** @var {boolean} */
    _isAuthenticated = false;

    constructor() {}

    /**
     * @param {String|null} token
     */
    login (token) {
        if (token && this._isAuthenticated && this._token !== token) {
            throw new Error("Already authenticated");
        }
        this._isAuthenticated = !!token;
        this._token = token;
    }

    logout () {
        this._isAuthenticated = false;
        this._token = null;
    }

    get token () {
        return this._token;
    }

    get isAuthenticated () {
        return this._isAuthenticated;
    }
}

class WebsocketStateMap {
    /** @var WeakMap.<WebSocket, WebsocketState> */
    _map = null;

    constructor () {
        this._map = new WeakMap();
    }

    /**
     * @param {WebSocket} ws
     * @returns {WebsocketState}
     */
    add (ws) {
        let item = this.get(ws);

        if (!item) {
            item = new WebsocketState();
            this._map.set(ws, item);
        }

        return item;
    }

    /**
     * @param {WebSocket} ws
     *
     * @returns {WebsocketState|undefined}
     */
    get (ws) {
        return this._map.get(ws);
    }

    delete (ws) {
        this._map.delete(ws);
    }
}

module.exports = {WebsocketState, WebsocketStateMap};
