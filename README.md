# Firestarter Akadémia Weboldal 🔥
## Projekt Áttekintés
A Firestarter Akadémia egy modern, reszponzív weboldal PHP backend-del és MySQL adatbázissal. A rendszer lehetővé teszi a felhasználók regisztrációját, bejelentkezését, időpontfoglalást és adminisztrációt.
## Főbb Funkciók
### 🔐 Felhasználói Rendszer
- Regisztráció és bejelentkezés (hagyományos és Google OAuth)
- Jelszó visszaállítás funkció
- Felhasználói profilkezelés
- Adminisztrátori felület
### 📅 Időpontfoglalás
- Szolgáltatások böngészése
- Interaktív naptár felület
- Időpontok foglalása és lemondása
- Foglalások kezelése
### 🎨 Design
- Modern, reszponzív felület
- Sötét/világos téma támogatás
- Animációk (AOS könyvtár)
- Gradient design elemek
### 👨‍💼 Adminisztráció
- Felhasználók kezelése
- Szolgáltatások menedzselése
- Foglalások áttekintése
- Rendszerbeállítások
---
## Technikai Részletek
### Frontend
- HTML5- CSS3 (egyedi változókkal)- JavaScript (vanilla)- AOS animációs könyvtár- Reszponzív design (mobile-first megközelítés)\
### Backend
- PHP 7+- MySQL adatbázis- PDO adatbázis kapcsolat- Google OAuth integráció\
### Biztonsági Funkciók
> - Jelszó titkosítás
> - CSRF védelem
> - SQL injection védelem
> - XSS védelem
> - Biztonságos munkamenet kezelés
## Telepítés
1. Klónozza le a repót
2. Állítsa be az adatbázis kapcsolatot a \db_config.php\ fájlban
3. Importálja az SQL sémát
4. Konfigurálja a Google OAuth beállításokat
5. Állítsa be a webszerver gyökérkönyvtárát
## Adatbázis Séma
A rendszer a következő fő táblákat használja:
- users
- services
- bookings
- time_slots
- auth_tokens
## Közreműködés
A projekthez való hozzájárulást szívesen fogadjuk. Kérjük, kövesse a következő lépéseket:
1. Fork-olja a repót
2. Hozzon létre egy új branch-et
3. Commitolja a változtatásokat
4. Nyisson egy Pull Request-et

### 👨‍💼 Fejlesztők
> - Balogh Richárd
> - Takács Botond
## Licensz
*MIT License
---
*Ez a dokumentáció folyamatosan frissül.
