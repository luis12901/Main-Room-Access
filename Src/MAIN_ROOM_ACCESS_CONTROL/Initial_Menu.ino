
void Init_Menu()
{
  Serial.println("Seleccione que movimiento le gustaria realizar porfavor:");
  Serial.println("1.- Ocupar/Desocupar una estacion de trabajo con equipo");
  Serial.println("2.- Acompañar a otro estudiante en una estacion");
  Serial.println("3.- Ocupar/Desocupar una estacion sin equipo");
  Serial.println("4.- Apartar dos o mas estaciones   (Solo maestros)  ");
}

void performAction(int option)
{
  lcd.clear();
  switch (option)
  {
  case 1:
    getStation();
    break;

  case 2:
    shareStation();
    break;

  case 3:
    getEmptyStation();
    break;

  case 4:
    getMultipleStations();
    break;

  default:
    Serial.println("Opción no válida");
    break;
  }
  delay(100);
}

void checkButtonPress()
{
  noInterrupts(); // Deshabilita interrupciones
  int currentOption = optionSelected;
  interrupts(); // Habilita interrupciones nuevamente

  if (currentOption != 0)
  {
    performAction(currentOption);
    optionSelected = 0; // Restablece el valor después de procesar la acción
  }
}

bool displayMenu()
{
  if (millis() - startTime < 2000)
  {
    if (!screenUpdated)
    {
    lcd.clear();
    printCentered(0, "1. Ocup/Desocup");
    printCentered(1, "estacion con eq.");
    screenUpdated = true;

    }
  
  }
  else if (millis() - startTime < 4000)
  {
    if (screenUpdated)
    {
    lcd.clear();
    printCentered(0, "2. Compartir");
    printCentered(1, "estacion.");
    screenUpdated = false;

    }

  }
  else if (millis() - startTime < 6000)
  {
    if (!screenUpdated)
    {
    lcd.clear();
    printCentered(0, "3. Ocup/Desocup");
    printCentered(1, "estacion vacia.");
    screenUpdated = true;

    }
    
  }
  else if (millis() - startTime < 8000)
  {
    if (screenUpdated)
    {
    lcd.clear();
    printCentered(0, "4. Apartar dos o");
    printCentered(1, "mas estaciones");
    screenUpdated = false;

    }

  }
  else
  {
    startTime = millis();
  }






  if (digitalRead(BOTON_1) == HIGH)
  {
    optionSelected = 1;
    Serial.println("Opción 1 presionada");
    return true;
  }
  else if (digitalRead(BOTON_2) == HIGH)
  {
    optionSelected = 2;
    Serial.println("Opción 2 presionada");
    return true;
  }
  else if (digitalRead(BOTON_3) == HIGH)
  {
    optionSelected = 3;
    Serial.println("Opción 3 presionada");
    return true;
  }
  else if (digitalRead(BOTON_4) == HIGH)
  {
    optionSelected = 4;
    Serial.println("Opción 4 presionada");
    return true;
  }

  if (millis() - lastButtonPressTime > 50)
  {
    lastButtonPressTime = millis();
  }
  return false;
}

void buttonInterrupt()
{
  unsigned long currentTime = millis();
  if (currentTime - lastButtonPressTime > 300)
  {
    if (digitalRead(BOTON_1) == HIGH)
      buttonPressed = 1;
    else if (digitalRead(BOTON_2) == HIGH)
      buttonPressed = 2;
    else if (digitalRead(BOTON_3) == HIGH)
      buttonPressed = 3;
    else if (digitalRead(BOTON_4) == HIGH)
      buttonPressed = 4;
  }
  lastButtonPressTime = currentTime;
}