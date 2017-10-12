"use strict";
var amqp = require('amqplib/callback_api');
var axios = require('axios');


const key = '1b90ae9b4a76dd0cf044bfc1332206cf';

// Change localhost to correct IP of rabbitMQ server
amqp.connect('amqp://test:test@localhost:5672/testHost', function (err, conn) {
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

                    console.log("Inside switchcase: ", msg);

                    var r = getLocations(loc, lat, lon);
                    //var r = getLocations(loc, lat, lon);
                    ch.sendToQueue(msg.properties.replyTo, new Buffer(JSON.stringify(r)), { correlationId: msg.properties.correlationId });
                    ch.ack(msg);
            }
        });
    });
});
function getLocations(loc, lat, lon) {
    console.log("Inside getLocations");
    let url = "https://developers.zomato.com/api/v2.1/locations?query=" + loc + "&lat=" + lat + "&lon=" + lon;
    axios.get(url, {
        headers: { 'Accept': 'application/json', 'user-key': key }
    })
        .then(function (response) {
            console.log("Response from locations: ", response);
            let ent_type = response.location_suggestions[0].entity_type;
            let ent_id = response.location_suggestions[0].entity_id;
            let zipcode = response.location_suggestions[0].zipcode;

            let request = { "type": "insert_loc", "loc": loc, "ent_type": ent_type, "ent_id": ent_id, "lat": lat, "lon": lon };

            amqp.connect('amqp://test:test@localhost:5672/testHost', function (err, conn) {
                conn.createChannel(function (err, ch) {
                    var q = 'loc';
                    ch.assertQueue(q, { durable: false });
                    ch.sendToQueue(msg.properties.replyTo, new Buffer(JSON.stringify(request)), { correlationId: msg.properties.correlationId });
                });
            });

            let res = getRestaurants(ent_id, ent_type);
            return res;
        })
        .catch(function (error) {
            console.log(error);
        });
}

function getRestaurants(ent_id, ent_type) {
    console.log("Inside getRestaurants");
    let url = "https://developers.zomato.com/api/v2.1/location_details?entity_id=" + ent_id + "&entity_type=" + ent_type;
    axios.get(url, {
        headers: { 'Accept': 'application/json', 'user-key': key }
    })
        .then(function (response) {
            let rest_arr = response.best_rated_restaurant;
            let request = { "type": "insert_res", "rest_arr": rest_arr };

            amqp.connect('amqp://test:test@localhost:5672/testHost', function (err, conn) {
                conn.createChannel(function (err, ch) {
                    var q = 'loc';
                    ch.assertQueue(q, { durable: false });
                    ch.sendToQueue(msg.properties.replyTo, new Buffer(JSON.stringify(request)), { correlationId: msg.properties.correlationId });
                });
            });

        })
        .catch(function (error) {
            console.log(error);
        });
}
