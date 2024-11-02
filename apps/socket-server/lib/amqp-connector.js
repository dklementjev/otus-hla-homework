const debug = require("debug")("amqp-consumer");
const {Connection} = require("rabbitmq-client");

class AmqpConnector {
    constructor () {
        this.connection = null;
        this.consumer = null;
    }

    /**
     * @param {String} host
     * @param {String|Number} port
     * @param {String} login
     * @param {String} password
     */
    connect (host, port, login, password) {
        debug("connect");
        if (this.connection) {
            throw new Error("Already connected");
        }

        this.connection = new Connection(
            this.generateDSN(host, port, login, password)
        );

        this.setupConnectionEvents();
    }

    /**
     * @param {String} queueName
     * @param {Number} prefetch
     * @param {Function} callback
     */
    subscribe (queueName, prefetch, callback) {
        debug("subscribe");
        if (!this.connection) {
            throw new Error("Connection is not established");
        }
        if (this.consumer) {
            throw new Error("Already subscribed");
        }

        this.consumer = this.connection.createConsumer(
            {
                queue: queueName,
                qos: {prefetchCount: prefetch || 1}
            },
            callback
        );

        this.setupConsumerEvents()
    }

    /**
     * @private
     */
    setupConnectionEvents() {
        this.connection.on(
            "connection",
            () => debug("Connected")
        );
        this.connection.on(
            "error",
            (err) => debug("Connection error %o", err)
        );

        const terminationHandler = async (signal) => {
            debug("Terminating on %s", signal);

            if (this.consumer) {
                debug("consumer.close");
                await this.consumer.close();
            }
            if (this.connection && this.connection.ready) {
                debug("connection.close");
                await this.connection.close();
            }

            process.exit(0);
        }

        process.on("SIGINT", terminationHandler);
        process.on("SIGTERM", terminationHandler);
    }

    /**
     * @private
     */
    setupConsumerEvents() {
        this.consumer.on(
            "error",
            (err) => debug("Consumer error %o", err)
        );
    }

    /**
     * @private
     *
     * @param {String} host
     * @param {String|Number} port
     * @param {String} login
     * @param {String} password
     *
     * @returns {String}
     */
    generateDSN (host, port, login, password) {
        return `amqp://${login}:${password}@${host}:${port}`;
    }
}

module.exports = {AmqpConnector};
