# PortalCore PHP

**PortalCore PHP**, geleneksel bir veritabanı kullanmak yerine tüm verilerini sunucuda şifrelenmiş tek bir JSON dosyasında saklayan, PHP tabanlı dinamik bir web portalıdır. Sistem, kullanıcıların yetkilerine göre farklı uygulamalara erişebileceği bir ana sayfa ve tüm sistemin yönetileceği kapsamlı bir yönetici paneli içerir.

Bu proje, harici bir framework kullanılmadan, saf PHP ile oluşturulmuştur.

## Özellikler

-   **Veritabanı Yok:** Tüm sistem verileri (kullanıcılar, uygulamalar, menüler) `data/data.json.enc` dosyasında saklanır.
-   **Güçlü Şifreleme:** `openssl` (AES-256-CBC) kütüphanesi ile veri dosyası tamamen şifrelenir.
-   **Güvenli Parola Saklama:** Kullanıcı parolaları PHP'nin standart `password_hash()` ve `password_verify()` fonksiyonları ile güvenli bir şekilde hash'lenir.
-   **Dinamik Kullanıcı Portalı:** Kullanıcılar, kendilerine atanan yetkilere göre uygulama kartlarını görür.
-   **Kapsamlı Yönetici Paneli:**
    -   **Kullanıcı Yönetimi:** Kullanıcı ekleme, düzenleme, silme ve yetkilendirme.
    -   **Uygulama Yönetimi:** Portalda gösterilecek uygulamaları yönetme (CRUD).
    -   **Menü Yönetimi:** Ana navigasyon menüsünü yönetme (CRUD).
-   **Güvenlik Odaklı:** CSRF tokenları ile form güvenliği ve `htmlspecialchars` ile XSS koruması sağlanmıştır.

## Teknolojiler

-   **Backend:** Saf PHP (7.4 veya üstü)
-   **Frontend:** Sunucu taraflı oluşturulmuş HTML, Bootstrap 5, FontAwesome ve Vanilla JavaScript.
-   **Şifreleme:** `openssl` PHP eklentisi.

---

## Kurulum ve Çalıştırma

### 1. Gereksinimler

-   Bir web sunucusu (Apache, Nginx vb.)
-   PHP 7.4 veya daha yeni bir sürüm.
-   PHP `openssl` eklentisinin aktif olması. (Genellikle varsayılan olarak aktiftir, `php.ini` dosyanızdan kontrol edebilirsiniz.)

### 2. Proje Dosyalarını Sunucuya Yükleme

Bu projenin dosyalarını web sunucunuzun kök dizinine (örn: `/var/www/html` veya `htdocs`) kopyalayın.

### 3. Proje Yapılandırması

#### a) Alt Dizin Yapılandırması (BASE_URL)

Projenin temel yapılandırma dosyası `core/config.php`'dir. Eğer projeyi bir alt dizinde (örneğin, `http://siteniz.com/portal`) çalıştırıyorsanız, bu dosyayı açıp `BASE_URL` sabitini kendi dizininize göre düzenlemeniz gerekir:

```php
// Örnek: /portal dizini için
define('BASE_URL', '/portal');

// Örnek: Ana dizin için
// define('BASE_URL', '');
```

#### b) Ortam Değişkenini (Environment Variable) Ayarlama

Uygulamanın en kritik güvenlik adımı, şifreleme anahtarını bir ortam değişkeni olarak ayarlamaktır.

-   **`PORTAL_CORE_SECRET_KEY`**: Bu anahtar, `data.json.enc` dosyasını şifrelemek ve deşifre etmek için kullanılır. **Güçlü, benzersiz ve gizli bir anahtar olmalıdır.**

**Apache için:**
`.htaccess` veya sanal sunucu yapılandırma dosyanıza (`httpd.conf` veya `sites-available/your-site.conf`) şu satırı ekleyin:
```apache
SetEnv PORTAL_CORE_SECRET_KEY "sizin-cok-guclu-ve-benzersiz-anahtariniz-buraya"
```
Değişiklik sonrası Apache'yi yeniden başlatmayı unutmayın.

**Nginx için:**
`nginx.conf` veya sunucu bloğu yapılandırmanızdaki `location ~ \.php$` bloğuna şu parametreyi ekleyin:
```nginx
fastcgi_param PORTAL_CORE_SECRET_KEY "sizin-cok-guclu-ve-benzersiz-anahtariniz-buraya";
```
Değişiklik sonrası Nginx'i yeniden başlatmayı unutmayın.

### 4. Kurulum Betiğini Çalıştırma

Projenin ilk veri dosyasını oluşturmak için tarayıcınızdan veya komut satırından `install.php` betiğini bir kez çalıştırın.

**Tarayıcıdan:**
`http://siteniz.com/install.php` adresine gidin.

**Komut Satırından:**
```bash
php install.php
```

Başarılı bir kurulum sonrası ekranda bir onay mesajı göreceksiniz.

> **ÇOK ÖNEMLİ:** Kurulumu tamamladıktan sonra güvenlik nedeniyle **`install.php` dosyasını sunucudan mutlaka silin!**

### 5. Uygulamayı Kullanma

Artık portalınız hazır! `http://siteniz.com/` adresine giderek uygulamayı kullanmaya başlayabilirsiniz.

**Varsayılan Admin Bilgileri:**
-   **Kullanıcı Adı:** `admin`
-   **Parola:** `admin`

**Güvenlik Uyarısı:** İlk girişinizde, varsayılan `admin` parolasını Yönetici Paneli'nden hemen değiştirmeniz şiddetle tavsiye edilir. Proje, bu konuda sizi uyaracaktır.