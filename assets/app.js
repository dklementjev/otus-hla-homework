import './styles/app.css';

import {LoginPage} from "./login-page";
import {Container} from "./utils/Container";
import {Urlconf} from "./utils/Urlconf";
import {SessionStorage} from "./models/token-storage/SessionStoage";
import {Auth} from "./models/Auth";
import {AuthAPI} from "./api/AuthAPI";
import {PostsPage} from "./posts-page";
import {SidebarBlock} from "./sidebar";

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
}

class App {
    /**
     * @param {Auth} auth
     * @param {AuthAPI} authAPI
     */
    constructor(auth, authAPI) {
        this._auth = auth;
        this._authAPI = authAPI;
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
    }

    render () {
        this.loginPage.render();
        this.postsPage.render()
        this.sidebar.render();
    }

    sidebarActionHandler(eventData) {
        this.runMenuAction(eventData.name || null);
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

const dic = Container.getInstance()
setupCore(dic);

const app = new App(
    dic.get("auth"),
    dic.get('api.auth')
);
app.render();
