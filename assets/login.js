import {Container} from "./utils/Container";
import {SessionStorage} from "./models/token-storage/SessionStoage";
import {Auth} from "./models/Auth";
import {Form} from "./utils/Form";
import {Urlconf} from "./utils/Urlconf";
import {AuthAPI} from "./api/AuthAPI";

class Login {
    /**
     * @param {AuthAPI} authAPI
     * @param {Auth} auth
     */
    constructor(authAPI, auth) {
        this._authAPI = authAPI;
        this._auth = auth;
        this._form = new Form("login");
        this.initialize()
        this.setupEvents();
    }

    initialize() {
        this._auth.load();
        this._form.getField('token').val(this._auth.token);
    }

    setupEvents() {
        this._form.getEl().on("submit", this.formSubmitHandler.bind(this));
    }

    render() {

    }

    formSubmitHandler (e) {
        const loginEl = this._form.getField("login"),
            passwordEl = this._form.getField("password"),
            tokenEl = this._form.getField('token')
        ;
        e.preventDefault();
        this._authAPI.login(loginEl.val(), passwordEl.val())
            .then(
                (response) => {
                    return response.token;
                },
                () => {
                    return null;
                }
            )
            .then((token) => {
                tokenEl.val(token);
                this._auth.token = token;
                this._auth.save()
            })
        ;
    }
}

const container = Container.getInstance()

// TODO: move to common services
container.set(
    'urlconf',
    () => new Urlconf(window.apiBaseUrl, window.urlconf)
);
container.set(
    'token-storage',
    () => new SessionStorage('authToken')
);
container.set(
    'auth',
    () => new Auth(container.get('token-storage'))
);
container.set(
    'api.auth',
    () => new AuthAPI(container.get('urlconf'))
);

const loginPage = new Login(
    container.get('api.auth'),
    container.get('auth')
);
loginPage.render();
