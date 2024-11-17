import {Backbone} from "backbone_es6";
import {Post as PostModel} from "./models/Post";
import {Post as PostView} from "./views/Post";

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
        this._collection.on("all", (eventName, arg1, arg2) => console.log("collection event", eventName, arg1, arg2));
        this._collection.on("reset", this.collectionResetHandler, this);
        this._collection.on("add", this.collectionAddHandler, this);
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
                this._collection.set(
                    json.map((rawDataRow) => new PostModel(rawDataRow)),
                    {merge: true}
                )
            })
        ;
    }

    collectionResetHandler () {
        this.getPostsContainerEl().empty();
    }

    collectionAddHandler (model) {
        const postView = new PostView({
            model: model,
            el: $('<div></div>')
        });
        postView.render();
        this.getPostsContainerEl().append(postView.getEl());
    }

    /**
     * @returns {jQuery}
     */
    getPostsContainerEl () {
        return this._el.find("#posts");
    }
}
Object.assign(PostsPage.prototype, Backbone.Events);

export {PostsPage};
