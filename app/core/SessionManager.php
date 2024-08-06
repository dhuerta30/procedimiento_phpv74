<?php

namespace App\core;

class SessionManager {
    
    // Método estático para iniciar una sesión
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            define('DURACION_SESION','1800'); // 30 minutos en segundos
            // Configurar parámetros de sesión antes de iniciar
            ini_set('session.gc_maxlifetime', DURACION_SESION);  // Tiempo máximo de vida de la sesión
            ini_set('session.cookie_lifetime', DURACION_SESION); // Tiempo de vida de la cookie de sesión
            ini_set("session.save_path", "/tmp");
            session_cache_expire(30); // Duración de la caché de sesiones en minutos
            session_start();
            session_regenerate_id(true);
        }
    }

    // Método estático para establecer un valor en la sesión
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    // Método estático para obtener un valor de la sesión
    public static function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    // Método estático para eliminar un valor de la sesión
    public static function delete($key) {
        unset($_SESSION[$key]);
    }

    // Método estático para destruir la sesión
    public static function destroy() {
        @session_destroy();
    }
}