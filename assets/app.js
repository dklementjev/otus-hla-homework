import './styles/app.css';

import {LoginPage} from "./login-page";
import {Container} from "./utils/Container";
import {Urlconf} from "./utils/Urlconf";
import {SessionStorage} from "./models/token-storage/SessionStoage";
import {Auth} from "./models/Auth";
import {AuthAPI} from "./api/AuthAPI";
import {PostsPage} from "./posts-page";
import {SidebarBlock} from "./sidebar";
import {PostsAPI} from "./api/PostsAPI";
import {UserNotificationsAPI} from "./api/UserNotificationsAPI";
import {Backbone} from "backbone_es6";

/**
 * @param {Container} container
 */
function setupCore(container) {
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
    container.set(
        'api.posts',
        () => new PostsAPI(container.get('urlconf'), container.get('auth'))
    );
    container.set(
        'api.user_notifications',
        () => new UserNotificationsAPI(window.wsApiBaseUrl)
    )
}

class App {
    /**
     * @param {Auth} auth
     * @param {AuthAPI} authAPI
     * @param {PostsAPI} postsAPI
     * @param {UserNotificationsAPI} notificationsAPI
     */
    constructor(auth, authAPI, postsAPI, notificationsAPI) {
        this._auth = auth;
        this._authAPI = authAPI;
        this._postsAPI = postsAPI;
        this._notificationsAPI = notificationsAPI;
        this._pageViews = new Map();
        this.setupChildViews();
        this.setupEvents();
    }

    setupChildViews() {
        this.loginPage = new LoginPage(
            this._authAPI,
            this._auth,
            $("#login-page")
        );
        this.postsPage = new PostsPage(
            this._postsAPI,
            this._notificationsAPI,
            $("#posts-page")
        );

        this._pageViews.set("login", this.loginPage);
        this._pageViews.set("posts", this.postsPage);

        this.sidebar = new SidebarBlock(
            $("#sidebar")
        );
    }

    showPageView(tag) {
        this._pageViews.forEach((pageView, pageViewTag) => {
            if (pageViewTag === tag) {
                pageView.show();
            } else {
                pageView.hide();
            }
        });
    }

    setupEvents () {
        this.sidebar.on("action", (eventData) => this.sidebarActionHandler(eventData));
        this._auth.on("load change", () => this.authChangeHandler());
        this.once("rendered", async () => {
            await this._notificationsAPI.connect();
            this.updateNotificationsAuth()
        });
    }

    render () {
        this.loginPage.render();
        this.postsPage.render()
        this.sidebar.render();
        this.trigger("rendered");
    }

    sidebarActionHandler(eventData) {
        this.runMenuAction(eventData.name || null);
    }

    authChangeHandler () {
        this.updateNotificationsAuth();
    }

    updateNotificationsAuth() {
        if (this._auth.token) {
            this._notificationsAPI.login(this._auth.token);
        } else {
            this._notificationsAPI.logout();
        }
    }

    runMenuAction (actionName) {
        console.log("runMenuAction", actionName);
        switch (actionName) {
            case "login":
                this.showPageView("login");
                break;
            case "posts":
                this.showPageView("posts");
                break;
            case "logout":
                this._auth.token = null;
                this._auth.save();

                break;
        }
    }
}
Object.assign(App.prototype, Backbone.Events);

const dic = Container.getInstance()
setupCore(dic);

const app = new App(
    dic.get("auth"),
    dic.get('api.auth'),
    dic.get('api.posts'),
    dic.get('api.user_notifications')
);
app.render();
