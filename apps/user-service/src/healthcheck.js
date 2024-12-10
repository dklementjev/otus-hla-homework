const PORT = Deno.env.get('PORT');

fetch(`http://127.0.0.1:${PORT}`)
    .catch(() => process.exit(1))
;
