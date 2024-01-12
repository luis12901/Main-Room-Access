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

  pinMode(BOTON_1, INPUT_PULLDOWN);
  pinMode(BOTON_2, INPUT_PULLDOWN);
  pinMode(BOTON_3, INPUT_PULLDOWN);
  pinMode(BOTON_4, INPUT_PULLDOWN);



  digitalWrite(LCD, LOW);
  digitalWrite(RFID, LOW);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LOCK_PIN, HIGH);


  //attachInterrupt(digitalPinToInterrupt(deepSleepPin), boton_1_isr, RISING);          // Deepsleep Mode Pin || GND Permanently


}