/*
   Project: RFID Access Control ()
   Description: 
   Author: Jose Luis Murillo Salas
   Creation Date: August 20, 2023
   Contact: joseluis.murillo2022@hotmail.com
*/



void pinConfig(){

  pinMode(deepSleepPin, INPUT_PULLDOWN);
  pinMode(LCD, OUTPUT);
  pinMode(RFID, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LOCK_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);

  pinMode(BOTON_1, INPUT_PULLDOWN);
  pinMode(BOTON_2, INPUT_PULLDOWN);
  pinMode(BOTON_3, INPUT_PULLDOWN);
  pinMode(BOTON_4, INPUT_PULLDOWN);
  /*attachInterrupt(digitalPinToInterrupt(BOTON_1), buttonInterrupt, RISING);
  attachInterrupt(digitalPinToInterrupt(BOTON_2), buttonInterrupt, RISING);
  attachInterrupt(digitalPinToInterrupt(BOTON_3), buttonInterrupt, RISING);
  attachInterrupt(digitalPinToInterrupt(BOTON_4), buttonInterrupt, RISING);
*/

  digitalWrite(LCD, LOW);
  digitalWrite(RFID, LOW);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LOCK_PIN, HIGH);
  digitalWrite(LED_PIN, LOW);


  //attachInterrupt(digitalPinToInterrupt(deepSleepPin), boton_1_isr, RISING);          // Deepsleep Mode Pin || GND Permanently


}