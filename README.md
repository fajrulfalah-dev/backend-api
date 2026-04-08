# Laravel 11 Auth API 

Sistem autentikasi RESTful API yang dibangun dengan **Laravel 12** dan **Laravel Sanctum**. 

## Tech Stack
- **Framework:** Laravel 12
- **Authentication:** Laravel Sanctum (Token-based)
- **Database:** MySQL
- **Architecture:** Controller-Service Pattern

## Fitur 
- **Service Layer Pattern:** Logika bisnis dipisahkan dari Controller ke dalam `AuthService` untuk menjaga prinsip *Single Responsibility*.
- **Form Request Validation:** Validasi input dipusatkan di kelas khusus (`RegisterRequest` & `LoginRequest`) untuk menjaga controller tetap bersih.
- **API Resources:** Transformasi data menggunakan `UserResource` untuk mencegah kebocoran data sensitif (seperti password) ke Client.
- **Bearer Token Authentication:** Mengamankan endpoint menggunakan token yang dapat dicabut (*Revoke*) saat logout.
- **Consistent JSON Response:** Struktur respon yang seragam untuk memudahkan integrasi oleh Frontend Developer.

---

## Dokumentasi
1. **Register 201 CREATED**
![REGISTER BERHASI](C:\laragon\www\backend-api\screenshot\Register201Created.png)

2. **Login 200 OK**
![LOGIN BERHASI](C:\laragon\www\backend-api\screenshot\Login200Ok.png)

3. **LogOUT 200 OK**
![LOGOUT BERHASI](C:\laragon\www\backend-api\screenshot\Logout200Ok.png)
