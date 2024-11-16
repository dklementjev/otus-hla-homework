import {Backbone} from "backbone_es6";
import {Post} from "./models/Post";

class PostsPage {
    /**
     * @param {PostsAPI} postsAPI
     * @param {jQuery} el
     */
    constructor(postsAPI, el) {
        this._postsAPI = postsAPI;
        this._el = el;
        this._collection = new Backbone.Collection();
        this.setupEvents();
    }

    setupEvents () {
        this.on("hide", () => this.hideHandler());
        this.on("show", () => this.showHandler());
        this._collection.on("all", (eventName) => console.log("collection event", eventName));
    }

    render () {
        this.hide();
        this.trigger("rendered");
    }

    show () {
        this._el.show();
        this.trigger("show");
    }

    hide () {
        this._el.hide();
        this.trigger("hide");
    }

    hideHandler () {
        this._collection.reset();
    }

    showHandler () {
        this._postsAPI.getFeed()
            .then((json) => {
                for (const rawDataRow of json) {
                    this._collection.add(new Post({
                        uuid: rawDataRow.id,
                        text: rawDataRow.text,
                        authorUUID: rawDataRow.author_user_id
                    }))
                }
            })
        ;
    }
}
Object.assign(PostsPage.prototype, Backbone.Events);

export {PostsPage};
