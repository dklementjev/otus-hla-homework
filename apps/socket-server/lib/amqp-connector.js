const debug = require("debug")("amqp-consumer");
const {Connection, Consumer} = require("rabbitmq-client");

class AmqpConnector {
    constructor () {
        this.connection = null;
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
     *
     * @returns {Consumer}
     */
    subscribe (queueName, prefetch, callback) {
        debug("subscribe");
        if (!this.connection) {
            throw new Error("Connection is not established");
        }

        const consumer = this.connection.createConsumer(
            {
                queue: queueName,
                qos: {prefetchCount: prefetch || 1}
            },
            callback
        );

        this.setupConsumerEvents(consumer);

        return consumer;
    }

    /**
     * @param {String} exchangeName
     * @param {String} topic
     * @param {String} queueName
     * @param {Number} prefetch
     * @param callback
     *
     * @returns {Consumer}
     */
    subscribeToTopic (exchangeName, topic, queueName, prefetch, callback) {
        debug("subscribeToTopic %s, %s, %s, %s", exchangeName, topic, queueName, prefetch);
        if (!this.connection) {
            throw new Error("Connection is not established");
        }

        const consumer = this.connection.createConsumer(
            {
                queue: queueName,
                qos: {prefetchCount: prefetch || 1},
                queueBindings: [{exchange: exchangeName, routingKey: topic}],
                queueOptions: {autoDelete: true}
            },
            callback
        );

        this.setupConsumerEvents(consumer);

        return consumer;
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
     * @param {Consumer} consumer
     */
    setupConsumerEvents(consumer) {
        consumer.on(
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
