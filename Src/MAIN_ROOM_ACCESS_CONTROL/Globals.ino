/*
   Project: RFID Access Control ()
   Description: 
   Author: Jose Luis Murillo Salas
   Creation Date: August 20, 2023
   Contact: joseluis.murillo2022@hotmail.com
*/



// INACTIVITY TIMER
  static unsigned long startTime;
  bool interaccionOcurre = true;

// OFFLINE TIMER
  bool offlineInteraction = true;
  unsigned long starOfLoop = 0;
  unsigned long startTimeOffline = 0;

//  WIFI AND SERVER
/*
  char ssid[100]     = "RFID_2.4";
  char password[100] = "3333379426";
  const char* serverIP = "http://192.168.43.197";
  char* phpDirectory = "http://192.168.43.197/MainRoom/AccesoEstacion.php";*/
/*
  char ssid[100]     = "Casa_Murillo_Salas_2.4Gnormal";
  char password[100] = "Guadalajara129#";
  const char* serverIP = "http://192.168.100.12";
  char* phpDirectory = "http://192.168.100.12/MainRoom/AccesoEstacion.php";

*/
  


  char ssid[100]     = "TP-Link_4D28";
  char password[100] = "Guadalajara129#";
  const char* serverIP = "http://192.168.100.12";
  char* phpDirectory = "http://192.168.100.12/MainRoom/AccesoEstacion.php";



/*
  char ssid[100]     = "INFINITUM8664_2.4";
  char password[100] = "7231669603";
  const char* serverIP = "http://192.168.1.124";
  char* phpDirectory = "http://192.168.1.124/MainRoom/AccesoEstacion.php";
*/

// Peripheral_pins

  #define SS_PIN 5
  #define RST_PIN 35
  #define BUZZER_PIN 13
  #define LOCK_PIN 33
  #define LCD 25
  #define RFID 26
  #define deepSleepPin 14
  #define BOTON_1 15
  #define BOTON_2 2
  #define BOTON_3 4
  #define BOTON_4 23




// RFID CARD
  MFRC522 mfrc522(SS_PIN, RST_PIN);
  int readData[4];
  String serialNumber = "";


// Database
  String json1 = "{\"serialNumber\":\"";
  String json2 = "\"}";
  String jsonMessage;


  String currentLine = "";  
  long int tiempoConexionInicio = 0;
  bool finMensaje = false; 
  long int tiempoComparacion = 0;


  uint8_t acceso_nivel = 0;
  uint8_t acceso = 0;
  uint8_t estado = 0;
  String claveS;
  String nombreS;


  WiFiClient clienteServidor;

  


// LCD Variables
    int nombreLength = 0;
    int espaciosLibres = 0;
    int espaciosIzquierda = 0;

// Offline Mode
  //bool key_pressed = false;
  volatile bool boton_1 = false;

// Keyboard_&_LCD
  const uint8_t ROWS = 4;
  const uint8_t COLS = 4;

  char keys[ROWS][COLS] = {
    { '1', '2', '3', 'A' },
    { '4', '5', '6', 'B' },
    { '7', '8', '9', 'C' },
    { '*', '0', '#', 'D' }
  };

  uint8_t colPins[COLS] = { 16, 4, 2, 15 };
  uint8_t rowPins[ROWS] = { 19, 18, 5, 17 };

  Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);

  char key;

  LiquidCrystal_I2C lcd(0x27,10,4);

// Infrared Motion
  const int pirPin = 35; 


// Initial Menu
  int optionSelected = 0;

