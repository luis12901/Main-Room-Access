
void Init_Menu(){
  Serial.println("Seleccione que movimiento le gustaria realizar porfavor:");

  Serial.println("1.- Ocupar una estacion de trabajo con equipo");
  Serial.println("2.- Acompañar a otro estudiante a una estacion");
  Serial.println("3.- Ocupar una estacion sin equipo");
  Serial.println("4.- Consultar que estaciones estan ocupadas");
  Serial.println("5.- Apartar dos o mas estaciones   (Solo maestros)  ");
}

int waitForOptionChoosed() {
  while (true) {
    if (digitalRead(BOTON_1) == HIGH) {
      return 1;
    } else if (digitalRead(BOTON_2) == HIGH) {
      return 2;
    } else if (digitalRead(BOTON_3) == HIGH) {
      return 3;
    } else if (digitalRead(BOTON_4) == HIGH) {
      return 4;
    }
    delay(100);
  }
}


void manageSelectedOption(int option){

    switch(option){
          case 1:
            online(); 
          break;


          case 2:
            
          break;


          case 3:
            
          break;


          case 4:
            
          break;


          default:
          Serial.println("Opción no válida");
            break;
        }

}
