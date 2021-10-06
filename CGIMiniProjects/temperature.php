<!DOCTYPE html>
<html>

<head> <title>Project 1 Weather Information</title> </head>

<body>

<div style = "margin-left: 2.5%; font-size: 200%">

<h1 style='margin-top: 1rem; margin-bottom: 1.5rem'>Your Local Weather</h1>

<?php

  // Initialize and Retrieve Weather and Geo information
  $api_key = "CENSORED"; // API key censored for safety
  $ip = $_SERVER['REMOTE_ADDR'];
  $latlong = explode(",", file_get_contents('https://ipapi.co/' . $ip . '/latlong/'));
  $WeatherJSON = file_get_contents('http://api.openweathermap.org/data/2.5/weather?lat=' . $latlong[0] . '&lon=' . $latlong[1] . '&appid=' . $api_key);

  $GeoArray = json_decode( file_get_contents('http://geoip-db.com/json/'. $ip), true);

  $WeatherArray = json_decode($WeatherJSON, true);
  
  
  // Geo Variables
  $City = $WeatherArray["name"];
  $State = $GeoArray["state"];
  $Country = $WeatherArray["sys"]["country"];

  // Weather Variables
  $Description = $WeatherArray["weather"][0]["description"];
  $TemperatureC = ($WeatherArray["main"]["temp"])-273.15;
  $TemperatureF = number_format((($TemperatureC*9)/5) + 32, 1);
  $TemperatureC = number_format($TemperatureC, 2);

  $Humidity = $WeatherArray["main"]["humidity"];
  $VisibilityMe = $WeatherArray["visibility"];
  $VisibilityMi = number_format($VisibilityMe * 0.000621371, 1);
  $Pressure = $WeatherArray["main"]["pressure"];
  $Wind_SpeedMS = $WeatherArray["wind"]["speed"];
  $Wind_SpeedMPH = number_format($Wind_SpeedMS * 2.23694, 1);
  $Wind_Direction = $WeatherArray["wind"]["deg"];
  $Wind_Cardinal_Direction;

  // Find Which direction the wind is facing based on degrees
    if($Wind_Direction <= 45 && $Wind_Direction >= 315)
      $Wind_Cardinal_Direction = "E";
    elseif ($Wind_Direction <= 135 && $Wind_Direction >= 45)
      $Wind_Cardinal_Direction = "N";
    elseif ($Wind_Direction <= 225 && $Wind_Direction >= 135)
      $Wind_Cardinal_Direction = "W";
    else
      $Wind_Cardinal_Direction = "S";   

  $Timezone = $WeatherArray["timezone"];
  $Sunrise = $WeatherArray["sys"]["sunrise"] + $Timezone;
  $Sunrise = gmdate('Y-m-d H:i:s', $Sunrise);

  $Sunset = $WeatherArray["sys"]["sunset"] + $Timezone;
  $Sunset = gmdate('Y-m-d H:i:s', $Sunset);
  $Timezone = timezone_name_from_abbr("", $Timezone, false);

  echo "\n<p>";
  echo "\nIP: <b>$ip</b>";
  echo "\n</p>\n<p>";
  echo "\nCity: <b>$City</b><br>";
  echo "\nState: <b>$State</b><br>";
  echo "\nCountry Name: <b>$Country</b>\n</p>";
  
  
  echo "\nDescription: <b>$Description</b><br>";
  echo "\nTemperature: <b>$TemperatureC</b> C = <b>$TemperatureF</b> F<br>";
  echo "\nHumidity: <b>$Humidity</b>%<br>";
  echo "\nVisibility: <b>$VisibilityMe</b> m = <b>$VisibilityMi</b> miles<br>";
  echo "\nPressure: <b>$Pressure</b> hpa<br>";
  echo "\nWind Speed: <b>$Wind_SpeedMS</b> m/s = <b>$Wind_SpeedMPH</b> mph<br>";
  echo "\nWind Direction: $Wind_Cardinal_Direction (<b>$Wind_Direction</b>)<br>";
  echo "\nTimezone: <b>$Timezone</b><br>";
  echo "\nSunrise: <b>$Sunrise</b><br>";
  echo "\nSunset: <b>$Sunset</b><br>";

?>

<br> <small>Powered by GeoIP and OpenWeather APIs</small>

</div>
</body>
</html>

