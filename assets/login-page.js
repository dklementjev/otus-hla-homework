import {Form} from "./utils/Form";
import {Backbone} from "backbone_es6";
import {SidebarMenu} from "./views/SidebarMenu";
import {TokenField} from "./views/login/TokenField";

/**
 * @class
 * @extends {Backbone.Events}
 */
class LoginPage {
    /**
     * @param {AuthAPI} authAPI
     * @param {Auth} auth
     */
    constructor(authAPI, auth) {
        this._authAPI = authAPI;
        this._auth = auth;
        this._form = new Form("login");
        this.initialize();
    }

    initialize() {
        this._auth.load();
        this._sidebarMenu = new SidebarMenu({
            auth: this._auth,
            el: $("#sidebar-menu")
        });
        this._tokenField = new TokenField({
            auth: this._auth,
            el: this._form.getField("token")
        });
        this.setupEvents();
    }

    setupEvents() {
        this._form.getEl().on("submit", this.formSubmitHandler.bind(this));
        this._sidebarMenu.on("action:run", (eventData) => this.runMenuAction(eventData.name));
    }

    render() {
        this._sidebarMenu.render();
        this._tokenField.render();
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
}
Object.assign(LoginPage.prototype, Backbone.Events);

export {LoginPage};
