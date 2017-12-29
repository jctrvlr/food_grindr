$(function(){
  $('#toggle').click(function(){
    $('#target').toggleClass('active');
  });
});


function poopValues(){
var name = "EXAMPLE";
var rating = "First name: ";
var address = "Last name: ";
var phone = "Email: "
var price = "Password: "

/*Get a boolean to output yes or no*/

document.getElementById("name").innerHTML = "Paragraph changed!"; 
document.getElementById("name").innerHTML  = name;
document.getElementById("rating").innerHTML  = rating;
document.getElementById("address").innerHTML  = address;
document.getElementById("phone").innerHTML = phone;
document.getElementById("price").innerHTML = price;

}

