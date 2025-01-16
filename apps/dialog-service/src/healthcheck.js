import process from "node:process";

const PORT = process.env.PORT;

fetch(`http://127.0.0.1:${PORT}?healthcheck`)
    .catch(() => process.exit(1))
;
