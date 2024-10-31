const debug = require("debug")("api.auth");

class AuthApi {
    constructor(baseUrl) {
        debug("baseUrl: %s", baseUrl);
        this.baseUrl = baseUrl;
    }

    /**
     * @param {String|null} token
     * @returns {Promise<int|null>}
     */
    checkToken (token) {
        debug("checkToken: %s", token);

        return fetch(
            new Request(
                `${this.baseUrl}/user/me`,
                {
                    method: "GET",
                    headers: {
                        'Authorization': `Bearer ${token}`,
                    },
                    cache: "no-cache"
                },
            )
        )
        .then((response) => {
            switch (response.status) {
                case 200:
                    return response.json();

                case 401:
                    return null;
            }

            return Promise.reject(`Unhandled response status: ${response.status}`);
        })
        .then((json) => {
            const userId = json && json.id || null;
            debug("userId: %s", userId);

            return userId;
        })
    ;
    }
}

module.exports = {AuthApi};
