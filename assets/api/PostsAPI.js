
class PostsAPI {
    /**
     * @param {Urlconf} urlconf
     * @param {Auth} auth
     */
    constructor(urlconf, auth) {
        this._urlconf = urlconf;
        this._auth = auth;
    }

    getFeed () {
        return fetch(
            new Request(
                this._urlconf.get('post_feed'),
                {
                    method: "GET",
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.getBearerToken()}`
                    },
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

    getBearerToken () {
        return this._auth.token || null;
    }
}

export {PostsAPI};
