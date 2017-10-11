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
            if(!msg.content.type) {

            }
            switch(content.type) {
                case "get_loc":
                    var loc = content.loc;
                    var lat = content.lat;
                    var lon = content.lon;

                    var r = getLocations(loc, lat, lon);
                    ch.sendToQueue(msg.properties.replyTo, new Buffer(JSON.stringify(r)), { correlationId: msg.properties.correlationId });
                    ch.ack(msg);
                case "calc_dist":

            }
        });
    });
});

function getLocations(loc, lat, lon) {
    let url = "https://developers.zomato.com/api/v2.1/locations?query=" + loc + "&lat=" + lat + "&lon=" + lon;
    axios.get(url, {
        headers: { 'Accept': 'application/json', 'user-key': key }
    })
    .then(function (response) {
        console.log("Response from locations: ", response);
        let ent_type = response.location_suggestions[0].entity_type;
        let ent_id = response.location_suggestions[0].entity_id;

        // Insert into cities database here

        let res = getRestaurants(ent_id, ent_type);
        return res;
    })
    .catch(function (error) {
        console.log(error);
    });
    // send back json array
}

function getRestaurants(ent_id, ent_type) {
    let url = "https://developers.zomato.com/api/v2.1/location_details?entity_id=" + ent_id + "&entity_type=" + ent_type;
    axios.get(url, {
        headers: { 'Accept': 'application/json', 'user-key': key }
    })
        .then(function (response) {

            console.log(response);
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