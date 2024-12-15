import Dotenv from "dotenv";
Dotenv.config({
    debug: true,
    path: [".env.local", ".env"],
});

import { bodyParser } from "@koa/bodyparser";
import Debug from "debug";
import Koa from "koa";
import process from "node:process";
import R from "koa-route";
import {UserServiceApi} from "./user-service-api.js";
import {DialogApi} from "./dialog-api.js";

const appDebug = Debug("app");
const httpDebug = Debug("app.http");

const PORT = process.env.PORT;

const USER_SERVICE_API_URL = process.env.USER_SERVICE_API_URL;

const TARANTOOL_DB_HOST = process.env.TARANTOOL_DB_HOST;
const TARANTOOL_DB_PORT = process.env.TARANTOOL_DB_PORT;
const TARANTOOL_DB_USER = process.env.TARANTOOL_DB_USER;
const TARANTOOL_DB_PASSWORD = process.env.TARANTOOL_DB_PASSWORD;

async function pingAction(ctx) {
    ctx.body = 'OK';
}

async function parseBearerToken(ctx, next) {
    const authorizationHeader = ctx.request.headers.authorization || '';
    const bearerToken = authorizationHeader.replace(/^Bearer\s+/i, '');

    ctx.state.bearerToken = bearerToken.trim() || null

    await next();
}

async function checkBearerToken(ctx, next) {
    const bearerToken = ctx.state.bearerToken || null;
    let user = null;

    if (bearerToken) {
        const userData = await app.userServiceApi.getByToken(bearerToken);
        user = userData?.user_id || null;
    }
    ctx.state.user = user;

    await next();
}

function requiresUser(callback) {
    return (ctx, next) => {
        if (!ctx.state.user) {
            ctx.response.body = null;
            ctx.response.status = 401;
            httpDebug("requiresUser: user is not set");

            return;
        }

        return callback(ctx, next);
    }
}

async function dialogMessageCreateAction (ctx, rawOtherUserId) {

    const userId = ctx.state.user;
    const otherUserId = parseInt(rawOtherUserId, 10);
    const messageText = ctx.request.body.text;

    httpDebug("dialogMessageCreateAction: userId=%s, otherUserId=%s, message=%s", userId, otherUserId, messageText);

    if (!messageText) {
        ctx.response.status = 400;
        return;
    }

    const dialog = await app.dialogApi.getPMForUsers(userId, otherUserId);
    httpDebug("dialogMessageCreateAction: dialogId=%s", dialog.id)

    const message = await app.dialogApi.createMessage(
        userId,
        dialog.id,
        messageText
    );
    httpDebug("dialogMessageCreateAction: message=%o", message);

    ctx.response.body = {'success': true};
}

async function dialogListAction (ctx, rawOtherUserId) {
    const userId = ctx.state.user;
    const otherUserId = parseInt(rawOtherUserId, 10);

    const dialog = await app.dialogApi.getOrCreatePMForUsers(userId, otherUserId);
    httpDebug("dialogList res: %o", dialog);
    const messages = await app.dialogApi.getRecentMessages(dialog.id);
    httpDebug("dialogList messages: %o", messages);

    ctx.response.body = messages;
}

const app = new Koa();
app.userServiceApi = new UserServiceApi(
    USER_SERVICE_API_URL
);
app.dialogApi = new DialogApi(
    TARANTOOL_DB_HOST,
    TARANTOOL_DB_PORT,
    TARANTOOL_DB_USER,
    TARANTOOL_DB_PASSWORD
);
app
    .use(async (ctx, next) => {
        const req = ctx.request;
        const res = ctx.response;

        httpDebug('> %s %s', req.method, req.url);
        await next();
        httpDebug('< %s %s (%s)', res.status, res.message, req.url);
    })
    .use(R.get('/', pingAction))
    .use(parseBearerToken)
    .use(checkBearerToken)
    .use(bodyParser())
    .use(R.post('/dialog/:user_id/send', requiresUser(dialogMessageCreateAction)))
    .use(R.get('/dialog/:user_id/list', requiresUser(dialogListAction)))
;
await app.dialogApi.connect();

appDebug(`Listening on ${PORT}`);
app.listen(PORT);
