
function deferMicrotask(callable) {
    return () => Promise.resolve().then(callable)
}

export {deferMicrotask}
