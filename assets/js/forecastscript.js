// JavaScript Document
$(document).ready(function(e){
    
    
});

function getWeatherForecast(latitude, longitude) {    
    const apiKey = '958c7f0d0533d1df3f591018df5bef88';
    const forecastDiv = document.getElementById("forecastResult");
    const apiUrl = `https://api.openweathermap.org/data/2.5/forecast?lat=${latitude}&lon=${longitude}&appid=${apiKey}`;
//alert(apiUrl);
    // Fetch forecast data from the API
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch forecast data');
                forecastDiv.textContent = 'Failed to fetch forecast data';
            }
            return response.json();
        })
        .then(data => {
            // Extract and process the forecast data
            const forecasts = data.list.slice(0, 4); // Slice to get only the first 3 forecasts
            
            // Clear previous forecast data
            forecastDiv.innerHTML = '';

            // Loop through each of the first 3 forecasts and append them to the forecastDiv
            forecasts.forEach(forecast => {
                // Extract relevant information from each forecast object
                const forecastDate = monthDayformatFromUnixTimestamp(forecast.dt);
                const forecastTime = unixTimestampToTimeString(forecast.dt);
                      //new Date(forecast.dt * 1000); // Convert timestamp to Date object
                const temperature = forecast.main.temp;
                const temperatureInCelcius = kelvinToCelsius(temperature).toFixed(1);
                const weatherMain = forecast.weather[0].main;
                const weatherDescription = forecast.weather[0].description;
                const weatherIcon = setWeatherIcon(forecast.weather[0].icon);
                const windDirection = formatWindDirection(forecast.wind.deg);
                const windSpeedRaw = Math.round(metersPerSecondToKnots(forecast.wind.speed));
                const windSpeed = windSpeedRaw < 10? '0'+windSpeedRaw : windSpeedRaw;

                // Create forecast element
                const forecastElement = document.createElement('div');
                forecastElement.classList.add('col-6', 'col-md-4', 'col-lg-3');
                forecastElement.innerHTML = `
                    <div class='row'>
                        <div class='col-12 text-primary'>${forecastDate} ${forecastTime}</div>
                    </div>
                    <div class='row text-danger'>
                        <div class='col-2'><i class='mdi mdi-temperature-celsius'></i></div>
                        <div class='col-10'>${temperatureInCelcius} <sup>o</sup>C</div>
                    </div>
                    <div class='row text-info'>
                        <div class='col-2'><i class='mdi mdi-weather-windy'></i></div>
                        <div class='col-10'>${windDirection} ${windSpeed} KT</div>
                    </div>
                    <div class='row text-warning'>
                        <div class='col-2'><i class='mdi mdi-weather-cloudy'></i></div>
                        <div class='col-10'>${weatherMain} ${weatherIcon}</div>
                    </div>`;
                
                // Append forecast element to forecastDiv
                forecastDiv.appendChild(forecastElement);
            });
        })
        .catch(error => {
            console.error('Error fetching weather forecast:', error);
        });
}



// Example usage:

const latitude = 12.0001; // Latitude of the location
const longitude = 8.5167; // Longitude of the location
//getWeatherForecast(latitude, longitude);
