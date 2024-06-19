const apiKey = '88f85db2138ba99acbf7124931a9527c';

const weatherContainer = document.getElementById("temperature");
const city = document.getElementById("city");
const error = document.getElementById('error');

const units = 'imperial'; //can be imperial or metric
let temperatureSymobol = units == 'imperial' ? "°F" : "°C";

async function fetchWeather() {
    try {
        weatherContainer.innerHTML = '';
        error.innerHTML = '';
        city.innerHTML = '';


        const cnt = 1;
        const cityInputtedByUser = document.getElementById("input").value;

        const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${cityInputtedByUser}&appid=${apiKey}&units=${units}`;


        const response = await fetch(apiUrl);
        const data = await response.json();

        //Display error if user types invalid city or no city
        if (data.cod == '400' || data.cod == '404') {
            error.innerHTML = `Not valid city. Please input another city`;
            return;
        }

        // Display city name based on latitude and longitude
        city.innerHTML = `City: ${cityInputtedByUser}`;
        weatherContainer.innerHTML = `Temperature: ${data.main.temp}`

    } catch (error) {
        console.log(error);
    }
}

function initMap() {
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 10
    });

    // Try HTML5 geolocation
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };

            map.setCenter(pos);

            // Optionally, add a marker at the user's location
            var marker = new google.maps.Marker({
                position: pos,
                map: map
            });
        }, function() {
            handleLocationError(true, map.getCenter());
        });
    } else {
        // Browser doesn't support Geolocation
        handleLocationError(false, map.getCenter());
    }
}

function handleLocationError(browserHasGeolocation, pos) {
    var infoWindow = new google.maps.InfoWindow({
        map: map
    });
    infoWindow.setPosition(pos);
    infoWindow.setContent(browserHasGeolocation ?
        'Error: The Geolocation service failed.' :
        'Error: Your browser doesn\'t support geolocation.');
}
