// JavaScript Document
$(document).ready(function(e){
    google.charts.load('current', {'packages':['corechart']});
    
    //loadcity
    loadCities();
});

function loadCities(){
    
    // Send a GET request to the PHP script to load cities
    $.get('../dist/php/loadCities.php', function(response) {

        // Parse the JSON response into JavaScript object
        var cities = JSON.parse(response);
        
        // Select the select box with ID 'chartcity'
        var selectBox = $('#forecastcity');
        
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

function validateParameter(){
    const city = $("#forecastcity").val();
    const fdate = $("#chartstartdate").val();
    const fdate2 = $("#chartenddate").val();
    const parameter = $("#chartparameters").val();
    const responceDiv = $("#meteolytics_chart_analysis");
    const chartType = getSelectedRadioButton();
    if(city !== "" && fdate !== ""  && fdate2 !== "" && parameter !== ""){
        responceDiv.html("").css("color","#000");
        
        if(parameter==="temperature"){
            validateChart_Temperature(city, fdate, fdate2, parameter, chartType);
        }else if(parameter==="pressure"){
            validateChart_Pressure(city, fdate, fdate2, parameter, chartType);
        }else if(parameter==="wind_speed"){
            validateChart_Wind_Speed(city, fdate, fdate2, parameter, chartType);
        }else if(parameter==="wind_direction"){
            validateChart_Wind_Direction(city, fdate, fdate2, parameter, chartType);
        }else if(parameter==="visibility"){
            validateChart_Visibility(city, fdate, fdate2, parameter, chartType);
        }else{
            responceDiv.html("Selected parameter is not available at the moment").css("color","#F80");    
        }
    }else{
        responceDiv.html("please select all parameters").css("color","#F00");
    }
}
//visibility
function validateChart_Visibility(city, fdate, fdate2, parameter, chartType){
    
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/validateChart_Visibility.php', 'city=' + city + '&fdate=' + fdate + '&fdate2=' + fdate2+ '&parameter=' + parameter, function(response) {
    
        if(response.indexOf("invalid") >= 0 || response.indexOf("Data column(s) for axis #0 cannot be of type string")){
            document.getElementById('meteolytics_chart_analysis').innerHTML = "Invalid Data returned!";
        }
        
             var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Horizontal Visibility Validation Curve',
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0}
                    //1: {targetAxisIndex: 1}
                },
                vAxes: {
                    0: {title: 'Horizontal Visibility (Meters)'} // Y-axis for temperature
                    //1: {title: 'Observed Temperature (Celsius)'}           // Y-axis for humidity
                }
            };

        if(chartType === "ColumnChart"){
            var chart = new google.visualization.ColumnChart(document.getElementById('meteolytics_chart_analysis'));
        }else if(chartType === "LineChart"){
            var chart = new google.visualization.LineChart(document.getElementById('meteolytics_chart_analysis'));
        }else{
            var chart = new google.visualization.AreaChart(document.getElementById('meteolytics_chart_analysis'));
        }
            chart.draw(data, options);        
        });

    validateTable_Visibility(city, fdate, fdate2, parameter, chartType);
}
function validateTable_Visibility(city, fdate, fdate2, parameter, chartType){
    
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/validateTable_Visibility.php', 'city=' + city + '&fdate=' + fdate + '&fdate2=' + fdate2+ '&parameter=' + parameter, function(response) {
        
        if(response.indexOf("invalid") >= 0 || response.indexOf("Data column(s) for axis #0 cannot be of type string")){
            document.getElementById('meteolytics_table_analysis').innerHTML = "Invalid Data returned!";
        }
        $("#meteolytics_table_analysis").html(response);
    });
}


//temperature
function validateChart_Temperature(city, fdate, fdate2, parameter, chartType){
    
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/validateChart_Temperature.php', 'city=' + city + '&fdate=' + fdate + '&fdate2=' + fdate2+ '&parameter=' + parameter, function(response) {
        
        if(response.indexOf("invalid") >= 0 || response.indexOf("Data column(s) for axis #0 cannot be of type string")){
            document.getElementById('meteolytics_chart_analysis').innerHTML = "Invalid Data returned!";
        }
        
             var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Temperature Validation Curve',
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0}
                    //1: {targetAxisIndex: 1}
                },
                vAxes: {
                    0: {title: 'Forecast Temperature (Celsius)'} // Y-axis for temperature
                    //1: {title: 'Observed Temperature (Celsius)'}           // Y-axis for humidity
                }
            };

        if(chartType === "ColumnChart"){
            var chart = new google.visualization.ColumnChart(document.getElementById('meteolytics_chart_analysis'));
        }else if(chartType === "LineChart"){
            var chart = new google.visualization.LineChart(document.getElementById('meteolytics_chart_analysis'));
        }else{
            var chart = new google.visualization.AreaChart(document.getElementById('meteolytics_chart_analysis'));
        }
            chart.draw(data, options);        
        });

    validateTable_Temperature(city, fdate, fdate2, parameter, chartType);
}
//table
function validateTable_Temperature(city, fdate, fdate2, parameter, chartType){
    
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/validateTable_Temperature.php', 'city=' + city + '&fdate=' + fdate + '&fdate2=' + fdate2+ '&parameter=' + parameter, function(response) {
        
        if(response.indexOf("invalid") >= 0 || response.indexOf("Data column(s) for axis #0 cannot be of type string")){
            document.getElementById('meteolytics_table_analysis').innerHTML = "Invalid Data returned!";
        }
        $("#meteolytics_table_analysis").html(response);
    });
}

/*Pressure*/
function validateChart_Pressure(city, fdate, fdate2, parameter, chartType){
    
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/validateChart_Pressure.php', 'city=' + city + '&fdate=' + fdate + '&fdate2=' + fdate2+ '&parameter=' + parameter, function(response) {
        
        if(response.indexOf("invalid") >= 0 || response.indexOf("Data column(s) for axis #0 cannot be of type string")){
            document.getElementById('meteolytics_chart_analysis').innerHTML = "Invalid Data returned!";
        }
        
             var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Pressure Validation Curve',
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0}
                    //1: {targetAxisIndex: 1}
                },
                vAxes: {
                    0: {title: 'Atmospheric Pressure (hPA)'} // Y-axis for temperature
                    //1: {title: 'Observed Temperature (Celsius)'}           // Y-axis for humidity
                }
            };

        if(chartType === "ColumnChart"){
            var chart = new google.visualization.ColumnChart(document.getElementById('meteolytics_chart_analysis'));
        }else if(chartType === "LineChart"){
            var chart = new google.visualization.LineChart(document.getElementById('meteolytics_chart_analysis'));
        }else{
            var chart = new google.visualization.AreaChart(document.getElementById('meteolytics_chart_analysis'));
        }
            chart.draw(data, options);        
        });

    validateTable_Pressure(city, fdate, fdate2, parameter, chartType);
}
//table
function validateTable_Pressure(city, fdate, fdate2, parameter, chartType){
    
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/validateTable_Pressure.php', 'city=' + city + '&fdate=' + fdate + '&fdate2=' + fdate2+ '&parameter=' + parameter, function(response) {
        
        if(response.indexOf("invalid") >= 0 || response.indexOf("Data column(s) for axis #0 cannot be of type string")){
            document.getElementById('meteolytics_table_analysis').innerHTML = "Invalid Data returned!";
        }
        $("#meteolytics_table_analysis").html(response);
    });
}

/*Wind_Speed*/
function validateChart_Wind_Speed(city, fdate, fdate2, parameter, chartType){
    
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/validateChart_Wind_Speed.php', 'city=' + city + '&fdate=' + fdate + '&fdate2=' + fdate2+ '&parameter=' + parameter, function(response) {
        
        if(response.indexOf("invalid") >= 0 || response.indexOf("Data column(s) for axis #0 cannot be of type string")){
            document.getElementById('meteolytics_chart_analysis').innerHTML = "Invalid Data returned!";
        }
        
             var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Wind Speed Validation Curve',
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0}
                    //1: {targetAxisIndex: 1}
                },
                vAxes: {
                    0: {title: 'Wind Speed (Knots)'} // Y-axis for temperature
                    //1: {title: 'Observed Temperature (Celsius)'}           // Y-axis for humidity
                }
            };

        if(chartType === "ColumnChart"){
            var chart = new google.visualization.ColumnChart(document.getElementById('meteolytics_chart_analysis'));
        }else if(chartType === "LineChart"){
            var chart = new google.visualization.LineChart(document.getElementById('meteolytics_chart_analysis'));
        }else{
            var chart = new google.visualization.AreaChart(document.getElementById('meteolytics_chart_analysis'));
        }
            chart.draw(data, options);        
        });

    validateTable_Wind_Speed(city, fdate, fdate2, parameter, chartType);
}
//table
function validateTable_Wind_Speed(city, fdate, fdate2, parameter, chartType){
    
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/validateTable_Wind_Speed.php', 'city=' + city + '&fdate=' + fdate + '&fdate2=' + fdate2+ '&parameter=' + parameter, function(response) {
        
        if(response.indexOf("invalid") >= 0 || response.indexOf("Data column(s) for axis #0 cannot be of type string")){
            document.getElementById('meteolytics_table_analysis').innerHTML = "Invalid Data returned!";
        }
        $("#meteolytics_table_analysis").html(response);
    });
}

/*Wind_Direction*/
function validateChart_Wind_Direction(city, fdate, fdate2, parameter, chartType){
    
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/validateChart_Wind_Direction.php', 'city=' + city + '&fdate=' + fdate + '&fdate2=' + fdate2+ '&parameter=' + parameter, function(response) {
        
        if(response.indexOf("invalid") >= 0 || response.indexOf("Data column(s) for axis #0 cannot be of type string")){
            document.getElementById('meteolytics_chart_analysis').innerHTML = "Invalid Data returned!";
        }
        
             var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Wind Speed Validation Curve',
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0}
                    //1: {targetAxisIndex: 1}
                },
                vAxes: {
                    0: {title: 'Wind Speed (Knots)'} // Y-axis for temperature
                    //1: {title: 'Observed Temperature (Celsius)'}           // Y-axis for humidity
                }
            };

        if(chartType === "ColumnChart"){
            var chart = new google.visualization.ColumnChart(document.getElementById('meteolytics_chart_analysis'));
        }else if(chartType === "LineChart"){
            var chart = new google.visualization.LineChart(document.getElementById('meteolytics_chart_analysis'));
        }else{
            var chart = new google.visualization.AreaChart(document.getElementById('meteolytics_chart_analysis'));
        }
            chart.draw(data, options);        
        });

    validateTable_Wind_Direction(city, fdate, fdate2, parameter, chartType);
}
//table
function validateTable_Wind_Direction(city, fdate, fdate2, parameter, chartType){
    
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/validateTable_Wind_Direction.php', 'city=' + city + '&fdate=' + fdate + '&fdate2=' + fdate2+ '&parameter=' + parameter, function(response) {
        
        if(response.indexOf("invalid") >= 0 || response.indexOf("Data column(s) for axis #0 cannot be of type string")){
            document.getElementById('meteolytics_table_analysis').innerHTML = "Invalid Data returned!";
        }
        $("#meteolytics_table_analysis").html(response);
    });
}

function getSelectedRadioButton() {
    var radios = document.getElementsByName('charttype');

    for (var i = 0; i < radios.length; i++) {
        if (radios[i].checked) {
            return radios[i].value;
        }
    }

    // If no radio button is checked, return null or handle the case accordingly
    return null;
}
