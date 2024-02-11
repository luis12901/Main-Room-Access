

def factorial(n):
    resultado = 1
    for i in range(1, n + 1):
        resultado *= i
        print(f"Valor de i: {i}, Resultado parcial: {resultado}")
    return resultado




name = input("Ingrese su nombre: ")

with open('nombres.txt','a') as archivo_nombres:

    archivo_nombres.write( name + '\n' )

with open('getStation.php','r') as archivo:
    content = archivo.read()
    print(content)


fact = int(input('Ingrese el numero de su facotorial '+ name+ " :"))

factorial(fact)

nombre = "Luis"
edad = 24

print(f"Hola, mi nombre es {nombre} y tengo {edad} años.")



import mysql.connector

# Establecer conexión
conexion = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="laboratorio"
)

# Crear un cursor para ejecutar consultas
cursor = conexion.cursor()

cursor.execute("SELECT * FROM estaciones WHERE Estado = 'disponible'")
resultados = cursor.fetchall()

# Mostrar los resultados
for fila in resultados:
    print(fila)
cursor.close()
conexion.close()






