// JavaScript Document
function getAllStateForecasts() {
    $.get('../dist/php/getAllStateForecasts.php',function(data) {
                $("#allstatesforecast").html(data);
    });
}

getAllStateForecasts();
