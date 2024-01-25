/*
   Project: RFID Access Control ()
   Description:
   Author: Jose Luis Murillo Salas
   Creation Date: August 20, 2023
   Contact: joseluis.murillo2022@hotmail.com
*/

// Librerias

#include <stdio.h>  /* printf, NULL */
#include <stdlib.h> /* strtod */
#include <esp_sleep.h>
#include <SPI.h>
#include <MFRC522.h>
#include <LiquidCrystal_I2C.h>
#include <ArduinoJson.h>
#include <EEPROM.h>
#include "esp_system.h"
#include <Keypad.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClient.h>
#include <ESPmDNS.h>

// prototypeFunctions();   // Use it only if the code doesn't compile for some missing prototype functions within the other ino files

// Initial Menu
int optionSelected = 0;
bool timerSt = false;
volatile int buttonPressed = 0;
volatile unsigned long lastButtonPressTime = 0;
unsigned long startTime = 0;
  static bool screenUpdated = false;

void setup() {
  pinConfig();
  interfaceInit();
  beginNetworking();
}
void loop() {
  if (onlineVerification()) {
    Init_Menu();
    startTime = millis();
    lastButtonPressTime = millis();
    screenUpdated = false;

    while (true) {
      if(displayMenu()){
        break;
      }
    }
    performAction(optionSelected);

  } else {
    Serial.println("Lo sentimos, no hay servicio.");
  }
}



void waitForOption(unsigned long timeout) {
  unsigned long startTime = millis();
  while (millis() - startTime < timeout) {
    // Aquí puedes poner otras tareas que se ejecutarán mientras esperas
    delay(10);
  }
}
