import {Backbone} from "backbone_es6";

class SidebarMenu extends Backbone.View {
    /**
     * @param {Auth} options.auth
     */
    initialize (options) {
        this._auth = options.auth;
        this.setupEvents();
    }

    setupEvents () {
        this.$el.find("[data-action]").on("click", (e) => this.runAction($(e.target).data("action")));

        const _runUpdate = () => Promise.resolve().then(() => this.update())
        this.on("rendered", _runUpdate)
        this._auth.on("change", _runUpdate)
    }

    render () {
        this.trigger("rendered");
    }

    runAction (actionName) {
        this.trigger("action:run", {name: actionName});
    }

    update () {
        const isAuthorized = !!this._auth.token;

        this.$el.find("li").each((_, rawEl) => {
            const el = $(rawEl),
                targetStatus = el.data("logged-in").toString()==="1"
            ;

            el.toggleClass("d-none", targetStatus!==isAuthorized);
        });
    }
}

export {SidebarMenu};
