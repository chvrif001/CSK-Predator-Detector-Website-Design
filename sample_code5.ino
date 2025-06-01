#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <WebServer.h>
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"
#include "esp_camera.h"
#include "Arduino.h"

// ===== CONFIGURATION =====
// WiFi credentials
const char* ssid = "Doodlepop";        // WIFI SSID
const char* password = "justfuckoff";  // WIFI password

// Telegram configuration
String token = "8065240956:AAEJT7DigtGISpkjkjaKQYNYrGJkpGO07Jc";
String chat_id = "7595966011";

// Website configuration
const char* serverHost = "csk-predator-detector.onrender.com";
const char* uploadUrl = "https://csk-predator-detector.onrender.com/uploads.php";
const char* commandUrl = "https://csk-predator-detector.onrender.com/action_response.php"; // URL to get commands

// Hardware configuration
int gpioPIR = 13;       // Primary PIR Motion Sensor pin
int gpioSecondPIR = 12; // Second PIR Motion Sensor pin
const int LED_PIN = 4;  // Onboard LED pin
const int LIGHT_PIN = 2; // External light control pin
const int BUZZER_PIN = 14; // Buzzer pin for alarm

// Timing configuration
const int MOTION_COOLDOWN = 10000;    // Time between detections (ms)
const int CONNECTION_TIMEOUT = 10000; // Connection timeout (ms)
const int LIGHT_DURATION = 30000;     // Duration to keep light on after motion (ms)
const int BUZZER_DURATION = 5000;     // Duration to keep buzzer on (ms)
const int COMMAND_CHECK_INTERVAL = 5000; // Check for commands every 5 seconds
const int STREAMING_DURATION = 60000;   // Duration to stream video after secondary motion (ms)

// Camera configuration for AI-THINKER ESP32-CAM module
#define PWDN_GPIO_NUM     32
#define RESET_GPIO_NUM    -1
#define XCLK_GPIO_NUM      0
#define SIOD_GPIO_NUM     26
#define SIOC_GPIO_NUM     27
#define Y9_GPIO_NUM       35
#define Y8_GPIO_NUM       34
#define Y7_GPIO_NUM       39
#define Y6_GPIO_NUM       36
#define Y5_GPIO_NUM       21
#define Y4_GPIO_NUM       19
#define Y3_GPIO_NUM       18
#define Y2_GPIO_NUM        5
#define VSYNC_GPIO_NUM    25
#define HREF_GPIO_NUM     23
#define PCLK_GPIO_NUM     22

// Variables for light and buzzer control
unsigned long lightOffTime = 0;
bool lightActive = false;
unsigned long buzzerOffTime = 0;
bool buzzerActive = false;
unsigned long lastCommandCheckTime = 0;

// Variables for streaming control
bool streamingActive = false;
unsigned long streamingOffTime = 0;
WebServer server(80);

// HTML for the streaming page
const char* streamingPage = R"(
<!DOCTYPE html>
<html>
<head>
<title>ESP32-CAM Live Stream</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { font-family: Arial; text-align: center; margin: 0px auto; padding-top: 30px; background-color: #1a1a1a; color: white; }
.container { max-width: 800px; margin: 0 auto; }
img { max-width: 100%; height: auto; border: 2px solid #333; border-radius: 10px; }
.status { background-color: #333; padding: 10px; border-radius: 5px; margin: 20px 0; }
.controls { margin: 20px 0; }
button { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
button:hover { background-color: #45a049; }
</style>
</head>
<body>
<div class="container">
<h1>ESP32-CAM Live Stream</h1>
<div class="status">
<p>Motion detected - Streaming active</p>
<p>Stream will auto-stop in 60 seconds</p>
</div>
<img id="stream" src="/stream" onerror="this.src='/no-stream'">
<div class="controls">
<button onclick="location.reload()">Refresh</button>
<button onclick="stopStream()">Stop Stream</button>
</div>
</div>
<script>
function stopStream() {
  fetch('/stop-stream');
  document.getElementById('stream').src = '/no-stream';
}
</script>
</body>
</html>
)";

const char* noStreamPage = R"(
<!DOCTYPE html>
<html>
<head>
<title>ESP32-CAM - No Stream</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { font-family: Arial; text-align: center; margin: 0px auto; padding-top: 30px; background-color: #1a1a1a; color: white; }
.container { max-width: 800px; margin: 0 auto; }
.message { background-color: #333; padding: 20px; border-radius: 10px; margin: 20px; }
</style>
</head>
<body>
<div class="container">
<h1>ESP32-CAM Security System</h1>
<div class="message">
<h2>No Active Stream</h2>
<p>Live streaming will activate when motion is detected by the secondary sensor.</p>
<p>Device IP: DEVICE_IP</p>
</div>
</div>
</body>
</html>
)";

void setup() {
  // Disable brownout detector
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);
  
  // Initialize serial
  Serial.begin(115200);
  delay(10);
  Serial.println("\n\n=== ESP32-CAM Motion Detection System with Live Streaming ===");
  
  // Setup pins
  pinMode(LED_PIN, OUTPUT);
  pinMode(gpioPIR, INPUT_PULLUP);
  pinMode(gpioSecondPIR, INPUT_PULLUP);
  pinMode(LIGHT_PIN, OUTPUT);
  digitalWrite(LIGHT_PIN, LOW); // Start with light off
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW); // Start with buzzer off
  
  // Connect to WiFi
  connectToWiFi();
  
  // Initialize camera
  initCamera();
  
  // Setup web server routes
  setupWebServer();
  
  // Signal ready with LED flashing
  flashLED(3, 100);
  Serial.println("System ready - waiting for motion or server commands");
  Serial.print("Stream URL: http://");
  Serial.print(WiFi.localIP());
  Serial.println("/");
}

void loop() {
  // Handle web server requests
  server.handleClient();
  
  // Check main PIR sensor for camera triggers
  int primaryMotionDetected = digitalRead(gpioPIR);
  
  // Check second PIR sensor for light control and streaming
  int secondaryMotionDetected = digitalRead(gpioSecondPIR);
  
  // Handle the light control and streaming based on second PIR sensor
  if (secondaryMotionDetected == 1) {
    Serial.println("Secondary motion - light ON and streaming ACTIVATED");
    digitalWrite(LIGHT_PIN, HIGH);
    lightActive = true;
    lightOffTime = millis() + LIGHT_DURATION;
    
    // Activate streaming
    if (!streamingActive) {
      streamingActive = true;
      streamingOffTime = millis() + STREAMING_DURATION;
      Serial.print("Live streaming started at: http://");
      Serial.print(WiFi.localIP());
      Serial.println("/");
      
      // Send notification to Telegram about streaming activation
      sendStreamingNotification();
    } else {
      // Extend streaming time if already active
      streamingOffTime = millis() + STREAMING_DURATION;
      Serial.println("Streaming time extended");
    }
  }
  
  // Turn off light if timer expired
  if (lightActive && millis() >= lightOffTime) {
    Serial.println("Light timeout - OFF");
    digitalWrite(LIGHT_PIN, LOW);
    lightActive = false;
  }
  
  // Turn off streaming if timer expired
  if (streamingActive && millis() >= streamingOffTime) {
    Serial.println("Streaming timeout - OFF");
    streamingActive = false;
  }
  
  // Turn off buzzer if timer expired
  if (buzzerActive && millis() >= buzzerOffTime) {
    Serial.println("Buzzer timeout - OFF");
    digitalWrite(BUZZER_PIN, LOW);
    buzzerActive = false;
  }
  
  // Handle primary motion detection for camera and notifications
  if (primaryMotionDetected == 1) {
    Serial.println("Primary motion detected!");
    flashLED(1, 100); // Quick flash to indicate motion detected
    
    // Capture and send
    captureAndSend();
    
    // Also turn on the light
    digitalWrite(LIGHT_PIN, HIGH);
    lightActive = true;
    lightOffTime = millis() + LIGHT_DURATION;
    
    // Cooldown period to avoid multiple triggers
    Serial.print("Cooldown for ");
    Serial.print(MOTION_COOLDOWN / 1000);
    Serial.println("s");
    delay(MOTION_COOLDOWN);
  }
  
  // Periodically check for commands from the server
  if (millis() - lastCommandCheckTime >= COMMAND_CHECK_INTERVAL) {
    checkServerCommands();
    lastCommandCheckTime = millis();
  }
  
  delay(100); // Short delay to avoid tight loop
}

void setupWebServer() {
  // Root page - show streaming page if active, otherwise show no-stream page
  server.on("/", HTTP_GET, []() {
    if (streamingActive) {
      server.send(200, "text/html", streamingPage);
    } else {
      String page = String(noStreamPage);
      page.replace("DEVICE_IP", WiFi.localIP().toString());
      server.send(200, "text/html", page);
    }
  });
  
  // Stream endpoint
  server.on("/stream", HTTP_GET, handleStream);
  
  // No stream message
  server.on("/no-stream", HTTP_GET, []() {
    server.send(200, "text/plain", "No active stream");
  });
  
  // Stop stream endpoint
  server.on("/stop-stream", HTTP_GET, []() {
    streamingActive = false;
    server.send(200, "text/plain", "Stream stopped");
    Serial.println("Stream stopped by user request");
  });
  
  // Start stream endpoint (for manual activation)
  server.on("/start-stream", HTTP_GET, []() {
    streamingActive = true;
    streamingOffTime = millis() + STREAMING_DURATION;
    server.send(200, "text/plain", "Stream started");
    Serial.println("Stream started by user request");
  });
  
  // Status endpoint
  server.on("/status", HTTP_GET, []() {
    String status = "{";
    status += "\"streaming\": " + String(streamingActive ? "true" : "false") + ",";
    status += "\"light\": " + String(lightActive ? "true" : "false") + ",";
    status += "\"buzzer\": " + String(buzzerActive ? "true" : "false") + ",";
    status += "\"ip\": \"" + WiFi.localIP().toString() + "\"";
    status += "}";
    server.send(200, "application/json", status);
  });
  
  server.begin();
  Serial.println("Web server started");
}

void handleStream() {
  if (!streamingActive) {
    server.send(503, "text/plain", "Streaming not active");
    return;
  }
  
  WiFiClient client = server.client();
  
  // Send HTTP headers for MJPEG stream
  String response = "HTTP/1.1 200 OK\r\n";
  response += "Content-Type: multipart/x-mixed-replace; boundary=frame\r\n\r\n";
  server.sendContent(response);
  
  Serial.println("Client connected to stream");
  
  while (client.connected() && streamingActive) {
    camera_fb_t* fb = esp_camera_fb_get();
    if (!fb) {
      Serial.println("Camera capture failed during streaming");
      break;
    }
    
    if (fb->width > 400) {
      // Send frame boundary
      server.sendContent("--frame\r\n");
      server.sendContent("Content-Type: image/jpeg\r\n");
      server.sendContent("Content-Length: " + String(fb->len) + "\r\n\r\n");
      
      // Send image data
      client.write(fb->buf, fb->len);
      server.sendContent("\r\n");
    }
    
    esp_camera_fb_return(fb);
    
    // Small delay to control frame rate (adjust as needed)
    delay(100); // ~10 FPS
  }
  
  Serial.println("Stream client disconnected");
}

void sendStreamingNotification() {
  // Create a simple notification message
  String message = "🔴 LIVE STREAMING ACTIVATED\n";
  message += "Motion detected by secondary sensor\n";
  message += "Stream URL: http://" + WiFi.localIP().toString() + "/\n";
  message += "Duration: 60 seconds";
  
  // Send notification via Telegram (text message)
  sendTextToTelegram(token, chat_id, message);
}

String sendTextToTelegram(String token, String chat_id, String message) {
  WiFiClientSecure client;
  client.setInsecure();
  
  if (client.connect("api.telegram.org", 443)) {
    String url = "/bot" + token + "/sendMessage";
    String postData = "chat_id=" + chat_id + "&text=" + message;
    
    client.println("POST " + url + " HTTP/1.1");
    client.println("Host: api.telegram.org");
    client.println("Content-Type: application/x-www-form-urlencoded");
    client.println("Content-Length: " + String(postData.length()));
    client.println();
    client.println(postData);
    
    // Wait for response
    String response = "";
    while (client.connected()) {
      String line = client.readStringUntil('\n');
      if (line == "\r") break;
    }
    while (client.available()) {
      response += client.readStringUntil('\n');
    }
    
    client.stop();
    return response;
  }
  
  return "Failed to connect";
}

void connectToWiFi() {
  Serial.print("Connecting to WiFi: ");
  Serial.println(ssid);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  unsigned long startAttemptTime = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startAttemptTime < CONNECTION_TIMEOUT) {
    flashLED(1, 50);
    Serial.print(".");
    delay(500);
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi connected");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
    flashLED(5, 100);
  } else {
    Serial.println("\nWiFi failed. Restarting...");
    flashLED(10, 200);
    delay(5000);
    ESP.restart();
  }
}

void initCamera() {
  Serial.println("Initializing camera...");
  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sscb_sda = SIOD_GPIO_NUM;
  config.pin_sscb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 20000000;
  config.pixel_format = PIXFORMAT_JPEG;

  if (psramFound()) {
    config.frame_size = FRAMESIZE_VGA; // Use VGA for streaming (better performance)
    config.jpeg_quality = 12;  // 0-63 lower is better quality
    config.fb_count = 2;
    Serial.println("PSRAM found - using VGA resolution for streaming");
  } else {
    config.frame_size = FRAMESIZE_QVGA; // 320x240 for devices without PSRAM
    config.jpeg_quality = 15;
    config.fb_count = 1;
    Serial.println("No PSRAM - using QVGA resolution");
  }

  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed 0x%x", err);
    delay(1000);
    ESP.restart();
  }
  
  // Adjust camera settings for streaming
  sensor_t* s = esp_camera_sensor_get();
  s->set_brightness(s, 0);     // -2 to 2
  s->set_contrast(s, 0);       // -2 to 2
  s->set_saturation(s, 0);     // -2 to 2
  s->set_special_effect(s, 0); // 0 to 6 (0 - No Effect)
  s->set_whitebal(s, 1);       // 0 = disable, 1 = enable
  s->set_awb_gain(s, 1);       // 0 = disable, 1 = enable
  s->set_wb_mode(s, 0);        // 0 to 4 - Auto, Sunny, Cloudy, Office, Home
  s->set_exposure_ctrl(s, 1);  // 0 = disable, 1 = enable
  s->set_aec2(s, 0);           // 0 = disable, 1 = enable
  s->set_ae_level(s, 0);       // -2 to 2
  s->set_aec_value(s, 300);    // 0 to 1200
  s->set_gain_ctrl(s, 1);      // 0 = disable, 1 = enable
  s->set_agc_gain(s, 0);       // 0 to 30
  s->set_gainceiling(s, (gainceiling_t)0);  // 0 to 6
  s->set_bpc(s, 0);            // 0 = disable, 1 = enable
  s->set_wpc(s, 1);            // 0 = disable, 1 = enable
  s->set_raw_gma(s, 1);        // 0 = disable, 1 = enable
  s->set_lenc(s, 1);           // 0 = disable, 1 = enable
  s->set_hmirror(s, 0);        // 0 = disable, 1 = enable
  s->set_vflip(s, 0);          // 0 = disable, 1 = enable
  s->set_dcw(s, 1);            // 0 = disable, 1 = enable
  s->set_colorbar(s, 0);       // 0 = disable, 1 = enable
  
  Serial.println("Camera initialized successfully");
}

void captureAndSend() {
  // Get camera frame buffer
  camera_fb_t* fb = esp_camera_fb_get();
  if (!fb) {
    Serial.println("Camera capture failed");
    return;
  }
  
  Serial.print("Captured image: ");
  Serial.print(fb->len);
  Serial.println(" bytes");

  // Send to Telegram
  Serial.println("Sending to Telegram...");
  String telegramResult = sendToTelegram(token, chat_id);
  Serial.println(telegramResult.indexOf("\"ok\":true") > 0 ? "Telegram: Success" : "Telegram: Failed");
  
  // Upload to website
  Serial.println("Uploading to website...");
  bool websiteResult = uploadToWebsite(fb);
  Serial.println(websiteResult ? "Website: Success" : "Website: Failed");
  
  // Free the frame buffer
  esp_camera_fb_return(fb);
}

String sendToTelegram(String token, String chat_id) {
  const char* myDomain = "api.telegram.org";
  String getAll = "", getBody = "";

  // Get a frame from the camera
  camera_fb_t* fb = esp_camera_fb_get();
  if (!fb) {
    Serial.println("Camera capture failed for Telegram");
    return "Camera capture failed";
  }

  // Create secure client with certificate validation disabled
  WiFiClientSecure client_tcp;
  client_tcp.setInsecure();

  // Connect to Telegram API
  Serial.println("Connecting to Telegram API...");
  if (client_tcp.connect(myDomain, 443)) {
    Serial.println("Connected to Telegram API");

    // Prepare multipart form data
    String head = "--India\r\nContent-Disposition: form-data; name=\"chat_id\"; \r\n\r\n" + chat_id + 
                  "\r\n--India\r\nContent-Disposition: form-data; name=\"photo\"; filename=\"esp32-cam.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n";
    String tail = "\r\n--India--\r\n";

    uint16_t imageLen = fb->len;
    uint16_t extraLen = head.length() + tail.length();
    uint16_t totalLen = imageLen + extraLen;

    // Send HTTP header
    client_tcp.println("POST /bot" + token + "/sendPhoto HTTP/1.1");
    client_tcp.print("Host: ");
    client_tcp.println(myDomain);
    client_tcp.print("Content-Length: ");
    client_tcp.println(totalLen);
    client_tcp.println("Content-Type: multipart/form-data; boundary=India");
    client_tcp.println();
    client_tcp.print(head);

    // Send image data in chunks
    uint8_t* fbBuf = fb->buf;
    size_t fbLen = fb->len;

    for (size_t n = 0; n < fbLen; n = n + 1024) {
      if (n + 1024 < fbLen) {
        client_tcp.write(fbBuf, 1024);
        fbBuf += 1024;
      } else if (fbLen % 1024 > 0) {
        size_t remainder = fbLen % 1024;
        client_tcp.write(fbBuf, remainder);
      }
    }

    client_tcp.print(tail);

    // Free the frame buffer
    esp_camera_fb_return(fb);

    // Wait for response with timeout
    int waitTime = 10000;  // timeout 10 seconds
    long startTime = millis();
    boolean state = false;

    while ((startTime + waitTime) > millis()) {
      Serial.print(".");
      delay(100);
      while (client_tcp.available()) {
        char c = client_tcp.read();
        if (c == '\n') {
          if (getAll.length() == 0) state = true;
          getAll = "";
        } else if (c != '\r')
          getAll += String(c);
        if (state == true) getBody += String(c);
        startTime = millis();
      }
      if (getBody.length() > 0) break;
    }
    client_tcp.stop();
    Serial.println("\nTelegram response: " + getBody);
  } else {
    // Free frame buffer if connection failed
    esp_camera_fb_return(fb);
    getBody = "Connection to Telegram failed.";
    Serial.println("Connection to Telegram failed.");
  }

  return getBody;
}

bool uploadToWebsite(camera_fb_t* fb) {
  if (!fb) {
    Serial.println("No frame buffer to upload");
    return false;
  }
  
  Serial.println("Preparing to upload to website...");
  
  // Create secure client with certificate validation disabled
  WiFiClientSecure client;
  client.setInsecure();
  
  const char* host = serverHost;
  int port = 443; // HTTPS port
  
  Serial.println("Connecting to server: " + String(host));
  if (!client.connect(host, port)) {
    Serial.println("Connection to server failed");
    return false;
  }
  Serial.println("Connected to server");
  
  // Create multipart boundary and form parts
  String boundary = "ESP32CAMBoundary";
  String head = "--" + boundary + "\r\n";
  head += "Content-Disposition: form-data; name=\"image\"; filename=\"esp32cam.jpg\"\r\n";
  head += "Content-Type: image/jpeg\r\n\r\n";
  
  String tail = "\r\n--" + boundary + "--\r\n";
  
  // Calculate content length
  uint32_t imageLen = fb->len;
  uint32_t extraLen = head.length() + tail.length();
  uint32_t totalLen = imageLen + extraLen;
  
  // Get the path part of the URL
  String path = "/profile.php"; // Default path
  int pathStart = String(uploadUrl).indexOf('/', 8); // Find the first '/' after 'https://'
  if (pathStart > 0) {
    path = String(uploadUrl).substring(pathStart);
  }
  
  // Send HTTP header
  client.print("POST " + path + " HTTP/1.1\r\n");
  client.print("Host: " + String(host) + "\r\n");
  client.print("Content-Length: " + String(totalLen) + "\r\n");
  client.print("Content-Type: multipart/form-data; boundary=" + boundary + "\r\n");
  client.print("Connection: close\r\n\r\n");
  
  // Send form header for image
  client.print(head);
  
  // Send JPEG data in chunks to avoid memory issues
  uint8_t *fbBuf = fb->buf;
  size_t fbLen = fb->len;
  
  for (size_t n=0; n<fbLen; n=n+1024) {
    if (n+1024 < fbLen) {
      client.write(fbBuf, 1024);
      fbBuf += 1024;
    } else if (fbLen % 1024 > 0) {
      size_t remainder = fbLen % 1024;
      client.write(fbBuf, remainder);
    }
    
    // Print progress dot every 10KB
    if (n % 10240 == 0) {
      Serial.print(".");
    }
  }
  
  // Send form boundary closing
  client.print(tail);
  
  // Wait for server response
  Serial.println("\nWaiting for server response...");
  unsigned long timeout = millis() + 15000; // 15 second timeout
  
  // Read and process HTTP headers
  bool headersDone = false;
  String statusLine = "";
  int statusCode = 0;
  
  while (client.connected() && millis() < timeout) {
    String line = client.readStringUntil('\n');
    line.trim();
    
    // Store first header line (contains status code)
    if (statusLine.length() == 0 && line.startsWith("HTTP/")) {
      statusLine = line;
      // Extract status code
      int spacePos = statusLine.indexOf(' ');
      if (spacePos > 0) {
        statusCode = statusLine.substring(spacePos + 1, spacePos + 4).toInt();
      }
    }
    
    // Empty line indicates end of headers
    if (line.length() == 0) {
      headersDone = true;
      break;
    }
  }
  
  // Read response body
  String responseBody = "";
  while (client.available() && millis() < timeout) {
    char c = client.read();
    responseBody += c;
    
    // Limit response body size to avoid memory issues
    if (responseBody.length() > 1000) {
      responseBody = responseBody.substring(0, 997) + "...";
      break;
    }
  }
  
  // Close connection
  client.stop();
  
  // Process response
  Serial.print("Status: ");
  Serial.println(statusLine);
  Serial.print("Response body: ");
  Serial.println(responseBody);
  
  // Determine success based on status code and response content
  bool success = false;
  
  if (statusCode >= 200 && statusCode < 300) {
    success = true;
  } else if (responseBody.indexOf("success") >= 0 || 
             responseBody.indexOf("uploaded") >= 0 ||
             responseBody.indexOf("OK") >= 0) {
    success = true;
  }
  
  return success;
}

void checkServerCommands() {
  // Check WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi not connected. Attempting to reconnect...");
    connectToWiFi();
    return;
  }
  
  Serial.println("Checking for server commands...");
  
  HTTPClient http;
  WiFiClientSecure client;
  client.setInsecure(); // Skip certificate verification
  
  // Set up the URL with device ID
  String url = String(commandUrl);
  
  // Add device identifier (MAC address)
  String deviceId = WiFi.macAddress();
  deviceId.replace(":", "");
  if (url.indexOf('?') >= 0) {
    url += "&device_id=" + deviceId;
  } else {
    url += "?device_id=" + deviceId;
  }
  
  http.begin(client, url);
  
  // Send request
  int httpCode = http.GET();
  
  if (httpCode > 0) {
    Serial.print("HTTP response code: ");
    Serial.println(httpCode);
    
    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      Serial.println("Command response: " + payload);
      
      // Process the command(s)
      if (payload.indexOf("command") > 0) {
        if (payload.indexOf("capture") > 0) {
          Serial.println("Received CAPTURE command!");
          processCommand("capture");
        }
        if (payload.indexOf("alarm") > 0) {
          Serial.println("Received ALARM command!");
          processCommand("alarm");
        }
        if (payload.indexOf("stream") > 0) {
          Serial.println("Received STREAM command!");
          processCommand("stream");
        }
        // Add more commands as needed
      }
    }
  } else {
    Serial.print("Command check failed, error: ");
    Serial.println(http.errorToString(httpCode));
  }
  
  http.end();
}

// Process commands received from the server
void processCommand(String command) {
  if (command == "capture") {
    // Flash LED to indicate command received
    flashLED(2, 100);
    Serial.println("Executing capture command");
    
    // Capture and send image
    captureAndSend();
    
  } else if (command == "alarm") {
    // Flash LED to indicate command received
    flashLED(3, 100);
    Serial.println("Executing alarm command");
    
    // Activate buzzer
    digitalWrite(BUZZER_PIN, HIGH);
    buzzerActive = true;
    buzzerOffTime = millis() + BUZZER_DURATION;
    Serial.println("Buzzer activated by server command");
    
    // Also turn on light
    digitalWrite(LIGHT_PIN, HIGH);
    lightActive = true;
    lightOffTime = millis() + LIGHT_DURATION;
    Serial.println("Light activated by server command");
    
  } else if (command == "stream") {
    // Flash LED to indicate command received
    flashLED(4, 100);
    Serial.println("Executing stream command");
    
    // Activate streaming
    streamingActive = true;
    streamingOffTime = millis() + STREAMING_DURATION;
    Serial.print("Live streaming activated by server command at: http://");
    Serial.print(WiFi.localIP());
    Serial.println("/");
    
    // Send notification about streaming activation
    sendStreamingNotification();
  }
}

void flashLED(int count, int delayTime) {
  for (int i = 0; i < count; i++) {
    digitalWrite(LED_PIN, HIGH);
    delay(delayTime);
    digitalWrite(LED_PIN, LOW);
    delay(delayTime);
  }
}