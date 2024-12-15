import Debug from "debug";
import TarantoolConnection from "tarantool-driver";

const debug = Debug("api.dialog");

class Hydrator {
    hydrateDialog (rawData) {
        debug("hydrateDialog %o", rawData);

        if (!rawData) {
            return null;
        }

        return {
            id: rawData.id,
            isGroupchat: rawData.is_groupchat,
            uuid: rawData.uuid,
            createdAt: new Date(rawData.created_at),
            participants: rawData.participants
        };
    }

    hydrateMessage (rawData) {
        debug("hydrateMessage %o", rawData);

        if (!rawData) {
            return null;
        }

        return {
            id: rawData.id,
            userId: rawData.user_id,
            dialogId: rawData.dialog_id,
            createdAt: new Date(rawData.created_at),
            uuid: rawData.uuid,
            text: rawData.message
        };
    }
}

class ReturnValue {
    constructor (rawValue) {
        this.rawValue = rawValue;
    }

    getFirst () {
        return this.getNth(0);
    }

    getNth (index) {
        if (index>=this.rawValue.length) {
            return null;
        }

        return this.rawValue[index];
    }
}

class DialogApi {
    constructor (dbHost, dbPort, dbUser, dbPassword) {
        debug('host=%s, port=%s, user=%s', dbHost, dbPort, dbUser);
        this.connection = new TarantoolConnection({
            host: dbHost,
            port: dbPort,
            username: dbUser,
            password: dbPassword,
            lazyConnect: true,
        });
        this.hydrator = new Hydrator();
    }

    connect () {
        debug("connect");

        return this.connection.connect();
    }

    disconnect () {
        debug("disconnect");

        return this.connection.disconnect();
    }

    async getPMForUsers (userId, otherUserId) {
        debug("getPMForUsers(%s, %s)", userId, otherUserId);

        const rawRes = await this.callUDF('box.space.dialogs:getPMForUsers', [userId, otherUserId]);
        debug("getPMForUsers: %o", rawRes);
        const res = new ReturnValue(rawRes);
        const rawDialogs =  res.getFirst();
        const rawDialog = rawDialogs && rawDialogs[0] || null;

        return this.hydrator.hydrateDialog(rawDialog);
    }

    async createPMForUsers (userId, otherUserId) {
        debug("createPMForUsers(%s, %s)", userId, otherUserId);

        const rawRes = await this.callUDF('box.space.dialogs:createPMForUsers', [userId, otherUserId]);
        debug("createPMForUsers: %o", rawRes);
        const res = new ReturnValue(rawRes);
        const rawDialogs = res.getFirst();
        const rawDialog = rawDialogs && rawDialogs[0] || null;

        return this.hydrator.hydrateDialog(rawDialog);
    }

    async getOrCreatePMForUsers (userId, otherUserId) {
        debug("getOrCreatePMForUsers(%s, %s)", userId, otherUserId);

        let res = await this.getPMForUsers(userId, otherUserId);
        if (!res) {
            res = await this.createPMForUsers(userId, otherUserId);
        }
        debug("getOrCreatePMForUsers res: %o", res);

        return res;
    }

    async createMessage (userId, dialogId, text) {
        debug("createMessage(%s, %s, %s)", userId, dialogId, text);

        const rawRes = await this.callUDF(
            'box.space.messages:create',
            [
                crypto.randomUUID(),
                userId,
                dialogId,
                text,
                (new Date()).toISOString()
            ]
        );
        debug("createMessage res: %o", rawRes);
        const res = new ReturnValue(rawRes);
        const rawMessages = res.getFirst();
        const rawMessage = rawMessages && rawMessages[0] || null;

        return this.hydrator.hydrateMessage(rawMessage);
    }

    async getRecentMessages(dialogId) {
        debug("getRecentMessages(%s)", dialogId);

        const rawRes = await this.callUDF('box.space.messages:findByDialogId', [dialogId, 100]);
        debug("getRecentMessages res: %o", rawRes);
        const res = new ReturnValue(rawRes);
        const messages = res.getFirst() || [];

        return messages.map(this.hydrator.hydrateMessage);
    }

    callUDF (udfName, udfArguments) {
        debug("callUDF(%s, %o)", udfName, udfArguments);

        return this.connection.call(udfName, ...udfArguments);
    }

    runEval (evalCode, evalArguments) {
        debug("runEval(%s, %o)", evalCode, evalArguments);

        return this.connection.eval(evalCode, ...evalArguments);
    }
}

export {DialogApi};
