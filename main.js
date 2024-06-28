const apiKey = '88f85db2138ba99acbf7124931a9527c';

const weatherContainer = document.getElementById("temperature");
const city = document.getElementById("city");
const error = document.getElementById('error');
const daily = document.getElementById("daily");
const humidity = document.getElementById("humidity");
const sun = document.getElementById("sun");
const wind = document.getElementById("wind");
const condition = document.getElementById("conditions");

const units = 'imperial'; // can be imperial or metric
let temperatureSymbol = units == 'imperial' ? "°F" : "°C";
let map, marker;

async function fetchWeatherByCity(cityInput) {
    try {
        weatherContainer.innerHTML = '';
        error.innerHTML = '';
        city.innerHTML = '';
        daily.innerHTML = '';
        humidity.innerHTML = '';
        wind.innerHTML = '';
        sun.innerHTML = '';
        condition.innerHTML = '';

        const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${cityInput}&appid=${apiKey}&units=${units}`;
        const response = await fetch(apiUrl);
        const data = await response.json();

        if (data.cod == '400' || data.cod == '404') {
            error.innerHTML = `Not valid city. Please input another city`;
            return;
        }

        // Additional details
        const sunrise = convertUnix(data.sys.sunrise);
        const sunset = convertUnix(data.sys.sunset);
        city.innerHTML = `City: ${cityInput}`;
        weatherContainer.innerHTML = `Temperature: ${data.main.temp} ${temperatureSymbol} | Feels Like: ${data.main.feels_like} ${temperatureSymbol}`;
        daily.innerHTML = `Max Temp: ${data.main.temp_max} ${temperatureSymbol} | Min Temp: ${data.main.temp_min} ${temperatureSymbol}`;
        humidity.innerHTML = `Humidity: ${data.main.humidity}%`;
        wind.innerHTML = `Wind Speed: ${data.wind.speed} MPH | Wind Direction: ${data.wind.deg}°`;
        sun.innerHTML = `Sunrise: ${sunrise} | Sunset: ${sunset}`;
        condition.innerHTML =`Current Condition: ${data.weather[0].description}`;

        const geocodeUrl = `https://maps.googleapis.com/maps/api/geocode/json?address=${cityInput}&key=AIzaSyAnnTUI-fzM3lyIilxG8EGYr9iGEbpdveM`;
        const geocodeResponse = await fetch(geocodeUrl);
        const geocodeData = await geocodeResponse.json();
        if (geocodeData.status === 'OK') {
            const location = geocodeData.results[0].geometry.location;
            const pos = { lat: location.lat, lng: location.lng };
            map.setCenter(pos);
            if (marker) {
                marker.setPosition(pos);
            } else {
                marker = new google.maps.Marker({
                    position: pos,
                    map: map
                });
            }
        } else {
            error.innerHTML = 'Unable to geocode the city.';
        }
    } catch (err) {
        console.error(err);
        error.innerHTML = 'Failed to fetch weather data. Please try again later.';
    }
}

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 10
    });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };

            map.setCenter(pos);
            marker = new google.maps.Marker({
                position: pos,
                map: map
            });

            fetchWeatherByCoords(pos.lat, pos.lng);
        }, function (error) {
            console.error('Error occurred. Error code: ' + error.code);
            handleLocationError(true, map.getCenter());
        });
    } else {
        console.error('Geolocation is not supported by this browser.');
        handleLocationError(false, map.getCenter());
    }
}

async function fetchWeatherByCoords(lat, lng) {
    try {
        const apiUrl = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lng}&appid=${apiKey}&units=${units}`;
        const response = await fetch(apiUrl);
        const data = await response.json();

        // Additional details
        const sunrise = convertUnix(data.sys.sunrise);
        const sunset = convertUnix(data.sys.sunset);
        city.innerHTML = `City: ${data.name}`;
        weatherContainer.innerHTML = `Temperature: ${data.main.temp} ${temperatureSymbol} | Feels Like: ${data.main.feels_like} ${temperatureSymbol}`;
        daily.innerHTML = `Max Temp: ${data.main.temp_max} ${temperatureSymbol} | Min Temp: ${data.main.temp_min} ${temperatureSymbol}`;
        humidity.innerHTML = `Humidity: ${data.main.humidity}%`;
        wind.innerHTML = `Wind Speed: ${data.wind.speed} MPH | Wind Direction: ${data.wind.deg}°`;
        sun.innerHTML = `Sunrise: ${sunrise} | Sunset: ${sunset}`;
        condition.innerHTML =`Current Condition: ${data.weather[0].description}`;
    } catch (error) {
        console.log(error);
    }
}

function convertUnix(unixTimestamp) {
    const date = new Date(unixTimestamp * 1000);
    const hours = ('0' + date.getHours()).slice(-2);
    const minutes = ('0' + date.getMinutes()).slice(-2);
    return `${hours}:${minutes}`;
}

function handleLocationError(browserHasGeolocation, pos) {
    const infoWindow = new google.maps.InfoWindow({
        map: map
    });
    infoWindow.setPosition(pos);
    infoWindow.setContent(browserHasGeolocation ?
        'Error: The Geolocation service failed.' :
        'Error: Your browser doesn\'t support geolocation.');
}

document.getElementById("submit").addEventListener("click", function () {
    const cityInputtedByUser = document.getElementById("input").value;
    if (cityInputtedByUser) {
        fetchWeatherByCity(cityInputtedByUser);
    } else {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                fetchWeatherByCoords(pos.lat, pos.lng);
            }, function () {
                handleLocationError(true, map.getCenter());
            });
        } else {
            handleLocationError(false, map.getCenter());
        }
    }
});

// Initialize map on load
initMap();
