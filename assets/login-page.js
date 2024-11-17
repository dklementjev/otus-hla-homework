import {Form} from "./utils/Form";
import {Backbone} from "backbone_es6";
import {TokenField} from "./views/login/TokenField";

/**
 * @class
 * @extends {Backbone.Events}
 */
class LoginPage {
    /**
     * @param {AuthAPI} authAPI
     * @param {Auth} auth
     * @param {jQuery} el
     */
    constructor(authAPI, auth, el) {
        this._authAPI = authAPI;
        this._auth = auth;
        this._el = el;
        this._form = new Form("login");
        this.initialize();
    }

    initialize() {
        this._auth.load();
        this._tokenField = new TokenField({
            auth: this._auth,
            el: this._form.getField("token")
        });
        this.setupEvents();
    }

    setupEvents() {
        this._form.getEl().on("submit", this.formSubmitHandler.bind(this));
    }

    render() {
        this.hide();
        this._tokenField.render();
        this.trigger("rendered");
    }

    hide() {
        this._el.hide();
    }

    show() {
        this._el.show();
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
Object.assign(LoginPage.prototype, Backbone.Events);

export {LoginPage};
