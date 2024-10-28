class AuthAPI {
    /**
     * @param {Urlconf} urlconf
     */
    constructor(urlconf) {
        this._urlconf = urlconf;
    }

    /**
     *
     * @param username
     * @param password
     * @returns {Promise<{token: string}>}
     */
    login (username, password) {
        return fetch(
            new Request(
                this._urlconf.get('auth_login'),
                {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({id: username, password: password}),
                    cache: "no-cache"
                },
            )
        )
        .then((response) => {
            if (response.status === 200) {
                return response.json();
            }

            return Promise.reject(null);
        })
    }
}

export {AuthAPI};
