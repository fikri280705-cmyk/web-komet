<?php
/**
 * Base Controller untuk Seluruh Controller SIAKAD
 */

namespace App\Core;

abstract class Controller {
    /**
     * Render view dengan layout master
     */
    protected function render(string $viewPath, array $data = [], string $layout = 'main'): void {
        extract($data);

        // Siapkan konten utama
        ob_start();
        $fileView = PATH_VIEWS . '/' . trim($viewPath, '/') . '.php';
        if (file_exists($fileView)) {
            require $fileView;
        } else {
            die("View [{$viewPath}] tidak ditemukan.");
        }
        $content = ob_get_clean();

        // Render layout utama
        $fileLayout = PATH_VIEWS . "/layouts/{$layout}.php";
        if (file_exists($fileLayout)) {
            require $fileLayout;
        } else {
            echo $content;
        }
    }

    /**
     * Render view tanpa layout (misal: halaman login atau modal)
     */
    protected function renderPartial(string $viewPath, array $data = []): void {
        extract($data);
        $fileView = PATH_VIEWS . '/' . trim($viewPath, '/') . '.php';
        if (file_exists($fileView)) {
            require $fileView;
        } else {
            die("View partial [{$viewPath}] tidak ditemukan.");
        }
    }

    /**
     * Mengirimkan response format JSON
     */
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect ke URL aplikasi
     */
    protected function redirect(string $path): void {
        $cleanPath = '/' . ltrim($path, '/');
        header('Location: ' . BASE_URL . $cleanPath);
        exit;
    }
}

