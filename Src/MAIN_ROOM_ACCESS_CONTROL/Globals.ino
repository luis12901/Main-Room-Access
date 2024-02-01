/*
   Project: RFID Access Control ()
   Description: 
   Author: Jose Luis Murillo Salas
   Creation Date: August 20, 2023
   Contact: joseluis.murillo2022@hotmail.com
*/


// INACTIVITY TIMER

  bool interaccionOcurre = true;

// OFFLINE TIMER
  bool offlineInteraction = true;
  unsigned long starOfLoop = 0;
  unsigned long startTimeOffline = 0;
  const int DISPLAY_TIME = 2000;
//  WIFI AND SERVER


char ssid[100]     = "GIT_LUIS12901";
  char password[100] = "3333379426";
  const char* serverIP = "http://192.168.43.122";
  String phpDir = "http://192.168.43.122/MainRoom/getStation.php";       //  Try to Change Http to Https
  String shareStationDir = "http://192.168.43.122/MainRoom/shareStation.php";
  String emptyStationdir = "http://192.168.43.122/MainRoom/getEmptyStation.php";
  String multiStationdir = "http://192.168.43.122/MainRoom/getMultipleStations.php";
/*
  char ssid[100]     = "Casa_Murillo_Salas_2.4Gnormal";
  char password[100] = "Guadalajara129#";
  const char* serverIP = "http://192.168.100.146";
  String phpDir = "http://192.168.100.146/MainRoom/getStation.php";       
  String shareStationDir = "http://192.168.100.146/MainRoom/shareStation.php";
  String emptyStationdir = "http://192.168.100.146/MainRoom/getEmptyStation.php";
  String multiStationdir = "http://192.168.100.146/MainRoom/getMultipleStations.php";



  char ssid[100]     = "TP-Link_4D28";
  char password[100] = "Guadalajara129#";
  const char* serverIP = "http://192.168.100.35";
  String phpDir = "http://192.168.100.35/MainRoom/getStation.php";       //  Try to switch Http protocol to Https protocol
  String shareStationDir = "http://192.168.100.35/MainRoom/shareStation.php";
  String emptyStationdir = "http://192.168.100.35/MainRoom/getEmptyStation.php";
  String multiStationdir = "http://192.168.100.35/MainRoom/getMultipleStations.php";





*/


// Peripheral_pins

  #define SS_PIN 5
  #define RST_PIN 35
  #define LED_PIN 34
  #define LOCK_PIN 14
  #define LCD 25
  #define RFID 26
  #define deepSleepPin 33
  #define BOTON_1 27
  #define BOTON_2 13    
  #define BOTON_3 33
  #define BOTON_4 32

  #define BUZZER_PIN 12


// RFID CARD
  MFRC522 mfrc522(SS_PIN, RST_PIN);
  int readData[4];
  char enteredStation[7];
  bool option2Selected = false;

      String status_str = "";
      String message = "";
      String station_str = "";
      String userName_str = "";
      String userCode = "";


// Database
String selectedStation = "";
String numberOfStations = "";
  String json1 = "{\"serialNumber\":\"";
  String json2 = "\"}";
  String serialNumber = "";
  String jsonData = "";
  String jsonMessage;

  String json1St = "{\"serialNumber\":\"";
  String json2St = "\",\"station\":\"";
  String json3St = "\"}";

  String station = "";



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




