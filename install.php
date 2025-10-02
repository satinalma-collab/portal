<?php

// This script should be run once from the command line or browser to set up the initial data file.
// For security, it's recommended to delete this file after setup is complete.

require_once __DIR__ . '/core/database.php';

// --- Check if data file already exists ---
if (file_exists(DATA_FILE)) {
    echo "Installation aborted: The data file (" . basename(DATA_FILE) . ") already exists.\n";
    echo "If you want to re-install, please delete the existing data file first.\n";
    exit;
}

// --- Check if secret key is set (and warn the user) ---
if (empty(getenv('PORTAL_CORE_SECRET_KEY'))) {
    echo "-----------------------------------------------------------------\n";
    echo "WARNING: The 'PORTAL_CORE_SECRET_KEY' environment variable is not set.\n";
    echo "The application is using a default, insecure key for development.\n";
    echo "For a production environment, you MUST set a strong, unique secret key.\n";
    echo "-----------------------------------------------------------------\n\n";
}


// --- Define Initial Data ---
$initial_data = [
    "users" => [
        [
            "id" => 1,
            "username" => "admin",
            "passwordHash" => password_hash('admin', PASSWORD_DEFAULT),
            "role" => "admin",
            "permissions" => ["all"]
        ],
        [
            "id" => 2,
            "username" => "kullanici1",
            "passwordHash" => password_hash('password123', PASSWORD_DEFAULT),
            "role" => "user",
            "permissions" => [101, 103]
        ]
    ],
    "applications" => [
        [
            "id" => 101,
            "name" => "Müşteri Yönetim Sistemi",
            "description" => "Müşteri bilgilerini takip etme uygulaması.",
            "path" => "crm.php",
            "icon" => "fa-user-tie"
        ],
        [
            "id" => 102,
            "name" => "Proje Takibi",
            "description" => "Devam eden projelerin durumunu izleyin.",
            "path" => "projects.php",
            "icon" => "fa-tasks"
        ],
        [
            "id" => 103,
            "name" => "Raporlama Aracı",
            "description" => "Satış ve pazarlama raporları oluşturun.",
            "path" => "reports.php",
            "icon" => "fa-chart-pie"
        ]
    ],
    "menus" => [
        [
            "id" => 1,
            "title" => "Anasayfa",
            "path" => "/index.php",
            "order" => 1
        ],
        [
            "id" => 2,
            "title" => "Destek",
            "path" => "/support.php",
            "order" => 2
        ]
    ]
];


// --- Save the Initial Data ---
if (save_data($initial_data)) {
    echo "PortalCore installation successful!\n";
    echo "Initial data has been encrypted and saved to: " . basename(DATA_FILE) . "\n";
    echo "You can now log in with:\n";
    echo "Username: admin\n";
    echo "Password: admin\n";
    echo "\nIMPORTANT: For security, please delete this 'install.php' file now.\n";
} else {
    echo "Installation failed. Could not write to data file. Check file permissions and configuration.\n";
}

?>