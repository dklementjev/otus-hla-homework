require("dotenv").config({path: [".env.local", ".env"]});

const WebSocket = require("ws");
const Debug= require( "debug");
const {MessageProcessor} = require("./lib/message-processor");
const {WebsocketStateMap} = require("./lib/websocket-state");
const {AuthApi} = require("./lib/auth-api");
const {AmqpConnector} = require("./lib/amqp-connector");
const appDebug = Debug("app");
const SERVER_PORT = process.env.SERVER_PORT;
const SERVER_PING_TIMEOUT = process.env.SERVER_PING_TIMEOUT;
const AMQP_EXCHANGE_NAME = process.env.AMQP_EXCHANGE_NAME;

const authApi = new AuthApi(process.env.AUTH_API_URL);
const messageProcessor = new MessageProcessor(new WebsocketStateMap(), authApi);
const amqpConnector = new AmqpConnector();
amqpConnector.connect(process.env.RABBITMQ_HOST, process.env.RABBITMQ_PORT, process.env.RABBITMQ_USER, process.env.RABBITMQ_PASSWORD);

messageProcessor.on("login", (eventData) => {
    const ws = eventData.websocket;
    const subscriptionTopic = `user_notification.*.${eventData.userId}`;
    const queueName = `user_notifications.${eventData.userId}`;

    ws.consumer = amqpConnector.subscribeToTopic(AMQP_EXCHANGE_NAME, subscriptionTopic, queueName, 1, (message) => {
        appDebug("amqp message received: %o", message);

        const messageBody = message.body || {};
        const commandName = messageBody.command || null;
        const commandData = messageBody.data || {};

        ws.send(JSON.stringify({command: commandName, data: commandData}));
    });
});
messageProcessor.on("logout", (eventData) => {
    const ws = eventData.websocket;
    ws.consumer.close();
});

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
        messageProcessor.process(ws, data);
    });
    ws.on("pong", () => {
        debug("Pong");
        ws.isAlive = true;
    });
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
