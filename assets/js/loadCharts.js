// JavaScript Document
//$(document).ready(function(e){
    google.charts.load('current', {'packages':['corechart']});
    //google.charts.setOnLoadCallback(drawTemperatureChart);

    //first call
    drawTemperatureChart();

    // Update the drawTemperatureChart() function every second
    setInterval(function() {    
        drawTemperatureChart();
    }, 30000);

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

