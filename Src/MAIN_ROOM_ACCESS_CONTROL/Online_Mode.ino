/*
   Project: RFID Access Control (Online functions)
   Description: This code controls an access system using RFID tags.
   Author: Jose Luis Murillo Salas
   Creation Date: August 20, 2023
   Contact: joseluis.murillo2022@hotmail.com
*/

void sendPostRequest(String data, String directory) {
  HTTPClient http;

  Serial.print("[HTTP] Iniciando POST...\n");
  if (http.begin(directory)) {
    http.addHeader("Content-Type", "application/json");

    Serial.print("[HTTP] Enviando datos JSON...\n");
    int httpResponseCode = http.POST(data);

    if (httpResponseCode > 0) {
      Serial.print("[HTTP] Respuesta del servidor: ");
      Serial.println(httpResponseCode);
      jsonData = "";
      String response = http.getString();

      DynamicJsonDocument doc(2048);
      DeserializationError error = deserializeJson(doc, response);

      if (error) {
        Serial.print("Error al analizar JSON: ");
        Serial.println(error.c_str());
        return;
      }

      String status = doc["status"];
      String message = doc["message"];
      String userName = doc["userName"];
      String userCode = doc["userCode"];
      String station = doc["userStation"];

      status_str = status;
      userName_str = userName;
      station_str = station;

      Serial.println();
      Serial.print("     *** Respuesta del servidor: ");
      Serial.print(message);
      Serial.println("     ***");
      Serial.println();

      Serial.println();
      Serial.print("     *** Nombre del usuario: ");
      Serial.print(userName);
      Serial.println("     ***");
      Serial.println();

      Serial.println();
      Serial.print("     *** Status: ");
      Serial.print(status);
      Serial.println("     ***");
      Serial.println();

      Serial.println();
      Serial.print("     *** Codigo: ");
      Serial.print(userCode);
      Serial.println("     ***");
      Serial.println();

      Serial.println();
      Serial.print("     *** Estacion: ");
      Serial.print(station);
      Serial.println("     ***");
      Serial.println();

      applyAction();

      status = "";
      message = "";
      userName = "";
      userCode = "";
      station = "";

      serialNumber = "";
      userName_str = "";
      status_str = "";
      station_str = "";
      timerSt = false;
      optionSelected = 0;
        ESP.restart();


    
      
    } else {
      jsonData = "";
      Serial.print("[HTTP] Fallo en la solicitud HTTP. Código de error: ");
      Serial.println(httpResponseCode);
        ESP.restart();

    }

    http.end();
  } else {
    Serial.println("[HTTP] Fallo al conectar al servidor");
  }
  
}

void lcdMessages(int selector) {
  if (selector == 1) {
    if (!screenUpdated) {
      lcd.clear();
      printCentered(0, "Coloque su");
      printCentered(1, "credencial");

      Serial.println("Place your credential on the reader");

      screenUpdated = true;
    }
  } else if (selector == 2) {
    lcd.clear();
    printCentered(0, "Bienvenido,");
    printCentered(1, userName_str);
    delay(1000);
    lcd.clear();
    printCentered(0, "Use la estacion:");
    printCentered(1, station_str);
    delay(3000);

  } else if (selector == 3) {
    lcd.clear();
    printCentered(0, "Salida ");
    printCentered(1, "registrada");
  } else if (selector == 4) {
    lcd.clear();
    printCentered(0, "Uusario");
    printCentered(1, "inexistente.");
  } else if (selector == 5) {
    lcd.clear();
    printCentered(0, "No tiene acceso");
    printCentered(1, "a esta aula.");
  } else if (selector == 6) {
    lcd.clear();
    printCentered(1, "Acceso denegado.");
  } else if (selector == 7) {
    lcd.clear();
    printCentered(0, "(DB ERROR.)");
  }

  else if (selector == 8) {
    lcd.clear();
    printCentered(0, "Error. Json");
    printCentered(1, "no valido.");
  }
}

void getStation() {
  while (true) {
    lcdMessages(1);

    getRFIDData();

    if (serialNumber.length() > 0) {
      screenUpdated = false;
      jsonData = "{\"serialNumber\":\"" + serialNumber + "\"}";
      sendPostRequest(jsonData, phpDir);
      break;
    }
  }
}

void shareStation() {

  while (true) {
    lcdMessages(1);
    getRFIDData();
    
    
    if (serialNumber.length() > 0) {
      waitForStationsSelection();
      screenUpdated = false;
      String jsonData = "{\"serialNumber\":\"" + serialNumber + "\",\"station\":\"" + selectedStation + "\"}";

      sendPostRequest(jsonData, shareStationDir);
      option2Selected = true;
      break;
    }
  }
}



void getEmptyStation() {

  while (true) {
    lcdMessages(1);
    getRFIDData();

    if (serialNumber.length() > 0) {
      screenUpdated = false;
      jsonData = "{\"serialNumber\":\"" + serialNumber + "\"}";
      sendPostRequest(jsonData, emptyStationdir);
      break;
    }
  }
}



void waitForStationNumber() {
  lcd.clear();
  printCentered(0, "Numero");
  printCentered(1, "de estaciones");

  Serial.println("Ingrese el número de estaciones que desea usar: ");

  numberOfStations = "";
  uint8_t rightSpace = 14;
  lcd.clear();
  
  printCentered(0, "Numero de");
  lcd.setCursor(1, 1);
  lcd.print("estaciones:");

  uint8_t index = 0;
  while (index < 1) {
    key = keypad.getKey();

    if (key) {

      lcd.setCursor(rightSpace + index, 1);
      lcd.print(key);

      numberOfStations += String(key);
      Serial.println(key);
      index++;
      lcd.setCursor(0, 0);
      lcd.print(index);
    }

  }
}


void getMultipleStations() {
  while (true) {

    lcdMessages(1);
    getRFIDData();

    if (serialNumber.length() > 0) {
      
      waitForStationNumber();
      screenUpdated = false;
      lcd.clear();
      printCentered(0, "Numero");
      printCentered(1, "de estaciones");
      delay(1000);
      printCentered(0, numberOfStations);
      printCentered(1, "estaciones");
      delay(1000);

      String jsonData = "{\"serialNumber\":\"" + serialNumber + "\",\"stationsNumber\":\"" + numberOfStations + "\"}";
      sendPostRequest(jsonData, multiStationdir);
      break;
    }
  }
}

void waitForStationsSelection() {
  lcd.clear();
  printCentered(0, "Numero");
  printCentered(1, "de estaciones");

  Serial.println("Ingrese el número de estaciones que desea usar: ");

  selectedStation = "";
  uint8_t rightSpace = 7;
  lcd.clear();
  printCentered(0, "Estacion:");
  printCentered(1, "");

  uint8_t index = 0;
  while (index < 2) {
    key = keypad.getKey();

    if (key) {
      lcd.setCursor(rightSpace + index, 1);
      lcd.print(key);

      selectedStation += String(key);
      Serial.println(key);
      index++;
      lcd.setCursor(1, 1);
      lcd.print(index);
    }

  }
}





bool onlineVerification() {
  static bool previousConnectionState = false;

  bool isConnected = ServerConnected();

  if (isConnected != previousConnectionState) {
    lcd.clear();

    if (isConnected) {
      printCentered(0, "Conectado");
      printCentered(1, "");
      Serial.println("Connected Successfully");
    } else {
      printCentered(0, "Error de");
      printCentered(1, "conexion");
      Serial.println("Connection Error (Code: 002)");
    }

    previousConnectionState = isConnected;
  }

  return isConnected;
}

void getRFIDData() {
    serialNumber = "";
  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    lcd.clear();


    digitalWrite(BUZZER_PIN, HIGH);
    delay(200);
    digitalWrite(BUZZER_PIN, LOW);

    printCentered(0, "Por favor");
    printCentered(1, "    espere....");

    Serial.println("Card Detected!");
    Serial.println("Please Wait .........");

    for (int i = 0; i < 4; i++) {
      readData[i] = mfrc522.uid.uidByte[i];
    }

    for (int i = 0; i < 4; i++) {
      String decimalByte = String(readData[i]);
      serialNumber += decimalByte;
    }

    if (serialNumber.length() > 0) {
      Serial.print("Serial number: ");
      Serial.println(serialNumber);
    }
  }
}



void applyAction() {

  if (status_str == "success (E)") {
    userEntry();
  } else if (status_str == "success (A)") {
    userEntryAcomp();

  }else if (status_str == "success (M)") {
    userEntryMulti();
  }
  else if (status_str == "success (S)") {
    userExit();
  } else if (status_str == "userNotFound") {
    lcdMessages(4);
    digitalWrite(LOCK_PIN, HIGH);
    delay(3000);
    lcd.clear();
  } else if (status_str == "dbConnectionError") {
    lcdMessages(7);
    delay(3000);
    lcd.clear();
  } else if (status_str == "error") {
    lcdMessages(8);
    delay(3000);
    lcd.clear();
  } else if (status_str == "busy") {
    lcd.clear();
    printCentered(0, "Ya hay dos");
    printCentered(1, "personas aquí.");
    delay(3000);
    lcd.clear();
  } else if (status_str == "stationNotFound") {
    lcd.clear();
    printCentered(0, "Estacion");
    printCentered(1, "inexistente.");
    delay(3000);
    lcd.clear();
  } else if (status_str == "notInUse") {
    lcd.clear();
    printCentered(0, "Esta estacion no");
    printCentered(1, "esta en uso.");
    delay(3000);
    lcd.clear();
  } else if (status_str == "non-existent") {
    lcd.clear();
    printCentered(0, "Estacion");
    printCentered(1, "inexistente.");
    delay(3000);
    lcd.clear();
  } else if (status_str == "already-accompanying") {
    lcd.clear();
    printCentered(0, "Ya esta asignado");
    printCentered(1, "con alguien.");
    delay(3000);
    ESP.restart();
    lcd.clear();
  } else if (status_str == "already-using-a-station") {
    lcd.clear();
    printCentered(0, "Ya esta usando");
    printCentered(1, "una estacion.");
    delay(3000);
    ESP.restart();
    lcd.clear();
  } else if (status_str == "userNotFound") {
    lcd.clear();
    printCentered(0, "Usuario no");
    printCentered(1, "registrado.");
    delay(3000);
    lcd.clear();
  } else if (status_str == "success (SHARE)") {
    lcd.clear();
    lcdMessages(2);
    delay(3000);
    lcd.clear();
  }
}

void userEntry() {
  digitalWrite(LOCK_PIN, LOW);
  lcdMessages(2);
  Serial.print("Bienvenido ");
  Serial.print(userName_str);
  Serial.println(", tu entrada ha sido registrada.");

  delay(6000);
  digitalWrite(LOCK_PIN, HIGH);
  lcd.clear();
}
void userEntryAcomp(){
  digitalWrite(LOCK_PIN, LOW);
  lcd.clear();
    printCentered(0, "Bienvenido,");
    printCentered(1, userName_str);
    delay(1000);
    lcd.clear();
    printCentered(0, "Estaciones:");
    printCentered(1, station_str);
    delay(3000);
  Serial.print("Bienvenido ");
  Serial.print(userName_str);
  Serial.println(", tu entrada ha sido registrada.");

  delay(6000);
  digitalWrite(LOCK_PIN, HIGH);
  ESP.restart();

}

void userEntryMulti(){
  digitalWrite(LOCK_PIN, LOW);
  lcd.clear();
    printCentered(0, "Bienvenido,");
    printCentered(1, userName_str);
    delay(1000);
    lcd.clear();
  int comma_pos = station_str.indexOf(',');
  String first_station = station_str.substring(0, comma_pos);
  int last_comma_pos = station_str.lastIndexOf(',');
  String last_station = station_str.substring(last_comma_pos + 1);
  String stationsAv = first_station + " - " + last_station;
    printCentered(0, "Estacion:");
    printCentered(1, stationsAv);
    delay(3000);

  Serial.print("Bienvenido ");
  Serial.print(userName_str);
  Serial.println(", tu entrada ha sido registrada.");

  Serial.print("Estaciones disponibles:  ");
  Serial.println(station_str);

  delay(6000);
  digitalWrite(LOCK_PIN, HIGH);
    ESP.restart();

  

}

void userExit() {
  lcdMessages(3);
  Serial.print(userName_str);
  Serial.println(", tu salida ha sido registrada.");
  digitalWrite(LOCK_PIN, LOW);
  delay(5000);
  digitalWrite(LOCK_PIN, HIGH);
  lcd.clear();
}

void conexionURL(int counter, char *mensajeJSON, char *servidor, bool pruebas) {
  char temporal[50];
  char mensajeHTML[400];
  char Usuario[10] = "bot33";
  char urlVar[10] = "/";
  int j = 0;

  memset(mensajeHTML, NULL, sizeof(mensajeHTML));
  memset(temporal, NULL, sizeof(temporal));

  int cuantosBytes = strlen(mensajeJSON);
  sprintf(temporal, "POST %s HTTP/1.0\r\n", urlVar);
  strcat(mensajeHTML, temporal);

  sprintf(temporal, "Host: %s \r\n", servidor);
  strcat(mensajeHTML, temporal);

  sprintf(temporal, "User-Agent: %s \r\n", Usuario);
  strcat(mensajeHTML, temporal);

  sprintf(temporal, "Content-Length: %i \r\n", cuantosBytes);
  strcat(mensajeHTML, temporal);

  strcat(mensajeHTML, "Content-Type: application/json\r\n");
  strcat(mensajeHTML, "\r\n");
  strcat(mensajeHTML, mensajeJSON);

  int cuantosMensaje = strlen(mensajeHTML);

  if (!pruebas) {
    WiFiClient client;
    HTTPClient http;
    http.begin(client, servidor);
    http.addHeader("Content-Type", "application/json");
    int httpResponseCode = http.POST(mensajeJSON);

    Serial.print("Codigo HTTP de respuesta: ");
    Serial.println(httpResponseCode);

    if (httpResponseCode > 0) {
      String respuesta = http.getString();
      Serial.print("Respuesta del servidor: ");
      Serial.println(respuesta);
    }

    http.end();
  } else {
    Serial.println("Bytes para transmitir: ");
    Serial.println("");
    Serial.println(cuantosMensaje);
    for (j = 0; j <= cuantosMensaje - 1; j++) {
      Serial.print(mensajeHTML[j]);
    }
    Serial.println(" ");
  }
  serialNumber = "";
}
