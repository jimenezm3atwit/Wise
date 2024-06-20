const apiKey = '88f85db2138ba99acbf7124931a9527c';

const weatherContainer = document.getElementById("temperature");
const city = document.getElementById("city");
const error = document.getElementById('error');
const daily = document.getElementById("daily");
const humidity = document.getElementById("humidity");
const wind = document.getElementById("1hrRain");
const sun = document.getElementById("sun");

const units = 'imperial'; //can be imperial or metric
let temperatureSymobol = units == 'imperial' ? "°F" : "°C";

async function fetchWeather() {
    try {
        weatherContainer.innerHTML = '';
        error.innerHTML = '';
        city.innerHTML = '';
        daily.innerHTML = '';
        humidity.innerHTML = '';
        wind.innerHTML = '';
        sun.innerHTML = '';

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

        sunrise = convertUnix(data.sys.sunrise);
        sunset = convertUnix(data.sys.sunset);

        // Display city name based on latitude and longitude
        city.innerHTML = `City: ${data.name}`;
        weatherContainer.innerHTML = `Temperature: ${data.main.temp}°F | Feels Like: ${data.main.feels_like}°F`;
        daily.innerHTML = `Max Temp: ${data.main.temp_max}°F | Min Temp: ${data.main.temp_min}°F`;
        humidity.innerHTML = `Humidity: ${data.main.humidity}%`;
        wind.innerHTML = `Wind Speed: ${data.wind.speed} MPH | Wind Direction: ${data.wind.deg}°`;
        sun.innerHTML = `Sunrise: ${sunrise}AM | Sunset: ${sunset}PM`;

    } catch (error) {
        console.log(error);
    }
}

function convertUnix(unixTimestamp) {
    const date = new Date(unixTimestamp * 1000);

    // Use methods of the Date object to get readable date and time
    const year = date.getFullYear();
    const month = ('0' + (date.getMonth() + 1)).slice(-2); // Months are zero-based
    const day = ('0' + date.getDate()).slice(-2);
    const hours = ('0' + date.getHours()).slice(-2);
    const minutes = ('0' + date.getMinutes()).slice(-2);
    const seconds = ('0' + date.getSeconds()).slice(-2);

    const formattedTime = `${hours}:${minutes}`;
    return formattedTime;
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
