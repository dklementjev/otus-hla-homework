/* global Deno */
import Debug from "debug";
import Koa from "koa";
import {Client as PostgreClient} from "https://deno.land/x/postgres/mod.ts";
import R from "koa-route";

const appDebug = Debug("app");
const httpDebug = Debug("app.http");

const PORT = Deno.env.get('PORT');

async function pingAction(ctx) {
    ctx.body = 'OK';
}

async function tokenAction(ctx, token) {
    const sth = await dbConnection.queryArray(
        'SELECT id, user_id FROM app_access_tokens WHERE token=$token',
        {token}
    );
    if (sth.rows.length>0) {
        ctx.response.body = {user_id: sth.rows[0][1]};
    } else {
        ctx.response.body = null;
        ctx.response.status = 404;
    }
}

async function userSearchAction(ctx) {
    const firstName = ctx.request.query.first_name || null;
    const lastName = ctx.request.query.last_name || null;

    if (!firstName || !lastName) {
        ctx.response.status = 404;
        return;
    }

    const sth = await dbConnection.queryObject(
        'SELECT * FROM app_users AS u WHERE starts_with(lower(u.first_name), $first_name) AND starts_with(lower(u.last_name), $last_name) ORDER BY u.id',
        {
            first_name: firstName.toLowerCase(),
            last_name: lastName.toLowerCase()
        }
    );

    httpDebug('userSearch for "%s" "%s": %s results', firstName, lastName, sth.rows.length);
    const users = sth.rows.map(
        (rawUser) => ({id: rawUser.id, first_name: rawUser.first_name, second_name: rawUser.last_name})
    );

    ctx.response.body = {items: users};
}

const app = new Koa();

function getDBOptions(debugSQL) {
    const res = {
        hostname: Deno.env.get('POSTGRESQL_HOST'),
        port: Deno.env.get('POSTGRESQL_PORT'),
        user: Deno.env.get('POSTGRESQL_USER'),
        password: Deno.env.get('POSTGRESQL_PASSWORD'),
        database: Deno.env.get('POSTGRESQL_DB'),
    };

    if (debugSQL) {
        Object.assign(
            res,
            {
                controls: {
                    debug: {
                        queries: true,
                        notices: true,
                        results: true,
                        queryInError: true,
                    }
                }
            }
        )
    }

    return res;
}

const dbConnection = new PostgreClient(getDBOptions(false))
app.use(async (ctx, next) => {
    const req = ctx.request;
    const res = ctx.response;
    const requestId = ctx.request.header['x-request-id'] || '<empty>';

    httpDebug('> [%s] %s %s', requestId, req.method, req.url);
    await next();
    httpDebug('< [%s] %s %s', requestId, res.status, res.message);
    ctx.response.append('x-request-id', requestId);
});
app.use(R.get('/', pingAction))
app.use(R.get('/user/search', userSearchAction));
app.use(R.get('/token/:token', tokenAction));

appDebug(`Connecting to DB`);
await dbConnection.connect();
appDebug(`Listening on ${PORT}`);
app.listen(PORT);
