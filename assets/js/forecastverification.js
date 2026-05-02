// JavaScript Document
$("#forecastverify").submit(function(event){    
    event.preventDefault(); // Prevent default form submission
    
    $("#allstatesforecast").html('<i class="fa-solid fa-arrows-spin fa-spin"></i>');
    const foption = $("#foption").val();
    const ftype = $("#ftype").val();
    const tafMessage = $("#fcontent").val().trim();
    
    if(foption === "decode"){
        //$("#allstatesforecast").html("Available now...");
            $("#allstatesforecast").html(JSON.stringify(decodeTAF(tafMessage)));
       }else{//verify
           $("#allstatesforecast").html("Available soon...");
       }
    // Create a FormData object to collect form data
    //var formData = new FormData(this);

    // Send form data via AJAX
    /*
    $.ajax({
        url: $(this).attr('action'), // URL to submit the form
        type: $(this).attr('method'), // Method (POST or GET)
        data: formData, // Form data including files
        processData: false, // Prevent jQuery from automatically processing the data
        contentType: false, // Prevent jQuery from automatically setting the Content-Type header
        success: function(response) {
            // Handle successful AJAX response
            alert("Form submitted successfully");
        },
        error: function(xhr, status, error) {
            // Handle AJAX errors
            console.error(error);
            alert("An error occurred while submitting the form");
        }
    });
    */
});

function decodeTAF(tafMessage) {    
        //if TAF check TAF type
    if (tafMessage.startsWith("TAF")) {
        const decodedTAF = {};
    //remove all extra spaces
        tafMessage = trimString(tafMessage);
        const isTAFCOR = tafMessage.startsWith("TAF COR"); // TAF Correct
        const isTAFAMD = tafMessage.startsWith("TAF AMD"); // TAF Amend
        const isTAFCNL = tafMessage.startsWith("TAF CNL"); // TAF Cancel
    
        if (!isTAFAMD && !isTAFCOR && !isTAFCNL) {
            // Extracting TAF Type
            decodedTAF.tafType = tafMessage.substring(0, 4);
        }else{
            decodedTAF.tafType = tafMessage.substring(0, 8);
        }
        const tafTypeLenght = decodedTAF.tafType.length;
        
            // Extracting airport name
            decodedTAF.airport = tafMessage.substring(tafTypeLenght, tafTypeLenght+4).trim().length == 4? tafMessage.substring(tafTypeLenght, tafTypeLenght+4) : "invalid ICAO Designator";
                    
            //if error, abort
            if(decodedTAF.airport === "invalid ICAO Designator"){
               return decodedTAF.airport;
               }
        
        const tafTypePlusAirportLenght = tafTypeLenght + 5;//extra 1 space
        
            // Extracting date and time
            decodedTAF.datetime = tafMessage.substring(tafTypePlusAirportLenght, tafMessage.indexOf("Z")+1).trim().length == 7? tafMessage.substring(tafTypePlusAirportLenght, tafMessage.indexOf("Z")+1) : "Invalid Date format";
            
            //if error, abort
            if(decodedTAF.datetime === "Invalid Date format"){
               return decodedTAF.datetime;
               }
        
            //extract date and time seperraty            
            decodedTAF.date = decodedTAF.datetime.substring(0, 2);
            decodedTAF.time = decodedTAF.datetime.substring(2, 6);
            decodedTAF.timeZone = decodedTAF.datetime.substring(6, 7);
                    
            const datetimeLenght = tafTypePlusAirportLenght + decodedTAF.datetime.length;
            // Extracting forecast period
            decodedTAF.forecastPeriod = tafMessage.substring(datetimeLenght+1, datetimeLenght+10);
            decodedTAF.startForecastPeriod = decodedTAF.forecastPeriod.substring(0, 4);
            decodedTAF.endForecastPeriod = decodedTAF.forecastPeriod.substring(5, 10);
            decodedTAF.startDate = parseInt(decodedTAF.startForecastPeriod.substring(0,2));
            decodedTAF.endDate = parseInt(decodedTAF.endForecastPeriod.substring(0,2));
            decodedTAF.startTime = parseInt(decodedTAF.startForecastPeriod.substring(2,4));
            decodedTAF.endTime = parseInt(decodedTAF.endForecastPeriod.substring(2,4));
            decodedTAF.dateDiff = Math.abs(decodedTAF.startDate - decodedTAF.endDate);//must be 1
            decodedTAF.timeDiff = Math.abs(decodedTAF.startTime - decodedTAF.endTime); //must be 6
            decodedTAF.validHours = 24 + decodedTAF.timeDiff;
        
            //verify the date and validity correctness
            if(decodedTAF.dateDiff != 1){
                   return "Invalid Validity Date Difference";
                   }
            if (!isTAFAMD && !isTAFCOR && !isTAFCNL) {
                if(decodedTAF.timeDiff != 6){
                   return "Invalid Validity Time Difference";
                   }                
            }
            const forecastPeriodLenght = decodedTAF.forecastPeriod.length + datetimeLenght;
                        
            // Extracting wind information
            const ktindex = tafMessage.indexOf("KT");
            decodedTAF.baseWind = tafMessage.substring(forecastPeriodLenght+2, ktindex+2);
            decodedTAF.windDirection = decodedTAF.baseWind.substring(0, 3);
        
            if(decodedTAF.baseWind.length == 7){//no gust
                decodedTAF.baseWindType = "normal wind";
                decodedTAF.windSpeed = decodedTAF.baseWind.substring(3, 5);    
           }else if(decodedTAF.baseWind.length == 10){//gust
                decodedTAF.baseWindType = "Gusty wind";
               decodedTAF.windSpeed = decodedTAF.baseWind.substring(3, 8);
           }else{
                decodedTAF.baseWindType = "indeterminable base wind parameters";     
            }
            
            decodedTAF.windUnits = tafMessage.substring(ktindex, ktindex+2);
          
        
            // Extracting base condition, visibility, weather conditions and cloud
            if (tafMessage.indexOf("CAVOK") == ktindex+3) {//if cavok
                decodedTAF.baseCondition = tafMessage.substring(ktindex+3, tafMessage.indexOf("CAVOK") + 5);
                decodedTAF.baseVisibility = "10000";
                decodedTAF.baseWeather = "NIL";
                decodedTAF.baseCloud = "NSC";                
                
            } else {
                //check if change group exist
                if(isChangeGroup(tafMessage)){
                   //get the first index of the first change group                    
                    decodedTAF.baseCondition = tafMessage.substring(ktindex+3, indexOfFirstChangeGroup(tafMessage)-1);
                    
                    //if there is change group but no base condition
                    if(decodedTAF.baseCondition.trim() ===""){
                       return "invalid TAF format, no base condition included in your TAF";
                       }
                     
                   }else{//if no change group is included, base condition becomes the forecast
                       
                       decodedTAF.baseCondition = tafMessage.substring(ktindex+3);
                       
                   }
                if(countWords(decodedTAF.baseCondition)==2){
                   //first word is visibilty and second cloud
                    
                        decodedTAF.baseVisibility = decodedTAF.baseCondition.substring(0, 4);
                        decodedTAF.baseWeather = "NIL";             
                        if(isCLoudGroup(decodedTAF.baseCondition)){
                           decodedTAF.baseCloud = decodedTAF.baseCondition.substring(4);
                        }
                    
                   }else if(countWords(decodedTAF.baseCondition) > 2){
                    //likely all the groups are complete
                    
                        decodedTAF.baseVisibility = decodedTAF.baseCondition.substring(0, 4);
                        const secondWord = decodedTAF.baseCondition.substring(5);
                        
                       //before deciding if next word is weather of cloud, check if the begining of the second word indicates a cloud
                       if(indexOfFirstCloudGroup(secondWord) == 0){                           
                           decodedTAF.baseWeather = "NIL";
                           decodedTAF.baseCloud = secondWord.substring(0);
                       }else{//it contains weather
                           decodedTAF.baseWeather = secondWord.substring(0, indexOfFirstCloudGroup(secondWord)-1);
                           decodedTAF.baseCloud = secondWord.substring(indexOfFirstCloudGroup(secondWord));                           
                       }                       
                        
                   }
            }
            /*
            HANDLING CHANGE GROUPS. get the length and index of basecondition to determine change gropus
            */
        const baseConditionLength = decodedTAF.baseCondition.length;
        const baseConditionIndex = findSubstringIndex(tafMessage, decodedTAF.baseCondition);
        decodedTAF.changeGroup = tafMessage.substring(baseConditionIndex+ baseConditionLength+1).trim();
        //decodedTAF.occurrences = findAllOccurrences(decodedTAF.changeGroup);//all change group
        //decodedTAF.changeGroupLength = getArrayLength(decodedTAF.occurrences); 
        switch (true) {
            case decodedTAF.changeGroup.substring(0,5) === "TEMPO":
                const indexOfTempo = decodedTAF.changeGroup.indexOf("TEMPO");
                decodedTAF.firstChangeGroup = decodedTAF.changeGroup.substring(indexOfTempo, indexOfTempo + 5);
                decodedTAF.firstChangeGroupTime = decodedTAF.changeGroup.substring(indexOfTempo+5, indexOfTempo + 15);
                break;
            case decodedTAF.changeGroup.substring(0,5) === "BECMG":
                const indexOfBecmg = decodedTAF.changeGroup.indexOf("BECMG");
                decodedTAF.firstChangeGroup = decodedTAF.changeGroup.substring(indexOfBecmg, indexOfBecmg + 5);
                decodedTAF.firstChangeGroupTime = decodedTAF.changeGroup.substring(indexOfBecmg+5, indexOfBecmg + 15);
                break;
            case decodedTAF.changeGroup.substring(0,2) === "FM":
                const indexOfFM = decodedTAF.changeGroup.indexOf("FM");
                decodedTAF.firstChangeGroup = decodedTAF.changeGroup.substring(indexOfFM, indexOfFM + 2);//FM
                decodedTAF.firstChangeGroupTime = decodedTAF.changeGroup.substring(indexOfFM + 2, indexOfFM + 9);//FM161500
                break;
            case decodedTAF.changeGroup.substring(0, 12) === "PROB30 TEMPO" || decodedTAF.changeGroup.substring(0, 12) === "PROB40 TEMPO":
                const indexOfProb = decodedTAF.changeGroup.indexOf("PROB");
                decodedTAF.firstChangeGroup = decodedTAF.changeGroup.substring(indexOfProb, indexOfProb + 12);
                decodedTAF.firstChangeGroupTime = decodedTAF.changeGroup.substring(indexOfProb+12, indexOfProb + 22);
                break;
            default: // Only PROB30 or PROB40 without TEMPO
                const indexOfProbDefault = decodedTAF.changeGroup.indexOf("PROB");
                decodedTAF.firstChangeGroup = decodedTAF.changeGroup.substring(indexOfProbDefault, indexOfProbDefault + 6);
                decodedTAF.firstChangeGroupTime = decodedTAF.changeGroup.substring(indexOfProbDefault+6, indexOfProbDefault + 16);
                break;
        }      
            return decodedTAF;
    }
    
    
    
/* Example usage
const exampleText1 = "The forecast includes BECMG and TEMPO changes.";
const exampleText2 = "No significant changes are expected.";
console.log(indexOfFirstChangeGroup(exampleText1)); // Output: 19 (Index of "BECMG")
console.log(indexOfFirstChangeGroup(exampleText2)); // Output: -1 (None of the change groups found)
*/

/* Example usage
const exampleText1 = "The forecast includes BECMG and TEMPO changes.";
const exampleText2 = "No significant changes are expected.";
console.log(isChangeGroup(exampleText1)); // Output: true
console.log(isChangeGroup(exampleText2)); // Output: false
*/
return;
    
    // Extracting date and time
    decodedTAF.date = tafMessage.substring(9, 13);
    decodedTAF.time = tafMessage.substring(13, 15) + ":" + tafMessage.substring(15, 17) + "Z";

   // $("#allstatesforecast").html(decodedTAF);
    //return;
    // Extracting forecast period
    decodedTAF.forecastPeriod = tafMessage.substring(18, 22) + "/" + tafMessage.substring(22, 26);

    // Extracting wind information
    decodedTAF.windDirection = parseInt(tafMessage.substring(27, 30));
    decodedTAF.windSpeed = parseInt(tafMessage.substring(30, 32));
    decodedTAF.windUnits = tafMessage.substring(32, 35);

    // Extracting visibility and weather conditions
    if (tafMessage.includes("CAVOK")) {
        decodedTAF.visibility = "CAVOK";
    } else {
        decodedTAF.visibility = "Not CAVOK";
    }

    // Extracting probability of thunderstorms
    if (tafMessage.includes("PROB30")) {
        decodedTAF.probabilityTS = "30%";
    } else {
        decodedTAF.probabilityTS = "N/A";
    }

    // Extracting temporary changes
    if (tafMessage.includes("TEMPO")) {
        decodedTAF.temporaryChange = "Temporary changes expected";
    } else {
        decodedTAF.temporaryChange = "No temporary changes expected";
    }

    // Extracting wind changes
    const windChangeIndexes = [tafMessage.indexOf("BECMG"), tafMessage.indexOf("BECMG", tafMessage.indexOf("BECMG") + 1)];
    decodedTAF.windChanges = [];
    for (let i = 0; i < windChangeIndexes.length; i++) {
        if (windChangeIndexes[i] !== -1) {
            const changeTime = tafMessage.substring(windChangeIndexes[i] + 6, windChangeIndexes[i] + 10);
            const windDirection = parseInt(tafMessage.substring(windChangeIndexes[i] + 10, windChangeIndexes[i] + 13));
            const windSpeed = parseInt(tafMessage.substring(windChangeIndexes[i] + 13, windChangeIndexes[i] + 15));
            decodedTAF.windChanges.push({ time: changeTime, direction: windDirection, speed: windSpeed });
        }
    }

    // Extracting cloud information
    decodedTAF.clouds = [];
    const cloudRegex = /(\w{3})(\d{3})/;
    const cloudMatches = tafMessage.match(cloudRegex);
    if (cloudMatches) {
        for (let i = 0; i < cloudMatches.length; i += 3) {
            const cloudType = cloudMatches[i + 1];
            const cloudHeight = parseInt(cloudMatches[i + 2]);
            decodedTAF.clouds.push({ type: cloudType, height: cloudHeight });
        }
    }

    return decodedTAF;
}

/* Example TAF message
const exampleTAF = "TAF DNAA 161700Z 1618/1724 32010KT CAVOK PROB30 TEMPO 1619/1622 TS FEW013 FEW020CB BECMG 1622/1624 VRB02KT BECMG 1709/1711 25010KT SCT013=";
const decodedExampleTAF = decodeTAF(exampleTAF);
console.log(decodedExampleTAF);
*/