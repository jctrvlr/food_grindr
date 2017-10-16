"use strict";
var amqp = require('amqplib/callback_api');
var axios = require('axios');


const key = '1b90ae9b4a76dd0cf044bfc1332206cf';

// Change localhost to correct IP of rabbitMQ server
amqp.connect('amqp://test:test@localhost:5672/testHost', function (err, conn) {
    conn.createChannel(function (err, ch) {
        var q = 'backendApi';

        ch.assertQueue(q, { durable: true });
        ch.prefetch(1);
        console.log(' [x] Awaiting RPC requests');
        ch.consume(q, function reply(msg) {
            var content = JSON.parse(msg.content);
            if(!msg.content.type) {

            }
            switch(content.type) {
                case "get_loc":
                    var loc = content.loc;
                    var lat = content.lat;
                    var lon = content.lon;
                    var zip = content.zip;
                    console.log(loc, lat, lon, zip);
                    var r = getLocations(loc, lat, lon);
                    console.log(r);
                    ch.sendToQueue(msg.properties.replyTo, new Buffer(JSON.stringify(r)), { correlationId: msg.properties.correlationId });
                    ch.ack(msg);
                case "calc_dist":

            }
        });
    });
});

function getLocations(loc, lat, lon) {
    let url = "https://developers.zomato.com/api/v2.1/locations?query=" + loc + "&lat=" + lat + "&lon=" + lon;
    console.log(url);
    axios.get(url, {
        headers: { 'Accept': 'application/json', 'user-key': key }
    })
    .then(function (response) {
        console.log("Response from locations: ", response);
        let ent_type = response.data.location_suggestions[0].entity_type;
        let ent_id = response.data.location_suggestions[0].entity_id;
        let zipcode = response.data.location_suggestions[0].zipcode;

        let request = { "type":"insert_loc", "loc": loc, "ent_type": ent_type, "ent_id": ent_id, "lat": lat, "lon": lon };

        amqp.connect('amqp://test:test@localhost:5672/testHost', function (err, conn) {
            conn.createChannel(function (err, ch) {
                var q = 'loc';
                ch.assertExchange('testExchange', 'topic', {durable: false});
                ch.publish('testExchange', 'loc', new Buffer(JSON.stringify(request)));
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
    let url = "https://developers.zomato.com/api/v2.1/location_details?entity_id=" + ent_id + "&entity_type=" + ent_type;
    axios.get(url, {
        headers: { 'Accept': 'application/json', 'user-key': key }
    })
        .then(function (response) {
            let rest_arr = response.data.best_rated_restaurant;
            let request = { "type": "insert_res", "rest_arr": rest_arr };

            amqp.connect('amqp://test:test@localhost:5672/testHost', function (err, conn) {
                conn.createChannel(function (err, ch) {
                    var q = 'dataQueue';
                    ch.assertExchange('dataExchnge', 'topic', {durable: false});
                    ch.publish('dataExchnge', 'dataQueue', new Buffer(JSON.stringify(request)));
                });
            });

        })
        .catch(function (error) {
            console.log(error);
        });
}

function sendLog(level, loc, mess) {
    
}
// Calculate distance from restaurant to users location
function degreesToRadians(degrees) {
    return degrees * Math.PI / 180;
}

function distanceInKmBetweenEarthCoordinates(lat1, lon1, lat2, lon2) {
    var earthRadiusKm = 6371;

    var dLat = degreesToRadians(lat2 - lat1);
    var dLon = degreesToRadians(lon2 - lon1);

    lat1 = degreesToRadians(lat1);
    lat2 = degreesToRadians(lat2);

    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.sin(dLon / 2) * Math.sin(dLon / 2) * Math.cos(lat1) * Math.cos(lat2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return earthRadiusKm * c;
}
