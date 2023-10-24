/*
   Project: RFID Access Control (Offline Mode)
   Description: 
   Author: Jose Luis Murillo Salas
   Creation Date: August 20, 2023
   Contact: joseluis.murillo2022@hotmail.com
*/




void offline(){

  menu();
  
  waitForKeyPress();

  waitForPasswordPress();

  
}
void menu(){
  
  Serial.println(""); 
  Serial.println("Selecciona una opción:");
  Serial.println("1.- Ingresar al modo offline");
  Serial.println("2.- Intentar reconexion");

}
void waitForKeyPress(){
          
  while(true){
  inactivityTimer();


      if(boton_1){
        interaccionOcurre = true;
          inactivityTimer();
          
          Serial.println("Offline mode selected");
          boton_1 = false; 
          break;

      }

    }
  
}
void waitForPasswordPress(){
         
    Serial.println("Please enter the password:");
    uint8_t index = 0;
    while(true){


        inactivityTimer(); 


        char enteredPassword[7];
        char correctPassword[7] = "AAAAAA";

        key = keypad.getKey();

         
        if (key) {
          
          enteredPassword[index] = key;  
          index++;
          Serial.println(key);



          interaccionOcurre = true;
          inactivityTimer(); 
       }


        if (index == 6) {
          interaccionOcurre = true;
          inactivityTimer(); 
         
          if (strcmp(correctPassword, enteredPassword) == 0) {
           
            Serial.println("Access granted"); 
            Serial.println("");

            //lcd.clear();
            //lcd.setCursor(2, 0);
            //lcd.print("Acceso permitido");
            
            digitalWrite(LOCK_PIN, 0);
            delay(8000);
            digitalWrite(LOCK_PIN, 1);
            
          }

          else{

            Serial.println("Access denied. Incorrect password.");
            Serial.println("");

            //lcd.clear();
            //lcd.setCursor(2, 0);
            //lcd.print("Acceso denegado");

            digitalWrite(LOCK_PIN, 1);

          }
          
          index = 0;

          break;
         
        }
 
    }

} 
void IRAM_ATTR boton_1_isr() {
  boton_1 = true; 
}