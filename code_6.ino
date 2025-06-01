#include <Arduino.h>
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"
#include "esp_camera.h"
#include <UniversalTelegramBot.h>
#include <ArduinoJson.h>

const char* ssid = "MyDomainLiving";
const char* password = "mydomain2024";

// Initialize Telegram BOT
String BOTtoken = "8065240956:AAEJT7DigtGISpkjkjaKQYNYrGJkpGO07Jc";  // your Bot Token (Get from Botfather)

// Use @myidbot to find out the chat ID of an individual or a group
// Also note that you need to click "start" on a bot before it can
// message you
String CHAT_ID = "7595966011";

bool sendPhoto = false;

WiFiClientSecure clientTCP;
UniversalTelegramBot bot(BOTtoken, clientTCP);

#define FLASH_LED_PIN 4
bool flashState = LOW;

//Checks for new messages every 1 second.
int botRequestDelay = 1000;
unsigned long lastTimeBotRan;

// PIR sensor 1 (for photo capture)
const int PIRsensor = 13;
const int led = 12;
int PIRstate = LOW; // we start, assuming no motion detected
int val = 0;

// PIR sensor 2 (for light only)
const int PIRsensor2 = 14; // Second PIR sensor pin
const int light2 = 15;     // Second light pin
int PIRstate2 = LOW;       // Second PIR state
int val2 = 0;              // Second PIR value

// Buzzer control
const int buzzerPin = 2;   // Buzzer pin
bool buzzerState = LOW;

// the time we give the sensor to calibrate (approx. 10-60 secs according to datatsheet)
const int calibrationTime = 30; // 30 secs

//CAMERA_MODEL_AI_THINKER
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


void configInitCamera() {
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

  //init with high specs to pre-allocate larger buffers
  if (psramFound()) {
    config.frame_size = FRAMESIZE_UXGA;
    config.jpeg_quality = 10;  //0-63 lower number means higher quality
    config.fb_count = 2;
  } else {
    config.frame_size = FRAMESIZE_SVGA;
    config.jpeg_quality = 12;  //0-63 lower number means higher quality
    config.fb_count = 1;
  }

  // camera init
  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed with error 0x%x", err);
    delay(1000);
    ESP.restart();
  }

  // Drop down frame size for higher initial frame rate
  sensor_t * s = esp_camera_sensor_get();
  s->set_framesize(s, FRAMESIZE_CIF);  // UXGA|SXGA|XGA|SVGA|VGA|CIF|QVGA|HQVGA|QQVGA
}

void handleNewMessages(int numNewMessages) {
  Serial.print("Handle New Messages: ");
  Serial.println(numNewMessages);

  for (int i = 0; i < numNewMessages; i++) {
    String chat_id = String(bot.messages[i].chat_id);
    if (chat_id != CHAT_ID) {
      bot.sendMessage(chat_id, "Unauthorized user", "");
      continue;
    }

    // Print the received message
    String text = bot.messages[i].text;
    Serial.println(text);

    String from_name = bot.messages[i].from_name;
    if (text == "/start") {
      String welcome = "Welcome , " + from_name + "\n";
      welcome += "Use the following commands to interact with the ESP32-CAM \n";
      welcome += "/photo : takes a new photo\n";
      welcome += "/flash : toggles flash LED \n";
      welcome += "/buzzer : toggles buzzer on/off \n";
      bot.sendMessage(CHAT_ID, welcome, "");
    }
    if (text == "/flash") {
      flashState = !flashState;
      digitalWrite(FLASH_LED_PIN, flashState);
      Serial.println("Change flash LED state");
    }
    if (text == "/photo") {
      sendPhoto = true;
      Serial.println("New photo request");
    }
    if (text == "/buzzer") {
      buzzerState = !buzzerState;
      digitalWrite(buzzerPin, buzzerState);
      Serial.print("Buzzer state changed to: ");
      Serial.println(buzzerState ? "ON" : "OFF");
      String buzzerMessage = "Buzzer is now " + String(buzzerState ? "ON" : "OFF");
      bot.sendMessage(CHAT_ID, buzzerMessage, "");
    }
  }
}

String sendPhotoTelegram() {
  const char* myDomain = "api.telegram.org";
  String getAll = "";
  String getBody = "";

  camera_fb_t * fb = NULL;
  fb = esp_camera_fb_get();
  if (!fb) {
    Serial.println("Camera capture failed");
    delay(1000);
    ESP.restart();
    return "Camera capture failed";
  }

  Serial.println("Connect to " + String(myDomain));


  if (clientTCP.connect(myDomain, 443)) {
    Serial.println("Connection successful");

    String head = "--c010blind3ngineer\r\nContent-Disposition: form-data; name=\"chat_id\"; \r\n\r\n" + CHAT_ID + "\r\n--c010blind3ngineer\r\nContent-Disposition: form-data; name=\"photo\"; filename=\"esp32-cam.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n";
    String tail = "\r\n--c010blind3ngineer--\r\n";

    uint16_t imageLen = fb->len;
    uint16_t extraLen = head.length() + tail.length();
    uint16_t totalLen = imageLen + extraLen;

    clientTCP.println("POST /bot" + BOTtoken + "/sendPhoto HTTP/1.1");
    clientTCP.println("Host: " + String(myDomain));
    clientTCP.println("Content-Length: " + String(totalLen));
    clientTCP.println("Content-Type: multipart/form-data; boundary=c010blind3ngineer");
    clientTCP.println();
    clientTCP.print(head);

    uint8_t *fbBuf = fb->buf;
    size_t fbLen = fb->len;
    for (size_t n = 0; n < fbLen; n = n + 1024) {
      if (n + 1024 < fbLen) {
        clientTCP.write(fbBuf, 1024);
        fbBuf += 1024;
      }
      else if (fbLen % 1024 > 0) {
        size_t remainder = fbLen % 1024;
        clientTCP.write(fbBuf, remainder);
      }
    }

    clientTCP.print(tail);

    esp_camera_fb_return(fb);

    int waitTime = 10000;   // timeout 10 seconds
    long startTimer = millis();
    boolean state = false;

    while ((startTimer + waitTime) > millis()) {
      Serial.print(".");
      delay(100);
      while (clientTCP.available()) {
        char c = clientTCP.read();
        if (state == true) getBody += String(c);
        if (c == '\n') {
          if (getAll.length() == 0) state = true;
          getAll = "";
        }
        else if (c != '\r')
          getAll += String(c);
        startTimer = millis();
      }
      if (getBody.length() > 0) break;
    }
    clientTCP.stop();
    Serial.println(getBody);
  }
  else {
    getBody = "Connected to api.telegram.org failed.";
    Serial.println("Connected to api.telegram.org failed.");
  }
  return getBody;
}

void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);
  // Init Serial Monitor
  Serial.begin(115200);

  // Set LED Flash as output
  pinMode(FLASH_LED_PIN, OUTPUT);
  digitalWrite(FLASH_LED_PIN, flashState);

  // Set PIR sensor 1 as input and LED as output
  pinMode(PIRsensor, INPUT);
  pinMode(led, OUTPUT);

  // Set PIR sensor 2 as input and light 2 as output
  pinMode(PIRsensor2, INPUT);
  pinMode(light2, OUTPUT);

  // Set buzzer pin as output
  pinMode(buzzerPin, OUTPUT);
  digitalWrite(buzzerPin, buzzerState);

  // Give some time for the PIR sensors to warm up
  Serial.println("Waiting for the sensors to warm up on first boot");
  delay(calibrationTime * 1000); // Time converted back to miliseconds

  // Blink LED 3 times to indicate PIR sensors warmed up
  digitalWrite(led, HIGH);
  digitalWrite(light2, HIGH);
  delay(500);
  digitalWrite(led, LOW);
  digitalWrite(light2, LOW);
  delay(500);
  digitalWrite(led, HIGH);
  digitalWrite(light2, HIGH);
  delay(500);
  digitalWrite(led, LOW);
  digitalWrite(light2, LOW);
  delay(500);
  digitalWrite(led, HIGH);
  digitalWrite(light2, HIGH);
  delay(500);
  digitalWrite(led, LOW);
  digitalWrite(light2, LOW);
  Serial.println("PIR sensors are ACTIVE");

  // Config and init the camera
  configInitCamera();

  // Connect to Wi-Fi
  WiFi.mode(WIFI_STA);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  clientTCP.setCACert(TELEGRAM_CERTIFICATE_ROOT); // Add root certificate for api.telegram.org
  while (WiFi.status() != WL_CONNECTED) {
    Serial.print(".");
    delay(500);
  }
  Serial.println();
  Serial.print("ESP32-CAM IP Address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  // Read PIR sensor 1 (for photo capture)
  val = digitalRead(PIRsensor);
  
  // Read PIR sensor 2 (for light only)
  val2 = digitalRead(PIRsensor2);

  // Handle PIR sensor 1 (motion detection with photo capture)
  if (val == HIGH) {
    digitalWrite(led, HIGH);
    if (PIRstate == LOW) {
      // we have just turned on because movement is detected
      Serial.println("Motion detected on PIR 1!");
      delay(500);
      Serial.println("Sending photo to Telegram");
      sendPhotoTelegram();
      PIRstate = HIGH;
    }
  }
  else {
    digitalWrite(led, LOW);
    if (PIRstate == HIGH) {
      Serial.println("Motion ended on PIR 1!");
      PIRstate = LOW;
    }
  }

  // Handle PIR sensor 2 (light only)
  if (val2 == HIGH) {
    digitalWrite(light2, HIGH);
    if (PIRstate2 == LOW) {
      Serial.println("Motion detected on PIR 2 - Light ON!");
      PIRstate2 = HIGH;
    }
  }
  else {
    digitalWrite(light2, LOW);
    if (PIRstate2 == HIGH) {
      Serial.println("Motion ended on PIR 2 - Light OFF!");
      PIRstate2 = LOW;
    }
  }

  // Handle manual photo request
  if (sendPhoto) {
    Serial.println("Preparing photo");
    digitalWrite(FLASH_LED_PIN, HIGH);
    Serial.println("Flash state set to HIGH");
    delay(500);
    sendPhotoTelegram();
    sendPhoto = false;
    digitalWrite(FLASH_LED_PIN, LOW);
    Serial.println("Flash state set to LOW");
  }

  // Handle Telegram bot messages
  if (millis() > lastTimeBotRan + botRequestDelay) {
    int numNewMessages = bot.getUpdates(bot.last_message_received + 1);
    while (numNewMessages) {
      Serial.println("got response");
      handleNewMessages(numNewMessages);
      numNewMessages = bot.getUpdates(bot.last_message_received + 1);
    }
    lastTimeBotRan = millis();
  }
}