class TokenStorage {
    /**
     * @abstract
     * @return {string|null}
     */
    read() {
        throw new Error("Abstract method");
    }

    /**
     * @abstract
     * @param {string|null} data
     * @return void
     */
    write(data) {
        throw new Error("Abstract method");
    }
}

export {TokenStorage};
