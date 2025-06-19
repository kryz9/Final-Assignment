#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <Fonts/FreeSerif9pt7b.h>
#include <DHT.h>

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64

// ESP32 pin configuration
const int trigPin = 14;
const int echoPin = 12;
const int relay = 17;
#define DHTPIN 16 // GPIO16
#define DHTTYPE DHT11

const char* ssid = "UUMWiFi_Guest";
const char* password = "";

const char* serverName = "https://mexaze.com.my/post-esp-data.php";
String apiKeyValue = "fdc7d98d-ef4c-433f-bcc4-3f86ae34a96f";
String sensorName = "ESP32";
String sensorLocation = "Home";

DHT dht(DHTPIN, DHTTYPE);
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);

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
  // Ultrasonic sensor
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  float duration_us = pulseIn(echoPin, HIGH);
  float distance_cm = 0.017 * duration_us;

  // DHT11 sensor
  float humidity = dht.readHumidity();
  float temperature = dht.readTemperature();

  //String combinedText = "Distance: " + String(distance_cm, 2) + " cm | Humidity: " + String(humidity, 2) + "% | Temperature: " + String(temperature, 2) + "°C";
  String combinedText = "Humidity: " + String(humidity, 2) + "% | Temperature: " + String(temperature, 2) + "°C";

  int16_t x1, y1;
  uint16_t textWidth, textHeight;
  display.getTextBounds(combinedText, 0, 0, &x1, &y1, &textWidth, &textHeight);

  if (WiFi.status() == WL_CONNECTED) {
    WiFiClientSecure *client = new WiFiClientSecure;
    client->setInsecure();
    HTTPClient https;
    https.setTimeout(4500);
    https.begin(*client, serverName);
    https.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String httpRequestData = "api_key=" + apiKeyValue + "&sensor=" + sensorName +
                             "&location=" + sensorLocation + "&humvalue=" + String(humidity, 2) +
                             "&temvalue=" + String(temperature, 2) + "&disvalue=" + String(distance_cm, 2);

    Serial.print("httpRequestData: ");
    Serial.println(httpRequestData);

    int httpResponseCode = https.POST(httpRequestData);

    if (httpResponseCode > 0) {
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);

      digitalWrite(relay, HIGH);
      delay(2000); // 2 seconds
      digitalWrite(relay, LOW); // Turn off the relay LED

    } else {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }

    https.end();
    delete client;
  } else {
    Serial.println("WiFi Disconnected");
  }

  if (isnan(humidity) || isnan(temperature) || isnan(distance_cm)) {
    display.clearDisplay();
    display.setFont(&FreeSerif9pt7b);
    display.setCursor(0, 20);
    display.println("Sensor error!");
    display.display();
    Serial.println("Sensor error!");
  } else {
    display.clearDisplay();
    display.setFont(&FreeSerif9pt7b);
    display.setCursor(0, 20);
    display.print(combinedText);
    display.display();

    //Serial.print("Distance: ");
    //Serial.print(distance_cm);
    Serial.print("Humidity: ");
    Serial.print(humidity);
    Serial.print("% | Temperature: ");
    Serial.print(temperature);
    Serial.println("°C");
  }

  delay(10000); // 10s delay between readings
}
