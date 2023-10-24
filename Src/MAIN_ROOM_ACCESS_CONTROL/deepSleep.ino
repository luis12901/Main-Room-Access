

void inactivityTimer(){

  if (!interaccionOcurre) {
        unsigned long elapsedTime = millis() - startTime;

        if (elapsedTime >= 300000) { // If the user is not using this terminal for 5 minutes, we switch to deepSleep mode   
          
          Serial.println();
          Serial.println("      Entering deep sleep mode ......");
          Serial.println();
          delay(100); 

          esp_sleep_enable_ext0_wakeup(GPIO_NUM_14, HIGH); 
          esp_deep_sleep_start();
        }
      }
      else{
        startTime = millis();
        Serial.println("Activity detected, deep sleep timer reset");
        Serial.println(startTime);
        interaccionOcurre = false;
      }  

}
