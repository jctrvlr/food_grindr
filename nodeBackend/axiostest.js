"use strict";
var axios = require('axios');
const key = '1b90ae9b4a76dd0cf044bfc1332206cf';

console.log(getLocations('brooklyn', '40.597139', '-73.973428'));
function getLocations(loc, lat, lon) {
    console.log("Inside getLocations");
    let url = "https://developers.zomato.com/api/v2.1/locations?query=" + loc + "&lat=" + lat + "&lon=" + lon;
    axios.get(url, {
        headers: { 'Accept': 'application/json', 'user-key': key }
    })
        .then(function (response) {
            console.log("Response from locations: ", response);
            let ent_type = response.data.location_suggestions[0].entity_type;
            let ent_id = response.data.location_suggestions[0].entity_id;
            let zipcode = response.data.location_suggestions[0].zipcode;
            console.log(response.data.location_suggestions[0]);
            let request = { "type": "insert_loc", "loc": loc, "ent_type": ent_type, "ent_id": ent_id, "lat": lat, "lon": lon };

            /*amqp.connect('amqp://test:test@localhost:5672/testHost', function (err, conn) {
                conn.createChannel(function (err, ch) {
                    var q = 'loc';
                    ch.assertQueue(q, { durable: false });
                    ch.sendToQueue(msg.properties.replyTo, new Buffer(JSON.stringify(request)), { correlationId: msg.properties.correlationId });
                });
            });*/

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
            let rest_arr = response.data.best_rated_restaurant;
            console.log(rest_arr);
            let request = { "type": "insert_res", "rest_arr": rest_arr };

            /*amqp.connect('amqp://test:test@localhost:5672/testHost', function (err, conn) {
                conn.createChannel(function (err, ch) {
                    var q = 'loc';
                    ch.assertQueue(q, { durable: false });
                    ch.sendToQueue(msg.properties.replyTo, new Buffer(JSON.stringify(request)), { correlationId: msg.properties.correlationId });
                });
            });*/

        })
        .catch(function (error) {
            console.log(error);
        });
}