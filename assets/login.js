import {Container} from "./utils/Container";
import {SessionStorage} from "./models/token-storage/SessionStoage";
import {Auth} from "./models/Auth";
import {Form} from "./utils/Form";
import {Urlconf} from "./utils/Urlconf";
import {AuthAPI} from "./api/AuthAPI";
import {Backbone} from "backbone_es6";

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
    }

    setupEvents() {
        const _runTokenChangeHandler = () => Promise.resolve().then(() => this.tokenChangeHandler());
        this._auth.on("change", _runTokenChangeHandler);
        this.on("rendered", _runTokenChangeHandler);

        this._form.getEl().on("submit", this.formSubmitHandler.bind(this));
        this.getSidebarMenu().find("[data-action]").on("click", (e) => this.runMenuAction($(e.target).data("action")))
    }

    render() {
        this.trigger("rendered");
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

    runMenuAction (actionName) {
        switch (actionName) {
            case "logout":
                this._auth.token = null;
                this._auth.save();

                break;
        }
    }

    tokenChangeHandler () {
        this.updateSidebarMenu();
        this.updateTokenInput();
    }

    updateTokenInput () {
        this._form.getField('token').val(this._auth.token);
    }

    updateSidebarMenu () {
        const menuEl = this.getSidebarMenu(),
            isAuthorized = !!this._auth.token
        ;

        menuEl.find("li").each((_, rawEl) => {
            const el = $(rawEl),
                targetStatus = el.data("logged-in").toString()==="1"
            ;

            el.toggleClass("d-none", targetStatus!==isAuthorized);
        })
    }

    getSidebarMenu () {
        return $("#sidebar-menu");
    }
}
Object.assign(Login.prototype, Backbone.Events);

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
