; ================================================================
; MATRIX SERVER - php.ini
; https://github.com/Franklin-Soft/Matrix-Server
; ================================================================
; IMPORTANTE: Las extensiones (sodium, mysqli, pdo_mysql, etc.)
; se cargan automaticamente desde $PREFIX/etc/php/conf.d/*.ini
; NO las declares aqui para evitar duplicados.
; ================================================================

; --- Errores ---
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /data/data/com.termux/files/usr/var/log/php_errors.log

; --- Zona horaria ---
date.timezone = America/Lima

; --- Memoria y limites ---
memory_limit = 256M
post_max_size = 32M
upload_max_filesize = 32M
max_execution_time = 300
max_input_time = 300

; --- Sesiones ---
session.gc_maxlifetime = 1440

; --- OPcache ---
opcache.enable = 1
opcache.enable_cli = 0
opcache.validate_timestamps = 1
opcache.revalidate_freq = 0
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000