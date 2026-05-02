// JavaScript Document
//extract numeric value from a string
function extractNumericValue(inputString) {
    // Use regular expression to match numeric value
    const numericValue = inputString.match(/\d+/);
    
    // Check if numericValue is not null
    if (numericValue) {
        // Return the numeric value as a string
        return numericValue[0];
    } else {
        // Return null if no numeric value found
        return null;
    }
}

//function to compute dewpoint temperature from temperature and RH
function calculateDewPoint(temperature, relativeHumidity) {
    /*        
        dp = (b × α(T,RH)) / (a - α(T,RH))
        dp – Dew point (in degrees Celsius);
        T – Temperature (in degrees Celsius);
        RH - Relative humidity of the air (in percent);
        a and b are the Magnus coefficients. As recommended by Alduchov and Eskridge, the value of these are: a = 17.625 and b = 243.04 °C; and 
        α(T,RH) = ln(RH/100) + aT/(b+T)
        */
    // Constants for the Magnus-Tetens formula
    const a = 17.625;
    const b = 243.04;

    // Calculate gamma (natural logarithm of relative humidity)
    const gamma = Math.log(relativeHumidity / 100) + ( a * temperature/(b + temperature));

    // Calculate dew point temperature
    const dewPoint = (b * gamma) / (a - gamma);

    return dewPoint.toFixed(2); // Return dew point temperature rounded to 2 decimal places
}

//compute hours and minutes betweem 2 unix timestamps
function calculateDayLightHours(startTime, endTime) {
    
    // Convert Unix timestamps to Date objects
    const startDate = new Date(startTime * 1000);
    const endDate = new Date(endTime * 1000);

    // Calculate the difference in milliseconds
    const difference = endDate - startDate;

    // Convert milliseconds to hours and minutes
    const hours = Math.floor(difference / (1000 * 60 * 60));
    const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
    
    // Return the result as an object
    return {
        hours: hours,
        minutes: minutes
    };
}

//convert unixstamp to local time
function unixTimestampToTimeString(unixTimestamp) {
    // Convert Unix timestamp to milliseconds
    const milliseconds = unixTimestamp * 1000;
    
    // Create a new Date object with the milliseconds
    const date = new Date(milliseconds);
    
    // Extract hours and minutes
    let hours = date.getHours();
    let minutes = date.getMinutes();
    
    // Determine AM/PM
    const ampm = hours >= 12 ? 'PM' : 'AM';
    
    // Convert hours from 24-hour to 12-hour format
    hours = hours % 12;
    hours = hours ? hours : 12; // Handle midnight (0 hours)
    
    // Add leading zero to minutes if necessary
    minutes = minutes < 10 ? '0' + minutes : minutes;
    
    // Format the time string
    const timeString = hours + ':' + minutes + ampm;
    
    return timeString;
}

// Create a new Date object with the provided Unix timestamp
function monthDayformatFromUnixTimestamp(unixTimestamp) {
    
    const date = new Date(unixTimestamp * 1000); // Convert Unix timestamp to milliseconds

    // Array of month names
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    // Get the month and day from the date object
    const month = monthNames[date.getMonth()];
    const day = date.getDate();

    // Construct the formatted date string
    const formattedDate = `${month} ${day}`;

    return formattedDate;
}

/* Example usage:
const unixTimestamp = 1661871600; // Example Unix timestamp
const formattedDate = formatDateFromUnixTimestamp(unixTimestamp);
console.log("Formatted Date:", formattedDate);
*/

// Function to format time
function formatTime(date) {
    let hours = date.getHours();
    const minutes = date.getMinutes();
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // Handle midnight (0 hours)
    const formattedTime = ('0' + hours).slice(-2) + ':' + ('0' + minutes).slice(-2) + ampm;
    return formattedTime;
}

//covert atmospheric pressure to mean sea level pressure
function convertToSeaLevelPressure(pressure, altitude) {
    // Standard atmospheric pressure at sea level in millibars (mb)
    const standardPressureAtSeaLevel = 1013.25; // in mb
    
    // Standard temperature lapse rate (in degrees Celsius per meter)
    const standardTemperatureLapseRate = 0.0065; 

    // Estimate temperature at given altitude assuming standard lapse rate
    const temperatureAtAltitude = 15 - (standardTemperatureLapseRate * altitude / 1000);

    // Calculate sea-level pressure using barometric formula
    const seaLevelPressure = pressure * Math.pow((1 - (standardTemperatureLapseRate * altitude / (temperatureAtAltitude + 273.15))), 5.255);

    return seaLevelPressure;
}

//atm pressure to station level pressure
function convertToGroundLevelPressure(pressure, altitude) {
    // Standard atmospheric pressure at sea level in millibars (mb)
    const standardPressureAtSeaLevel = 1013.25; // in mb
    
    // Standard temperature lapse rate (in degrees Celsius per meter)
    const standardTemperatureLapseRate = 0.0065; 

    // Estimate temperature at given altitude assuming standard lapse rate
    const temperatureAtAltitude = 15 - (standardTemperatureLapseRate * altitude / 1000);

    // Calculate sea-level pressure using barometric formula
    const seaLevelPressure = pressure * Math.pow((1 - (standardTemperatureLapseRate * altitude / (temperatureAtAltitude + 273.15))), 5.255);

    // Adjust sea-level pressure to ground level using barometric formula
    const groundLevelPressure = seaLevelPressure / Math.pow((1 - (standardTemperatureLapseRate * altitude / (temperatureAtAltitude + 273.15))), 5.255);

    return groundLevelPressure;
}

//convert given pressure to inches
function convertToInches(pressure) {
    const conversionFactor = 0.02953;
    const pressureInInches = pressure * conversionFactor;
    return pressureInInches;
}

//function to trim a string of extra spaces
function trimString(msg){
    return msg.replace(/\s+/g, ' ').trim();
}

//get length of a given
    function getArrayLength(arr) {
        return arr.length;
    }
    
    //check for the occurences of change groups in a message
    function findAllOccurrences(string) {
        const wordsToFind = ["BECMG", "TEMPO", "PROB", "FM"];
        const occurrences = [];

        wordsToFind.forEach(word => {
            let index = string.indexOf(word);
            while (index !== -1) {
                occurrences.push({ word: word, index: index });
                index = string.indexOf(word, index + 1);
            }
        });
        // Sort occurrences array by index
        occurrences.sort((a, b) => a.index - b.index);

        return occurrences;
    }

    
    //get the index of a substring in a string
    function findSubstringIndex(mainString, substring) {
        return mainString.indexOf(substring);
    }
    
    //return the first word in a string
    function firstWord(text) {
        // Split the text into an array of words using whitespace as the delimiter
        const wordsArray = text.split(/\s+/);
        // Return the first word (element) of the array
        return wordsArray[0];
    }
    
    //function to check for the index of the first occurrence of any cloud
    function indexOfFirstCloudGroup(tafMessage) {
        const cloudGroups = ["FEW", "SCT", "BKN", "OVC", "NSC", "VV"];
        let minIndex = Infinity;

        for (let i = 0; i < cloudGroups.length; i++) {
            const index = tafMessage.indexOf(cloudGroups[i]);
            if (index !== -1 && index < minIndex) {
                minIndex = index;
            }
        }

        if (minIndex === Infinity) {
            return -1; // None of the cloud groups found
        }

        return minIndex;
    }


    //function to check for cloud in a given message
    function isCLoudGroup(tafMessage) {
        const cloudGroups = ["FEW", "SCT", "BKN", "OVC", "NSC", "VV"];
        for (let i = 0; i < cloudGroups.length; i++) {
            if (tafMessage.includes(cloudGroups[i])) {
                return true;
            }
        }
        return false;
    }
    
    //count number of words in a string
    function countWords(string) {
    // Split the text into an array of words using whitespace as the delimiter
        const wordsArray = string.split(/\s+/);
    // Return the number of words in the array
        return wordsArray.length;
    }
    
    //function to check for change groups
    function isChangeGroup(tafMessage) {
        const changeGroups = ["BECMG", "TEMPO", "PROB", "FM"];
        for (let i = 0; i < changeGroups.length; i++) {
            if (tafMessage.includes(changeGroups[i])) {
                return true;
            }
        }
        return false;
    }
    
    //function to check for the index of the first occurrence of any of the change groups
    function indexOfFirstChangeGroup(tafMessage) {
        const changeGroups = ["BECMG", "TEMPO", "PROB", "FM"];
        let minIndex = Infinity;

        for (let i = 0; i < changeGroups.length; i++) {
            const index = tafMessage.indexOf(changeGroups[i]);
            if (index !== -1 && index < minIndex) {
                minIndex = index;
            }
        }

        if (minIndex === Infinity) {
            return -1; // None of the change groups found
        }

        return minIndex;
    }

// Formula to convert Kelvin to Celsius: Celsius = Kelvin - 273.15
function kelvinToCelsius(kelvin) {    
    const celsius = kelvin - 273.15;
    return celsius;
}

// Convert wind direction to a string
function formatWindDirection(windDirection) {
    
    let windDirString = windDirection.toString();

    // If wind direction has less than 3 digits, pad it with leading zeros
    while (windDirString.length < 3) {
        windDirString = "0" + windDirString;
    }

    return windDirString;
}

// Conversion factor from meters per second to knots
function metersPerSecondToKnots(metersPerSecond) {
    
    const conversionFactor = 1.94384;

    // Convert meters per second to knots
    const knots = metersPerSecond * conversionFactor;

    return knots;
}

function setWeatherIcon(iconCode) {
    return "<img src='https://openweathermap.org/img/wn/"+iconCode+".png' alt='Weather' height='35px' width='35px'>";
}


function formatDate(date) {
    const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    const dayOfWeekIndex = date.getDay();
    const dayOfMonth = date.getDate();
    const monthIndex = date.getMonth();

    const formattedDate = daysOfWeek[dayOfWeekIndex] + ', ' + 
                          ('0' + dayOfMonth).slice(-2) + ' ' + 
                          months[monthIndex] + ' ' + 
                          date.getFullYear();

    return formattedDate;
}

//a general function to hide visibility of div by checkbox
function toggleDivVisibility(checkboxid, divid) {
    // Get the checkbox element
    var checkbox = document.getElementById(checkboxid);
    
    // Get the div element to show or hide
    var div = document.getElementById(divid); // 
    
    // Add event listener to the checkbox
    checkbox.addEventListener("change", function() {
        // If the checkbox is checked, show the div; otherwise, hide it
        if (checkbox.checked) {
            div.style.display = "block";
        } else {
            div.style.display = "none";
        }
    });
}

//set current date for date inputs 
function getCurrentDate() {
    // Get the current date
    var currentDate = new Date();

    // Format the date to YYYY-MM-DD (required format for date input)
    var formattedDate = currentDate.toISOString().split('T')[0];

    // Return the formatted date
    return formattedDate;
}

