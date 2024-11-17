import {Backbone} from "backbone_es6";
import {Post as PostModel} from "./models/Post";
import {Post as PostView} from "./views/Post";

class PostsPage {
    /**
     * @param {PostsAPI} postsAPI
     * @param {UserNotificationsAPI} notificationsAPI
     * @param {jQuery} el
     */
    constructor(postsAPI, notificationsAPI, el) {
        this._postsAPI = postsAPI;
        this._notificationsAPI = notificationsAPI;
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
        this._notificationsAPI.on("command.receive", this.commandReceivedHandler, this);
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

    /**
     * @returns {bool}
     */
    isVisible () {
        return this._el.is(":visible");
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
        const postView = this.renderPostModel(model);

        this.getPostsContainerEl().append(postView.getEl());
    }

    commandReceivedHandler (eventData) {
        const {command: commandName, data} = eventData;

        switch (commandName) {
            case "post.added":
                this.postAddedCommandHandler(data);
                break;
        }
    }

    postAddedCommandHandler (data) {
        const postModel = new PostModel({
            id: data.postID,
            text: data.postText,
            author_user_id: data.author_user_id
        });
        const postView = this.renderPostModel(postModel);

        this.getPostsContainerEl().prepend(postView.getEl());
    }

    renderPostModel (model) {
        const postView = new PostView({
            model: model,
            el: $('<div></div>')
        });
        postView.render();

        return postView;
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
