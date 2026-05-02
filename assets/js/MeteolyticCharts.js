// JavaScript Document
// Get the current date
    var currentDate = new Date();

    // Format the current date as YYYY-MM-DD (required format for input type="date")
    var year = currentDate.getFullYear();
    var month = ("0" + (currentDate.getMonth() + 1)).slice(-2);
    var day = ("0" + currentDate.getDate()).slice(-2);
    var formattedDate = year + "-" + month + "-" + day;

    // Set the value of the input element to the current date
    document.getElementById("chartstartdate").value = formattedDate;
    document.getElementById("chartenddate").value = formattedDate;

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

function loadCharts(){
    const city = $("#chartcity").val();
    const start = $("#chartstartdate").val();
    const end = $("#chartenddate").val();
    const period = $("#chartperiod").val();
    
    if(city !== "" && start !== "" && end !== "" && period !== ""){
        // All required values are provided, perform the desired actions
        
        loadTemperatureChartsData(city, start, end, period);//temperature
        loadPressureChartsData(city, start, end, period);//pressure
        loadDewPointChartsData(city, start, end, period);//dewpoint
        loadHumidityChartsData(city, start, end, period);//humidity
        loadWindSpeedChartsData(city, start, end, period);//speed
        loadVisibilityChartsData(city, start, end, period);//visibility
    } else {
        // If any required value is missing, display an error message or take appropriate action
        alert("Please fill in all required fields.");
    }
}

function loadVisibilityChartsData(city, start, end, period){
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/meteolyticVisibilityCharts.php', 'city=' + city + '&start=' + start+ '&end=' + end+ '&period=' + period, function(response) {
        //document.getElementById('meteolytics_visibility_chart').innerHTML = response;
        //return;
            var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Visibility Graph between '+start+' and '+end,
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0},
                    
                },
                vAxes: {
                    0: {title: 'Visibility (Meters)'}, // Y-axis for temperature
                    
                }
            };

            var chart = new google.visualization.AreaChart(document.getElementById('meteolytics_visibility_chart'));

            chart.draw(data, options);        
        });
}


function loadWindSpeedChartsData(city, start, end, period){
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/meteolyticWindSpeedCharts.php', 'city=' + city + '&start=' + start+ '&end=' + end+ '&period=' + period, function(response) {
        //document.getElementById('meteolytics_temperature_chart').innerHTML = response;
        //return;
            var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Wind Speed Graph between '+start+' and '+end,
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0},
                    
                },
                vAxes: {
                    0: {title: 'Wind Speed (Knots)'}, // Y-axis for temperature
                    
                }
            };

            var chart = new google.visualization.SteppedAreaChart(document.getElementById('meteolytics_windspeed_chart'));

            chart.draw(data, options);        
        });
}

function loadHumidityChartsData(city, start, end, period){
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/meteolyticHumidityCharts.php', 'city=' + city + '&start=' + start+ '&end=' + end+ '&period=' + period, function(response) {
        //document.getElementById('meteolytics_humidity_chart').innerHTML = response;
        //return;
            var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Relative Humidity Graph between '+start+' and '+end,
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0},
                    
                },
                vAxes: {
                    0: {title: 'Humidity (%)'}, // Y-axis for temperature
                    
                }
            };

            var chart = new google.visualization.AreaChart(document.getElementById('meteolytics_humidity_chart'));

            chart.draw(data, options);        
        });
}


function loadDewPointChartsData(city, start, end, period){
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/meteolyticDewpointCharts.php', 'city=' + city + '&start=' + start+ '&end=' + end+ '&period=' + period, function(response) {
        //document.getElementById('meteolytics_temperature_chart').innerHTML = response;
        //return;
            var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Dewpoint Temperature Graph between '+start+' and '+end,
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0},
                    
                },
                vAxes: {
                    0: {title: 'DewPoint Temperature (Celsius)'}, // Y-axis for temperature
                    
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('meteolytics_dewpoint_chart'));

            chart.draw(data, options);        
        });
}

function loadTemperatureChartsData(city, start, end, period){
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/meteolyticTemperatureCharts.php', 'city=' + city + '&start=' + start+ '&end=' + end+ '&period=' + period, function(response) {
        //document.getElementById('meteolytics_temperature_chart').innerHTML = response;
        //return;
            var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Temperature Graph between '+start+' and '+end,
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0},
                    
                },
                vAxes: {
                    0: {title: 'Temperature (Celsius)'}, // Y-axis for temperature
                    
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('meteolytics_temperature_chart'));

            chart.draw(data, options);        
        });
}


function loadPressureChartsData(city, start, end, period){
    
    // Perform actions to load charts data using the provided values
    $.get('../dist/php/meteolyticPressureCharts.php', 'city=' + city + '&start=' + start+ '&end=' + end+ '&period=' + period, function(response) {
        //alert(response);
        //document.getElementById('meteolytics_pressure_chart').innerHTML = response;
        //return;
            var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Pressure Graph between '+start+' and '+end,
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,       
                series: {
                    0: {targetAxisIndex: 0},
                    
                },
                vAxes: {
                    0: {title: 'Pressure (hPA)'}, // Y-axis for temperature
                    
                }
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('meteolytics_pressure_chart'));

            chart.draw(data, options);        
        });
}
    
function drawTemperatureChart() {
    const stateid = $("#state_0").text().trim || "kano";
    const chartType = $("#analysistype").val();

    if(chartType === "humidity"){

        $.get('../dist/php/temperaturechart.php', 'stateid=' + stateid, function(response) {
            // Parse the JSON data using JSON.parse() function
            var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Temperature/Humidity Curve',
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0},
                    1: {targetAxisIndex: 1}
                },
                vAxes: {
                    0: {title: 'Temperature (Celsius)'}, // Y-axis for temperature
                    1: {title: 'Humidity (%)'}           // Y-axis for humidity
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('temperature_chart'));

            chart.draw(data, options);
        });
    }else if(chartType === "dewpoint"){

            $.get('../dist/php/dewpointchart.php', 'stateid=' + stateid, function(response) {
            //document.getElementById('temperature_chart').innerHTML = response;
            //return;
            // Parse the JSON data using JSON.parse() function
            var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Temperature/DewPoint Curve',
                curveType: 'function',
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                  // Gives each series an axis name that matches the Y-axis below.
                  0: {axis: 'Temps'},
                  1: {axis: 'DewPoint'}
                },
                axes: {
                  // Adds labels to each axis; they don't have to match the axis names.
                  y: {
                    Temps: {label: 'Temperature (Celsius)'},
                    Humidity: {label: 'DewPoint (Celsius)'}
                  }
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('temperature_chart'));

            chart.draw(data, options);
        });
    }else if(chartType === "cloudcover"){

        $.get('../dist/php/cloudcoverchart.php', 'stateid=' + stateid, function(response) {
            // Parse the JSON data using JSON.parse() function
            var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Dewpoint/Cloud Cover Curve',
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0},
                    1: {targetAxisIndex: 1}
                },
                vAxes: {
                    0: {title: 'DewPoint (Celsius)'}, // Y-axis for temperature
                    1: {title: 'Cloud Cover (%)'}           // Y-axis for humidity
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('temperature_chart'));

            chart.draw(data, options);
        });
    }else if(chartType === "pressure"){
        $.get('../dist/php/pressurechart.php', 'stateid=' + stateid, function(response) {
            // Parse the JSON data using JSON.parse() function
            var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);
        

        var options = {
          title : 'Temperature and Pressure Hourly Chart',
          hAxis: {title: 'Date'},
          seriesType: 'bars',
          series: {0: {type: 'line'}},
          vAxes: {
            0: {title: 'Temperature (Celcius)'}, // Y-axis for Temperature
            1: {title: 'Pressure (hPA)'} // Y-axis for Pressure
          },
          series: {
            0: {targetAxisIndex: 0}, // Assign the first series to the first Y-axis
            1: {targetAxisIndex: 1} // Assign the second series to the second Y-axis
          }
        };

        var chart = new google.visualization.ComboChart(document.getElementById('temperature_chart'));
        chart.draw(data, options);
        
        });

      }else if(chartType === "wind"){    

        $.get('../dist/php/windchart.php', 'stateid=' + stateid, function(response) {
            // Parse the JSON data using JSON.parse() function
            var jsonData = JSON.parse(response);

            // Convert JSON data to Google Charts DataTable
            var data = google.visualization.arrayToDataTable(jsonData);

            var options = {
                title: 'Temperature/Wind Speed Curve',
                legend: { position: 'bottom' },
                width: 1200,
                height: 600,
                series: {
                    0: {targetAxisIndex: 0},
                    1: {targetAxisIndex: 1}
                },
                vAxes: {
                    0: {title: 'Temperature (Celsius)'}, // Y-axis for temperature
                    1: {title: 'Wind Speed (knots)'}           // Y-axis for humidity
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('temperature_chart'));

            chart.draw(data, options);
        });
       
        }else{
            var data = google.visualization.arrayToDataTable([
          ['Month', 'Bolivia', 'Ecuador', 'Madagascar', 'Papua New Guinea', 'Rwanda', 'Average'],
          ['2004/05',  165,      938,         522,             998,           450,      614.6],
          ['2005/06',  135,      1120,        599,             1268,          288,      682],
          ['2006/07',  157,      1167,        587,             807,           397,      623],
          ['2007/08',  139,      1110,        615,             968,           215,      609.4],
          ['2008/09',  136,      691,         629,             1026,          366,      569.6]
        ]);

        var options = {
          title : 'Monthly Coffee Production by Country',
          vAxis: {title: 'Cups'},
          hAxis: {title: 'Month'},
          seriesType: 'bars',
          series: {0: {type: 'line'}}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('temperature_chart'));
        chart.draw(data, options);
      }
        
    
}

