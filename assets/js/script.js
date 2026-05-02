// JavaScript Document
$(document).ready(function(e){
    // Initial update
    updateDateTime();
    /*get current location
        getCurrentLocation();
    */
    // Set interval to update every minute (60,000 milliseconds)
    setInterval(updateDateTime, 1000);
    
    setInterval(loadInd, 30000);//every 30secs
    
    //wind guage
      google.charts.load('current', {'packages':['gauge']});
      
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {          
        var data = google.visualization.arrayToDataTable([
          ['Label', 'Value'],
          ['Knots', 0]
        ]);

        var options = {
          width: 300, height: 100,
          redFrom: 22, redTo: 100,
          yellowFrom:16, yellowTo: 22,
          minorTicks: 5
        };

        var chart = new google.visualization.Gauge(document.getElementById('chart_div'));

        chart.draw(data, options);

        setInterval(function() {
            //read speed from input
          const kt = extractNumericValue($("#knots_0").html());
          data.setValue(0, 1, Math.round(kt));
          chart.draw(data, options);
        }, 1000);
      }
    
});


function loadInd(){
    if($("#allstatesforecast").innerHTML !=="") {
        for (let i = 0; i <= 36; i++) {
            const statename = $("#state_" + i).text().trim(); // Added 'let' before 'i' and used '.trim()' after '.text()'
            searchWeather(statename, i);
        }
        
       }
    
}

function searchWeather(state, ind) {
    //ind = ind;
    const searchInput = state//$('#searchInput').val() || "Kano";
    const apiKey = '958c7f0d0533d1df3f591018df5bef88'; // Replace with your API key
    const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${searchInput},NG&appid=${apiKey}&units=metric`;
    
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            displayWeather(data, ind);
        })
        .catch(error => console.log('Error fetching weather data:', error));
}

function displayWeather(data, ind) {    
    if (data.cod === '404') {
        weatherResult.textContent = 'City not found. Please try again.';
    } else {
        weatherResult.textContent = '';
        const obstime = data.dt;
        const cityName = data.name;
        const temperature = Math.round(data.main.temp);
        const feels_like = Math.round(data.main.feels_like);
        const weatherId = data.weather[0].id;
        const weatherMainDescription = data.weather[0].main;
        const weatherDescription = data.weather[0].description;
        const weatherIcon = data.weather[0].icon;
        const country = data.sys.country;
        const sunrise = data.sys.sunrise;
        const sunset = data.sys.sunset;
        const high = Math.round(data.main.temp_max);
        const low = Math.round(data.main.temp_min);
        const ff = Math.round(data.wind.speed * 1.94);//in knots
        const ddd = (data.wind.deg < 100)? ('0'+Math.round(data.wind.deg)):Math.round(data.wind.deg);
        const gust = data.wind.gust? (Math.round(data.wind.gust * 1.94)):'--';//in knots
        const humidity = Math.round(data.main.humidity);
        const pressure = Math.round(data.main.pressure);
        const pressureinch = data.main.pressure? (convertToInches(pressure)).toFixed(2):'--';
        const sea_level = data.main.sea_level? Math.round(data.main.sea_level):'--';
        const sea_levelinch = data.main.sea_level? (convertToInches(sea_level)).toFixed(2):'--';
        const ground_level = data.main.grnd_level? Math.round(data.main.grnd_level):'--';
        const ground_levelinch = data.main.grnd_level? (convertToInches(ground_level)).toFixed(2):'--';
        const rain1h = data.rain? Math.round(data.rain):'0.0';
        const cloud = data.clouds.all;
        const lon = data.coord.lon? (data.coord.lon):'--';
        const lat = data.coord.lat? (data.coord.lat):'--';
        const dddtext = 'N';
        const dd = data.wind.deg;
        const visibility = data.visibility;
        const windicon = document.getElementById("windicon_"+ind);
            if (dd >= 0 && dd < 22.5 || dd >= 337.5 && dd <= 360) {
                ddtext = 'N';
                if (windicon) {
                    windicon.src = "../assets/images/windicons/windguage/n.png";
                }
            } else if (dd >= 22.5 && dd < 67.5) {
                ddtext = 'NE';
                if (windicon) {
                    windicon.src = "../assets/images/windicons/windguage/ne.png";
                }
            } else if (dd >= 67.5 && dd < 112.5) {
                ddtext = 'E';
                if (windicon) {
                    windicon.src = "../assets/images/windicons/windguage/e.png";
                }
            } else if (dd >= 112.5 && dd < 157.5) {
                ddtext = 'SE';
                if (windicon) {
                    windicon.src = "../assets/images/windicons/windguage/se.png";
                }
            } else if (dd >= 157.5 && dd < 202.5) {
                ddtext = 'S';
                if (windicon) {
                    windicon.src = "../assets/images/windicons/windguage/s.png";
                }
            } else if (dd >= 202.5 && dd < 247.5) {
                ddtext = 'SW';
                if (windicon) {
                    windicon.src = "../assets/images/windicons/windguage/sw.png";
                }
            } else if (dd >= 247.5 && dd < 292.5) {
                ddtext = 'W';
                if (windicon) {
                    windicon.src = "../assets/images/windicons/windguage/w.png";
                }
            } else if (dd >= 292.5 && dd < 337.5) {
                ddtext = 'NW';
                if (windicon) {
                    windicon.src = "../assets/images/windicons/windguage/nw.png";
                }
            } else {
                ddtext = 'N'; // Default to 'N' if degrees are out of range
                if (windicon) {
                    windicon.src = "../assets/images/windicons/windguage/n.png";
                }
            }
        
        //compute dewpoint
        
        $("#dewpoint_"+ ind).html(calculateDewPoint(temperature, humidity) + ' <sup>o</sup>C');

        
        
        $("#obsTime").html("last updated : "+unixTimestampToTimeString(obstime));
        $("#state_"+ ind).html(cityName);
        $("#weather_"+ ind).html(weatherMainDescription);
        if (weatherMainDescription.indexOf("Clouds") >= 0) {
            $("#weather_"+ ind).html(weatherDescription);         
        }
        
        $("#weatherdesc_"+ ind).html(weatherDescription);
        //cloud type
        if (cloud >= 11 && cloud < 25) {
            $("#cloudtype_"+ ind).html("FEW");
        }else if (cloud >= 25 && cloud < 51) {
            $("#cloudtype_"+ ind).html("SCT");
        }else if (cloud >= 51 && cloud < 85) {
            $("#cloudtype_"+ ind).html("BKN");
        }else if (cloud >= 85 && cloud <= 100) {
            $("#cloudtype_"+ ind).html("OVC");
        }else{
            $("#cloudtype_"+ ind).html("NSC");
        }
        //rain
        if (weatherId >= 200 && weatherId <= 531) {
            $("#raintype_"+ ind).html(weatherMainDescription);
            $("#raindescription_"+ ind).html(weatherDescription);
            $("#rainamount_"+ ind).html(rain1h);
        }else{
            $("#raintype_"+ ind).html("...");
            $("#raindescription_"+ ind).html("...");
            $("#rainamount_"+ ind).html("...");
        }
        
        
        $("#sunrise_"+ ind).html(unixTimestampToTimeString(sunrise)  + " <i class='fa-solid fa-sun text14' style='color:orange;'></i>");
        $("#sunset_"+ ind).html(unixTimestampToTimeString(sunset) + " <i class='fa-solid fa-sun text14' style='color:gray;'></i>");
        const daylight = calculateDayLightHours(sunrise, sunset);
        $("#daylight_" + ind).html("Hours: " + daylight.hours + ", Minutes: " + daylight.minutes);

        $("#temp_"+ ind).html(`${temperature}°C`);
        $("#feeltemp_"+ ind).html(`${feels_like}°C`);
        $("#tempmax_"+ ind).html(`${high}°C`);
        $("#tempmin_"+ ind).html(`${low}°C`);
        $("#humidity_"+ ind).html(`${humidity} %`);
        $("#pressure_"+ ind).html(`${pressure} hPA`);
        $("#knots_"+ ind).html(`${ff} kts`);
        $("#gust_"+ ind).html(`${gust} kts`);
        $("#ddd_"+ ind).html(`${ddd} °N`);
        $("#ddtext_"+ ind).html(`(${ddtext})`);
        $("#cover_"+ ind).html(`${cloud} %`);
        $("#humidity_"+ ind).html(`${humidity} %`);
        $("#rain1h_"+ ind).html(`${rain1h}`);
        $("#pressure_"+ ind).html(`${pressure} hpa`);
        $("#pressureinch_"+ ind).html(`${pressureinch}`);
        $("#sealevel_"+ ind).html(`${sea_level}`);
        $("#sealevelinch_"+ ind).html(`${sea_levelinch}`);
        $("#groundlevel_"+ ind).html(`${ground_level}`);
        $("#groundlevelinch_"+ ind).html(`${ground_levelinch}`);
        $("#visibility_"+ ind).html(`${visibility}`);
        $("#lon_"+ ind).html(`${lon}`);
        $("#lat_"+ ind).html(`${lat}`);
        $("#weathericon_"+ ind).html("<img src='https://openweathermap.org/img/wn/"+weatherIcon+".png' alt='Weather' height='35px' width='35px'>");
                
        $("#weather-description_"+ ind).html(weatherDescription); 
        
        if (weatherMainDescription.indexOf("Clouds") >= 0) {
            $("#weather-main_"+ ind).html(weatherMainDescription).addClass('text-dark');
            $("#weather-main_"+ ind).html("Cloudy");
        } else if (weatherMainDescription.indexOf("Rain") >= 0) {
            $("#weather-main_"+ ind).html(weatherMainDescription).addClass('text-success');
        } else if (weatherMainDescription.indexOf("Clear") >= 0) {
            $("#weather-main_"+ ind).html(weatherMainDescription).addClass('text-orange');
        } else {
            // Handle other weather conditions or set a default icon
            $("#weather-main_"+ ind).html(weatherMainDescription).addClass('text-primary');
        }
        
        if (weatherMainDescription.indexOf("Clouds") >= 0) {
            $("#weather-icon_" + ind).html("<i class='fas fa-cloud text-dark'></i>");
        } else if (weatherMainDescription.indexOf("Rain") >= 0) {
            $("#weather-icon_" + ind).html("<i class='fas fa-cloud-rain text-success'></i>");
        } else if (weatherMainDescription.indexOf("Clear") >= 0) {
            $("#weather-icon_" + ind).html("<i class='fas fa-sun text-orange'></i>");
        } else {
            // Handle other weather conditions or set a default icon
            $("#weather-icon_" + ind).html("<i class='fas fa-question-circle text-primary'></i>");
        }

                //get Forecast
        const latitude = parseFloat($("#lat_0").text().trim());
        const longitude = parseFloat($("#lon_0").text().trim());
        getWeatherForecast(latitude, longitude);       
    }
}

function updateDateTime() {
    const currentDate = new Date();

    // Format date
    const formattedDate = formatDate(currentDate);

    // Format time
    const formattedTime = formatTime(currentDate);

    // Update date in one div
    document.getElementById('currentDate').textContent = formattedDate;

    // Update time in another div
    document.getElementById('currentTime').textContent = formattedTime;
}


function getCurrentLocation(){
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const latitude = 6.595;//position.coords.latitude;
            const longitude = 3.336;//position.coords.longitude;
//11.932, 8.525 11.996, 8.503 6.595, 3.336
            // Fetch city name using reverse geocoding
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                .then(response => response.json())
                .then(data => {
                    const city = data.address.city;
                    console.log(`City: ${city}`);
                    // You can do further processing with the city name here
                })
                .catch(error => console.log('Error fetching city name:', error));
            });
        } else {
            console.log("Geolocation is not supported by this browser.");
        }
}