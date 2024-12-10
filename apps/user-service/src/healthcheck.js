/* global Deno */
const PORT = Deno.env.get('PORT');

fetch(`http://127.0.0.1:${PORT}?healthcheck`)
    .catch(() => process.exit(1))
;
