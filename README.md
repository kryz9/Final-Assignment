_**1. Project Title:**_
Smart Flood Detection and Alert System Using ESP32

_**2. Project Description:**_

**Problem Statement:**
Flooding poses a significant risk to homes, especially in low-lying areas. Without timely alerts, property damage and safety risks can escalate rapidly. Many households lack affordable and reliable flood detection systems.

**Objective:**
To develop a smart, cost-effective flood detection system that monitors water level, distance from water surface, temperature, and humidity in near real-time, and provides visual insights through a web interface.
How the Selected Sensors Solve the Problem
- Ultrasonic Sensor: Measures the distance between the water surface and sensor, indicating rising water levels.
- Water Sensor: Detects the presence and intensity of water (wetness), helping confirm flood conditions.
- DHT11: Monitors ambient temperature and humidity, which are environmental indicators often related to weather changes.
- ESP32: Collects sensor data and sends it to a server through Wi-Fi for real-time monitoring.

_**3. Schematic (Circuit Diagram):**_
Tools used: Custom PCB with ESP32 and Breadboard prototype

**ESP32 Pins:**
- DHT11: GPIO 16
- Ultrasonic: Trig GPIO 12, Echo GPIO 13
- Water Sensor: Analog GPIO 34
- Relay: GPIO 17
- OLED: I2C (GPIO 21 SDA, GPIO 22 SCL)

_**4. Arduino Code:**_
Refer to your ESP32-Final.ino sketch.
Key features:
- WiFi connectivity
- OLED status display
- Secure HTTPS POST to database
- JSON-formatted data
- Automatic relay handling possible if implemented

_**5. Web Interface:**_
Developed in PHP + Highcharts.js

Link to website: _https://mexaze.com.my/_

Features:
- Live data visualization (Temperature, Humidity, Water Level, Distance)
- Color indicators for Normal (Green), Caution (Yellow), and Critical (Red) values
- Paginated table log with sortable columns
- Sidebar navigation with responsive layout
Sensor Color Thresholds:
Sensor	Green (Normal)	Yellow (Caution)	Red (Critical)
Temperature	< 30°C	30–34°C	≥ 35°C
Humidity	< 60%	60–79%	≥ 80%
Distance	> 20cm	11–20cm	≤ 10cm
Water Level	< 30%	30–59%	≥ 60%
