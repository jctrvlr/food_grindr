$(function(){
  $('#toggle').click(function(){
    $('#target').toggleClass('active');
  });
});


function poopValues(){
var name = "EXAMPLE";
var rating = "Rating: ";
var address = "Address: ";
var phone = "Phone Number: "
var price = "Price Range: "
var cuisine = "Cuisine: ";
var hours = "Hours: "
var delivery = "Delivery: "
/*Get a boolean to output yes or no*/

document.getElementById("name").innerHTML = "Paragraph changed!"; 
document.getElementById("name").innerHTML  = name;
document.getElementById("rating").innerHTML  = rating;
document.getElementById("address").innerHTML  = address;
document.getElementById("phone").innerHTML = phone;
document.getElementById("price").innerHTML = price;
document.getElementById("cuisine").innerHTML  = cuisine;
document.getElementById("hours").innerHTML = hours;
document.getElementById("delivery").innerHTML  = delivery;

}


