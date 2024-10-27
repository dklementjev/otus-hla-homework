require("dotenv").config({path: [".env.local", ".env"]});

const WebSocket = require("ws");
const Debug= require( "debug");
const appDebug = Debug("app");
const SERVER_PORT = process.env.SERVER_PORT;
const SERVER_PING_TIMEOUT = process.env.SERVER_PING_TIMEOUT;

appDebug(`Listening in port ${SERVER_PORT}`);
const wss = new WebSocket.WebSocketServer({port: SERVER_PORT})
wss.on("connection", (ws, req) => {
    const ip = req.socket.remoteAddress;
    const debug = Debug(`wss:${ip}`);

    debug(`Connected`);
    ws.isAlive = true;
    ws.on("close", () => {
        debug("Disconnected");
    })
    ws.on("error", (eventData) => {
        debug("Error: %o", eventData)
    });
    ws.on("message", (data) => {
        debug("Message: %s", data);
    });
    ws.on("pong", () => {
        debug("Pong");
        ws.isAlive = true;
    })
});
appDebug("Setting ping poll for %s", SERVER_PING_TIMEOUT);
const hPingInterval = setInterval(function ping() {
    wss.clients.forEach((ws)=> {
        if (ws.isAlive === false) return ws.terminate();

        ws.isAlive = false;
        ws.ping();
    });
}, SERVER_PING_TIMEOUT);
wss.on("close", () => {
    appDebug("Clearing ping poll");
    clearInterval(hPingInterval);
})
