const debug = require("debug")("message-processor");

class MessageProcessor {
    /**
     * @param {WebsocketStateMap} wsStateMap
     * @param {AuthApi} authApi
     */
    constructor(wsStateMap, authApi) {
        this.wsStateMap = wsStateMap;
        this.authApi = authApi;
    }

    /**
     * @param {WebSocket} ws
     * @param {Object} data
     */
    processLoginCommand(ws, data) {
        const token = data.token || null;
        return Promise.resolve()
            .then (() => {
                return this.authApi.checkToken(token);
            })
            .then((userId) => {
                debug("Got userId %s", userId);

                const state = this.wsStateMap.add(ws);
                state.login(token);
            })
        ;
    }

    /**
     * @param {WebSocket} ws
     */
    processLogoutCommand(ws) {
        const state = this.wsStateMap.get(ws);
        state && state.logout();

        return null;
    }

    /**
     * @param {WebSocket} ws
     */
    processStatusCommand(ws) {
        const state = this.wsStateMap.add(ws);

        return {isAuthenticated: state.isAuthenticated};
    }

    /**
     * @param {WebSocket} ws
     * @param {String} rawData
     */
    process(ws, rawData) {
        debug("processMessage: %s", rawData);
        Promise.resolve()
            .then(() => {
                let data;

                try {
                    data = JSON.parse(rawData);
                } catch (e) {
                    throw new Error("Malformed JSON");
                }

                return data;
            })
            .then((data) => {
                const command = data.command || "<empty>";
                debug("Command: %s", command);

                switch (command) {
                    case "login":
                        return this.processLoginCommand(ws, data);

                    case "logout":
                        return this.processLogoutCommand(ws);

                    case "status":
                        return this.processStatusCommand(ws);

                    default:
                        throw new Error("Unknown command");
                }
            })
            .then((res) => {
                ws.send(JSON.stringify({success: true, msg: null, data: res}));
                debug("processMessage done");
            })
            .catch((errorText) => {
                ws.send(JSON.stringify({success: false, msg: errorText.toString()}));
                debug("processMessage failed: %o", errorText);
            })
        ;
    }
}

module.exports = {MessageProcessor};
