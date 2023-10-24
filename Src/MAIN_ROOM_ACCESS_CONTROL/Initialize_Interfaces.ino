/*
   Project: RFID Access Control ()
   Description: 
   Author: Jose Luis Murillo Salas
   Creation Date: August 20, 2023
   Contact: joseluis.murillo2022@hotmail.com
*/


void interfaceInit(){

    Serial.begin(115200);
    SPI.begin();       

    MFRC522 mfrc522(SS_PIN, RST_PIN);                                         
    mfrc522.PCD_Init();  

    lcd.begin();    
    lcd.backlight(); 

}