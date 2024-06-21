const apiKey = '88f85db2138ba99acbf7124931a9527c';

const weatherContainer = document.getElementById("temperature");
const city = document.getElementById("city");
const error = document.getElementById('error');
const daily = document.getElementById("daily");
const humidity = document.getElementById("humidity");
const wind = document.getElementById("1hrRain");
const sun = document.getElementById("sun");

const units = 'imperial'; // can be imperial or metric
let temperatureSymbol = units == 'imperial' ? "°F" : "°C";
let map, marker;

async function fetchWeatherByCity(cityInput) {
    try {
        clearWeatherData();
        const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${cityInput}&appid=${apiKey}&units=${units}`;
        const response = await fetch(apiUrl);
        const data = await response.json();

        if (data.cod == '400' || data.cod == '404') {
            error.innerHTML = `Not valid city. Please input another city`;
            return;
        }

        displayWeatherData(data);
        geocodeCity(cityInput);

    } catch (error) {
        console.log(error);
    }
}

async function fetchWeatherByCoordinates(lat, lon) {
    try {
        clearWeatherData();
        const apiUrl = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=${units}`;
        const response = await fetch(apiUrl);
        const data = await response.json();

        if (data.cod == '400' || data.cod == '404') {
            error.innerHTML = `Unable to retrieve weather data.`;
            return;
        }

        displayWeatherData(data);
        updateMap(lat, lon);

    } catch (error) {
        console.log(error);
    }
}

function displayWeatherData(data) {
    const sunrise = convertUnix(data.sys.sunrise);
    const sunset = convertUnix(data.sys.sunset);
    city.innerHTML = `City: ${data.name}`;
    weatherContainer.innerHTML = `Temperature: ${data.main.temp} ${temperatureSymbol} | Feels Like: ${data.main.feels_like} ${temperatureSymbol}`;
    daily.innerHTML = `Max Temp: ${data.main.temp_max} ${temperatureSymbol} | Min Temp: ${data.main.temp_min} ${temperatureSymbol}`;
    humidity.innerHTML = `Humidity: ${data.main.humidity}%`;
    wind.innerHTML = `Wind Speed: ${data.wind.speed} MPH | Wind Direction: ${data.wind.deg}°`;
    sun.innerHTML = `Sunrise: ${sunrise} | Sunset: ${sunset}`;
}

function clearWeatherData() {
    weatherContainer.innerHTML = '';
    error.innerHTML = '';
    city.innerHTML = '';
    daily.innerHTML = '';
    humidity.innerHTML = '';
    wind.innerHTML = '';
    sun.innerHTML = '';
}

async function geocodeCity(cityInput) {
    const geocodeUrl = `https://maps.googleapis.com/maps/api/geocode/json?address=${cityInput}&key=AIzaSyAnnTUI-fzM3lyIilxG8EGYr9iGEbpdveM`;
    const geocodeResponse = await fetch(geocodeUrl);
    const geocodeData = await geocodeResponse.json();
    if (geocodeData.status === 'OK') {
        const location = geocodeData.results[0].geometry.location;
        updateMap(location.lat, location.lng);
    } else {
        error.innerHTML = `Unable to geocode the city.`;
    }
}

function updateMap(lat, lon) {
    const pos = { lat: lat, lng: lon };
    map.setCenter(pos);
    if (marker) {
        marker.setPosition(pos);
    } else {
        marker = new google.maps.Marker({
            position: pos,
            map: map
        });
    }
}

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 10
    });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };

            map.setCenter(pos);
            marker = new google.maps.Marker({
                position: pos,
                map: map
            });

            fetchWeatherByCoordinates(pos.lat, pos.lng);
        }, function() {
            handleLocationError(true, map.getCenter());
        });
    } else {
        handleLocationError(false, map.getCenter());
    }
}

function convertUnix(unixTimestamp) {
    const date = new Date(unixTimestamp * 1000);
    const hours = ('0' + date.getHours()).slice(-2);
    const minutes = ('0' + date.getMinutes()).slice(-2);
    return `${hours}:${minutes}`;
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

document.getElementById("submit").addEventListener("click", function() {
    const cityInputtedByUser = document.getElementById("input").value;
    if (cityInputtedByUser) {
        fetchWeatherByCity(cityInputtedByUser);
    } else {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                fetchWeatherByCoordinates(pos.lat, pos.lng);
            }, function() {
                handleLocationError(true, map.getCenter());
            });
        } else {
            handleLocationError(false, map.getCenter());
        }
    }
});

// Initialize map on load
initMap();
