// JavaScript Document
$(document).ready(function(e){
    //google.charts.load('current', {'packages':['corechart']});
    $(".customdiv").hide("fast");
    
    //set current dates
    $("#tafdate").val(getCurrentDate());
    
    
    //loadcity
     loadStations();
});


function loadStations(){
    // Send a GET request to the PHP script to load cities
    $.get('../dist/php/loadStations.php', function(response) {

        // Parse the JSON response into JavaScript object
        var cities = JSON.parse(response);
        
        // Select the select box with ID 'chartcity'
        var selectBox = $('#chartcity');
        
        // Clear the select box
        selectBox.empty();
        selectBox.append($('<option>', {
                value: '',
                text: 'select station'
            }));
        selectBox.append($('<option>', {
                value: '0',
                text: 'All'
            }));
        // Iterate through the cities and add options to the select box
        $.each(cities, function(index, city) {
            selectBox.append($('<option>', {
                value: city.tblid,
                text: city.names
            }));
        });
    });
    setValidity();
}

function setValidity() {
    // Get current date and hour in UTC
    let curDate = new Date().getUTCDate() < 10 ? "0" + new Date().getUTCDate() : new Date().getUTCDate();
    let curHour = new Date().getUTCHours() < 10 ? "0" + new Date().getUTCHours() : new Date().getUTCHours();
    
    // Concatenate curDate with the items of the array
    //let availableOptions = [curDate + "0006", curDate + "0612", curDate + "1218", curDate + "1824"];
    let availableOptions = ["0006", "0612", "1218", "1824"];
    
    // Select the select box with ID 'chartcity'
    var selectBox = $('#tafvalidity');
    
    // Clear the select box
    selectBox.empty();
    
    // Add a default option
    selectBox.append($('<option>', {
        value: '',
        text: 'Select Validity'
    }));
    
    // Iterate through the available options and add them to the select box
    $.each(availableOptions, function(index, option) {
        selectBox.append($('<option>', {
            value: option,
            text: option
        }));
    });
    
    // Set selected index based on current UTC hour
    if (new Date().getUTCHours() >= 0 && new Date().getUTCHours() < 6) {
        selectBox.val(availableOptions[1]);
    } else if (new Date().getUTCHours() >= 6 && new Date().getUTCHours() < 12) {
        selectBox.val(availableOptions[2]);
    } else if (new Date().getUTCHours() >= 12 && new Date().getUTCHours() < 18) {
        selectBox.val(availableOptions[3]);
    } else {
        selectBox.val(availableOptions[0]);
    }
}


function selectedMetarType(selected){
    $(".customdiv").hide("fast");
    if(selected ==0){
        $(".customdiv").show("fast");
    }    
}


    $("#myForm").submit(function(e){
        e.preventDefault(); // Prevent form submission
        
        // Serialize form data
        var formData = $(this).serialize();
        
        // Send AJAX request
        $.ajax({
            url: "../dist/php/taf_form.php", // Path to your PHP script handling form submission
            type: "POST",
            data: formData,
            success: function(response){
                $("#printable").html(response); // Show success message or handle response
            },
            error: function(xhr, status, error){
                $("#printable").html(xhr.responseText); // Log error message
            }
        });
    });

