# Matrix Server

Matrix Server (Termux, Apache, MariaDB, PHP)

Matrix Server es un servidor web local completo para Android construido sobre Termux. Proporciona un entorno **Apache + PHP-FPM + MariaDB** con soporte criptográfico moderno, listo para ejecutar aplicaciones y sitios PHP directamente desde el teléfono, sin root y sin necesidad de una PC.

Es la evolución natural del clásico stack TAMP (Termux, Apache, MariaDB, PHP), reconstruido y modernizado con:

- **Apache 2.4** escuchando en los puertos `80` (HTTP) y `443` (HTTPS)
- **PHP 8.5.1** ejecutado a través de **PHP-FPM** mediante un socket Unix
- **Argon2id** disponible para hashing de contraseñas vía `password_hash()`
- Extensión **Sodium** para criptografía moderna (`sodium_crypto_*`)
- Servidor de base de datos **MariaDB / MySQL**
- **phpMyAdmin** preinstalado y listo para usar
- **Composer** para la gestión de dependencias PHP
- **Certificado SSL autofirmado** generado automáticamente en la primera ejecución
- **Página de bienvenida** con información del servidor y gestor de proyectos

Todo se ejecuta localmente en tu dispositivo. Sin servidor externo, sin nube, sin suscripciones.

## Qué es Matrix Server

Matrix Server convierte tu dispositivo Android en un pequeño servidor web autónomo. Instala y configura todo el stack por ti en un solo paso, y te da un comando simple (`matrix`) para iniciar, detener, reiniciar, actualizar o desinstalar el servidor.

Está pensado para:

- Desarrollo local de PHP en Android
- Probar sitios web y aplicaciones web directamente en el teléfono
- Aprender Apache, PHP-FPM, MariaDB y phpMyAdmin
- Ejecutar pequeños proyectos personales que no necesitan un hosting externo
- Tener un entorno de desarrollo portátil que cabe en el bolsillo

## Arquitectura

```text
             Matrix Server
                   │
              Apache 2.4
          ┌────────┴────────┐
       HTTP :80         HTTPS :443
          │                 │
          └────────┬────────┘
                   │
            mod_proxy_fcgi
                   │
              PHP-FPM
              (socket unix)
                   │
               PHP 8.5.1
          ┌────────┼────────┐
          │        │        │
       Argon2id  Sodium   mysqli
                            │
                         MariaDB
                            │
                        phpMyAdmin
```

PHP ya no se ejecuta dentro de Apache mediante `mod_php`. En su lugar, Apache envía cada petición `.php` a PHP-FPM a través de un socket Unix. Esto es lo que hace que **Argon2id** y **Sodium** estén disponibles para tus aplicaciones PHP.

## Requisitos

- [Termux](https://github.com/termux/termux-app/releases/latest) **v0.118.0** o superior
- Dispositivo Android con suficiente espacio libre (aproximadamente 300 MB tras la instalación completa)
- Conexión a internet estable y rápida para la instalación inicial

## Instalación

1. Instala y abre Termux.

2. Actualiza y mejora los paquetes:

```bash
pkg update && pkg upgrade
```

3. Otorga permiso de almacenamiento a Termux:

```bash
termux-setup-storage
```

4. Instala `git` y clona este repositorio:

```bash
pkg install git -y && cd ~/ && git clone https://github.com/Franklin-Soft/Matrix-Server.git matrix
```

5. Instala Matrix Server:

```bash
cd ~/matrix && bash setup && cd ~/
```

6. Espera a que la instalación termine.

7. ¡Disfruta Matrix Server!

El script `setup` instala y configura Apache, PHP, PHP-FPM, Sodium, MariaDB, phpMyAdmin y Composer, genera un certificado SSL autofirmado, detecta el socket de MariaDB, ajusta PHP-FPM para Termux y registra el comando `matrix` en el `$PATH`.

## Uso

### Iniciar el servidor HTTP

```bash
matrix start
```

Esto inicia Apache, PHP-FPM y MariaDB, y luego abre:

```text
http://localhost/
```

HTTP usa el puerto `80`.

### Iniciar el servidor HTTPS

```bash
matrix start-ssl
```

Esto reinicia Apache con SSL, inicia PHP-FPM y MariaDB, y luego abre:

```text
https://localhost/
```

HTTPS usa el puerto `443`. El certificado autofirmado se genera automáticamente si no existe o ha expirado.

### Detener el servidor

```bash
matrix stop
```

Detiene Apache, PHP-FPM y MariaDB en orden, verifica que se hayan detenido, y limpia sockets huérfanos.

### Reiniciar el servidor

```bash
matrix restart
```

Equivale a `matrix stop` seguido de `matrix start`. Útil cuando cambias configuración y quieres aplicar los cambios sin hacer dos comandos.

### Ver el estado del servidor

```bash
matrix status
```

Muestra:

- Si Apache está activo o apagado
- Si PHP-FPM está activo o apagado (y cuántos procesos)
- Si MariaDB está activo o apagado
- Los puertos `80`, `443` y `3306` en estado `ESCUCHANDO` o `NO ESCUCHA`
- Las rutas reales de los sockets de PHP-FPM y MariaDB
- Las URLs de acceso

Ejemplo:

```text
=== Estado de Matrix Server ===

Apache:      ACTIVO
PHP-FPM:     ACTIVO (3 procesos)
MariaDB:     ACTIVO

Puertos:
 Puerto 80:   ESCUCHANDO
 Puerto 443:  NO ESCUCHA
 Puerto 3306: ESCUCHANDO

Sockets:
 PHP-FPM:     /data/data/com.termux/files/usr/var/run/php-fpm.sock
 MariaDB:     /data/data/com.termux/files/usr/var/run/mysqld.sock

Acceso:
 HTTP:        http://localhost/
 HTTPS:       https://localhost/
 phpMyAdmin:  http://localhost/phpmyadmin
 htdocs:      /sdcard/htdocs
```

### Actualizar Matrix Server

```bash
matrix update
```

Descarga los últimos cambios desde el repositorio oficial, actualiza `httpd.conf`, `httpd-ssl.conf`, `php.ini` y el comando `matrix`, reinstala PHP-FPM y Sodium si es necesario, y regenera la configuración del socket de MariaDB.

### Desinstalar Matrix Server

```bash
matrix uninstall
```

Elimina Apache, PHP, PHP-FPM, Sodium, MariaDB, Composer, los certificados SSL y el directorio local `matrix`.

## Página de bienvenida

Matrix Server incluye una página de bienvenida en `/sdcard/htdocs/index.php` accesible en:

```text
http://localhost/
```

Desde ahí puedes:

- Ver información del servidor (PHP, Apache, SSL, memoria, IPs)
- Ver la lista de proyectos en `/sdcard/htdocs/`
- Crear nuevos proyectos desde el navegador
- Entrar a phpMyAdmin con un clic
- Ver el `phpinfo()` completo

## phpMyAdmin

Matrix Server incluye phpMyAdmin preinstalado.

```text
http://localhost/phpmyadmin
```

- **Usuario:** `root`
- **Contraseña:** *(dejar en blanco)*

Puedes crear bases de datos, usuarios y ejecutar consultas SQL directamente desde el navegador del teléfono.

## Directorio de documentos (htdocs)

Los archivos de tu sitio web o aplicación viven en:

```text
/sdcard/htdocs
```

o, usando la ruta alternativa:

```text
/storage/emulated/0/htdocs
```

Todo lo que coloques ahí será servido por Apache. Por ejemplo:

```text
/sdcard/htdocs/index.php          ← página de bienvenida
/sdcard/htdocs/miapp/             ← tu proyecto
/sdcard/htdocs/phpinfo/           ← phpinfo()
/sdcard/htdocs/phpmyadmin/        ← phpMyAdmin
```

## Argon2id y Sodium

Matrix Server usa **PHP-FPM**, lo que significa que el hashing moderno de contraseñas y la criptografía están disponibles desde el primer momento.

### Verificar desde un archivo PHP

Crea un archivo de prueba:

```bash
cat > /sdcard/htdocs/argon_test.php <<'EOF'
<?php
header('Content-Type: text/plain');

echo "SAPI: " . php_sapi_name() . "\n";
echo "PHP: " . PHP_VERSION . "\n\n";

echo "argon2id definido: ";
var_dump(defined('PASSWORD_ARGON2ID'));

echo "password_algos(): ";
print_r(password_algos());

echo "sodium cargado: ";
var_dump(extension_loaded('sodium'));

echo "\nHash de prueba:\n";
echo password_hash('MiPasswordSegura', PASSWORD_ARGON2ID), "\n";
EOF
```

Luego abre en el navegador:

```text
http://localhost/argon_test.php
```

### Salida esperada

```text
SAPI: fpm-fcgi
PHP: 8.5.1

argon2id definido: bool(true)
password_algos(): Array
(
    [0] => 2y
    [1] => argon2i
    [2] => argon2id
)
sodium cargado: bool(true)

Hash de prueba:
$argon2id$v=19$m=65536,t=4,p=1$...
```

Si ves `fpm-fcgi` y un hash que empieza con `$argon2id$v=19$`, el stack criptográfico está funcionando correctamente.

### Ejemplo de uso en tu aplicación

```php
<?php
// Hash de contraseña con Argon2id
$hash = password_hash($password, PASSWORD_ARGON2ID);

// Verificación
if (password_verify($password, $hash)) {
    echo "Contraseña válida";
}
```

```php
<?php
// Cifrado con Sodium
$key = sodium_crypto_secretbox_keygen();
$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
$ciphertext = sodium_crypto_secretbox('mensaje secreto', $nonce, $key);

// Descifrado
$plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
```

## Comandos

```text
matrix start       Inicia Matrix Server en el puerto 80
matrix start-ssl   Inicia Matrix Server con SSL en el puerto 443
matrix stop        Detiene Matrix Server
matrix restart     Reinicia Matrix Server (stop + start)
matrix status      Muestra el estado de los servicios
matrix update      Actualiza Matrix Server
matrix uninstall   Desinstala Matrix Server
```

## Estructura del proyecto

```text
~/matrix
├── .htaccess
├── LICENSE
├── README.md
├── config.inc.php
├── httpd.conf
├── httpd-ssl.conf
├── index.php
├── php.ini
├── matrix
├── setup
└── update
```

## Componentes instalados

| Componente | Versión | Función |
|---|---|---|
| Apache | 2.4 | Servidor web HTTP/HTTPS |
| PHP | 8.5.1 | Lenguaje de programación del lado del servidor |
| PHP-FPM | 8.5.1 | Procesador FastCGI de PHP |
| Sodium | 1.0.22 | Criptografía moderna |
| Argon2id | — | Hashing seguro de contraseñas |
| MariaDB | 13.0.2 | Base de datos relacional |
| phpMyAdmin | 5.x | Administrador web de bases de datos |
| Composer | 2.10.3 | Gestor de dependencias PHP |
| OpenSSL | — | Certificados SSL/TLS |

## Solución de problemas

### Apache no inicia

Comprueba la configuración:

```bash
apachectl configtest
```

### Los archivos PHP se descargan en lugar de ejecutarse

Asegúrate de que PHP-FPM esté corriendo:

```bash
pgrep -f php-fpm
```

Si no está corriendo, inícialo manualmente:

```bash
php-fpm
```

Luego reinicia Apache:

```bash
matrix restart
```

### Falta el socket de PHP-FPM

El socket se encuentra en:

```text
/data/data/com.termux/files/usr/var/run/php-fpm.sock
```

Si no existe, PHP-FPM no está corriendo. `matrix restart` lo regenera automáticamente.

### El puerto 80 o 443 ya está en uso

Detén cualquier instancia previa de Apache:

```bash
matrix stop
```

Luego inicia de nuevo:

```bash
matrix start
```

### Argon2id no está disponible

Comprueba que PHP-FPM y Sodium estén instalados:

```bash
pkg install php-fpm php-sodium -y
matrix restart
```

Luego verifica:

```bash
curl -s http://localhost/argon_test.php
```

### phpMyAdmin muestra errores de conexión a MariaDB

Verifica que el socket de MariaDB esté detectado correctamente:

```bash
matrix status
```

La línea `MariaDB:` debe mostrar una ruta que exista. Si no, ejecuta:

```bash
matrix restart
```

### phpMyAdmin muestra avisos de `Deprecated E_STRICT`

Es un aviso de compatibilidad entre phpMyAdmin y PHP 8.4+. Ya está suprimido en `php.ini` de Matrix Server. Si aún aparecen, verifica:

```bash
grep error_reporting $PREFIX/etc/php/php.ini
```

Debe mostrar:

```ini
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
```

## Créditos

- [Termux](https://github.com/termux/termux-app)
- [parzibyte.me](https://parzibyte.me/blog/en/2019/04/28/install-apache-php-7-android-termux/)
- [termux-php-apache2-setup](https://github.com/gungunpriatna/termux-php-apache2-setup)
- [termux-webserver](https://github.com/HadiKhoirudin/termux-webserver)

## Licencia

Matrix Server está licenciado bajo la GNU General Public License v3.0. Consulta el archivo [LICENSE](LICENSE) para más detalles.
