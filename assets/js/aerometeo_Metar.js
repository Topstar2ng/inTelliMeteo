// JavaScript Document
$(document).ready(function(e){
    //google.charts.load('current', {'packages':['corechart']});
    $(".customdiv").hide("fast");
    
    //set current dates
    $("#startdate").val(getCurrentDate());
    $("#enddate").val(getCurrentDate());
    
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
                text: 'select city'
            }));
        // Iterate through the cities and add options to the select box
        $.each(cities, function(index, city) {
            selectBox.append($('<option>', {
                value: city.tblid,
                text: city.names
            }));
        });
    });    
}

function selectedMetarType(selected){
    $(".customdiv").hide("fast");
    if(selected ==0){
        $(".customdiv").show("fast");
    }    
}


    $("#metarForm").submit(function(e){
        e.preventDefault(); // Prevent form submission
        
        // Serialize form data
        var formData = $(this).serialize();
        
        // Send AJAX request
        $.ajax({
            url: "../dist/php/submit_metar_form.php", // Path to your PHP script handling form submission
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

