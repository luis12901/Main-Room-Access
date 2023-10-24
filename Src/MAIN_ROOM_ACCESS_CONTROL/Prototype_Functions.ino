/*
   Project: RFID Access Control (Online functions)
   Description: This code controls an access system using RFID tags.
   Author: Jose Luis Murillo Salas
   Creation Date: August 20, 2023
   Contact: joseluis.murillo2022@hotmail.com
*/

void prototypeFunctions(){

  bool WifiConnected();

  bool ServerConnected();

  bool beginNetworking();

  void interfaceInit();

  void conexionURL(int counter, char* mensajeJSON, char* servidor, bool pruebas);

  void postJSONToServer();

  bool validateCardPresence();

  void getRFIDData();

}