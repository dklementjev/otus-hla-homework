import Debug from "debug";
import Koa from "koa";
import { Client as PostgreClient} from "https://deno.land/x/postgres/mod.ts";
import R from "koa-route";

const appDebug = Debug("app");
const httpDebug = Debug("app.http");

const PORT = Deno.env.get('PORT');

async function pingAction(ctx) {
    ctx.body = "OK";
}

async function tokenAction(ctx, token: String) {
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

const app = new Koa();
const dbOptions = {
    hostname: Deno.env.get('POSTGRESQL_HOST'),
    port: Deno.env.get('POSTGRESQL_PORT'),
    user: Deno.env.get('POSTGRESQL_USER'),
    password: Deno.env.get('POSTGRESQL_PASSWORD'),
    database: Deno.env.get('POSTGRESQL_DB'),
}

const dbConnection = new PostgreClient(dbOptions)
app.use(async (ctx, next) => {
    const req = ctx.request;
    const res = ctx.response;

    httpDebug('> %s %s', req.method, req.url);
    await next();
    httpDebug('< %s %s', res.status, res.message);
});
app.use(R.get('/', pingAction))
app.use(R.get('/token/:token', tokenAction));

appDebug(`Connecting to DB`);
await dbConnection.connect();
appDebug(`Listening on ${PORT}`);
app.listen(PORT);
