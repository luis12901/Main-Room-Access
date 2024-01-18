/*
   Project: RFID Access Control ()
   Description: 
   Author: Jose Luis Murillo Salas
   Creation Date: August 20, 2023
   Contact: joseluis.murillo2022@hotmail.com
*/




  WiFiServer servidor(80);


bool WifiConnected() {

  uint8_t secondsCounter = 0;

  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {

      delay(500);

      secondsCounter++;

      Serial.print(".");

      if(secondsCounter == 20){

          return false;
          
        }

    }
    

  
  if(!MDNS.begin("sensor")) {}
  else{

      servidor.begin();
      MDNS.addService("http", "tcp", 80);

  }


  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("Local IP: ");
  Serial.println(WiFi.localIP());
  Serial.println("MAC address: ");
  Serial.println(WiFi.macAddress());
  return true;

}
bool ServerConnected() {
      
    WiFiClient client;
    HTTPClient http;

  
  
  http.begin(client, serverIP);
  int httpCode = http.GET();

  if (httpCode > 0) {
  
      return true;
       
  }
  else{

      return false;

  }

  http.end();

  delay(100);
    
}

