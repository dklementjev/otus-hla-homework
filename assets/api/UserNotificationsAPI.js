import {Backbone} from "backbone_es6";

/**
 * @property {WebSocket} _ws
 */
class UserNotificationsAPI {
    /**
     * @param {String} endpoint
     */
    constructor (endpoint) {
        this._endpoint = endpoint;
        this._ws = null;
        this.setupEvents();
    }

    setupEvents () {
        this.on("all", (eventName, eventData) => console.log("UserNotificationsAPI", eventName, eventData));
        this.on("ws.message", this.wsMessageHandler, this);
        this.on("message", this.messageHandler, this);
        this.on("command.receive", this.commandReceiveHandler);
    }

    connect () {
        this._ws = new WebSocket(this._endpoint);
        this._ws.onmessage = (eventData) => this.trigger("ws.message", eventData)
        this._ws.onopen = (eventData) => this.trigger("ws.open", eventData);
        this._ws.onclose = (eventData) => this.trigger("ws.close", eventData);
        this._ws.onerror = (eventData) => this.trigger("ws.error", eventData);

        return new Promise((resolve) => {
            this.once("ws.open", resolve);
        });
    }

    disconnect () {
        if (this.isConnected()) {
            this._ws.close();
        }
    }

    isConnected () {
        return !!this._ws;
    }

    /**
     * @param {String} token
     */
    login (token) {
        this._sendCommand("login", {token})
    }

    logout () {
        this._sendCommand("logout");
    }

    wsMessageHandler (eventData) {
        this.trigger("message", JSON.parse(eventData.data || "null"));
    }

    messageHandler (eventData) {
        const isCommandResponse = (typeof(eventData.success) !== "undefined" && typeof(eventData.id) !== "undefined");

        if (isCommandResponse) {
            this.trigger("command.response", {...eventData});
        } else {
            this.trigger("command.receive", {...eventData});
        }
    }

    commandReceiveHandler (eventData) {
        const commandName = eventData.command;
    }

    /**
     * @param {String} name
     * @param {Object} args
     *
     * @private
     */
    _sendCommand (name, args) {
        if (!this.isConnected()) {
            throw new Error("WS is not connected");
        }
        const correlationId = crypto.randomUUID();
        const commandData = {command: name, id: correlationId, ...args};
        this.trigger("command.send", commandData);

        this._ws.send(
            JSON.stringify(commandData)
        );

        return correlationId;
    }
}
Object.assign(UserNotificationsAPI.prototype, Backbone.Events);

export {UserNotificationsAPI}
