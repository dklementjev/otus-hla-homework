import Debug from "debug";

const debug = Debug("api.user-service");

function logResponse (response) {
    response.text()
        .then((responseText) => {
            debug("response: %O", response.headers);
            debug("response: %s", responseText);
        })
    ;
}

function logFetchError (e) {
    debug("fetch error: %o", e);
}

class UserServiceApi {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
        debug("baseUrl: %s", this.baseUrl)
    }

    async ping() {
        return fetch(this.baseUrl+'/', {})
            .then(
                () => true,
                (e) => {
                    logFetchError(e);
                    return false;
                }
            )
        ;
    }

    async getByToken(token) {
        const response = await fetch (this.generateUrl('/token/'+encodeURIComponent(token)))
            .then((response) => {
                if (!response.ok) {
                    logResponse(response);
                    return null;
                }

                return response.json()
            })
            .catch((e) => {
                logFetchError(e);
                return null;
            })
        ;
        const json = response || null;

        return {
            user_id: json?.user_id || null
        };
    }

    generateUrl(path) {
        const normalizedPath = path.replace(/^\/+/, '');

        return [this.baseUrl, normalizedPath].join('/');
    }
}

export {UserServiceApi}
