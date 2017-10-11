var amqp = require('amqplib/callback_api');
var axios = require('axios');


const key = '1b90ae9b4a76dd0cf044bfc1332206cf';

// Change localhost to correct IP of rabbitMQ server
amqp.connect('amqp://localhost', function (err, conn) {
    conn.createChannel(function (err, ch) {
        var q = 'loc';

        ch.assertQueue(q, { durable: false });
        ch.prefetch(1);
        console.log(' [x] Awaiting RPC requests');
        ch.consume(q, function reply(msg) {
            var content = JSON.parse(msg.content);
            if (!msg.content.type) {

            }
            switch (content.type) {
                case "get_loc":
                    var loc = content.loc;
                    var lat = content.lat;
                    var lon = content.lon;
                    console.log('Got message from get_loc queue: ', content);
                    var r = "Response to get_loc message";
                    //var r = getLocations(loc, lat, lon);
                    ch.sendToQueue(msg.properties.replyTo, new Buffer(JSON.stringify(r)), { correlationId: msg.properties.correlationId });
                    ch.ack(msg);
                case "calc_dist":

            }
        });
    });
});