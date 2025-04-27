<?php
$apiKey = "ff5352907696f34a8bcbef019263e4de"; 
$city = "Mangaluru";
$countryCode = "IN";
$units = "metric";
$weatherCacheFile = 'weather_details_cache.json'; 
$forecastCacheFile = 'forecast_cache.json';     
$cacheTime = 900; 

$weatherData = null;
$forecastData = null;

if (file_exists($weatherCacheFile) && (time() - filemtime($weatherCacheFile)) < $cacheTime) {
    $weatherData = json_decode(file_get_contents($weatherCacheFile), true);
} else {
    $weatherUrl = "http://api.openweathermap.org/data/2.5/weather?q={$city},{$countryCode}&appid={$apiKey}&units={$units}";
    $weatherApiResponse = @file_get_contents($weatherUrl);
    $weatherData = $weatherApiResponse ? json_decode($weatherApiResponse, true) : null;

    if ($weatherData && $weatherData['cod'] == 200) {
        file_put_contents($weatherCacheFile, json_encode($weatherData));
    } else {
        if (file_exists($weatherCacheFile)) { 
            $weatherData = json_decode(file_get_contents($weatherCacheFile), true);
        }
    }
}


if (file_exists($forecastCacheFile) && (time() - filemtime($forecastCacheFile)) < $cacheTime) {
    $forecastData = json_decode(file_get_contents($forecastCacheFile), true);
} else {
    $forecastUrl = "http://api.openweathermap.org/data/2.5/forecast?q={$city},{$countryCode}&appid={$apiKey}&units={$units}";
    $forecastApiResponse = @file_get_contents($forecastUrl);
    $forecastData = $forecastApiResponse ? json_decode($forecastApiResponse, true) : null;

    if ($forecastData && $forecastData['cod'] == '200') {
        file_put_contents($forecastCacheFile, json_encode($forecastData));
    } else {
        if (file_exists($forecastCacheFile)) { 
            $forecastData = json_decode(file_get_contents($forecastCacheFile), true);
        }
    }
}


$currentWeatherDescription = '';
$currentTemperature = '';
$currentWeatherIcon = '';
$humidity = '';
$windSpeed = '';

if ($weatherData && $weatherData['cod'] == 200) {
$currentWeatherDescription = $weatherData['weather'][0]['description'];
$currentTemperature = round($weatherData['main']['temp']);
$currentWeatherIcon = "http://openweathermap.org/img/wn/{$weatherData['weather'][0]['icon']}.png";
$humidity = $weatherData['main']['humidity'];
$windSpeed = $weatherData['wind']['speed'];
}

$forecastList = [];
if ($forecastData && $forecastData['cod'] == '200') {
$forecastList = $forecastData['list'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Details - Mangaluru</title>
    <link rel="stylesheet" href="css/weather.css">  
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">

       
</head>
<body>
    <div class="weather-details-container">
        <div class="weather-current">
            <h2>Current Weather in Mangaluru</h2>
            <div class="weather-current-icon">
                <?php if ($currentWeatherIcon): ?>
                    <img src="<?php echo $currentWeatherIcon; ?>" alt="Weather Icon">
                <?php endif; ?>
            </div>
            <div class="weather-current-temp"><?php echo $currentTemperature; ?>°C</div>
            <div class="weather-current-desc"><?php echo $currentWeatherDescription; ?></div>
        </div>

        <div class="weather-details-list">
            <h2>Details</h2>
            <div class="detail-item">
                <span class="detail-label">Humidity:</span>
                <span><?php echo $humidity; ?>%</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Wind Speed:</span>
                <span><?php echo $windSpeed; ?> m/s</span>
            </div>
             </div>

        <div class="forecast-section">
            <h2>5-Day Forecast (3-hour intervals)</h2>
            <div class="forecast-list">
                <?php foreach ($forecastList as $forecastItem): ?>
                <?php
                    $forecastTime = date("D, H:i", strtotime($forecastItem['dt_txt'])); 
                    $forecastTemp = round($forecastItem['main']['temp']);
                    $forecastIconCode = $forecastItem['weather'][0]['icon'];
                    $forecastIconUrl = "http://openweathermap.org/img/wn/{$forecastIconCode}.png";
                    $forecastDescription = $forecastItem['weather'][0]['description'];
                ?>
                    <div class="forecast-item">
                        <div class="forecast-time"><?php echo $forecastTime; ?></div>
                        <div class="forecast-icon">
                            <img src="<?php echo $forecastIconUrl; ?>" alt="Forecast Icon">
                        </div>
                        <div class="forecast-temp"><?php echo $forecastTemp; ?>°C</div>
                        <div class="forecast-desc"><?php echo $forecastDescription; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>