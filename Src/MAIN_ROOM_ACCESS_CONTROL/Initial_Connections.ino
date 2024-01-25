bool beginNetworking() {
  if (WifiConnected()) {
    lcd.clear();
    printCentered(0, "Conectando");
    printCentered(1, "al servidor ...");
    
    if (ServerConnected()) {
      digitalWrite(BUZZER_PIN, HIGH);
      delay(200);
      digitalWrite(BUZZER_PIN, LOW);
      Serial.println("Connected Successfully");

      lcd.clear();
      printCentered(0, "Conectado");
      printCentered(1, "");
      return true;
    } else {
      digitalWrite(BUZZER_PIN, HIGH);
      delay(2000);
      digitalWrite(BUZZER_PIN, LOW);
      delay(200);
      digitalWrite(BUZZER_PIN, HIGH);
      delay(2000);
      digitalWrite(BUZZER_PIN, LOW);
      Serial.println("Connection Error");

      lcd.clear();
      printCentered(0, "Error de ");
      printCentered(1, "    Conexion");
      return false;
    }
  } else {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(2000);
    digitalWrite(BUZZER_PIN, LOW);
    delay(200);
    digitalWrite(BUZZER_PIN, HIGH);
    delay(2000);
    digitalWrite(BUZZER_PIN, LOW);

    Serial.println("Connection Error");

    lcd.clear();
    printCentered(0, "Error de ");
    printCentered(1, "    Conexion");
    return false;
  }
}
