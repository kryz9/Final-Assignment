#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <Fonts/FreeSerif9pt7b.h>
#include <DHT.h>

// OLED config
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);

// DHT config
#define DHTPIN 16
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

// Ultrasonic config
const int trigPin = 12;
const int echoPin = 13;
const int relay = 17;

// Water sensor config
#define WATER_SENSOR_PIN 34

// WiFi and server
const char* ssid = "Tersangat la laju";
const char* password = "12345678";
const char* serverName = "https://mexaze.com.my/post-esp-data.php";
String apiKeyValue = "d3d2b9b8-19df-4f23-a898-e30787d81cbb";
String sensorName = "ESP32";
String sensorLocation = "Home";

void setup() {
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);
  pinMode(relay, OUTPUT);
  digitalWrite(relay, LOW); // Make sure it's off initially
  Serial.begin(115200);
  dht.begin();

  WiFi.begin(ssid, password);
  Serial.println("Connecting to WiFi...");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nConnected to WiFi. IP: " + WiFi.localIP().toString());

  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("SSD1306 allocation failed");
    while (true);
  }

  display.clearDisplay();
  display.display();
  display.setFont(&FreeSerif9pt7b);
  display.setTextColor(WHITE);
  delay(2000);
}

void loop() {
  // Ultrasonic
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  float duration_us = pulseIn(echoPin, HIGH);
  float distance_cm = 0.017 * duration_us;

  // DHT11
  float humidity = dht.readHumidity();
  float temperature = dht.readTemperature();

  // Water Sensor
  int waterValue = analogRead(WATER_SENSOR_PIN);
  bool waterDetected = waterValue < 2000;
  float waterPercent = 100.0 - (waterValue / 4095.0) * 100.0;

  String combinedText = "Distance: " + String(distance_cm, 2) + " cm | Humidity: " + String(humidity, 2) + "% | Temperature: " + String(temperature, 2) + "°C | Water: %.1f%%: " + String(waterPercent, 2);

  int16_t x1, y1;
  uint16_t textWidth, textHeight;
  display.getTextBounds(combinedText, 0, 0, &x1, &y1, &textWidth, &textHeight);

  // Send to server
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClientSecure *client = new WiFiClientSecure;
    client->setInsecure();
    HTTPClient https;
    https.setTimeout(4500);
    https.begin(*client, serverName);
    https.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String httpRequestData = "api_key=" + apiKeyValue +
                             "&sensor=" + sensorName +
                             "&location=" + sensorLocation +
                             "&humvalue=" + String(humidity, 2) +
                             "&temvalue=" + String(temperature, 2) +
                             "&disvalue=" + String(distance_cm, 2) +
                             "&watervalue=" + String(waterPercent, 2);

    Serial.print("httpRequestData: ");
    Serial.println(httpRequestData);

    int httpResponseCode = https.POST(httpRequestData);
    if (httpResponseCode > 0) {
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
    } 
    else {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }

    https.end();
    delete client;
  } 
  else {
    Serial.println("WiFi Disconnected");
  }

  // Display to OLED
  if (isnan(humidity) || isnan(temperature) || isnan(distance_cm) || isnan(waterPercent)){
    display.clearDisplay();
    display.setFont(&FreeSerif9pt7b);
    display.setCursor(0, 20);
    display.println("Sensor error!");
    display.display();
    Serial.println("Sensor error!");
  } 
  else {
    display.clearDisplay();
    display.setFont(&FreeSerif9pt7b);
    display.setCursor(0, 20);
    display.print(combinedText);
    display.display();

    // Serial debug
    Serial.println("---- Sensor Readings ----");
    Serial.printf("Temp: %.1f °C\n", temperature);
    Serial.printf("Humidity: %.1f %%\n", humidity);
    Serial.printf("Distance: %.1f cm\n", distance_cm);
    Serial.printf("Water Sensor: %d (%.1f%% wet)\n", waterValue, waterPercent);
    Serial.println("-------------------------\n");
  }

  delay(10000); // 10s delay between readings
}
